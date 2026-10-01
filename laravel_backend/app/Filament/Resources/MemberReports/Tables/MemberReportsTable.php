<?php

namespace App\Filament\Resources\MemberReports\Tables;

use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use App\Models\Member;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;

class MemberReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
   ->recordAction(null)
            ->columns([

             Tables\Columns\TextColumn::make('serial_number')
    ->label('ID')
    ->state(function ($record) {
        return Member::where(function ($query) use ($record) {
            $query->where('created_at', '<', $record->created_at)
                  ->orWhere(function ($q) use ($record) {
                      $q->where('created_at', $record->created_at)
                        ->where('id', '<=', $record->id);
                  });
        })->count();
    })
                ->toggleable(),
                
                Tables\Columns\TextColumn::make('login_action')
    ->label('Action')
    ->default('View Member')
    ->color('primary')
    ->weight('bold')
    ->url(fn ($record) => route('admin.login.member', $record->user_id ?: $record->id))
    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('user_id')
                    ->searchable()
                      ->toggleable()
                    ->placeholder('NA'),
                Tables\Columns\TextColumn::make('sponsor.user_id')
                    ->label('Sponsor ID')
                    ->toggleable()
                    ->description(fn ($record) => $record->sponsor?->fullname)
                    ->placeholder('NA'),
                Tables\Columns\TextColumn::make('fullname')
                ->searchable()
                ->toggleable()
                ->placeholder('NA'),

                Tables\Columns\TextColumn::make('parent.user_id')
                ->toggleable()
                ->description(fn ($record) => $record->parent?->fullname)
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('position')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('package_step')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('self_bv')
                ->label('BV')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('builtup_left_bv')
                ->label('Left BV')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('builtup_right_bv')
                ->label('Right BV')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('carry_forward_left')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('carry_forward_right')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('is_active')->label('Active')
                ->toggleable()
                ->placeholder('NA'),

                Tables\Columns\TextColumn::make('dob')->date('d-m-Y')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('gender')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('pan_number')
                ->label('PAN')
                ->toggleable()
                ->placeholder('NA'),

                Tables\Columns\TextColumn::make('mobile_no')
                ->searchable()
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('email')
                ->toggleable()
                ->searchable()
                ->placeholder('NA'),

                Tables\Columns\TextColumn::make('address')
                    ->limit(20)
                    ->toggleable()
                    ->tooltip(fn($record) => $record->address)
                    ->placeholder('NA'),

                Tables\Columns\TextColumn::make('pin_code')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('state')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('city')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('district')
                ->toggleable()
                ->placeholder('NA'),

                // Shipping
                Tables\Columns\TextColumn::make('shipping_address')
                ->limit(20)
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('shipping_state')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('shipping_city')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('shipping_district')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('shipping_pin_code')
                ->toggleable()
                ->placeholder('NA'),

                // Nominee
                Tables\Columns\TextColumn::make('nominee_name')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_relation')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_mobile_no')->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_address')
                ->limit(20)
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_state')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_city')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_district')
                ->toggleable()
                ->placeholder('NA'),
                Tables\Columns\TextColumn::make('nominee_pin_code')
                ->toggleable()
                ->placeholder('NA'),

                Tables\Columns\TextColumn::make('status')
                ->toggleable()
                ->placeholder('NA'),

                Tables\Columns\TextColumn::make('activation_date')
                    ->date('d-m-Y')
                    ->toggleable()
                    ->placeholder('NA'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joining Date')
                    ->toggleable()
                    ->date('d-m-Y'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d-m-Y')
                    ->toggleable()
                    ->placeholder('NA'),
            ])

            ->filters([ ])

           ->actions([

       ViewAction::make(),
 EditAction::make(),

])


            ->bulkActions([]);
    }
}