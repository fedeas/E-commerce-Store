<x-filament-panels::page>
    @php
        $workbookInfo = $workbookInfo ?? [];
        $headers = $workbookInfo['headers'] ?? [];
        $sampleRows = $workbookInfo['sample_rows'] ?? [];
        $dataRows = $workbookInfo['data_rows'] ?? null;
        $columns = $workbookInfo['columns'] ?? null;
        $detectedColumns = $detectedColumns ?? [];
        $importStats = $importStats ?? [];

        $statLabels = [
            'rows_seen' => 'Γραμμές διαβάστηκαν',
            'rows_processed' => 'Γραμμές επεξεργάστηκαν',
            'rows_skipped' => 'Γραμμές αγνοήθηκαν',
            'products_created' => 'Προϊόντα δημιουργήθηκαν',
            'products_updated' => 'Προϊόντα ενημερώθηκαν',
            'prices_created' => 'Τιμές δημιουργήθηκαν',
            'prices_updated' => 'Τιμές ενημερώθηκαν',
            'brands_created' => 'Κατασκευαστές δημιουργήθηκαν',
            'suppliers_created' => 'Προμηθευτές δημιουργήθηκαν',
            'categories_created' => 'Κατηγορίες δημιουργήθηκαν',
        ];
    @endphp

    <div class="cl-excel-admin">
        @php
            $showWorkbookPanel = !empty($workbookInfo) || !empty($detectedColumns);

            $showStatsCards = false;

            foreach (($importStats ?? []) as $value) {
                if (is_numeric($value) && (float) $value > 0) {
                    $showStatsCards = true;
                    break;
                }
            }

            $showProcessPanel = !empty($commandOutput)
                || !empty($lastStatus)
                || $showStatsCards;
        @endphp


        @php
            $hasWorkbookPanel = !empty($workbookInfo) || !empty($detectedColumns);

            $hasNonZeroImportStats = false;

            foreach (($importStats ?? []) as $statValue) {
                if (is_numeric($statValue) && (float) $statValue > 0) {
                    $hasNonZeroImportStats = true;
                    break;
                }

                if (is_array($statValue) && count($statValue) > 0) {
                    $hasNonZeroImportStats = true;
                    break;
                }
            }

            $hasProcessPanel = !empty($commandOutput)
                || !empty($lastStatus)
                || $hasNonZeroImportStats;
        @endphp

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

            .cl-excel-admin {
                --cl-panel: #ffffff;
                --cl-border: #c5d3df;
                --cl-border-dark: #9fb6c9;
                --cl-blue: #2e6fa7;
                --cl-blue-dark: #245b8b;
                --cl-text: #102033;
                --cl-muted: #607080;
                --cl-green: #159447;
                --cl-green-bg: #e7f8ed;
                --cl-red: #c73333;
                --cl-red-bg: #fdeaea;
                --cl-yellow-bg: #fff7df;
                display: grid;
                gap: 12px;
                width: 100%;
                color: var(--cl-text);
            }

            .cl-top-title,
            .cl-panel-head {
                background: linear-gradient(180deg, #dceefa 0%, #c7e0f2 100%);
            }

            .cl-top-title {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border: 1px solid var(--cl-border-dark);
                border-radius: 8px;
            }

            .cl-title-left,
            .cl-title-stats,
            .cl-panel-title,
            .cl-actions,
            .cl-pill-row {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .cl-title-left {
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

            .cl-icon {
                width: 17px;
                height: 17px;
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
                color: var(--cl-muted);
                font-size: 13px;
            }

            .cl-stat-pill,
            .cl-pill,
            .cl-status {
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

            .cl-status.is-ok,
            .cl-pill.is-found {
                color: var(--cl-green);
                background: var(--cl-green-bg);
                border-color: #98d9ae;
            }

            .cl-status.is-warn,
            .cl-pill.is-missing {
                color: var(--cl-red);
                background: var(--cl-red-bg);
                border-color: #efb1b1;
            }

            .cl-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 340px;
                gap: 12px;
                align-items: start;
            }

            .cl-stack {
                display: grid;
                gap: 12px;
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
                border-bottom: 1px solid var(--cl-border-dark);
            }

            .cl-panel-title {
                color: #18364f;
                font-size: 14px;
                font-weight: 900;
            }

            .cl-panel-body {
                padding: 12px;
                display: grid;
                gap: 12px;
            }

            .cl-form-grid {
                display: grid;
                grid-template-columns: minmax(260px, 1.5fr) repeat(2, minmax(120px, .5fr));
                gap: 10px;
                align-items: end;
            }

            .cl-field {
                display: grid;
                gap: 4px;
                min-width: 0;
            }

            .cl-label {
                color: var(--cl-muted);
                font-size: 11px;
                font-weight: 900;
                letter-spacing: 0;
                text-transform: none;
            }

            .cl-input {
                width: 100%;
                min-height: 35px;
                border: 1px solid var(--cl-border-dark);
                border-radius: 6px;
                background: #ffffff;
                color: var(--cl-text);
                font-size: 13px;
                padding: 6px 9px;
                outline: none;
            }

            .cl-input:focus {
                border-color: var(--cl-blue);
                box-shadow: 0 0 0 3px rgba(46, 111, 167, .13);
            }

            .cl-help {
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.35;
            }

            .cl-btn {
                min-height: 36px;
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
                line-height: 1;
                padding: 0 12px;
                text-decoration: none;
                transition: .15s ease;
                white-space: nowrap;
            }

            .cl-btn:hover {
                background: linear-gradient(180deg, #ffffff 0%, #dceefa 100%);
                border-color: #7fa6c7;
            }

            .cl-btn.primary {
                background: linear-gradient(180deg, #367db8 0%, #245f95 100%);
                border-color: #1e507d;
                color: #ffffff;
            }

            .cl-btn.green {
                background: linear-gradient(180deg, #20a956 0%, #148241 100%);
                border-color: #117338;
                color: #ffffff;
            }

            .cl-btn.red {
                background: linear-gradient(180deg, #d94b4b 0%, #b92d2d 100%);
                border-color: #9d2323;
                color: #ffffff;
            }

            .cl-btn:disabled {
                cursor: wait;
                opacity: .55;
            }

            .cl-info-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(140px, 1fr));
                gap: 8px;
            }

            .cl-info-card {
                border: 1px solid #d6e3ed;
                border-radius: 8px;
                background: #fbfdff;
                padding: 9px;
            }

            .cl-info-label {
                color: #64748b;
                font-size: 11px;
                font-weight: 900;
                text-transform: none;
            }

            .cl-info-value {
                margin-top: 4px;
                color: var(--cl-text);
                font-size: 18px;
                font-weight: 900;
                line-height: 1.15;
                word-break: break-word;
            }

            .cl-info-value.small {
                font-size: 13px;
            }

            .cl-column-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(130px, 1fr));
                gap: 7px;
            }

            .cl-pill {
                justify-content: flex-start;
                border-radius: 8px;
                min-height: 34px;
            }

            .cl-pill small {
                color: inherit;
                opacity: .75;
                font-weight: 800;
            }

            .cl-table-wrap {
                overflow-x: auto;
                background: #ffffff;
            }

            .cl-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 920px;
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
                font-size: 12px;
                padding: 7px 8px;
                border-bottom: 1px solid #e5edf3;
                border-right: 1px solid #edf3f8;
                vertical-align: top;
                background: #ffffff;
                max-width: 280px;
                overflow-wrap: anywhere;
            }

            .cl-table tr:nth-child(even) td {
                background: #fbfdff;
            }

            .cl-log-card {
                border: 1px solid #d6e3ed;
                border-radius: 10px;
                background: #ffffff;
                overflow: hidden;
            }

            .cl-log-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                flex-wrap: wrap;
                padding: 10px 12px;
                background: linear-gradient(180deg, #f8fbff 0%, #edf6fc 100%);
                border-bottom: 1px solid #d6e3ed;
            }

            .cl-log-title {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #18364f;
                font-size: 13px;
                font-weight: 900;
            }

            .cl-log-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                min-height: 28px;
                padding: 0 10px;
                border-radius: 999px;
                border: 1px solid #c8d8e5;
                background: #ffffff;
                color: #38566e;
                font-size: 11px;
                font-weight: 900;
                white-space: nowrap;
            }

            .cl-log-badge.running {
                color: #9a6700;
                background: #fff7df;
                border-color: #f0d493;
            }

            .cl-log-badge.finished {
                color: #159447;
                background: #e7f8ed;
                border-color: #98d9ae;
            }

            .cl-log-badge.failed {
                color: #c73333;
                background: #fdeaea;
                border-color: #efb1b1;
            }

            .cl-log-body {
                padding: 0;
                background: #fcfdff;
            }

            .cl-output {
                max-height: 420px;
                overflow: auto;
                margin: 0;
                padding: 14px 16px;
                background: #fcfdff;
                color: #1e293b;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
                font-size: 12px;
                line-height: 1.55;
                white-space: pre-wrap;
                word-break: break-word;
            }

            .cl-output::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }

            .cl-output::-webkit-scrollbar-thumb {
                background: #c7d6e2;
                border-radius: 999px;
                border: 2px solid #fcfdff;
            }

            .cl-output::-webkit-scrollbar-track {
                background: #eef4f8;
            }

            .cl-log-empty {
                padding: 18px 16px;
                color: #64748b;
                font-size: 13px;
                font-weight: 700;
                background: #fcfdff;
            }

            .cl-side {
                position: sticky;
                top: 16px;
            }

            .cl-update-list {
                display: grid;
                gap: 9px;
            }

            .cl-update-group {
                border: 1px solid #d6e3ed;
                border-radius: 8px;
                background: #fbfdff;
                overflow: hidden;
            }

            .cl-update-title {
                padding: 8px 9px;
                background: #edf6fc;
                color: #38566e;
                font-size: 12px;
                font-weight: 900;
            }

            .cl-update-group ul {
                margin: 0;
                padding: 8px 9px 9px 25px;
            }

            .cl-update-group li {
                color: #475569;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.35;
                margin: 3px 0;
            }

            .cl-warning {
                border: 1px solid #f0d493;
                border-radius: 8px;
                background: var(--cl-yellow-bg);
                color: #705318;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.4;
                padding: 10px;
            }

            @media (max-width: 1350px) {
                .cl-layout,
                .cl-form-grid {
                    grid-template-columns: 1fr;
                }

                .cl-side {
                    position: static;
                }

                .cl-column-grid,
                .cl-info-grid {
                    grid-template-columns: repeat(2, minmax(140px, 1fr));
                }
            }

            @media (max-width: 680px) {
                .cl-top-title {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .cl-column-grid,
                .cl-info-grid {
                    grid-template-columns: 1fr;
                }

                .cl-btn,
                .cl-stat-pill,
                .cl-status {
                    width: 100%;
                }
            }
        </style>

        <section class="cl-top-title">
            <div class="cl-title-left">
                <span class="cl-title-icon">
                    <x-heroicon-o-document-arrow-up class="cl-icon" />
                </span>

                <div class="cl-title-text">
                    <h1>Ενημέρωση ειδών από Excel</h1>
                    <p>Ανέβασε export αρχείο, κάνε έλεγχο χωρίς αλλαγές και μετά ενημέρωσε προϊόντα, τιμές και βασικά catalog στοιχεία.</p>
                </div>
            </div>

            <div class="cl-title-stats">
                <span class="cl-stat-pill">Αρχείο: {{ $lastFileName ?: 'Δεν έχει επιλεγεί' }}</span>
                @if ($lastRunMode)
                    <span class="cl-stat-pill">{{ $lastRunMode }}</span>
                @endif
                @if ($lastRunAt)
                    <span class="cl-stat-pill">{{ $lastRunAt }}</span>
                @endif
            </div>
        </section>

        <div class="cl-layout">
            <div class="cl-stack">
                <section class="cl-panel">
                    <div class="cl-panel-head">
                        <div class="cl-panel-title">
                            <x-heroicon-o-arrow-up-tray class="cl-icon" />
                            <span>Αρχείο και εκτέλεση</span>
                        </div>

                        @if ($lastStatus)
                            <span class="cl-status {{ str_contains($lastStatus, 'Αποτυχία') || str_contains($lastStatus, 'σφάλμα') ? 'is-warn' : 'is-ok' }}">
                                {{ $lastStatus }}
                            </span>
                        @endif
                    </div>

                    <div class="cl-panel-body">
                        <div class="cl-form-grid">
                            <div class="cl-field">
                                <label class="cl-label" for="excel-product-file">Excel αρχείο .xlsx</label>
                                <input id="excel-product-file" type="file" class="cl-input" wire:model="excelFile" accept=".xlsx">
                                <div class="cl-help">Το αρχείο αποθηκεύεται προσωρινά στο `storage/app/private/imports` πριν τρέξει το update.</div>
                            </div>

                            <div class="cl-field" style="align-self: start;">
                                <label class="cl-label" for="excel-start-row">Έναρξη από γραμμή δεδομένων</label>
                                <input id="excel-start-row" type="number" min="1" class="cl-input" wire:model.live="startRow">
                            </div>

                            <div class="cl-field">
                                <label class="cl-label" for="excel-limit-rows">Όριο γραμμών</label>
                                <input id="excel-limit-rows" type="number" min="0" class="cl-input" wire:model.live="limitRows">
                                <div class="cl-help">0 = όλες οι γραμμές.</div>
                            </div>
                        </div>

                        <div class="cl-actions">
                            <button type="button" class="cl-btn" wire:click="inspectUpload" wire:loading.attr="disabled">
                                <x-heroicon-o-eye class="cl-icon" />
                                Ανάγνωση αρχείου
                            </button>

                            <button type="button" class="cl-btn primary" wire:click="runDryRun" wire:loading.attr="disabled">
                                <x-heroicon-o-beaker class="cl-icon" />
                                Έλεγχος χωρίς αλλαγές
                            </button>

                            <button
                                type="button"
                                class="cl-btn green"
                                x-on:click="if (! confirm('Να γίνει πραγματική ενημέρωση στη βάση από το Excel;')) $event.stopImmediatePropagation()"
                                wire:click="runDatabaseUpdate"
                                wire:loading.attr="disabled"
                            >
                                <x-heroicon-o-circle-stack class="cl-icon" />
                                Ενημέρωση βάσης
                            </button>

                            <button type="button" class="cl-btn red" wire:click="clearOutput" wire:loading.attr="disabled">
                                <x-heroicon-o-x-mark class="cl-icon" />
                                Καθαρισμός αποτελεσμάτων
                            </button>
                        </div>

                        <div wire:loading class="cl-warning">
                            Η διαδικασία τρέχει. Για μεγάλο αρχείο όπως το export των 79k γραμμών μπορεί να χρειαστεί λίγη ώρα.
                        </div>
                    </div>
                </section>

                @if($showWorkbookPanel)
                <section class="cl-panel">
                    <div class="cl-panel-head">
                        <div class="cl-panel-title">
                            <x-heroicon-o-table-cells class="cl-icon" />
                            <span>Σύνοψη αρχείου</span>
                        </div>
                    </div>

                    <div class="cl-panel-body">
                        <div class="cl-info-grid">
                            <div class="cl-info-card">
                                <div class="cl-info-label">Γραμμές δεδομένων</div>
                                <div class="cl-info-value">{{ $dataRows !== null ? number_format($dataRows, 0, ',', '.') : '-' }}</div>
                            </div>

                            <div class="cl-info-card">
                                <div class="cl-info-label">Στήλες</div>
                                <div class="cl-info-value">{{ $columns !== null ? $columns : '-' }}</div>
                            </div>

                            <div class="cl-info-card">
                                <div class="cl-info-label">Μέγεθος</div>
                                <div class="cl-info-value small">{{ $lastFileSize ?: '-' }}</div>
                            </div>
                        </div>

                        @if ($detectedColumns !== [])
                            <div class="cl-column-grid">
                                @foreach ($detectedColumns as $column)
                                    <span class="cl-pill {{ $column['found'] ? 'is-found' : ($column['required'] ? 'is-missing' : '') }}">
                                        {{ $column['found'] ? '✓' : '•' }}
                                        <span>{{ $column['label'] }}</span>
                                        @if ($column['found'])
                                            <small>#{{ $column['column'] }}</small>
                                        @elseif ($column['required'])
                                            <small>λείπει</small>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="cl-help">Δεν έχει γίνει ακόμα ανάγνωση αρχείου. Διάλεξε `.xlsx` και πάτα “Ανάγνωση αρχείου”.</div>
                        @endif
                    </div>
                </section>
                @endif

                @if ($sampleRows !== [] && $headers !== [])
                    <section class="cl-panel">
                        <div class="cl-panel-head">
                            <div class="cl-panel-title">
                                <x-heroicon-o-list-bullet class="cl-icon" />
                                <span>Δείγμα πρώτων γραμμών</span>
                            </div>
                        </div>

                        <div class="cl-table-wrap">
                            <table class="cl-table">
                                <thead>
                                    <tr>
                                        @foreach (array_slice($headers, 0, 12) as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sampleRows as $row)
                                        <tr>
                                            @foreach (array_slice($row, 0, 12) as $cell)
                                                <td>{{ $cell !== '' && $cell !== null ? $cell : '-' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($importStats !== [] || $commandOutput)
                    @if($showProcessPanel)
                <section class="cl-panel">
                        <div class="cl-panel-head">
                            <div class="cl-panel-title">
                                <x-heroicon-o-chart-bar-square class="cl-icon" />
                                <span>Αποτελέσματα διαδικασίας</span>
                            </div>
                        </div>

                        <div class="cl-panel-body">
                            @if ($importStats !== [])
                                <div class="cl-info-grid">
                                    @foreach ($statLabels as $key => $label)
                                        <div class="cl-info-card">
                                            <div class="cl-info-label">{{ $label }}</div>
                                            <div class="cl-info-value">{{ number_format($importStats[$key] ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(filled($commandOutput))
                                @php
                                    $logStatusClass = match($lastStatus) {
                                        'running' => 'running',
                                        'finished' => 'finished',
                                        'failed' => 'failed',
                                        default => '',
                                    };

                                    $logStatusLabel = match($lastStatus) {
                                        'running' => 'Σε εξέλιξη',
                                        'finished' => 'Ολοκληρώθηκε',
                                        'failed' => 'Απέτυχε',
                                        default => 'Κατάσταση άγνωστη',
                                    };
                                @endphp

                                <section class="cl-panel">
                                    <div class="cl-panel-head">
                                        <div class="cl-panel-title">
                                            <svg class="cl-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v18m0 0h16.5M3.75 21l4.5-4.5 3 3L20.25 9"></path>
                                            </svg>
                                            <span>Αποτελέσματα / Log</span>
                                        </div>
                                    </div>

                                    <div class="cl-panel-body">
                                        <div class="cl-log-card">
                                            <div class="cl-log-head">
                                                <div class="cl-log-title">
                                                    <span>Τελευταία δραστηριότητα import</span>
                                                </div>

                                                <div class="cl-log-badge {{ $logStatusClass }}">
                                                    {{ $logStatusLabel }}
                                                </div>
                                            </div>

                                            <div class="cl-log-body">
                                                <pre class="cl-output">{{ $commandOutput }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @endif
                        </div>
                    </section>
                @endif
                @endif
            </div>

            <aside class="cl-stack cl-side">
                <section class="cl-panel">
                    <div class="cl-panel-head">
                        <div class="cl-panel-title">
                            <x-heroicon-o-information-circle class="cl-icon" />
                            <span>Τι ενημερώνεται</span>
                        </div>
                    </div>

                    <div class="cl-panel-body">
                        <div class="cl-update-list">
                            @foreach ($updateGroups as $group)
                                <div class="cl-update-group">
                                    <div class="cl-update-title">{{ $group['title'] }}</div>
                                    <ul>
                                        @foreach ($group['items'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>

                        <div class="cl-warning">
                            Καλό πρώτο βήμα: τρέξε “Έλεγχος χωρίς αλλαγές”. Το “Ενημέρωση βάσης” κάνει πραγματικές αλλαγές σε προϊόντα, τιμές, κατασκευαστές, προμηθευτές και κατηγορίες.
                        </div>
                    </div>
                </section>

                <section class="cl-panel">
                    <div class="cl-panel-head">
                        <div class="cl-panel-title">
                            <x-heroicon-o-circle-stack class="cl-icon" />
                            <span>Βάση δεδομένων</span>
                        </div>
                    </div>

                    <div class="cl-panel-body">
                        <div class="cl-update-list">
                            @foreach ($databaseSummary as $table)
                                <div class="cl-info-card">
                                    <div class="cl-info-label">{{ $table['table'] }}</div>
                                    <div class="cl-info-value small">
                                        @if ($table['exists'])
                                            {{ number_format($table['count'] ?? 0, 0, ',', '.') }} εγγραφές
                                        @elseif ($table['exists'] === null)
                                            Μη διαθέσιμη
                                        @else
                                            Δεν υπάρχει
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
