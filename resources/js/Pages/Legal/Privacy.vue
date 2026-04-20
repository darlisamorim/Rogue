<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Política de Privacidade" />

    <div class="min-h-screen bg-gray-50">
        <!-- Nav simples -->
        <header class="bg-white border-b border-gray-200">
            <div class="mx-auto max-w-3xl px-4 py-4 flex items-center justify-between">
                <Link :href="route('welcome')" class="text-lg font-bold text-gray-900">Rogue</Link>
                <div class="flex gap-4 text-sm text-gray-500">
                    <Link :href="route('legal.terms')" class="hover:text-gray-800">Termos de Uso</Link>
                    <Link :href="route('login')" class="hover:text-gray-800">Entrar</Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Política de Privacidade</h1>
            <p class="text-sm text-gray-500 mb-10">Última atualização: abril de 2026</p>

            <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Quem somos</h2>
                    <p>O Rogue é uma plataforma online de criação de currículos profissionais. Ao usar nosso serviço, você confia a nós seus dados pessoais. Esta política explica quais dados coletamos, como os usamos e quais são seus direitos.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Dados que coletamos</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li><strong>Dados de cadastro:</strong> nome, e-mail e senha (armazenada criptografada).</li>
                        <li><strong>Dados do currículo:</strong> informações profissionais que você preenche voluntariamente.</li>
                        <li><strong>Dados de pagamento:</strong> CPF do pagador e dados da transação Pix. Não armazenamos dados de cartão.</li>
                        <li><strong>Dados de uso:</strong> logs de acesso, endereço IP e tipo de dispositivo para segurança e diagnóstico.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Como usamos seus dados</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Prestar e melhorar o serviço de criação de currículos.</li>
                        <li>Processar pagamentos e emitir comprovantes.</li>
                        <li>Enviar comunicações sobre sua conta (confirmações, alertas de segurança).</li>
                        <li>Cumprir obrigações legais e prevenir fraudes.</li>
                    </ul>
                    <p class="mt-3">Não vendemos seus dados a terceiros. Não enviamos e-mails de marketing sem seu consentimento explícito.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Compartilhamento de dados</h2>
                    <p>Seus dados podem ser compartilhados apenas com:</p>
                    <ul class="list-disc list-inside space-y-2 mt-2">
                        <li><strong>Gateways de pagamento</strong> (Mercado Pago / Asaas) — somente os dados necessários para processar a cobrança Pix.</li>
                        <li><strong>Infraestrutura de hospedagem</strong> — servidores onde o sistema opera.</li>
                        <li><strong>Autoridades legais</strong> — quando exigido por lei.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Seus direitos (LGPD)</h2>
                    <p>Conforme a Lei Geral de Proteção de Dados (Lei nº 13.709/2018), você tem direito a:</p>
                    <ul class="list-disc list-inside space-y-2 mt-2">
                        <li>Acessar os dados que temos sobre você.</li>
                        <li>Corrigir dados incompletos ou incorretos.</li>
                        <li>Solicitar a exclusão de seus dados (direito ao esquecimento).</li>
                        <li>Revogar consentimento a qualquer momento.</li>
                        <li>Portabilidade dos seus dados.</li>
                    </ul>
                    <p class="mt-3">Para exercer qualquer desses direitos, entre em contato pelo e-mail indicado abaixo.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Retenção de dados</h2>
                    <p>Mantemos seus dados enquanto sua conta estiver ativa. Após exclusão da conta, os dados são removidos em até 30 dias, exceto onde a lei exige retenção por período maior (ex.: dados fiscais por 5 anos).</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Cookies</h2>
                    <p>Usamos cookies estritamente necessários para o funcionamento da plataforma (autenticação e segurança). Não utilizamos cookies de rastreamento de terceiros para publicidade.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Contato</h2>
                    <p>Dúvidas, solicitações ou reclamações sobre privacidade: <strong>privacidade@rogue.com.br</strong></p>
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
