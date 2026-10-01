<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Expenses\Tables\ExpensesTable;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    public function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
    }

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make(),

            Action::make('pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')

                ->form([

                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->required(),

                    Forms\Components\DatePicker::make('to_date')
                        ->label('To Date')
                        ->required(),

                ])

                ->action(function (array $data) {

                    return redirect()->route(
                        'admin.expense_report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}