<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\HtmlString;

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Gateways de Pagamento';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'active_gateway'             => env('ACTIVE_PAYMENT_GATEWAY', 'mercadopago'),
            'mercadopago_access_token'   => env('MERCADOPAGO_ACCESS_TOKEN', ''),
            'mercadopago_webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET', ''),
            'asaas_api_key'              => env('ASAAS_API_KEY', ''),
            'asaas_webhook_secret'       => env('ASAAS_WEBHOOK_SECRET', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        $activeGateway = env('ACTIVE_PAYMENT_GATEWAY', 'mercadopago');

        return $form
            ->schema([
                Section::make('Gateway Ativo')
                    ->description('Define qual gateway processa os pagamentos Pix na plataforma.')
                    ->schema([
                        Select::make('active_gateway')
                            ->label('Gateway de pagamento')
                            ->options([
                                'mercadopago' => 'Mercado Pago — recomendado para valores abaixo de R$ 5',
                                'asaas'       => 'Asaas — recomendado para valores acima de R$ 5',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Mercado Pago cobra ~0,99% sem taxa fixa. Asaas cobra ~R$ 0,49 por Pix.'),

                        Placeholder::make('gateway_info')
                            ->label('Gateway atual')
                            ->content(new HtmlString(
                                $activeGateway === 'mercadopago'
                                    ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">● Mercado Pago ativo</span>'
                                    : '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">● Asaas ativo</span>'
                            )),
                    ])->columns(2),

                Section::make('Mercado Pago')
                    ->description('Credenciais para cobranças Pix via Mercado Pago. Encontre em mercadopago.com.br → Sua Integração → Credenciais de produção.')
                    ->schema([
                        TextInput::make('mercadopago_access_token')
                            ->label('Access Token')
                            ->password()
                            ->revealable()
                            ->placeholder('APP_USR-...')
                            ->helperText('Token de produção (começa com APP_USR-)'),

                        TextInput::make('mercadopago_webhook_secret')
                            ->label('Webhook Secret')
                            ->password()
                            ->revealable()
                            ->helperText('Chave HMAC para validar notificações recebidas'),
                    ])->columns(2),

                Section::make('Asaas')
                    ->description('Credenciais para cobranças Pix via Asaas. Encontre em asaas.com → Configurações → Integrações → API.')
                    ->schema([
                        TextInput::make('asaas_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('$aact_...')
                            ->helperText('Chave de API de produção (começa com $aact_)'),

                        TextInput::make('asaas_webhook_secret')
                            ->label('Webhook Secret')
                            ->password()
                            ->revealable()
                            ->helperText('Chave HMAC para validar notificações recebidas'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->setEnvValue('ACTIVE_PAYMENT_GATEWAY', $data['active_gateway']);

        if (filled($data['mercadopago_access_token'])) {
            $this->setEnvValue('MERCADOPAGO_ACCESS_TOKEN', $data['mercadopago_access_token']);
        }
        if (filled($data['mercadopago_webhook_secret'])) {
            $this->setEnvValue('MERCADOPAGO_WEBHOOK_SECRET', $data['mercadopago_webhook_secret']);
        }
        if (filled($data['asaas_api_key'])) {
            $this->setEnvValue('ASAAS_API_KEY', $data['asaas_api_key']);
        }
        if (filled($data['asaas_webhook_secret'])) {
            $this->setEnvValue('ASAAS_WEBHOOK_SECRET', $data['asaas_webhook_secret']);
        }

        Artisan::call('config:clear');

        Notification::make()
            ->title('Configurações salvas!')
            ->body('As credenciais foram atualizadas no .env e o cache de config foi limpo.')
            ->success()
            ->send();
    }

    protected function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        // Wrap value in quotes if it contains spaces or special chars
        $needsQuotes = preg_match('/[\s#$]/', $value);
        $formatted   = $needsQuotes ? "\"{$value}\"" : $value;

        $escaped = preg_quote('=', '/');
        if (preg_match("/^{$key}{$escaped}/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$formatted}", $content);
        } else {
            $content .= "\n{$key}={$formatted}";
        }

        file_put_contents($envPath, $content);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Salvar configurações')
                ->icon('heroicon-o-check-circle')
                ->action('save'),
        ];
    }
}
