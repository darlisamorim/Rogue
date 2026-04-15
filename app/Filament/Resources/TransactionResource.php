<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transações';
    protected static ?string $modelLabel = 'Transação';
    protected static ?string $pluralModelLabel = 'Transações';
    protected static ?string $navigationGroup = 'Pagamentos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'failed'    => 'Falhou',
                    ]),
                Forms\Components\TextInput::make('amount')
                    ->label('Valor')
                    ->prefix('R$')
                    ->disabled(),
                Forms\Components\TextInput::make('type')
                    ->label('Tipo')
                    ->disabled(),
                Forms\Components\TextInput::make('gateway')
                    ->label('Gateway')
                    ->disabled(),
                Forms\Components\TextInput::make('gateway_transaction_id')
                    ->label('ID no Gateway')
                    ->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'download'        => 'Download',
                        'redownload'      => 'Re-download',
                        'template_change' => 'Troca de template',
                        'credit_purchase' => 'Compra de créditos',
                        default           => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'credit_purchase' => 'success',
                        'download'        => 'info',
                        default           => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'confirmed' => 'success',
                        'pending'   => 'warning',
                        'failed'    => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'confirmed' => 'Confirmado',
                        'pending'   => 'Pendente',
                        'failed'    => 'Falhou',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'failed'    => 'Falhou',
                    ]),
                Tables\Filters\SelectFilter::make('gateway')
                    ->label('Gateway')
                    ->options([
                        'mercadopago' => 'Mercado Pago',
                        'asaas'       => 'Asaas',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'download'        => 'Download',
                        'redownload'      => 'Re-download',
                        'template_change' => 'Troca de template',
                        'credit_purchase' => 'Compra de créditos',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view'  => Pages\ViewTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
