# CLAUDE.md — SaaS de Criação de Currículos Online

## Visão Geral do Projeto

Plataforma SaaS onde usuários criam, personalizam e baixam currículos profissionais em PDF.
Monetização por micro-transações via Pix (valores a partir de R$ 0,99).
Princípios: **Prático**, **Rápido**, **Sem complicação**, **Acessível**, **Inteligente (IA)**.

---

## Stack Tecnológica

### Backend
- **Framework:** Laravel 11
- **Autenticação (usuário):** Laravel Breeze (login, registro, reset senha, verificação email)
- **Login social:** Laravel Socialite (Google, Facebook)
- **Painel Admin:** Filament v3 (CRUD automático, dashboards, widgets, settings)
- **Banco de Dados:** PostgreSQL
- **ORM:** Eloquent (nativo Laravel)
- **Filas/Jobs:** Laravel Queues + Redis (geração de PDF em background)
- **Cache:** Redis (rate limiting, sessão, cache de config)
- **Geração PDF:** Browsershot (Puppeteer via PHP)
- **Storage:** Laravel Storage (S3/Cloudflare R2 para fotos de perfil)
- **Email:** Laravel Mail (Mailgun ou Resend)
- **IA:** API OpenAI (GPT-4o-mini) via AIService

### Frontend
- **Framework JS:** Vue 3 (Composition API)
- **Bridge Front-Back:** Inertia.js (sem API REST separada)
- **Linguagem:** TypeScript
- **Estilização:** Tailwind CSS
- **Componentes UI:** shadcn-vue ou PrimeVue
- **Formulários:** VeeValidate + Zod
- **Editor Rich Text:** TipTap
- **Drag and Drop:** VueDraggable (SortableJS)
- **Internacionalização:** vue-i18n
- **Build:** Vite (nativo Laravel)

### Infraestrutura
- **Hosting:** VPS (DigitalOcean ou Hetzner)
- **Deploy:** Laravel Forge ou Ploi
- **CDN:** Cloudflare (grátis)
- **Monitoramento:** Laravel Telescope (dev) + Sentry (produção)
- **CI/CD:** GitHub Actions

---

## Estrutura de Pastas (Laravel)

```
app/
├── Models/
│   ├── User.php
│   ├── Resume.php
│   ├── ResumeVersion.php
│   ├── Template.php
│   ├── Transaction.php
│   ├── CreditPackage.php
│   ├── CreditTransaction.php
│   ├── PricingConfig.php
│   └── Coupon.php
├── Http/Controllers/
│   ├── ResumeController.php
│   ├── PaymentController.php
│   ├── TemplateController.php
│   ├── AIController.php
│   ├── CreditController.php
│   └── DashboardController.php
├── Services/
│   ├── PaymentService.php        # Abstrai Asaas + MercadoPago
│   ├── AsaasGateway.php
│   ├── MercadoPagoGateway.php
│   ├── AIService.php             # Chamadas OpenAI
│   ├── PdfService.php            # Browsershot
│   └── CreditService.php         # Lógica de créditos
├── Jobs/
│   ├── GeneratePdfJob.php
│   └── ProcessPaymentWebhookJob.php
├── Events/
│   └── PaymentConfirmed.php
├── Listeners/
│   └── ReleasePdfDownload.php
├── Filament/
│   ├── Resources/
│   │   ├── UserResource.php
│   │   ├── ResumeResource.php
│   │   ├── TemplateResource.php
│   │   ├── TransactionResource.php
│   │   ├── PricingConfigResource.php
│   │   ├── CreditPackageResource.php
│   │   └── CouponResource.php
│   ├── Widgets/
│   │   ├── StatsOverviewWidget.php
│   │   └── RevenueChartWidget.php
│   └── Pages/
│       └── PaymentSettings.php
resources/
├── js/
│   ├── Pages/
│   │   ├── Dashboard.vue
│   │   ├── Resume/
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Index.vue
│   │   ├── Payment/
│   │   │   └── Checkout.vue
│   │   └── Auth/
│   │       ├── Login.vue
│   │       └── Register.vue
│   ├── Components/
│   │   ├── ResumeForm/
│   │   │   ├── StepPersonalData.vue
│   │   │   ├── StepWorkHistory.vue
│   │   │   ├── StepEducation.vue
│   │   │   ├── StepSkills.vue
│   │   │   ├── StepSummary.vue
│   │   │   ├── StepLinks.vue
│   │   │   └── StepAdditional.vue
│   │   ├── ResumePreview/
│   │   │   └── PreviewPanel.vue
│   │   ├── Templates/
│   │   │   ├── TemplateMinimalist.vue
│   │   │   ├── TemplateModern.vue
│   │   │   ├── TemplateClassic.vue
│   │   │   ├── TemplateCreative.vue
│   │   │   └── TemplateTech.vue
│   │   ├── Payment/
│   │   │   ├── PixQrCode.vue
│   │   │   └── CreditPackages.vue
│   │   └── UI/
│   │       ├── StepProgress.vue
│   │       ├── SkillChip.vue
│   │       └── CharacterCount.vue
│   ├── Composables/
│   │   ├── useResumeForm.ts
│   │   ├── useAutoSave.ts
│   │   ├── useAI.ts
│   │   └── usePayment.ts
│   ├── Types/
│   │   ├── resume.ts
│   │   ├── template.ts
│   │   └── payment.ts
│   └── i18n/
│       └── pt-BR.json
├── views/
│   └── app.blade.php             # Layout Inertia
lang/
├── pt_BR/
│   ├── auth.php
│   ├── validation.php
│   └── messages.php
```

