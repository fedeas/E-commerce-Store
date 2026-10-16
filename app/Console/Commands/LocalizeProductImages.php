<?php

namespace App\Console\Commands;

use App\Support\LocalMediaStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocalizeProductImages extends Command
{
    protected $signature = 'catalog:localize-product-images {--limit=0 : Maximum product image rows to process}';

    protected $description = 'Download external product/banner image URLs into local storage and update database paths.';

    public function handle(LocalMediaStore $mediaStore): int
    {
        $stats = [
            'gallery_checked' => 0,
            'gallery_localized' => 0,
            'products_checked' => 0,
            'products_localized' => 0,
            'banners_checked' => 0,
            'banners_localized' => 0,
        ];

        if (Schema::hasTable('product_images')) {
            $query = DB::table('product_images')
                ->where(function ($query): void {
                    $query->where(function ($query): void {
                        $query->whereNotNull('url')->where('url', '<>', '');
                    })->orWhere(function ($query): void {
                        $query->whereNotNull('path')->where('path', '<>', '');
                    });
                })
                ->orderBy('id');

            $limit = (int) $this->option('limit');

            if ($limit > 0) {
                $query->limit($limit);
            }

            $query->get()->each(function ($image) use ($mediaStore, &$stats): void {
                $stats['gallery_checked']++;
                $source = (string) ($image->path ?: $image->url ?: '');

                if (! $mediaStore->isRemote($source)) {
                    return;
                }

                $path = $mediaStore->localize($source, 'product-images/' . (int) $image->product_id);

                if ($path === '' || $mediaStore->isRemote($path)) {
                    return;
                }

                DB::table('product_images')->where('id', $image->id)->update([
                    'url' => (string) ($image->url ?: $source),
                    'path' => $path,
                    'updated_at' => now(),
                ]);

                $stats['gallery_localized']++;
            });
        }

        if (Schema::hasTable('products')) {
            DB::table('products')
                ->select('id', 'image_path', 'main_image')
                ->where(function ($query): void {
                    foreach (['image_path', 'main_image'] as $column) {
                        if (Schema::hasColumn('products', $column)) {
                            $query->orWhere($column, 'like', 'http%');
                        }
                    }
                })
                ->orderBy('id')
                ->get()
                ->each(function ($product) use ($mediaStore, &$stats): void {
                    $stats['products_checked']++;
                    $data = [];

                    foreach (['image_path', 'main_image'] as $column) {
                        if (! Schema::hasColumn('products', $column)) {
                            continue;
                        }

                        $source = (string) ($product->{$column} ?? '');

                        if (! $mediaStore->isRemote($source)) {
                            continue;
                        }

                        $path = $mediaStore->localize($source, 'product-images/' . (int) $product->id);

                        if ($path !== '' && ! $mediaStore->isRemote($path)) {
                            $data[$column] = $path;
                        }
                    }

                    if ($data === []) {
                        return;
                    }

                    if (Schema::hasColumn('products', 'updated_at')) {
                        $data['updated_at'] = now();
                    }

                    DB::table('products')->where('id', $product->id)->update($data);
                    $stats['products_localized']++;
                });
        }

        if (Schema::hasTable('home_banners')) {
            DB::table('home_banners')
                ->select('id', 'media_path', 'media_url')
                ->where(function ($query): void {
                    $query->where('media_path', 'like', 'http%')
                        ->orWhere('media_url', 'like', 'http%');
                })
                ->orderBy('id')
                ->get()
                ->each(function ($banner) use ($mediaStore, &$stats): void {
                    $stats['banners_checked']++;
                    $source = (string) ($banner->media_url ?: $banner->media_path ?: '');

                    if (! $mediaStore->isRemote($source)) {
                        return;
                    }

                    $path = $mediaStore->localize($source, 'home-banners');

                    if ($path === '' || $mediaStore->isRemote($path)) {
                        return;
                    }

                    DB::table('home_banners')->where('id', $banner->id)->update([
                        'media_path' => $path,
                        'media_url' => null,
                        'updated_at' => now(),
                    ]);

                    $stats['banners_localized']++;
                });
        }

        $this->info(sprintf(
            'Gallery checked: %d, localized: %d. Products checked: %d, localized: %d. Banners checked: %d, localized: %d.',
            $stats['gallery_checked'],
            $stats['gallery_localized'],
            $stats['products_checked'],
            $stats['products_localized'],
            $stats['banners_checked'],
            $stats['banners_localized'],
        ));

        return self::SUCCESS;
    }
}
