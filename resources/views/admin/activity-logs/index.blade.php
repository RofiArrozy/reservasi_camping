<x-layouts.dashboard title="Log Aktivitas" heading="Log Aktivitas">

    @php
        // Warna lencana berdasarkan awalan aksi (mis. "booking.picked_up" -> "booking")
        $colors = [
            'booking' => 'bg-sky-100 text-sky-800', 'cancellation' => 'bg-amber-100 text-amber-800',
            'incident' => 'bg-red-100 text-red-800', 'item' => 'bg-emerald-100 text-emerald-800',
            'category' => 'bg-emerald-100 text-emerald-800', 'package' => 'bg-teal-100 text-teal-800',
            'article' => 'bg-indigo-100 text-indigo-800', 'user' => 'bg-purple-100 text-purple-800',
        ];
        $hasFilter = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty();
    @endphp

    <p class="text-sm text-gray-600">Jejak seluruh tindakan staf dan admin. Data ini hanya dapat dibaca, tidak dapat diubah.</p>

    <form method="GET" action="{{ route('admin.activity-logs.index') }}"
          class="mt-4 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-3">
        <select name="user" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua pelaku</option>
            @foreach ($actors as $actor)
                <option value="{{ $actor->id }}" @selected(($filters['user'] ?? null) == $actor->id)>{{ $actor->name }} ({{ $actor->role }})</option>
            @endforeach
        </select>
        <select name="action" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua aksi</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(($filters['action'] ?? null) === $action)>{{ $action }}</option>
            @endforeach
        </select>
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari deskripsi..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <label class="flex items-center gap-2 text-sm text-gray-600">Dari
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></label>
        <label class="flex items-center gap-2 text-sm text-gray-600">Sampai
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></label>
        <div class="flex gap-2">
            <x-button type="submit">Terapkan</x-button>
            @if ($hasFilter)<x-button :href="route('admin.activity-logs.index')" variant="ghost">Reset</x-button>@endif
        </div>
    </form>

    @error('to') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

    @if ($logs->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">Tidak ada aktivitas yang cocok.</div>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Pelaku</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($logs as $log)
                        @php $prefix = \Illuminate\Support\Str::before($log->action, '.'); @endphp
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                {{ $log->user?->name ?? 'Sistem' }}
                                @if ($log->user)<span class="block text-xs text-gray-500">{{ $log->user->role }}</span>@endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colors[$prefix] ?? 'bg-gray-100 text-gray-700' }}">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $log->description }}
                                @if ($log->ip_address)<span class="block text-xs text-gray-400">IP {{ $log->ip_address }}</span>@endif
                                @if (! empty($log->properties))
                                    <details class="mt-1">
                                        <summary class="cursor-pointer text-xs text-emerald-700">Detail</summary>
                                        <pre class="mt-1 overflow-x-auto rounded-lg bg-gray-50 p-2 text-xs">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $logs->links() }}</div>
    @endif

</x-layouts.dashboard>