---

## Banco de Dados (Entidades Principais)

### users
- id, name, email, password, avatar_url, provider (google/facebook/email)
- credit_balance (decimal), email_verified_at, timestamps

### resumes
- id, user_id (FK), template_id (FK), title, data (JSON — todos os campos do currículo)
- customization (JSON — cor, fonte, tamanho, espaçamento, layout)
- current_version, is_downloaded (bool), timestamps

### resume_versions
- id, resume_id (FK), version_number, data (JSON snapshot), timestamps

### templates
- id, name, slug, thumbnail_url, component_name (nome do Vue component)
- config (JSON — cores disponíveis, fontes), is_active (bool), sort_order, timestamps

### transactions
- id, user_id (FK), resume_id (FK nullable), type (download/redownload/template_change/credit_purchase)
- amount, gateway (asaas/mercadopago), gateway_transaction_id
- gross_amount, fee_amount, net_amount, status (pending/confirmed/failed)
- pix_qr_code, pix_copy_paste, expires_at, confirmed_at, timestamps

### credit_packages
- id, name, price (decimal), credits (decimal), bonus_percentage
- is_active (bool), sort_order, timestamps

### credit_transactions
- id, user_id (FK), type (purchase/debit), amount, balance_after
- reference_type (transaction/resume), reference_id, timestamps

### pricing_configs
- id, action_slug (first_download/redownload/template_change)
- label, price (decimal), is_active (bool), timestamps

### coupons
- id, code (unique), type (percentage/fixed), value
- applicable_to (all/specific action), max_uses, current_uses
- valid_from, valid_until, is_active (bool), timestamps

---

## Sistema de Pagamento

### Gateways
- **Mercado Pago:** taxa ~0,99% sem taxa fixa → melhor para valores avulsos (< R$ 5)
- **Asaas:** taxa ~R$ 0,49 por Pix → melhor para pacotes de créditos (> R$ 5)
- PaymentService abstrai ambos com interface comum + fallback automático
- Admin configura gateway ativo via Filament (Settings Page)

### Preços Iniciais (editáveis via Filament)
- Primeiro download: R$ 1,99
- Re-download / pós-modificação: R$ 0,99
- Aplicação em novo template: R$ 3,00

### Pacotes de Créditos (editáveis via Filament)
- Básico: R$ 4,90 → R$ 6,00 em créditos (~22% bônus)
- Intermediário: R$ 7,90 → R$ 10,00 em créditos (~27% bônus)
- Avançado: R$ 12,90 → R$ 17,00 em créditos (~32% bônus)

### Fluxo de Pagamento
1. Usuário clica "Baixar PDF"
2. PaymentController verifica credit_balance do user
3. Se tem créditos → CreditService debita + despacha GeneratePdfJob
4. Se não tem → exibe modal: "Pagar avulso via Pix" OU "Comprar créditos"
5. PaymentService gera cobrança Pix (QR code + copia-e-cola)
6. Webhook recebido → ProcessPaymentWebhookJob valida assinatura HMAC
7. Evento PaymentConfirmed → Listener libera download automaticamente

---

## Inteligência Artificial (AIService)

- API: OpenAI GPT-4o-mini (~R$ 0,001 por geração)
- Chamadas sempre server-side (protege API key)

### Funcionalidades:
1. **Geração de Resumo Profissional** — baseado em cargo, experiências, habilidades → 400-600 chars
2. **Descrição de Experiências** — botão "Obter ajuda para escrever" → bullet points
3. **Sugestão de Habilidades** — chips clicáveis baseados no cargo
4. **Wizard de Onboarding** — fluxo guiado com opção de entrada por voz (Web Speech API)
5. **Adaptação para Vaga (futuro)** — colar descrição da vaga → IA adapta currículo

---

## Sistema de Templates

