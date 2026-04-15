<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditPackageResource\Pages;
use App\Models\CreditPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreditPackageResource extends Resource
{
    protected static ?string $model = CreditPackage::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Pacotes de Créditos';
    protected static ?string $modelLabel = 'Pacote de Créditos';
    protected static ?string $pluralModelLabel = 'Pacotes de Créditos';
    protected static ?string $navigationGroup = 'Pagamentos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do Pacote')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do pacote')
                    ->required()
                    ->maxLength(120)
                    ->placeholder('Ex: Básico, Intermediário, Avançado'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordem de exibição')
                    ->numeric()
                    ->default(0)
                    ->helperText('Menor número aparece primeiro'),
            ])->columns(2),

            Forms\Components\Section::make('Valores')->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Preço cobrado (R$)')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01)
                    ->minValue(0.01)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcBonus($get, $set))
                    ->helperText('Quanto o usuário paga'),

                Forms\Components\TextInput::make('credits')
                    ->label('Créditos entregues (R$)')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01)
                    ->minValue(0.01)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalcBonus($get, $set))
                    ->helperText('Valor em créditos que o usuário recebe na conta'),

                Forms\Components\TextInput::make('bonus_percentage')
                    ->label('Bônus calculado')
                    ->numeric()
                    ->suffix('%')
                    ->default(0)
                    ->readOnly()
                    ->helperText('Calculado automaticamente com base no preço e créditos'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Pacote ativo')
                    ->default(true)
                    ->helperText('Pacotes inativos não aparecem para o usuário'),
            ])->columns(2),
        ]);
    }

    protected static function recalcBonus(Get $get, Set $set): void
    {
        $price   = (float) $get('price');
        $credits = (float) $get('credits');

        if ($price > 0 && $credits > 0) {
            $bonus = (int) round((($credits - $price) / $price) * 100);
            $set('bonus_percentage', max(0, $bonus));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Pacote')
                    ->searchable()
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('credits')
                    ->label('Créditos')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('bonus_percentage')
                    ->label('Bônus')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCreditPackages::route('/'),
            'create' => Pages\CreateCreditPackage::route('/create'),
            'edit'   => Pages\EditCreditPackage::route('/{record}/edit'),
        ];
    }
}
