<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Reviews extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';

    protected static ?string $navigationLabel = 'Κριτικές';

    protected static ?string $title = 'Κριτικές';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 25;

    protected string $view = 'filament.pages.catalog.reviews';

    public string $search = '';

    public string $statusFilter = 'all';

    public string $answeredFilter = 'all';

    public ?int $selectedReviewId = null;

    protected function getViewData(): array
    {
        $reviews = $this->reviewsForView();

        if ($this->selectedReviewId === null && $reviews !== []) {
            $this->selectedReviewId = (int) $reviews[0]['id'];
        }

        return [
            'reviews' => $reviews,
            'selectedReview' => collect($reviews)->first(fn (array $review): bool => (int) $review['id'] === (int) $this->selectedReviewId),
            'totalReviews' => count($reviews),
            'tableReady' => Schema::hasTable('product_reviews'),
        ];
    }

    public function selectReview(int $id): void
    {
        if ($id > 0) {
            $this->selectedReviewId = $id;
        }
    }

    public function toggleStatus(int $id, bool $active): void
    {
        $this->updateBoolean($id, 'is_active', $active, $active ? 'Η κριτική ενεργοποιήθηκε.' : 'Η κριτική απενεργοποιήθηκε.');
    }

    public function toggleAnswered(int $id, bool $answered): void
    {
        $this->updateBoolean($id, 'is_answered', $answered, $answered ? 'Η κριτική σημειώθηκε ως απαντημένη.' : 'Η κριτική σημειώθηκε ως αναπάντητη.');
    }

    protected function updateBoolean(int $id, string $column, bool $value, string $message): void
    {
        if (! Schema::hasTable('product_reviews') || ! Schema::hasColumn('product_reviews', $column) || $id <= 0) {
            return;
        }

        DB::table('product_reviews')
            ->where('id', $id)
            ->update([
                $column => $value,
                'updated_at' => now(),
            ]);

        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    protected function reviewsForView(): array
    {
        if (! Schema::hasTable('product_reviews')) {
            return [];
        }

        $query = DB::table('product_reviews');

        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($query) use ($search): void {
                $query->where('product_name', 'like', $search)
                    ->orWhere('customer_name', 'like', $search)
                    ->orWhere('customer_email', 'like', $search)
                    ->orWhere('review_text', 'like', $search);
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->answeredFilter === 'answered') {
            $query->where('is_answered', true);
        } elseif ($this->answeredFilter === 'unanswered') {
            $query->where('is_answered', false);
        }

        return $query
            ->orderByDesc('added_at')
            ->orderByDesc('id')
            ->limit(300)
            ->get()
            ->map(fn ($review): array => $this->mapReview((array) $review))
            ->all();
    }

    protected function mapReview(array $review): array
    {
        return [
            'id' => (int) ($review['id'] ?? 0),
            'product_name' => (string) ($review['product_name'] ?? 'Προϊόν #' . ($review['product_id'] ?? '-')),
            'rating' => (int) ($review['rating'] ?? 0),
            'customer_name' => (string) ($review['customer_name'] ?? '-'),
            'customer_email' => (string) ($review['customer_email'] ?? ''),
            'review_text' => (string) ($review['review_text'] ?? ''),
            'reply_text' => (string) ($review['reply_text'] ?? ''),
            'is_active' => (bool) ($review['is_active'] ?? false),
            'is_answered' => (bool) ($review['is_answered'] ?? false),
            'added_at' => $this->formatDate($review['added_at'] ?? null),
        ];
    }

    protected function formatDate($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
