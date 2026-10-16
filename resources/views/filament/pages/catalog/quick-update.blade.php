<x-filament-panels::page>
    @php
        $products = $products ?? [];
        $categories = $categories ?? [];
        $brands = $brands ?? [];
        $suppliers = $suppliers ?? [];
        $stockTexts = $stockTexts ?? [];
        $selectedCount = $selectedCount ?? 0;
        $allPageSelected = $allPageSelected ?? false;
        $totalProducts = $totalProducts ?? 0;
        $productFirstItem = $productFirstItem ?? 0;
        $productLastItem = $productLastItem ?? 0;
        $productLastPage = $productLastPage ?? 1;
        $productCurrentPage = $productCurrentPage ?? 1;
        $perPageValue = $perPageValue ?? 50;
        $pageWindowStart = max(1, $productCurrentPage - 2);
        $pageWindowEnd = min($productLastPage, $productCurrentPage + 2);
    @endphp

    <div class="cl-quick-admin">
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

            .cl-quick-admin {
                --cl-bg: #edf3f8;
                --cl-panel: #ffffff;
                --cl-border: #c5d3df;
                --cl-border-dark: #9fb6c9;
                --cl-blue: #2e6fa7;
                --cl-blue-dark: #245b8b;
                --cl-blue-soft: #dceefa;
                --cl-text: #102033;
                --cl-muted: #607080;
                --cl-green: #159447;
                --cl-green-bg: #e7f8ed;
                --cl-red: #c73333;
                --cl-red-bg: #fdeaea;
                --cl-yellow: #f5b642;
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
                color: var(--cl-blue-dark);
                flex-shrink: 0;
            }

            .cl-title-svg {
                width: 18px;
                height: 18px;
            }

            .cl-title-text {
                min-width: 0;
            }

            .cl-title-text h1 {
                margin: 0;
                font-size: 20px;
                font-weight: 900;
                color: var(--cl-text);
                line-height: 1.1;
            }

            .cl-title-text p {
                margin: 3px 0 0 0;
                font-size: 13px;
                color: var(--cl-muted);
            }

            .cl-title-stats {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                flex-wrap: wrap;
            }

            .cl-stat-pill,
            .cl-selected-counter {
                min-height: 30px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 0 10px;
                border: 1px solid #bdd2e3;
                border-radius: 999px;
                background: rgba(255, 255, 255, .75);
                color: #38566e;
                font-size: 12px;
                font-weight: 900;
                white-space: nowrap;
            }

            .cl-selected-counter {
                background: #eef8ff;
                border-color: #9bc9dd;
                color: #24506a;
            }

            .cl-panel {
                border: 1px solid var(--cl-border-dark);
                background: var(--cl-panel);
                border-radius: 8px;
                overflow: hidden;
            }

            .cl-panel-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
                padding: 8px 10px;
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
                border-bottom: 1px solid var(--cl-border-dark);
            }

            .cl-panel-title {
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

            .cl-panel-actions,
            .cl-toolbar,
            .cl-footer-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .cl-filter-grid {
                display: grid;
                grid-template-columns: repeat(6, minmax(150px, 1fr));
                gap: 10px;
                padding: 10px;
                background: #ffffff;
            }

            .cl-field {
                display: grid;
                gap: 4px;
                min-width: 0;
            }

            .cl-field.is-wide {
                grid-column: span 2;
            }

            .cl-label {
                font-size: 11px;
                font-weight: 900;
                color: #607080;
                text-transform: none;
                letter-spacing: 0;
            }

            .cl-input,
            .cl-select {
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

            .cl-input:focus,
            .cl-select:focus {
                border-color: var(--cl-blue);
                box-shadow: 0 0 0 3px rgba(46, 111, 167, .13);
            }

            .cl-action-bar {
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

            .cl-btn,
            .cl-small-btn,
            .cl-page-btn,
            .cl-page-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                border: 1px solid var(--cl-border-dark);
                background: linear-gradient(180deg, #ffffff 0%, #edf5fb 100%);
                color: #1f3548;
                font-weight: 900;
                line-height: 1;
                cursor: pointer;
                transition: .15s ease;
                white-space: nowrap;
                text-decoration: none;
                user-select: none;
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
            .cl-small-btn:hover,
            .cl-page-btn:hover,
            .cl-page-number:hover {
                background: linear-gradient(180deg, #ffffff 0%, #dceefa 100%);
                border-color: #7fa6c7;
                color: #0f2540;
            }

            .cl-btn.primary,
            .cl-small-btn.primary {
                background: linear-gradient(180deg, #367db8 0%, #245f95 100%);
                border-color: #1e507d;
                color: #ffffff;
            }

            .cl-btn.green {
                background: linear-gradient(180deg, #20a956 0%, #148241 100%);
                border-color: #117338;
                color: #ffffff;
            }

            .cl-btn.red,
            .cl-small-btn.red {
                background: linear-gradient(180deg, #d94b4b 0%, #b92d2d 100%);
                border-color: #9d2323;
                color: #ffffff;
            }

            .cl-btn-icon {
                width: 16px;
                height: 16px;
                flex-shrink: 0;
            }

            .cl-table-wrap {
                overflow-x: auto;
                background: #ffffff;
            }

            .cl-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 1460px;
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

            .cl-table tr:hover td {
                background: #edf4fa;
            }

            .cl-check-col {
                width: 76px;
                text-align: center;
            }

            .cl-id-col {
                width: 76px;
                color: #0f2540;
                font-weight: 900;
                text-align: right;
                white-space: nowrap;
            }

            .cl-title-col {
                width: 520px;
                min-width: 520px;
            }

            .cl-status-col {
                width: 104px;
                text-align: center;
            }

            .cl-code-col {
                width: 190px;
                min-width: 190px;
            }

            .cl-price-col {
                width: 118px;
                font-weight: 900;
                white-space: nowrap;
            }

            .cl-brand-col,
            .cl-supplier-col,
            .cl-category-col {
                width: 136px;
            }

            .cl-date-col {
                width: 128px;
                white-space: nowrap;
            }

            .cl-checkbox {
                width: 17px;
                height: 17px;
                border-radius: 4px;
                cursor: pointer;
                accent-color: var(--cl-blue);
            }

            .cl-product-cell {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                min-width: 0;
            }

            .cl-image-wrap {
                width: 50px;
                height: 50px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                border: 1px solid #c7d8e6;
                border-radius: 8px;
                background: #ffffff;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
            }

            .cl-image-wrap img {
                display: block;
                max-width: 48px;
                max-height: 48px;
                object-fit: contain;
            }

            .cl-image-empty {
                color: #7b8da0;
                font-size: 10px;
                font-weight: 900;
            }

            .cl-product-main {
                display: grid;
                gap: 4px;
                min-width: 0;
            }

            .cl-product-title {
                color: #14283a;
                display: block;
                font-size: 13px;
                font-weight: 900;
                line-height: 1.3;
                overflow-wrap: anywhere;
            }

            .cl-row-links {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                color: #64748b;
                font-size: 11px;
                font-weight: 800;
                line-height: 1.25;
            }

            .cl-row-links a {
                color: #0f5f99;
                text-decoration: none;
            }

            .cl-row-links a:hover {
                text-decoration: underline;
            }

            .cl-status-btn {
                min-width: 78px;
                height: 26px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 900;
                border: 1px solid transparent;
                line-height: 1;
                user-select: none;
            }

            .cl-status-btn.is-on {
                color: var(--cl-green);
                background: var(--cl-green-bg);
                border-color: #98d9ae;
            }

            .cl-status-btn.is-off {
                color: var(--cl-red);
                background: var(--cl-red-bg);
                border-color: #efb1b1;
            }

            .cl-code-lines {
                display: grid;
                gap: 4px;
                line-height: 1.35;
                word-break: break-word;
                font-size: 12.5px;
            }

            .cl-code-lines strong {
                color: #0f2540;
                font-weight: 900;
            }

            .cl-muted {
                color: #64748b;
                font-weight: 800;
            }

            .cl-empty-row {
                height: 92px;
                color: #64748b;
                font-size: 13px;
                font-weight: 900;
                text-align: center;
            }

            .cl-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                padding: 12px;
                border-top: 1px solid var(--cl-border);
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

            .cl-page-btn,
            .cl-page-number {
                min-width: 34px;
                height: 34px;
                border-radius: 8px;
                font-size: 12px;
            }

            .cl-page-btn {
                width: auto;
                padding: 0 11px;
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

            .cl-per-page {
                width: 74px;
            }

            @media (max-width: 1450px) {
                .cl-filter-grid {
                    grid-template-columns: repeat(4, minmax(150px, 1fr));
                }
            }

            @media (max-width: 1050px) {
                .cl-top-title,
                .cl-action-bar,
                .cl-pagination {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .cl-title-stats,
                .cl-toolbar,
                .cl-panel-actions,
                .cl-footer-actions {
                    justify-content: flex-start;
                }

                .cl-filter-grid {
                    grid-template-columns: repeat(2, minmax(150px, 1fr));
                }
            }

            @media (max-width: 640px) {
                .cl-filter-grid {
                    grid-template-columns: 1fr;
                }

                .cl-field.is-wide {
                    grid-column: auto;
                }

                .cl-btn,
                .cl-small-btn,
                .cl-selected-counter,
                .cl-stat-pill {
                    width: 100%;
                }

                .cl-table {
                    min-width: 1320px;
                }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title-left">
                <span class="cl-title-icon">
                    <x-heroicon-o-arrow-path class="cl-title-svg" />
                </span>

                <div class="cl-title-text">
                    <h1>Γρήγορη ανανέωση</h1>
                    <p>Μαζική διαχείριση προϊόντων, φίλτρων και εξαγωγών.</p>
                </div>
            </div>

            <div class="cl-title-stats">
                <span class="cl-stat-pill">Προϊόντα: {{ $totalProducts }}</span>
                <span class="cl-stat-pill">Σελίδα: {{ $productCurrentPage }} / {{ $productLastPage }}</span>
                <span class="cl-selected-counter">Επιλεγμένα: {{ $selectedCount }}</span>
            </div>
        </section>

        <section class="cl-panel">
            <div class="cl-panel-head">
                <div class="cl-panel-title">
                    <x-heroicon-o-funnel class="cl-btn-icon" />
                    <span>Φίλτρα</span>
                </div>

                <button type="button" class="cl-small-btn" wire:click="resetFilters">
                    <x-heroicon-o-arrow-path class="cl-btn-icon" />
                    Επαναφορά
                </button>
            </div>

            <div class="cl-filter-grid">
                <div class="cl-field is-wide">
                    <label class="cl-label" for="qu-search">Αναζήτηση</label>
                    <input id="qu-search" type="search" class="cl-input" wire:model.live.debounce.350ms="search" placeholder="Τίτλος / κωδικός / MPN / EAN">
                </div>

                <div class="cl-field is-wide">
                    <label class="cl-label" for="qu-category">Κατηγορία</label>
                    <select id="qu-category" class="cl-select" wire:model.live="categoryFilter">
                        <option value="all">Αρχή</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-brand">Κατασκευαστής</label>
                    <select id="qu-brand" class="cl-select" wire:model.live="brandFilter">
                        <option value="all">-</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand['id'] }}">{{ $brand['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-status">Κατάσταση</label>
                    <select id="qu-status" class="cl-select" wire:model.live="statusFilter">
                        <option value="all">Όλα</option>
                        <option value="active">Ενεργά</option>
                        <option value="inactive">Ανενεργά</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-supplier">Προμηθευτής</label>
                    <select id="qu-supplier" class="cl-select" wire:model.live="supplierFilter">
                        <option value="all">-</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-not-supplier">Όχι προμηθευτής</label>
                    <select id="qu-not-supplier" class="cl-select" wire:model.live="notSupplierFilter">
                        <option value="all">-</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-xml">Προέλευση XML</label>
                    <select id="qu-xml" class="cl-select" wire:model.live="xmlSourceFilter">
                        <option value="all">-</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-linked">Σύνδεση γέφυρας</label>
                    <select id="qu-linked" class="cl-select" wire:model.live="supplierLinkedFilter">
                        <option value="all">Όλα</option>
                        <option value="linked">Ναι</option>
                        <option value="unlinked">Όχι</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-price-update">Ενημέρωση πώλησης</label>
                    <select id="qu-price-update" class="cl-select" wire:model.live="priceUpdateFilter">
                        <option value="all">Όλα</option>
                        <option value="yes">Ναι</option>
                        <option value="no">Όχι</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-cost-update">Ενημέρωση αγοράς</label>
                    <select id="qu-cost-update" class="cl-select" wire:model.live="purchasePriceUpdateFilter">
                        <option value="all">Όλα</option>
                        <option value="yes">Ναι</option>
                        <option value="no">Όχι</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-status-update">Ενημέρωση κατάστασης</label>
                    <select id="qu-status-update" class="cl-select" wire:model.live="statusUpdateFilter">
                        <option value="all">Όλα</option>
                        <option value="yes">Ναι</option>
                        <option value="no">Όχι</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-prices">Τιμές</label>
                    <select id="qu-prices" class="cl-select" wire:model.live="pricePresenceFilter">
                        <option value="all">-</option>
                        <option value="with_price">Με τιμή</option>
                        <option value="without_price">Χωρίς τιμή</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-stock">Τεμάχια</label>
                    <select id="qu-stock" class="cl-select" wire:model.live="stockFilter">
                        <option value="all">-</option>
                        <option value="available">&gt; 0</option>
                        <option value="unavailable">0</option>
                        <option value="without_quantity">-</option>
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-stock-text">Λεκτικό στοκ</label>
                    <select id="qu-stock-text" class="cl-select" wire:model.live="stockTextFilter">
                        <option value="all">--κανένα--</option>
                        <option value="__blank">Κενό</option>
                        @foreach ($stockTexts as $stockText)
                            <option value="{{ $stockText }}">{{ $stockText }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cl-field">
                    <label class="cl-label" for="qu-misc">Διάφορα</label>
                    <select id="qu-misc" class="cl-select" wire:model.live="miscellaneousFilter">
                        <option value="all">-</option>
                        <option value="boxnow">BoxNow</option>
                        <option value="article11">Άρθρο 11</option>
                        <option value="with_image">Με εικόνα</option>
                        <option value="without_image">Χωρίς εικόνα</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="cl-action-bar">
            <div class="cl-toolbar">
                <button type="button" class="cl-btn primary" wire:click="editSelected">
                    <x-heroicon-o-pencil-square class="cl-btn-icon" />
                    Επεξεργασία επιλεγμένων
                </button>

                <button type="button" class="cl-btn green" wire:click="addSelectedToSupplierOrder">
                    <x-heroicon-o-plus-circle class="cl-btn-icon" />
                    Προσθήκη σε παραγγελία
                </button>

                <button
                    type="button"
                    class="cl-btn red"
                    x-on:click="if (! confirm('Να διαγραφούν τα επιλεγμένα προϊόντα;')) $event.stopImmediatePropagation()"
                    wire:click="deleteSelected"
                >
                    <x-heroicon-o-trash class="cl-btn-icon" />
                    Διαγραφή
                </button>
            </div>

            <div class="cl-toolbar">
                <button type="button" class="cl-btn" wire:click="exportSelected">
                    <x-heroicon-o-printer class="cl-btn-icon" />
                    Εκτύπωση επιλεγμένων
                </button>

                <button type="button" class="cl-btn" wire:click="exportSelected(true)">
                    <x-heroicon-o-check-circle class="cl-btn-icon" />
                    Εκτύπωση διαθέσιμων
                </button>
            </div>
        </section>

        <section class="cl-panel">
            <div class="cl-panel-head">
                <div class="cl-panel-title">
                    <x-heroicon-o-cube class="cl-btn-icon" />
                    <span>Προϊόντα</span>
                    <span class="cl-panel-count">{{ $productFirstItem }}-{{ $productLastItem }} / {{ $totalProducts }}</span>
                </div>

                <div class="cl-panel-actions">
                    <button type="button" class="cl-small-btn" wire:click="togglePageSelection">
                        <x-heroicon-o-check-circle class="cl-btn-icon" />
                        {{ $allPageSelected ? 'Αποεπιλογή σελίδας' : 'Επιλογή σελίδας' }}
                    </button>
                </div>
            </div>

            <div class="cl-table-wrap">
                <table class="cl-table">
                    <thead>
                        <tr>
                            <th class="cl-check-col">Επιλογή</th>
                            <th class="cl-id-col">ID</th>
                            <th class="cl-title-col">Τίτλος</th>
                            <th class="cl-status-col">Κατάσταση</th>
                            <th class="cl-code-col">Κωδικός</th>
                            <th class="cl-price-col">Τιμή πώλησης<br>ΜΕ ΦΠΑ</th>
                            <th class="cl-brand-col">Κατασκευαστής</th>
                            <th class="cl-supplier-col">Προμηθευτής</th>
                            <th class="cl-category-col">Κατηγορία</th>
                            <th class="cl-date-col">Προσθήκη</th>
                            <th class="cl-date-col">Επεξ/σία</th>
                            <th class="cl-date-col">Ενημ. προμηθευτή</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr wire:key="quick-update-product-{{ $product['id'] }}">
                                <td class="cl-check-col">
                                    <input
                                        id="qu-product-{{ $product['id'] }}"
                                        type="checkbox"
                                        class="cl-checkbox"
                                        value="{{ $product['id'] }}"
                                        wire:model.live="selectedProductIds"
                                    >
                                </td>
                                <td class="cl-id-col">{{ $product['display_id'] }}</td>
                                <td class="cl-title-col">
                                    <div class="cl-product-cell">
                                        <span class="cl-image-wrap">
                                            @if ($product['image'] !== '')
                                                <img src="{{ $product['image'] }}" alt="">
                                            @else
                                                <span class="cl-image-empty">NO IMG</span>
                                            @endif
                                        </span>

                                        <div class="cl-product-main">
                                            <span class="cl-product-title">{{ $product['title'] }}</span>
                                            <span class="cl-row-links">
                                                <a href="{{ $this->productEditUrl((int) $product['id']) }}">Πλήρης επεξεργασία</a>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="cl-status-col">
                                    <span class="cl-status-btn {{ $product['active'] ? 'is-on' : 'is-off' }}">
                                        {{ $product['active'] ? 'Ενεργό' : 'Ανενεργό' }}
                                    </span>
                                </td>
                                <td class="cl-code-col">
                                    <div class="cl-code-lines">
                                        <strong>{{ $product['code'] !== '' ? $product['code'] : '-' }}</strong>
                                        <span><span class="cl-muted">MPN:</span> {{ $product['mpn'] !== '' ? $product['mpn'] : '-' }}</span>
                                        <span><span class="cl-muted">EAN:</span> {{ $product['ean'] !== '' ? $product['ean'] : '-' }}</span>
                                    </div>
                                </td>
                                <td class="cl-price-col">{{ $product['price'] }}</td>
                                <td class="cl-brand-col">{{ $product['manufacturer'] }}</td>
                                <td class="cl-supplier-col">{{ $product['supplier'] }}</td>
                                <td class="cl-category-col">{{ $product['category'] }}</td>
                                <td class="cl-date-col">{{ $product['created_at'] }}</td>
                                <td class="cl-date-col">{{ $product['updated_at'] }}</td>
                                <td class="cl-date-col">{{ $product['supplier_updated_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="cl-empty-row" colspan="12">Δεν βρέθηκαν προϊόντα.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="cl-pagination">
                <div class="cl-pagination-summary">
                    Εμφάνιση {{ $productFirstItem }} έως {{ $productLastItem }} από {{ $totalProducts }} εγγραφές
                </div>

                <div class="cl-footer-actions">
                    <span class="cl-muted">Ανά σελίδα</span>
                    <select class="cl-select cl-per-page" wire:model.live="perPage" aria-label="Ανά σελίδα">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="300">300</option>
                    </select>

                    <button type="button" class="cl-page-btn" wire:click="previousProductPage" @disabled($productCurrentPage <= 1)>
                        <x-heroicon-o-chevron-left class="cl-btn-icon" />
                    </button>

                    @if ($pageWindowStart > 1)
                        <button type="button" class="cl-page-number" wire:click="gotoProductPage(1)">1</button>
                        @if ($pageWindowStart > 2)
                            <span class="cl-page-dots">...</span>
                        @endif
                    @endif

                    @for ($page = $pageWindowStart; $page <= $pageWindowEnd; $page++)
                        <button
                            type="button"
                            class="cl-page-number {{ $page === $productCurrentPage ? 'is-active' : '' }}"
                            wire:click="gotoProductPage({{ $page }})"
                            @disabled($page === $productCurrentPage)
                        >
                            {{ $page }}
                        </button>
                    @endfor

                    @if ($pageWindowEnd < $productLastPage)
                        @if ($pageWindowEnd < $productLastPage - 1)
                            <span class="cl-page-dots">...</span>
                        @endif
                        <button type="button" class="cl-page-number" wire:click="gotoProductPage({{ $productLastPage }})">{{ $productLastPage }}</button>
                    @endif

                    <button type="button" class="cl-page-btn" wire:click="nextProductPage" @disabled($productCurrentPage >= $productLastPage)>
                        <x-heroicon-o-chevron-right class="cl-btn-icon" />
                    </button>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
