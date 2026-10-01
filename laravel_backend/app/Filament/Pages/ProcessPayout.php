<?php

namespace App\Filament\Pages;

use App\Models\WeeklyClosing;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class ProcessPayout extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \UnitEnum | null $navigationGroup = 'Accounts';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Process Payout';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.process-payout';

    public ?string $weekStart = null;

    public ?string $weekEnd = null;

    public ?string $lastProcessed = null;

    public ?array $data = [];

    public function mount(): void
    {
        $closing = WeeklyClosing::latest('week_end')->first();

        if ($closing) {
            $this->weekStart = $closing->week_start;
            $this->weekEnd = $closing->week_end;

            $this->lastProcessed =
                \Illuminate\Support\Carbon::parse($closing->week_start)
                    ->format('d M Y')
                . ' - ' .
                \Illuminate\Support\Carbon::parse($closing->week_end)
                    ->format('d M Y');
        }

        $this->form->fill([
            'weekStart' => $this->weekStart,
            'weekEnd' => $this->weekEnd,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Forms\Components\DatePicker::make('weekStart')
                    ->label('Week Start')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required(),

                Forms\Components\DatePicker::make('weekEnd')
                    ->label('Week End')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required(),

            ])
            ->statePath('data');
    }

    public function viewPayout()
    {
        $weekStart = \Carbon\Carbon::parse(
            $this->data['weekStart']
        )->toDateString();

        $weekEnd = \Carbon\Carbon::parse(
            $this->data['weekEnd']
        )->toDateString();

        return redirect()->route(
            'filament.admin.pages.payout-preview',
            [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
            ]
        );
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermission('process_payout');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}