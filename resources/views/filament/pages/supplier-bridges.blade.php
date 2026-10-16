<x-filament-panels::page>
    @php
        $suppliers = $suppliers ?? [];
        $selectedSupplier = $selectedSupplier ?? null;
        $remoteCategories = $remoteCategories ?? [];
        $categoryMappings = $categoryMappings ?? [];
        $localCategories = $localCategories ?? [];
        $stats = $stats ?? ['suppliers' => 0, 'active' => 0, 'remote_categories' => 0, 'mappings' => 0];
        $activeFeedRun = $activeFeedRun ?? null;
        $mappedByCode = collect($categoryMappings)->keyBy('remote_code');
    @endphp

    <div class="cl-suppliers-admin" @if($activeFeedRun && in_array($activeFeedRun->status, ['queued', 'running'], true)) wire:poll.2000ms="refreshFeedRun" @endif>
        <style>
            .fi-header { display:none !important; }
            .fi-page-content { background:#edf3f8; }
            .cl-suppliers-admin { --border:#c5d3df; --border-dark:#9fb6c9; --text:#102033; --muted:#607080; --green:#159447; --red:#c73333; display:grid; gap:12px; color:var(--text); }
            .cl-top-title, .cl-panel-head, .cl-side-head { display:flex; align-items:center; justify-content:space-between; gap:12px; border:1px solid var(--border-dark); background:linear-gradient(180deg,#dceefa 0%,#c7e0f2 100%); }
            .cl-top-title { padding:12px; border-radius:8px; }
            .cl-top-title h1 { margin:0; font-size:21px; font-weight:950; line-height:1.1; }
            .cl-top-title p { margin:4px 0 0; color:var(--muted); font-size:13px; font-weight:700; }
            .cl-chip { display:inline-flex; align-items:center; min-height:24px; padding:0 8px; border-radius:999px; background:#edf3f8; color:#334155; font-size:11px; font-weight:950; white-space:nowrap; }
            .cl-chip.green { background:#e8f7ee; color:#107a3b; }
            .cl-chip.red { background:#fff0ee; color:#a33b32; }
            .cl-feed-run { border:1px solid #9fc3df; border-radius:8px; background:#f7fbff; padding:12px; display:grid; gap:10px; }
            .cl-feed-run.is-error { border-color:#ef9a9a; background:#fff7f7; }
            .cl-feed-run-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
            .cl-feed-run strong { display:block; color:#18364f; font-size:14px; font-weight:950; }
            .cl-feed-run p { margin:4px 0 0; color:#607080; font-size:12px; font-weight:750; }
            .cl-feed-run-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:7px; }
            .cl-feed-run-stat { border:1px solid #d5e5f0; border-radius:7px; background:#fff; padding:8px; }
            .cl-feed-run-stat small { display:block; color:#607080; font-size:10px; font-weight:950; text-transform:uppercase; }
            .cl-feed-run-stat span { display:block; margin-top:4px; color:#102033; font-size:16px; font-weight:950; }
            .cl-feed-log { display:grid; gap:4px; max-height:150px; overflow:auto; border-top:1px solid #d5e5f0; padding-top:8px; }
            .cl-feed-log div { display:grid; grid-template-columns:58px 150px minmax(0,1fr); gap:8px; color:#40566b; font-size:12px; font-weight:750; }
            .cl-feed-log b { color:#18364f; }
            .cl-progress { position:relative; height:8px; margin-top:10px; overflow:hidden; border-radius:999px; background:#dbeaf4; }
            .cl-progress::before { content:''; position:absolute; inset:0 auto 0 0; width:42%; border-radius:999px; background:linear-gradient(90deg,#159447,#00a7b5); animation:clProgress 1.1s ease-in-out infinite; }
            @keyframes clProgress { 0% { transform:translateX(-105%); } 100% { transform:translateX(245%); } }
            .cl-stat-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
            .cl-stat { padding:12px; border:1px solid var(--border); border-radius:8px; background:#fff; }
            .cl-stat small { display:block; color:var(--muted); font-size:10px; font-weight:950; text-transform:uppercase; letter-spacing:.9px; }
            .cl-stat strong { display:block; margin-top:5px; font-size:24px; font-weight:950; }
            .cl-layout { display:grid; grid-template-columns:330px minmax(0,1fr); gap:12px; align-items:start; }
            .cl-panel, .cl-side { border:1px solid var(--border-dark); border-radius:8px; background:#fff; overflow:hidden; }
            .cl-panel-head, .cl-side-head { padding:9px 10px; border-width:0 0 1px; }
            .cl-panel-head strong, .cl-side-head strong { color:#18364f; font-size:14px; font-weight:950; }
            .cl-side-body, .cl-panel-body { padding:12px; display:grid; gap:10px; }
            .cl-input, .cl-select, .cl-textarea { width:100%; border:1px solid #a9bed1; border-radius:7px; background:#fff; padding:0 10px; font:inherit; font-size:13px; }
            .cl-input, .cl-select { height:36px; }
            .cl-textarea { min-height:76px; padding-top:9px; resize:vertical; }
            .cl-field-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
            .cl-field { display:grid; gap:5px; }
            .cl-field.full { grid-column:1 / -1; }
            .cl-field label { color:#334155; font-size:11px; font-weight:950; text-transform:uppercase; letter-spacing:.7px; }
            .cl-checks { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; }
            .cl-check { min-height:38px; display:flex; align-items:center; gap:8px; padding:8px; border:1px solid #dce5ee; border-radius:8px; background:#fbfdff; font-size:12px; font-weight:850; }
            .cl-btns { display:flex; flex-wrap:wrap; gap:6px; }
            .cl-btns.nowrap { flex-wrap:nowrap; align-items:center; }
            .cl-btn { min-height:32px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--border-dark); border-radius:7px; background:linear-gradient(180deg,#fff 0%,#edf5fb 100%); color:#1f3548; padding:0 10px; font-size:12px; font-weight:900; cursor:pointer; text-decoration:none; }
            .cl-btn.primary { background:linear-gradient(180deg,#159447 0%,#0f7536 100%); border-color:#0c642d; color:#fff; }
            .cl-btn.dark { background:linear-gradient(180deg,#34495a 0%,#263746 100%); border-color:#20303d; color:#fff; }
            .cl-btn.red { background:linear-gradient(180deg,#d94b4b 0%,#b92d2d 100%); border-color:#9d2323; color:#fff; }
            .cl-supplier-list { display:grid; gap:7px; max-height:calc(100vh - 280px); overflow:auto; }
            .cl-supplier-item { width:100%; padding:10px; border:1px solid #dce5ee; border-radius:8px; background:#fff; text-align:left; cursor:pointer; }
            .cl-supplier-item.is-active { border-color:#7fa6c7; background:#eaf5ff; }
            .cl-supplier-item strong { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:13px; }
            .cl-supplier-id { color:#64748b; font-size:11px; font-weight:950; }
            .cl-supplier-item span { display:flex; flex-wrap:wrap; gap:5px; margin-top:7px; }
            .cl-table-wrap { overflow:auto; border:1px solid #e2edf5; border-radius:8px; }
            .cl-table { width:100%; min-width:960px; border-collapse:collapse; }
            .cl-table th { padding:8px; border-bottom:1px solid var(--border); border-right:1px solid #e2edf5; background:#edf6fc; color:#28455e; font-size:12px; font-weight:950; text-align:left; white-space:nowrap; }
            .cl-table td { padding:8px; border-bottom:1px solid #e5edf3; border-right:1px solid #edf3f8; background:#fff; font-size:13px; vertical-align:middle; }
            .cl-table tr:nth-child(even) td { background:#fbfdff; }
            .cl-muted { color:var(--muted); font-size:12px; font-weight:750; }
            .cl-combo { position:relative; min-width:320px; }
            .cl-combo-trigger { width:100%; min-height:36px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1px solid #a9bed1; border-radius:7px; background:#fff; padding:0 10px; cursor:pointer; font-size:13px; font-weight:850; color:#1f3548; text-align:left; }
            .cl-combo-trigger span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .cl-combo-panel { position:fixed; z-index:99999; display:none; width:520px; max-width:calc(100vw - 24px); max-height:360px; border:1px solid #8fb0ca; border-radius:10px; background:#fff; box-shadow:0 18px 40px rgba(15,23,42,.22); padding:8px; }
            .cl-combo.is-open .cl-combo-panel { display:block; }
            .cl-combo-search { width:100%; height:36px; border:1px solid #a9bed1; border-radius:7px; padding:0 10px; font-size:13px; }
            .cl-combo-options { display:grid; gap:4px; max-height:300px; overflow:auto; margin-top:8px; }
            .cl-combo-option { width:100%; border:0; border-radius:7px; background:#f8fbfd; color:#142235; padding:8px 9px; font-size:12px; font-weight:850; text-align:left; cursor:pointer; }
            .cl-combo-option:hover, .cl-combo-option.is-active { background:#dff3ff; color:#075985; box-shadow:inset 0 0 0 1px #7cc4ea; }
            .cl-tabs { display:flex; flex-wrap:wrap; gap:8px; }
            .cl-tab-card { padding:12px; border:1px solid #dce5ee; border-radius:8px; background:#fbfdff; }
            .cl-tab-card strong { display:block; font-size:14px; }
            .cl-tab-card p { margin:5px 0 0; color:var(--muted); font-size:12px; line-height:1.45; font-weight:750; }
            @media (max-width:1100px) { .cl-layout { grid-template-columns:1fr; } .cl-stat-row, .cl-field-grid, .cl-checks { grid-template-columns:1fr 1fr; } }
            @media (max-width:680px) { .cl-stat-row, .cl-field-grid, .cl-checks, .cl-feed-run-grid { grid-template-columns:1fr; } .cl-feed-log div { grid-template-columns:1fr; } }
        </style>

        <section class="cl-top-title">
            <div>
                <h1>XML Προμηθευτών</h1>
                <p>Feeds προμηθευτών και σύνδεση γέφυρας κατηγοριών όπως το παλιό bridge.</p>
            </div>
            <span class="cl-chip">Σύνδεση γέφυρας</span>
        </section>

        <div class="cl-stat-row">
            <div class="cl-stat"><small>Προμηθευτές</small><strong>{{ $stats['suppliers'] }}</strong></div>
            <div class="cl-stat"><small>Ενεργοί</small><strong>{{ $stats['active'] }}</strong></div>
            <div class="cl-stat"><small>Remote κατηγορίες</small><strong>{{ $stats['remote_categories'] }}</strong></div>
            <div class="cl-stat"><small>Συνδέσεις</small><strong>{{ $stats['mappings'] }}</strong></div>
        </div>

        <div class="cl-layout">
            <aside class="cl-side">
                <div class="cl-side-head">
                    <strong>XML Προμηθευτών</strong>
                    <span class="cl-chip">{{ count($suppliers) }}</span>
                </div>
                <div class="cl-side-body">
                    <input class="cl-input" type="search" wire:model.live.debounce.350ms="supplierSearch" placeholder="Αναζήτηση προμηθευτή">

                    <div class="cl-supplier-list">
                        @foreach($suppliers as $supplier)
                            <button type="button" class="cl-supplier-item {{ (int) $selectedSupplierId === (int) $supplier->id ? 'is-active' : '' }}" wire:click="selectSupplier({{ $supplier->id }})">
                                <strong>
                                    <span>{{ $supplier->name }}</span>
                                    <em class="cl-supplier-id">ID {{ $supplier->id }}{{ $supplier->storefront_code ? ' · Code ' . $supplier->storefront_code : '' }}</em>
                                </strong>
                                <span>
                                    <em class="cl-chip {{ $supplier->active ? 'green' : 'red' }}">{{ $supplier->active ? 'Ενεργό' : 'Ανενεργό' }}</em>
                                    <em class="cl-chip">{{ $supplier->remote_categories_count }} remote</em>
                                    <em class="cl-chip">{{ $supplier->mappings_count }} links</em>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </aside>

            <section class="cl-panel">
                <div class="cl-panel-head">
                    <strong>{{ $selectedSupplier?->name ?? 'Προμηθευτής' }}</strong>
                    @if($selectedSupplier)
                        <span class="cl-chip">{{ $selectedSupplier->last_bridge_sync_at ? 'Τελ. σάρωση: ' . \Carbon\Carbon::parse($selectedSupplier->last_bridge_sync_at)->format('d/m/Y H:i') : 'Δεν έγινε σάρωση' }}</span>
                    @endif
                </div>

                <div class="cl-panel-body">
                    <div class="cl-tabs">
                        <div class="cl-tab-card"><strong>Feed</strong><p>Ρυθμίσεις XML/CSV/API και προσαυξήσεις αγοράς/πώλησης.</p></div>
                        <div class="cl-tab-card"><strong>Σύνδεση γέφυρας</strong><p>Remote κατηγορία προμηθευτή προς τοπική κατηγορία eShop.</p></div>
                    </div>

                    <form wire:submit.prevent="saveSupplier" class="cl-field-grid">
                        <div class="cl-field">
                            <label>Προμηθευτής</label>
                            <input class="cl-input" type="text" wire:model.defer="supplierName">
                        </div>
                        <div class="cl-field">
                            <label>Τύπος feed</label>
                            <select class="cl-select" wire:model.defer="feedType">
                                <option value="xml">XML</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <div class="cl-field full">
                            <label>API URL</label>
                            <input class="cl-input" type="url" wire:model.defer="apiUrl">
                        </div>
                        <div class="cl-field">
                            <label>Προσαύξηση αγοράς %</label>
                            <input class="cl-input" type="text" wire:model.defer="purchaseMarkupPercent">
                        </div>
                        <div class="cl-field">
                            <label>Προσαύξηση πώλησης %</label>
                            <input class="cl-input" type="text" wire:model.defer="saleMarkupPercent">
                        </div>
                        <div class="cl-field">
                            <label>Διαθέσιμο</label>
                            <input class="cl-input" type="text" wire:model.defer="availableText">
                        </div>
                        <div class="cl-field">
                            <label>Μη διαθέσιμο</label>
                            <input class="cl-input" type="text" wire:model.defer="unavailableText">
                        </div>
                        <div class="cl-field">
                            <label>Τιμή αγοράς</label>
                            <select class="cl-select" wire:model.defer="purchasePriceVatMode">
                                <option value="ex_vat">Χωρίς ΦΠΑ</option>
                                <option value="inc_vat">Με ΦΠΑ</option>
                            </select>
                        </div>
                        <div class="cl-field">
                            <label>Τιμή πώλησης</label>
                            <select class="cl-select" wire:model.defer="salePriceVatMode">
                                <option value="ex_vat">Χωρίς ΦΠΑ</option>
                                <option value="inc_vat">Με ΦΠΑ</option>
                            </select>
                        </div>
                        <div class="cl-field full">
                            <div class="cl-checks">
                                <label class="cl-check"><input type="checkbox" wire:model.defer="updateQuantities"> Ενημέρωση ποσοτήτων</label>
                                <label class="cl-check"><input type="checkbox" wire:model.defer="updatePurchasePrice"> Ενημέρωση τιμής αγοράς</label>
                                <label class="cl-check"><input type="checkbox" wire:model.defer="updateSalePrice"> Ενημέρωση τιμής πώλησης</label>
                                <label class="cl-check"><input type="checkbox" wire:model.defer="active"> Ενεργό</label>
                            </div>
                        </div>
                        <div class="cl-field full">
                            <div class="cl-btns">
                                <button class="cl-btn primary" type="submit" wire:loading.attr="disabled">Αποθήκευση προμηθευτή</button>
                                <button class="cl-btn dark" type="button" wire:click="scanFeedCategories" wire:loading.attr="disabled">Σάρωση feed κατηγοριών</button>
                                <button class="cl-btn primary" type="button" wire:click="syncSelectedSupplierProducts" wire:loading.attr="disabled">Update τιμών/stock τώρα</button>
                                @if($selectedSupplier?->product_data_url)
                                    <button class="cl-btn dark" type="button" wire:click="syncSelectedSupplierProductData" wire:loading.attr="disabled">Import product data + εικόνες</button>
                                @endif
                                @if($selectedSupplier?->api_url)
                                    <a class="cl-btn" href="{{ $selectedSupplier->api_url }}" target="_blank" rel="noopener">Προβολή feed</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($activeFeedRun)
                        <div class="cl-feed-run {{ $activeFeedRun->status === 'failed' ? 'is-error' : '' }}">
                            <div class="cl-feed-run-top">
                                <div>
                                    <strong>{{ $activeFeedRun->stage ?: 'Εργασία feed' }}</strong>
                                    <p>{{ $activeFeedRun->message ?: 'Αναμονή ενημέρωσης status.' }}</p>
                                    @if($activeFeedRun->error)
                                        <p style="color:#b91c1c;">{{ $activeFeedRun->error }}</p>
                                    @endif
                                </div>
                                <span class="cl-chip {{ $activeFeedRun->status === 'completed' ? 'green' : ($activeFeedRun->status === 'failed' ? 'red' : '') }}">
                                    {{ match($activeFeedRun->status) { 'queued' => 'Σε αναμονή', 'running' => 'Τρέχει', 'completed' => 'Ολοκληρώθηκε', 'failed' => 'Απέτυχε', default => $activeFeedRun->status } }}
                                </span>
                            </div>

                            @if(in_array($activeFeedRun->status, ['queued', 'running'], true))
                                <div class="cl-progress" aria-hidden="true"></div>
                            @endif

                            <div class="cl-feed-run-grid">
                                <div class="cl-feed-run-stat"><small>Γραμμές</small><span>{{ number_format((int) $activeFeedRun->processed_rows) }}</span></div>
                                <div class="cl-feed-run-stat"><small>Κατηγορίες</small><span>{{ number_format((int) $activeFeedRun->categories_found) }}</span></div>
                                <div class="cl-feed-run-stat"><small>Νέα</small><span>{{ number_format((int) $activeFeedRun->products_created) }}</span></div>
                                <div class="cl-feed-run-stat"><small>Updates</small><span>{{ number_format((int) $activeFeedRun->products_updated) }}</span></div>
                                <div class="cl-feed-run-stat"><small>Skipped</small><span>{{ number_format((int) $activeFeedRun->products_skipped) }}</span></div>
                                <div class="cl-feed-run-stat"><small>Εικόνες</small><span>{{ number_format((int) $activeFeedRun->images_saved) }}</span></div>
                            </div>

                            @if(!empty($activeFeedRun->log_entries))
                                <div class="cl-feed-log">
                                    @foreach(array_reverse($activeFeedRun->log_entries) as $entry)
                                        <div>
                                            <span>{{ $entry['time'] ?? '' }}</span>
                                            <b>{{ $entry['stage'] ?? '' }}</b>
                                            <span>{{ $entry['message'] ?? '' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="cl-panel">
                        <div class="cl-panel-head">
                            <strong>Σύνδεση γέφυρας</strong>
                            <span class="cl-chip">{{ count($categoryMappings) }} συνδέσεις</span>
                        </div>
                        <div class="cl-panel-body">
                            <div class="cl-field">
                                <label>Αναζήτηση remote κατηγορίας</label>
                                <input class="cl-input" type="search" wire:model.live.debounce.250ms="remoteCategorySearch" placeholder="π.χ. storage media, cpu, cable">
                            </div>

                            <div class="cl-table-wrap">
                                <table class="cl-table">
                                    <thead>
                                        <tr>
                                            <th>Remote κατηγορία</th>
                                            <th>Τοπική κατηγορία</th>
                                            <th>Νέα σύνδεση</th>
                                            <th>Πράξη</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($remoteCategories as $remote)
                                            @php
                                                $mapping = $mappedByCode->get($remote->remote_code);
                                                $localInputKey = md5($remote->remote_code);
                                                $selectedLocalId = (int) ($localCategoryIds[$localInputKey] ?? 0);
                                                $selectedLocalCategory = collect($localCategories)->firstWhere('id', $selectedLocalId);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $remote->remote_name }}</strong>
                                                    <div class="cl-muted">{{ $remote->remote_code }} · {{ $remote->products_count }} products</div>
                                                </td>
                                                <td>
                                                    @if($mapping)
                                                        <span class="cl-chip green">{{ $mapping->local_path ?: $mapping->local_name }}</span>
                                                    @else
                                                        <span class="cl-chip red">Δεν έχει σύνδεση</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="cl-combo" data-category-combo>
                                                        <button class="cl-combo-trigger" type="button" data-category-trigger>
                                                            <span data-category-label>{{ $selectedLocalCategory['label'] ?? 'Επιλογή κατηγορίας' }}</span>
                                                            <b>⌄</b>
                                                        </button>

                                                        <input type="hidden" wire:model="localCategoryIds.{{ $localInputKey }}">

                                                        <div class="cl-combo-panel" data-category-panel>
                                                            <input class="cl-combo-search" type="search" placeholder="Αναζήτηση κατηγορίας... π.χ. storage media">

                                                            <div class="cl-combo-options">
                                                                @foreach($localCategories as $category)
                                                                    <button
                                                                        class="cl-combo-option"
                                                                        type="button"
                                                                        data-category-option
                                                                        data-id="{{ $category['id'] }}"
                                                                        data-label="{{ $category['label'] }}"
                                                                        data-search="{{ mb_strtolower($category['label']) }}"
                                                                    >
                                                                        {{ $category['label'] }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="cl-btns nowrap">
                                                        <button class="cl-btn primary" type="button" wire:click="saveCategoryMapping('{{ addslashes($remote->remote_code) }}', '{{ addslashes($remote->remote_name) }}')">Σύνδεση</button>
                                                        @if($mapping)
                                                            <button class="cl-btn red" type="button" wire:click="deleteMapping({{ $mapping->id }})">Διαγραφή</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">Δεν υπάρχουν remote κατηγορίες. Κάντε σάρωση XML ή προσθέστε χειροκίνητα.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            if (window.__clCategoryOverlayReady) return;
            window.__clCategoryOverlayReady = true;

            function normalize(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[>/_-]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            function closeAll(except = null) {
                document.querySelectorAll('[data-category-combo].is-open').forEach((combo) => {
                    if (combo !== except) combo.classList.remove('is-open');
                });
            }

            function positionPanel(combo) {
                const trigger = combo.querySelector('[data-category-trigger]');
                const panel = combo.querySelector('[data-category-panel]');
                const rect = trigger.getBoundingClientRect();

                let left = rect.left;
                let top = rect.bottom + 6;

                if (left + 520 > window.innerWidth - 12) {
                    left = window.innerWidth - 532;
                }

                if (top + 360 > window.innerHeight - 12) {
                    top = rect.top - 366;
                }

                panel.style.left = Math.max(12, left) + 'px';
                panel.style.top = Math.max(12, top) + 'px';
            }

            function filterOptions(combo) {
                const search = normalize(combo.querySelector('.cl-combo-search')?.value || '');
                const words = search.split(' ').filter(Boolean);

                combo.querySelectorAll('[data-category-option]').forEach((option) => {
                    const text = normalize(option.dataset.search || option.textContent);
                    const show = words.length === 0 || words.every((word) => text.includes(word));
                    option.style.display = show ? '' : 'none';
                });
            }

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-category-trigger]');

                if (trigger) {
                    const combo = trigger.closest('[data-category-combo]');
                    const opening = !combo.classList.contains('is-open');

                    closeAll(combo);
                    combo.classList.toggle('is-open', opening);

                    if (opening) {
                        positionPanel(combo);
                        const search = combo.querySelector('.cl-combo-search');
                        search.value = '';
                        filterOptions(combo);
                        setTimeout(() => search.focus(), 20);
                    }

                    return;
                }

                const option = event.target.closest('[data-category-option]');

                if (option) {
                    const combo = option.closest('[data-category-combo]');
                    const label = option.dataset.label;
                    const id = option.dataset.id;

                    combo.querySelector('[data-category-label]').textContent = label;
                    combo.querySelector('input[type="hidden"]').value = id;
                    combo.querySelector('input[type="hidden"]').dispatchEvent(new Event('input', { bubbles: true }));
                    combo.classList.remove('is-open');

                    return;
                }

                if (!event.target.closest('[data-category-combo]')) {
                    closeAll();
                }
            });

            document.addEventListener('input', (event) => {
                if (!event.target.matches('.cl-combo-search')) return;
                filterOptions(event.target.closest('[data-category-combo]'));
            });

            window.addEventListener('scroll', () => {
                document.querySelectorAll('[data-category-combo].is-open').forEach(positionPanel);
            }, true);

            window.addEventListener('resize', () => {
                document.querySelectorAll('[data-category-combo].is-open').forEach(positionPanel);
            });
        })();
    </script>
</x-filament-panels::page>
