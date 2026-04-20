<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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

    // ─── TABELA ───────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Telefone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.cpf')
                    ->label('CPF')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'redownload'      => 'warning',
                        default           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Líquido')
                    ->money('BRL')
                    ->sortable()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'mercadopago' => 'Mercado Pago',
                        'asaas'       => 'Asaas',
                        'credits'     => 'Créditos',
                        default       => $state,
                    }),

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
                        'credits'     => 'Créditos',
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
                Tables\Actions\ViewAction::make()->label('Ver detalhes'),
            ]);
    }

    // ─── INFOLIST (detalhe da transação) ──────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Dados do comprador ──────────────────────────────────────────
            Infolists\Components\Section::make('Comprador')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Nome completo'),

                    Infolists\Components\TextEntry::make('user.email')
                        ->label('E-mail')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('user.phone')
                        ->label('Telefone')
                        ->placeholder('—'),

                    Infolists\Components\TextEntry::make('user.cpf')
                        ->label('CPF')
                        ->placeholder('—'),

                    Infolists\Components\TextEntry::make('user.professional_title')
                        ->label('Cargo / Área')
                        ->placeholder('—'),

                    Infolists\Components\TextEntry::make('user.location')
                        ->label('Cidade / Região')
                        ->placeholder('—'),
                ]),

            // ── Currículo relacionado ───────────────────────────────────────
            Infolists\Components\Section::make('Currículo')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->hidden(fn ($record) => $record->resume_id === null)
                ->schema([
                    Infolists\Components\TextEntry::make('resume.title')
                        ->label('Título do currículo'),

                    Infolists\Components\TextEntry::make('resume.template.name')
                        ->label('Template')
                        ->placeholder('—'),
                ]),

            // ── Dados da transação ──────────────────────────────────────────
            Infolists\Components\Section::make('Transação')
                ->icon('heroicon-o-credit-card')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('type')
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
                            'redownload'      => 'warning',
                            default           => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('status')
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

                    Infolists\Components\TextEntry::make('gateway')
                        ->label('Gateway')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'mercadopago' => 'Mercado Pago',
                            'asaas'       => 'Asaas',
                            'credits'     => 'Créditos',
                            default       => $state,
                        }),

                    Infolists\Components\TextEntry::make('gateway_transaction_id')
                        ->label('ID no Gateway')
                        ->placeholder('—')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Criada em')
                        ->dateTime('d/m/Y H:i:s'),

                    Infolists\Components\TextEntry::make('confirmed_at')
                        ->label('Confirmada em')
                        ->dateTime('d/m/Y H:i:s')
                        ->placeholder('Aguardando'),
                ]),

            // ── Valores financeiros ─────────────────────────────────────────
            Infolists\Components\Section::make('Financeiro')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('amount')
                        ->label('Valor cobrado')
                        ->money('BRL'),

                    Infolists\Components\TextEntry::make('fee_amount')
                        ->label('Taxa do gateway')
                        ->money('BRL')
                        ->color('danger'),

                    Infolists\Components\TextEntry::make('net_amount')
                        ->label('Valor líquido (seu)')
                        ->money('BRL')
                        ->color('success')
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                ]),

        ]);
    }

    // ─── PÁGINAS ───────────────────────────────────────────────────────────────

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
