<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';

const { dark } = useTheme();
const props = defineProps({
    user:     Object,
    bulletins: Array,
    career:   { type: Array, default: () => [] },
});

const activeTab = ref('bulletins');

const affectationActuelle = computed(() => props.career.find(c => c.actif) ?? props.career.at(-1) ?? null);

const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(n) + ' FCFA' : '—';
const pct = (a, b) => b ? Math.abs(Math.round((a - b) / b * 100)) : 0;

const statutLabel = { brouillon: 'Brouillon', valide: 'Validé', paye: 'Payé' };
const statutColor = {
    brouillon: 'bg-slate-100 text-slate-600',
    valide:    'bg-blue-100 text-blue-700',
    paye:      'bg-emerald-100 text-emerald-700',
};

const contratColor = {
    employe:     'bg-[#760078]/10 text-[#760078]',
    prestataire: 'bg-[#7677B7]/10 text-[#7677B7]',
};

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';

const tabs = [
    { key: 'bulletins',      label: 'Bulletins de paie' },
    { key: 'conges',         label: 'Congés' },
    { key: 'paiements',      label: 'Paiements' },
    { key: 'parcours',       label: 'Parcours carrière' },
    { key: 'administration', label: 'Administration' },
];

const tabCount = (key) => {
    if (key === 'bulletins') return props.bulletins.length;
    if (key === 'conges')    return props.user.conges?.length ?? 0;
    if (key === 'paiements') return props.user.paiements?.length ?? 0;
    if (key === 'parcours')  return props.career.length;
    return null;
};

const openDoc = (path) => window.open(path, '_blank');

// Salary evolution: max increase across all career steps
const salaireActuel = computed(() => props.career.at(-1)?.salaire ?? props.user.salaire_base ?? 0);
const salairePremier = computed(() => props.career[0]?.salaire ?? props.user.salaire_base ?? 0);
const evolutionTotal = computed(() =>
    salairePremier.value ? Math.round((salaireActuel.value - salairePremier.value) / salairePremier.value * 100) : 0
);

const totalPrimesUser = computed(() => {
    const u = props.user;
    return (u.prime_transport ?? 0) + (u.prime_logement ?? 0) + (u.prime_fonction ?? 0)
         + (u.prime_rendement ?? 0) + (u.prime_panier ?? 0) + (u.bonus_annuel ?? 0);
});
</script>

