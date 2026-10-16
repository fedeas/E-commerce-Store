<x-filament-panels::page>
    @php
        $totalProducts = $totalProducts ?? (property_exists($this, 'totalProducts') ? $this->totalProducts : 0);
        $productFirstItem = $productFirstItem ?? (property_exists($this, 'productFirstItem') ? $this->productFirstItem : 0);
        $productLastItem = $productLastItem ?? (property_exists($this, 'productLastItem') ? $this->productLastItem : 0);
        $productLastPage = $productLastPage ?? (property_exists($this, 'productLastPage') ? $this->productLastPage : 1);
        $productCurrentPage = $productCurrentPage ?? (property_exists($this, 'productCurrentPage') ? $this->productCurrentPage : 1);
        $productPerPageValue = $productPerPageValue ?? (property_exists($this, 'productPerPageValue') ? $this->productPerPageValue : 50);
        $categories = $categories ?? [];
        $products = $products ?? [];
        $selectedCategory = $selectedCategory ?? [];
        $currentPath = $currentPath ?? 'Αρχή';

        $isEnabled = function ($value): bool {
            return in_array($value, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
        };

        $categoriesCount = count($categories);
        $productsCount = count($products);

        $selectedCatalogType = property_exists($this, 'selectedCatalogType') ? $this->selectedCatalogType : null;
        $selectedCatalogId = property_exists($this, 'selectedCatalogId') ? (int) $this->selectedCatalogId : 0;

        $selectedCategoryRow = $selectedCatalogType === 'category'
            ? collect($categories)->first(fn ($row) => (int) data_get($row, 'id', 0) === $selectedCatalogId)
            : null;

        $selectedProductRow = $selectedCatalogType === 'product'
            ? collect($products)->first(fn ($row) => (int) data_get($row, 'id', 0) === $selectedCatalogId)
            : null;
        $totalRows = $categoriesCount + $productsCount;
    @endphp

    <div x-data="{ detailsOpen: true }" class="cl-admin-simple">
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

            .cl-admin-simple {
                --cl-bg: #edf3f8;
                --cl-panel: #ffffff;
                --cl-border: #c5d3df;
                --cl-border-dark: #9fb6c9;
                --cl-blue: #2e6fa7;
                --cl-blue-dark: #245b8b;
                --cl-blue-light: #dceefa;
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
                font-size: 16px;
                flex-shrink: 0;
            }

            .cl-title-text {
                min-width: 0;
            }

            .cl-title-text h1 {
                margin: 0;
                font-size: 20px;
                font-weight: 800;
                color: var(--cl-text);
                line-height: 1.1;
            }

            .cl-title-text p {
                margin: 3px 0 0 0;
                font-size: 13px;
                color: var(--cl-muted);
            }

            .cl-breadcrumb {
                font-size: 13px;
                font-weight: 700;
                color: #38566e;
                background: rgba(255,255,255,.72);
                border: 1px solid #bdd2e3;
                border-radius: 999px;
                padding: 7px 11px;
                white-space: nowrap;
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

            .cl-toolbar-left,
            .cl-toolbar-right {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
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
                font-weight: 700;
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

            .cl-filter-bar {
                display: grid;
                grid-template-columns: 2fr 2fr 1fr 1.4fr;
                gap: 8px;
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
                font-weight: 800;
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
                border-color: #2e6fa7;
                box-shadow: 0 0 0 3px rgba(46,111,167,.13);
            }

            .cl-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 320px;
                gap: 12px;
                align-items: start;
            }

            .cl-left {
                min-width: 0;
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
                font-weight: 800;
                color: #18364f;
            }

            .cl-panel-count {
                padding: 3px 8px;
                border-radius: 999px;
                background: #ffffff;
                border: 1px solid #bdd2e3;
                font-size: 12px;
                font-weight: 800;
                color: #38566e;
            }

            .cl-panel-actions {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }

            .cl-table-wrap {
                overflow-x: auto;
                background: #ffffff;
            }


            .cl-image-cell {
                width: 74px;
                min-width: 74px;
                text-align: center;
                vertical-align: middle;
                background: #f8fbfe;
            }

            .cl-product-thumb,
            .cl-thumb-placeholder,
            .cl-folder-thumb {
                width: 46px;
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #c7d8e6;
                border-radius: 10px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
            }

            .cl-product-thumb {
                object-fit: contain;
                padding: 3px;
            }

            .cl-thumb-placeholder {
                color: #7b8da0;
                font-size: 10px;
                font-weight: 900;
                letter-spacing: .04em;
                background: linear-gradient(180deg, #ffffff 0%, #eef5fa 100%);
            }

            .cl-folder-thumb {
                color: #2c6f9f;
                font-size: 22px;
                background: linear-gradient(180deg, #ffffff 0%, #eef7fd 100%);
            }


            .cl-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 1660px;
            }

            .cl-table th {
                background: #edf6fc;
                color: #28455e;
                font-size: 12px;
                font-weight: 800;
                padding: 8px 8px;
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

            .cl-row-category td {
                background: #fffaf0 !important;
            }

            .cl-row-category:hover td {
                background: #fff3d9 !important;
            }

            .cl-id {
                color: #0f2540;
                font-weight: 900;
                white-space: nowrap;
            }

            .cl-type {
                width: 58px;
                text-align: center;
                font-size: 16px;
            }

            .cl-name-cell {
                min-width: 410px;
            }

            .cl-category-name,
            .cl-product-name {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                min-width: 0;
            }

            .cl-folder,
            .cl-box {
                flex-shrink: 0;
                font-size: 15px;
                margin-top: 1px;
            }

            .cl-folder {
                color: var(--cl-yellow);
            }

            .cl-main-name {
                display: grid;
                gap: 3px;
                min-width: 0;
            }

            .cl-main-name strong,
            .cl-product-title-link {
                color: #14283a;
                font-size: 13px;
                font-weight: 900;
                line-height: 1.3;
                text-decoration: none;
            }

            .cl-product-title-link:hover {
                color: #0f5f99;
                text-decoration: underline;
            }

            .cl-main-name span {
                font-size: 11px;
                color: #64748b;
                line-height: 1.25;
            }

            .cl-status-btn {
                min-width: 58px;
                height: 26px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 900;
                border: 1px solid transparent;
                line-height: 1;
                cursor: default;
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

            button.cl-status-dot:focus-visible {
                outline: 2px solid #2e6fa7;
                outline-offset: 2px;
            }

            button.cl-status-dot:disabled {
                cursor: wait;
                opacity: .55;
            }

            .cl-price {
                font-weight: 900;
                color: #17324a;
                white-space: nowrap;
            }

            .cl-dash {
                color: #94a3b8;
                font-weight: 800;
            }

            .cl-summary {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
                padding: 8px 10px;
                background: #f8fbfe;
                border-top: 1px solid #e1edf6;
            }

            .cl-pill {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 9px;
                border-radius: 999px;
                background: #ffffff;
                border: 1px solid #c8d8e5;
                font-size: 12px;
                font-weight: 800;
                color: #38566e;
            }

            .cl-legend {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                font-weight: 700;
                color: #52687d;
            }

            .cl-legend-item {
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            .cl-product-edit-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 6px 10px;
                border-radius: 8px;
                border: 1px solid #9db6cf;
                background: #eef7ff;
                color: #0f4f8a;
                font-size: 12px;
                font-weight: 800;
                text-decoration: none;
                white-space: nowrap;
            }

            .cl-product-edit-button:hover {
                background: #dcefff;
                color: #0a3660;
            }

            .cl-side {
                border: 1px solid var(--cl-border-dark);
                background: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                position: sticky;
                top: 16px;
            }

            .cl-side-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                padding: 8px 10px;
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
                border-bottom: 1px solid var(--cl-border-dark);
            }

            .cl-side-title {
                font-size: 14px;
                font-weight: 900;
                color: #18364f;
                line-height: 1.2;
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
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
                font-size: 12px;
            }

            .cl-info-row span:first-child {
                color: #64748b;
                font-weight: 700;
            }

            .cl-info-row span:last-child {
                color: #102033;
                font-weight: 900;
                text-align: right;
            }

            .cl-site-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                padding: 7px 8px;
                border: 1px solid #d6e3ed;
                border-radius: 7px;
                background: #ffffff;
                font-size: 12px;
                font-weight: 700;
            }

            .cl-check {
                width: 15px;
                height: 15px;
                border-radius: 4px;
                background: #dff5e8;
                border: 1px solid #80ca98;
                position: relative;
                flex-shrink: 0;
            }

            .cl-check::after {
                content: '';
                position: absolute;
                left: 4px;
                top: 1px;
                width: 5px;
                height: 9px;
                border-right: 2px solid #159447;
                border-bottom: 2px solid #159447;
                transform: rotate(45deg);
            }

            .cl-image-box {
                height: 110px;
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

            
            .cl-row-category,
            .cl-row-product {
                cursor: pointer;
            }

            .cl-row-category:hover td,
            .cl-row-product:hover td {
                background: #edf4fa !important;
            }

            .cl-row-category.is-selected td,
            .cl-row-product.is-selected td {
                background: #dfeaf3 !important;
            }

            
            .cl-side-empty {
                padding: 12px;
                border: 1px dashed #b6c8d8;
                border-radius: 8px;
                background: #f8fbfe;
                color: #52687d;
                font-size: 13px;
                font-weight: 700;
                line-height: 1.45;
            }

            .cl-product-pagination-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                padding: 10px;
                border-bottom: 1px solid #c5d3df;
                background: #f8fbfe;
            }

            .cl-product-pagination-summary {
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

            .cl-product-per-page {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: #607080;
                font-size: 12px;
                font-weight: 900;
            }

            .cl-product-per-page select {
                height: 34px;
                min-width: 90px;
                border: 1px solid #9fb6c9;
                border-radius: 8px;
                background: #ffffff;
                color: #102033;
                font-size: 12px;
                font-weight: 900;
                padding: 0 8px;
                outline: none;
            }

            .cl-product-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                padding: 12px;
                border-top: 1px solid #c5d3df;
                background: linear-gradient(180deg, #f8fbfe 0%, #edf5fb 100%);
            }

            .cl-product-pagination-controls {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 6px;
                flex-wrap: wrap;
            }

            .cl-product-page-btn,
            .cl-product-page-number {
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

            .cl-product-page-btn {
                width: auto;
                padding: 0 11px;
            }

            .cl-product-page-number:hover,
            .cl-product-page-btn:hover {
                background: linear-gradient(180deg, #ffffff 0%, #dceefa 100%);
                border-color: #7fa6c7;
                color: #0f2540;
            }

            .cl-product-page-number.is-active {
                background: linear-gradient(180deg, #367db8 0%, #245f95 100%);
                border-color: #1e507d;
                color: #ffffff;
                box-shadow: 0 1px 4px rgba(30, 80, 125, .25);
            }

            .cl-product-page-btn:disabled,
            .cl-product-page-number:disabled {
                opacity: .45;
                cursor: not-allowed;
                background: #eef3f7;
                color: #7b8b9a;
                box-shadow: none;
            }

            .cl-product-page-dots {
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

            @media (max-width: 900px) {
                .cl-filter-bar {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media (max-width: 640px) {
                .cl-top-title,
                .cl-toolbar {
                    align-items: stretch;
                    flex-direction: column;
                }

                .cl-toolbar-left,
                .cl-toolbar-right,
                .cl-btn {
                    width: 100%;
                }

                .cl-filter-bar {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title-left">
                <div class="cl-title-text">
                    <h1>Κατηγορίες / Προϊόντα</h1>
                    <p>Ενιαία προβολή καταλόγου με κατηγορίες, υποκατηγορίες και προϊόντα στο ίδιο table.</p>
                </div>
            </div>

            <div class="cl-breadcrumb">
                Κατάλογος / {{ $currentPath }}
            </div>
        </section>

        <section class="cl-toolbar">
            <div class="cl-toolbar-left">
                @if(method_exists($this, 'openCategoryModal'))
                    <button type="button" wire:click="openCategoryModal" class="cl-btn primary">+ Νέα Κατηγορία</button>
                @else
                    <button type="button" class="cl-btn primary">+ Νέα Κατηγορία</button>
                @endif

                @if(method_exists($this, 'openProductModal'))
                    <button type="button" wire:click="openProductModal" class="cl-btn primary">+ Νέο Προϊόν</button>
                @else
                    <button type="button" class="cl-btn primary">+ Νέο Προϊόν</button>
                @endif

                @if(method_exists($this, 'goToParent'))
                    <button type="button" wire:click="goToParent" class="cl-btn">← Πίσω</button>
                @else
                    <button type="button" class="cl-btn">← Πίσω</button>
                @endif
            </div>

            <div class="cl-toolbar-right">
                @if(method_exists($this, 'exportScope'))
                    <button type="button" wire:click="exportScope" class="cl-btn green">Εξαγωγή ειδών</button>
                @elseif(method_exists($this, 'exportProductsCsv'))
                    <button type="button" wire:click="exportProductsCsv" class="cl-btn green">Εξαγωγή ειδών</button>
                @else
                    <button type="button" class="cl-btn green">Εξαγωγή ειδών</button>
                @endif

                @if(method_exists($this, 'openImportModal'))
                    <button type="button" wire:click="openImportModal" class="cl-btn">Ενημέρωση από Excel</button>
                @else
                    <button type="button" class="cl-btn">Ενημέρωση από Excel</button>
                @endif
            </div>
        </section>

        <section class="cl-filter-bar">
            <div class="cl-field">
                <label class="cl-label" for="categories_products_blade_categorySearch_1">Αναζήτηση Καταλόγου</label>
                @if(property_exists($this, 'categorySearch'))
                    <input type="text" wire:model.live.debounce.350ms="categorySearch" class="cl-input" placeholder="Όνομα κατηγορίας / διαδρομή" id="categories_products_blade_categorySearch_1" name="categorySearch">
                @else
                    <input type="text" class="cl-input" placeholder="Όνομα κατηγορίας / διαδρομή" id="categories_products_blade_field_2" name="field">
                @endif
            </div>

            <div class="cl-field">
                <label class="cl-label" for="categories_products_blade_productSearch_3">Αναζήτηση Προϊόντος</label>
                @if(property_exists($this, 'productSearch'))
                    <input type="text" wire:model.live.debounce.350ms="productSearch" class="cl-input" placeholder="Τίτλος / Κωδικός / MPN / EAN" id="categories_products_blade_productSearch_3" name="productSearch">
                @else
                    <input type="text" class="cl-input" placeholder="Τίτλος / Κωδικός / MPN / EAN" id="categories_products_blade_MPN_EAN_4" name="MPN_EAN">
                @endif
            </div>

            <div class="cl-field">
                <label class="cl-label" for="categories_products_blade_select_5_5">Κατάσταση</label>
                <select class="cl-select" id="categories_products_blade_select_5_5" name="select_5">
                    <option>Όλα</option>
                    <option>Ενεργά</option>
                    <option>Ανενεργά</option>
                </select>
            </div>

            <div class="cl-field">
                <label class="cl-label" for="categories_products_blade_text_6_6">Τρέχουσα Θέση</label>
                <input type="text" value="{{ $currentPath }}" class="cl-input" readonly id="categories_products_blade_text_6_6" name="text_6">
            </div>
        </section>

        <div class="cl-layout">
            <div class="cl-left">
                <section class="cl-panel">
                    <div class="cl-panel-head">
                        <div class="cl-panel-title">
                            <span>Κατάλογος</span>
                            <span class="cl-panel-count">{{ $totalRows }}</span>
                        </div>

                        <div class="cl-panel-actions">
                            <div class="cl-legend">
                                <span class="cl-legend-item">
                                    <span class="cl-status-dot yes is-active"></span>
                                    <span>Ναι</span>
                                </span>

                                <span class="cl-legend-item">
                                    <span class="cl-status-dot no is-active"></span>
                                    <span>Όχι</span>
                                </span>
                            </div>

                            <span class="cl-pill">Κατηγορίες: {{ $categoriesCount }}</span>
                            <span class="cl-pill">Προϊόντα: {{ $productsCount }}</span>
                        </div>
                    </div>

                    <div class="cl-product-pagination-toolbar">
                    <div class="cl-product-pagination-summary">
                        Προϊόντα: {{ $productFirstItem }}-{{ $productLastItem }} από {{ $totalProducts }}
                    </div>

                    <label class="cl-product-per-page" for="categories_products_blade_productPerPage_7">
                        Ανά σελίδα
                        <select wire:model.live="productPerPage" id="categories_products_blade_productPerPage_7" name="productPerPage">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="300">300</option>
                        </select>
                    </label>
                </div>

                <div class="cl-table-wrap">
                        <table class="cl-table">
                            <thead>
                                <tr>
                                    <th>Εικόνα</th>
                                    <th>ID</th>
                                    <th>Κατηγορία / Προϊόν</th>
                                    <th>Κατάσταση</th>
                                    <th>Κωδικός</th>
                                    <th>Τιμή</th>
                                    <th>Κατασκευαστής</th>
                                    <th>Προμηθευτής</th>
                                    <th>Κατηγορία</th>
                                    <th>Προσθήκη</th>
                                    <th>Ενημέρωση</th>
                                    <th>XML Feed</th>
                                    <th>XML διαθέσιμα</th>
                                    <th>Φίλτρο</th>
                                    <th>Ενεργό</th>
                                    <th>Boxnow</th>
                                    <th>Άρθρο 11</th>
                                    <th>Σειρά</th>
                                    
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($categories as $row)
                                    @php
                                        $rowId = (int) data_get($row, 'id', 0);
                                        $rowName = data_get($row, 'name', '-');
                                        $rowPath = data_get($row, 'path', $currentPath);
                                        $rowSort = data_get($row, 'sort_order', '-');

                                        $categoryStatus = $isEnabled(data_get($row, 'status', false));
                                        $categoryXmlFeed = $isEnabled(data_get($row, 'xml_feed', false));
                                        $categoryXmlOnly = $isEnabled(data_get($row, 'xml_only', false));
                                        $categoryFilter = $isEnabled(data_get($row, 'filter', false));
                                        $categoryBoxnow = $isEnabled(data_get($row, 'boxnow', false));
                                        $categoryArticle11 = $isEnabled(data_get($row, 'article11', false));
                                    @endphp

                                    <tr class="cl-row-category {{ $selectedCatalogType === 'category' && $selectedCatalogId === $rowId ? 'is-selected' : '' }}" wire:click="handleCategoryRowClick({{ $rowId }})">

                                    <td class="cl-image-cell">
                                        <span class="cl-folder-thumb" title="Κατηγορία">📁</span>
                                    </td>
                                        <td class="cl-id">{{ $rowId ?: '-' }}</td>

                                        <td class="cl-name-cell">
                                            <div class="cl-category-name">
                                                <div class="cl-main-name">
                                                    <strong>{{ $rowName }}</strong>
                                                    <span>{{ $rowPath ?: 'Κατηγορία / Υποκατηγορία' }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        
                                        <td>
                                            <span class="cl-status-pair">
                                                <button
                                                    type="button"
                                                    wire:click.stop="toggleCategoryFlag({{ (int) $rowId }}, 'status', true)"
                                                    wire:loading.attr="disabled"
                                                    class="cl-status-dot yes {{ $categoryStatus ? 'is-active' : '' }}"
                                                    title="Ναι"
                                                    aria-label="Κατάσταση Ναι"
                                                ></button>

                                                <button
                                                    type="button"
                                                    wire:click.stop="toggleCategoryFlag({{ (int) $rowId }}, 'status', false)"
                                                    wire:loading.attr="disabled"
                                                    class="cl-status-dot no {{ ! $categoryStatus ? 'is-active' : '' }}"
                                                    title="Όχι"
                                                    aria-label="Κατάσταση Όχι"
                                                ></button>
                                            </span>
                                        </td>


                                        <td><span class="cl-dash">-</span></td>
                                        <td><span class="cl-dash">-</span></td>
                                        <td><span class="cl-dash">-</span></td>
                                        <td><span class="cl-dash">-</span></td>
                                        <td>{{ $rowPath }}</td>
                                        <td><span class="cl-dash">-</span></td>
                                        <td><span class="cl-dash">-</span></td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleCategoryFlag') && $rowId)
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'xml_feed', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $categoryXmlFeed ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'xml_feed', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $categoryXmlFeed ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $categoryXmlFeed ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $categoryXmlFeed ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleCategoryFlag') && $rowId)
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'xml_only', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $categoryXmlOnly ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'xml_only', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $categoryXmlOnly ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $categoryXmlOnly ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $categoryXmlOnly ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleCategoryFlag') && $rowId)
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'filter', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $categoryFilter ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'filter', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $categoryFilter ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $categoryFilter ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $categoryFilter ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleCategoryFlag') && $rowId)
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'status', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $categoryStatus ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'status', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $categoryStatus ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $categoryStatus ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $categoryStatus ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleCategoryFlag') && $rowId)
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'boxnow', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $categoryBoxnow ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'boxnow', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $categoryBoxnow ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $categoryBoxnow ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $categoryBoxnow ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleCategoryFlag') && $rowId)
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'article11', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $categoryArticle11 ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleCategoryFlag({{ $rowId }}, 'article11', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $categoryArticle11 ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $categoryArticle11 ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $categoryArticle11 ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td class="cl-id">{{ $rowSort }}</td>
                                    </tr>
                                @endforeach

                                @foreach($products as $product)
                                    @php
                                        $productId = (int) data_get($product, 'id', 0);
                                        $productStatus = $isEnabled(data_get($product, 'status', false));
                                        $productId = (int) data_get($product, 'id', 0);
                                        $productActive = $isEnabled(data_get($product, 'active', data_get($product, 'status', false)));
                                        $productBoxnow = $isEnabled(data_get($product, 'boxnow', false));
                                        $productArticle11 = $isEnabled(data_get($product, 'article11', false));
                                        $editUrl = $productId ? $this->productEditUrl((int) $productId) : '#';
                                    @endphp

                                    <tr class="cl-row-product {{ $selectedCatalogType === 'product' && $selectedCatalogId === $productId ? 'is-selected' : '' }}" wire:click="selectCatalogItem('product', {{ $productId }})">

                                    @php
                                        $productImage = $product['image'] ?? '';
                                    @endphp

                                    <td class="cl-image-cell">
                                        @if($productImage !== '')
                                            <img
                                                src="{{ $productImage }}"
                                                class="cl-product-thumb"
                                                alt="{{ $product['title'] ?? 'Product image' }}"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                                            >
                                            <span class="cl-thumb-placeholder" style="display:none;">IMG</span>
                                        @else
                                            <span class="cl-thumb-placeholder">IMG</span>
                                        @endif
                                    </td>
                                        <td class="cl-id">{{ $productId ?: '-' }}</td>

                                        <td class="cl-name-cell">
                                            <div class="cl-product-name">
                                                <div class="cl-main-name">
                                                    <a href="{{ $editUrl }}" class="cl-product-title-link">
                                                        {{ data_get($product, 'title', '-') }}
                                                    </a>

                                                    <span>
                                                        MPN: {{ data_get($product, 'mpn') ?: '-' }}
                                                        |
                                                        EAN: {{ data_get($product, 'ean') ?: '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        
                                        <td>
                                            <span class="cl-status-pair">
                                                <button
                                                    type="button"
                                                    wire:click.stop="toggleProductFlag({{ $productId }}, 'active', true)"
                                                    wire:loading.attr="disabled"
                                                    class="cl-status-dot yes {{ $productStatus ? 'is-active' : '' }}"
                                                    title="Ναι"
                                                    aria-label="Κατάσταση Ναι"
                                                ></button>

                                                <button
                                                    type="button"
                                                    wire:click.stop="toggleProductFlag({{ $productId }}, 'active', false)"
                                                    wire:loading.attr="disabled"
                                                    class="cl-status-dot no {{ ! $productStatus ? 'is-active' : '' }}"
                                                    title="Όχι"
                                                    aria-label="Κατάσταση Όχι"
                                                ></button>
                                            </span>
                                        </td>


                                        <td>{{ data_get($product, 'code', '-') }}</td>
                                        <td class="cl-price">{{ data_get($product, 'price', '-') }}</td>
                                        <td>{{ data_get($product, 'manufacturer', '-') }}</td>
                                        <td>{{ data_get($product, 'supplier', '-') }}</td>
                                        <td>{{ data_get($product, 'category', '-') }}</td>
                                        <td>{{ data_get($product, 'created_at', '-') }}</td>
                                        <td>{{ data_get($product, 'updated_at', '-') }}</td>

                                        <td><span class="cl-dash">-</span></td>
                                        <td><span class="cl-dash">-</span></td>
                                        <td><span class="cl-dash">-</span></td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleProductFlag') && $productId)
                                                    <button type="button" wire:click.stop="toggleProductFlag({{ $productId }}, 'active', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $productActive ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleProductFlag({{ $productId }}, 'active', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $productActive ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $productActive ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $productActive ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleProductFlag') && $productId)
                                                    <button type="button" wire:click.stop="toggleProductFlag({{ $productId }}, 'boxnow', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $productBoxnow ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleProductFlag({{ $productId }}, 'boxnow', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $productBoxnow ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $productBoxnow ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $productBoxnow ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td>
                                            <span class="cl-status-pair">
                                                @if(method_exists($this, 'toggleProductFlag') && $productId)
                                                    <button type="button" wire:click.stop="toggleProductFlag({{ $productId }}, 'article11', true)" wire:loading.attr="disabled" class="cl-status-dot yes {{ $productArticle11 ? 'is-active' : '' }}" title="Ναι"></button>
                                                    <button type="button" wire:click.stop="toggleProductFlag({{ $productId }}, 'article11', false)" wire:loading.attr="disabled" class="cl-status-dot no {{ ! $productArticle11 ? 'is-active' : '' }}" title="Όχι"></button>
                                                @else
                                                    <span class="cl-status-dot yes {{ $productArticle11 ? 'is-active' : '' }}"></span>
                                                    <span class="cl-status-dot no {{ ! $productArticle11 ? 'is-active' : '' }}"></span>
                                                @endif
                                            </span>
                                        </td>

                                        <td><span class="cl-dash">-</span></td>
                                    </tr>
                                @endforeach

                                @if($totalRows === 0)
                                    <tr>
                                        <td colspan="19" class="text-center text-slate-500">
                                            Δεν υπάρχουν κατηγορίες ή προϊόντα στο τρέχον scope.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                @if($productLastPage > 1)
                    @php
                        $startProductPage = max(1, $productCurrentPage - 2);
                        $endProductPage = min($productLastPage, $productCurrentPage + 2);
                    @endphp

                    <div class="cl-product-pagination">
                        <div class="cl-product-pagination-summary">
                            Σελίδα {{ $productCurrentPage }} από {{ $productLastPage }}
                        </div>

                        <div class="cl-product-pagination-controls">
                            <button
                                type="button"
                                wire:click="previousProductPage"
                                wire:loading.attr="disabled"
                                class="cl-product-page-btn"
                                @disabled($productCurrentPage <= 1)
                            >
                                ‹ Προηγούμενη
                            </button>

                            @if($startProductPage > 1)
                                <button type="button" wire:click="gotoProductPage(1)" wire:loading.attr="disabled" class="cl-product-page-number">1</button>

                                @if($startProductPage > 2)
                                    <span class="cl-product-page-dots">...</span>
                                @endif
                            @endif

                            @for($page = $startProductPage; $page <= $endProductPage; $page++)
                                <button
                                    type="button"
                                    wire:click="gotoProductPage({{ $page }})"
                                    wire:loading.attr="disabled"
                                    class="cl-product-page-number {{ $page === $productCurrentPage ? 'is-active' : '' }}"
                                    @disabled($page === $productCurrentPage)
                                >
                                    {{ $page }}
                                </button>
                            @endfor

                            @if($endProductPage < $productLastPage)
                                @if($endProductPage < $productLastPage - 1)
                                    <span class="cl-product-page-dots">...</span>
                                @endif

                                <button type="button" wire:click="gotoProductPage({{ $productLastPage }})" wire:loading.attr="disabled" class="cl-product-page-number">{{ $productLastPage }}</button>
                            @endif

                            <button
                                type="button"
                                wire:click="nextProductPage"
                                wire:loading.attr="disabled"
                                class="cl-product-page-btn"
                                @disabled($productCurrentPage >= $productLastPage)
                            >
                                Επόμενη ›
                            </button>
                        </div>
                    </div>
                @endif



                    <div class="cl-summary">
                        <span class="cl-pill">Σύνολο γραμμών: {{ $totalRows }}</span>
                        <span class="cl-pill">Κατηγορίες: {{ $categoriesCount }}</span>
                        <span class="cl-pill">Προϊόντα: {{ $productsCount }}</span>
                        <span class="cl-pill">Scope: {{ $currentPath }}</span>
                    </div>
                </section>
            </div>

            <aside class="cl-side">
                <div class="cl-side-head">
                    <div class="cl-side-title">
                        @if($selectedProductRow)
                            Προϊόν
                        @elseif($selectedCategoryRow)
                            Κατηγορία / Υποκατηγορία
                        @else
                            Πληροφορίες
                        @endif
                    </div>

                    <button type="button" class="cl-small-btn" @click="detailsOpen = !detailsOpen">
                        <span x-text="detailsOpen ? 'Κλείσιμο' : 'Άνοιγμα'"></span>
                    </button>
                </div>

                <div x-show="detailsOpen" x-transition class="cl-side-body">
                    @if($selectedCategoryRow)
                        @php
                            $sideCategoryId = (int) data_get($selectedCategoryRow, 'id', 0);
                            $sideCategoryName = data_get($selectedCategoryRow, 'name', '-');
                            $sideCategoryStatus = $isEnabled(data_get($selectedCategoryRow, 'status', false));
                            $sideCategoryXmlFeed = $isEnabled(data_get($selectedCategoryRow, 'xml_feed', false));
                            $sideCategoryXmlOnly = $isEnabled(data_get($selectedCategoryRow, 'xml_only', false));
                            $sideCategoryFilter = $isEnabled(data_get($selectedCategoryRow, 'filter', false));
                            $sideCategoryBoxnow = $isEnabled(data_get($selectedCategoryRow, 'boxnow', false));
                            $sideCategoryArticle11 = $isEnabled(data_get($selectedCategoryRow, 'article11', false));
                        @endphp

                        <div class="cl-side-actions">
                            <button type="button" class="cl-small-btn primary">Επεξεργασία</button>
                            <button type="button" class="cl-small-btn red">Διαγραφή</button>
                            <button type="button" class="cl-small-btn">Μετακίνηση</button>

                            @if(method_exists($this, 'selectCategory') && $sideCategoryId)
                                <button type="button" wire:click="selectCategory({{ $sideCategoryId }})" class="cl-small-btn">Άνοιγμα</button>
                            @endif
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Περιγραφή</div>

                            <div class="cl-info-box-body">
                                <div class="text-sm font-black text-slate-800 leading-snug">
                                    {{ $sideCategoryName }}
                                </div>

                                <div class="text-xs font-bold text-slate-500">
                                    ID: {{ $sideCategoryId }} | Scope: {{ $currentPath }}
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Πληροφορίες κατηγορίας</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>ID</span>
                                    <span>{{ $sideCategoryId }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Όνομα</span>
                                    <span>{{ $sideCategoryName }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Κατάσταση</span>
                                    <span>{{ $sideCategoryStatus ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Σειρά</span>
                                    <span>{{ data_get($selectedCategoryRow, 'sort_order', '-') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Ρυθμίσεις</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>XML Feed</span>
                                    <span>{{ $sideCategoryXmlFeed ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>XML μόνο διαθέσιμα</span>
                                    <span>{{ $sideCategoryXmlOnly ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Φίλτρο</span>
                                    <span>{{ $sideCategoryFilter ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Boxnow</span>
                                    <span>{{ $sideCategoryBoxnow ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Άρθρο 11</span>
                                    <span>{{ $sideCategoryArticle11 ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Εικόνα</div>

                            <div class="cl-info-box-body">
                                <div class="cl-image-box">
                                    Δεν υπάρχει εικόνα
                                </div>
                            </div>
                        </div>
                    @elseif($selectedProductRow)
                        @php
                            $sideProductId = (int) data_get($selectedProductRow, 'id', 0);
                            $sideProductTitle = data_get($selectedProductRow, 'title', '-');
                            $sideProductStatus = $isEnabled(data_get($selectedProductRow, 'status', false));
                            $sideProductActive = $isEnabled(data_get($selectedProductRow, 'active', data_get($selectedProductRow, 'status', false)));
                            $sideProductBoxnow = $isEnabled(data_get($selectedProductRow, 'boxnow', false));
                            $sideProductArticle11 = $isEnabled(data_get($selectedProductRow, 'article11', false));
                            $sideProductEditUrl = $sideProductId ? $this->productEditUrl((int) $sideProductId) : '#';
                        @endphp

                        <div class="cl-side-actions">
                            <a href="{{ $sideProductEditUrl }}" class="cl-small-btn primary">Επεξεργασία</a>
                            <button type="button" class="cl-small-btn">Προβολή</button>
                            <button type="button" class="cl-small-btn">Αντιγραφή</button>
                            <button type="button" class="cl-small-btn red">Διαγραφή</button>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Περιγραφή</div>

                            <div class="cl-info-box-body">
                                <div class="text-sm font-black text-slate-800 leading-snug">
                                    {{ $sideProductTitle }}
                                </div>

                                <div class="text-xs font-bold text-slate-500">
                                    ID: {{ $sideProductId }} | Κωδικός: {{ data_get($selectedProductRow, 'code', '-') }}
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Πληροφορίες προϊόντος</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>ID</span>
                                    <span>{{ $sideProductId }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Κωδικός</span>
                                    <span>{{ data_get($selectedProductRow, 'code', '-') }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Τιμή</span>
                                    <span>{{ data_get($selectedProductRow, 'price', '-') }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Κατάσταση</span>
                                    <span>{{ $sideProductStatus ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Ενεργό</span>
                                    <span>{{ $sideProductActive ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Κωδικοί</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>MPN</span>
                                    <span>{{ data_get($selectedProductRow, 'mpn') ?: '-' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>EAN</span>
                                    <span>{{ data_get($selectedProductRow, 'ean') ?: '-' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Κατασκευαστής</span>
                                    <span>{{ data_get($selectedProductRow, 'manufacturer', '-') }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Προμηθευτής</span>
                                    <span>{{ data_get($selectedProductRow, 'supplier', '-') }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Κατηγορία</span>
                                    <span>{{ data_get($selectedProductRow, 'category', '-') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Ρυθμίσεις</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>Boxnow</span>
                                    <span>{{ $sideProductBoxnow ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Άρθρο 11</span>
                                    <span>{{ $sideProductArticle11 ? 'ΝΑΙ' : 'ΟΧΙ' }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Προσθήκη</span>
                                    <span>{{ data_get($selectedProductRow, 'created_at', '-') }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Ενημέρωση</span>
                                    <span>{{ data_get($selectedProductRow, 'updated_at', '-') }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="cl-side-empty">
                            Πάτα μία φορά σε κατηγορία ή προϊόν για να εμφανιστούν εδώ οι πληροφορίες.
                            <br>
                            Δεύτερο click στην ίδια κατηγορία μπαίνει μέσα στις υποκατηγορίες / προϊόντα.
                        </div>

                        <div class="cl-info-box">
                            <div class="cl-info-box-title">Τρέχον scope</div>

                            <div class="cl-info-box-body">
                                <div class="cl-info-row">
                                    <span>Κατηγορίες</span>
                                    <span>{{ $categoriesCount }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Προϊόντα</span>
                                    <span>{{ $productsCount }}</span>
                                </div>

                                <div class="cl-info-row">
                                    <span>Θέση</span>
                                    <span>{{ $currentPath }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

        </div>
    </div>
</x-filament-panels::page>
