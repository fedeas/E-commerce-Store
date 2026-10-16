<?php

namespace App\Filament\Pages\Catalog;

use App\Support\LocalMediaStore;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use UnitEnum;

class HomeImagesBanners extends Page
{
    use WithFileUploads;

    protected ?LocalMediaStore $mediaStore = null;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Εικόνες αρχικής / banners';
    protected static ?string $title = 'Εικόνες αρχικής / banners';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-photo';
    protected static ?int $navigationSort = 27;

    protected string $view = 'filament.pages.catalog.home-images-banners';

    public ?int $editingId = null;
    public string $titleInput = '';
    public string $subtitleInput = '';
    public string $mediaTypeInput = 'image';
    public string $mediaUrlInput = '';
    public string $productRefsInput = '';
    public string $linkUrlInput = '';
    public string $buttonLabelInput = '';
    public string $sortOrderInput = '0';
    public string $startsAtInput = '';
    public string $endsAtInput = '';
    public bool $isActiveInput = true;
    public bool $opensNewTabInput = false;
    public $mediaUpload = null;

    protected function getViewData(): array
    {
        return [
            'banners' => $this->bannersForView(),
            'tableReady' => Schema::hasTable('home_banners'),
        ];
    }

    public function createNew(): void
    {
        $this->resetForm();
    }

    public function editBanner(int $id): void
    {
        if (! Schema::hasTable('home_banners')) {
            return;
        }

        $banner = DB::table('home_banners')->where('id', $id)->first();

        if (! $banner) {
            return;
        }

        $this->editingId = (int) $banner->id;
        $this->titleInput = (string) ($banner->title ?? '');
        $this->subtitleInput = (string) ($banner->subtitle ?? '');
        $this->mediaTypeInput = (string) ($banner->media_type ?? 'image');
        $this->mediaUrlInput = (string) ($banner->media_url ?? '');
        $this->productRefsInput = (string) ($banner->product_refs ?? '');
        $this->linkUrlInput = (string) ($banner->link_url ?? '');
        $this->buttonLabelInput = (string) ($banner->button_label ?? '');
        $this->sortOrderInput = (string) ($banner->sort_order ?? 0);
        $this->startsAtInput = $this->dateTimeLocal($banner->starts_at ?? null);
        $this->endsAtInput = $this->dateTimeLocal($banner->ends_at ?? null);
        $this->isActiveInput = (bool) ($banner->is_active ?? true);
        $this->opensNewTabInput = (bool) ($banner->opens_new_tab ?? false);
        $this->mediaUpload = null;
    }

    public function saveBanner(): void
    {
        if (! Schema::hasTable('home_banners')) {
            Notification::make()->title('Τρέξε πρώτα τα migrations για banners.')->warning()->send();
            return;
        }

        $this->validate([
            'titleInput' => ['nullable', 'string', 'max:255'],
            'subtitleInput' => ['nullable', 'string', 'max:255'],
            'mediaTypeInput' => ['required', 'in:image,video'],
            'mediaUrlInput' => ['nullable', 'string', 'max:255'],
            'productRefsInput' => ['nullable', 'string', 'max:2000'],
            'linkUrlInput' => ['nullable', 'string', 'max:255'],
            'buttonLabelInput' => ['nullable', 'string', 'max:80'],
            'sortOrderInput' => ['nullable', 'integer'],
            'startsAtInput' => ['nullable', 'date'],
            'endsAtInput' => ['nullable', 'date'],
            'mediaUpload' => ['nullable', 'file', 'max:51200'],
        ]);

        $existing = $this->editingId ? DB::table('home_banners')->where('id', $this->editingId)->first() : null;
        $mediaUrl = $this->emptyToNull($this->mediaUrlInput);
        $mediaPath = $mediaUrl ? $this->mediaStore()->localize($mediaUrl, 'home-banners') : ($existing->media_path ?? null);
        $mediaUrl = $mediaPath && $this->mediaStore()->isRemote($mediaPath) ? $mediaPath : null;
        $mediaPath = $mediaUrl ? null : $mediaPath;

        if ($this->mediaUpload) {
            $mediaPath = $this->mediaUpload->store('home-banners', 'public');
            $mediaUrl = null;
        }

        $data = [
            'title' => $this->emptyToNull($this->titleInput),
            'subtitle' => $this->emptyToNull($this->subtitleInput),
            'media_type' => $this->mediaTypeInput,
            'media_path' => $mediaPath,
            'media_url' => $mediaUrl,
            'product_refs' => $this->emptyToNull($this->productRefsInput),
            'link_url' => $this->emptyToNull($this->linkUrlInput),
            'button_label' => $this->emptyToNull($this->buttonLabelInput),
            'opens_new_tab' => $this->opensNewTabInput,
            'is_active' => $this->isActiveInput,
            'sort_order' => (int) ($this->sortOrderInput ?: 0),
            'starts_at' => $this->emptyToNull($this->startsAtInput),
            'ends_at' => $this->emptyToNull($this->endsAtInput),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('home_banners', 'placement')) {
            $data['placement'] = 'top';
        }

        if ($this->editingId) {
            DB::table('home_banners')->where('id', $this->editingId)->update($data);
            $message = 'Το banner ενημερώθηκε.';
        } else {
            $data['created_at'] = now();
            $this->editingId = (int) DB::table('home_banners')->insertGetId($data);
            $message = 'Το banner προστέθηκε.';
        }

        $this->mediaUpload = null;

        Notification::make()->title($message)->success()->send();
    }