- Cada template é um componente Vue que recebe `resumeData` como props (JSON)
- Templates definem apenas layout visual; dados são independentes
- Para PDF: Browsershot renderiza o componente como HTML → converte para PDF
- Personalização: cores (6-8 por template), fonte, tamanho, espaçamento, layout (A4/Carta)
- Migração entre templates: carrega dados automaticamente → confirmação → cobra R$ 3,00

### Templates MVP (5-10):
- Minimalista, Moderno (sidebar), Clássico/corporativo, Criativo (header), Técnico/dev

---

## Painel Admin (Filament v3)

### Dashboard (Widgets)
- StatsOverviewWidget: usuários ativos, currículos hoje, receita dia/mês
- ChartWidget: tendências (usuários, receita, downloads)
- Templates mais populares, taxa de conversão do funil

### Resources
- UserResource, ResumeResource, TemplateResource, TransactionResource
- PricingConfigResource, CreditPackageResource, CouponResource

### Settings Pages
- PaymentSettings: gateway ativo, API keys (criptografadas), status integrações
- GeneralSettings: email, termos de serviço, política de privacidade
- SupportSettings: tickets (construído, desativado inicialmente)

---

## Internacionalização (i18n)

- Arquitetura multi-idiomas desde o dia 1, lançamento apenas em PT-BR
- Backend: Laravel lang files (`/lang/pt_BR/`), nunca strings hardcoded
- Frontend: vue-i18n com JSON (`/resources/js/i18n/pt-BR.json`)
- Rotas preparadas para prefixo de locale futuro (`/pt-br/`, `/en/`)
- Formatos de data/moeda via locale (Carbon + Intl)

---

## Segurança

- HTTPS obrigatório (Cloudflare)
- CSRF protection (nativo Laravel)
- SQL injection prevention (Eloquent parametriza queries)
- XSS protection (Blade/Vue escapam output)
- Rate limiting (Laravel RateLimiter + Redis)
- Webhooks validados por assinatura HMAC
- Dados sensíveis em `.env`
- LGPD: consentimento cookies, política privacidade, direito ao esquecimento
- Filament com guard separado (admin ≠ usuário)

---

## Performance

- Auto-save com debounce (3s inatividade via Inertia)
- Preview renderizado no client (Vue), PDF no server (Browsershot)
- Geração de PDF via Queue (não bloqueia request)
- Cache de templates e config via Redis
- Assets otimizados via Vite
- Lazy loading de componentes Vue pesados

---

## Convenções de Código

### PHP/Laravel
- PSR-12 para formatação
- Models sempre com `$fillable` explícito
- Form Requests para validação (nunca validar no controller)
- Services para lógica de negócio complexa
- Jobs para operações pesadas (PDF, webhooks)
- Events/Listeners para desacoplamento
- Testes com PHPUnit (Feature + Unit)

### Vue/TypeScript
- Composition API com `<script setup lang="ts">`
- Composables em `/Composables/` para lógica reutilizável
- Types em `/Types/` para interfaces TypeScript
- Componentes nomeados em PascalCase
- Props tipadas com TypeScript
- Emits declarados explicitamente

### Git
- Conventional Commits: `feat:`, `fix:`, `refactor:`, `docs:`, `chore:`
- Branch naming: `feature/nome`, `fix/nome`, `hotfix/nome`
- PR obrigatório para main/production

---

## Comandos Úteis

```bash
# Desenvolvimento
php artisan serve                    # Iniciar servidor Laravel
npm run dev                          # Vite dev server (hot reload)
php artisan queue:work               # Processar filas (PDF, webhooks)

# Banco de dados
php artisan migrate                  # Rodar migrations
php artisan migrate:fresh --seed     # Reset + seed
php artisan make:model Nome -mfs     # Model + migration + factory + seeder

# Filament
php artisan make:filament-resource Nome  # Criar resource do admin
php artisan make:filament-widget Nome    # Criar widget do dashboard

# Testes
php artisan test                     # Rodar todos os testes
php artisan test --filter=NomeTest   # Rodar teste específico

# Cache
php artisan config:cache             # Cache de configuração
php artisan route:cache              # Cache de rotas
php artisan view:cache               # Cache de views

# Deploy
php artisan optimize                 # Otimizar para produção
```

---

## Variáveis de Ambiente Importantes (.env)

```env
# Banco
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=curriculos_saas
DB_USERNAME=
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1

# Pagamento
ASAAS_API_KEY=
ASAAS_WEBHOOK_SECRET=
MERCADOPAGO_ACCESS_TOKEN=
MERCADOPAGO_WEBHOOK_SECRET=
ACTIVE_PAYMENT_GATEWAY=mercadopago  # mercadopago | asaas | both

# OpenAI
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini

# Storage
FILESYSTEM_DISK=s3  # ou r2
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=

# Socialite
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=

# Email
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
```