<template>
    <Head :title="`${user.nom} ${user.prenom}`" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-5">

            <!-- ── En-tête fiche employé ── -->
            <div class="rounded-2xl border p-6 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <!-- Avatar -->
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white shrink-0 bg-gradient-to-br from-[#760078] to-[#7677B7]">
                            {{ user.prenom?.charAt(0) }}{{ user.nom?.charAt(0) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-lg font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                                    {{ user.nom }} {{ user.prenom }}
                                </h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="contratColor[user.type_contrat]">
                                    {{ user.type_contrat === 'employe' ? 'Employé' : 'Prestataire' }}
                                </span>
                            </div>
                            <p class="text-sm mt-0.5" :class="sL(dark)">
                                {{ affectationActuelle?.poste ?? 'Aucun poste' }}
                                <span v-if="affectationActuelle?.departement" class="mx-1 opacity-40">·</span>
                                {{ affectationActuelle?.departement ?? '' }}
                            </p>
                            <p class="text-xs mt-1" :class="sL(dark)">{{ user.email }} · {{ user.telephone }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <Link :href="`/bulletins-paie/create?user_id=${user.id}`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Nouveau bulletin
                        </Link>
                        <Link :href="`/users/${user.id}/edit`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                            :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Modifier
                        </Link>
                        <Link href="/users"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                            :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Retour
                        </Link>
                    </div>
                </div>

                <!-- Cards info rapide -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                    <div class="rounded-xl p-3 border" :class="dark ? 'bg-[#0d1117] border-[#30363d]' : 'bg-slate-50 border-slate-200'">
                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Salaire de base</p>
                        <p class="text-base font-extrabold text-[#760078]">{{ fmt(user.salaire_base) }}</p>
                    </div>
                    <div class="rounded-xl p-3 border" :class="dark ? 'bg-[#0d1117] border-[#30363d]' : 'bg-slate-50 border-slate-200'">
                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Mode de paiement</p>
                        <p class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">
                            {{ user.mode_paiement === 'fixe' ? 'Salaire fixe' : 'Par prestation' }}
                        </p>
                    </div>
                    <div class="rounded-xl p-3 border" :class="dark ? 'bg-[#0d1117] border-[#30363d]' : 'bg-slate-50 border-slate-200'">
                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">CNPS</p>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full" :class="user.cnps ? 'bg-blue-500' : 'bg-slate-300'"></div>
                            <p class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ user.cnps ? 'Affilié' : 'Non affilié' }}</p>
                        </div>
                    </div>
                    <div class="rounded-xl p-3 border" :class="dark ? 'bg-[#0d1117] border-[#30363d]' : 'bg-slate-50 border-slate-200'">
                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Bulletins émis</p>
                        <p class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">{{ bulletins.length }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Rémunération détaillée ── -->
            <div v-if="totalPrimesUser > 0"
                class="rounded-2xl border transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="px-5 py-3 border-b" :class="div(dark)">
                    <p class="text-[10px] font-bold uppercase tracking-widest" :class="sL(dark)">Primes contractuelles</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <div v-for="(val, label) in { 'Transport': user.prime_transport, 'Logement': user.prime_logement, 'Fonction': user.prime_fonction, 'Rendement': user.prime_rendement, 'Panier': user.prime_panier, 'Bonus annuel': user.bonus_annuel }"
                        :key="label" v-show="val > 0">
                        <p class="text-[10px] font-semibold mb-0.5" :class="sL(dark)">{{ label }}</p>
                        <p class="text-sm font-bold text-[#760078]">{{ fmt(val) }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Onglets ── -->
            <div class="rounded-2xl border overflow-hidden transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <!-- Tabs nav -->
                <div class="flex border-b overflow-x-auto" :class="div(dark)">
                    <button v-for="tab in tabs" :key="tab.key"
                        @click="activeTab = tab.key"
                        class="px-5 py-3 text-xs font-bold whitespace-nowrap transition-all border-b-2"
                        :class="activeTab === tab.key
                            ? 'border-[#760078] text-[#760078]'
                            : 'border-transparent ' + sL(dark) + ' hover:text-[#760078]'">
                        {{ tab.label }}
                        <span v-if="tabCount(tab.key) !== null" class="ml-1.5 px-1.5 py-0.5 rounded-full text-[9px]"
                            :class="activeTab === tab.key ? 'bg-[#760078]/10 text-[#760078]' : dark ? 'bg-[#21262d] text-slate-500' : 'bg-slate-100 text-slate-500'">
                            {{ tabCount(tab.key) }}
                        </span>
                    </button>
                </div>

                <!-- Tab : Bulletins de paie -->
                <div v-show="activeTab === 'bulletins'">
                    <div v-if="bulletins.length === 0" class="p-10 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                            <svg class="w-6 h-6" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-sm font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-500'">Aucun bulletin émis</p>
                        <Link :href="`/bulletins-paie/create?user_id=${user.id}`" class="mt-2 inline-block text-xs text-[#760078] font-semibold hover:underline">
                            Créer le premier bulletin
                        </Link>
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Référence</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Période</th>
                                <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Brut</th>
                                <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Net à payer</th>
                                <th class="px-5 py-2.5 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Statut</th>
                                <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="b in bulletins" :key="b.id" class="border-t transition-colors"
                                :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-100 hover:bg-slate-50'">
                                <td class="px-5 py-2.5">
                                    <span class="font-mono text-xs font-semibold" :class="dark ? 'text-slate-300' : 'text-slate-600'">{{ b.reference }}</span>
                                </td>
                                <td class="px-5 py-2.5 text-xs font-medium" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                    {{ b.mois }} {{ b.annee }}
                                </td>
                                <td class="px-5 py-2.5 text-right text-xs font-semibold" :class="dark ? 'text-slate-300' : 'text-slate-600'">
                                    {{ fmt(b.salaire_brut) }}
                                </td>
                                <td class="px-5 py-2.5 text-right text-xs font-extrabold text-[#760078]">
                                    {{ fmt(b.net_a_payer) }}
                                </td>
                                <td class="px-5 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="statutColor[b.statut]">
                                        {{ statutLabel[b.statut] }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-right">
                                    <Link :href="`/bulletins-paie/${b.id}`"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all"
                                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                                        Voir
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab : Congés -->
                <div v-show="activeTab === 'conges'">
                    <div v-if="!user.conges?.length" class="p-10 text-center">
                        <p class="text-sm font-semibold" :class="sL(dark)">Aucun congé enregistré</p>
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Date début</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Date fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in user.conges" :key="c.id" class="border-t" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                                <td class="px-5 py-2.5 text-xs" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ c.date_debut }}</td>
                                <td class="px-5 py-2.5 text-xs" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ c.date_fin ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab : Paiements -->
                <div v-show="activeTab === 'paiements'">
                    <div v-if="!user.paiements?.length" class="p-10 text-center">
                        <p class="text-sm font-semibold" :class="sL(dark)">Aucun paiement enregistré</p>
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Référence</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Période</th>
                                <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in user.paiements" :key="p.id" class="border-t" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                                <td class="px-5 py-2.5 font-mono text-xs" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ p.reference }}</td>
                                <td class="px-5 py-2.5 text-xs" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ p.mois }} {{ p.annee }}</td>
                                <td class="px-5 py-2.5 text-right text-xs font-bold text-[#760078]">{{ fmt(p.montant) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab : Parcours Carrière -->
                <div v-show="activeTab === 'parcours'" class="p-5">

                    <!-- Résumé évolution salariale globale -->
                    <div v-if="career.length > 1 && salairePremier > 0"
                        class="mb-5 rounded-xl border px-5 py-4 flex items-center gap-6"
                        :class="dark ? 'bg-[#0d1117] border-[#30363d]' : 'bg-gradient-to-r from-[#760078]/5 to-[#7677B7]/5 border-[#760078]/15'">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Salaire initial</p>
                            <p class="text-sm font-bold" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ fmt(salairePremier) }}</p>
                        </div>
                        <svg class="w-4 h-4 shrink-0" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Salaire actuel</p>
                            <p class="text-sm font-extrabold text-[#760078]">{{ fmt(salaireActuel) }}</p>
                        </div>
                        <div class="ml-auto">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-extrabold"
                                :class="evolutionTotal >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        :d="evolutionTotal >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"/>
                                </svg>
                                {{ evolutionTotal >= 0 ? '+' : '' }}{{ evolutionTotal }}% sur la carrière
                            </span>
                        </div>
                    </div>

                    <!-- Timeline vide -->
                    <div v-if="career.length === 0" class="py-10 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                            <svg class="w-6 h-6" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-500'">Aucune affectation enregistrée</p>
                        <Link href="/affectations/create" class="mt-2 inline-block text-xs text-[#760078] font-semibold hover:underline">
                            Ajouter la première affectation
                        </Link>
                    </div>

                    <!-- Timeline -->
                    <div v-else class="relative">
                        <!-- Ligne verticale -->
                        <div class="absolute left-4 top-4 bottom-4 w-0.5"
                            :class="dark ? 'bg-[#30363d]' : 'bg-slate-200'"></div>

                        <div v-for="(step, index) in career" :key="step.id" class="relative flex gap-5 mb-6 last:mb-0">
                            <!-- Dot -->
                            <div class="relative z-10 shrink-0 mt-0.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all"
                                    :class="step.actif
                                        ? 'bg-[#760078] border-[#760078] text-white shadow-lg shadow-[#760078]/30'
                                        : dark
                                            ? 'bg-[#21262d] border-[#30363d] text-slate-400'
                                            : 'bg-white border-slate-300 text-slate-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Content card -->
                            <div class="flex-1 rounded-xl border p-4 transition-colors"
                                :class="step.actif
                                    ? dark ? 'bg-[#1a0d1a] border-[#760078]/40' : 'bg-[#760078]/5 border-[#760078]/20'
                                    : dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-slate-50 border-slate-200'">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                                                {{ step.poste }}
                                            </span>
                                            <span v-if="step.actif"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#760078] text-white">
                                                Poste actuel
                                            </span>
                                        </div>
                                        <p class="text-xs mt-0.5 font-medium" :class="sL(dark)">
                                            {{ step.departement }}
                                        </p>
                                    </div>

                                    <!-- Dates + durée -->
                                    <div class="text-right shrink-0">
                                        <p class="text-xs font-semibold" :class="dark ? 'text-slate-300' : 'text-slate-600'">
                                            {{ step.date_debut }} → {{ step.date_fin ?? 'Présent' }}
                                        </p>
                                        <p class="text-[10px] mt-0.5 font-medium" :class="sL(dark)">{{ step.duree }}</p>
                                    </div>
                                </div>

                                <!-- Salaire + évolution vs étape précédente -->
                                <div v-if="step.salaire > 0" class="flex items-center gap-3 mt-3 flex-wrap">
                                    <span class="text-sm font-extrabold text-[#760078]">{{ fmt(step.salaire) }}</span>

                                    <!-- Badge évolution salariale -->
                                    <span v-if="index > 0 && career[index - 1].salaire > 0 && step.salaire !== career[index - 1].salaire"
                                        class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md text-[10px] font-bold"
                                        :class="step.salaire > career[index - 1].salaire
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-red-100 text-red-700'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                :d="step.salaire > career[index - 1].salaire
                                                    ? 'M5 10l7-7m0 0l7 7m-7-7v18'
                                                    : 'M19 14l-7 7m0 0l-7-7m7 7V3'"/>
                                        </svg>
                                        {{ step.salaire > career[index - 1].salaire ? '+' : '-' }}{{ pct(step.salaire, career[index - 1].salaire) }}%
                                    </span>

                                    <!-- Primes détail -->
                                    <template v-if="step.primes && Object.values(step.primes).some(v => v > 0)">
                                        <span class="text-[10px]" :class="sL(dark)">+</span>
                                        <span class="text-xs font-medium" :class="sL(dark)">
                                            {{ fmt(Object.values(step.primes).reduce((s, v) => s + (v || 0), 0)) }} primes
                                        </span>
                                    </template>
                                </div>

                                <!-- Motif du changement -->
                                <div v-if="step.motif" class="mt-3">
                                    <p class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg"
                                        :class="dark ? 'bg-[#21262d] text-slate-400' : 'bg-white border border-slate-200 text-slate-600'">
                                        <svg class="w-3 h-3 shrink-0 text-[#760078]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        {{ step.motif }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton ajouter une étape -->
                    <div class="mt-5 pt-4 border-t" :class="div(dark)">
                        <Link :href="`/affectations/create`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                            :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Enregistrer une nouvelle affectation
                        </Link>
                    </div>
                </div>

                <!-- Tab : Administration -->
                <div v-show="activeTab === 'administration'" class="p-5 space-y-5">

                    <!-- Documents RH -->
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Documents à générer</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">

                            <!-- Contrat de travail -->
                            <button @click="openDoc(`/users/${user.id}/documents/contrat`)"
                                class="flex items-start gap-3 p-4 rounded-xl border text-left transition-all hover:shadow-md"
                                :class="dark ? 'bg-[#0d1117] border-[#21262d] hover:border-[#760078]/40' : 'bg-white border-slate-200 hover:border-[#760078]/30'">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 bg-[#760078]/10">
                                    <svg class="w-4.5 h-4.5 text-[#760078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Contrat de travail</p>
                                    <p class="text-[10px] mt-0.5" :class="sL(dark)">CDI / Prestation — avec rémunération</p>
                                </div>
                            </button>

                            <!-- Attestation de travail -->
                            <button @click="openDoc(`/users/${user.id}/documents/attestation`)"
                                class="flex items-start gap-3 p-4 rounded-xl border text-left transition-all hover:shadow-md"
                                :class="dark ? 'bg-[#0d1117] border-[#21262d] hover:border-emerald-500/40' : 'bg-white border-slate-200 hover:border-emerald-400/40'">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 bg-emerald-500/10">
                                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Attestation de travail</p>
                                    <p class="text-[10px] mt-0.5" :class="sL(dark)">Certificat officiel d'emploi</p>
                                </div>
                            </button>

                            <!-- Lettre de licenciement -->
                            <button @click="openDoc(`/users/${user.id}/documents/licenciement`)"
                                class="flex items-start gap-3 p-4 rounded-xl border text-left transition-all hover:shadow-md"
                                :class="dark ? 'bg-[#0d1117] border-[#21262d] hover:border-rose-500/40' : 'bg-white border-slate-200 hover:border-rose-400/40'">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 bg-rose-500/10">
                                    <svg class="w-4.5 h-4.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Lettre de licenciement</p>
                                    <p class="text-[10px] mt-0.5" :class="sL(dark)">Notification de fin de contrat</p>
                                </div>
                            </button>

                        </div>
                    </div>

                    <!-- Congés de l'employé avec bouton lettre -->
                    <div v-if="user.conges?.length">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Lettres de congé / permission</p>
                        <div class="rounded-xl border overflow-hidden" :class="dark ? 'border-[#21262d]' : 'border-slate-200'">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Motif</th>
                                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Début</th>
                                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Fin</th>
                                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Statut</th>
                                        <th class="px-4 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Lettre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in user.conges" :key="c.id"
                                        class="border-t transition-colors"
                                        :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-100 hover:bg-slate-50'">
                                        <td class="px-4 py-2.5 font-medium" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                            {{ c.motif?.nom ?? '—' }}
                                        </td>
                                        <td class="px-4 py-2.5" :class="sL(dark)">{{ c.date_debut }}</td>
                                        <td class="px-4 py-2.5" :class="sL(dark)">{{ c.date_fin ?? '—' }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                :class="c.statut === 'approuve' ? 'bg-emerald-100 text-emerald-700' : c.statut === 'refuse' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-700'">
                                                {{ c.statut === 'approuve' ? 'Approuvé' : c.statut === 'refuse' ? 'Refusé' : 'En attente' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <button v-if="c.statut === 'approuve'"
                                                @click="openDoc(`/conges/${c.id}/lettre`)"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-[#760078]/10 text-[#760078] border-[#760078]/20 hover:bg-[#760078]/20">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Imprimer
                                            </button>
                                            <span v-else class="text-[10px]" :class="sL(dark)">—</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-sm" :class="sL(dark)">
                        Aucun congé enregistré pour cet employé.
                    </div>

                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
