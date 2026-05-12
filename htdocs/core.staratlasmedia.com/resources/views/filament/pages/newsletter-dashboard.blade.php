<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($this->getStats() as $label => $value)
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <div class="text-sm text-gray-500">{{ str_replace('_', ' ', $label) }}</div>
                <div class="text-2xl font-semibold">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <h2 class="text-lg font-semibold">Phase 9 safeguards</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Mass sending, digest auto-send, automatic AI calls, and production polling remain disabled unless explicitly configured in dedicated settings.
            Open rate means image-pixel opens, not guaranteed reading.
        </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($this->getHealth() as $label => $value)
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <div class="text-sm text-gray-500">{{ str_replace('_', ' ', $label) }}</div>
                <div class="mt-2 text-sm">
                    @if ($value instanceof \Illuminate\Support\Collection)
                        @forelse ($value as $event)
                            <div>{{ $event->sns_type ?? 'event' }}: {{ $event->status }}</div>
                        @empty
                            <div>none</div>
                        @endforelse
                    @else
                        <span class="text-xl font-semibold">{{ $value }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <h2 class="text-lg font-semibold">Top clicked links skeleton</h2>
        <div class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
            @forelse ($this->getTopClickedLinks() as $link)
                <div>{{ $link->url ?: $link->url_hash }} - {{ $link->clicks }} clicks</div>
            @empty
                <div>No click events yet.</div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
