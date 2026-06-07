<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion | EDEN Corpor@te" />

    <GuestLayout>

        <!-- ── Slot hero : contenu spécifique à la page de connexion ─────── -->
        <template #hero>
            <div class="inline-flex items-center self-start gap-2 px-3.5 py-1.5 mb-6
                        rounded-full bg-white/10 border border-white/20 backdrop-blur-sm">
                <span class="h-2 w-2 rounded-full bg-[#CDA071] animate-pulse"></span>
                <span class="text-xs font-bold tracking-widest text-white/90 uppercase">
                    10 Ans d'Excellence · 2016 – 2026
                </span>
            </div>

            <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-4 tracking-tight">
                Bienvenue sur votre<br />
                <span class="text-white/70 font-light">portail RH.</span>
            </h1>

            <p class="text-base text-white/75 leading-relaxed max-w-md mb-8">
                Eden Corpor@te centralise la gestion de vos collaborateurs,
                de vos paies et de vos activités en un seul espace sécurisé.
            </p>

            <div class="grid grid-cols-3 gap-4 max-w-sm">
                <div class="bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl p-4 text-center">
                    <p class="text-2xl font-extrabold text-white">10+</p>
                    <p class="text-xs text-white/60 mt-1 font-medium">Années</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl p-4 text-center">
                    <p class="text-2xl font-extrabold text-white">360°</p>
                    <p class="text-xs text-white/60 mt-1 font-medium">Gestion RH</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl p-4 text-center">
                    <p class="text-2xl font-extrabold text-white">100%</p>
                    <p class="text-xs text-white/60 mt-1 font-medium">Sécurisé</p>
                </div>
            </div>
        </template>

        <!-- ── Slot default : formulaire de connexion ─────────────────────── -->
        <div class="mb-7 text-center">
            <h2 class="text-2xl font-extrabold text-[#000080] tracking-tight mb-1">Connexion</h2>
            <p class="text-sm text-slate-500">Accédez à votre espace RH</p>
        </div>

        <div v-if="status"
            class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-100
                   text-sm font-medium text-emerald-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586
                       7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ status }}
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 p-8">
            <form @submit.prevent="submit" class="space-y-5">

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-slate-700 tracking-wide">
                        Adresse e-mail
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

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-semibold text-slate-700 tracking-wide">
                            Mot de passe
                        </label>
                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-xs font-semibold text-[#760078] hover:text-[#5a005c]
                                   hover:underline transition-colors">
                            Mot de passe oublié ?
                        </Link>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0
                                       00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input id="password" type="password" v-model="form.password" required
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200
                                   rounded-xl text-slate-900 placeholder-slate-400 text-sm
                                   focus:outline-none focus:border-[#760078] focus:ring-2
                                   focus:ring-[#760078]/20 transition-all" />
                    </div>
                    <InputError :message="form.errors.password" class="text-xs text-rose-500 font-medium" />
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input id="remember" type="checkbox" v-model="form.remember"
                        class="w-4 h-4 rounded border-slate-300 text-[#760078]
                               focus:ring-[#760078]/30 cursor-pointer" />
                    <label for="remember"
                        class="text-xs font-medium text-slate-600 cursor-pointer select-none">
                        Rester connecté
                    </label>
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
                        {{ form.processing ? 'Connexion en cours…' : 'Se connecter' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-5 text-center space-y-1">
            <p class="text-xs text-slate-400">Système SIRH · Eden Corpor@te</p>
            <p class="text-xs text-slate-400">v4.10.0</p>
        </div>

    </GuestLayout>
</template>
