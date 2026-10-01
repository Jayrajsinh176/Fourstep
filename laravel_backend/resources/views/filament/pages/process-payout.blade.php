<x-filament-panels::page>

<div class="max-w-3xl mx-auto space-y-6">

    <x-filament::section>

        <x-slot name="heading">

            <div class="flex items-center gap-3">

                <div
                    class="flex items-center justify-center rounded-lg bg-[#AE4329]/10 text-[#AE4329]"
                    style="width:40px;height:40px;"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        style="width:20px;height:20px;"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"
                        />
                    </svg>
                </div>

                <span class="text-xl font-bold">
                    Process Weekly Payout
                </span>

            </div>

        </x-slot>

        <x-slot name="description">
            Select the weekly closing period and process the payout
            for all eligible members.
        </x-slot>


        {{-- Warning banner --}}

        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-900 p-4">

            <div class="flex gap-3">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="shrink-0 text-amber-500"
                    style="width:20px;height:20px;margin-top:2px;"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                    />
                </svg>

                <div>

                    <h3 class="font-semibold text-amber-800 dark:text-amber-300 text-sm">
                        Important
                    </h3>

                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-400 leading-relaxed">

                        Select the week and click
                        <strong>View Payout</strong>.

                        Purchase bonus income for all eligible members
                        will be shown for the selected week.

                        <strong>
                            This action cannot be undone.
                        </strong>

                    </p>

                </div>

            </div>

        </div>


        <div class="mb-6">
            {{ $this->form }}
        </div>


        <hr style="margin: 16px 0; border-top: 1px solid #e5e7eb;">


        <div class="flex justify-end">

            <x-filament::button
                wire:click="viewPayout"
                icon="heroicon-o-eye"
                color="primary"
            >
                View Payout
            </x-filament::button>

        </div>

    </x-filament::section>

</div>

</x-filament-panels::page>