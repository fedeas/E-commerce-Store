<?php

namespace App\Console\Commands;

use App\Support\ProductImagesXmlImporter;
use Illuminate\Console\Command;

class ImportProductImagesFromXml extends Command
{
    protected $signature = 'catalog:import-product-images-xml {path}';

    protected $description = 'Import product image galleries from an XML file with sku/ean/imageLinks nodes.';

    public function handle(ProductImagesXmlImporter $importer): int
    {
        $stats = $importer->import((string) $this->argument('path'));

        $this->info(sprintf(
            'Items: %d, matched: %d, unmatched: %d, images imported: %d',
            $stats['items'],
            $stats['matched'],
            $stats['unmatched'],
            $stats['images'],
        ));

        return self::SUCCESS;
    }
}
