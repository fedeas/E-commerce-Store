<x-filament-panels::page>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $this->getTitle() }}</h2>
        <p class="mt-2 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
            Η ενότητα έχει προστεθεί στο νέο admin panel. Στα επόμενα βήματα θα συνδεθεί με πραγματικούς πίνακες, φόρμες, φίλτρα και λειτουργίες.
        </p>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-gray-50 p-5 dark:bg-gray-950">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Κατάσταση</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Σε προετοιμασία</div>
            </div>

            <div class="rounded-2xl bg-gray-50 p-5 dark:bg-gray-950">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">UI</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Modern layout</div>
            </div>

            <div class="rounded-2xl bg-gray-50 p-5 dark:bg-gray-950">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Επόμενο</div>
                <div class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Σύνδεση με δεδομένα</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
