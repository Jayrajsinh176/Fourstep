<?php

namespace App\Filament\Widgets;

use App\Models\Orderecom;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentOrders extends TableWidget
{
    protected static ?string $heading = 'Recent Orders';

    protected int|string|array $columnSpan = 'full'; // ðŸ”¥ THIS LINE

public static function canView(): bool
{
    $user = Auth::user();

    if (! $user) {
        return false;
    }

    // Owner always sees it
    if ($user->isOwner()) {
        return true;
    }

    return
        $user->hasPermission('ecommerce_orders.view') ||
        $user->hasPermission('member_orders.view');
}

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Orderecom::query()->latest()->limit(5))
            ->columns([

                // ðŸ“¦ Order ID
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable(),

                // ðŸ‘¤ Customer Name
                Tables\Columns\TextColumn::make('member.fullname')
                    ->label('Customer')
                    ->searchable(),

               Tables\Columns\TextColumn::make('total_amount')
    ->label('Amount')
    ->formatStateUsing(fn ($state) => '₹ ' . number_format($state, 2)),

                // ðŸš¦ Status
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'info' => 'dispatched',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ]),

                // ðŸ“… Date
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}