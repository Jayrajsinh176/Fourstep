<?php

namespace App\Filament\Resources\MemberKycs\Tables;

use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\DeleteBulkAction;
use App\Services\ActivityLogService;

class MemberKycsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc') // ✅ latest first
       ->recordUrl(null)
        ->recordAction(null)
            ->columns([

                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_beneficiary_name')
                    ->label('Name'),

                Tables\Columns\TextColumn::make('account_no'),
                Tables\Columns\TextColumn::make('ifs_code'),
                Tables\Columns\TextColumn::make('bank_name'),
                Tables\Columns\TextColumn::make('branch_name'),

                // ✅ NEW: Attempt count
                Tables\Columns\TextColumn::make('member_id')
                    ->label('Attempts')
                    ->formatStateUsing(fn ($state) =>
                        \App\Models\MyKyc::where('member_id', $state)->count()
                    ),

                // ✅ NEW: Latest indicator
                Tables\Columns\IconColumn::make('is_latest')
                    ->label('Latest')
                    ->boolean(),

                ImageColumn::make('bank_passbook_image')
                    ->label(' Account document')
                    ->disk('public')
                    ->visibility('public')
                    ->height(40)
                    ->getStateUsing(fn($record) => $record->bank_passbook_image ? asset('storage/' . $record->bank_passbook_image) : null)
                    ->url(fn($record) => $record->bank_passbook_image ? asset('storage/' . $record->bank_passbook_image) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('aadhaar_number'),

                ImageColumn::make('aadhaar_image')
                    ->label('Aadhaar')
                    ->disk('public')
                    ->visibility('public')
                    ->height(40)
                    ->getStateUsing(fn($record) => $record->aadhaar_image ? asset('storage/' . $record->aadhaar_image) : null)
                    ->url(fn($record) => $record->aadhaar_image ? asset('storage/' . $record->aadhaar_image) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('pan_number'),

                ImageColumn::make('pan_image')
                    ->label('PAN')
                    ->disk('public')
                    ->visibility('public')
                    ->height(40)
                    ->getStateUsing(fn($record) => $record->pan_image ? asset('storage/' . $record->pan_image) : null)
                    ->url(fn($record) => $record->pan_image ? asset('storage/' . $record->pan_image) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),

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

                // 👁 VIEW FULL DETAILS
                ViewAction::make()
                    ->modalHeading('KYC Full Details')
                    ->modalWidth('7xl')

                    ->form([

                        Section::make('Basic Info')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('user_id')->disabled(),
                                Forms\Components\TextInput::make('account_beneficiary_name')->disabled(),
                                Forms\Components\TextInput::make('account_no')->disabled(),
                                Forms\Components\TextInput::make('ifs_code')->disabled(),
                                Forms\Components\TextInput::make('bank_name')->disabled(),
                                Forms\Components\TextInput::make('branch_name')->disabled(),
                            ]),

                        Section::make('Documents')
                            ->columns(2)
                            ->schema([

                                // ✅ FIXED (aadhaar spelling)
                                Forms\Components\TextInput::make('aadhaar_number')->disabled(),

                                FileUpload::make('aadhaar_image')
                                    ->label('Aadhaar Image')
                                    ->disk('public')
                                    ->image()
                                    ->disabled(),

                                Forms\Components\TextInput::make('pan_number')->disabled(),

                                FileUpload::make('pan_image')
                                    ->label('PAN Image')
                                    ->disk('public')
                                    ->image()
                                    ->disabled(),

                                FileUpload::make('bank_passbook_image')
                                    ->label('Passbook Image')
                                    ->disk('public')
                                    ->image()
                                    ->disabled(),
                            ]),

                        Section::make('Verification')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('status')->disabled(),
                                Forms\Components\TextInput::make('otp_verified')->disabled(),
                                Forms\Components\TextInput::make('transaction_password_status')->disabled(),
                                Forms\Components\TextInput::make('transaction_password_checked_at')->disabled(),
                            ]),
                    ]),

                // ✅ APPROVE (ONLY LATEST)
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn($record) => $record->is_latest && $record->status !== 'approved')
             ->action(function ($record) {

    $record->update([
        'status' => 'approved',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'KYC Approved',
        'Approved KYC for member ' .
        $record->user_id .
        ' (' . $record->account_beneficiary_name . ')'
    );
}),
               

                // ❌ REJECT (ONLY LATEST)
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn($record) => $record->is_latest && $record->status !== 'rejected')
                 ->action(function ($record) {

    $record->update([
        'status' => 'rejected',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'KYC Rejected',
        'Rejected KYC for member ' .
        $record->user_id .
        ' (' . $record->account_beneficiary_name . ')'
    );
}),
               
            ])

            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}