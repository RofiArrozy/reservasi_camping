<x-layouts.dashboard title="Dashboard" heading="Dashboard Saya">
 
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Halo, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-sm text-gray-600">
            Di sini Anda dapat melihat riwayat sewa, mengubah data diri, dan mengajukan pembatalan booking.
        </p>
    </div>
 
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach (['Riwayat Sewa', 'Profil Saya', 'Analitik'] as $card)
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-5 text-center">
                <p class="font-medium text-gray-700">{{ $card }}</p>
                <p class="mt-1 text-xs text-gray-500">Segera hadir</p>
            </div>
        @endforeach
    </div>
 
</x-layouts.dashboard>