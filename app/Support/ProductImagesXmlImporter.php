<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;

class ProductImagesXmlImporter
{
    public function __construct(protected ?LocalMediaStore $mediaStore = null)
    {
        $this->mediaStore ??= new LocalMediaStore();
    }

    public function import(string $path): array
    {
        if (! Schema::hasTable('product_images')) {
            throw new \RuntimeException('The product_images table is missing. Run migrations first.');
        }

        if (! is_file($path)) {
            throw new \InvalidArgumentException('XML file was not found.');
        }

        $xml = simplexml_load_file($path);

        if (! $xml instanceof SimpleXMLElement) {
            throw new \RuntimeException('The XML file could not be parsed.');
        }

        $stats = [
            'items' => 0,
            'matched' => 0,
            'unmatched' => 0,
            'images' => 0,
        ];

        foreach ($xml->xpath('//item') ?: [] as $item) {
            $stats['items']++;

            $sku = trim((string) ($item->sku ?? ''));
            $ean = trim((string) ($item->ean ?? ''));
            $mpn = trim((string) ($item->mpn ?? ''));
            $urls = $this->imageUrls($item);

            if ($urls === []) {
                continue;
            }

            $product = $this->findProduct($sku, $ean, $mpn);

            if (! $product) {
                $stats['unmatched']++;
                continue;
            }

            $stats['matched']++;
            $images = $this->localizeImages($urls, (int) $product->id);
            $stats['images'] += $this->replaceImages((int) $product->id, $images, 'xml');
            $this->syncProductPrimaryImage((int) $product->id, $images[0]['path'] ?? $images[0]['url'] ?? '');
        }

        return $stats;
    }

    protected function findProduct(string $sku, string $ean, string $mpn): ?object
    {
        if ($sku === '' && $ean === '' && $mpn === '') {
            return null;
        }

        $query = DB::table('products');

        if (Schema::hasColumn('products', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->where(function ($builder) use ($sku, $ean, $mpn): void {
            foreach ([
                'code' => $sku,
                'ean' => $ean,
                'mpn' => $mpn,
            ] as $column => $value) {
                if ($value !== '' && Schema::hasColumn('products', $column)) {
                    $builder->orWhere($column, $value);
                }
            }
        });

        return $query->orderByDesc('id')->first();
    }

    protected function replaceImages(int $productId, array $images, string $source): int
    {
        DB::table('product_images')
            ->where('product_id', $productId)
            ->where('source', $source)
            ->delete();

        $now = now();
        $rows = [];

        foreach (array_values($images) as $index => $image) {
            $rows[] = [
                'product_id' => $productId,
                'url' => $image['url'] ?? null,
                'path' => $image['path'] ?? null,
                'source' => $source,
                'sort_order' => ($index + 1) * 10,
                'is_primary' => $index === 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('product_images')->insert($rows);
        }

        return count($rows);
    }

    protected function syncProductPrimaryImage(int $productId, string $path): void
    {
        if ($path === '') {
            return;
        }

        $data = [];

        if (Schema::hasColumn('products', 'image_path')) {
            $data['image_path'] = $path;
        }

        if (Schema::hasColumn('products', 'main_image')) {
            $data['main_image'] = $path;
        }

        if ($data === []) {
            return;
        }

        if (Schema::hasColumn('products', 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table('products')->where('id', $productId)->update($data);
    }

    protected function localizeImages(array $urls, int $productId): array
    {
        return collect($urls)
            ->map(function (string $url) use ($productId): array {
                $path = $this->mediaStore->localize($url, 'product-images/' . $productId);

                return [
                    'url' => $this->mediaStore->isRemote($url) ? $url : null,
                    'path' => $this->mediaStore->isRemote($path) ? null : $path,
                ];
            })
            ->filter(fn (array $image): bool => ($image['path'] ?? $image['url'] ?? '') !== '')
            ->unique(fn (array $image): string => (string) ($image['path'] ?? $image['url']))
            ->values()
            ->all();
    }

    protected function imageUrls(SimpleXMLElement $item): array
    {
        $values = [];

        foreach (['imageLinks', 'images', 'image', 'image_url'] as $field) {
            if (isset($item->{$field})) {
                $values[] = (string) $item->{$field};

                foreach ($item->{$field}->children() as $child) {
                    $values[] = (string) $child;
                }
            }
        }

        $urls = [];

        foreach ($values as $value) {
            if (preg_match_all('~https?://[^\s|;]+~', $value, $matches)) {
                foreach ($matches[0] as $url) {
                    $urls[] = trim($url);
                }

                continue;
            }

            foreach (preg_split('/[\r\n|;]+/', $value) ?: [] as $part) {
                $url = trim($part);

                if ($url !== '' && Str::startsWith($url, ['http://', 'https://', '/'])) {
                    $urls[] = $url;
                }
            }
        }

        return array_values(array_unique($urls));
    }
}
