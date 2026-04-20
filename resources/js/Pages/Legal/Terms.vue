<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Termos de Uso" />

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white border-b border-gray-200">
            <div class="mx-auto max-w-3xl px-4 py-4 flex items-center justify-between">
                <Link :href="route('welcome')" class="text-lg font-bold text-gray-900">Rogue</Link>
                <div class="flex gap-4 text-sm text-gray-500">
                    <Link :href="route('legal.privacy')" class="hover:text-gray-800">Privacidade</Link>
                    <Link :href="route('login')" class="hover:text-gray-800">Entrar</Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Termos de Uso</h1>
            <p class="text-sm text-gray-500 mb-10">Última atualização: abril de 2026</p>

            <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Aceitação dos termos</h2>
                    <p>Ao criar uma conta ou utilizar o Rogue, você concorda com estes Termos de Uso. Se não concordar, não utilize o serviço.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Descrição do serviço</h2>
                    <p>O Rogue é uma plataforma SaaS que permite criar, personalizar e baixar currículos profissionais em PDF. O serviço funciona sob modelo de micropagamento: você paga apenas ao baixar o currículo em PDF, sem assinatura mensal obrigatória.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Cadastro e conta</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Você deve fornecer informações verdadeiras ao criar sua conta.</li>
                        <li>É responsável por manter a segurança de sua senha.</li>
                        <li>Uma conta por pessoa. Contas duplicadas podem ser removidas.</li>
                        <li>Menores de 18 anos devem ter autorização dos responsáveis.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Pagamentos</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Pagamentos são processados via Pix por gateways de terceiros (Mercado Pago / Asaas).</li>
                        <li>Os valores cobrados são os exibidos no momento da compra.</li>
                        <li>Créditos adquiridos não expiram e não são reembolsáveis, exceto por erro técnico comprovado.</li>
                        <li>Downloads individuais também não são reembolsáveis após a geração do PDF.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Uso aceitável</h2>
                    <p>Você concorda em não:</p>
                    <ul class="list-disc list-inside space-y-2 mt-2">
                        <li>Usar o serviço para fins ilegais ou fraudulentos.</li>
                        <li>Criar currículos com informações falsas de terceiros sem autorização.</li>
                        <li>Tentar acessar contas de outros usuários.</li>
                        <li>Realizar engenharia reversa, scraping ou automação não autorizada.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Propriedade intelectual</h2>
                    <p>Os templates, design e código do Rogue são de nossa propriedade. O conteúdo dos currículos que você cria é seu — você mantém todos os direitos sobre as informações que preenche.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Disponibilidade do serviço</h2>
                    <p>Nos esforçamos para manter o serviço disponível 24/7, mas não garantimos disponibilidade ininterrupta. Manutenções programadas serão comunicadas com antecedência quando possível. Não nos responsabilizamos por perdas decorrentes de indisponibilidade do serviço.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Encerramento de conta</h2>
                    <p>Você pode encerrar sua conta a qualquer momento nas configurações de perfil. Reservamo-nos o direito de suspender contas que violem estes termos, com ou sem aviso prévio dependendo da gravidade da infração.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Alterações nos termos</h2>
                    <p>Podemos atualizar estes termos periodicamente. Notificaremos por e-mail em caso de mudanças relevantes. O uso continuado do serviço após a notificação implica aceitação dos novos termos.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Contato</h2>
                    <p>Dúvidas sobre estes termos: <strong>contato@rogue.com.br</strong></p>
                </section>

            </div>
        </main>

        <footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-400">
            <div class="flex items-center justify-center gap-4">
                <Link :href="route('legal.privacy')" class="hover:text-gray-600">Privacidade</Link>
                <Link :href="route('legal.terms')" class="hover:text-gray-600">Termos de uso</Link>
            </div>
        </footer>
    </div>
</template>
