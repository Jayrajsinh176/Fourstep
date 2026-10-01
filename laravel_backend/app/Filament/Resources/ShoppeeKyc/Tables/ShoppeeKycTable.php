<?php

namespace App\Filament\Resources\ShoppeeKyc\Tables;

use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use App\Services\ActivityLogService;

class ShoppeeKycTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
       ->recordUrl(null)
        ->recordAction(null)
            ->columns([

    Tables\Columns\TextColumn::make('id')
        ->label('ID')
        ->searchable()
        ->sortable(),

Tables\Columns\TextColumn::make('member')
    ->label('Member Details')
    ->state(fn ($record) =>
        $record->member_id . ' (' . $record->member_name . ')'
    )
       ->searchable([
        'member_id',
        'member_name',
    ]),
    
    Tables\Columns\TextColumn::make('account_beneficiary_name')
        ->label('Account Holder'),

    Tables\Columns\TextColumn::make('account_no')
        ->label('Account No'),

    Tables\Columns\TextColumn::make('bank_name')
        ->label('Bank'),


    ImageColumn::make('bank_passbook_image')
        ->label('Bank Doc')
        ->disk('public')
        ->visibility('public')
        ->height(40)
        ->getStateUsing(fn($record) =>
            $record->bank_passbook_image
                ? asset('storage/' . $record->bank_passbook_image)
                : null
        )
        ->url(fn($record) =>
            $record->bank_passbook_image
                ? asset('storage/' . $record->bank_passbook_image)
                : null
        )
        ->openUrlInNewTab(),

    Tables\Columns\TextColumn::make('aadhaar_number')
        ->label('Aadhaar'),

    ImageColumn::make('aadhaar_image')
        ->label('Aadhaar Img')
        ->disk('public')
        ->visibility('public')
        ->height(40)
        ->getStateUsing(fn($record) =>
            $record->aadhaar_image
                ? asset('storage/' . $record->aadhaar_image)
                : null
        )
        ->url(fn($record) =>
            $record->aadhaar_image
                ? asset('storage/' . $record->aadhaar_image)
                : null
        )
        ->openUrlInNewTab(),

    Tables\Columns\TextColumn::make('pan_number')
        ->label('PAN'),

    ImageColumn::make('pan_image')
        ->label('PAN Img')
        ->disk('public')
        ->visibility('public')
        ->height(40)
        ->getStateUsing(fn($record) =>
            $record->pan_image
                ? asset('storage/' . $record->pan_image)
                : null
        )
        ->url(fn($record) =>
            $record->pan_image
                ? asset('storage/' . $record->pan_image)
                : null
        )
        ->openUrlInNewTab(),

    Tables\Columns\TextColumn::make('status')
        ->badge()
        ->color(fn($state) => match ($state) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        }),
        
    Tables\Columns\TextColumn::make('member_id')
        ->label('Attempts')
        ->formatStateUsing(fn ($state) =>
            \App\Models\Shoppee_Kyc::where('member_id', $state)->count()
        ),

    Tables\Columns\IconColumn::make('is_latest')
        ->label('Latest')
        ->boolean(),

    Tables\Columns\TextColumn::make('created_at')
        ->label('Submitted At')
        ->dateTime('d M Y H:i'),
])

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])

            ->actions([

                ViewAction::make()
    ->modalHeading('Shoppee KYC Full Details')
    ->modalWidth('7xl')

    ->form([

        Section::make('Member Details')
            ->columns(2)
            ->schema([

                Forms\Components\TextInput::make('member_id')
                    ->disabled(),

                Forms\Components\TextInput::make('member_name')
                    ->disabled(),

                Forms\Components\TextInput::make('status')
                    ->disabled(),
            ]),

        Section::make('Bank Details')
            ->columns(2)
            ->schema([

                Forms\Components\TextInput::make('account_beneficiary_name')
                    ->label('Account Holder')
                    ->disabled(),

                Forms\Components\TextInput::make('account_no')
                    ->label('Account Number')
                    ->disabled(),

                Forms\Components\TextInput::make('ifs_code')
                    ->label('IFSC Code')
                    ->disabled(),

                Forms\Components\TextInput::make('bank_name')
                    ->label('Bank Name')
                    ->disabled(),

                Forms\Components\TextInput::make('branch_name')
                    ->label('Branch Name')
                    ->disabled(),

                FileUpload::make('bank_passbook_image')
                    ->label('Passbook Image')
                    ->disk('public')
                    ->image()
                    ->disabled(),
            ]),

        Section::make('Aadhaar Details')
            ->columns(2)
            ->schema([

                Forms\Components\TextInput::make('aadhaar_number')
                    ->disabled(),

                FileUpload::make('aadhaar_image')
                    ->label('Aadhaar Image')
                    ->disk('public')
                    ->image()
                    ->disabled(),
            ]),

        Section::make('PAN Details')
            ->columns(2)
            ->schema([

                Forms\Components\TextInput::make('pan_number')
                    ->disabled(),

                FileUpload::make('pan_image')
                    ->label('PAN Image')
                    ->disk('public')
                    ->image()
                    ->disabled(),
            ]),
    ]),

                Action::make('approve')
    ->label('Approve')
    ->color('success')
    ->icon('heroicon-o-check')
    ->visible(fn($record) =>
        $record->is_latest &&
        $record->status !== 'approved'
    )
->action(function ($record) {

    $record->update([
        'status' => 'approved',
    ]);

    ActivityLogService::log(
        'Shoppee Panel',
        'Shoppee KYC Approved',
        'Approved Shoppee KYC for member ' .
        $record->member_id .
        ' (' . $record->member_name . ')'
    );
}),
               Action::make('reject')
        ->label('Reject')
        ->color('danger')
        ->icon('heroicon-o-x-mark')
        ->visible(fn($record) =>
            $record->is_latest &&
            $record->status !== 'rejected'
        )
     ->action(function ($record) {

    $record->update([
        'status' => 'rejected',
    ]);

    ActivityLogService::log(
        'Shoppee Panel',
        'Shoppee KYC Rejected',
        'Rejected Shoppee KYC for member ' .
        $record->member_id .
        ' (' . $record->member_name . ')'
    );
}),

            ])

            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}