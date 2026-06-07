<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { computed } from 'vue';

const { dark } = useTheme();
const props = defineProps({
    bulletin: { type: Object, required: true },
    users:    { type: Array,  default: () => [] },
});

const b = props.bulletin;

const mois  = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const annees = ['2024','2025','2026','2027','2028'];

const form = useForm({
    user_id:                    b.user_id,
    mois:                       b.mois,
    annee:                      String(b.annee),
    salaire_base:               b.salaire_base,
    prime_transport:            b.prime_transport,
    prime_logement:             b.prime_logement,
    prime_fonction:             b.prime_fonction,
    prime_rendement:            b.prime_rendement,
    prime_panier:               b.prime_panier,
    bonus_annuel:               b.bonus_annuel,
    heures_sup:                 b.heures_sup,
    taux_heures_sup:            b.taux_heures_sup,
    avantages_nature_montant:   b.avantages_nature_montant,
    cnps_salarie:               b.cnps_salarie,
    assurance_maladie_salarie:  b.assurance_maladie_salarie,
    igr:                        b.igr,
    is_salaire:                 b.is_salaire,
    avance_salaire:             b.avance_salaire,
    pret_entreprise:            b.pret_entreprise,
    autres_retenues:            b.autres_retenues,
    cnps_employeur:             b.cnps_employeur,
    accident_travail:           b.accident_travail,
    prestations_familiales:     b.prestations_familiales,
    formation_professionnelle:  b.formation_professionnelle,
    mode_paiement:              b.mode_paiement,
    date_paiement:              b.date_paiement ?? '',
    statut:                     b.statut,
});

const n = (v) => Number(v) || 0;

const montantHeuresSup = computed(() => n(form.heures_sup) * n(form.taux_heures_sup));

const salaireBrut = computed(() =>
    n(form.salaire_base) + n(form.prime_transport) + n(form.prime_logement) +
    n(form.prime_fonction) + n(form.prime_rendement) + n(form.prime_panier) +
    n(form.bonus_annuel) + montantHeuresSup.value + n(form.avantages_nature_montant)
);

const totalRetenues = computed(() =>
    n(form.cnps_salarie) + n(form.assurance_maladie_salarie) + n(form.igr) +
    n(form.is_salaire) + n(form.avance_salaire) + n(form.pret_entreprise) + n(form.autres_retenues)
);

const netAPayer  = computed(() => salaireBrut.value - totalRetenues.value);
const coutPatron = computed(() =>
    n(form.cnps_employeur) + n(form.accident_travail) +
    n(form.prestations_familiales) + n(form.formation_professionnelle)
);
const coutTotal = computed(() => salaireBrut.value + coutPatron.value);

const fmt    = (v) => new Intl.NumberFormat('fr-CI').format(Math.round(n(v)));
const submit = () => form.put(`/bulletins-paie/${b.id}`);

const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15';
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';
</script>

