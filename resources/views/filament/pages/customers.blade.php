<x-filament-panels::page>
    @php
        $users = $users ?? collect();
        $selectedUser = $selectedUser ?? null;
        $stats = $stats ?? ['total' => 0, 'retail' => 0, 'b2b' => 0, 'pending' => 0];

        $groupLabel = function ($user): string {
            return ($user?->customer_group ?? 'retail') === 'b2b' ? 'B2B / Χονδρική' : 'Retail / Λιανική';
        };

        $isPending = fn ($user): bool => $user
            && $user->b2b_requested_at
            && ! $user->b2b_approved_at
            && ! $user->b2b_rejected_at
            && ! $user->isB2b();
    @endphp

    <div class="cl-customers-admin">
        <style>
            .fi-header { display:none !important; }
            .fi-page-content { background:#edf3f8; }
            .cl-customers-admin { --border:#c5d3df; --border-dark:#9fb6c9; --text:#102033; --muted:#607080; --green:#159447; --red:#c73333; display:grid; gap:12px; color:var(--text); }
            .cl-top-title, .cl-panel-head, .cl-side-head { display:flex; align-items:center; justify-content:space-between; gap:12px; border:1px solid var(--border-dark); background:linear-gradient(180deg,#dceefa 0%,#c7e0f2 100%); }
            .cl-top-title { padding:12px; border-radius:8px; }
            .cl-top-title h1 { margin:0; font-size:21px; font-weight:950; line-height:1.1; }
            .cl-top-title p { margin:4px 0 0; color:var(--muted); font-size:13px; font-weight:700; }
            .cl-stat-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
            .cl-stat { padding:12px; border:1px solid var(--border); border-radius:8px; background:#fff; }
            .cl-stat small { display:block; color:var(--muted); font-size:10px; font-weight:950; text-transform:uppercase; letter-spacing:.9px; }
            .cl-stat strong { display:block; margin-top:5px; font-size:24px; font-weight:950; }
            .cl-tools { display:grid; grid-template-columns:minmax(260px,1fr) 220px auto; gap:8px; padding:10px; border:1px solid var(--border-dark); border-radius:8px; background:#f8fbff; }
            .cl-input, .cl-select, .cl-textarea { width:100%; border:1px solid #a9bed1; border-radius:7px; background:#fff; padding:0 10px; font:inherit; font-size:13px; }
            .cl-input, .cl-select { height:36px; }
            .cl-textarea { min-height:76px; padding-top:9px; resize:vertical; }
            .cl-layout { display:grid; grid-template-columns:minmax(0,1fr) 390px; gap:12px; align-items:start; }
            .cl-panel, .cl-side { border:1px solid var(--border-dark); border-radius:8px; background:#fff; overflow:hidden; }
            .cl-panel-head, .cl-side-head { padding:9px 10px; border-width:0 0 1px; }
            .cl-panel-head strong, .cl-side-head strong { color:#18364f; font-size:14px; font-weight:950; }
            .cl-table-wrap { overflow:auto; }
            .cl-table { width:100%; min-width:980px; border-collapse:collapse; }
            .cl-table th { padding:8px; border-bottom:1px solid var(--border); border-right:1px solid #e2edf5; background:#edf6fc; color:#28455e; font-size:12px; font-weight:950; text-align:left; white-space:nowrap; }
            .cl-table td { padding:8px; border-bottom:1px solid #e5edf3; border-right:1px solid #edf3f8; background:#fff; font-size:13px; vertical-align:middle; }
            .cl-table tr:nth-child(even) td { background:#fbfdff; }
            .cl-table tr.is-active td { background:#eaf5ff; }
            .cl-row-btn { width:100%; border:0; background:transparent; color:inherit; text-align:left; font:inherit; font-weight:850; cursor:pointer; }
            .cl-muted { color:var(--muted); font-size:12px; font-weight:700; }
            .cl-chip { display:inline-flex; align-items:center; min-height:24px; padding:0 8px; border-radius:999px; background:#edf3f8; color:#334155; font-size:11px; font-weight:950; white-space:nowrap; }
            .cl-chip.green { background:#e8f7ee; color:#107a3b; }
            .cl-chip.orange { background:#fff4df; color:#9a5f00; }
            .cl-btns { display:flex; flex-wrap:wrap; gap:6px; }
            .cl-btn { min-height:30px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--border-dark); border-radius:7px; background:linear-gradient(180deg,#fff 0%,#edf5fb 100%); color:#1f3548; padding:0 9px; font-size:12px; font-weight:900; cursor:pointer; }
            .cl-btn.primary { background:linear-gradient(180deg,#159447 0%,#0f7536 100%); border-color:#0c642d; color:#fff; }
            .cl-btn.dark { background:linear-gradient(180deg,#34495a 0%,#263746 100%); border-color:#20303d; color:#fff; }
            .cl-btn.red { background:linear-gradient(180deg,#d94b4b 0%,#b92d2d 100%); border-color:#9d2323; color:#fff; }
            .cl-detail { padding:12px; display:grid; gap:10px; }
            .cl-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:9px; }
            .cl-form-field { display:grid; gap:5px; }
            .cl-form-field.full { grid-column:1 / -1; }
            .cl-form-field label { color:#334155; font-size:10px; font-weight:950; text-transform:uppercase; letter-spacing:.7px; }
            .cl-error { color:#b91c1c; font-size:11px; font-weight:850; }
            .cl-detail-item { padding:10px; border:1px solid #e2edf5; border-radius:8px; background:#fbfdff; }
            .cl-detail-item small { display:block; color:var(--muted); font-size:10px; font-weight:950; text-transform:uppercase; letter-spacing:.7px; }
            .cl-detail-item strong, .cl-detail-item span { display:block; margin-top:5px; font-size:13px; line-height:1.35; overflow-wrap:anywhere; }
            @media (max-width:1100px) { .cl-layout { grid-template-columns:1fr; } .cl-stat-row { grid-template-columns:repeat(2,minmax(0,1fr)); } }
            @media (max-width:680px) { .cl-tools, .cl-stat-row, .cl-form-grid { grid-template-columns:1fr; } }
        </style>

        <section class="cl-top-title">
            <div>
                <h1>Πελάτες</h1>
                <p>Retail/B2B λογαριασμοί, αιτήσεις χονδρικής και στοιχεία εταιρείας.</p>
            </div>
            <span class="cl-chip">Customer groups</span>
        </section>

        <div class="cl-stat-row">
            <div class="cl-stat"><small>Σύνολο</small><strong>{{ $stats['total'] }}</strong></div>
            <div class="cl-stat"><small>Λιανική</small><strong>{{ $stats['retail'] }}</strong></div>
            <div class="cl-stat"><small>Χονδρική</small><strong>{{ $stats['b2b'] }}</strong></div>
            <div class="cl-stat"><small>Αιτήσεις B2B</small><strong>{{ $stats['pending'] }}</strong></div>
        </div>

        <div class="cl-tools">
            <input class="cl-input" type="search" wire:model.live.debounce.350ms="search" placeholder="Αναζήτηση: όνομα, email, εταιρεία, VAT, τηλέφωνο">
            <select class="cl-select" wire:model.live="groupFilter">
                <option value="all">Όλοι οι πελάτες</option>
                <option value="retail">Retail / Λιανική</option>
                <option value="b2b">B2B / Χονδρική</option>
                <option value="pending">Αιτήσεις B2B</option>
            </select>
            <button class="cl-btn primary" type="button" wire:click="newCustomer">+ Νέος χρήστης</button>
        </div>

        <div class="cl-layout">
            <section class="cl-panel">
                <div class="cl-panel-head">
                    <strong>Λίστα πελατών</strong>
                    <span class="cl-chip">{{ $users->count() }} εμφανίζονται</span>
                </div>

                <div class="cl-table-wrap">
                    <table class="cl-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Πελάτης</th>
                                <th>Email</th>
                                <th>Group</th>
                                <th>Αίτηση</th>
                                <th>Εταιρεία</th>
                                <th>Τηλέφωνο</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="{{ (int) $selectedUserId === (int) $user->id ? 'is-active' : '' }}" wire:key="customer-{{ $user->id }}">
                                    <td>{{ $user->id }}</td>
                                    <td><button class="cl-row-btn" type="button" wire:click="selectUser({{ $user->id }})">{{ $user->name }}</button></td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="cl-chip {{ $user->isB2b() ? 'green' : '' }}">{{ $groupLabel($user) }}</span></td>
                                    <td>
                                        @if($isPending($user))
                                            <span class="cl-chip orange">Εκκρεμεί</span>
                                        @elseif($user->b2b_approved_at)
                                            <span class="cl-chip green">Εγκρίθηκε</span>
                                        @elseif($user->b2b_rejected_at)
                                            <span class="cl-chip">Απορρίφθηκε</span>
                                        @else
                                            <span class="cl-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->company_name ?: '-' }}</td>
                                    <td>{{ $user->mobile ?: ($user->phone ?: '-') }}</td>
                                    <td>
                                        <div class="cl-btns">
                                            <button class="cl-btn primary" type="button" wire:click="setCustomerGroup({{ $user->id }}, 'b2b')">B2B</button>
                                            <button class="cl-btn dark" type="button" wire:click="setCustomerGroup({{ $user->id }}, 'retail')">Retail</button>
                                            @if($isPending($user))
                                                <button class="cl-btn red" type="button" wire:click="rejectB2bRequest({{ $user->id }})">Reject</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8">Δεν βρέθηκαν πελάτες.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="cl-side">
                <div class="cl-side-head">
                    <strong>{{ $formUserId ? 'Επεξεργασία χρήστη' : 'Νέος χρήστης' }}</strong>
                    @if($selectedUser)<span class="cl-chip">{{ $groupLabel($selectedUser) }}</span>@endif
                </div>

                <div class="cl-detail">
                    <form wire:submit.prevent="saveCustomer" class="cl-form-grid">
                        <div class="cl-form-field full">
                            <label>Όνομα</label>
                            <input class="cl-input" type="text" wire:model.defer="name">
                            @error('name')<span class="cl-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="cl-form-field full">
                            <label>Email</label>
                            <input class="cl-input" type="email" wire:model.defer="email">
                            @error('email')<span class="cl-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="cl-form-field">
                            <label>Password {{ $formUserId ? '(κενό = δεν αλλάζει)' : '' }}</label>
                            <input class="cl-input" type="password" wire:model.defer="password" autocomplete="new-password">
                            @error('password')<span class="cl-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="cl-form-field">
                            <label>Group</label>
                            <select class="cl-select" wire:model.defer="customerGroup">
                                <option value="retail">Retail / Λιανική</option>
                                <option value="b2b">B2B / Χονδρική</option>
                            </select>
                        </div>
                        <div class="cl-form-field">
                            <label>Τηλέφωνο</label>
                            <input class="cl-input" type="text" wire:model.defer="phone">
                        </div>
                        <div class="cl-form-field">
                            <label>Κινητό</label>
                            <input class="cl-input" type="text" wire:model.defer="mobile">
                        </div>
                        <div class="cl-form-field full">
                            <label>Εταιρεία</label>
                            <input class="cl-input" type="text" wire:model.defer="companyName">
                        </div>
                        <div class="cl-form-field">
                            <label>VAT NR</label>
                            <input class="cl-input" type="text" wire:model.defer="vatNumber">
                        </div>
                        <div class="cl-form-field">
                            <label>Occupation</label>
                            <input class="cl-input" type="text" wire:model.defer="occupation">
                        </div>
                        <div class="cl-form-field full">
                            <label>Company address</label>
                            <input class="cl-input" type="text" wire:model.defer="companyAddress">
                        </div>
                        <div class="cl-form-field">
                            <label>Πόλη</label>
                            <input class="cl-input" type="text" wire:model.defer="city">
                        </div>
                        <div class="cl-form-field">
                            <label>Τ.Κ.</label>
                            <input class="cl-input" type="text" wire:model.defer="postcode">
                        </div>
                        <div class="cl-form-field full">
                            <label>Σχόλια</label>
                            <textarea class="cl-textarea" wire:model.defer="customerNotes"></textarea>
                        </div>
                        <div class="cl-form-field full">
                            <div class="cl-btns">
                                <button class="cl-btn primary" type="submit">Αποθήκευση</button>
                                <button class="cl-btn" type="button" wire:click="newCustomer">Καθαρισμός</button>
                                @if($formUserId)
                                    <button class="cl-btn red" type="button" wire:click="deleteCustomer({{ $formUserId }})" wire:confirm="Να διαγραφεί ο χρήστης;">Διαγραφή</button>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($selectedUser)
                        <div class="cl-detail-item"><small>Πελάτης</small><strong>{{ $selectedUser->name }}</strong><span>{{ $selectedUser->email }}</span></div>
                        <div class="cl-detail-item"><small>B2B αίτηση</small><strong>{{ $isPending($selectedUser) ? 'Εκκρεμεί' : ($selectedUser->isB2b() ? 'Ενεργή χονδρική' : 'Λιανική') }}</strong><span>{{ optional($selectedUser->b2b_requested_at)->format('d/m/Y H:i') ?: '-' }}</span></div>
                        <div class="cl-detail-item"><small>Εταιρεία</small><strong>{{ $selectedUser->company_name ?: '-' }}</strong><span>VAT: {{ $selectedUser->vat_number ?: '-' }}</span></div>
                        <div class="cl-detail-item"><small>Δραστηριότητα</small><span>{{ $selectedUser->occupation ?: '-' }}</span></div>
                        <div class="cl-detail-item"><small>Διεύθυνση</small><span>{{ $selectedUser->company_address ?: '-' }} {{ $selectedUser->city ? ' / ' . $selectedUser->city : '' }} {{ $selectedUser->postcode ? ' / ' . $selectedUser->postcode : '' }}</span></div>
                        <div class="cl-detail-item"><small>Επικοινωνία</small><span>{{ $selectedUser->mobile ?: '-' }} {{ $selectedUser->phone ? ' / ' . $selectedUser->phone : '' }}</span></div>
                        <div class="cl-detail-item"><small>Σχόλια</small><span>{{ $selectedUser->customer_notes ?: '-' }}</span></div>
                    @else
                        <div class="cl-detail-item"><span>Επιλέξτε πελάτη από τη λίστα.</span></div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
