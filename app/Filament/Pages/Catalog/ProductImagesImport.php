<?php

namespace App\Filament\Pages\Catalog;

use App\Support\ProductImagesXmlImporter;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use UnitEnum;

class ProductImagesImport extends Page
{
    use WithFileUploads;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationLabel = 'Εισαγωγή εικόνων XML';

    protected static ?string $title = 'Εισαγωγή εικόνων προϊόντων';

    protected static ?string $slug = 'catalog/product-images-import';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 44;

    protected string $view = 'filament.pages.catalog.product-images-import';

    public TemporaryUploadedFile|string|null $xmlFile = null;

    public ?array $lastStats = null;

    public function import(ProductImagesXmlImporter $importer): void
    {
        if (! Schema::hasTable('product_images')) {
            Notification::make()
                ->title('Λείπει ο πίνακας product_images. Τρέξε πρώτα migrations.')
                ->danger()
                ->send();

            return;
        }

        if (! $this->xmlFile instanceof TemporaryUploadedFile) {
            Notification::make()
                ->title('Διάλεξε XML αρχείο.')
                ->warning()
                ->send();

            return;
        }

        $this->lastStats = $importer->import($this->xmlFile->getRealPath());

        Notification::make()
            ->title('Η εισαγωγή εικόνων ολοκληρώθηκε.')
            ->body("Matched: {$this->lastStats['matched']} / Images: {$this->lastStats['images']}")
            ->success()
            ->send();

        $this->xmlFile = null;
    }
}
