<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingConfigResource\Pages;
use App\Models\PricingConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PricingConfigResource extends Resource
{
    protected static ?string $model = PricingConfig::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Preços das Ações';
    protected static ?string $modelLabel = 'Preço';
    protected static ?string $pluralModelLabel = 'Preços das Ações';
    protected static ?string $navigationGroup = 'Pagamentos';
    protected static ?int $navigationSort = 3;

    private static array $actionLabels = [
        'first_download'  => 'Primeiro download do PDF',
        'redownload'      => 'Re-download (pós edição)',
        'template_change' => 'Troca de template',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Configuração de Preço')->schema([
                Forms\Components\Select::make('action_slug')
                    ->label('Ação cobrada')
                    ->options(self::$actionLabels)
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->helperText('Define qual evento dispara a cobrança'),

                Forms\Components\TextInput::make('label')
                    ->label('Nome exibido ao usuário')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Download do PDF'),

                Forms\Components\TextInput::make('price')
                    ->label('Preço')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01)
                    ->minValue(0.01)
                    ->helperText('Valor cobrado via Pix ao usuário'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Cobrança ativa')
                    ->helperText('Desativar suspende a cobrança para esta ação')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Ação')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('action_slug')
                    ->label('Identificador')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'first_download'  => 'first_download',
                        'redownload'      => 'redownload',
                        'template_change' => 'template_change',
                        default           => $state,
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar preço'),
            ])
            ->defaultSort('action_slug');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPricingConfigs::route('/'),
            'create' => Pages\CreatePricingConfig::route('/create'),
            'edit'   => Pages\EditPricingConfig::route('/{record}/edit'),
        ];
    }
}