    public function deleteBanner(int $id): void
    {
        if (Schema::hasTable('home_banners') && $id > 0) {
            DB::table('home_banners')->where('id', $id)->delete();
        }

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function toggleBanner(int $id, bool $active): void
    {
        if (Schema::hasTable('home_banners') && $id > 0) {
            DB::table('home_banners')->where('id', $id)->update(['is_active' => $active, 'updated_at' => now()]);
        }
    }

    protected function bannersForView(): array
    {
        if (! Schema::hasTable('home_banners')) {
            return [];
        }

        $query = DB::table('home_banners');

        if (Schema::hasColumn('home_banners', 'placement')) {
            $query->where(function ($query): void {
                $query->where('placement', 'top')->orWhereNull('placement');
            });
        }

        return $query
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($banner): array => [
                'id' => (int) $banner->id,
                'title' => (string) ($banner->title ?? ''),
                'subtitle' => (string) ($banner->subtitle ?? ''),
                'media_type' => (string) ($banner->media_type ?? 'image'),
                'media' => $this->publicMediaUrl($banner->media_path ?? null, $banner->media_url ?? null),
                'product_refs' => (string) ($banner->product_refs ?? ''),
                'link_url' => (string) ($banner->link_url ?? ''),
                'button_label' => (string) ($banner->button_label ?? ''),
                'sort_order' => (int) ($banner->sort_order ?? 0),
                'is_active' => (bool) ($banner->is_active ?? false),
                'period' => $this->periodLabel($banner->starts_at ?? null, $banner->ends_at ?? null),
            ])
            ->all();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->titleInput = '';
        $this->subtitleInput = '';
        $this->mediaTypeInput = 'image';
        $this->mediaUrlInput = '';
        $this->productRefsInput = '';
        $this->linkUrlInput = '';
        $this->buttonLabelInput = '';
        $this->sortOrderInput = '0';
        $this->startsAtInput = '';
        $this->endsAtInput = '';
        $this->isActiveInput = true;
        $this->opensNewTabInput = false;
        $this->mediaUpload = null;
    }

    protected function publicMediaUrl(?string $path, ?string $url): string
    {
        $url = trim((string) $url);

        if ($url !== '') {
            return $url;
        }

        if ($path) {
            return '/media/' . ltrim($path, '/');
        }

        return '';
    }

    protected function mediaStore(): LocalMediaStore
    {
        return $this->mediaStore ??= new LocalMediaStore();
    }

    protected function periodLabel($startsAt, $endsAt): string
    {
        return ($startsAt ? date('d/m/Y H:i', strtotime((string) $startsAt)) : 'Άμεσα') . ' - ' . ($endsAt ? date('d/m/Y H:i', strtotime((string) $endsAt)) : 'Χωρίς λήξη');
    }

    protected function dateTimeLocal($value): string
    {
        return $value ? date('Y-m-d\TH:i', strtotime((string) $value)) : '';
    }

    protected function emptyToNull(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

}
