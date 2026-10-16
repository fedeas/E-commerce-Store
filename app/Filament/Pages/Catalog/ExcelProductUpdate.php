<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;
use UnitEnum;
use XMLReader;
use ZipArchive;

class ExcelProductUpdate extends Page
{
    use WithFileUploads;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';

    protected static ?string $navigationLabel = 'Ενημέρωση από Excel';

    protected static ?string $title = 'Ενημέρωση ειδών από Excel';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?int $navigationSort = 18;

    protected string $view = 'filament.pages.catalog.excel-product-update';

    public $excelFile = null;

    public string $startRow = '1';

    public string $limitRows = '0';

    public ?string $lastStoredPath = null;

    public ?string $lastFileName = null;

    public ?string $lastFileSize = null;

    public ?string $lastRunMode = null;

    public ?string $lastRunAt = null;

    public ?string $lastStatus = null;

    public ?string $commandOutput = null;

    public array $workbookInfo = [];

    public array $detectedColumns = [];

    public array $importStats = [];

    protected function getViewData(): array
    {
        return [
            'databaseSummary' => $this->databaseSummary(),
            'updateGroups' => $this->updateGroups(),
            'requiredColumns' => $this->requiredColumns(),
        ];
    }

    public function inspectUpload(): void
    {
        try {
            $storedPath = $this->storeUploadIfNeeded();
        } catch (Throwable $exception) {
            $this->lastStatus = 'Αποτυχία αποθήκευσης αρχείου.';
            $this->commandOutput = $exception->getMessage();

            Notification::make()
                ->title('Δεν μπόρεσα να αποθηκεύσω το Excel.')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        if (! $storedPath) {
            Notification::make()
                ->title('Διάλεξε πρώτα αρχείο Excel.')
                ->warning()
                ->send();

            return;
        }

        $path = Storage::disk('local')->path($storedPath);

        try {
            $this->workbookInfo = $this->inspectWorkbook($path);
            $this->detectedColumns = $this->detectColumns($this->workbookInfo['headers'] ?? []);
            $this->lastStatus = 'Το αρχείο διαβάστηκε και είναι έτοιμο για έλεγχο.';
            $this->lastFileSize = $this->formatOptionalBytes($this->fileSizeFromPath($path));

            Notification::make()
                ->title('Το Excel διαβάστηκε.')
                ->body(($this->workbookInfo['data_rows'] ?? 0) . ' γραμμές δεδομένων, ' . ($this->workbookInfo['columns'] ?? 0) . ' στήλες.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            $this->lastStatus = 'Αποτυχία ανάγνωσης αρχείου.';
            $this->commandOutput = $exception->getMessage();

            Notification::make()
                ->title('Δεν μπόρεσα να διαβάσω το Excel.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
    public function mount(): void
    {
        $this->loadLatestImportState();
    }



    public function runDryRun(): void
    {
        $this->runImport(true);
    }

    public function runDatabaseUpdate(): void
    {
        $this->runImport(false);
    }

    public function clearOutput(): void
    {
        $this->commandOutput = null;
        $this->importStats = [];
        $this->lastStatus = null;
    }

    protected function runImport(bool $dryRun): void
    {
        try {
            $this->extendImportRuntime();

            $path = $this->storeUploadIfNeeded();

            if (! $path) {
                Notification::make()
                    ->title('Διάλεξε πρώτα αρχείο Excel.')
                    ->warning()
                    ->send();

                return;
            }

            $absolutePath = Storage::disk('local')->path($path);

            $this->workbookInfo = $this->inspectWorkbook($absolutePath);
            $this->detectedColumns = $this->detectColumns($this->workbookInfo['headers'] ?? []);

            $arguments = [
                'file' => $absolutePath,
                '--start' => $this->normalizedStartRow(),
                '--limit' => $this->normalizedLimitRows(),
            ];

            if ($dryRun) {
                $arguments['--dry-run'] = true;
            }

            $background = $this->runImportInBackground($arguments, $dryRun);

            $this->importStats = [
                'status' => 'running',
                'mode' => $dryRun ? 'dry-run' : 'import',
                'pid' => $background['pid'],
                'log_file' => $background['log_file'],
                'log_path' => $background['log_path'],
            ];

            if (! empty($background['error'])) {
                $this->lastStatus = 'failed';

                $this->persistLatestImportState([
                    'status' => 'failed',
                    'mode' => $dryRun ? 'dry-run' : 'import',
                    'pid' => $background['pid'] ?? '-',
                    'log_file' => $background['log_file'] ?? '',
                    'log_path' => $background['log_path'] ?? '',
                    'file' => $background['file'] ?? '',
                    'started_at' => now()->format('Y-m-d H:i:s'),
                    'error' => $background['error'],
                ]);

                $this->commandOutput = $background['error'] . "\nLog: storage/logs/{$background['log_file']}";
                return;
            }

            $this->lastStatus = 'running';
            $this->lastRunMode = $dryRun ? 'dry-run' : 'import';
            $this->lastRunAt = now()->format('Y-m-d H:i:s');

            $this->persistLatestImportState([
                'status' => 'running',
                'mode' => $dryRun ? 'dry-run' : 'import',
                'pid' => $background['pid'] ?? '-',
                'log_file' => $background['log_file'] ?? '',
                'log_path' => $background['log_path'] ?? '',
                'file' => $background['file'] ?? ($arguments['file'] ?? 'storage/app/imports/products_export_all.xlsx'),
                'suppress_log_restore' => false,
                'started_at' => $this->lastRunAt ?: now()->format('Y-m-d H:i:s'),
            ]);

            $this->commandOutput = "Το import ξεκίνησε στο background.\nPID: {$background['pid']}\nLog: storage/logs/{$background['log_file']}\n\nΜπορείς να συνεχίσεις να δουλεύεις και να κάνεις refresh αργότερα.";

            return;
            $output = $this->cleanConsoleOutput(Artisan::output());

            $this->lastRunMode = $dryRun ? 'Έλεγχος χωρίς αλλαγές' : 'Πραγματική ενημέρωση βάσης';
            $this->lastRunAt = now()->format('d/m/Y H:i:s');
            $this->commandOutput = trim($output);
            $this->importStats = $this->parseImportStats($output);
            $this->lastStatus = $exitCode === 0
                ? ($dryRun ? 'Ο έλεγχος ολοκληρώθηκε χωρίς αλλαγές στη βάση.' : 'Η ενημέρωση βάσης ολοκληρώθηκε.')
                : 'Η διαδικασία επέστρεψε σφάλμα.';

            Notification::make()
                ->title($exitCode === 0 ? 'Η διαδικασία ολοκληρώθηκε.' : 'Η διαδικασία απέτυχε.')
                ->body($this->lastStatus)
                ->{$exitCode === 0 ? 'success' : 'danger'}()
                ->send();
        } catch (Throwable $exception) {
            $this->lastStatus = 'Αποτυχία διαδικασίας.';
            $this->commandOutput = $exception->getMessage();

            Notification::make()
                ->title('Αποτυχία ενημέρωσης από Excel.')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function storeUploadIfNeeded(): ?string
    {
        if ($this->excelFile instanceof TemporaryUploadedFile) {
            $originalName = $this->excelFile->getClientOriginalName();

            if (mb_strtolower(pathinfo($originalName, PATHINFO_EXTENSION), 'UTF-8') !== 'xlsx') {
                throw new \RuntimeException('Το αρχείο πρέπει να είναι .xlsx.');
            }

            $name = pathinfo($originalName, PATHINFO_FILENAME);
            $safeName = Str::slug($name) ?: 'products-import';
            $filename = now()->format('Ymd_His') . '_' . $safeName . '.xlsx';

            Storage::disk('local')->makeDirectory('imports');

            $path = 'imports/' . $filename;
            $destination = Storage::disk('local')->path($path);
            $source = $this->excelFile->getRealPath();

            if (! is_string($source) || ! is_file($source)) {
                $storedPath = $this->excelFile->storeAs('imports', $filename, 'local');

                if (! is_string($storedPath) || ! Storage::disk('local')->exists($storedPath)) {
                    throw new \RuntimeException('Δεν βρέθηκε το προσωρινό αρχείο upload για να αποθηκευτεί.');
                }

                $path = $storedPath;
            } else {
                if (! is_dir(dirname($destination))) {
                    mkdir(dirname($destination), 0775, true);
                }

                if (! copy($source, $destination)) {
                    throw new \RuntimeException('Δεν μπόρεσα να αντιγράψω το Excel στα imports.');
                }
            }

            $this->lastStoredPath = $path;
            $this->lastFileName = $originalName;
            $this->lastFileSize = $this->formatOptionalBytes($this->fileSizeFromStorage($path));

            return $path;
        }

        if ($this->lastStoredPath && Storage::disk('local')->exists($this->lastStoredPath)) {
            return $this->lastStoredPath;
        }

        return null;
    }

    protected function currentUploadPath(): ?string
    {
        if ($this->excelFile instanceof TemporaryUploadedFile) {
            return $this->excelFile->getRealPath();
        }

        if ($this->lastStoredPath && Storage::disk('local')->exists($this->lastStoredPath)) {
            return Storage::disk('local')->path($this->lastStoredPath);
        }

        return null;
    }

    protected function inspectWorkbook(string $path): array
    {
        if (! class_exists(XMLReader::class)) {
            throw new \RuntimeException('Το PHP περιβάλλον χρειάζεται XMLReader για XLSX αρχεία.');
        }

        if (! class_exists(ZipArchive::class) && ! $this->unzipAvailable()) {
            throw new \RuntimeException('Το PHP περιβάλλον χρειάζεται ZipArchive ή διαθέσιμο command-line unzip για XLSX αρχεία.');
        }

        if (! is_file($path)) {
            throw new \RuntimeException('Δεν βρέθηκε το αρχείο Excel.');
        }

        $sheetPath = $this->firstWorksheetPath($path);
        $sharedStrings = $this->sharedStrings($path);
        $sheetXml = $this->readZipEntry($path, $sheetPath);

        $reader = new XMLReader();
        $reader->XML($sheetXml);

        $headers = [];
        $sampleRows = [];
        $dataRows = 0;
        $rowNumber = 0;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                continue;
            }

            $row = $this->parseRow($reader->readOuterXML(), $sharedStrings);

            if ($row === []) {
                continue;
            }

            $rowNumber++;
            $values = $this->rowValues($row);

            if ($rowNumber === 1) {
                $headers = array_values(array_map(fn ($value): string => trim((string) $value), $values));

                continue;
            }

            $dataRows++;

            if (count($sampleRows) < 5) {
                $sampleRows[] = array_slice(array_pad($values, count($headers), ''), 0, count($headers));
            }
        }

        $reader->close();

        return [
            'sheet' => basename($sheetPath),
            'rows' => $dataRows + 1,
            'data_rows' => $dataRows,
            'columns' => count($headers),
            'headers' => $headers,
            'sample_rows' => $sampleRows,
        ];
    }

    protected function firstWorksheetPath(string $path): string
    {
        $sheets = [];

        foreach ($this->zipEntries($path) as $name) {
            if (preg_match('/^xl\/worksheets\/sheet\d+\.xml$/', $name)) {
                $sheets[] = $name;
            }
        }

        natsort($sheets);

        $first = array_values($sheets)[0] ?? null;

        if (! $first) {
            throw new \RuntimeException('Δεν βρέθηκε worksheet μέσα στο XLSX.');
        }

        return $first;
    }

    protected function sharedStrings(string $path): array
    {
        $xml = $this->readZipEntry($path, 'xl/sharedStrings.xml', false);

        if ($xml === null) {
            return [];
        }

        $reader = new XMLReader();
        $reader->XML($xml);

        $strings = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'si') {
                continue;
            }

            $node = simplexml_load_string($reader->readOuterXML());

            if (! $node) {
                $strings[] = '';

                continue;
            }

            $texts = $node->xpath('.//*[local-name()="t"]') ?: [];
            $parts = [];

            foreach ($texts as $text) {
                $parts[] = (string) $text;
            }

            $strings[] = implode('', $parts);
        }

        $reader->close();

        return $strings;
    }

    protected function zipEntries(string $path): array
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($path) !== true) {
                throw new \RuntimeException('Το αρχείο δεν ανοίγει ως XLSX.');
            }

            $entries = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if (is_string($name)) {
                    $entries[] = $name;
                }
            }

            $zip->close();

            return $entries;
        }

