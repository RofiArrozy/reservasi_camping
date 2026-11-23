<x-layouts.dashboard title="Analitik" heading="Analitik Saya">

    @if ($summary['count'] === 0)
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
            <p>Belum ada data sewa yang dapat dianalisis.</p>
            <p class="mt-1 text-sm">Grafik muncul setelah Anda memiliki booking yang dikonfirmasi.</p>
            <div class="mt-4"><x-button :href="route('booking.create')">Pesan Sekarang</x-button></div>
        </div>
    @else

        {{-- Ringkasan --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Total sewa', $summary['count'] . ' kali'],
                ['Total hari menyewa', $summary['days'] . ' hari'],
                ['Rata-rata lama sewa', $summary['avgDays'] . ' hari'],
                ['Total biaya sewa', \App\Support\Format::rupiah($summary['spent'])],
            ] as [$label, $value])
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">

            {{-- Grafik 1: sewa per bulan --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Sewa per bulan</h2>
                <p class="text-xs text-gray-500">Jumlah sewa berdasarkan bulan tanggal mulai (6 bulan terakhir).</p>

                @php $maxMonthly = max(1, $monthly->max('count')); @endphp
                <div class="mt-4 grid grid-cols-6 gap-2" role="img" aria-label="Grafik batang jumlah sewa per bulan">
                    @foreach ($monthly as $month)
                        <div class="flex flex-col items-center">
                            <div class="flex h-40 w-full flex-col items-center justify-end">
                                <span class="mb-1 text-xs font-semibold text-gray-700">{{ $month['count'] }}</span>
                                <div class="w-full max-w-[3rem] rounded-t-md bg-emerald-500"
                                     style="height: {{ $month['count'] > 0 ? max(4, round($month['count'] / $maxMonthly * 80)) : 0 }}%"></div>
                            </div>
                            <span class="mt-2 text-center text-xs text-gray-500">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Grafik 2: lama sewa --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Sebaran lama sewa</h2>
                <p class="text-xs text-gray-500">Berapa kali Anda menyewa untuk durasi tertentu.</p>

                @php $maxDuration = max(1, max($durations)); @endphp
                <ul class="mt-4 space-y-3">
                    @foreach ($durations as $label => $count)
                        <li>
                            <div class="flex justify-between text-sm">
                                <span>{{ $label }}</span>
                                <span class="font-semibold">{{ $count }}×</span>
                            </div>
                            <div class="mt-1 h-2.5 rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full bg-sky-500" style="width: {{ round($count / $maxDuration * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Grafik 3: pembatalan --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Pembatalan</h2>
                <p class="text-xs text-gray-500">Status pengajuan pembatalan Anda.</p>

                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-lg bg-amber-50 p-3">
                        <p class="text-2xl font-bold text-amber-700">{{ $cancel['pending'] }}</p>
                        <p class="text-xs text-amber-800">Menunggu</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-3">
                        <p class="text-2xl font-bold text-emerald-700">{{ $cancel['approved'] }}</p>
                        <p class="text-xs text-emerald-800">Disetujui</p>
                    </div>
                    <div class="rounded-lg bg-red-50 p-3">
                        <p class="text-2xl font-bold text-red-700">{{ $cancel['rejected'] }}</p>
                        <p class="text-xs text-red-800">Ditolak</p>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex justify-between text-sm">
                        <span>Tingkat pembatalan</span>
                        <span class="font-semibold">{{ $cancel['rate'] }}% ({{ $cancel['cancelled'] }} dari {{ $cancel['total'] }} booking)</span>
                    </div>
                    <div class="mt-1 h-2.5 rounded-full bg-gray-100">
                        <div class="h-2.5 rounded-full bg-red-500" style="width: {{ $cancel['rate'] }}%"></div>
                    </div>
                </div>
            </section>

            {{-- Grafik 4: pemakaian alat --}}
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Alat paling sering disewa</h2>
                <p class="text-xs text-gray-500">Total unit yang pernah Anda sewa (6 teratas).</p>

                @php $maxQty = max(1, $topItems->max('qty')); @endphp
                <ul class="mt-4 space-y-3">
                    @foreach ($topItems as $item)
                        <li>
                            <div class="flex justify-between gap-2 text-sm">
                                <span class="truncate">{{ $item['name'] }}</span>
                                <span class="shrink-0 font-semibold">{{ $item['qty'] }} unit <span class="font-normal text-gray-400">· {{ $item['times'] }}× sewa</span></span>
                            </div>
                            <div class="mt-1 h-2.5 rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ round($item['qty'] / $maxQty * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

        </div>
    @endif

</x-layouts.dashboard>
