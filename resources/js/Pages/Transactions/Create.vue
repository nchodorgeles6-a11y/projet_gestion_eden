<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed, watch } from 'vue';

const { dark } = useTheme();

const props = defineProps({
    users:        { type: Array, default: () => [] },
    departements: { type: Array, default: () => [] },
});

// ── Motifs prédéfinis ─────────────────────────────────────────────────────────
// Chaque icon est un path SVG (viewBox 0 0 24 24, stroke, fill=none)
const MOTIFS = {
    'dépense': [
        { icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', label: 'Salaires & Rémunérations',  desc: 'Paiement de salaires, primes, indemnités' },
        { icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0L9 7',                                              label: 'Transport & Déplacement',    desc: 'Carburant, taxi, mission terrain' },
        { icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',                                                                                              label: 'Factures & Abonnements',     desc: 'Eau, électricité, internet, téléphone' },
        { icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',                                                                      label: 'Loyer & Charges locatives',  desc: 'Bail, charges communes, entretien locaux' },
        { icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10',                                                                                                                                                              label: 'Fournitures & Matériel',     desc: 'Bureautique, informatique, consommables' },
        { icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z', label: 'Maintenance & Réparations',  desc: 'Entretien équipements, prestataires' },
        { icon: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222',                              label: 'Formation & Développement',  desc: 'Séminaires, certifications, e-learning' },
        { icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',                                    label: 'Prestation externe',         desc: 'Consultants, freelances, sous-traitants' },
        { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',                    label: 'Assurances',                 desc: 'Primes d\'assurance, mutuelles' },
        { icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',                                                                                                                                                  label: 'Impôts & Taxes',             desc: 'Fiscalité, cotisations obligatoires' },
        { icon: 'M3 4a1 1 0 00-1 1v14a1 1 0 001 1h18a1 1 0 001-1V5a1 1 0 00-1-1H3zm0 2h18v2H3V6zm0 4h18v8H3v-8zm4 2v2h2v-2H7zm4 0v2h6v-2h-6z',                                                                                      label: 'Restauration & Réception',   desc: 'Repas d\'affaires, événements clients' },
        { icon: 'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z',                                                                                         label: 'Autre dépense',              desc: 'Dépense non listée ci-dessus' },
    ],
    'revenu': [
        { icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',                                    label: 'Prestation de service',      desc: 'Honoraires, consulting, mission facturable' },
        { icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',                                                                                                                                                                         label: 'Vente de produit',           desc: 'Produits, marchandises, biens' },
        { icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',                                                                                              label: 'Contrat & Marché',           desc: 'Signature de contrat, appel d\'offres gagné' },
        { icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',                                                                                                                                                  label: 'Subvention & Financement',   desc: 'Aide, grant, fonds institutionnels' },
        { icon: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',                                                                                                                                                                           label: 'Remboursement',              desc: 'Remboursement de frais avancés' },
        { icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',                                                                                                                                                                                     label: 'Intérêts & Dividendes',      desc: 'Revenus financiers, placement' },
        { icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',        label: 'Partenariat',                desc: 'Accord commercial, joint-venture' },
        { icon: 'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z',                                                                                         label: 'Autre revenu',               desc: 'Rentrée non listée ci-dessus' },
    ],
};

const motifSelectionne = ref('');
const details          = ref('');
const avecEmploye      = ref(false);

const form = useForm({
    type:             '',
    departement_id:   '',
    user_id:          '',
    montant:          '',
    description:      '',
    date_transaction: new Date().toISOString().substring(0, 10),
});

// Reset motif quand on change de type
watch(() => form.type, () => { motifSelectionne.value = ''; details.value = ''; });

const motifsActuels = computed(() => MOTIFS[form.type] ?? []);
const estAutre      = computed(() => motifSelectionne.value.startsWith('Autre'));

const submit = () => {
    if (!avecEmploye.value) form.user_id = '';
    const desc = motifSelectionne.value + (details.value.trim() ? ' — ' + details.value.trim() : '');
    form.description = desc;
    form.post('/transactions');
};

const fmt = (v) => Number(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' FCFA';

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';
</script>

<template>
    <Head title="Nouvelle Transaction" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-6">

            <!-- En-tête -->
            <div class="flex items-center gap-3">
                <Link href="/transactions"
                    class="text-decoration-none p-2 rounded-xl border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white hover:bg-[#21262d]' : 'border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Nouvelle transaction</h2>
                    <p class="text-[11px]" :class="sL(dark)">Enregistrer une entrée ou une sortie de trésorerie</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">

                <!-- ── Étape 1 : Type ── -->
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-white text-[9px] font-black mr-1.5"
                            :class="form.type ? 'bg-[#760078]' : 'bg-slate-400'">1</span>
                        Type de transaction
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button type="button" @click="form.type = 'revenu'"
                            class="flex items-center gap-3 px-5 py-4 rounded-2xl border-2 text-left transition-all"
                            :class="form.type === 'revenu'
                                ? 'border-emerald-500 bg-emerald-50'
                                : dark ? 'border-[#30363d] hover:border-emerald-500/40' : 'border-slate-200 hover:border-emerald-400/40'">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0 text-lg">↑</div>
                            <div>
                                <p class="text-sm font-bold" :class="form.type === 'revenu' ? 'text-emerald-700' : dark ? 'text-white' : 'text-slate-800'">Entrée</p>
                                <p class="text-[10px]" :class="sL(dark)">Revenu, recette, rentrée de fonds</p>
                            </div>
                        </button>
                        <button type="button" @click="form.type = 'dépense'"
                            class="flex items-center gap-3 px-5 py-4 rounded-2xl border-2 text-left transition-all"
                            :class="form.type === 'dépense'
                                ? 'border-rose-500 bg-rose-50'
                                : dark ? 'border-[#30363d] hover:border-rose-500/40' : 'border-slate-200 hover:border-rose-400/40'">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center shrink-0 text-lg">↓</div>
                            <div>
                                <p class="text-sm font-bold" :class="form.type === 'dépense' ? 'text-rose-700' : dark ? 'text-white' : 'text-slate-800'">Sortie</p>
                                <p class="text-[10px]" :class="sL(dark)">Dépense, charge, sortie de caisse</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- ── Étape 2 : Motif ── -->
                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0">
                <div v-if="form.type">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-white text-[9px] font-black mr-1.5"
                            :class="motifSelectionne ? 'bg-[#760078]' : 'bg-slate-400'">2</span>
                        Motif de {{ form.type === 'revenu' ? "l'entrée" : 'la sortie' }}
                    </p>

                    <div class="grid grid-cols-3 gap-2.5">
                        <button v-for="m in motifsActuels" :key="m.label"
                            type="button"
                            @click="motifSelectionne = m.label; if (!estAutre) details = ''"
                            class="flex items-start gap-2.5 px-3 py-3 rounded-xl border-2 text-left transition-all"
                            :class="motifSelectionne === m.label
                                ? form.type === 'revenu'
                                    ? 'border-emerald-500 bg-emerald-50'
                                    : 'border-rose-500 bg-rose-50'
                                : dark ? 'border-[#30363d] hover:border-[#760078]/40' : 'border-slate-200 hover:border-[#760078]/30'">
                            <span class="shrink-0 mt-0.5 w-4 h-4"
                                :class="motifSelectionne === m.label
                                    ? form.type === 'revenu' ? 'text-emerald-600' : 'text-rose-600'
                                    : dark ? 'text-slate-400' : 'text-slate-500'">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                    <path :d="m.icon"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold leading-tight"
                                    :class="motifSelectionne === m.label
                                        ? form.type === 'revenu' ? 'text-emerald-700' : 'text-rose-700'
                                        : dark ? 'text-slate-200' : 'text-slate-800'">
                                    {{ m.label }}
                                </p>
                                <p class="text-[9px] mt-0.5 leading-tight" :class="sL(dark)">{{ m.desc }}</p>
                            </div>
                        </button>
                    </div>

                    <!-- Champ "Autre" -->
                    <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100">
                        <div v-if="estAutre" class="mt-3">
                            <input v-model="details" type="text" placeholder="Préciser le motif…"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                        </div>
                    </Transition>
                </div>
                </Transition>

                <!-- ── Étape 3 : Détails ── -->
                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0">
                <div v-if="motifSelectionne" class="rounded-2xl border p-5 space-y-4"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">

                    <p class="text-[10px] font-bold uppercase tracking-widest" :class="sL(dark)">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-[#760078] text-white text-[9px] font-black mr-1.5">3</span>
                        Détails
                    </p>

                    <!-- Département + Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">
                                Département <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.departement_id"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="">Sélectionner…</option>
                                <option v-for="d in departements" :key="d.id" :value="d.id">{{ d.nom }}</option>
                            </select>
                            <p v-if="form.errors.departement_id" class="text-[11px] text-rose-500 mt-1">{{ form.errors.departement_id }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">
                                Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" v-model="form.date_transaction"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                        </div>
                    </div>

                    <!-- Montant -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">
                            Montant (FCFA) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" v-model="form.montant" min="0" step="1" placeholder="0"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                        <p v-if="form.montant" class="text-[10px] mt-1 font-semibold"
                            :class="form.type === 'revenu' ? 'text-emerald-500' : 'text-rose-500'">
                            {{ fmt(form.montant) }}
                        </p>
                        <p v-if="form.errors.montant" class="text-[11px] text-rose-500 mt-1">{{ form.errors.montant }}</p>
                    </div>

                    <!-- Précisions complémentaires (si motif non "Autre") -->
                    <div v-if="!estAutre">
                        <label class="block text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">
                            Précisions <span class="font-normal">(optionnel)</span>
                        </label>
                        <input v-model="details" type="text"
                            :placeholder="form.type === 'dépense' ? 'Ex: trajet Abidjan–Yamoussoukro, facture n°1234…' : 'Ex: client SARL Horizon, projet XYZ…'"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                    </div>

                    <!-- Toggle employé -->
                    <div class="border-t pt-4" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                        <button type="button" @click="avecEmploye = !avecEmploye; if (!avecEmploye) form.user_id = ''"
                            class="flex items-center gap-3 w-full text-left transition-all">
                            <div class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                :class="avecEmploye ? 'border-[#760078] bg-[#760078]' : dark ? 'border-slate-600' : 'border-slate-300'">
                                <svg v-if="avecEmploye" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" :class="avecEmploye ? 'text-[#760078]' : dark ? 'text-slate-300' : 'text-slate-700'">
                                    Concerne un employé spécifique
                                </p>
                                <p class="text-[10px]" :class="sL(dark)">Optionnel — à renseigner si la dépense est directement liée à une personne</p>
                            </div>
                        </button>

                        <Transition enter-active-class="transition-all duration-200 overflow-hidden"
                            enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-24 opacity-100"
                            leave-active-class="transition-all duration-150 overflow-hidden"
                            leave-from-class="max-h-24 opacity-100" leave-to-class="max-h-0 opacity-0">
                            <div v-if="avecEmploye" class="mt-3">
                                <select v-model="form.user_id"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                    <option value="">Sélectionner un employé…</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.nom }} {{ u.prenom }}</option>
                                </select>
                            </div>
                        </Transition>
                    </div>
                </div>
                </Transition>

                <!-- ── Récap ── -->
                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
                <div v-if="motifSelectionne && form.montant && form.departement_id"
                    class="rounded-xl border px-5 py-3.5 flex items-center gap-3"
                    :class="form.type === 'revenu' ? 'border-emerald-300 bg-emerald-50' : 'border-rose-300 bg-rose-50'">
                    <span class="shrink-0" :class="form.type === 'revenu' ? 'text-emerald-600' : 'text-rose-600'">
                        <svg v-if="motifsActuels.find(m => m.label === motifSelectionne)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path :d="motifsActuels.find(m => m.label === motifSelectionne)?.icon"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold" :class="form.type === 'revenu' ? 'text-emerald-700' : 'text-rose-700'">
                            {{ form.type === 'revenu' ? 'Entrée' : 'Sortie' }} · {{ fmt(form.montant) }} · {{ motifSelectionne }}
                        </p>
                        <p v-if="details" class="text-[10px] mt-0.5" :class="form.type === 'revenu' ? 'text-emerald-600' : 'text-rose-600'">
                            {{ details }}
                        </p>
                    </div>
                </div>
                </Transition>

                <!-- ── Actions ── -->
                <div v-if="motifSelectionne" class="flex items-center justify-end gap-3">
                    <Link href="/transactions"
                        class="px-5 py-2 rounded-xl text-sm font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                        Annuler
                    </Link>
                    <button type="submit"
                        :disabled="form.processing || !form.type || !motifSelectionne || !form.departement_id || !form.montant"
                        class="inline-flex items-center gap-2 px-6 py-2 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-40 bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