        $output = shell_exec('unzip -Z -1 ' . escapeshellarg($path) . ' 2>/dev/null');

        if (! is_string($output) || trim($output) === '') {
            throw new \RuntimeException('Δεν μπόρεσα να διαβάσω τα περιεχόμενα του XLSX με unzip.');
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R/', $output) ?: [])));
    }

    protected function readZipEntry(string $path, string $entry, bool $required = true): ?string
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($path) !== true) {
                throw new \RuntimeException('Το αρχείο δεν ανοίγει ως XLSX.');
            }

            $contents = $zip->getFromName($entry);
            $zip->close();

            if ($contents !== false) {
                return $contents;
            }

            if (! $required) {
                return null;
            }

            throw new \RuntimeException('Λείπει τμήμα του XLSX: ' . $entry);
        }

        $output = shell_exec('unzip -p ' . escapeshellarg($path) . ' ' . escapeshellarg($entry) . ' 2>/dev/null');

        if (is_string($output) && $output !== '') {
            return $output;
        }

        if (! $required) {
            return null;
        }

        throw new \RuntimeException('Δεν μπόρεσα να διαβάσω τμήμα του XLSX: ' . $entry);
    }

    protected function unzipAvailable(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $path = shell_exec('command -v unzip 2>/dev/null');

        return is_string($path) && trim($path) !== '';
    }

    protected function parseRow(string $rowXml, array $sharedStrings): array
    {
        $xml = simplexml_load_string($rowXml);

        if (! $xml) {
            return [];
        }

        $cells = $xml->xpath('.//*[local-name()="c"]') ?: [];
        $row = [];

        foreach ($cells as $cell) {
            $reference = (string) ($cell['r'] ?? '');

            if (! preg_match('/^([A-Z]+)/', $reference, $matches)) {
                continue;
            }

            $index = $this->columnIndex($matches[1]);
            $type = (string) ($cell['t'] ?? '');
            $value = '';

            if ($type === 'inlineStr') {
                $texts = $cell->xpath('.//*[local-name()="t"]') ?: [];
                $parts = [];

                foreach ($texts as $text) {
                    $parts[] = (string) $text;
                }

                $value = implode('', $parts);
            } else {
                $nodes = $cell->xpath('./*[local-name()="v"]') ?: [];
                $raw = isset($nodes[0]) ? (string) $nodes[0] : '';
                $value = $type === 's' ? ($sharedStrings[(int) $raw] ?? '') : $raw;
            }

            $row[$index] = $value;
        }

        return $row;
    }

    protected function rowValues(array $row): array
    {
        if ($row === []) {
            return [];
        }

        $max = max(array_keys($row));
        $values = [];

        for ($i = 0; $i <= $max; $i++) {
            $values[] = $row[$i] ?? '';
        }

        return $values;
    }

    protected function columnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $number = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $number = ($number * 26) + (ord($letters[$i]) - 64);
        }

        return $number - 1;
    }

    protected function detectColumns(array $headers): array
    {
        $normalizedHeaders = [];

        foreach ($headers as $index => $header) {
            $normalizedHeaders[$this->normalizeHeader((string) $header)] = [
                'index' => $index + 1,
                'header' => (string) $header,
            ];
        }

        return collect($this->requiredColumns())
            ->map(function (array $column) use ($normalizedHeaders): array {
                $match = $this->matchDetectedHeader(
                    $normalizedHeaders,
                    $column['candidates'] ?? [],
                    $column['contains'] ?? [],
                );

                return [
                    'label' => $column['label'],
                    'field' => $column['field'],
                    'required' => $column['required'],
                    'found' => $match !== null,
                    'column' => $match['index'] ?? null,
                    'header' => $match['header'] ?? null,
                ];
            })
            ->all();
    }

    protected function matchDetectedHeader(array $normalizedHeaders, array $exactCandidates, array $containsCandidates): ?array
    {
        foreach ($exactCandidates as $candidate) {
            if (isset($normalizedHeaders[$candidate])) {
                return $normalizedHeaders[$candidate];
            }
        }

        foreach ($containsCandidates as $terms) {
            foreach ($normalizedHeaders as $header => $match) {
                $matched = true;

                foreach ($terms as $term) {
                    if (! str_contains($header, $term)) {
                        $matched = false;
                        break;
                    }
                }

                if ($matched) {
                    return $match;
                }
            }
        }

        return null;
    }

    protected function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, [
            'ά' => 'α',
            'έ' => 'ε',
            'ή' => 'η',
            'ί' => 'ι',
            'ό' => 'ο',
            'ύ' => 'υ',
            'ώ' => 'ω',
            'ϊ' => 'ι',
            'ΐ' => 'ι',
            'ϋ' => 'υ',
            'ΰ' => 'υ',
        ]);

        return preg_replace('/[^a-z0-9α-ω]+/u', '', $value) ?: '';
    }

    protected function requiredColumns(): array
    {
        return [
            ['label' => 'ID', 'field' => 'legacy_id', 'required' => true, 'candidates' => ['id']],
            ['label' => 'Κωδικός', 'field' => 'code', 'required' => false, 'candidates' => ['κωδικος', 'code']],
            ['label' => 'MPN', 'field' => 'mpn', 'required' => false, 'candidates' => ['mpn']],
            ['label' => 'EAN', 'field' => 'ean', 'required' => false, 'candidates' => ['ean']],
            ['label' => 'Τίτλος', 'field' => 'title', 'required' => true, 'candidates' => ['τιτλος', 'title']],
            ['label' => 'Κατασκευαστής', 'field' => 'brand', 'required' => false, 'candidates' => ['κατασκευαστης', 'manufacturer', 'brand']],
            ['label' => 'Προμηθευτής', 'field' => 'supplier', 'required' => false, 'candidates' => ['προμηθευτης', 'supplier']],
            ['label' => 'Κατηγορία', 'field' => 'category', 'required' => false, 'candidates' => ['κατηγορια', 'category']],
            ['label' => 'ΦΠΑ', 'field' => 'vat_rate', 'required' => false, 'candidates' => ['φπα', 'vat']],
            ['label' => 'Τιμή αγοράς', 'field' => 'cost_ex_vat', 'required' => false, 'candidates' => ['τιμηαγοραςχωρισφπα', 'τιμηαγοραςχωριςφπα', 'costexvat'], 'contains' => [['τιμη', 'αγορας'], ['cost']]],
            ['label' => 'Τιμή πώλησης', 'field' => 'retail_inc_vat', 'required' => false, 'candidates' => ['τιμηπωλησηςμεφπα', 'retailincvat'], 'contains' => [['τιμη', 'πωλησης'], ['retail']]],
            ['label' => 'Τιμή έκπτωσης', 'field' => 'discount_inc_vat', 'required' => false, 'candidates' => ['τιμηεκπτωσηςμεφπα', 'discountincvat'], 'contains' => [['εκπτωσης'], ['discount']]],
            ['label' => 'Τιμή Skroutz', 'field' => 'skroutz_inc_vat', 'required' => false, 'candidates' => ['τιμηskroutzμεφπα', 'skroutzincvat'], 'contains' => [['skroutz']]],
            ['label' => 'Χονδρική', 'field' => 'wholesale_inc_vat', 'required' => false, 'candidates' => ['χονδρικη', 'wholesale'], 'contains' => [['χονδρικη'], ['wholesale']]],
            ['label' => 'Silver', 'field' => 'silver_inc_vat', 'required' => false, 'candidates' => ['sliver', 'silver']],
            ['label' => 'Gold', 'field' => 'gold_inc_vat', 'required' => false, 'candidates' => ['gold']],
            ['label' => 'Βάρος', 'field' => 'weight_kg', 'required' => false, 'candidates' => ['βαροςkg', 'weightkg']],
            ['label' => 'Διαστάσεις', 'field' => 'dimensions', 'required' => false, 'candidates' => ['διαστασειςπλxyxπαχεκ', 'dimensions']],
            ['label' => 'Meta desc', 'field' => 'meta_description', 'required' => false, 'candidates' => ['metadesc', 'metadescription']],
            ['label' => 'Meta keywords', 'field' => 'meta_keywords', 'required' => false, 'candidates' => ['metakeywords']],
        ];
    }

    protected function updateGroups(): array
    {
        return [
            [
                'title' => 'Ταυτοποίηση προϊόντος',
                'items' => ['Προτεραιότητα στο ID του Excel ως legacy_id', 'Αν υπάρχει προϊόν, γίνεται update', 'Αν δεν υπάρχει, δημιουργείται νέο προϊόν'],
            ],
            [
                'title' => 'Προϊόν',
                'items' => ['Κωδικός, MPN, EAN, τίτλος', 'Κατασκευαστής, προμηθευτής, κατηγορία', 'ΦΠΑ, βάρος, διαστάσεις, meta στοιχεία', 'Κατάσταση/active με βάση την κατηγορία'],
            ],
            [
                'title' => 'Τιμές',
                'items' => ['Αγορά χωρίς ΦΠΑ', 'Λιανική με ΦΠΑ', 'Έκπτωση με ΦΠΑ', 'Skroutz με ΦΠΑ', 'Χονδρική, Silver, Gold'],
            ],
            [
                'title' => 'Αυτόματες δημιουργίες',
                'items' => ['Νέοι κατασκευαστές', 'Νέοι προμηθευτές', 'Νέο category path όταν λείπει'],
            ],
        ];
    }

    protected function databaseSummary(): array
    {
        return collect(['products', 'product_prices', 'categories', 'brands', 'suppliers'])
            ->map(function (string $table): array {
                try {
                    $exists = Schema::hasTable($table);
                    $count = $exists ? DB::table($table)->count() : null;
                } catch (Throwable) {
                    $exists = null;
                    $count = null;
                }

                return [
                    'table' => $table,
                    'exists' => $exists,
                    'count' => $count,
                ];
            })
            ->all();
    }

    protected function normalizedStartRow(): int
    {
        return max(1, (int) $this->startRow);
    }

    protected function normalizedLimitRows(): int
    {
        return max(0, (int) $this->limitRows);
    }
    public function updatedExcelFile(): void
    {
        $file = $this->resolveImportFileForBackground();

        $this->workbookInfo = [];
        $this->detectedColumns = [];
        $this->commandOutput = '';
        $this->lastStatus = null;
        $this->lastRunMode = null;
        $this->lastRunAt = null;
        $this->importStats = [];

        $this->persistLatestImportState([
            'status' => 'pending_upload',
            'mode' => 'upload',
            'file' => $file,
            'log_file' => '',
            'log_path' => '',
            'pid' => '-',
            'suppress_log_restore' => true,
            'started_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
    protected function resolveImportFileForBackground(): string
    {
        $defaultFile = 'storage/app/imports/products_export_all.xlsx';
        $latestUploadedFile = 'storage/app/imports/products_export_uploaded_latest.xlsx';

        if (! empty($this->excelFile)) {
            $directory = storage_path('app/imports');

            if (! is_dir($directory)) {
                @mkdir($directory, 0775, true);
            }

            foreach (glob(storage_path('app/imports/products_export_uploaded_*.xlsx')) ?: [] as $oldUploadedFile) {
                @unlink($oldUploadedFile);
            }

            $realPath = null;

            if (is_object($this->excelFile) && method_exists($this->excelFile, 'getRealPath')) {
                $realPath = $this->excelFile->getRealPath();
            }

            if ($realPath && file_exists($realPath)) {
                copy($realPath, base_path($latestUploadedFile));
                @chmod(base_path($latestUploadedFile), 0664);

                return $latestUploadedFile;
            }
        }

        $statePath = $this->latestImportStatePath();

        if (file_exists($statePath) && is_readable($statePath)) {
            $state = json_decode((string) file_get_contents($statePath), true);

            if (
                is_array($state)
                && (string) ($state['status'] ?? '') === 'pending_upload'
                && ! empty($state['file'])
                && file_exists(base_path((string) $state['file']))
            ) {
                return (string) $state['file'];
            }
        }

        if (file_exists(base_path($latestUploadedFile))) {
            return $latestUploadedFile;
        }

        return $defaultFile;
    }






    protected function runImportInBackground(array $arguments, bool $dryRun): array
    {
        $arguments['file'] = $this->resolveImportFileForBackground();


        $logFile = 'catalog-products-import-' . date('Ymd-His') . ($dryRun ? '-dry-run' : '') . '.log';
        $logPath = storage_path('logs/' . $logFile);

        $file = $arguments['file'] ?? null;

        if (! $file || ! file_exists(base_path((string) $file))) {
            $defaultFile = 'storage/app/imports/products_export_all.xlsx';

            if (file_exists(base_path($defaultFile))) {
                $file = $defaultFile;
            }
        }

        if (! $file || ! file_exists(base_path((string) $file))) {
            file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] ERROR: Excel file not found.
", FILE_APPEND);

            return [
                'pid' => '-',
                'log_file' => $logFile,
                'log_path' => $logPath,
                'command' => '',
                'file' => (string) ($file ?? ''),
                'error' => 'Δεν βρέθηκε Excel αρχείο. Ανέβασε αρχείο από τη σελίδα ή βάλε το products_export_all.xlsx στο storage/app/imports/products_export_all.xlsx',
            ];
        }

        $commandParts = [
            'php',
            '-d',
            'max_execution_time=0',
            '-d',
            'memory_limit=1024M',
            'artisan',
            'catalog:import-products',
            (string) $file,
        ];

        if (! empty($arguments['--dry-run'])) {
            $commandParts[] = '--dry-run';
        }

        if (! empty($arguments['--limit'])) {
            $commandParts[] = '--limit=' . (string) $arguments['--limit'];
        }

        if (! empty($arguments['--start'])) {
            $commandParts[] = '--start=' . (string) $arguments['--start'];
        }

        $artisanCommand = implode(' ', array_map('escapeshellarg', $commandParts));

        file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] Starting background import...
", FILE_APPEND);
        file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] File: {$file}
", FILE_APPEND);

        $command = 'cd ' . escapeshellarg(base_path())
            . ' && nohup ' . $artisanCommand
            . ' >> ' . escapeshellarg($logPath)
            . ' 2>&1 < /dev/null & echo $!';

        $pid = trim((string) @shell_exec($command));

        file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] Background command started. PID: " . ($pid !== '' ? $pid : '-') . "
