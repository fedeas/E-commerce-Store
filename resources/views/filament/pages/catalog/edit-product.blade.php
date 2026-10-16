<x-filament-panels::page>
    <style>
        .cl-edit-wrap {
            width: 100%;
            max-width: none;
            font-size: 13px;
            color: #1f2937;
        }

        .cl-edit-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            border: 1px solid #b9c9d8;
            border-radius: 16px;
            background: linear-gradient(180deg, #d7edf9 0%, #c3e3f4 100%);
            margin-bottom: 14px;
        }

        .cl-edit-title {
            font-size: 18px;
            font-weight: 800;
            color: #14314b;
        }

        .cl-edit-subtitle {
            font-size: 12px;
            color: #446277;
            margin-top: 2px;
        }

        .cl-edit-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .cl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 8px 13px;
            border-radius: 10px;
            border: 1px solid #b7c5d1;
            background: #ffffff;
            font-weight: 800;
            font-size: 12px;
            cursor: pointer;
            transition: .15s ease;
        }

        .cl-btn:hover {
            filter: brightness(.98);
            transform: translateY(-1px);
        }

        .cl-btn-save {
            background: #14853b;
            border-color: #0f6b2f;
            color: #ffffff;
        }

        .cl-btn-back {
            background: #f8fafc;
            color: #334155;
        }

        .cl-btn-delete {
            background: #c92525;
            border-color: #a61e1e;
            color: #ffffff;
        }

        .cl-edit-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 14px;
        }

        .cl-panel {
            border: 1px solid #d8e1ea;
            border-radius: 16px;
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .cl-panel-title {
            padding: 11px 14px;
            font-size: 13px;
            font-weight: 900;
            color: #14314b;
            background: #eef7fc;
            border-bottom: 1px solid #d8e1ea;
        }

        .cl-panel-body {
            padding: 14px;
        }

        .cl-form-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cl-form-table th,
        .cl-form-table td {
            padding: 8px 7px;
            border-bottom: 1px solid #eef2f6;
            vertical-align: top;
        }

        .cl-form-table th {
            width: 230px;
            text-align: left;
            font-size: 12px;
            font-weight: 800;
            color: #475569;
            background: #fbfdff;
        }

        .cl-input,
        .cl-select,
        .cl-textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 8px 10px;
            background: #ffffff;
            font-size: 13px;
            outline: none;
        }

        .cl-input:focus,
        .cl-select:focus,
        .cl-textarea:focus {
            border-color: #2e6fa7;
            box-shadow: 0 0 0 3px rgba(46, 111, 167, .12);
        }

        .cl-textarea {
            min-height: 130px;
            resize: vertical;
            font-family: inherit;
        }

        .cl-textarea-big {
            min-height: 260px;
        }

        .cl-radio-row,
        .cl-check-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
        }

        .cl-radio-row label,
        .cl-check-row label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .cl-price-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .cl-price-box {
            padding: 10px;
            border: 1px solid #e1e8ef;
            border-radius: 12px;
            background: #fbfdff;
        }

        .cl-price-box label {
            display: block;
            font-size: 11px;
            font-weight: 900;
            color: #64748b;
            margin-bottom: 5px;
        }

        .cl-sticky-actions {
            position: fixed;
            right: 22px;
            bottom: 24px;
            z-index: 60;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            background: rgba(255, 255, 255, .94);
            border: 1px solid #d8e1ea;
            border-radius: 16px;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .18);
        }

        .cl-gallery-preview {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .cl-gallery-preview img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: contain;
            border: 1px solid #e1e8ef;
            border-radius: 10px;
            background: #f8fafc;
        }

        @media (max-width: 1180px) {
            .cl-edit-grid {
                grid-template-columns: 1fr;
            }

            .cl-sticky-actions {
                left: 12px;
                right: 12px;
                justify-content: flex-end;
            }
        }

        @media (max-width: 720px) {
            .cl-edit-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .cl-form-table,
            .cl-form-table tbody,
            .cl-form-table tr,
            .cl-form-table th,
            .cl-form-table td {
                display: block;
                width: 100%;
            }

            .cl-price-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="cl-edit-wrap">
        <div class="cl-edit-head">
            <div>
                <div class="cl-edit-title">Επεξεργασία Προϊόντος</div>
                <div class="cl-edit-subtitle">
                    ID: {{ $productId }} @if($legacyId) / Legacy ID: {{ $legacyId }} @endif
                </div>
            </div>

            <div class="cl-edit-actions">
                <button type="button" wire:click="save" class="cl-btn cl-btn-save">Αποθήκευση</button>
                <button type="button" wire:click="backToCatalog" class="cl-btn cl-btn-back">Επιστροφή</button>
                <button type="button" wire:click="deleteProduct" wire:confirm="Να διαγραφεί το προϊόν;" class="cl-btn cl-btn-delete">Διαγραφή</button>
            </div>
        </div>

        <div class="cl-edit-grid">
            <div>
                <div class="cl-panel">
                    <div class="cl-panel-title">Βασικά στοιχεία</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Κατάσταση Προϊόντος</th>
                                    <td>
                                        <div class="cl-radio-row">
                                            <label for="edit_product_blade_active_1">
                                                <input type="radio" wire:model.live="active" value="1" id="edit_product_blade_active_1" name="active">
                                                Στην Αποθήκη / Ενεργό
                                            </label>
                                            <label for="edit_product_blade_active_2">
                                                <input type="radio" wire:model.live="active" value="0" id="edit_product_blade_active_2" name="active">
                                                Εκτός Αποθήκης / Ανενεργό
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Flags</th>
                                    <td>
                                        <div class="cl-check-row">
                                            <label for="edit_product_blade_boxnow_3"><input type="checkbox" wire:model.live="boxnow" id="edit_product_blade_boxnow_3" name="boxnow"> Boxnow</label>
                                            <label for="edit_product_blade_article11_4"><input type="checkbox" wire:model.live="article11" id="edit_product_blade_article11_4" name="article11"> Άρθρο 11</label>
                                            <label for="edit_product_blade_shippingDisabled_5"><input type="checkbox" wire:model.live="shippingDisabled" id="edit_product_blade_shippingDisabled_5" name="shippingDisabled"> Απενεργοποίηση αποστολής</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Κατηγορία</th>
                                    <td>
                                        <select wire:model.live="categoryId" class="cl-select" id="edit_product_blade_categoryId_6" name="categoryId">
                                            <option value="">-- καμία --</option>
                                            @foreach($categories as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Κατασκευαστής</th>
                                    <td>
                                        <select wire:model.live="brandId" class="cl-select" id="edit_product_blade_brandId_7" name="brandId">
                                            <option value="">-- κανένα --</option>
                                            @foreach($brands as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Προμηθευτής</th>
                                    <td>
                                        <select wire:model.live="supplierId" class="cl-select" id="edit_product_blade_supplierId_8" name="supplierId">
                                            <option value="">-- κανένας --</option>
                                            @foreach($suppliers as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Ονομασία Προϊόντος</th>
                                    <td><input type="text" wire:model.live="productTitle" class="cl-input" id="edit_product_blade_productTitle_9" name="productTitle"></td>
                                </tr>

                                <tr>
                                    <th>Κωδικός Προϊόντος</th>
                                    <td><input type="text" wire:model.live="code" class="cl-input" id="edit_product_blade_code_10" name="code"></td>
                                </tr>

                                <tr>
                                    <th>Κωδικός κατασκευαστή MPN</th>
                                    <td><input type="text" wire:model.live="mpn" class="cl-input" id="edit_product_blade_mpn_11" name="mpn"></td>
                                </tr>

                                <tr>
                                    <th>Barcode / EAN</th>
                                    <td><input type="text" wire:model.live="ean" class="cl-input" id="edit_product_blade_ean_12" name="ean"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cl-panel">
                    <div class="cl-panel-title">Τιμές</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Φορολογική Κλάση</th>
                                    <td>
                                        <select wire:model.live="vatRate" class="cl-select" id="edit_product_blade_vatRate_13" name="vatRate">
                                            <option value="19">19%</option>
                                            <option value="0">ΦΠΑ 0%</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Τιμές προϊόντος</th>
                                    <td>
                                        <div class="cl-price-grid">
                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_costExVat_14">Τιμή αγοράς χωρίς ΦΠΑ</label>
                                                <input type="text" wire:model.live="costExVat" class="cl-input" id="edit_product_blade_costExVat_14" name="costExVat">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_retailIncVat_15">Τιμή πώλησης με ΦΠΑ</label>
                                                <input type="text" wire:model.live="retailIncVat" class="cl-input" id="edit_product_blade_retailIncVat_15" name="retailIncVat">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_skroutzIncVat_16">Τιμή Skroutz με ΦΠΑ</label>
                                                <input type="text" wire:model.live="skroutzIncVat" class="cl-input" id="edit_product_blade_skroutzIncVat_16" name="skroutzIncVat">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_discountIncVat_17">Τιμή έκπτωσης με ΦΠΑ</label>
                                                <input type="text" wire:model.live="discountIncVat" class="cl-input" id="edit_product_blade_discountIncVat_17" name="discountIncVat">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_wholesaleIncVat_18">Χονδρική</label>
                                                <input type="text" wire:model.live="wholesaleIncVat" class="cl-input" id="edit_product_blade_wholesaleIncVat_18" name="wholesaleIncVat">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_silverIncVat_19">Silver</label>
                                                <input type="text" wire:model.live="silverIncVat" class="cl-input" id="edit_product_blade_silverIncVat_19" name="silverIncVat">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_goldIncVat_20">Gold</label>
                                                <input type="text" wire:model.live="goldIncVat" class="cl-input" id="edit_product_blade_goldIncVat_20" name="goldIncVat">
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Λειτουργίες γέφυρας</th>
                                    <td>
                                        <div class="cl-check-row">
                                            <label for="edit_product_blade_statusUpdateEnabled_21"><input type="checkbox" wire:model.live="statusUpdateEnabled" id="edit_product_blade_statusUpdateEnabled_21" name="statusUpdateEnabled"> Ενημέρωση κατάστασης</label>
                                            <label for="edit_product_blade_purchasePriceUpdateEnabled_22"><input type="checkbox" wire:model.live="purchasePriceUpdateEnabled" id="edit_product_blade_purchasePriceUpdateEnabled_22" name="purchasePriceUpdateEnabled"> Ενημέρωση τιμής αγοράς</label>
                                            <label for="edit_product_blade_priceUpdateEnabled_23"><input type="checkbox" wire:model.live="priceUpdateEnabled" id="edit_product_blade_priceUpdateEnabled_23" name="priceUpdateEnabled"> Ενημέρωση τιμής πώλησης</label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Προσαυξήσεις / όρια</th>
                                    <td>
                                        <div class="cl-price-grid">
                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_addonPriceFixed_24">Προσαύξηση fixed</label>
                                                <input type="text" wire:model.live="addonPriceFixed" class="cl-input" id="edit_product_blade_addonPriceFixed_24" name="addonPriceFixed">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_addonPricePercent_25">Προσαύξηση ποσοστό</label>
                                                <input type="text" wire:model.live="addonPricePercent" class="cl-input" id="edit_product_blade_addonPricePercent_25" name="addonPricePercent">
                                            </div>

                                            <div class="cl-price-box">
                                                <label for="edit_product_blade_minimumPriceIncVat_26">Κατώτατο όριο τιμής με ΦΠΑ</label>
                                                <input type="text" wire:model.live="minimumPriceIncVat" class="cl-input" id="edit_product_blade_minimumPriceIncVat_26" name="minimumPriceIncVat">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cl-panel">
                    <div class="cl-panel-title">Περιγραφές</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Σύντομη περιγραφή</th>
                                    <td><textarea wire:model.live="shortDescription" class="cl-textarea" id="edit_product_blade_shortDescription_27" name="shortDescription"></textarea></td>
                                </tr>

                                <tr>
                                    <th>Περιγραφή προϊόντος</th>
                                    <td><textarea wire:model.live="description" class="cl-textarea cl-textarea-big" id="edit_product_blade_description_28" name="description"></textarea></td>
                                </tr>

                                <tr>
                                    <th>Τεχνικά χαρακτηριστικά</th>
                                    <td><textarea wire:model.live="specs" class="cl-textarea cl-textarea-big" id="edit_product_blade_specs_29" name="specs"></textarea></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cl-panel">
                    <div class="cl-panel-title">Meta στοιχεία</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Meta description</th>
                                    <td><input type="text" wire:model.live="metaDescription" class="cl-input" id="edit_product_blade_metaDescription_30" name="metaDescription"></td>
                                </tr>

                                <tr>
                                    <th>Meta keywords</th>
                                    <td><input type="text" wire:model.live="metaKeywords" class="cl-input" id="edit_product_blade_metaKeywords_31" name="metaKeywords"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="cl-panel">
                    <div class="cl-panel-title">Stock / Διαθεσιμότητα</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>

                                <tr>
                                    <th>Ελάχιστη ποσότητα αγοράς</th>
                                    <td><input type="text" wire:model.live="minQuantity" class="cl-input" id="edit_product_blade_minQuantity_32" name="minQuantity"></td>
                                </tr>

                                <tr>
                                    <th>Σετ / Πώληση ανά</th>
                                    <td><input type="text" wire:model.live="setQuantity" class="cl-input" id="edit_product_blade_setQuantity_33" name="setQuantity"></td>
                                </tr>

                                <tr>
                                    <th>Διαθέσιμο από</th>
                                    <td><input type="date" wire:model.live="availableFrom" class="cl-input" id="edit_product_blade_availableFrom_34" name="availableFrom"></td>
                                </tr>

                                <tr>
                                    <th>Διαθεσιμότητα</th>
                                    <td><input type="text" wire:model.live="stockText" class="cl-input" placeholder="Available from 1 to 3 days" id="edit_product_blade_stockText_35" name="stockText"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cl-panel">
                    <div class="cl-panel-title">Εικόνα / Media</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Image path</th>
                                    <td><input type="text" wire:model.live="imagePath" class="cl-input" id="edit_product_blade_imagePath_36" name="imagePath"></td>
                                </tr>

                                <tr>
                                    <th>Main image</th>
                                    <td><input type="text" wire:model.live="mainImage" class="cl-input" id="edit_product_blade_mainImage_37" name="mainImage"></td>
                                </tr>

                                <tr>
                                    <th>Gallery images</th>
                                    <td>
                                        <textarea wire:model.live="galleryImages" class="cl-textarea" placeholder="One image URL per line"></textarea>
                                        @php
                                            $previewImages = collect(preg_split('/[\r\n]+/', $galleryImages) ?: [])->map(fn ($url) => trim($url))->filter()->take(6);
                                        @endphp
                                        @if($previewImages->isNotEmpty())
                                            <div class="cl-gallery-preview">
                                                @foreach($previewImages as $previewImage)
                                                    <img src="{{ $previewImage }}" alt="">
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>Youtube URL</th>
                                    <td><input type="text" wire:model.live="youtubeUrl" class="cl-input" id="edit_product_blade_youtubeUrl_38" name="youtubeUrl"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cl-panel">
                    <div class="cl-panel-title">Διαστάσεις / Βάρος</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Διαστάσεις</th>
                                    <td><input type="text" wire:model.live="dimensions" class="cl-input" placeholder="Πλάτος x Ύψος x Πάχος" id="edit_product_blade_dimensions_39" name="dimensions"></td>
                                </tr>

                                <tr>
                                    <th>Βάρος kg</th>
                                    <td><input type="text" wire:model.live="weightKg" class="cl-input" id="edit_product_blade_weightKg_40" name="weightKg"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cl-panel">
                    <div class="cl-panel-title">Γρήγορες πληροφορίες</div>
                    <div class="cl-panel-body">
                        <table class="cl-form-table">
                            <tbody>
                                <tr>
                                    <th>Product ID</th>
                                    <td>{{ $productId }}</td>
                                </tr>

                                <tr>
                                    <th>Legacy ID</th>
                                    <td>{{ $legacyId ?: '-' }}</td>
                                </tr>

                                <tr>
                                    <th>Code</th>
                                    <td>{{ $code ?: '-' }}</td>
                                </tr>

                                <tr>
                                    <th>MPN</th>
                                    <td>{{ $mpn ?: '-' }}</td>
                                </tr>

                                <tr>
                                    <th>EAN</th>
                                    <td>{{ $ean ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="cl-sticky-actions">
            <button type="button" wire:click="save" class="cl-btn cl-btn-save">Αποθήκευση</button>
            <button type="button" wire:click="backToCatalog" class="cl-btn cl-btn-back">Ακύρωση / Επιστροφή</button>
            <button type="button" wire:click="deleteProduct" wire:confirm="Να διαγραφεί το προϊόν;" class="cl-btn cl-btn-delete">Διαγραφή</button>
        </div>
    </div>
</x-filament-panels::page>
