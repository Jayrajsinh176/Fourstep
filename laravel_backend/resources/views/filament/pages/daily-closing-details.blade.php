<x-filament-panels::page>

    <h2 class="text-2xl font-bold mb-6">
        Weekly Closing Details
        ({{ \Carbon\Carbon::parse($this->weekStart)->format('d M Y') }}
        -
        {{ \Carbon\Carbon::parse($this->weekEnd)->format('d M Y') }})
    </h2>

    {{ $this->table }}

</x-filament-panels::page>