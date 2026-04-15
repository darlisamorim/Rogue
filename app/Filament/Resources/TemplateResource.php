<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'Templates';
    protected static ?string $modelLabel = 'Template';
    protected static ?string $pluralModelLabel = 'Templates';
    protected static ?string $navigationGroup = 'Currículos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(120),
                Forms\Components\TextInput::make('component_name')
                    ->label('Componente Vue')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('thumbnail_url')
                    ->label('URL da thumbnail')
                    ->url(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
                Forms\Components\KeyValue::make('config')
                    ->label('Configuração (JSON)')
                    ->reorderable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('component_name')
                    ->label('Componente')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('resumes_count')
                    ->label('Usos')
                    ->counts('resumes')
                    ->sortable(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit'   => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