", FILE_APPEND);

        return [
            'pid' => $pid !== '' ? $pid : '-',
            'log_file' => $logFile,
            'log_path' => $logPath,
            'command' => $command,
            'file' => (string) $file,
            'error' => null,
        ];
    }
    protected function latestImportStatePath(): string
    {
        return storage_path('app/catalog-products-import-latest.json');
    }

    protected function persistLatestImportState(array $state): void
    {
        $state['updated_at'] = now()->format('Y-m-d H:i:s');

        $path = $this->latestImportStatePath();

        if (! is_dir(dirname($path))) {
            @mkdir(dirname($path), 0775, true);
        }

        @file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function loadLatestImportState(): void
    {
        $state = [];
        $statePath = $this->latestImportStatePath();

        if (file_exists($statePath) && is_readable($statePath)) {
            $decoded = json_decode((string) file_get_contents($statePath), true);

            if (is_array($decoded)) {
                $state = $decoded;
            }
        }

        if ($state === []) {
            $state = $this->buildLatestImportStateFromLatestLog();
        }

        if ($state === []) {
            return;
        }

        $status = (string) ($state['status'] ?? 'running');
        $pid = (string) ($state['pid'] ?? '-');
        $logPath = (string) ($state['log_path'] ?? '');
        $logFile = (string) ($state['log_file'] ?? '');
        $mode = (string) ($state['mode'] ?? '');
        $startedAt = (string) ($state['started_at'] ?? '');

        if ($status === 'running' && $pid !== '-' && $pid !== '' && ! $this->importProcessIsRunning($pid)) {
            $status = $this->importLogLooksFailed($logPath) ? 'failed' : 'finished';
            $state['status'] = $status;
            $state['finished_at'] = now()->format('Y-m-d H:i:s');
            $this->persistLatestImportState($state);
        }

        $this->lastStatus = $status;
        $this->lastRunMode = $mode !== '' ? $mode : null;
        $this->lastRunAt = $startedAt !== '' ? $startedAt : null;

        $this->importStats = [
            'status' => $status,
            'mode' => $mode,
            'pid' => $pid,
            'log_file' => $logFile,
            'log_path' => $logPath,
        ];

        $statusLabel = match ($status) {
            'running' => 'Τρέχει ακόμα',
            'finished' => 'Ολοκληρώθηκε',
            'failed' => 'Απέτυχε',
            default => $status,
        };

        $this->commandOutput =
            "Τελευταίο import μετά από refresh:\n"
            . "Status: {$statusLabel}\n"
            . "Mode: {$mode}\n"
            . "PID: {$pid}\n"
            . "Log: storage/logs/{$logFile}\n"
            . "Started: {$startedAt}\n\n"
            . "Τελευταίες γραμμές log:\n"
            . $this->tailImportLog($logPath, 100);
    }

    protected function latestImportLogPath(): string
    {
        $files = glob(storage_path('logs/catalog-products-import-*.log')) ?: [];

        if ($files === []) {
            return '';
        }

        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return $files[0] ?? '';
    }

    protected function buildLatestImportStateFromLatestLog(): array
    {
        $logPath = $this->latestImportLogPath();

        if ($logPath === '' || ! file_exists($logPath)) {
            return [];
        }

        $logFile = basename($logPath);
        $content = (string) file_get_contents($logPath);

        $pid = '-';
        $file = 'storage/app/imports/products_export_all.xlsx';
        $startedAt = date('Y-m-d H:i:s', filemtime($logPath));

        if (preg_match('/Background command started\. PID:\s*([0-9]+)/', $content, $matches)) {
            $pid = $matches[1];
        }

        if (preg_match('/File:\s*(.+)/', $content, $matches)) {
            $file = trim($matches[1]);
        }

        if (preg_match('/^\[([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})\]/m', $content, $matches)) {
            $startedAt = $matches[1];
        }

        $status = 'running';

        if ($pid !== '-' && ! $this->importProcessIsRunning($pid)) {
            $status = $this->importLogLooksFailed($logPath) ? 'failed' : 'finished';
        }

        $state = [
            'status' => $status,
            'mode' => str_contains($logFile, 'dry-run') ? 'dry-run' : 'import',
            'pid' => $pid,
            'log_file' => $logFile,
            'log_path' => $logPath,
            'file' => $file,
            'started_at' => $startedAt,
        ];

        $this->persistLatestImportState($state);

        return $state;
    }

    protected function importProcessIsRunning(string $pid): bool
    {
        $pid = trim($pid);

        if ($pid === '' || $pid === '-' || ! ctype_digit($pid)) {
            return false;
        }

        $output = trim((string) @shell_exec('ps -p ' . escapeshellarg($pid) . ' -o pid= 2>/dev/null'));

        return $output !== '';
    }

    protected function importLogLooksFailed(string $logPath): bool
    {
        if ($logPath === '' || ! file_exists($logPath)) {
            return false;
        }

        $tail = $this->tailImportLog($logPath, 200);

        return str_contains($tail, 'ERROR')
            || str_contains($tail, 'Exception')
            || str_contains($tail, 'Fatal error')
            || str_contains($tail, 'SQLSTATE')
            || str_contains($tail, 'FAILED')
            || str_contains($tail, 'failed');
    }

    protected function tailImportLog(string $logPath, int $lines = 100): string
    {
        if ($logPath === '' || ! file_exists($logPath)) {
            return 'Δεν βρέθηκε ακόμα log.';
        }

        $lines = max(10, min($lines, 500));

        $output = (string) @shell_exec('tail -n ' . (int) $lines . ' ' . escapeshellarg($logPath) . ' 2>/dev/null');

        return trim($output) !== '' ? $output : 'Το log είναι ακόμα κενό.';
    }







    protected function parseImportStats(string $output): array
    {
        $stats = [];

        foreach (preg_split('/\R/', $output) ?: [] as $line) {
            $line = trim($line);

            if (preg_match('/^(rows seen|rows processed|rows skipped|products created|products updated|prices created|prices updated|brands created|suppliers created|categories created):\s*([0-9]+)/i', $line, $matches)) {
                $stats[strtolower(str_replace(' ', '_', $matches[1]))] = (int) $matches[2];
            }
        }

        return $stats;
    }

    protected function cleanConsoleOutput(string $output): string
    {
        return preg_replace('/\e\[[\d;]*m/', '', $output) ?: $output;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return number_format($bytes / 1048576, 2) . ' MB';
    }

    protected function extendImportRuntime(): void
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
    }

    protected function formatOptionalBytes(?int $bytes): ?string
    {
        return $bytes === null ? null : $this->formatBytes($bytes);
    }

    protected function fileSizeFromPath(string $path): ?int
    {
        try {
            if (is_file($path)) {
                $size = filesize($path);

                return $size === false ? null : (int) $size;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    protected function fileSizeFromStorage(string $path): ?int
    {
        try {
            if (Storage::disk('local')->exists($path)) {
                return (int) Storage::disk('local')->size($path);
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }
}
