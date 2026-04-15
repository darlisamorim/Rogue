<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?string $navigationGroup = 'Currículos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados da conta')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Deixe em branco para manter a senha atual'),
            ])->columns(2),

            Forms\Components\Section::make('Perfil')->schema([
                Forms\Components\TextInput::make('professional_title')
                    ->label('Cargo'),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefone'),
                Forms\Components\TextInput::make('location')
                    ->label('Cidade/Região'),
                Forms\Components\TextInput::make('cpf')
                    ->label('CPF'),
            ])->columns(2),

            Forms\Components\Section::make('Créditos e Permissões')->schema([
                Forms\Components\TextInput::make('credit_balance')
                    ->label('Saldo de créditos (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01)
                    ->helperText('Use a ação "Conceder Créditos" na lista para adicionar via pacote'),
                Forms\Components\Toggle::make('is_admin')
                    ->label('Acesso ao painel administrativo'),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('professional_title')
                    ->label('Cargo')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('credit_balance')
                    ->label('Créditos')
                    ->money('BRL')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('resumes_count')
                    ->label('Currículos')
                    ->counts('resumes')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Administrador'),
                Tables\Filters\Filter::make('has_credits')
                    ->label('Com créditos')
                    ->query(fn ($query) => $query->where('credit_balance', '>', 0)),
            ])
            ->actions([
                Tables\Actions\Action::make('add_credits')
                    ->label('Conceder Créditos')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('package_id')
                            ->label('Aplicar pacote')
                            ->options(
                                CreditPackage::where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [
                                        $p->id => "{$p->name} — paga R$ " . number_format($p->price, 2, ',', '.') . " → recebe R$ " . number_format($p->credits, 2, ',', '.'),
                                    ])
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $package = CreditPackage::find($state);
                                    $set('amount', number_format($package->credits, 2, '.', ''));
                                }
                            })
                            ->placeholder('Selecione um pacote ou insira o valor manualmente'),

                        Forms\Components\TextInput::make('amount')
                            ->label('Valor a adicionar (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01),

                        Forms\Components\Textarea::make('note')
                            ->label('Observação (opcional)')
                            ->rows(2)
                            ->placeholder('Ex: Cortesia, ajuste manual, pacote promocional...'),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->increment('credit_balance', $data['amount']);

                        CreditTransaction::create([
                            'user_id'        => $record->id,
                            'type'           => 'purchase',
                            'amount'         => $data['amount'],
                            'balance_after'  => $record->fresh()->credit_balance,
                            'reference_type' => 'manual',
                            'reference_id'   => null,
                        ]);

                        Notification::make()
                            ->title('Créditos adicionados')
                            ->body("R$ " . number_format($data['amount'], 2, ',', '.') . " adicionados ao saldo de {$record->name}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('remove_credits')
                    ->label('Remover Créditos')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Valor a remover (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01),

                        Forms\Components\Textarea::make('note')
                            ->label('Motivo')
                            ->rows(2)
                            ->required()
                            ->placeholder('Ex: Estorno, ajuste de saldo...'),
                    ])
                    ->action(function (User $record, array $data) {
                        $deduct = min($data['amount'], (float) $record->credit_balance);
                        $record->decrement('credit_balance', $deduct);

                        CreditTransaction::create([
                            'user_id'        => $record->id,
                            'type'           => 'debit',
                            'amount'         => $deduct,
                            'balance_after'  => $record->fresh()->credit_balance,
                            'reference_type' => 'manual',
                            'reference_id'   => null,
                        ]);

                        Notification::make()
                            ->title('Créditos removidos')
                            ->body("R$ " . number_format($deduct, 2, ',', '.') . " removidos do saldo de {$record->name}.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
