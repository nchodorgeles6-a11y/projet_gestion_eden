<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirm  = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const strength = (pwd) => {
    if (!pwd) return 0;
    let s = 0;
    if (pwd.length >= 8)           s++;
    if (/[A-Z]/.test(pwd))         s++;
    if (/[0-9]/.test(pwd))         s++;
    if (/[^A-Za-z0-9]/.test(pwd))  s++;
    return s;
};

const strengthLabel = (s) => ['', 'Faible', 'Moyen', 'Fort', 'Très fort'][s] ?? '';
const strengthColor = (s) => ['', 'bg-rose-500', 'bg-amber-500', 'bg-blue-500', 'bg-emerald-500'][s] ?? '';
const strengthText  = (s) => ['', 'text-rose-600', 'text-amber-600', 'text-blue-600', 'text-emerald-600'][s] ?? '';
</script>

<template>
    <Head title="Nouveau mot de passe | EDEN Corpor@te" />

    <GuestLayout>

        <!-- ── Slot hero ──────────────────────────────────────────────────── -->
        <template #hero>
            <div class="inline-flex items-center self-start gap-2 px-3.5 py-1.5 mb-6
                        rounded-full bg-white/10 border border-white/20 backdrop-blur-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-bold tracking-widest text-white/90 uppercase">
                    Étape 2 / 2 · Finalisation
                </span>
            </div>

            <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-4 tracking-tight">
                Nouveau<br />
                <span class="text-white/70 font-light">mot de passe.</span>
            </h1>

            <p class="text-base text-white/75 leading-relaxed max-w-md mb-8">
                Choisissez un mot de passe robuste pour sécuriser votre compte EdenCorporate.
            </p>

            <div class="space-y-2.5 max-w-sm">
                <p class="text-xs font-bold text-white/60 uppercase tracking-widest mb-3">Conseils de sécurité</p>
                <div v-for="(tip, i) in [
                    { icon: '🔢', text: 'Au moins 8 caractères' },
                    { icon: '🔠', text: 'Une majuscule et un chiffre' },
                    { icon: '🔣', text: 'Un caractère spécial (!@#$…)' },
                    { icon: '🔒', text: 'Différent de vos anciens mots de passe' },
                ]" :key="i"
                    class="flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-xl px-4 py-2.5">
                    <span class="text-base">{{ tip.icon }}</span>
                    <p class="text-sm text-white/80 font-medium">{{ tip.text }}</p>
                </div>
            </div>
        </template>

        <!-- ── Slot default : formulaire ──────────────────────────────────── -->
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#760078] to-[#7677B7] flex items-center justify-center shadow-lg shadow-purple-900/20">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0
                           01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622
                           5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>

        <div class="mb-7 text-center">
            <h2 class="text-2xl font-extrabold text-[#000080] tracking-tight mb-1">Nouveau mot de passe</h2>
            <p class="text-sm text-slate-500 leading-relaxed">
                Choisissez un mot de passe sécurisé pour votre compte.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 p-8">
            <form @submit.prevent="submit" class="space-y-5">

                <!-- Email readonly -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 tracking-wide">Adresse e-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0
                                       002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <input type="email" :value="form.email" readonly
                            class="block w-full pl-10 pr-4 py-2.5 bg-slate-100 border border-slate-200
                                   rounded-xl text-slate-500 text-sm cursor-not-allowed" />
                    </div>
                    <InputError :message="form.errors.email" class="text-xs text-rose-500 font-medium" />
                </div>

                <!-- Nouveau mot de passe -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-700 tracking-wide">
                        Nouveau mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0
                                       00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input id="password" :type="showPassword ? 'text' : 'password'"
                            v-model="form.password" required autofocus
                            placeholder="••••••••" autocomplete="new-password"
                            class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200
                                   rounded-xl text-slate-900 placeholder-slate-400 text-sm
                                   focus:outline-none focus:border-[#760078] focus:ring-2
                                   focus:ring-[#760078]/20 transition-all" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Indicateur de force -->
                    <div v-if="form.password" class="mt-2 space-y-1.5">
                        <div class="flex gap-1">
                            <div v-for="n in 4" :key="n"
                                class="h-1 flex-1 rounded-full transition-all duration-300"
                                :class="n <= strength(form.password) ? strengthColor(strength(form.password)) : 'bg-slate-200'">
                            </div>
                        </div>
                        <p class="text-[10px] font-semibold" :class="strengthText(strength(form.password))">
                            {{ strengthLabel(strength(form.password)) }}
                        </p>
                    </div>

                    <InputError :message="form.errors.password" class="text-xs text-rose-500 font-medium" />
                </div>

                <!-- Confirmation -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 tracking-wide">
                        Confirmer le mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'"
                            v-model="form.password_confirmation" required
                            placeholder="••••••••" autocomplete="new-password"
                            class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200
                                   rounded-xl text-slate-900 placeholder-slate-400 text-sm
                                   focus:outline-none focus:border-[#760078] focus:ring-2
                                   focus:ring-[#760078]/20 transition-all"
                            :class="form.password_confirmation && form.password !== form.password_confirmation
                                ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-200'
                                : form.password_confirmation && form.password === form.password_confirmation
                                ? 'border-emerald-300 focus:border-emerald-400 focus:ring-emerald-200'
                                : ''" />
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg v-if="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                        <span v-if="form.password_confirmation && form.password === form.password_confirmation"
                            class="absolute inset-y-0 right-8 flex items-center text-emerald-500 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </div>
                    <InputError :message="form.errors.password_confirmation" class="text-xs text-rose-500 font-medium" />
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
                        {{ form.processing ? 'Enregistrement…' : 'Réinitialiser le mot de passe' }}
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