<template>
    <Head :title="`Modifier ${b.reference}`" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                        Modifier le bulletin — {{ b.reference }}
                    </h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ b.user?.nom }} {{ b.user?.prenom }} · {{ b.mois }} {{ b.annee }}</p>
                </div>
                <Link :href="`/bulletins-paie/${b.id}`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                <!-- ── Formulaire ─────────────────────────────── -->
                <form @submit.prevent="submit" class="lg:col-span-2 space-y-5">

                    <!-- Identification -->
                    <div class="rounded-2xl border p-5 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Identification</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Employé</label>
                                <select v-model="form.user_id" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.nom }} {{ u.prenom }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Mois</label>
                                    <select v-model="form.mois" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                        <option v-for="m in mois" :key="m" :value="m">{{ m }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Année</label>
                                    <select v-model="form.annee" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                        <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Statut</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <button v-for="s in ['brouillon','valide','paye']" :key="s" type="button" @click="form.statut = s"
                                        class="py-2 rounded-xl border-2 text-xs font-bold transition-all"
                                        :class="form.statut === s
                                            ? s === 'brouillon' ? 'border-slate-400 bg-slate-100 text-slate-700' : s === 'valide' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                            : dark ? 'border-[#30363d] text-slate-500' : 'border-slate-200 text-slate-400'">
                                        {{ s === 'brouillon' ? 'Brouillon' : s === 'valide' ? 'Validé' : 'Payé' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rémunération -->
                    <div class="rounded-2xl border p-5 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Éléments de rémunération</p>
                        <div class="space-y-2">
                            <div v-for="(label, key) in {
                                salaire_base: 'Salaire de base *', prime_transport: 'Prime de transport',
                                prime_logement: 'Prime de logement', prime_fonction: 'Prime de fonction',
                                prime_rendement: 'Prime de rendement', prime_panier: 'Prime de panier',
                                bonus_annuel: 'Bonus annuel', avantages_nature_montant: 'Avantages en nature'
                            }" :key="key" class="flex items-center gap-3">
                                <label class="w-52 shrink-0 text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</label>
                                <input v-model="form[key]" type="number" min="0" placeholder="0"
                                    class="flex-1 rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2" :class="inp(dark)" />
                                <span class="text-xs w-14 text-right" :class="sL(dark)">FCFA</span>
                            </div>
                        </div>
                        <div class="border-t mt-4 pt-4" :class="div(dark)">
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Heures supplémentaires</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Nb heures</label>
                                    <input v-model="form.heures_sup" type="number" min="0" step="0.5" class="w-full rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2" :class="inp(dark)" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Taux / heure</label>
                                    <input v-model="form.taux_heures_sup" type="number" min="0" class="w-full rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2" :class="inp(dark)" />
                                </div>
                                <div class="pb-2 text-right">
                                    <p class="text-[10px]" :class="sL(dark)">Montant</p>
                                    <p class="text-sm font-bold text-[#760078]">{{ fmt(montantHeuresSup) }} FCFA</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Retenues -->
                    <div class="rounded-2xl border p-5 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Retenues salariales</p>
                        <div class="space-y-2">
                            <div v-for="(label, key) in {
                                cnps_salarie: 'CNPS salarié', assurance_maladie_salarie: 'Assurance maladie',
                                igr: 'IGR', is_salaire: 'IS (Impôt sur salaire)',
                                avance_salaire: 'Avance sur salaire', pret_entreprise: 'Prêt entreprise',
                                autres_retenues: 'Autres retenues'
                            }" :key="key" class="flex items-center gap-3">
                                <label class="w-52 shrink-0 text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</label>
                                <input v-model="form[key]" type="number" min="0" placeholder="0"
                                    class="flex-1 rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2" :class="inp(dark)" />
                                <span class="text-xs w-14 text-right" :class="sL(dark)">FCFA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cotisations patronales -->
                    <div class="rounded-2xl border p-5 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Cotisations patronales</p>
                        <div class="space-y-2">
                            <div v-for="(label, key) in {
                                cnps_employeur: 'CNPS employeur', accident_travail: 'Accident du travail',
                                prestations_familiales: 'Prestations familiales', formation_professionnelle: 'Formation professionnelle'
                            }" :key="key" class="flex items-center gap-3">
                                <label class="w-52 shrink-0 text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</label>
                                <input v-model="form[key]" type="number" min="0" placeholder="0"
                                    class="flex-1 rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2" :class="inp(dark)" />
                                <span class="text-xs w-14 text-right" :class="sL(dark)">FCFA</span>
                            </div>
                        </div>
                        <div class="border-t mt-4 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-3" :class="div(dark)">
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Mode de paiement</label>
                                <select v-model="form.mode_paiement" class="w-full rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2" :class="inp(dark)">
                                    <option value="virement">Virement bancaire</option>
                                    <option value="especes">Espèces</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="cheque">Chèque</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Date de paiement</label>
                                <input v-model="form.date_paiement" type="date"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2" :class="inp(dark)" />
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all disabled:opacity-50">
                            {{ form.processing ? 'Enregistrement…' : 'Enregistrer les modifications' }}
                        </button>
                    </div>
                </form>

                <!-- ── Récapitulatif ───────────────────────────── -->
                <div class="sticky top-4 space-y-3">
                    <div class="rounded-2xl border p-4 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Récapitulatif</p>

                        <div v-for="([label, val, color]) in [
                            ['Salaire brut',     salaireBrut,    'text-[#760078]'],
                            ['Total retenues',   totalRetenues,  'text-rose-500'],
                            ['Net à payer',      netAPayer,      'text-emerald-600'],
                            ['Coût patronal',    coutPatron,     'text-slate-500'],
                            ['Coût total',       coutTotal,      'text-slate-700'],
                        ]" :key="label" class="flex items-center justify-between py-1.5 border-b last:border-0 last:pt-2 last:font-extrabold"
                            :class="div(dark)">
                            <span class="text-xs" :class="sL(dark)">{{ label }}</span>
                            <span class="text-xs font-bold" :class="color">{{ fmt(val) }} <span class="text-[9px] font-normal">FCFA</span></span>
                        </div>
                    </div>

                    <div v-if="form.errors.salaire_base || form.errors.user_id"
                        class="rounded-xl p-3 bg-rose-50 border border-rose-200 text-xs text-rose-600 space-y-1">
                        <p v-for="(err, k) in form.errors" :key="k">{{ err }}</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
