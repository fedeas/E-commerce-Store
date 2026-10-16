<x-filament-panels::page>
    @php
        $featured = $featured ?? [];
        $products = $products ?? [];
        $section = $section ?? ['title' => 'Προτεινόμενα', 'type_label' => 'Προτεινόμενα', 'is_active' => true];
        $tableReady = $tableReady ?? false;
    @endphp

    <div class="cl-admin">
        <style>
            main.fi-main, .fi-main, .fi-page, .fi-page-main, .fi-page-content, .fi-page-header-main-ctn { max-width:100% !important; width:100% !important; }
            .fi-header { display:none !important; }
            .fi-page-content { background:#eef3f7; }

            .cl-admin { --line:#b8c7d4; --line2:#d7e2eb; --text:#102033; --muted:#607080; display:grid; gap:12px; color:var(--text); }

            .cl-card,
            .cl-card * {
                box-sizing:border-box;
            }

            .cl-head, .cl-card { border:1px solid var(--line); border-radius:8px; background:#fff; overflow:hidden; }
            .cl-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:12px 14px; background:#d9eaf6; }
            .cl-title h1 { margin:0; font-size:20px; font-weight:900; }
            .cl-title p { margin:3px 0 0; font-size:13px; color:var(--muted); }

            .cl-layout { display:grid; grid-template-columns:310px minmax(0,1fr) 390px; gap:12px; align-items:start; }

            .cl-sticky {
                position:sticky;
                top:16px;
                max-height:calc(100vh - 32px);
                overflow:auto;
            }

            .cl-panel-head { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; padding:10px 12px; background:#e2f0fa; border-bottom:1px solid var(--line); font-weight:900; }
            .cl-body { display:grid; gap:10px; padding:12px; }

            .cl-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:10px; }
            .cl-datetime-grid { grid-template-columns:1fr; }

            .cl-field { display:grid; gap:5px; min-width:0; }
            .cl-field label { font-size:12px; font-weight:900; color:#35536a; }

            .cl-input, .cl-select {
                width:100%;
                max-width:100%;
                min-height:36px;
                border:1px solid var(--line);
                border-radius:6px;
                background:#fff;
                color:var(--text);
                font-size:13px;
                padding:7px 9px;
            }

            .cl-input[type="datetime-local"] {
                min-width:0;
                width:100%;
            }

            .cl-checks { display:flex; gap:12px; flex-wrap:wrap; font-size:13px; font-weight:800; color:#35536a; }

            .cl-btn, .cl-small {
                min-height:34px;
                display:inline-flex;
                align-items:center;
                justify-content:center;
                border:1px solid var(--line);
                border-radius:7px;
                background:#f6fafc;
                color:#1f3548;
                cursor:pointer;
                font-size:13px;
                font-weight:900;
                padding:0 11px;
                text-decoration:none;
                white-space:nowrap;
            }

            .cl-btn.primary { background:#226fa8; border-color:#185986; color:#fff; }
            .cl-small.danger { color:#a12626; }

            .cl-pill {
                display:inline-flex;
                align-items:center;
                min-height:24px;
                padding:0 8px;
                border-radius:999px;
                border:1px solid #c8d8e5;
                background:#fff;
                color:#38566e;
                font-size:12px;
                font-weight:900;
            }

            .cl-mode { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
            .cl-mode button {
                min-height:46px;
                border:1px solid var(--line);
                border-radius:8px;
                background:#fff;
                color:#24394b;
                cursor:pointer;
                font-size:13px;
                font-weight:900;
            }
            .cl-mode button.is-active { background:#226fa8; border-color:#185986; color:#fff; }

            .cl-products { max-height:680px; overflow:auto; display:grid; gap:8px; }
            .cl-product { display:grid; gap:4px; border:1px solid var(--line2); border-radius:7px; padding:8px; background:#fff; }
            .cl-product strong { font-size:12px; line-height:1.25; overflow-wrap:anywhere; }
            .cl-product span { font-size:12px; color:var(--muted); }

            .cl-list { display:grid; }
            .cl-row {
                display:grid;
                grid-template-columns:66px minmax(0,1fr) 116px 90px 115px;
                gap:10px;
                align-items:center;
                padding:10px;
                border-top:1px solid var(--line2);
            }
            .cl-row:first-child { border-top:0; }
            .cl-row.is-selected { background:#edf6fc; box-shadow:inset 3px 0 0 #226fa8; }

            .cl-thumb {
                width:56px;
                height:56px;
                border:1px solid var(--line2);
                border-radius:6px;
                background:#f8fbfd;
                display:flex;
                align-items:center;
                justify-content:center;
                overflow:hidden;
                color:#64748b;
                font-size:12px;
                font-weight:900;
            }
            .cl-thumb img { width:100%; height:100%; object-fit:cover; }

            .cl-main strong { display:block; font-size:14px; overflow-wrap:anywhere; }
            .cl-main span { display:block; margin-top:3px; font-size:12px; color:var(--muted); overflow-wrap:anywhere; }

            .cl-status { display:inline-flex; gap:5px; }
            .cl-dot { width:13px; height:13px; border-radius:999px; border:1px solid transparent; opacity:.35; cursor:pointer; }
            .cl-dot.yes { background:#38c867; border-color:#0f7a38; }
            .cl-dot.no { background:#f04444; border-color:#9e1f1f; }
            .cl-dot.is-active { opacity:1; box-shadow:0 0 0 1px rgba(15,23,42,.18), inset 0 0 0 2px rgba(255,255,255,.55); }

            .cl-actions { display:flex; justify-content:flex-end; gap:6px; flex-wrap:wrap; }

            .cl-note {
                border:1px solid #d7e2eb;
                border-radius:7px;
                background:#f8fbfd;
                padding:8px;
                color:#4b6478;
                font-size:12px;
                font-weight:800;
            }

            .cl-empty { padding:24px; text-align:center; color:#64748b; font-weight:800; }

            @media (max-width:1350px) {
                .cl-layout { grid-template-columns:1fr 390px; }
                .cl-products-card { grid-column:1 / -1; position:static; max-height:none; overflow:visible; }
            }

            @media (max-width:980px) {
                .cl-layout { grid-template-columns:1fr; }
                .cl-sticky { position:static; max-height:none; overflow:visible; }
                .cl-row { grid-template-columns:1fr; }
                .cl-actions { justify-content:flex-start; }
            }

            @media (max-width:720px) {
                .cl-grid, .cl-mode { grid-template-columns:1fr; }
            }
        </style>

        <section class="cl-head">
            <div class="cl-title">
                <h1>{{ $section['title'] }}</h1>
                <p>Ένα section στην αρχική. Όλα τα αντικείμενα μέσα του έχουν τον ίδιο τύπο: {{ $section['type_label'] }}.</p>
            </div>
            <button type="button" wire:click="createNew" class="cl-btn primary">Νέο αντικείμενο</button>
        </section>

        <div class="cl-layout">
            <aside class="cl-card cl-products-card cl-sticky">
                <div class="cl-panel-head">
                    <span>Προϊόντα</span>
                    <span class="cl-pill">Use = override</span>
                </div>

                <div class="cl-body">
                    <input type="search" wire:model.live.debounce.350ms="productSearch" class="cl-input" placeholder="Τίτλος / κωδικός / MPN / EAN">

                    <div class="cl-products">
                        @forelse($products as $product)
                            <div class="cl-product">
                                <strong>{{ $product['code'] ?: '#' . $product['id'] }} - {{ $product['title'] }}</strong>
                                <span>{{ $product['price'] }}</span>
                                <button type="button" wire:click="useProduct({{ $product['id'] }})" class="cl-small">Use</button>
                            </div>
                        @empty
                            <div class="cl-empty">Δεν βρέθηκαν προϊόντα.</div>
                        @endforelse
                    </div>
                </div>
            </aside>

            <section class="cl-card">
                <div class="cl-panel-head">
                    <span>Αντικείμενα section</span>
                    @unless($tableReady)
                        <span class="cl-pill">Τρέξε migrations</span>
                    @endunless
                </div>

                <div class="cl-list">
                    @forelse($featured as $item)
                        <div class="cl-row {{ (int) $editingId === (int) $item['id'] ? 'is-selected' : '' }}">
                            <div class="cl-thumb">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['product'] }}">
                                @else
                                    Image
                                @endif
                            </div>

                            <div class="cl-main">
                                <strong>{{ $item['product'] ?: '-' }}</strong>
                                <span>{{ $item['label'] ?: 'Χωρίς label' }}</span>
                                <span>{{ $item['period'] }}</span>
                            </div>

                            <span class="cl-pill">{{ $section['type_label'] }}</span>
                            <strong>{{ $item['price'] }}</strong>

                            <div class="cl-actions">
                                <span class="cl-status">
                                    <button type="button" wire:click="toggleFeatured({{ $item['id'] }}, true)" class="cl-dot yes {{ $item['is_active'] ? 'is-active' : '' }}" title="Ενεργό"></button>
                                    <button type="button" wire:click="toggleFeatured({{ $item['id'] }}, false)" class="cl-dot no {{ ! $item['is_active'] ? 'is-active' : '' }}" title="Ανενεργό"></button>
                                </span>

                                <button type="button" wire:click="editFeatured({{ $item['id'] }})" class="cl-small">Edit</button>
                                <button type="button" wire:click="deleteFeatured({{ $item['id'] }})" wire:confirm="Να διαγραφεί το αντικείμενο;" class="cl-small danger">Delete</button>
                            </div>
                        </div>
                    @empty
                        <div class="cl-empty">Δεν υπάρχουν αντικείμενα στο section.</div>
                    @endforelse
                </div>
            </section>

            <aside class="cl-sticky" style="display:grid;gap:12px;">
                <section class="cl-card">
                    <div class="cl-panel-head">
                        <span>Ρυθμίσεις section</span>
                        <span class="cl-pill">{{ $sectionEnabledInput ? 'Enabled' : 'Disabled' }}</span>
                    </div>

                    <form wire:submit="saveSection" class="cl-body">
                        <div class="cl-field">
                            <label>Τίτλος section</label>
                            <input type="text" wire:model.live="sectionTitleInput" class="cl-input">
                        </div>

                        <div class="cl-mode">
                            <button type="button" wire:click="$set('sectionTypeInput', 'recommended')" class="{{ $sectionTypeInput === 'recommended' ? 'is-active' : '' }}">Προτεινόμενα</button>
                            <button type="button" wire:click="$set('sectionTypeInput', 'offers')" class="{{ $sectionTypeInput === 'offers' ? 'is-active' : '' }}">Προσφορές</button>
                        </div>

                        <div class="cl-note">Αυτός ο τύπος εφαρμόζεται σε όλα τα αντικείμενα του section.</div>

                        <div class="cl-checks">
                            <label>
                                <input type="checkbox" wire:model.live="sectionEnabledInput"> Ενεργό στην αρχική
                            </label>
                        </div>

                        <button type="submit" class="cl-btn primary">Αποθήκευση section</button>
                    </form>
                </section>

                <section class="cl-card">
                    <div class="cl-panel-head">
                        <span>{{ $editingId ? 'Επεξεργασία item' : 'Νέο item' }}</span>
                        <span class="cl-pill">Product ID: {{ $productIdInput ?: '-' }}</span>
                    </div>

                    <form wire:submit="saveFeatured" class="cl-body">
                        <div class="cl-note">Αν πατήσεις `Use` σε δεύτερο προϊόν, αντικαθιστά το πρώτο.</div>

                        <div class="cl-grid">
                            <div class="cl-field">
                                <label>Product ID</label>
                                <input type="number" wire:model.live="productIdInput" class="cl-input">
                            </div>

                            <div class="cl-field">
                                <label>Σειρά</label>
                                <input type="number" wire:model.live="sortOrderInput" class="cl-input">
                            </div>
                        </div>

                        <div class="cl-field">
                            <label>Label</label>
                            <input type="text" wire:model.live="labelInput" class="cl-input" placeholder="Top επιλογή / Hot offer">
                        </div>

                        <div class="cl-grid cl-datetime-grid">
                            <div class="cl-field">
                                <label>Έναρξη</label>
                                <input type="datetime-local" wire:model.live="startsAtInput" class="cl-input">
                            </div>

                            <div class="cl-field">
                                <label>Λήξη</label>
                                <input type="datetime-local" wire:model.live="endsAtInput" class="cl-input">
                            </div>
                        </div>

                        <div class="cl-checks">
                            <label>
                                <input type="checkbox" wire:model.live="isActiveInput"> Ενεργό
                            </label>
                        </div>

                        <button type="submit" class="cl-btn primary">Αποθήκευση item</button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>