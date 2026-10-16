<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class CatalogProductsExcelExporter
{
    protected array $categoryPathCache = [];

    public function export(string $path, array $filters = []): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $sheetPath = tempnam(sys_get_temp_dir(), 'catalog_products_sheet_');

        if ($sheetPath === false) {
            throw new \RuntimeException('Could not create temporary worksheet file.');
        }

        $handle = fopen($sheetPath, 'wb');

        if (! $handle) {
            throw new \RuntimeException('Could not open temporary worksheet file.');
        }

        fwrite($handle, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
        fwrite($handle, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
        fwrite($handle, '<sheetViews><sheetView workbookViewId="0"/></sheetViews>');
        fwrite($handle, '<sheetFormatPr defaultRowHeight="15"/>');
        fwrite($handle, '<sheetData>');

        $this->writeXlsxRow($handle, 1, $this->headers());

        $rowNumber = 2;
        $query = $this->buildQuery($filters);

        $query->chunk(1000, function ($products) use ($handle, &$rowNumber): void {
            foreach ($products as $product) {
                $this->writeXlsxRow($handle, $rowNumber, [
                    $product->legacy_id ?? $product->id,
                    (string) ($product->code ?? ''),
                    (string) ($product->mpn ?? ''),
                    (string) ($product->ean ?? ''),
                    (string) ($product->title ?? ''),
                    $this->manufacturer($product),
                    $this->supplier($product),
                    $this->category($product),
                    $this->vat($product),
                    $this->value($product, ['cost_ex_vat']),
                    $this->value($product, ['retail_inc_vat']),
                    $this->value($product, ['discount_inc_vat']),
                    $this->value($product, ['skroutz_inc_vat']),
                    $this->value($product, ['wholesale_inc_vat']),
                    $this->value($product, ['silver_inc_vat']),
                    $this->value($product, ['gold_inc_vat']),
                    $this->value($product, ['weight_kg', 'weight']),
                    $this->value($product, ['dimensions']),
                    $this->value($product, ['meta_description']),
                    $this->value($product, ['meta_keywords']),
                    '',
                ]);

                $rowNumber++;
            }
        });

        fwrite($handle, '</sheetData>');
        fwrite($handle, '</worksheet>');
        fclose($handle);

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($sheetPath);
            throw new \RuntimeException('Could not create XLSX export file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFile($sheetPath, 'xl/worksheets/sheet1.xml');
        $zip->addFromString('docProps/core.xml', $this->core());
        $zip->addFromString('docProps/app.xml', $this->app());

        $zip->close();

        @unlink($sheetPath);
    }

    protected function headers(): array
    {
        return [
            'ID',
            'Κωδικός',
            'MPN',
            'EAN',
            'Τίτλος',
            'Κατασκευαστής',
            'Προμηθευτής',
            'Κατηγορία',
            'ΦΠΑ',
            'Τιμή αγοράς (ΧΩΡΙΣ ΦΠΑ)',
            'Τιμή πώλησης (ΜΕ ΦΠΑ)',
            'Τιμή έκπτωσης (ΜΕ ΦΠΑ)',
            'Τιμή Skroutz (ΜΕ ΦΠΑ)',
            'Χονδρική',
            'Sliver',
            'Gold',
            'Βάρος (kg)',
            'Διαστάσεις Πλ x Y x Πάχ(εκ)',
            'Meta desc',
            'Meta keywords',
            'Δ. αρχ. 05-05-2026 09:07',
        ];
    }
    protected function buildQuery(array $filters)
    {
        $query = DB::table('products');
        $supplierKey = $this->supplierForeignKey();

        if (Schema::hasTable('product_prices')) {
            $query->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id');
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id')) {
            $query->leftJoin('brands', 'brands.id', '=', 'products.brand_id');
        }

        if (Schema::hasTable('suppliers') && $supplierKey !== null) {
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'products.' . $supplierKey);
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id')) {
            $query->leftJoin('categories', 'categories.id', '=', 'products.category_id');
        }

        $query->select($this->selectColumns());

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($query) use ($like): void {
                foreach (['title', 'code', 'mpn', 'ean'] as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $query->orWhere('products.' . $column, 'like', $like);
                    }
                }
            });
        }

        $categoryFilter = $filters['categoryFilter'] ?? $filters['selectedCategoryId'] ?? null;

        if (
            ! in_array((string) $categoryFilter, ['all', '', '0'], true)
            && Schema::hasColumn('products', 'category_id')
        ) {
            $categoryId = (int) $categoryFilter;
            $categoryIds = ! empty($filters['includeCategoryDescendants'])
                ? $this->categoryAndDescendantIds($categoryId)
                : [$categoryId];

            $query->whereIn('products.category_id', $categoryIds);
        }

        $brandFilter = $filters['brandFilter'] ?? 'all';

        if (
            ! in_array((string) $brandFilter, ['all', '', '0'], true)
            && Schema::hasColumn('products', 'brand_id')
        ) {
            $query->where('products.brand_id', (int) $brandFilter);
        }

        $supplierFilter = $filters['supplierFilter'] ?? 'all';

        if (
            ! in_array((string) $supplierFilter, ['all', '', '0'], true)
            && $supplierKey !== null
        ) {
            $query->where('products.' . $supplierKey, (int) $supplierFilter);
        }

        $notSupplierFilter = $filters['notSupplierFilter'] ?? 'all';

        if (
            ! in_array((string) $notSupplierFilter, ['all', '', '0'], true)
            && $supplierKey !== null
        ) {
            $query->where('products.' . $supplierKey, '!=', (int) $notSupplierFilter);
        }

        $statusFilter = $filters['statusFilter'] ?? 'all';

        if (! in_array((string) $statusFilter, ['all', ''], true)) {
            if (Schema::hasColumn('products', 'status')) {
                $query->where('products.status', $statusFilter);
            } elseif (Schema::hasColumn('products', 'active')) {
                $query->where('products.active', $statusFilter === 'active' ? 1 : 0);
            }
        }

        $query->orderBy('products.id');

        return $query;
    }

    protected function selectColumns(): array
    {
        $supplierKey = $this->supplierForeignKey();
        $supplierNameColumn = $this->supplierNameColumn();

        $select = [
            'products.id as id',
        ];

        foreach ([
            'legacy_id',
            'code',
            'mpn',
            'ean',
            'title',
            'manufacturer',
            'brand',
            'supplier',
            'category',
            'category_path',
            'vat',
            'vat_rate',
            'weight',
            'weight_kg',
            'dimensions',
            'meta_description',
            'meta_keywords',
            'status',
            'active',
            'category_id',
            'brand_id',
            'supplier_id',
            'primary_supplier_id',
        ] as $column) {
            $select[] = Schema::hasColumn('products', $column)
                ? 'products.' . $column . ' as ' . $column
                : DB::raw('NULL as ' . $column);
        }

        foreach ([
            'cost_ex_vat',
            'retail_inc_vat',
            'discount_inc_vat',
            'skroutz_inc_vat',
            'wholesale_inc_vat',
            'silver_inc_vat',
            'gold_inc_vat',
        ] as $column) {
            $select[] = Schema::hasTable('product_prices') && Schema::hasColumn('product_prices', $column)
                ? 'product_prices.' . $column . ' as ' . $column
                : DB::raw('NULL as ' . $column);
        }

        $select[] = Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id') && Schema::hasColumn('brands', 'name')
            ? DB::raw('brands.name as brand_name')
            : DB::raw('NULL as brand_name');

        $select[] = Schema::hasTable('suppliers') && $supplierKey !== null && $supplierNameColumn !== null
            ? DB::raw('suppliers.' . $supplierNameColumn . ' as supplier_name')
            : DB::raw('NULL as supplier_name');

        $select[] = Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id') && Schema::hasColumn('categories', 'name')
            ? DB::raw('categories.name as category_name')
            : DB::raw('NULL as category_name');

        return $select;
    }

    protected function supplierForeignKey(): ?string
    {
        foreach (['primary_supplier_id', 'supplier_id'] as $column) {
            if (Schema::hasColumn('products', $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function supplierNameColumn(): ?string
    {
        if (! Schema::hasTable('suppliers')) {
            return null;
        }

        foreach (['name', 'title', 'supplier_name', 'company_name'] as $column) {
            if (Schema::hasColumn('suppliers', $column)) {
                return $column;
            }
        }

        return null;
    }



    protected function manufacturer(object $product): string
    {
        foreach (['brand_name', 'manufacturer', 'brand'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function supplier(object $product): string
    {
        foreach (['supplier_name', 'supplier'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function category(object $product): string
    {
        if (property_exists($product, 'category_id') && $product->category_id) {
            return $this->categoryPath((int) $product->category_id, (string) ($product->category_name ?? ''));
        }

        foreach (['category_path', 'category', 'category_name'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function vat(object $product): int|float|string
    {
        foreach (['vat', 'vat_rate'] as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return is_numeric($product->{$column}) ? (float) $product->{$column} : (string) $product->{$column};
            }
        }

        return 19;
    }

    protected function value(object $product, array $columns): int|float|string
    {
        foreach ($columns as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return is_numeric($product->{$column}) ? (float) $product->{$column} : (string) $product->{$column};
            }
        }

        return '';
    }

    protected function categoryPath(int $categoryId, string $fallback = ''): string
    {
        if (isset($this->categoryPathCache[$categoryId])) {
            return $this->categoryPathCache[$categoryId];
        }

        if (! Schema::hasTable('categories')) {
            return $fallback;
        }

        $names = [];
        $currentId = $categoryId;
        $guard = 0;

        while ($currentId && $guard < 50) {
            $category = DB::table('categories')
                ->where('id', $currentId)
                ->first();

            if (! $category) {
                break;
            }

            $name = trim((string) ($category->name ?? ''));

            if ($name !== '') {
                array_unshift($names, $name);
            }

            $currentId = property_exists($category, 'parent_id') ? (int) ($category->parent_id ?? 0) : 0;
            $guard++;
        }

        $path = trim(implode(' > ', $names));

        if ($path === '') {
            $path = $fallback;
        }

        $this->categoryPathCache[$categoryId] = $path;

        return $path;
    }

    protected function categoryAndDescendantIds(int $categoryId): array
    {
        if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'parent_id')) {
            return [$categoryId];
        }

        $ids = [$categoryId];
        $queue = [$categoryId];
        $guard = 0;

        while ($queue !== [] && $guard < 10000) {
            $children = DB::table('categories')
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $children = array_values(array_diff($children, $ids));

            if ($children === []) {
                break;
            }

            $ids = array_merge($ids, $children);
            $queue = $children;
            $guard += count($children);
        }

        return array_values(array_unique($ids));
    }

    protected function writeXlsxRow($handle, int $rowNumber, array $values): void
    {
        fwrite($handle, '<row r="' . $rowNumber . '">');

        $columnIndex = 1;

        foreach ($values as $value) {
            $cell = $this->columnName($columnIndex) . $rowNumber;

            if (is_int($value) || is_float($value)) {
                fwrite($handle, '<c r="' . $cell . '"><v>' . $value . '</v></c>');
            } else {
                $value = (string) ($value ?? '');
                fwrite($handle, '<c r="' . $cell . '" t="inlineStr"><is><t xml:space="preserve">' . $this->xml($value) . '</t></is></c>');
            }

            $columnIndex++;
        }

        fwrite($handle, '</row>');
    }

    protected function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    protected function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    protected function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    protected function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Products" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    protected function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    protected function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }

    protected function core(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>E-commerce Admin</dc:creator>'
            . '<cp:lastModifiedBy>E-commerce Admin</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . now()->toISOString() . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . now()->toISOString() . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    protected function app(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>E-commerce Admin</Application>'
            . '</Properties>';
    }
}
