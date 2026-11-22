<x-layouts.app title="Pesan Alat">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <h1 class="text-2xl font-bold">Form Pemesanan</h1>
        <p class="mt-1 text-gray-600">Pilih tanggal, tentukan alat atau paket, lalu isi data Anda.</p>

        {{-- Error pilihan alat/paket (stok kurang, dll.) --}}
        @php
            $selectionErrors = collect($errors->getMessages())
                ->filter(fn ($messages, $key) => str_starts_with($key, 'items') || str_starts_with($key, 'packages'))
                ->flatten();
        @endphp
        @if ($selectionErrors->isNotEmpty())
            <div role="alert" class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Pesanan belum dapat diproses:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($selectionErrors as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="booking-form" method="POST" action="{{ route('booking.store') }}" class="mt-6 grid gap-8 lg:grid-cols-3">
            @csrf

            <div class="space-y-6 lg:col-span-2">

                {{-- 1. Tanggal --}}
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold">1. Tanggal sewa</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-input label="Tanggal mulai" name="start_date" type="date" :value="$defaultStart" :min="$today" required />
                        <x-input label="Tanggal selesai" name="end_date" type="date" :value="$defaultEnd" :min="$today" required />
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Maksimal {{ \App\Services\BookingService::MAX_DAYS }} hari. Hari mulai dan hari selesai sama-sama dihitung.</p>
                </section>

                {{-- 2. Paket --}}
                @if ($packages->isNotEmpty())
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold">2. Paket sewa <span class="text-sm font-normal text-gray-500">(opsional)</span></h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($packages as $package)
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 p-3">
                                    <div class="min-w-0">
                                        <p class="font-medium">{{ $package->name }}
                                            @if ($package->discount_percent > 0)
                                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Hemat {{ $package->discount_percent }}%</span>
                                            @endif
                                        </p>
                                        <p class="truncate text-xs text-gray-500">{{ $package->items->pluck('name')->join(', ') }}</p>
                                        <p class="text-sm font-semibold text-emerald-700">{{ \App\Support\Format::rupiah($package->daily_price) }} <span class="text-xs font-normal text-gray-500">/ hari</span></p>
                                    </div>
                                    <input type="number" name="packages[{{ $package->id }}]" min="0" max="10"
                                           value="{{ old('packages.' . $package->id, $selectedPackages[$package->id] ?? 0) }}"
                                           data-price="{{ $package->daily_price }}" aria-label="Jumlah {{ $package->name }}"
                                           class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- 3. Alat satuan --}}
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold">3. Alat satuan <span class="text-sm font-normal text-gray-500">(opsional)</span></h2>

                    @foreach ($itemsByCategory as $categoryName => $categoryItems)
                        <h3 class="mt-5 text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $categoryName }}</h3>
                        <div class="divide-y divide-gray-100">
                            @foreach ($categoryItems as $item)
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $item->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ \App\Support\Format::rupiah($item->daily_rate) }} / hari &middot;
                                            <span id="stock-{{ $item->id }}" class="text-gray-500">Tersedia: -</span>
                                        </p>
                                    </div>
                                    <input type="number" name="items[{{ $item->id }}]" min="0" max="99"
                                           value="{{ old('items.' . $item->id, $selectedItems[$item->id] ?? 0) }}"
                                           data-price="{{ $item->daily_rate }}" data-item-id="{{ $item->id }}"
                                           aria-label="Jumlah {{ $item->name }}"
                                           class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </section>

                {{-- 4. Data penyewa --}}
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold">4. Data penyewa</h2>
                    <div class="mt-4 space-y-4">
                        <x-input label="Nama lengkap" name="customer_name" :value="auth()->user()?->name" required autocomplete="name" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-input label="Email" name="customer_email" type="email" :value="auth()->user()?->email" required autocomplete="email" />
                            <x-input label="Nomor telepon" name="customer_phone" type="tel" :value="auth()->user()?->phone" placeholder="081234567890" required autocomplete="tel" />
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan <span class="font-normal text-gray-500">(opsional)</span></label>
                            <textarea id="notes" name="notes" rows="3" maxlength="500"
                                      class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @guest
                            <p class="text-xs text-gray-500">Memesan tanpa akun? Simpan kode booking Anda, lalu lacak statusnya lewat menu Lacak Booking.</p>
                        @endguest
                    </div>
                </section>
            </div>

            {{-- Ringkasan --}}
            <aside class="lg:col-span-1">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                    <h2 class="font-semibold">Ringkasan</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-600">Durasi</dt><dd id="sum-days" class="font-medium">-</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Estimasi sewa</dt><dd id="sum-subtotal" class="font-medium">Rp 0</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Deposit (20%)</dt><dd id="sum-deposit" class="font-medium">Rp 0</dd></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold"><dt>Estimasi total</dt><dd id="sum-total" class="text-emerald-700">Rp 0</dd></div>
                    </dl>
                    <ul class="mt-4 list-disc space-y-1 pl-5 text-xs text-gray-500">
                        <li>Total akhir dihitung ulang oleh sistem saat booking dibuat.</li>
                        <li>Deposit dikembalikan setelah alat kembali dalam kondisi baik.</li>
                        <li>Alat rusak/hilang diganti sesuai nilai penggantian.</li>
                    </ul>
                    <x-button type="submit" class="mt-5 w-full">Buat Booking</x-button>
                </div>
            </aside>
        </form>
    </div>

    <script>
        (function () {
            const form = document.getElementById('booking-form');
            if (!form) return;

            const startEl = document.getElementById('start_date');
            const endEl = document.getElementById('end_date');
            const availabilityUrl = @json(route('booking.availability'));
            const DEPOSIT_RATE = {{ \App\Services\BookingService::DEPOSIT_RATE }};
            const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));

            // Jumlah hari (hari mulai & selesai sama-sama dihitung)
            function days() {
                if (!startEl.value || !endEl.value) return 0;
                const diff = Math.round((new Date(endEl.value) - new Date(startEl.value)) / 86400000) + 1;
                return diff > 0 ? diff : 0;
            }

            function recalc() {
                const d = days();
                let perDay = 0;
                form.querySelectorAll('[data-price]').forEach((el) => {
                    perDay += (parseInt(el.value, 10) || 0) * parseFloat(el.dataset.price);
                });
                const subtotal = perDay * d;
                const deposit = Math.round(subtotal * DEPOSIT_RATE / 1000) * 1000;

                document.getElementById('sum-days').textContent = d ? d + ' hari' : '-';
                document.getElementById('sum-subtotal').textContent = rupiah(subtotal);
                document.getElementById('sum-deposit').textContent = rupiah(deposit);
                document.getElementById('sum-total').textContent = rupiah(subtotal + deposit);
            }

            // Tampilkan stok tersedia per alat untuk tanggal terpilih
            async function loadAvailability() {
                if (days() < 1) return;
                try {
                    const response = await fetch(availabilityUrl + '?start=' + startEl.value + '&end=' + endEl.value,
                        { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();

                    form.querySelectorAll('[data-item-id]').forEach((input) => {
                        const id = input.dataset.itemId;
                        const available = data[id] ?? 0;
                        const label = document.getElementById('stock-' + id);
                        label.textContent = 'Tersedia: ' + available;
                        label.className = available === 0 ? 'font-medium text-red-600' : 'text-emerald-700';
                        input.max = available;
                    });
                } catch (e) { /* abaikan; validasi server tetap berjalan */ }
            }

            function onDateChange() {
                endEl.min = startEl.value || endEl.min;
                if (endEl.value && startEl.value && endEl.value < startEl.value) {
                    endEl.value = startEl.value;
                }
                recalc();
                loadAvailability();
            }

            startEl.addEventListener('change', onDateChange);
            endEl.addEventListener('change', onDateChange);
            form.addEventListener('input', recalc);

            onDateChange();
        })();
    </script>
</x-layouts.app>
