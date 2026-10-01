<?php

namespace App\Filament\Pages;

use App\Models\WeeklyClosing;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class DailyClosingDetails extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'weekly-closing-details';

    protected string $view = 'filament.pages.daily-closing-details';

    protected static ?string $title = 'Weekly Closing Details';
    
    public string $weekStart = '';

    public string $weekEnd = '';

    public int $totalMembers = 0;

    public float $totalBusinessBv = 0;

    public float $totalIncome = 0;

    public function mount(): void
    {
        $this->weekStart = request()->query('week_start', '');
        $this->weekEnd = request()->query('week_end', '');

        abort_if(
            blank($this->weekStart) || blank($this->weekEnd),
            404,
            'Weekly closing period not found.'
        );

        $query = WeeklyClosing::whereDate(
            'week_start',
            $this->weekStart
        )->whereDate(
            'week_end',
            $this->weekEnd
        );

        $this->totalMembers = (clone $query)->count();

        $this->totalBusinessBv = (float) (clone $query)
            ->sum('matched_bv');

        $this->totalIncome = (float) (clone $query)
            ->sum('income');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WeeklyClosing::query()
                    ->with('member')
                    ->whereDate('week_start', $this->weekStart)
                    ->whereDate('week_end', $this->weekEnd)
            )
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('Sr.')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('member.user_id')
                    ->label('Member ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.fullname')
                    ->label('Member Name')
                    ->searchable()
                    ->grow(),

                Tables\Columns\TextColumn::make('left_bv')
                    ->label('Left BV')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BV'),

                Tables\Columns\TextColumn::make('right_bv')
                    ->label('Right BV')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BV'),

                Tables\Columns\TextColumn::make('matched_bv')
                    ->label('Matching BV')
                    ->badge()
                    ->color('success')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BV'),

                Tables\Columns\TextColumn::make('carry_left')
                    ->label('Carry Left')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BV'),

                Tables\Columns\TextColumn::make('carry_right')
                    ->label('Carry Right')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BV'),

                Tables\Columns\TextColumn::make('income')
                    ->label('Purchase Bonus')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ])
                    ->sortable(),
            ]);
    }
}