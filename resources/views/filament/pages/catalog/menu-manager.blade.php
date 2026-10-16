<x-filament-panels::page>
    @php
        $items = $items ?? [];
        $selectedItem = $selectedItem ?? null;
        $domains = $domains ?? [];
        $totalItems = $totalItems ?? count($items);
        $tableReady = $tableReady ?? false;
    @endphp

    <div class="cl-menu-admin">
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

            .cl-menu-admin {
                --cl-border: #c5d3df;
                --cl-border-dark: #9fb6c9;
                --cl-text: #102033;
                --cl-muted: #607080;
                --cl-green: #159447;
                --cl-green-bg: #e7f8ed;
                --cl-red: #c73333;
                --cl-red-bg: #fdeaea;
                display: grid;
                gap: 12px;
                color: var(--cl-text);
            }

            .cl-top-title,
            .cl-panel-head,
            .cl-side-head {
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
            }

            .cl-top-title,
            .cl-toolbar,
            .cl-panel,
            .cl-side {
                border: 1px solid var(--cl-border-dark);
                border-radius: 8px;
                overflow: hidden;
                background: #ffffff;
            }

            .cl-top-title,
            .cl-toolbar,
            .cl-panel-head,
            .cl-side-head,
            .cl-actions,
            .cl-domain-tabs {
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

            .cl-btn,
            .cl-tab,
            .cl-small-btn {
                min-height: 34px;
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
                padding: 0 11px;
                text-decoration: none;
                white-space: nowrap;
            }

            .cl-tab.is-active,
            .cl-btn.primary {
                background: linear-gradient(180deg, #367db8 0%, #245f95 100%);
                border-color: #1e507d;
                color: #ffffff;
            }

            .cl-input {
                width: min(420px, 100%);
                min-height: 35px;
                border: 1px solid var(--cl-border-dark);
                border-radius: 6px;
                padding: 0 9px;
                font-size: 13px;
            }

            .cl-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 330px;
                gap: 12px;
                align-items: start;
            }

            .cl-panel-head,
            .cl-side-head {
                justify-content: space-between;
                padding: 8px 10px;
                border-bottom: 1px solid var(--cl-border-dark);
                font-size: 14px;
                font-weight: 900;
                color: #18364f;
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

            .cl-side { position: sticky; top: 16px; }
            .cl-side-body { display: grid; gap: 10px; padding: 10px; }
            .cl-info-row { display: flex; justify-content: space-between; gap: 12px; font-size: 12px; }
            .cl-info-row span:first-child { color: #64748b; font-weight: 800; }
            .cl-info-row span:last-child { font-weight: 900; text-align: right; }
            .cl-empty { padding: 24px; text-align: center; color: #64748b; font-weight: 800; }

            @media (max-width: 1200px) {
                .cl-layout { grid-template-columns: 1fr; }
                .cl-side { position: static; }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title">
                <h1>Στοιχεία Μενού</h1>
                <p>Διαχείριση στοιχείων μενού ανά site, θέση, σειρά και κατάσταση.</p>
            </div>

            <span class="cl-pill">{{ $totalItems }} εγγραφές</span>
        </section>

        <section class="cl-toolbar">
            <div class="cl-domain-tabs">
                @foreach ($domains as $key => $label)
                    <button type="button" wire:click="setDomainFilter('{{ $key }}')" class="cl-tab {{ $domainFilter === $key ? 'is-active' : '' }}">{{ $label }}</button>
                @endforeach
            </div>

            <input type="search" wire:model.live.debounce.350ms="search" class="cl-input" placeholder="Αναζήτηση τίτλου / θέσης / slug">
        </section>

        <div class="cl-layout">
            <section class="cl-panel">
                <div class="cl-panel-head">
                    <span>Στοιχεία Μενού</span>
                    @unless($tableReady)
                        <span class="cl-pill">Preview μέχρι να τρέξει migration</span>
                    @endunless
                </div>

                <div class="cl-table-wrap">
                    <table class="cl-table">
                        <thead>
                            <tr>
                                <th>Στοιχεία Μενού</th>
                                <th>Θέση</th>
                                <th>Sites</th>
                                <th>Σειρά</th>
                                <th>Κατάσταση</th>
                                <th style="text-align:right;">Πράξη</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr wire:click="selectMenuItem({{ $item['id'] }})" class="{{ (int) $selectedMenuId === (int) $item['id'] ? 'is-selected' : '' }}">
                                    <td>
                                        <strong>{{ $item['title'] }}</strong>
                                        @if($item['slug'])
                                            <br><span style="color:#7c3aed;font-size:11px;">{{ $item['slug'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item['position'] }}</td>
                                    <td>{{ $item['sites'] !== [] ? implode(', ', $item['sites']) : '-' }}</td>
                                    <td>{{ $item['sort_order'] }}</td>
                                    <td>
                                        <span class="cl-status-pair">
                                            <button type="button" wire:click.stop="toggleStatus({{ $item['id'] }}, true)" class="cl-status-dot yes {{ $item['is_active'] ? 'is-active' : '' }}" title="Ενεργό"></button>
                                            <button type="button" wire:click.stop="toggleStatus({{ $item['id'] }}, false)" class="cl-status-dot no {{ ! $item['is_active'] ? 'is-active' : '' }}" title="Ανενεργό"></button>
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <button type="button" wire:click.stop="selectMenuItem({{ $item['id'] }})" class="cl-small-btn">Πληροφορίες</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="cl-empty">Δεν υπάρχουν στοιχεία μενού.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="cl-side">
                <div class="cl-side-head">{{ $selectedItem['title'] ?? 'Πληροφορίες' }}</div>
                <div class="cl-side-body">
                    @if($selectedItem)
                        <div class="cl-actions">
                            <button type="button" class="cl-btn primary">Επεξεργασία</button>
                            <button type="button" class="cl-btn">Κλωνοποίηση</button>
                        </div>
                        <div class="cl-info-row"><span>ID</span><span>{{ $selectedItem['id'] }}</span></div>
                        <div class="cl-info-row"><span>Θέση</span><span>{{ $selectedItem['position'] }}</span></div>
                        <div class="cl-info-row"><span>Sites</span><span>{{ $selectedItem['sites'] !== [] ? implode(', ', $selectedItem['sites']) : '-' }}</span></div>
                        <div class="cl-info-row"><span>Σειρά</span><span>{{ $selectedItem['sort_order'] }}</span></div>
                        <div class="cl-info-row"><span>Κατάσταση</span><span>{{ $selectedItem['is_active'] ? 'Ενεργό' : 'Ανενεργό' }}</span></div>
                        <div class="cl-info-row"><span>Link</span><span>{{ $selectedItem['link_url'] ?: '-' }}</span></div>
                    @else
                        <div class="cl-empty">Επίλεξε στοιχείο μενού.</div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
