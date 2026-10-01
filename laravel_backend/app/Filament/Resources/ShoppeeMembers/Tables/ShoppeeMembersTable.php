<?php

namespace App\Filament\Resources\ShoppeeMembers\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;

class ShoppeeMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
      
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('login_action')
    ->label('Action')
    ->default('View Member')
       ->color('primary')
    ->weight('bold')
    ->url(function ($record) {

        $user = base64_encode(json_encode([
            'id' => $record->id,
            'member_id' => $record->member_id,
            'fullname' => $record->fullname,
            'email' => $record->email,
            'mobile_no' => $record->mobile_no,
            'branch_name' => $record->branch_name,
            'branch_type' => $record->branch_type,
        ]));

        return rtrim(config('app.frontend_url'), '/') . '/shoppee/auto-login?user=' . urlencode($user);

    })
    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fullname')
                    ->searchable(),
                    
                        Tables\Columns\TextColumn::make('dob')
                    ->date(),
                    
                    Tables\Columns\TextColumn::make('email'),

                Tables\Columns\TextColumn::make('mobile_no'),
                
                    Tables\Columns\TextColumn::make('user_pan')
    ->label('User PAN')
    ->searchable(),

Tables\Columns\TextColumn::make('aadhaar_no')
    ->label('Aadhaar No')
    ->searchable(),

Tables\Columns\TextColumn::make('user_address')
    ->label('User Address')
    ->toggleable(),
                
   
                // 🔥 Branch with badge color
                Tables\Columns\TextColumn::make('branch_name')
                    ->badge()
                    ->color(fn($state) => str_contains($state, 'Bharuch') ? 'success' : 'warning'),
                    Tables\Columns\TextColumn::make('branch_type')
    ->badge()
    ->color('info'),

                Tables\Columns\TextColumn::make('branch_pan'),

         

                Tables\Columns\TextColumn::make('gst_no')->toggleable(),
                Tables\Columns\TextColumn::make('address')->toggleable(),




                Tables\Columns\TextColumn::make('pin_code'),

                Tables\Columns\TextColumn::make('state'),

                Tables\Columns\TextColumn::make('city'),

                Tables\Columns\TextColumn::make('district'),

         Tables\Columns\TextColumn::make('created_at')
    ->label('Created At')
    ->dateTime('d-m-Y h:i:s A')
    ->sortable(),

Tables\Columns\TextColumn::make('updated_at')
    ->label('Updated At')
    ->dateTime('d-m-Y h:i:s A')
    ->sortable(),
            ])

           ->filters([

    Tables\Filters\SelectFilter::make('branch_type')
        ->label('Branch Type')
        ->options([
            'Mega Branch' => 'Mega Branch',
            'Mini Branch' => 'Mini Branch',
            'Area Branch' => 'Area Branch',
        ]),

])
  ->recordUrl(null)
            ->recordActions([
    ViewAction::make(),
    EditAction::make(),
])

            ->toolbarActions([
                // Tables\Actions\CreateAction::make(),
            ]);

    }
}