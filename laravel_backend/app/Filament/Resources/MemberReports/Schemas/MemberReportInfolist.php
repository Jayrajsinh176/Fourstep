<?php

namespace App\Filament\Resources\MemberReports\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;

class MemberReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | HEADER — Full-width summary bar (columnSpanFull fix)
                |--------------------------------------------------------------------------
                */

                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)
                            ->schema([

                                TextEntry::make('fullname')
                                    ->label('Full Name')
                                    // ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->columnSpan(1),

                                TextEntry::make('user_id')
                                    ->label('Member ID')
                                    ->badge()
                                    ->copyable()
                                    ->columnSpan(1),

                                TextEntry::make('sponsor.user_id')
                                    ->label('Sponsor ID')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('—')
                                    ->belowContent(fn ($record) => $record->sponsor?->fullname)
                                    ->columnSpan(1),

                                TextEntry::make('is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                    ->columnSpan(1),

                            ]),
                    ])
                    ->extraAttributes(['class' => 'bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm']),

                /*
                |--------------------------------------------------------------------------
                | ROW 1 — Personal Details + MLM Details
                |--------------------------------------------------------------------------
                */

                Grid::make(2)
                    ->schema([

                        Section::make('Personal Details')
                            ->icon('heroicon-m-user-circle')
                            ->collapsible()
                            ->compact()
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        TextEntry::make('fullname')
                                            ->label('Full Name')
                                            ->weight(FontWeight::Bold)
                                            ->color('primary'),

                                        TextEntry::make('user_id')
                                            ->label('Member ID')
                                            ->badge()
                                            ->copyable(),

                                        TextEntry::make('email')
                                            ->label('Email Address')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable()
                                            ->columnSpanFull(),

                                        TextEntry::make('mobile_no')
                                            ->label('Mobile Number')
                                            ->icon('heroicon-m-phone')
                                            ->copyable(),

                                        TextEntry::make('dob')
                                            ->label('Date of Birth')
                                            ->date('d M, Y')
                                            ->icon('heroicon-m-calendar-days'),

                                        TextEntry::make('gender')
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('pan_number')
                                            ->label('PAN Number')
                                            ->copyable()
                                            ->placeholder('—'),

                                        TextEntry::make('status')
                                            ->label('Account Status')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                            ->color(fn ($state) => $state ? 'success' : 'danger'),

                                        TextEntry::make('activation_date')
                                            ->label('Activation Date')
                                            ->date('d M, Y')
                                            ->placeholder('—'),

                                        TextEntry::make('created_at')
                                            ->label('Joining Date')
                                            ->date('d M, Y'),

                                    ]),
                            ]),

                        Section::make('MLM Details')
                            ->icon('heroicon-m-chart-bar')
                            ->collapsible()
                            ->compact()
                            ->schema([

                                Grid::make(2)
                                    ->schema([

                                        TextEntry::make('sponsor.user_id')
                                            ->label('Sponsor ID')
                                            ->badge()
                                            ->color('info')
                                            ->placeholder('—')
                                            ->belowContent(fn ($record) => $record->sponsor?->fullname),

                                        TextEntry::make('parent.user_id')
                                            ->label('Parent ID')
                                            ->placeholder('—')
                                            ->belowContent(fn ($record) => $record->parent?->fullname),

                                        TextEntry::make('position')
                                            ->badge()
                                            ->color('warning'),

                                        TextEntry::make('package_step')
                                            ->label('Package')
                                            ->badge()
                                            ->color('primary'),

                                    ]),

                                Section::make('Point Values')
                                    ->compact()
                                    ->extraAttributes(['class' => 'bg-gray-50 dark:bg-gray-800 rounded-lg mt-2'])
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([

                                                TextEntry::make('self_bv')
                                                    ->label('BV')
                                                    ->numeric(decimalPlaces: 2)
                                                    ->badge()
                                                    ->color('primary'),

                                                TextEntry::make('builtup_left_bv')
                                                    ->label('Left BV')
                                                    ->numeric(decimalPlaces: 2),

                                                TextEntry::make('builtup_right_bv')
                                                    ->label('Right BV')
                                                    ->numeric(decimalPlaces: 2),

                                                TextEntry::make('carry_forward_left')
                                                    ->label('CF Left')
                                                    ->numeric(decimalPlaces: 2),

                                                TextEntry::make('carry_forward_right')
                                                    ->label('CF Right')
                                                    ->numeric(decimalPlaces: 2),

                                            ]),
                                    ]),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | ROW 2 — Address Details + Shipping Details
                |--------------------------------------------------------------------------
                */

                Grid::make(2)
                    ->schema([

                        Section::make('Address Details')
                            ->icon('heroicon-m-map-pin')
                            ->collapsible()
                            ->compact()
                            ->schema([

                                TextEntry::make('address')
                                    ->label('Full Address')
                                    ->icon('heroicon-m-home')
                                    ->placeholder('—')
                                    ->columnSpanFull(),

                                Grid::make(4)
                                    ->schema([

                                        TextEntry::make('city')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('district')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('state')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('pin_code')
                                            ->label('Pin Code')
                                            ->badge()
                                            ->color('gray')
                                            ->placeholder('—'),

                                    ]),
                            ]),

                        Section::make('Shipping Details')
                            ->icon('heroicon-m-truck')
                            ->collapsible()
                            ->compact()
                            ->schema([

                                TextEntry::make('shipping_address')
                                    ->label('Shipping Address')
                                    ->icon('heroicon-m-home')
                                    ->placeholder('—')
                                    ->columnSpanFull(),

                                Grid::make(4)
                                    ->schema([

                                        TextEntry::make('shipping_city')
                                            ->label('City')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('shipping_district')
                                            ->label('District')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('shipping_state')
                                            ->label('State')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('shipping_pin_code')
                                            ->label('Pin Code')
                                            ->badge()
                                            ->color('gray')
                                            ->placeholder('—'),

                                    ]),
                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | ROW 3 — Nominee Details + System Details
                |--------------------------------------------------------------------------
                */

                Grid::make(2)
                    ->schema([

                        Section::make('Nominee Details')
                            ->icon('heroicon-m-user-plus')
                            ->collapsible()
                            ->compact()
                            ->schema([

                                Grid::make(2)
                                    ->schema([

                                        TextEntry::make('nominee_name')
                                            ->label('Nominee Name')
                                            ->weight(FontWeight::Bold)
                                            ->placeholder('—'),

                                        TextEntry::make('nominee_relation')
                                            ->label('Relation')
                                            ->badge()
                                            ->color('info')
                                            ->placeholder('—'),

                                        TextEntry::make('nominee_mobile_no')
                                            ->label('Mobile Number')
                                            ->copyable()
                                            ->placeholder('—'),

                                    ]),

                                TextEntry::make('nominee_address')
                                    ->label('Nominee Address')
                                    ->icon('heroicon-m-home')
                                    ->placeholder('—')
                                    ->columnSpanFull(),

                                Grid::make(4)
                                    ->schema([

                                        TextEntry::make('nominee_city')
                                            ->label('City')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('nominee_district')
                                            ->label('District')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('nominee_state')
                                            ->label('State')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('—'),

                                        TextEntry::make('nominee_pin_code')
                                            ->label('Pin Code')
                                            ->badge()
                                            ->color('gray')
                                            ->placeholder('—'),

                                    ]),
                            ]),

                        Section::make('System Details')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->collapsible()
                            ->compact()
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime('d M, Y — H:i')
                                            ->icon('heroicon-m-clock'),

                                        TextEntry::make('updated_at')
                                            ->label('Updated At')
                                            ->dateTime('d M, Y — H:i')
                                            ->icon('heroicon-m-clock'),

                                        TextEntry::make('is_active')
                                            ->label('Member Status')
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'success' : 'danger')
                                            ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),

                                    ]),
                            ]),

                    ]),

            ]);
    }
}