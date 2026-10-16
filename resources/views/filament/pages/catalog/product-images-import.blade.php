<x-filament-panels::page>
    <style>
        .cl-import-box {
            max-width: 880px;
            border: 1px solid #d8e1ea;
            border-radius: 16px;
            background: #ffffff;
            overflow: hidden;
        }

        .cl-import-head {
            padding: 14px 16px;
            background: #eef7fc;
            border-bottom: 1px solid #d8e1ea;
            font-weight: 900;
            color: #14314b;
        }

        .cl-import-body {
            padding: 16px;
            display: grid;
            gap: 14px;
        }

        .cl-import-file {
            width: 100%;
            border: 1px dashed #9fb2c3;
            border-radius: 12px;
            padding: 18px;
            background: #fbfdff;
        }

        .cl-import-btn {
            justify-self: start;
            min-height: 38px;
            padding: 9px 15px;
            border: 1px solid #0f6b2f;
            border-radius: 10px;
            background: #14853b;
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .cl-import-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .cl-import-stat {
            padding: 12px;
            border: 1px solid #e1e8ef;
            border-radius: 12px;
            background: #fbfdff;
        }

        .cl-import-stat strong {
            display: block;
            font-size: 18px;
        }

        .cl-import-stat span {
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
        }
    </style>

    <div class="cl-import-box">
        <div class="cl-import-head">XML upload για product galleries</div>
        <div class="cl-import-body">
            <input type="file" wire:model="xmlFile" accept=".xml,text/xml,application/xml" class="cl-import-file">

            <button type="button" wire:click="import" class="cl-import-btn">
                Εισαγωγή εικόνων
            </button>

            <p style="margin:0;color:#64748b;font-size:13px;font-weight:700;">
                Υποστηρίζει nodes όπως <code>sku</code>, <code>ean</code>, <code>mpn</code> και <code>imageLinks</code>.
                Αν υπάρχουν πολλές εικόνες, βάλ’ τες στο <code>imageLinks</code> χωρισμένες με νέα γραμμή, κόμμα, pipe ή semicolon.
            </p>

            @if($lastStats)
                <div class="cl-import-stats">
                    <div class="cl-import-stat"><strong>{{ $lastStats['items'] }}</strong><span>Items</span></div>
                    <div class="cl-import-stat"><strong>{{ $lastStats['matched'] }}</strong><span>Matched</span></div>
                    <div class="cl-import-stat"><strong>{{ $lastStats['unmatched'] }}</strong><span>Unmatched</span></div>
                    <div class="cl-import-stat"><strong>{{ $lastStats['images'] }}</strong><span>Images</span></div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
