<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use App\Models\PricingConfig;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário de teste
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Preços das ações (editáveis via Filament)
        $pricingConfigs = [
            [
                'action_slug' => 'first_download',
                'label' => 'Primeiro download',
                'price' => 1.99,
                'is_active' => true,
            ],
            [
                'action_slug' => 'redownload',
                'label' => 'Re-download / pós-modificação',
                'price' => 0.99,
                'is_active' => true,
            ],
            [
                'action_slug' => 'template_change',
                'label' => 'Aplicar novo template',
                'price' => 3.00,
                'is_active' => true,
            ],
        ];

        foreach ($pricingConfigs as $config) {
            PricingConfig::updateOrCreate(
                ['action_slug' => $config['action_slug']],
                $config
            );
        }

        // Pacotes de créditos
        $creditPackages = [
            [
                'name' => 'Básico',
                'price' => 4.90,
                'credits' => 6.00,
                'bonus_percentage' => 22,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Intermediário',
                'price' => 7.90,
                'credits' => 10.00,
                'bonus_percentage' => 27,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Avançado',
                'price' => 12.90,
                'credits' => 17.00,
                'bonus_percentage' => 32,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($creditPackages as $package) {
            CreditPackage::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }

        // Templates iniciais
        $templates = [
            [
                'name' => 'Minimalista',
                'slug' => 'minimalist',
                'component_name' => 'TemplateMinimalist',
                'config' => [
                    'colors' => ['#000000', '#333333', '#666666', '#2563eb', '#16a34a', '#dc2626'],
                    'fonts' => ['Inter', 'Roboto', 'Open Sans'],
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Moderno',
                'slug' => 'modern',
                'component_name' => 'TemplateModern',
                'config' => [
                    'colors' => ['#1e40af', '#7c3aed', '#059669', '#d97706', '#dc2626', '#0f172a'],
                    'fonts' => ['Inter', 'Poppins', 'Montserrat'],
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Clássico',
                'slug' => 'classic',
                'component_name' => 'TemplateClassic',
                'config' => [
                    'colors' => ['#1a1a1a', '#2c3e50', '#34495e', '#8b4513', '#2e4057', '#003366'],
                    'fonts' => ['Georgia', 'Times New Roman', 'Garamond'],
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Criativo',
                'slug' => 'creative',
                'component_name' => 'TemplateCreative',
                'config' => [
                    'colors' => ['#f97316', '#8b5cf6', '#ec4899', '#06b6d4', '#10b981', '#f59e0b'],
                    'fonts' => ['Poppins', 'Nunito', 'Raleway'],
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Técnico',
                'slug' => 'tech',
                'component_name' => 'TemplateTech',
                'config' => [
                    'colors' => ['#0ea5e9', '#22c55e', '#a855f7', '#f97316', '#ef4444', '#14b8a6'],
                    'fonts' => ['JetBrains Mono', 'Fira Code', 'Source Code Pro'],
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
