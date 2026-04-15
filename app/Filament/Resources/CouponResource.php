<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Cupons';
    protected static ?string $modelLabel = 'Cupom';
    protected static ?string $pluralModelLabel = 'Cupons';
    protected static ?string $navigationGroup = 'Pagamentos';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cupom')->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->extraAttributes(['style' => 'text-transform:uppercase']),
                Forms\Components\Select::make('type')
                    ->label('Tipo de desconto')
                    ->required()
                    ->options([
                        'percentage' => 'Porcentagem (%)',
                        'fixed'      => 'Valor fixo (R$)',
                    ]),
                Forms\Components\TextInput::make('value')
                    ->label('Valor do desconto')
                    ->required()
                    ->numeric()
                    ->step(0.01),
                Forms\Components\Select::make('applicable_to')
                    ->label('Aplicável a')
                    ->options([
                        'all'             => 'Todas as ações',
                        'first_download'  => 'Primeiro download',
                        'redownload'      => 'Re-download',
                        'template_change' => 'Troca de template',
                        'credit_purchase' => 'Compra de créditos',
                    ])
                    ->default('all'),
            ])->columns(2),

            Forms\Components\Section::make('Limites e validade')->schema([
                Forms\Components\TextInput::make('max_uses')
                    ->label('Máximo de usos')
                    ->numeric()
                    ->helperText('Deixe em branco para ilimitado'),
                Forms\Components\TextInput::make('current_uses')
                    ->label('Usos realizados')
                    ->numeric()
                    ->disabled()
                    ->default(0),
                Forms\Components\DateTimePicker::make('valid_from')
                    ->label('Válido a partir de')
                    ->displayFormat('d/m/Y H:i'),
                Forms\Components\DateTimePicker::make('valid_until')
                    ->label('Válido até')
                    ->displayFormat('d/m/Y H:i'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Porcentagem' : 'Valor fixo'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Desconto')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->type === 'percentage'
                            ? "{$state}%"
                            : 'R$ ' . number_format($state, 2, ',', '.')
                    ),
                Tables\Columns\TextColumn::make('current_uses')
                    ->label('Usos')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->max_uses ? "{$state}/{$record->max_uses}" : $state
                    ),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Válido até')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
