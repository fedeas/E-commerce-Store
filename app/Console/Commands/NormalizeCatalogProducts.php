<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NormalizeCatalogProducts extends Command
{
    protected $signature = 'catalog:normalize-products
        {--limit= : Maximum products to scan}
        {--product-id=* : Scan only specific product IDs}
        {--apply-extra-fields : Write detected extra field values}
        {--apply-titles : Update product titles when confidence is high}
        {--only-missing-extra-fields : Do not overwrite existing extra field values}
        {--min-title-confidence=85 : Minimum confidence for title updates}';

    protected $description = 'Normalize product text, extract safe technical extra fields, and optionally update canonical product titles.';

    private array $fieldIds = [];

    private ?string $titleColumn = null;

    private bool $productsHaveUpdatedAt = false;

    public function handle(): int
    {
        if (! Schema::hasTable('products')) {
            $this->error('Δεν βρέθηκε ο πίνακας products.');

            return self::FAILURE;
        }

        $this->titleColumn = $this->detectColumn('products', ['title', 'name', 'products_name']);

        if ($this->titleColumn === null) {
            $this->error('Δεν βρέθηκε στήλη τίτλου στο products.');

            return self::FAILURE;
        }

        $this->productsHaveUpdatedAt = Schema::hasColumn('products', 'updated_at');

        $canWriteExtraFields = Schema::hasTable('product_extra_fields') && Schema::hasTable('product_extra_field_values');
        $applyExtraFields = (bool) $this->option('apply-extra-fields') && $canWriteExtraFields;
        $applyTitles = (bool) $this->option('apply-titles');
        $minTitleConfidence = max(0, min(100, (int) $this->option('min-title-confidence')));

        if ((bool) $this->option('apply-extra-fields') && ! $canWriteExtraFields) {
            $this->warn('Δεν υπάρχουν τα product_extra_* tables. Τρέξε πρώτα php artisan migrate.');
        }

        if ($canWriteExtraFields) {
            $this->fieldIds = DB::table('product_extra_fields')
                ->pluck('id', 'name')
                ->mapWithKeys(fn (int $id, string $name): array => [Str::lower($name) => $id])
                ->all();
        }

        $query = DB::table('products')->select(['id', $this->titleColumn]);

        $ids = array_filter(array_map('intval', (array) $this->option('product-id')));
        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        if ($this->option('limit')) {
            $query->limit(max(1, (int) $this->option('limit')));
        }

        $scanned = 0;
        $extraValuesWritten = 0;
        $titlesUpdated = 0;
        $titlePreviews = [];

        $query->orderBy('id')->chunkById(500, function ($products) use (
            $applyExtraFields,
            $applyTitles,
            $minTitleConfidence,
            &$scanned,
            &$extraValuesWritten,
            &$titlesUpdated,
            &$titlePreviews,
        ): void {
            foreach ($products as $product) {
                $scanned++;

                $title = (string) ($product->{$this->titleColumn} ?? '');
                $features = $this->extractFeatures($title);

                if ($applyExtraFields) {
                    $extraValuesWritten += $this->writeExtraFieldValues((int) $product->id, $features);
                }

                $candidate = $this->buildCanonicalTitle($title, $features);
                if ($candidate !== null && $candidate['confidence'] >= $minTitleConfidence) {
                    if ($candidate['title'] !== $title) {
                        if ($applyTitles) {
                            $updates = [
                                $this->titleColumn => $candidate['title'],
                            ];

                            if ($this->productsHaveUpdatedAt) {
                                $updates['updated_at'] = now();
                            }

                            DB::table('products')
                                ->where('id', (int) $product->id)
                                ->update($updates);

                            $titlesUpdated++;
                        } elseif (count($titlePreviews) < 20) {
                            $titlePreviews[] = [
                                'id' => (int) $product->id,
                                'confidence' => $candidate['confidence'],
                                'old' => $title,
                                'new' => $candidate['title'],
                            ];
                        }
                    }
                }
            }
        }, 'id');

        $this->info("Σαρώθηκαν {$scanned} προϊόντα.");
        $this->info($applyExtraFields ? "Γράφτηκαν/ενημερώθηκαν {$extraValuesWritten} extra field values." : 'Extra fields: preview mode, δεν γράφτηκε τίποτα.');
        $this->info($applyTitles ? "Ενημερώθηκαν {$titlesUpdated} τίτλοι." : 'Τίτλοι: preview mode, δεν γράφτηκε τίποτα.');

        foreach ($titlePreviews as $preview) {
            $this->line('');
            $this->line("#{$preview['id']} confidence {$preview['confidence']}%");
            $this->line('Πριν: ' . $preview['old']);
            $this->line('Μετά: ' . $preview['new']);
        }

        return self::SUCCESS;
    }

    private function extractFeatures(string $title): array
    {
        $text = $this->normaliseText($title);
        $features = [];

        if (preg_match('/\b(AMD\s+RYZEN\s+[3579]\s+\d{4}[A-Z]{0,2}|RYZEN\s+[3579]\s+\d{4}[A-Z]{0,2})\b/i', $text, $match)) {
            $features['CPU'] = $this->titleCaseCpu($match[1]);
        } elseif (preg_match('/\b(INTEL\s+)?(CORE\s+)?I([3579])[-\s]?(\d{3,5}[A-Z]{0,2})\b/i', $text, $match)) {
            $features['CPU'] = 'Intel Core i' . $match[3] . '-' . strtoupper($match[4]);
        }

        if (preg_match('/\b(NVIDIA\s+(?:GEFORCE|RTX|GTX)[A-Z0-9\s-]+|AMD\s+RADEON\s+[A-Z0-9\s]+|RADEON\s+[A-Z0-9\s]+|INTEL\s+(?:IRIS\s+XE|UHD)\s+GRAPHICS)\b/i', $text, $match)) {
            $features['GPU'] = $this->cleanValue($this->titleCaseKnown($match[1]));
        }

        if (preg_match('/\b(DDR[345])\b/i', $text, $match)) {
            $features['RAM Type'] = strtoupper($match[1]);
        }

        if (preg_match('/\b(\d{1,3})\s*GB(?:\s*\(\s*\d+\s*x\s*\d+\s*GB\s*\))?(?:\s*(?:DDR[345]))?\s*(?:RAM|MEMORY)?\b/i', $text, $match)) {
            $ram = (int) $match[1];
            if ($ram > 0 && $ram <= 128 && ! preg_match('/' . preg_quote($match[0], '/') . '\s*(?:SSD|HDD|STORAGE|NVME|PCIE)/i', $text)) {
                $features['RAM'] = $ram . 'GB';
            }
        }

        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*(TB|GB)\s*(?:(PCIE)\s*)?(?:(NVME)\s*)?(SSD|HDD|EMMC|STORAGE)\b/i', $text, $match)) {
            $features['Disk Size'] = str_replace(',', '.', $match[1]) . strtoupper($match[2]);
            $diskType = trim(implode(' ', array_filter([$match[3] ?? null, $match[4] ?? null, $match[5] ?? null])));
            $features['Disk Type'] = $this->normaliseStorageType($diskType);
        } elseif (preg_match('/\b(256|512|1024|2048)\s*GB\b/i', $text, $match)) {
            $features['Disk Size'] = $match[1] . 'GB';
        }

        if (preg_match('/\b(\d{2}(?:[.,]\d)?)\s*(?:"|INCH|INCHES|IN\.)\b/i', $text, $match)) {
            $features['Screen Size'] = str_replace(',', '.', $match[1]) . '"';
        } elseif (preg_match('/\b(\d{2}(?:[.,]\d)?)\s+(?:FHD|FULL\s+HD|QHD|UHD|IPS|TN|VA|OLED)\b/i', $text, $match)) {
            $features['Screen Size'] = str_replace(',', '.', $match[1]) . '"';
        }

        if (preg_match('/\b(FULL\s+HD|FHD|QHD|UHD|4K|HD)\b/i', $text, $match)) {
            $features['Resolution'] = match (strtoupper(str_replace(' ', '', $match[1]))) {
                'FHD', 'FULLHD' => 'Full HD',
                'UHD', '4K' => '4K UHD',
                default => strtoupper($match[1]),
            };
        }

        if (preg_match('/\b(IPS|TN|VA|OLED|MINI\s*LED)\b/i', $text, $match)) {
            $features['Panel Type'] = strtoupper(str_replace(' ', ' ', $match[1]));
        }

        if (preg_match('/\b(\d{2,3})\s*HZ\b/i', $text, $match)) {
            $features['Refresh Rate'] = $match[1] . 'Hz';
        }

        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*MS\b/i', $text, $match)) {
            $features['Response Time'] = str_replace(',', '.', $match[1]) . 'ms';
        }

        if (preg_match('/\b(FREEDOS(?:\s*3\.0)?|DOS|WINDOWS\s+11\s+(?:PRO|HOME)|WINDOWS\s+10\s+(?:PRO|HOME)|NO\s+OS|LINUX)\b/i', $text, $match)) {
            $features['OS System'] = $this->normaliseOs($match[1]);
        }

        foreach (['USB-C', 'USB 3.2', 'USB 3.1', 'USB 3.0', 'HDMI', 'DISPLAYPORT', 'THUNDERBOLT'] as $interface) {
            if (Str::contains(Str::upper($text), Str::upper($interface))) {
                $features['Interface / Connector'] = $this->appendListValue($features['Interface / Connector'] ?? null, $this->titleCaseKnown($interface));
            }
        }

        if ($this->looksLikeCableTitle($text) && preg_match_all('/(?<![\d.])(\d+(?:[.,]\d+)?)\s*(M|METERS?|METRES?|CM|FT|FEET)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $unit = strtoupper($match[2]);
                $number = str_replace(',', '.', $match[1]);

                $features['Cable Length'] = match (true) {
                    in_array($unit, ['M', 'METER', 'METERS', 'METRE', 'METRES'], true) => $number . ' m',
                    $unit === 'CM' => $number . ' cm',
                    in_array($unit, ['FT', 'FEET'], true) => $number . ' ft',
                    default => null,
                };

                if ($features['Cable Length']) {
                    break;
                }
            }
        }

        if (preg_match('/\b(WI-?FI\s*(?:4|5|6E?|7|802\.11[A-Z]+))\b/i', $text, $match)) {
            $features['Wi-Fi Standard'] = strtoupper(str_replace(' ', ' ', $match[1]));
        }

        if (preg_match('/\bBLUETOOTH\s*(\d+(?:\.\d+)?)?\b/i', $text, $match)) {
            $features['Bluetooth'] = isset($match[1]) && $match[1] !== '' ? 'Bluetooth ' . $match[1] : 'Bluetooth';
        }

        if (preg_match('/\b(\d{2,4})\s*W\b/i', $text, $match)) {
            $features['Wattage'] = $match[1] . 'W';
        }

        if (preg_match('/\b(\d+)\s*(?:PORT|PORTS)\b/i', $text, $match)) {
            $features['Number of Ports'] = $match[1];
        }

        if (preg_match('/\bPOE\+?\b/i', $text, $match)) {
            $features['PoE'] = strtoupper($match[0]);
        }

        return array_filter($features, fn (?string $value): bool => $value !== null && trim($value) !== '');
    }

    private function looksLikeCableTitle(string $text): bool
    {
        return preg_match('/\b(CABLE|CORD|LEAD|PATCH|TOSLINK|HDMI|DISPLAYPORT|USB|DVI|VGA|RCA|CAT\s*[5678]|ETHERNET|EXTENSION)\b/i', $text) === 1;
    }

    private function buildCanonicalTitle(string $originalTitle, array $features): ?array
    {
        $text = $this->normaliseText($originalTitle);
        $brand = $this->detectBrand($text);
        $model = $this->detectNotebookModel($text, $brand);
        $isNotebook = preg_match('/\b(NOTEBOOK|LAPTOP|HP\s+\d{3}[A-Z]?\s+G\d{1,2})\b/i', $text) === 1;

        if (! $brand || ! $model || ! $isNotebook) {
            return null;
        }

        $parts = [$brand . ' ' . $model . ' Notebook PC'];
        $confidence = 50;

        if (isset($features['Screen Size'])) {
            $parts[0] .= ' ' . $features['Screen Size'];
            $confidence += 8;
        }

        foreach (['CPU', 'RAM', 'Disk Size', 'OS System'] as $key) {
            if (! isset($features[$key])) {
                continue;
            }

            $value = $features[$key];
            if ($key === 'RAM') {
                $value .= isset($features['RAM Type']) ? ' ' . $features['RAM Type'] . ' RAM' : ' RAM';
                $confidence += 12;
            } elseif ($key === 'Disk Size') {
                $value .= ' Storage';
                $confidence += 10;
            } elseif ($key === 'CPU') {
                $confidence += 15;
            } elseif ($key === 'OS System') {
                $confidence += 5;
            }

            $parts[] = $value;
        }

        return [
            'title' => implode(', ', $parts),
            'confidence' => min(100, $confidence),
        ];
    }

    private function writeExtraFieldValues(int $productId, array $features): int
    {
        $written = 0;
        $onlyMissing = (bool) $this->option('only-missing-extra-fields');

        foreach ($features as $fieldName => $value) {
            $fieldId = $this->fieldIds[Str::lower($fieldName)] ?? null;

            if (! $fieldId) {
                continue;
            }

            $existing = DB::table('product_extra_field_values')
                ->where('product_id', $productId)
                ->where('extra_field_id', $fieldId)
                ->first();

            if ($existing && $onlyMissing) {
                continue;
            }

            if ($existing) {
                DB::table('product_extra_field_values')
                    ->where('id', $existing->id)
                    ->update([
                        'value' => $value,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('product_extra_field_values')->insert([
                    'product_id' => $productId,
                    'extra_field_id' => $fieldId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $written++;
        }

        return $written;
    }

    private function normaliseText(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\x{00A0}\t\r\n]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s*[,;]\s*/u', ', ', $value) ?? $value;
        $value = preg_replace('/\s+\/\s+/u', ' / ', $value) ?? $value;
        $value = preg_replace('/\s{2,}/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function detectColumn(string $table, array $candidates): ?string
    {
        $columns = Schema::getColumnListing($table);

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function detectBrand(string $text): ?string
    {
        foreach (['HP', 'LENOVO', 'DELL', 'APPLE', 'ASUS', 'ACER', 'MSI', 'TOSHIBA', 'FUJITSU'] as $brand) {
            if (preg_match('/\b' . preg_quote($brand, '/') . '\b/i', $text)) {
                return $this->titleCaseKnown($brand);
            }
        }

        return null;
    }

    private function detectNotebookModel(string $text, ?string $brand): ?string
    {
        if (preg_match('/\b(255R?\s+G\d{1,2}|250\s+G\d{1,2}|240\s+G\d{1,2}|450\s+G\d{1,2}|470\s+G\d{1,2})\b/i', $text, $match)) {
            return strtoupper(preg_replace('/\s+/', ' ', $match[1]) ?? $match[1]);
        }

        if ($brand && preg_match('/\b' . preg_quote($brand, '/') . '\s+([A-Z0-9][A-Z0-9 -]{2,30})\s+(?:NOTEBOOK|LAPTOP|PC)\b/i', $text, $match)) {
            return trim($this->titleCaseKnown($match[1]));
        }

        return null;
    }

    private function titleCaseCpu(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? $value;
        $value = preg_replace('/^RYZEN/i', 'AMD Ryzen', $value) ?? $value;
        $value = preg_replace('/^AMD\s+RYZEN/i', 'AMD Ryzen', $value) ?? $value;

        return preg_replace_callback('/\b([A-Z]{2,}|[A-Z]?\d+[A-Z]*)\b/i', fn (array $match): string => strtoupper($match[1]), ucwords(strtolower($value))) ?? $value;
    }

    private function titleCaseKnown(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
        $upperMap = ['HP', 'AMD', 'CPU', 'GPU', 'RAM', 'SSD', 'HDD', 'NVME', 'PCIE', 'USB', 'HDMI', 'VESA', 'MSI', 'ASUS', 'DOS', 'OLED', 'IPS', 'TN', 'VA'];

        $value = ucwords(strtolower($value));

        foreach ($upperMap as $token) {
            $value = preg_replace('/\b' . preg_quote(ucwords(strtolower($token)), '/') . '\b/', $token, $value) ?? $value;
        }

        $value = str_replace(['Usb-C', 'Pcie', 'Nvme', 'Freedos'], ['USB-C', 'PCIe', 'NVMe', 'FreeDOS'], $value);

        return $value;
    }

    private function cleanValue(string $value): string
    {
        $value = preg_replace('/\s*,.*$/', '', $value) ?? $value;
        $value = preg_replace('/\s{2,}/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function normaliseStorageType(string $value): string
    {
        $value = strtoupper(trim($value));

        return str_replace(['PCIE', 'NVME'], ['PCIe', 'NVMe'], $value);
    }

    private function normaliseOs(string $value): string
    {
        $value = strtoupper(trim($value));

        return match (true) {
            str_contains($value, 'WINDOWS 11 PRO') => 'Windows 11 Pro',
            str_contains($value, 'WINDOWS 11 HOME') => 'Windows 11 Home',
            str_contains($value, 'WINDOWS 10 PRO') => 'Windows 10 Pro',
            str_contains($value, 'WINDOWS 10 HOME') => 'Windows 10 Home',
            str_contains($value, 'NO OS') => 'No OS',
            str_contains($value, 'LINUX') => 'Linux',
            default => 'FreeDOS 3.0',
        };
    }

    private function appendListValue(?string $existing, string $value): string
    {
        $parts = $existing ? array_map('trim', explode(', ', $existing)) : [];
        $parts[] = $value;
        $parts = array_values(array_unique(array_filter($parts)));

        return implode(', ', $parts);
    }
}
