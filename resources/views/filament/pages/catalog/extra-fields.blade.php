<x-filament-panels::page>
    @php
        $sections = $sections ?? [];
        $fields = $fields ?? [];
        $selectedField = $selectedField ?? null;
        $totalFields = $totalFields ?? count($fields);
        $tableReady = $tableReady ?? false;
    @endphp

    <div class="cl-extra-admin">
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

            .cl-extra-admin {
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
            .cl-panel-head,
            .cl-side-head {
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
            }

            .cl-top-title,
            .cl-toolbar,
            .cl-panel-head,
            .cl-side-head,
            .cl-toolbar-left,
            .cl-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .cl-top-title,
            .cl-toolbar {
                justify-content: space-between;
                padding: 10px 12px;
            }

            .cl-panel-head,
            .cl-side-head {
                justify-content: space-between;
                padding: 8px 10px;
                border-bottom: 1px solid var(--cl-border-dark);
                color: #18364f;
                font-size: 14px;
                font-weight: 900;
            }

            .cl-title h1 {
                margin: 0;
                font-size: 20px;
                font-weight: 900;
            }

            .cl-title p {
                max-width: 920px;
                margin: 4px 0 0;
                color: var(--cl-muted);
                font-size: 13px;
                line-height: 1.35;
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
            .cl-select { min-width: 210px; }

            .cl-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 360px;
                gap: 12px;
                align-items: start;
            }

            .cl-table-wrap { overflow-x: auto; }

            .cl-table {
                width: 100%;
                min-width: 1040px;
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

            .cl-table th.is-center,
            .cl-table td.is-center { text-align: center; }
            .cl-table th.is-right,
            .cl-table td.is-right { text-align: right; }

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

            .cl-name {
                display: grid;
                gap: 3px;
                font-weight: 900;
            }

            .cl-tags-preview {
                color: #7c4fa2;
                font-size: 12px;
                font-weight: 800;
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
                border-radius: 999px;
                border: 1px solid transparent;
                opacity: .38;
                cursor: pointer;
            }

            .cl-status-dot.yes { background: #38c867; border-color: #0f7a38; }
            .cl-status-dot.no { background: #f04444; border-color: #9e1f1f; }
            .cl-status-dot.is-active { opacity: 1; box-shadow: 0 0 0 1px rgba(15, 23, 42, .18), inset 0 0 0 2px rgba(255,255,255,.55); }
            .cl-status-dot:disabled { cursor: not-allowed; opacity: .24; }

            .cl-pill {
                display: inline-flex;
                align-items: center;
                min-height: 25px;
                padding: 4px 8px;
                border-radius: 999px;
                border: 1px solid #c8d8e5;
                background: #ffffff;
                color: #38566e;
                font-size: 12px;
                font-weight: 900;
            }

            .cl-btn,
            .cl-small-btn {
                min-height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                border: 1px solid var(--cl-border-dark);
                border-radius: 7px;
                background: linear-gradient(180deg, #ffffff 0%, #edf5fb 100%);
                color: #1f3548;
                cursor: pointer;
                font-size: 13px;
                font-weight: 900;
                padding: 0 10px;
                text-decoration: none;
                white-space: nowrap;
            }

            .cl-small-btn { min-height: 28px; padding: 0 8px; }
            .cl-btn.primary { background: linear-gradient(180deg, #367db8 0%, #245f95 100%); border-color: #1e507d; color: #ffffff; }
            .cl-btn.danger { background: linear-gradient(180deg, #fff7f7 0%, #fde8e8 100%); border-color: #d8a1a1; color: #9d2424; }

            .cl-side { position: sticky; top: 16px; }
            .cl-side-body { display: grid; gap: 10px; padding: 10px; }
            .cl-info-row { display: flex; justify-content: space-between; gap: 12px; font-size: 12px; }
            .cl-info-row span:first-child { color: #64748b; font-weight: 800; }
            .cl-info-row span:last-child { font-weight: 900; text-align: right; }

            .cl-note,
            .cl-tag-box {
                border: 1px solid #d6e3ed;
                border-radius: 8px;
                background: #fbfdff;
                padding: 10px;
                color: #475569;
                font-size: 13px;
                line-height: 1.4;
            }

            .cl-products {
                display: grid;
                gap: 7px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .cl-products a {
                color: #245f95;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.3;
                text-decoration: none;
            }

            .cl-empty { padding: 24px; text-align: center; color: #64748b; font-weight: 800; }
            .cl-muted { color: #64748b; font-size: 12px; font-weight: 800; }

            @media (max-width: 1200px) {
                .cl-layout { grid-template-columns: 1fr; }
                .cl-side { position: static; }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title">
                <h1>Έξτρα πεδία</h1>
                <p>Διαχείριση φίλτρων προϊόντων και τεχνικών χαρακτηριστικών. Οδηγίες συγχώνευσης: κρατάμε το πεδίο που θέλουμε και στο Tags βάζουμε τις ονομασίες των πεδίων που θα διαγραφούν, ώστε η επόμενη ενημέρωση από γέφυρα να μη φτιάξει διπλό πεδίο.</p>
            </div>

            <span class="cl-pill">{{ $totalFields }} πεδία</span>
        </section>

        <section class="cl-toolbar">
            <div class="cl-toolbar-left">
                <input type="search" wire:model.live.debounce.350ms="search" class="cl-input" placeholder="Αναζήτηση πεδίου / tag / τομέα">

                <select wire:model.live="sectionFilter" class="cl-select">
                    @foreach($sections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                    @endforeach
                </select>
            </div>

            @unless($tableReady)
                <span class="cl-pill">Προεπισκόπηση μέχρι να τρέξουν τα migrations</span>
            @endunless
        </section>

        <section class="cl-layout">
            <div class="cl-panel">
                <div class="cl-panel-head">
                    <span>Λίστα πεδίων</span>
                    <span class="cl-pill">TECHNICAL DETAILS</span>
                </div>

                <div class="cl-table-wrap">
                    <table class="cl-table">
                        <thead>
                            <tr>
                                <th>Έξτρα πεδίο</th>
                                <th>Εσ. κωδικός</th>
                                <th>Τομέας</th>
                                <th class="is-center">Κατάσταση</th>
                                <th class="is-center">Φίλτρο</th>
                                <th class="is-center">Τύπος</th>
                                <th class="is-center">Πολλαπλό</th>
                                <th class="is-right">Ενέργεια</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fields as $field)
                                <tr wire:key="extra-field-{{ $field['id'] }}" wire:click="selectField({{ $field['id'] }})" class="{{ (int) $selectedFieldId === (int) $field['id'] ? 'is-selected' : '' }}">
                                    <td>
                                        <div class="cl-name">
                                            <span>{{ $field['name'] }}</span>
                                            @if($field['tags'] !== '')
                                                <span class="cl-tags-preview">{{ $field['tags'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $field['internal_code'] !== '' ? $field['internal_code'] : '-' }}</td>
                                    <td>{{ $field['section'] }}</td>
                                    <td class="is-center">
                                        <span class="cl-status-pair" title="Κατάσταση">
                                            <button type="button" class="cl-status-dot yes {{ $field['is_active'] ? 'is-active' : '' }}" wire:click.stop="toggleFieldFlag({{ $field['id'] }}, 'active', true)" @disabled(! $tableReady) aria-label="Ενεργό"></button>
                                            <button type="button" class="cl-status-dot no {{ ! $field['is_active'] ? 'is-active' : '' }}" wire:click.stop="toggleFieldFlag({{ $field['id'] }}, 'active', false)" @disabled(! $tableReady) aria-label="Ανενεργό"></button>
                                        </span>
                                    </td>
                                    <td class="is-center">
                                        <span class="cl-status-pair" title="Φίλτρο">
                                            <button type="button" class="cl-status-dot yes {{ $field['is_filterable'] ? 'is-active' : '' }}" wire:click.stop="toggleFieldFlag({{ $field['id'] }}, 'filter', true)" @disabled(! $tableReady) aria-label="Ενεργό φίλτρο"></button>
                                            <button type="button" class="cl-status-dot no {{ ! $field['is_filterable'] ? 'is-active' : '' }}" wire:click.stop="toggleFieldFlag({{ $field['id'] }}, 'filter', false)" @disabled(! $tableReady) aria-label="Ανενεργό φίλτρο"></button>
                                        </span>
                                    </td>
                                    <td class="is-center">{{ $field['field_type'] }}</td>
                                    <td class="is-center">
                                        <span class="cl-status-pair" title="Πολλαπλό">
                                            <button type="button" class="cl-status-dot yes {{ $field['is_multiple'] ? 'is-active' : '' }}" wire:click.stop="toggleFieldFlag({{ $field['id'] }}, 'multiple', true)" @disabled(! $tableReady) aria-label="Πολλαπλό"></button>
                                            <button type="button" class="cl-status-dot no {{ ! $field['is_multiple'] ? 'is-active' : '' }}" wire:click.stop="toggleFieldFlag({{ $field['id'] }}, 'multiple', false)" @disabled(! $tableReady) aria-label="Όχι πολλαπλό"></button>
                                        </span>
                                    </td>
                                    <td class="is-right">
                                        <button type="button" class="cl-small-btn" wire:click.stop="selectField({{ $field['id'] }})">Πληροφορίες</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="cl-empty">Δεν βρέθηκαν έξτρα πεδία.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="cl-side">
                <div class="cl-side-head">
                    <span>{{ $selectedField['name'] ?? 'Πληροφορίες' }}</span>
                </div>

                <div class="cl-side-body">
                    @if($selectedField)
                        <div class="cl-actions">
                            <button type="button" class="cl-btn primary">Επεξεργασία</button>
                            <button type="button" class="cl-btn danger">Διαγραφή</button>
                        </div>

                        <div class="cl-info-row">
                            <span>Τομέας</span>
                            <span>{{ $selectedField['section'] }}</span>
                        </div>
                        <div class="cl-info-row">
                            <span>Τύπος</span>
                            <span>{{ $selectedField['field_type'] }}</span>
                        </div>
                        <div class="cl-info-row">
                            <span>Προϊόντα</span>
                            <span>{{ number_format((int) $selectedField['products_count'], 0, ',', '.') }}</span>
                        </div>
                        <div class="cl-info-row">
                            <span>Κατάσταση</span>
                            <span>{{ $selectedField['is_active'] ? 'Ενεργό' : 'Ανενεργό' }}</span>
                        </div>
                        <div class="cl-info-row">
                            <span>Φίλτρο</span>
                            <span>{{ $selectedField['is_filterable'] ? 'Ενεργό' : 'Ανενεργό' }}</span>
                        </div>

                        <div class="cl-tag-box">
                            <strong>Tags</strong><br>
                            {{ $selectedField['tags'] !== '' ? $selectedField['tags'] : 'Δεν έχουν δηλωθεί tags συγχώνευσης.' }}
                        </div>

                        @if(! $tableReady)
                            <div class="cl-note">Για αποθήκευση αλλαγών και σύνδεση με προϊόντα χρειάζεται να τρέξουν τα migrations.</div>
                        @endif

                        @if(count($selectedField['sample_products']) > 0)
                            <div class="cl-note">
                                <strong>Products (up to 20)</strong>
                                <ul class="cl-products">
                                    @foreach($selectedField['sample_products'] as $product)
                                        <li>
                                            <a href="/product_info.php?products_id={{ $product['id'] }}" target="_blank">
                                                {{ $product['title'] }}
                                                @if($product['code'] !== '')
                                                    <span class="cl-muted">(Order code: {{ $product['code'] }})</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="cl-note">Δεν υπάρχουν ακόμη συνδεδεμένα προϊόντα για αυτό το πεδίο.</div>
                        @endif
                    @else
                        <div class="cl-empty">Επίλεξε ένα πεδίο για λεπτομέρειες.</div>
                    @endif
                </div>
            </aside>
        </section>
    </div>
</x-filament-panels::page>
