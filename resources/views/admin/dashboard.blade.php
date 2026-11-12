<x-layouts.dashboard title="Dashboard Admin" heading="Dashboard Admin">
 
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Halo, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-sm text-gray-600">
            Anda memiliki seluruh fitur Staff, ditambah pengelolaan akun dan pemantauan log aktivitas.
        </p>
    </div>
 
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach (['Kelola Akun', 'Log Aktivitas Staff'] as $card)
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-5 text-center">
                <p class="font-medium text-gray-700">{{ $card }}</p>
                <p class="mt-1 text-xs text-gray-500">Segera hadir</p>
            </div>
        @endforeach
    </div>
 
</x-layouts.dashboard>
 