<x-layouts.dashboard title="Pembatalan" heading="Manajemen Pembatalan">

    {{-- Tab status --}}
    @php
        $tabs = [
            'pending'  => ['Menunggu', $counts['pending'] ?? 0],
            'approved' => ['Disetujui', $counts['approved'] ?? 0],
            'rejected' => ['Ditolak', $counts['rejected'] ?? 0],
            'all'      => ['Semua', $counts->sum()],
        ];
        $payLabels = ['unpaid' => 'Belum dibayar', 'paid' => 'Lunas (simulasi)', 'refunded' => 'Dikembalikan'];
    @endphp

    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $key => [$label, $count])
            <a href="{{ route('staff.cancellations.index', ['status' => $key]) }}"
               @class([
                   'rounded-lg px-3 py-2 text-sm font-medium transition',
                   'bg-emerald-600 text-white' => $status === $key,
                   'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50' => $status !== $key,
               ])>
                {{ $label }}
                <span @class([
                    'ml-1 rounded-full px-2 py-0.5 text-xs',
                    'bg-white/20' => $status === $key,
                    'bg-gray-100' => $status !== $key,
                ])>{{ $count }}</span>
            </a>
        @endforeach
    </div>

    @if ($cancellations->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
            Tidak ada pengajuan pembatalan pada kategori ini.
        </div>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($cancellations as $c)
                @php
                    $b = $c->booking;
                    $hasError = old('_target') == $c->id;
                @endphp

                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    {{-- Kepala --}}
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold tracking-wide">{{ $b->booking_code }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $b->customer_name }} &middot; {{ $b->customer_phone }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-cancellation-status :status="$c->status" />
                            <x-booking-status :booking="$b" />
                        </div>
                    </div>

                    {{-- Detail booking --}}
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-gray-500">Periode sewa</dt>
                            <dd class="font-medium">{{ $b->start_date->translatedFormat('d M Y') }} &ndash; {{ $b->end_date->translatedFormat('d M Y') }} ({{ $b->total_days }} hari)</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Pembayaran</dt>
                            <dd class="font-medium">{{ $payLabels[$b->payment_status] ?? $b->payment_status }}
                                &middot; deposit {{ \App\Support\Format::rupiah($b->deposit_amount) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Alat</dt>
                            <dd class="font-medium">{{ $b->items->map(fn ($l) => $l->quantity . '× ' . $l->item->name)->join(', ') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Alasan pembatalan <span class="text-xs">(diajukan {{ $c->created_at->translatedFormat('d M Y H:i') }})</span></dt>
                            <dd class="mt-1 rounded-lg bg-gray-50 p-3">{{ $c->reason }}</dd>
                        </div>
                    </dl>

                    @if ($c->isPending())

                        <p class="mt-4 rounded-lg bg-sky-50 p-3 text-xs text-sky-900">
                            Jika <strong>disetujui</strong>: booking dibatalkan, stok alat dilepas
                            @if ($b->payment_status === 'paid')
                                dan deposit {{ \App\Support\Format::rupiah($b->deposit_amount) }} dicatat dikembalikan.
                            @else
                                (booking belum dibayar, jadi tidak ada deposit yang dikembalikan).
                            @endif
                        </p>

                        <div class="mt-4 grid gap-3 lg:grid-cols-2">

                            {{-- Setujui --}}
                            <details class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-3">
                                <summary class="cursor-pointer text-sm font-semibold text-emerald-800">Setujui pembatalan</summary>
                                <form method="POST" action="{{ route('staff.cancellations.approve', $c) }}" class="mt-3 space-y-3"
                                      onsubmit="return confirm('Setujui pembatalan {{ $b->booking_code }}? Tindakan ini membatalkan booking dan melepas stok.')">
                                    @csrf
                                    <input type="hidden" name="_target" value="{{ $c->id }}">
                                    <label class="block text-xs text-gray-600" for="approve-note-{{ $c->id }}">Catatan untuk pelanggan (opsional)</label>
                                    <textarea id="approve-note-{{ $c->id }}" name="staff_note" rows="2" maxlength="300"
                                              class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                                    <x-button type="submit">Setujui</x-button>
                                </form>
                            </details>

                            {{-- Tolak --}}
                            <details class="rounded-lg border border-red-200 bg-red-50/50 p-3" @if ($hasError) open @endif>
                                <summary class="cursor-pointer text-sm font-semibold text-red-800">Tolak pembatalan</summary>
                                <form method="POST" action="{{ route('staff.cancellations.reject', $c) }}" class="mt-3 space-y-3"
                                      onsubmit="return confirm('Tolak pembatalan {{ $b->booking_code }}?')">
                                    @csrf
                                    <input type="hidden" name="_target" value="{{ $c->id }}">
                                    <label class="block text-xs text-gray-600" for="reject-note-{{ $c->id }}">Alasan penolakan (wajib)</label>
                                    <textarea id="reject-note-{{ $c->id }}" name="staff_note" rows="2" maxlength="300" required
                                              class="block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1 {{ $hasError && $errors->has('staff_note') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-red-500 focus:ring-red-500' }}">{{ $hasError ? old('staff_note') : '' }}</textarea>
                                    @if ($hasError)
                                        @error('staff_note') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                    @endif
                                    <x-button type="submit" variant="danger">Tolak</x-button>
                                </form>
                            </details>
                        </div>

                    @else

                        {{-- Hasil keputusan --}}
                        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
                            <p>
                                Diputuskan oleh <span class="font-medium">{{ $c->reviewer?->name ?? 'staf' }}</span>
                                pada {{ $c->reviewed_at?->translatedFormat('d M Y H:i') }}.
                            </p>
                            @if ($c->staff_note)
                                <p class="mt-1"><span class="text-gray-500">Catatan:</span> {{ $c->staff_note }}</p>
                            @endif
                            @if ($c->deposit_refund_status === 'refunded')
                                <p class="mt-1 font-medium text-emerald-700">Deposit tercatat dikembalikan: {{ \App\Support\Format::rupiah($c->deposit_refund_amount) }}</p>
                            @endif
                        </div>

                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $cancellations->links() }}</div>
    @endif

</x-layouts.dashboard>
