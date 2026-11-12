{{-- Pesan flash: dikirim dari controller via ->with('success', '...') atau ->with('error', '...') --}}
@if (session('success') || session('error') || session('status'))
    <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('success') || session('status'))
            <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') ?? session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif
    </div>
@endif
