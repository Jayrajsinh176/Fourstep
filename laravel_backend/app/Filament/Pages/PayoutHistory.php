<?php

namespace App\Filament\Pages;

use App\Models\PayoutBatch;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;

class PayoutHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Accounts';

    protected static ?string $navigationLabel = 'Payout History';

    protected static ?string $title = 'Payout History';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.payout-history';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PayoutBatch::query()
            )

            ->defaultSort('id', 'desc')

            ->columns([

                TextColumn::make('batch_no')
                    ->label('Batch No')
                    ->searchable()
                    ->sortable(),

               TextColumn::make('payout_date')
    ->label('Payout Week')
    ->date('d-m-Y')
    ->sortable(),

                TextColumn::make('total_members')
                    ->label('Members')
                    ->alignCenter(),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('INR')
                    ->alignEnd(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                    

            ])

->recordActions([

    Action::make('view')
        ->label('View')
        ->icon('heroicon-o-eye')
        ->color('primary')
        ->url(fn (PayoutBatch $record) => url(
            '/admin/payout-history-details/' . $record->id
        )),

])

            ->paginated([10, 25, 50]);
    }
    
protected function getHeaderActions(): array
{
    return [
        Action::make('downloadSheet')
    ->label('Download Payout Sheet')
    ->icon('heroicon-o-arrow-down-tray')
    ->color('success')
    ->url(route('admin.payout-cycle-sheet'))
    ->openUrlInNewTab(),
    ];
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

    return $user->hasPermission('payout_history');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}

}