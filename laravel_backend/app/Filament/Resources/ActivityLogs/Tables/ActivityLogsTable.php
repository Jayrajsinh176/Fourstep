<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Admin User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('module')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color('success')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([

    // Admin User
    Tables\Filters\SelectFilter::make('user_id')
        ->label('Admin User')
        ->relationship('user', 'name')
        ->searchable()
        ->preload(),

    // Module
    Tables\Filters\SelectFilter::make('module')
        ->options([
            'User Access' => 'User Access',
            'Member Panel' => 'Member Panel',
            'Ecommerce Panel' => 'Ecommerce Panel',
            'Shoppee Panel' => 'Shoppee Panel',
        ]),

    // Action
    Tables\Filters\SelectFilter::make('action')
        ->options(function () {

            return \App\Models\ActivityLog::query()
                ->select('action')
                ->distinct()
                ->pluck('action', 'action')
                ->toArray();

        })
        ->searchable(),

    // Date Filter
    Tables\Filters\Filter::make('created_at')
        ->form([

            DatePicker::make('from')
                ->label('From'),

            DatePicker::make('until')
                ->label('To'),

        ])
        ->query(function (Builder $query, array $data): Builder {

            return $query
                ->when(
                    $data['from'],
                    fn (Builder $query, $date) =>
                        $query->whereDate('created_at', '>=', $date)
                )
                ->when(
                    $data['until'],
                    fn (Builder $query, $date) =>
                        $query->whereDate('created_at', '<=', $date)
                );

        }),

])
            ->actions([])

            ->bulkActions([]);
    }
}