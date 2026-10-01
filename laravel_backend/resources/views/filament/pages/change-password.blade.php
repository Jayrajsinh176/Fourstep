<x-filament-panels::page>
    <div style="max-width: 560px;">
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="
                        width: 42px; height: 42px;
                        background: #fdf0ed;
                        border-radius: 10px;
                        display: flex; align-items: center; justify-content: center;
                        flex-shrink: 0;
                    ">
                        <x-heroicon-o-lock-closed style="width: 20px; height: 20px; color: #AE4329;" />
                    </div>
                    <div>
                        <p style="font-size: 16px; font-weight: 600; color: var(--gray-900); margin: 0 0 3px;">Change Password</p>
                        <p style="font-size: 12px; color: var(--gray-500); font-weight: 400; margin: 0;">Update your account password</p>
                    </div>
                </div>
            </x-slot>

            <form wire:submit="changePassword">
                {{ $this->form }}

                <div style="
                    margin-top: 1.5rem;
                    padding-top: 1rem;
                    border-top: 1px solid var(--gray-200);
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                ">
                    <button type="button" wire:click="$refresh" style="
                        padding: 9px 18px; font-size: 13px; font-weight: 500;
                        border-radius: 8px; border: 1px solid var(--gray-300);
                        background: white; color: var(--gray-700); cursor: pointer;
                    ">Cancel</button>

                    <button type="submit" style="
                        padding: 9px 20px; font-size: 13px; font-weight: 500;
                        border-radius: 8px; border: none;
                        background: #AE4329; color: white; cursor: pointer;
                        display: flex; align-items: center; gap: 7px;
                    ">
                        <x-heroicon-o-check style="width: 15px; height: 15px;" />
                        Update Password
                    </button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>