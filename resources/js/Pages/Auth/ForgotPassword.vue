<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

defineProps({
    status: { type: String },
});

const form = useForm({ email: '' });

const submit = () => form.post(route('password.email'));
</script>

<template>
    <Head title="Mot de passe oublié | EDEN Corpor@te" />

    <GuestLayout>

        <!-- ── Slot hero ──────────────────────────────────────────────────── -->
        <template #hero>
            <div class="inline-flex items-center self-start gap-2 px-3.5 py-1.5 mb-6
                        rounded-full bg-white/10 border border-white/20 backdrop-blur-sm">
                <span class="h-2 w-2 rounded-full bg-[#CDA071] animate-pulse"></span>
                <span class="text-xs font-bold tracking-widest text-white/90 uppercase">
                    Étape 1 / 2 · Identification
                </span>
            </div>

            <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-4 tracking-tight">
                Récupérez<br />
                <span class="text-white/70 font-light">votre accès.</span>
            </h1>

            <p class="text-base text-white/75 leading-relaxed max-w-md mb-8">
                Renseignez votre adresse e-mail et nous vous enverrons un lien sécurisé
                pour réinitialiser votre mot de passe.
            </p>

            <div class="space-y-3 max-w-sm">
                <div v-for="(step, i) in [
                    { num: '1', text: 'Saisissez votre adresse e-mail professionnelle' },
                    { num: '2', text: 'Recevez le lien de réinitialisation' },
                    { num: '3', text: 'Choisissez un nouveau mot de passe sécurisé' },
                ]" :key="i"
                    class="flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-xl px-4 py-3">
                    <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-extrabold text-white shrink-0">
                        {{ step.num }}
                    </span>
                    <p class="text-sm text-white/80 font-medium">{{ step.text }}</p>
                </div>
            </div>
        </template>

        <!-- ── Slot default : formulaire ──────────────────────────────────── -->
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#760078] to-[#7677B7] flex items-center justify-center shadow-lg shadow-purple-900/20">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0
                           01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
        </div>

        <div class="mb-7 text-center">
            <h2 class="text-2xl font-extrabold text-[#000080] tracking-tight mb-1">Mot de passe oublié ?</h2>
            <p class="text-sm text-slate-500 leading-relaxed">
                Entrez votre e-mail et nous vous enverrons un lien de réinitialisation.
            </p>
        </div>

        <div v-if="status"
            class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-100
                   text-sm font-medium text-emerald-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9
                       10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ status }}
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 p-8">
            <form @submit.prevent="submit" class="space-y-5">

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-slate-700 tracking-wide">
                        Adresse e-mail professionnelle
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0
                                       002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <input id="email" type="email" v-model="form.email" required autofocus
                            placeholder="nom.prenom@edencorporate.com"
                            autocomplete="username"
                            class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200
                                   rounded-xl text-slate-900 placeholder-slate-400 text-sm
                                   focus:outline-none focus:border-[#760078] focus:ring-2
                                   focus:ring-[#760078]/20 transition-all" />
                    </div>
                    <InputError :message="form.errors.email" class="text-xs text-rose-500 font-medium" />
                </div>

                <div class="pt-2">
                    <button type="submit"
                        :disabled="form.processing"
                        :class="{ 'opacity-70 cursor-wait': form.processing }"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 text-sm font-semibold
                               text-white rounded-xl transition-all active:scale-[0.98]
                               bg-gradient-to-r from-[#760078] to-[#7677B7]
                               hover:from-[#5a005c] hover:to-[#5a5b9c]
                               focus:ring-4 focus:ring-[#760078]/20
                               shadow-md shadow-purple-900/15">
                        <svg v-if="form.processing"
                            class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291
                                   A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ form.processing ? 'Envoi en cours…' : 'Envoyer le lien de réinitialisation' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-5 text-center">
            <Link :href="route('login')"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#760078] hover:text-[#5a005c]
                       hover:underline transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la connexion
            </Link>
        </div>

        <div class="mt-4 text-center space-y-1">
            <p class="text-xs text-slate-400">Système SIRH · Eden Corpor@te</p>
            <p class="text-xs text-slate-400">v4.10.0</p>
        </div>

    </GuestLayout>
</template>
