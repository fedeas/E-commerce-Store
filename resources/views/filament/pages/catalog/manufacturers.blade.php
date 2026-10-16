<x-filament-panels::page>
    @php
        $manufacturers = $manufacturers ?? [];
        $selectedManufacturer = $selectedManufacturer ?? null;
        $totalManufacturers = $totalManufacturers ?? (is_countable($manufacturers) ? count($manufacturers) : 0);

        $isEnabled = function ($value): bool {
            return in_array($value, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
        };
    @endphp

    <div class="cl-manufacturer-admin">
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

            .fi-header {
                display: none !important;
            }

            .fi-page-content {
                background: #edf3f8;
            }

            .cl-manufacturer-admin {
                --cl-border: #c5d3df;
                --cl-border-dark: #9fb6c9;
                --cl-text: #102033;
                --cl-muted: #607080;
                display: grid;
                gap: 12px;
                width: 100%;
                color: var(--cl-text);
            }

            .cl-top-title {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border: 1px solid var(--cl-border-dark);
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
                border-radius: 8px;
            }

            .cl-title-left {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .cl-title-icon {
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                background: #ffffff;
                border: 1px solid #bdd2e3;
                font-size: 16px;
                flex-shrink: 0;
            }

            .cl-title-text h1 {
                margin: 0;
                font-size: 20px;
                font-weight: 900;
                line-height: 1.1;
            }

            .cl-title-text p {
                margin: 3px 0 0 0;
                font-size: 13px;
                color: var(--cl-muted);
            }

            .cl-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
                padding: 10px;
                border: 1px solid var(--cl-border);
                background: #ffffff;
                border-radius: 8px;
            }

            .cl-field {
                display: grid;
                gap: 4px;
            }

            .cl-label {
                font-size: 11px;
                font-weight: 900;
                color: #607080;
                text-transform: none;
                letter-spacing: .04em;
            }

            .cl-input {
                width: 100%;
                min-height: 35px;
                border: 1px solid var(--cl-border-dark);
                border-radius: 6px;
                background: #ffffff;
                color: var(--cl-text);
                font-size: 13px;
                padding: 0 9px;
                outline: none;
            }

            .cl-search {
                width: min(420px, 100%);
            }

            .cl-btn,
            .cl-small-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                border: 1px solid var(--cl-border-dark);
                background: linear-gradient(180deg, #ffffff 0%, #edf5fb 100%);
                color: #1f3548;
                font-weight: 800;
                line-height: 1;
                cursor: pointer;
                transition: .15s ease;
                white-space: nowrap;
                text-decoration: none;
            }

            .cl-btn {
                min-height: 36px;
                padding: 0 12px;
                border-radius: 7px;
                font-size: 13px;
            }

            .cl-small-btn {
                min-height: 30px;
                padding: 0 9px;
                border-radius: 6px;
                font-size: 12px;
            }

            .cl-btn:hover,
            .cl-small-btn:hover {
                background: linear-gradient(180deg, #ffffff 0%, #dceefa 100%);
                border-color: #7fa6c7;
            }

            .cl-btn.primary,
            .cl-small-btn.primary {
                background: linear-gradient(180deg, #367db8 0%, #245f95 100%);
                border-color: #1e507d;
                color: #ffffff;
            }

            .cl-small-btn.red {
                background: linear-gradient(180deg, #d94b4b 0%, #b92d2d 100%);
                border-color: #9d2323;
                color: #ffffff;
            }

            .cl-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 320px;
                gap: 12px;
                align-items: start;
            }

            .cl-panel,
            .cl-side {
                border: 1px solid var(--cl-border-dark);
                background: #ffffff;
                border-radius: 8px;
                overflow: hidden;
            }

            .cl-panel-head,
            .cl-side-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 8px 10px;
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
                border-bottom: 1px solid var(--cl-border-dark);
            }

            .cl-panel-title,
            .cl-side-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                font-weight: 900;
                color: #18364f;
            }

            .cl-panel-count {
                padding: 3px 8px;
                border-radius: 999px;
                background: #ffffff;
                border: 1px solid #bdd2e3;
                font-size: 12px;
                font-weight: 900;
                color: #38566e;
            }

            .cl-table-wrap {
                overflow-x: auto;
                background: #ffffff;
            }

            .cl-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 980px;
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
                padding: 7px 8px;
                border-bottom: 1px solid #e5edf3;
                border-right: 1px solid #edf3f8;
                vertical-align: middle;
                background: #ffffff;
            }

            .cl-table tr:nth-child(even) td {
                background: #fbfdff;
            }

            .cl-table tr {
                cursor: pointer;
            }

            .cl-table tr:hover td {
                background: #edf4fa;
            }

            .cl-table tr.is-selected td {
                background: #dfeaf3 !important;
                box-shadow: inset 3px 0 0 #2e6fa7;
            }

            .cl-id {
                color: #0f2540;
                font-weight: 900;
                white-space: nowrap;
            }

            .cl-name {
                min-width: 260px;
                font-weight: 900;
            }

            .cl-muted {
                color: #64748b;
                font-weight: 700;
            }

            .cl-status-pair {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                white-space: nowrap;
            }

            .cl-status-dot {
                width: 13px;
                height: 13px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                border: 1px solid transparent;
                opacity: .38;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .55);
            }

            .cl-status-dot.yes {
                background: #38c867;
                border-color: #0f7a38;
            }

            .cl-status-dot.no {
                background: #f04444;
                border-color: #9e1f1f;
            }

            .cl-status-dot.is-active {
                opacity: 1;
                box-shadow: 0 0 0 1px rgba(15, 23, 42, .18), inset 0 0 0 2px rgba(255, 255, 255, .55);
            }

            .cl-status-dot:not(.is-active) {
                filter: saturate(.5);
            }

            button.cl-status-dot {
                padding: 0;
                appearance: none;
                -webkit-appearance: none;
                cursor: pointer;
                transition: transform .12s ease, opacity .12s ease, box-shadow .12s ease;
            }

            button.cl-status-dot:hover {
                transform: scale(1.16);
            }

            .cl-side {
                position: sticky;
                top: 16px;
            }

            .cl-side-body {
                padding: 10px;
                display: grid;
                gap: 10px;
            }

            .cl-side-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }

            .cl-info-box {
                border: 1px solid #d6e3ed;
                border-radius: 7px;
                background: #fbfdff;
                overflow: hidden;
            }

            .cl-info-box-title {
                padding: 7px 8px;
                background: #edf6fc;
                border-bottom: 1px solid #d6e3ed;
                font-size: 12px;
                font-weight: 900;
                color: #38566e;
            }

            .cl-info-box-body {
                padding: 8px;
                display: grid;
                gap: 7px;
            }

            .cl-info-row {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                font-size: 12px;
            }

            .cl-info-row span:first-child {
                color: #64748b;
                font-weight: 800;
            }

            .cl-info-row span:last-child {
                color: #102033;
                font-weight: 900;
                text-align: right;
            }

            .cl-image-box {
                height: 120px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px dashed #b6c8d8;
                border-radius: 7px;
                background: #f8fbfe;
                color: #64748b;
                font-size: 12px;
                font-weight: 800;
            }

            .cl-image-box img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            .cl-modal-backdrop {
                position: fixed;
                inset: 0;
                z-index: 90;
                background: rgba(15, 23, 42, .45);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }

            .cl-modal {
                width: min(640px, 100%);
                border-radius: 10px;
                border: 1px solid var(--cl-border-dark);
                background: #ffffff;
                overflow: hidden;
                box-shadow: 0 25px 60px rgba(15, 23, 42, .25);
            }

            .cl-modal-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 10px 12px;
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
                border-bottom: 1px solid var(--cl-border-dark);
            }

            .cl-modal-title {
                font-size: 15px;
                font-weight: 900;
                color: #18364f;
            }

            .cl-modal-body {
                padding: 12px;
                display: grid;
                gap: 10px;
            }

            .cl-modal-footer {
                display: flex;
                justify-content: flex-end;
                gap: 8px;
                padding: 10px 12px;
                border-top: 1px solid #d6e3ed;
                background: #f8fbfe;
            }

            .cl-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                padding: 12px;
                border-top: 1px solid #c5d3df;
                background: linear-gradient(180deg, #f8fbfe 0%, #edf5fb 100%);
            }

            .cl-pagination-summary {
                display: inline-flex;
                align-items: center;
                min-height: 34px;
                padding: 0 11px;
                border: 1px solid #d6e3ed;
                border-radius: 999px;
                background: #ffffff;
                color: #38566e;
                font-size: 12px;
                font-weight: 900;
                white-space: nowrap;
            }

            .cl-pagination-controls {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 6px;
                flex-wrap: wrap;
            }

            .cl-page-btn,
            .cl-page-number {
                min-width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #b7c9d9;
                border-radius: 9px;
                background: linear-gradient(180deg, #ffffff 0%, #edf5fb 100%);
                color: #1f3548;
                font-size: 12px;
                font-weight: 900;
                line-height: 1;
                cursor: pointer;
                transition: .15s ease;
                text-decoration: none;
                user-select: none;
            }

            .cl-page-btn {
                width: auto;
                padding: 0 11px;
            }

            .cl-page-number:hover,
            .cl-page-btn:hover {
                background: linear-gradient(180deg, #ffffff 0%, #dceefa 100%);
                border-color: #7fa6c7;
                color: #0f2540;
            }

            .cl-page-number.is-active {
                background: linear-gradient(180deg, #367db8 0%, #245f95 100%);
                border-color: #1e507d;
                color: #ffffff;
                box-shadow: 0 1px 4px rgba(30, 80, 125, .25);
            }

            .cl-page-btn:disabled,
            .cl-page-number:disabled {
                opacity: .45;
                cursor: not-allowed;
                background: #eef3f7;
                color: #7b8b9a;
                box-shadow: none;
            }

            .cl-page-dots {
                min-width: 24px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #64748b;
                font-size: 13px;
                font-weight: 900;
            }

            @media (max-width: 1500px) {
                .cl-layout {
                    grid-template-columns: 1fr;
                }

                .cl-side {
                    position: static;
                }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title-left">
                <span class="cl-title-icon">🏭</span>

                <div class="cl-title-text">
                    <h1>Κατασκευαστές</h1>
                    <p>Διαχείριση κατασκευαστών/brands που συνδέονται με προϊόντα και vendor imports.</p>
                </div>
            </div>

            <button type="button" wire:click="openCreateModal" class="cl-btn primary">+ Νέος κατασκευαστής</button>
        </section>

        <section class="cl-toolbar">
            <div class="cl-field cl-search">
                <label class="cl-label" for="manufacturers_blade_search_1">Αναζήτηση</label>
                <input type="text" wire:model.live.debounce.350ms="search" class="cl-input" placeholder="Όνομα / slug / website" id="manufacturers_blade_search_1" name="search">
            </div>

            <div class="cl-field" style="width: 170px;">
                <label class="cl-label" for="manufacturers_blade_perPage_2">Ανά σελίδα</label>
                <select wire:model.live="perPage" class="cl-input" id="manufacturers_blade_perPage_2" name="perPage">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="300">300</option>
                </select>
            </div>

            <div class="cl-muted">
                Σύνολο κατασκευαστών: {{ $totalManufacturers }}
            </div>
        </section>

        <div class="cl-layout">
            <section class="cl-panel">
                <div class="cl-panel-head">
                    <div class="cl-panel-title">
                        <span>Κατασκευαστές</span>
                        <span class="cl-panel-count">
                            @if(method_exists($manufacturers, 'total'))
                                {{ $manufacturers->firstItem() ?? 0 }}-{{ $manufacturers->lastItem() ?? 0 }} / {{ $manufacturers->total() }}
                            @else
                                {{ count($manufacturers) }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="cl-table-wrap">
                    <table class="cl-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Κατασκευαστής</th>
                                <th>Slug</th>
                                <th>Ενεργός</th>
                                <th>Προϊόντα</th>
                                <th>Website</th>
                                <th>Σειρά</th>
                                <th>Προσθήκη</th>
                                <th>Ενημέρωση</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($manufacturers as $row)
                                @php
                                    $rowId = (int) data_get($row, 'id', 0);
                                    $isSelected = $selectedManufacturer && (int) data_get($selectedManufacturer, 'id', 0) === $rowId;
                                    $active = $isEnabled(data_get($row, 'active', true));
                                @endphp

                                <tr wire:click="selectManufacturer({{ $rowId }})" class="{{ $isSelected ? 'is-selected' : '' }}">
                                    <td class="cl-id">{{ $rowId }}</td>
                                    <td class="cl-name">{{ data_get($row, 'name', '-') }}</td>
                                    <td>{{ data_get($row, 'slug') ?: '-' }}</td>

                                    <td>
                                        <span class="cl-status-pair">
                                            <button type="button" wire:click.stop="toggleManufacturerStatus({{ $rowId }}, true)" class="cl-status-dot yes {{ $active ? 'is-active' : '' }}"></button>
                                            <button type="button" wire:click.stop="toggleManufacturerStatus({{ $rowId }}, false)" class="cl-status-dot no {{ ! $active ? 'is-active' : '' }}"></button>
                                        </span>
                                    </td>

                                    <td>{{ data_get($row, 'products_count', 0) }}</td>
                                    <td>{{ data_get($row, 'website_url') ?: '-' }}</td>
                                    <td class="cl-id">{{ data_get($row, 'sort_order', '-') }}</td>
                                    <td>{{ data_get($row, 'created_at', '-') }}</td>
                                    <td>{{ data_get($row, 'updated_at', '-') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-slate-500">Δεν υπάρχουν κατασκευαστές.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($manufacturers, 'currentPage'))
                    @php
                        $currentPage = $manufacturers->currentPage();
                        $lastPage = $manufacturers->lastPage();
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp

                    <div class="cl-pagination">
                        <div class="cl-pagination-summary">
                            Εμφάνιση {{ $manufacturers->firstItem() ?? 0 }}-{{ $manufacturers->lastItem() ?? 0 }} από {{ $manufacturers->total() }}
                        </div>

                        <div class="cl-pagination-controls">
                            <button
                                type="button"
                                wire:click="previousPage"
                                wire:loading.attr="disabled"
                                class="cl-page-btn"
                                @disabled($manufacturers->onFirstPage())
                            >
                                ‹ Προηγούμενη
                            </button>

                            @if($startPage > 1)
                                <button type="button" wire:click="gotoPage(1)" wire:loading.attr="disabled" class="cl-page-number">1</button>

                                @if($startPage > 2)
                                    <span class="cl-page-dots">...</span>
                                @endif
                            @endif

                            @for($page = $startPage; $page <= $endPage; $page++)
                                <button
                                    type="button"
                                    wire:click="gotoPage({{ $page }})"
                                    wire:loading.attr="disabled"
                                    class="cl-page-number {{ $page === $currentPage ? 'is-active' : '' }}"
                                    @disabled($page === $currentPage)
                                >
                                    {{ $page }}
                                </button>
                            @endfor

                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <span class="cl-page-dots">...</span>
                                @endif

                                <button type="button" wire:click="gotoPage({{ $lastPage }})" wire:loading.attr="disabled" class="cl-page-number">{{ $lastPage }}</button>
                            @endif

                            <button
                                type="button"
                                wire:click="nextPage"
                                wire:loading.attr="disabled"
                                class="cl-page-btn"
                                @disabled(! $manufacturers->hasMorePages())
                            >
                                Επόμενη ›
                            </button>
                        </div>
                    </div>
                @endif
            </section>

            <aside class="cl-side">
                <div class="cl-side-head">
                    <div class="cl-side-title">Πληροφορίες</div>
                </div>

                <div class="cl-side-body">
                    @if($selectedManufacturer)
                        <div class="cl-side-actions">
                            <button type="button" wire:click="openEditModal" class="cl-small-btn primary">Επεξεργασία</button>
                            <button type="button" wire:click="deleteSelectedManufacturer" class="cl-small-btn red">Διαγραφή</button>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Περιγραφή</div>

                            <div class="cl-info-box-body">
                                <div class="text-sm font-black text-slate-800 leading-snug">
                                    {{ data_get($selectedManufacturer, 'name', '-') }}
                                </div>

                                <div class="text-xs font-bold text-slate-500">
                                    Slug: {{ data_get($selectedManufacturer, 'slug') ?: '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Εικόνα</div>

                            <div class="cl-info-box-body">
                                <div class="cl-image-box">
                                    @if(data_get($selectedManufacturer, 'image_path'))
                                        <img src="{{ data_get($selectedManufacturer, 'image_path') }}" alt="{{ data_get($selectedManufacturer, 'name') }}">
                                    @else
                                        Δεν υπάρχει εικόνα
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Στοιχεία</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>ID</span>
                                    <span>{{ data_get($selectedManufacturer, 'id', '-') }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Ενεργός</span>
                                    <span>{{ data_get($selectedManufacturer, 'active') ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Προϊόντα</span>
                                    <span>{{ data_get($selectedManufacturer, 'products_count', 0) }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Website</span>
                                    <span>{{ data_get($selectedManufacturer, 'website_url') ?: '-' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Σειρά</span>
                                    <span>{{ data_get($selectedManufacturer, 'sort_order', '-') }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Πληροφορίες</div>

                            <div class="cl-info-box-body">
                                <div class="text-xs font-bold text-slate-600">
                                    Επίλεξε έναν κατασκευαστή από τον πίνακα.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>
        </div>

        @if($showEditModal)
            <div class="cl-modal-backdrop">
                <div class="cl-modal">
                    <div class="cl-modal-head">
                        <div class="cl-modal-title">
                            {{ $editingManufacturerId ? 'Επεξεργασία κατασκευαστή' : 'Νέος κατασκευαστής' }}
                        </div>

                        <button type="button" wire:click="closeEditModal" class="cl-small-btn">Κλείσιμο</button>
                    </div>

                    <div class="cl-modal-body">
                        <div class="cl-field">
                            <label class="cl-label" for="manufacturers_blade_editName_3">Κατασκευαστής</label>
                            <input type="text" wire:model.live="editName" class="cl-input" id="manufacturers_blade_editName_3" name="editName">
                        </div>

                        <div class="cl-field">
                            <label class="cl-label" for="manufacturers_blade_editSlug_4">Slug</label>
                            <input type="text" wire:model.live="editSlug" class="cl-input" id="manufacturers_blade_editSlug_4" name="editSlug">
                        </div>

                        <div class="cl-field">
                            <label class="cl-label" for="manufacturers_blade_editWebsiteUrl_5">Website URL</label>
                            <input type="text" wire:model.live="editWebsiteUrl" class="cl-input" id="manufacturers_blade_editWebsiteUrl_5" name="editWebsiteUrl">
                        </div>

                        <div class="cl-field">
                            <label class="cl-label" for="manufacturers_blade_editImagePath_6">Image path / URL</label>
                            <input type="text" wire:model.live="editImagePath" class="cl-input" id="manufacturers_blade_editImagePath_6" name="editImagePath">
                        </div>

                        <div class="cl-field">
                            <label class="cl-label" for="manufacturers_blade_editSortOrder_7">Σειρά</label>
                            <input type="text" wire:model.live="editSortOrder" class="cl-input" id="manufacturers_blade_editSortOrder_7" name="editSortOrder">
                        </div>

                        <div class="cl-field">
                            <label class="cl-label">Ενεργός</label>

                            <span class="cl-status-pair">
                                <button type="button" wire:click="$set('editActive', true)" class="cl-status-dot yes {{ $editActive ? 'is-active' : '' }}"></button>
                                <button type="button" wire:click="$set('editActive', false)" class="cl-status-dot no {{ ! $editActive ? 'is-active' : '' }}"></button>
                            </span>
                        </div>
                    </div>

                    <div class="cl-modal-footer">
                        <button type="button" wire:click="closeEditModal" class="cl-btn">Άκυρο</button>
                        <button type="button" wire:click="saveManufacturer" class="cl-btn primary">Αποθήκευση</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
