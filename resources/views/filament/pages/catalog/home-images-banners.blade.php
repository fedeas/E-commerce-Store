<x-filament-panels::page>
    @php
        $banners = $banners ?? [];
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

            .cl-card { border:1px solid var(--line); border-radius:8px; background:#fff; overflow:hidden; }

            .cl-head {
                display:flex;
                align-items:center;
                justify-content:space-between;
                gap:12px;
                flex-wrap:wrap;
                padding:12px 14px;
                background:#d9eaf6;
                border:1px solid var(--line);
                border-radius:8px;
            }

            .cl-title h1 { margin:0; font-size:20px; font-weight:900; }
            .cl-title p { margin:3px 0 0; font-size:13px; color:var(--muted); }

            .cl-layout {
                display:grid;
                grid-template-columns:minmax(0, 1fr) 390px;
                gap:12px;
                align-items:start;
            }

            .cl-panel-head {
                display:flex;
                align-items:center;
                justify-content:space-between;
                gap:8px;
                flex-wrap:wrap;
                padding:10px 12px;
                background:#e2f0fa;
                border-bottom:1px solid var(--line);
                font-weight:900;
            }

            .cl-form {
                position:sticky;
                top:16px;
                max-height:calc(100vh - 32px);
                overflow:auto;
            }

            .cl-form-body {
                display:grid;
                gap:10px;
                padding:12px;
            }

            .cl-grid {
                display:grid;
                grid-template-columns:minmax(0,1fr) minmax(0,1fr);
                gap:10px;
            }

            .cl-datetime-grid {
                grid-template-columns:1fr;
            }

            .cl-field {
                display:grid;
                gap:5px;
                min-width:0;
            }

            .cl-field label {
                font-size:12px;
                font-weight:900;
                color:#35536a;
            }

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

            .cl-checks {
                display:flex;
                flex-wrap:wrap;
                gap:12px;
                font-size:13px;
                font-weight:800;
                color:#35536a;
            }

            .cl-checks label {
                display:flex;
                align-items:center;
                gap:6px;
            }

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

            .cl-btn.primary {
                background:#226fa8;
                border-color:#185986;
                color:#fff;
            }

            .cl-small.danger {
                color:#a12626;
            }

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

            .cl-list {
                display:grid;
            }

            .cl-row {
                display:grid;
                grid-template-columns:168px minmax(0,1fr) 90px 90px 116px;
                gap:10px;
                align-items:center;
                padding:10px;
                border-top:1px solid var(--line2);
            }

            .cl-row:first-child {
                border-top:0;
            }

            .cl-row.is-selected {
                background:#edf6fc;
                box-shadow:inset 3px 0 0 #226fa8;
            }

            .cl-media {
                width:168px;
                aspect-ratio:16/7;
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

            .cl-media img, .cl-media video {
                width:100%;
                height:100%;
                object-fit:cover;
            }

            .cl-main strong {
                display:block;
                font-size:14px;
                overflow-wrap:anywhere;
            }

            .cl-main span {
                display:block;
                margin-top:3px;
                font-size:12px;
                color:var(--muted);
                overflow-wrap:anywhere;
            }

            .cl-status {
                display:inline-flex;
                gap:5px;
            }

            .cl-dot {
                width:13px;
                height:13px;
                border-radius:999px;
                border:1px solid transparent;
                opacity:.35;
                cursor:pointer;
            }

            .cl-dot.yes {
                background:#38c867;
                border-color:#0f7a38;
            }

            .cl-dot.no {
                background:#f04444;
                border-color:#9e1f1f;
            }

            .cl-dot.is-active {
                opacity:1;
                box-shadow:0 0 0 1px rgba(15,23,42,.18), inset 0 0 0 2px rgba(255,255,255,.55);
            }

            .cl-actions {
                display:flex;
                gap:6px;
                justify-content:flex-end;
                flex-wrap:wrap;
            }

            .cl-empty {
                padding:24px;
                text-align:center;
                color:#64748b;
                font-weight:800;
            }

            @media (max-width:1250px) {
                .cl-layout {
                    grid-template-columns:1fr;
                }

                .cl-form {
                    position:static;
                    max-height:none;
                    overflow:visible;
                }
            }

            @media (max-width:900px) {
                .cl-row {
                    grid-template-columns:1fr;
                }

                .cl-media {
                    width:100%;
                }

                .cl-actions {
                    justify-content:flex-start;
                }
            }

            @media (max-width:720px) {
                .cl-grid {
                    grid-template-columns:1fr;
                }
            }
        </style>

        <section class="cl-head">
            <div class="cl-title">
                <h1>Εικόνες αρχικής / banners</h1>
                <p>Banner Placeholders για το homepage carousel. Κάθε banner ενεργοποιείται ξεχωριστά.</p>
            </div>

            <button type="button" wire:click="createNew" class="cl-btn primary">Νέο banner</button>
        </section>

        <div class="cl-layout">
            <section class="cl-card">
                <div class="cl-panel-head">
                    <span>Banner Placeholders</span>

                    @unless($tableReady)
                        <span class="cl-pill">Τρέξε migrations</span>
                    @endunless
                </div>

                <div class="cl-list">
                    @forelse($banners as $banner)
                        <div class="cl-row {{ (int) $editingId === (int) $banner['id'] ? 'is-selected' : '' }}">
                            <div class="cl-media">
                                @if($banner['media'] && $banner['media_type'] === 'video')
                                    <video src="{{ $banner['media'] }}" muted playsinline></video>
                                @elseif($banner['media'])
                                    <img src="{{ $banner['media'] }}" alt="{{ $banner['title'] ?: 'Banner' }}">
                                @else
                                    Media
                                @endif
                            </div>

                            <div class="cl-main">
                                <strong>{{ $banner['title'] ?: 'Χωρίς τίτλο' }}</strong>
                                <span>{{ $banner['subtitle'] ?: $banner['link_url'] ?: 'Χωρίς κείμενο/link' }}</span>
                                <span>{{ $banner['period'] }}</span>
                            </div>

                            <span class="cl-pill">{{ $banner['media_type'] === 'video' ? 'Video' : 'Εικόνα' }}</span>
                            <span class="cl-pill">Σειρά {{ $banner['sort_order'] }}</span>

                            <div class="cl-actions">
                                <span class="cl-status">
                                    <button type="button" wire:click="toggleBanner({{ $banner['id'] }}, true)" class="cl-dot yes {{ $banner['is_active'] ? 'is-active' : '' }}" title="Ενεργό"></button>
                                    <button type="button" wire:click="toggleBanner({{ $banner['id'] }}, false)" class="cl-dot no {{ ! $banner['is_active'] ? 'is-active' : '' }}" title="Ανενεργό"></button>
                                </span>

                                <button type="button" wire:click="editBanner({{ $banner['id'] }})" class="cl-small">Edit</button>
                                <button type="button" wire:click="deleteBanner({{ $banner['id'] }})" wire:confirm="Να διαγραφεί το banner;" class="cl-small danger">Delete</button>
                            </div>
                        </div>
                    @empty
                        <div class="cl-empty">Δεν υπάρχουν banners.</div>
                    @endforelse
                </div>
            </section>

            <aside class="cl-card cl-form">
                <div class="cl-panel-head">
                    <span>{{ $editingId ? 'Επεξεργασία banner' : 'Νέο banner' }}</span>
                    <span class="cl-pill">Home</span>
                </div>

                <form wire:submit="saveBanner" class="cl-form-body">
                    <div class="cl-field">
                        <label>Τύπος</label>
                        <select wire:model.live="mediaTypeInput" class="cl-select">
                            <option value="image">Εικόνα</option>
                            <option value="video">Video</option>
                        </select>
                    </div>

                    <div class="cl-field">
                        <label>Τίτλος</label>
                        <input type="text" wire:model.live="titleInput" class="cl-input">
                        <p style="margin:6px 0 0;color:#5d7285;font-size:12px;font-weight:700;">Προαιρετικό. Άφησέ το κενό για να μην εμφανιστεί τίτλος.</p>
                    </div>

                    <div class="cl-field">
                        <label>Υπότιτλος</label>
                        <input type="text" wire:model.live="subtitleInput" class="cl-input">
                        <p style="margin:6px 0 0;color:#5d7285;font-size:12px;font-weight:700;">Προαιρετικό. Άφησέ το κενό για να μην εμφανιστεί υπότιτλος.</p>
                    </div>

                    <div class="cl-field">
                        <label>Upload εικόνας / video</label>
                        <input type="file" wire:model="mediaUpload" class="cl-input" accept="image/*,video/*">
                    </div>

                    <div class="cl-field">
                        <label>Ή media URL</label>
                        <input type="text" wire:model.live="mediaUrlInput" class="cl-input" placeholder="https://...">
                    </div>

                    <div class="cl-field">
                        <label>Προϊόντα foreground (SKU / EAN / MPN / code)</label>
                        <textarea wire:model.live="productRefsInput" class="cl-input" rows="4" placeholder="Ένα προϊόν ανά γραμμή ή χωρισμένα με κόμμα. Π.χ. 243055, UCG-MAX, 0810084691234"></textarea>
                        <p style="margin:6px 0 0;color:#5d7285;font-size:12px;font-weight:700;">Αν βάλεις προϊόντα, εμφανίζονται δεξιά στο banner και το media δουλεύει σαν background. Αν δεν βάλεις προϊόντα, το media εμφανίζεται καθαρά ως foreground εικόνα/video.</p>
                    </div>

                    <div class="cl-grid">
                        <div class="cl-field">
                            <label>Link</label>
                            <input type="text" wire:model.live="linkUrlInput" class="cl-input" placeholder="/offers">
                        </div>

                        <div class="cl-field">
                            <label>Κουμπί</label>
                            <input type="text" wire:model.live="buttonLabelInput" class="cl-input" placeholder="Δες τώρα">
                            <p style="margin:6px 0 0;color:#5d7285;font-size:12px;font-weight:700;">Άφησέ το κενό για να μην εμφανιστεί κουμπί στο banner.</p>
                        </div>
                    </div>

                    <div class="cl-field">
                        <label>Σειρά</label>
                        <input type="number" wire:model.live="sortOrderInput" class="cl-input">
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

                        <label>
                            <input type="checkbox" wire:model.live="opensNewTabInput"> Νέο tab
                        </label>
                    </div>

                    <button type="submit" class="cl-btn primary">Αποθήκευση</button>
                </form>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
