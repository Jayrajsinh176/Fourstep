<?php

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

              TextInput::make('password')
    ->password()
    ->revealable()
    ->required(fn (string $operation) => $operation === 'create')
    ->dehydrated(fn ($state) => filled($state))
    ->helperText('Leave blank to keep the current password.'),

            TextInput::make('password_confirmation')
    ->password()
    ->revealable()
    ->dehydrated(false)
    ->helperText('Only required if you are changing the password.'),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),

                    ]),

              Section::make('Permissions')
                ->columns(1)
                ->description('Select the modules and actions this admin user is allowed to access.')
    ->schema([

        Fieldset::make('Dashboard')
            ->schema([
                Checkbox::make('permissions.dashboard')
                    ->label('Dashboard'),
            ]),

        Fieldset::make('Ecommerce Panel')
            ->schema([

                Checkbox::make('permissions.customers')
                    ->label('Customers'),

                Checkbox::make('permissions.categories')
                    ->label('Categories'),

                Fieldset::make('Products')
                    ->schema([
                        Checkbox::make('permissions.products.view')->label('View'),
                        Checkbox::make('permissions.products.create')->label('Create'),
                        Checkbox::make('permissions.products.edit')->label('Edit'),
                        Checkbox::make('permissions.products.delete')->label('Delete'),
                    ]),

                Fieldset::make('Orders')
                    ->schema([
                        Checkbox::make('permissions.ecommerce_orders.view')->label('View'),
                        Checkbox::make('permissions.ecommerce_orders.status')->label('Status Change'),
                        Checkbox::make('permissions.ecommerce_orders.cancel')->label('Cancel'),
                    ]),

                Fieldset::make('Help Tickets')
                    ->schema([
                        Checkbox::make('permissions.help_tickets.view')->label('View'),
                        Checkbox::make('permissions.help_tickets.reply')->label('Reply'),
                        Checkbox::make('permissions.help_tickets.close')->label('Close'),
                    ]),

            ]),

Fieldset::make('Member Panel')
    ->columns(2)
    
    ->schema([

        Fieldset::make('Members')
            ->schema([
                Checkbox::make('permissions.members.view')->label('View'),
                Checkbox::make('permissions.members.create')->label('Create'),
                Checkbox::make('permissions.members.edit')->label('Edit'),
                Checkbox::make('permissions.members.delete')->label('Delete'),
            ]),

        Fieldset::make('Member KYC Verification')
            ->schema([
                Checkbox::make('permissions.member_kyc.view')->label('View'),
                Checkbox::make('permissions.member_kyc.approve')->label('Approve'),
                Checkbox::make('permissions.member_kyc.reject')->label('Reject'),
            ]),

        Checkbox::make('permissions.view_tree')
            ->label('View Tree'),

        Fieldset::make('Member Balance Requests')
            ->schema([
                Checkbox::make('permissions.member_balance_requests.view')->label('View'),
                Checkbox::make('permissions.member_balance_requests.approve')->label('Approve'),
                Checkbox::make('permissions.member_balance_requests.reject')->label('Reject'),
            ]),

        Checkbox::make('permissions.rank_achievers')
            ->label('Rank Achievers'),

        Checkbox::make('permissions.reward_achievers')
            ->label('Reward Achievers'),

        Checkbox::make('permissions.active_block')
            ->label('Active / Block'),

        Checkbox::make('permissions.daily_closing')
            ->label('Daily Closing'),

        Fieldset::make('Member Orders')
            ->schema([
                Checkbox::make('permissions.member_orders.view')->label('View'),
                Checkbox::make('permissions.member_orders.status')->label('Status Change'),
                Checkbox::make('permissions.member_orders.cancel')->label('Cancel'),
            ]),

    ]),
    
    Fieldset::make('Shoppee Panel')
  
    ->columns(2)
    ->schema([

        Checkbox::make('permissions.add_branch')
            ->label('Add Branch'),

        Fieldset::make('Branch List')
            ->schema([
                Checkbox::make('permissions.branch_list.view')->label('View'),
                Checkbox::make('permissions.branch_list.create')->label('Create'),
                Checkbox::make('permissions.branch_list.edit')->label('Edit'),
                Checkbox::make('permissions.branch_list.delete')->label('Delete'),
            ]),

        Fieldset::make('Shoppee KYC Verification')
            ->schema([
                Checkbox::make('permissions.shoppee_kyc.view')->label('View'),
                Checkbox::make('permissions.shoppee_kyc.approve')->label('Approve'),
                Checkbox::make('permissions.shoppee_kyc.reject')->label('Reject'),
            ]),

        Fieldset::make('Product Requests')
            ->schema([
                Checkbox::make('permissions.product_requests.view')->label('View'),
                Checkbox::make('permissions.product_requests.approve')->label('Approve'),
                Checkbox::make('permissions.product_requests.reject')->label('Reject'),
            ]),

        Fieldset::make('Shoppee Balance Requests')
            ->schema([
                Checkbox::make('permissions.shoppee_balance_requests.view')->label('View'),
                Checkbox::make('permissions.shoppee_balance_requests.approve')->label('Approve'),
                Checkbox::make('permissions.shoppee_balance_requests.reject')->label('Reject'),
            ]),

        Fieldset::make('Helpdesk')
            ->schema([
                Checkbox::make('permissions.shoppee_helpdesk.view')->label('View'),
                Checkbox::make('permissions.shoppee_helpdesk.reply')->label('Reply'),
                Checkbox::make('permissions.shoppee_helpdesk.close')->label('Close'),
            ]),

    ]),
    
    Fieldset::make('Income Reports')
    ->columns(2)
    ->schema([

        Checkbox::make('permissions.repurchase_bonus_report')
            ->label('Repurchase Bonus'),

        Checkbox::make('permissions.leadership_rank_report')
            ->label('Leadership Rank'),

        Checkbox::make('permissions.cashback_report')
            ->label('Cashback Report'),

        Checkbox::make('permissions.group_builtup_bonus_report')
            ->label('Group Built-up Bonus'),

        Checkbox::make('permissions.royalty_club_bonus_report')
            ->label('Royalty Club Bonus'),

        Checkbox::make('permissions.consistency_bonus_report')
            ->label('Consistency Bonus'),

        Checkbox::make('permissions.business_monitoring_report')
            ->label('Business Monitoring'),

        Checkbox::make('permissions.rank_reward_report')
            ->label('Rank Reward'),

        Checkbox::make('permissions.branch_turnover_bonus_report')
            ->label('Branch Turnover Bonus'),

        Checkbox::make('permissions.family_saver_bonus_report')
            ->label('Family Saver Bonus'),

    ]),
    
    Fieldset::make('Accounts')
    ->columns(2)
    ->schema([

        Checkbox::make('permissions.capital_management')
            ->label('Capital Management'),

        Checkbox::make('permissions.process_payout')
            ->label('Process Payout'),

        Checkbox::make('permissions.expenses')
            ->label('Expenses'),

        Checkbox::make('permissions.payout_history')
            ->label('Payout History'),

        Checkbox::make('permissions.tds_report')
            ->label('TDS Report'),

    ]),
    
    ])
    ->columnSpanFull(),

            ]);
    }
}