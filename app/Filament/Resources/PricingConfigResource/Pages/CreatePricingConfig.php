<?php
namespace App\Filament\Resources\PricingConfigResource\Pages;
use App\Filament\Resources\PricingConfigResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePricingConfig extends CreateRecord {
    protected static string $resource = PricingConfigResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
