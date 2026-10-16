<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Κατάλογος</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Διαχείριση προϊόντων, κατηγοριών, κατασκευαστών, ιδιοτήτων και μαζικών ενημερώσεων.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ \App\Filament\Pages\Catalog\CategoriesProducts::getUrl() }}" class="inline-flex items-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                        Κατηγορίες / Προϊόντα
                    </a>
                    <a href="{{ \App\Filament\Pages\Catalog\QuickUpdate::getUrl() }}" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Γρήγορη ανανέωση
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ \App\Filament\Pages\Catalog\CategoriesProducts::getUrl() }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Βασική διαχείριση</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Κατηγορίες / Προϊόντα</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Δέντρο κατηγοριών, κατάσταση, XML feeds και προβολή ανά site.</div>
            </a>

            <a href="{{ \App\Filament\Pages\Catalog\QuickUpdate::getUrl() }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Μαζική διαχείριση</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Γρήγορη ανανέωση</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Φίλτρα, επιλογές προϊόντων, εκτυπώσεις και γρήγορη επεξεργασία.</div>
            </a>

            <a href="{{ \App\Filament\Pages\Catalog\ExcelProductUpdate::getUrl() }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Excel</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Ενημέρωση ειδών από excel</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Μελλοντική εισαγωγή του υπάρχοντος products_export_all.xlsx.</div>
            </a>

            <a href="{{ \App\Filament\Pages\Catalog\Manufacturers::getUrl() }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Brands</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Κατασκευαστές</div>
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Καθαρή διαχείριση κατασκευαστών και αντιστοίχιση supplier brands.</div>
            </a>
        </div>
    </div>
</x-filament-panels::page>
