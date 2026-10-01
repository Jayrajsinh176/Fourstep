<?php

namespace App\Filament\Resources\ShoppeeHelpdesk\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Services\ActivityLogService;

class ShoppeeHelpdeskTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

    TextColumn::make('member.fullname')
    ->label('Member')
    ->formatStateUsing(function ($state, $record) {

        return $record->member->fullname . ' (' . $record->member->member_id . ')';

    }),
                TextColumn::make('subject')
                    ->limit(30),

                TextColumn::make('message')
                    ->limit(40),

                TextColumn::make('admin_reply')
                    ->limit(40),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'open',
                        'danger' => 'closed',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime(),

            ])
        ->recordActions([
    EditAction::make()
        ->after(function ($record) {

            if (! empty($record->admin_reply)) {

                ActivityLogService::log(
                    'Shoppee Panel',
                    'Shoppee Helpdesk Replied',
                    'Replied to Helpdesk Ticket #' .
                    $record->id .
                    ' for ' .
              ($record->member?->fullname ?? 'Unknown') .
                    ' (' . ($record->member?->member_id ?? '-') . ')' .
                    ' | Status: ' . ucfirst($record->status)
                );
            }

            if ($record->status === 'closed') {

                ActivityLogService::log(
                    'Shoppee Panel',
                    'Shoppee Helpdesk Closed',
                    'Closed Helpdesk Ticket #' .
                    $record->id .
                    ' for ' .
             ($record->member?->fullname ?? 'Unknown').
                    ' (' . ($record->member?->member_id ?? '-') . ')'
                );
            }
        }),
])

->bulkActions([

    DeleteBulkAction::make(),

]);
    }
}