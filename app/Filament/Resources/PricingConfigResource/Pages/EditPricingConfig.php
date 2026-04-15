<?php
namespace App\Filament\Resources\PricingConfigResource\Pages;
use App\Filament\Resources\PricingConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPricingConfig extends EditRecord {
    protected static string $resource = PricingConfigResource::class;
    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
