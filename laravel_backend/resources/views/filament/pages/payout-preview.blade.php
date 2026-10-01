<x-filament-panels::page>

    <x-filament::section>

        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">

                <div>
                    <h2 class="text-xl font-bold">
                        Cheque List
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Review all members before processing payout.
                    </p>
                </div>

                <div class="text-right text-sm space-y-1">

                    <div>
                        <strong>Week :</strong>
                        {{ \Carbon\Carbon::parse($weekStart)->format('d-m-Y') }}
                        -
                        {{ \Carbon\Carbon::parse($weekEnd)->format('d-m-Y') }}
                    </div>

                    <div>
                        <strong>Total Members :</strong>
                        {{ number_format($totalMembers) }}
                    </div>

                    <div>
                        <strong>Total Amount :</strong>
                        ₹{{ number_format($totalAmount, 2) }}
                    </div>

                </div>

            </div>
        </x-slot>

        {{ $this->table }}

    </x-filament::section>

</x-filament-panels::page>