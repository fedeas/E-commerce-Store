<x-filament-panels::page>
    @php
        $reviews = $reviews ?? [];
        $selectedReview = $selectedReview ?? null;
        $totalReviews = $totalReviews ?? count($reviews);
        $tableReady = $tableReady ?? false;
    @endphp

    <div class="cl-reviews-admin">
        <style>
            main.fi-main,
            main.fi-main.fi-width-7xl,
            .fi-main,
            .fi-main.fi-width-7xl,
            .fi-page,
            .fi-page-main,
            .fi-page-content,
            .fi-page-header-main-ctn {
                max-width: 100% !important;
                width: 100% !important;
            }

            .fi-header { display: none !important; }
            .fi-page-content { background: #edf3f8; }

            .cl-reviews-admin {
                --cl-border: #c5d3df;
                --cl-border-dark: #9fb6c9;
                --cl-text: #102033;
                --cl-muted: #607080;
                display: grid;
                gap: 12px;
                color: var(--cl-text);
            }

            .cl-top-title,
            .cl-toolbar,
            .cl-panel,
            .cl-side {
                border: 1px solid var(--cl-border-dark);
                border-radius: 8px;
                background: #ffffff;
                overflow: hidden;
            }

            .cl-top-title,
            .cl-toolbar,
            .cl-panel-head,
            .cl-side-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
            }

            .cl-top-title,
            .cl-toolbar {
                padding: 10px 12px;
            }

            .cl-top-title,
            .cl-panel-head,
            .cl-side-head {
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
            }

            .cl-title h1 {
                margin: 0;
                font-size: 20px;
                font-weight: 900;
            }

            .cl-title p {
                margin: 3px 0 0;
                color: var(--cl-muted);
                font-size: 13px;
            }

            .cl-input,
            .cl-select {
                min-height: 35px;
                border: 1px solid var(--cl-border-dark);
                border-radius: 6px;
                background: #ffffff;
                color: var(--cl-text);
                font-size: 13px;
                padding: 0 9px;
            }

            .cl-input { width: min(420px, 100%); }

            .cl-toolbar-left,
            .cl-toolbar-right {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .cl-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 330px;
                gap: 12px;
                align-items: start;
            }

            .cl-panel-head,
            .cl-side-head {
                padding: 8px 10px;
                border-bottom: 1px solid var(--cl-border-dark);
                color: #18364f;
                font-size: 14px;
                font-weight: 900;
            }

            .cl-table-wrap { overflow-x: auto; }

            .cl-table {
                width: 100%;
                min-width: 980px;
                border-collapse: collapse;
            }

            .cl-table th {
                background: #edf6fc;
                color: #28455e;
                font-size: 12px;
                font-weight: 900;
                padding: 8px;
                border-bottom: 1px solid var(--cl-border);
                border-right: 1px solid #e2edf5;
                text-align: left;
                white-space: nowrap;
            }

            .cl-table td {
                color: var(--cl-text);
                font-size: 13px;
                padding: 8px;
                border-bottom: 1px solid #e5edf3;
                border-right: 1px solid #edf3f8;
                background: #ffffff;
                vertical-align: middle;
            }

            .cl-table tr:nth-child(even) td { background: #fbfdff; }
            .cl-table tr:hover td,
            .cl-table tr.is-selected td { background: #edf4fa; }
            .cl-table tr.is-selected td { box-shadow: inset 3px 0 0 #2e6fa7; }

            .cl-status-pair {
                display: inline-flex;
                gap: 5px;
                white-space: nowrap;
            }

            .cl-status-dot {
                width: 13px;
                height: 13px;
                border-radius: 999px;
                border: 1px solid transparent;
                opacity: .38;
                cursor: pointer;
            }

            .cl-status-dot.yes { background: #38c867; border-color: #0f7a38; }
            .cl-status-dot.no { background: #f04444; border-color: #9e1f1f; }
            .cl-status-dot.is-active { opacity: 1; box-shadow: 0 0 0 1px rgba(15,23,42,.18), inset 0 0 0 2px rgba(255,255,255,.55); }

            .cl-pill {
                display: inline-flex;
                padding: 4px 8px;
                border-radius: 999px;
                border: 1px solid #c8d8e5;
                background: #ffffff;
                color: #38566e;
                font-size: 12px;
                font-weight: 900;
            }

            .cl-stars { color: #d89b00; font-weight: 900; white-space: nowrap; }
            .cl-side { position: sticky; top: 16px; }
            .cl-side-body { display: grid; gap: 10px; padding: 10px; }
            .cl-review-text { border: 1px solid #d6e3ed; border-radius: 8px; background: #fbfdff; padding: 10px; color: #475569; font-size: 13px; line-height: 1.4; }
            .cl-empty { padding: 24px; text-align: center; color: #64748b; font-weight: 800; }

            @media (max-width: 1200px) {
                .cl-layout { grid-template-columns: 1fr; }
                .cl-side { position: static; }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title">
                <h1>Κριτικές προϊόντων</h1>
                <p>Διαχείριση κριτικών με κατάσταση, απάντηση, πελάτη και βαθμολογία.</p>
            </div>

            <span class="cl-pill">{{ $totalReviews }} κριτικές</span>
        </section>

        <section class="cl-toolbar">
            <div class="cl-toolbar-left">
                <input type="search" wire:model.live.debounce.350ms="search" class="cl-input" placeholder="Αναζήτηση προϊόντος / πελάτη / κειμένου">
                <select wire:model.live="statusFilter" class="cl-select">
                    <option value="all">Όλες οι καταστάσεις</option>
                    <option value="active">Ενεργές</option>
                    <option value="inactive">Ανενεργές</option>
                </select>
                <select wire:model.live="answeredFilter" class="cl-select">
                    <option value="all">Όλες οι απαντήσεις</option>
                    <option value="answered">Απαντήθηκαν</option>
                    <option value="unanswered">Αναπάντητες</option>
                </select>
            </div>

            @unless($tableReady)
                <span class="cl-pill">Τρέξε migrations για αποθήκευση κριτικών</span>
            @endunless
        </section>

        <div class="cl-layout">
            <section class="cl-panel">
                <div class="cl-panel-head">
                    <span>Κριτικές</span>
                    <span class="cl-pill">Εμφάνιση {{ $totalReviews > 0 ? 1 : 0 }} ως {{ $totalReviews }} από {{ $totalReviews }}</span>
                </div>

                <div class="cl-table-wrap">
                    <table class="cl-table">
                        <thead>
                            <tr>
                                <th>Προϊόντα</th>
                                <th style="text-align:right;">Βαθμολογία</th>
                                <th style="text-align:right;">Πελάτης</th>
                                <th style="text-align:right;">Ημερομηνία Προσθήκης</th>
                                <th style="text-align:right;">Κατάσταση</th>
                                <th style="text-align:right;">Απαντήθηκε</th>
                                <th style="text-align:right;">Πράξη</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $review)
                                <tr wire:click="selectReview({{ $review['id'] }})" class="{{ (int) $selectedReviewId === (int) $review['id'] ? 'is-selected' : '' }}">
                                    <td><strong>{{ $review['product_name'] }}</strong></td>
                                    <td style="text-align:right;"><span class="cl-stars">{{ str_repeat('★', max(0, min(5, $review['rating']))) }}</span></td>
                                    <td style="text-align:right;">{{ $review['customer_name'] }}</td>
                                    <td style="text-align:right;">{{ $review['added_at'] }}</td>
                                    <td style="text-align:right;">
                                        <span class="cl-status-pair">
                                            <button type="button" wire:click.stop="toggleStatus({{ $review['id'] }}, true)" class="cl-status-dot yes {{ $review['is_active'] ? 'is-active' : '' }}" title="Ενεργό"></button>
                                            <button type="button" wire:click.stop="toggleStatus({{ $review['id'] }}, false)" class="cl-status-dot no {{ ! $review['is_active'] ? 'is-active' : '' }}" title="Ανενεργό"></button>
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <span class="cl-status-pair">
                                            <button type="button" wire:click.stop="toggleAnswered({{ $review['id'] }}, true)" class="cl-status-dot yes {{ $review['is_answered'] ? 'is-active' : '' }}" title="Απαντήθηκε"></button>
                                            <button type="button" wire:click.stop="toggleAnswered({{ $review['id'] }}, false)" class="cl-status-dot no {{ ! $review['is_answered'] ? 'is-active' : '' }}" title="Αναπάντητη"></button>
                                        </span>
                                    </td>
                                    <td style="text-align:right;"><button type="button" wire:click.stop="selectReview({{ $review['id'] }})" class="cl-pill">Πληροφορίες</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="cl-empty">Εμφάνιση 0 ως 0 από 0 κριτικές προϊόντων.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="cl-side">
                <div class="cl-side-head">{{ $selectedReview['product_name'] ?? 'Πληροφορίες' }}</div>
                <div class="cl-side-body">
                    @if($selectedReview)
                        <span class="cl-pill">{{ $selectedReview['is_active'] ? 'Ενεργή' : 'Ανενεργή' }}</span>
                        <span class="cl-pill">{{ $selectedReview['is_answered'] ? 'Απαντήθηκε' : 'Αναπάντητη' }}</span>
                        <div class="cl-review-text">{{ $selectedReview['review_text'] ?: 'Δεν υπάρχει κείμενο κριτικής.' }}</div>
                        <div class="cl-review-text">{{ $selectedReview['reply_text'] ?: 'Δεν υπάρχει απάντηση.' }}</div>
                    @else
                        <div class="cl-empty">Δεν υπάρχουν κριτικές προϊόντων.</div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
