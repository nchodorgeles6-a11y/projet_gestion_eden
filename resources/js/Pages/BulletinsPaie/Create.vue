<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed, watch } from 'vue';

const { dark } = useTheme();
const props = defineProps({
    users:          { type: Array,  default: () => [] },
    selectedUser:   { type: Object, default: null },
    resumeAbsences: { type: Object, default: null },
});

const currentStep = ref(1);
const totalSteps  = 3;
const steps = [
    { id: 1, label: 'Employé & Période', sub: 'Identification' },
    { id: 2, label: 'Rémunération',      sub: 'Salaire et retenues' },
    { id: 3, label: 'Cotisations',       sub: 'Patronales et paiement' },
];

const mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const annees = ['2024','2025','2026','2027','2028'];

const form = useForm({
    user_id:                    props.selectedUser?.id ?? '',
    mois:                       mois[new Date().getMonth()],
    annee:                      String(new Date().getFullYear()),
    salaire_base:               props.selectedUser?.salaire_base ?? '',
    prime_transport:            props.selectedUser?.prime_transport ?? 0,
    prime_logement:             props.selectedUser?.prime_logement ?? 0,
    prime_fonction:             props.selectedUser?.prime_fonction ?? 0,
    prime_rendement:            props.selectedUser?.prime_rendement ?? 0,
    prime_panier:               props.selectedUser?.prime_panier ?? 0,
    bonus_annuel:               props.selectedUser?.bonus_annuel ?? 0,
    heures_sup:                 0,
    taux_heures_sup:            0,
    avantages_nature_montant:   0,
    cnps_salarie:               0,
    assurance_maladie_salarie:  0,
    igr:                        0,
    is_salaire:                 0,
    avance_salaire:             0,
    pret_entreprise:            0,
    autres_retenues:            0,
    cnps_employeur:             0,
    accident_travail:           0,
    prestations_familiales:     0,
    formation_professionnelle:  0,
    mode_paiement:              'virement',
    date_paiement:              '',
    statut:                     'brouillon',
});

// Quand on change d'employé, on pré-remplit les champs depuis ses données
watch(() => form.user_id, (uid) => {
    const u = props.users.find(u => u.id === uid);
    if (!u) return;
    form.salaire_base      = u.salaire_base ?? 0;
    form.prime_transport   = u.prime_transport ?? 0;
    form.prime_logement    = u.prime_logement ?? 0;
    form.prime_fonction    = u.prime_fonction ?? 0;
    form.prime_rendement   = u.prime_rendement ?? 0;
    form.prime_panier      = u.prime_panier ?? 0;
    form.bonus_annuel      = u.bonus_annuel ?? 0;
});

// Rechargement partiel pour calculer les déductions quand user/mois/annee changent
let reloadTimer = null;
watch([() => form.user_id, () => form.mois, () => form.annee], ([uid, mois, annee]) => {
    if (!uid || !mois || !annee) return;
    clearTimeout(reloadTimer);
    reloadTimer = setTimeout(() => {
        router.get('/bulletins-paie/create',
            { user_id: uid, mois, annee },
            { preserveState: true, only: ['resumeAbsences'] }
        );
    }, 400);
});

// Pré-remplir "autres_retenues" avec la déduction calculée
watch(() => props.resumeAbsences, (resume) => {
    if (resume && resume.total_deduction >= 0) {
        form.autres_retenues = resume.total_deduction;
    }
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

const netAPayer = computed(() => salaireBrut.value - totalRetenues.value);

const coutPatronal = computed(() =>
    n(form.cnps_employeur) + n(form.accident_travail) + n(form.prestations_familiales) + n(form.formation_professionnelle)
);

const coutTotal = computed(() => salaireBrut.value + coutPatronal.value);

const fmt = (n) => new Intl.NumberFormat('fr-CI').format(Math.round(n));

const canProceed = computed(() => {
    if (currentStep.value === 1) return form.user_id && form.mois && form.annee;
    if (currentStep.value === 2) return form.salaire_base;
    return true;
});

const next   = () => { if (canProceed.value && currentStep.value < totalSteps) currentStep.value++; };
const prev   = () => { if (currentStep.value > 1) currentStep.value--; };
const submit = () => form.post('/bulletins-paie');

const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15';
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';
</script>

<template>
    <Head title="Nouveau bulletin de paie" />
    <AuthenticatedLayout>
        <div class="p-6">

            <!-- En-tête -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Nouveau bulletin de paie</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Étape {{ currentStep }} sur {{ totalSteps }} — {{ steps[currentStep - 1].sub }}</p>
                </div>
                <Link href="/bulletins-paie" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour
                </Link>
            </div>

            <!-- Stepper -->
            <div class="mb-6">
                <div class="flex items-center">
                    <template v-for="(step, i) in steps" :key="step.id">
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs transition-all border-2"
                                :class="currentStep > step.id
                                    ? 'bg-[#760078] border-[#760078] text-white'
                                    : currentStep === step.id
                                        ? 'border-[#760078] text-[#760078] ' + (dark ? 'bg-[#161b22]' : 'bg-white')
                                        : dark ? 'border-[#30363d] text-slate-600 bg-[#161b22]' : 'border-slate-200 text-slate-400 bg-white'">
                                <svg v-if="currentStep > step.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span v-else>{{ step.id }}</span>
                            </div>
                            <span class="mt-1.5 text-[10px] font-semibold text-center max-w-[80px] leading-tight"
                                :class="currentStep === step.id ? 'text-[#760078]' : sL(dark)">{{ step.label }}</span>
                        </div>
                        <div v-if="i < steps.length - 1" class="flex-1 h-0.5 mx-2 mb-5 rounded-full transition-all"
                            :class="currentStep > step.id ? 'bg-[#760078]' : dark ? 'bg-[#30363d]' : 'bg-slate-200'"></div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-5 items-start">
                <!-- Formulaire principal -->
                <div class="col-span-2 rounded-2xl border transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <form @submit.prevent="submit">

                        <!-- ── Étape 1 : Employé & Période ── -->
                        <div v-show="currentStep === 1" class="p-4 sm:p-6 space-y-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-5" :class="sL(dark)">Identification</p>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Employé <span class="text-rose-500">*</span></label>
                                <select v-model="form.user_id" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                    <option value="">Sélectionner un employé</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.nom }} {{ u.prenom }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Mois <span class="text-rose-500">*</span></label>
                                    <select v-model="form.mois" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                        <option v-for="m in mois" :key="m" :value="m">{{ m }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Année <span class="text-rose-500">*</span></label>
                                    <select v-model="form.annee" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                        <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Statut initial</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="s in ['brouillon','valide','paye']" :key="s" type="button" @click="form.statut = s"
                                        class="py-2 rounded-xl border-2 text-xs font-bold capitalize transition-all"
                                        :class="form.statut === s
                                            ? s === 'brouillon' ? 'border-slate-400 bg-slate-100 text-slate-700' : s === 'valide' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                            : dark ? 'border-[#30363d] text-slate-500' : 'border-slate-200 text-slate-400'">
                                        {{ s === 'brouillon' ? 'Brouillon' : s === 'valide' ? 'Validé' : 'Payé' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ── Étape 2 : Rémunération ── -->
                        <div v-show="currentStep === 2" class="p-4 sm:p-6 space-y-6">

                            <!-- ── Résumé absences du mois ── -->
                            <div v-if="resumeAbsences" class="rounded-xl border-2 p-4 space-y-3"
                                :class="resumeAbsences.total_deduction > 0
                                    ? (dark ? 'border-amber-500/30 bg-amber-900/5' : 'border-amber-200 bg-amber-50/60')
                                    : (dark ? 'border-emerald-500/30 bg-emerald-900/5' : 'border-emerald-200 bg-emerald-50/60')">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" :class="resumeAbsences.total_deduction > 0 ? 'text-amber-500' : 'text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-xs font-bold" :class="resumeAbsences.total_deduction > 0 ? (dark ? 'text-amber-300' : 'text-amber-700') : (dark ? 'text-emerald-300' : 'text-emerald-700')">
                                            Absences & retards du mois
                                        </p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                        :class="resumeAbsences.total_deduction > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
                                        {{ resumeAbsences.total_deduction > 0 ? '− ' + fmt(resumeAbsences.total_deduction) + ' FCFA' : 'Aucune déduction' }}
                                    </span>
                                </div>

                                <!-- KPIs absences -->
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="(item, i) in [
                                        { label: 'Abs. déduites',  val: resumeAbsences.absences_deduites,     color: 'text-rose-600',    unit: 'j' },
                                        { label: 'Abs. payées',    val: resumeAbsences.absences_payees,       color: 'text-emerald-600', unit: 'j' },
                                        { label: 'Retards déduits',val: resumeAbsences.retards_non_justifies, color: 'text-amber-600',   unit: '' },
                                        { label: 'Salaire/jour',   val: resumeAbsences.salaire_journalier,    color: 'text-slate-600',   unit: ' F' },
                                    ]" :key="i" class="rounded-lg p-2 text-center border"
                                        :class="dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-white border-slate-200'">
                                        <p class="text-[9px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">{{ item.label }}</p>
                                        <p class="text-lg font-black" :class="item.color">{{ item.val > 999 ? fmt(item.val) : item.val }}{{ item.unit }}</p>
                                    </div>
                                </div>

                                <!-- Décomposition déduction -->
                                <div v-if="resumeAbsences.total_deduction > 0" class="space-y-1 text-[11px]">
                                    <div v-if="resumeAbsences.deduction_absences > 0" class="flex items-center justify-between">
                                        <span :class="sL(dark)">{{ resumeAbsences.absences_deduites }} jour(s) × {{ fmt(resumeAbsences.salaire_journalier) }} FCFA</span>
                                        <span class="font-semibold text-rose-500">− {{ fmt(resumeAbsences.deduction_absences) }} FCFA</span>
                                    </div>
                                    <div v-if="resumeAbsences.deduction_retards > 0" class="flex items-center justify-between">
                                        <span :class="sL(dark)">{{ resumeAbsences.retards_non_justifies }} retard(s) × {{ fmt(resumeAbsences.salaire_horaire) }} FCFA/h</span>
                                        <span class="font-semibold text-amber-500">− {{ fmt(resumeAbsences.deduction_retards) }} FCFA</span>
                                    </div>
                                    <div class="flex items-center justify-between border-t pt-1 font-bold" :class="dark ? 'border-[#30363d]' : 'border-slate-200'">
                                        <span :class="dark ? 'text-white' : 'text-slate-800'">Total déduit → pré-rempli dans "Autres retenues"</span>
                                        <span class="text-rose-500">− {{ fmt(resumeAbsences.total_deduction) }} FCFA</span>
                                    </div>
                                </div>

                                <!-- Détail ligne par ligne -->
                                <div v-if="resumeAbsences.detail?.length" class="space-y-1 max-h-36 overflow-y-auto">
                                    <div v-for="(d, i) in resumeAbsences.detail" :key="i"
                                        class="flex items-center gap-2 text-[10px] px-2 py-1 rounded-lg"
                                        :class="dark ? 'bg-[#0d1117]' : 'bg-white'">
                                        <span class="font-bold w-10 shrink-0" :class="sL(dark)">{{ d.date }}</span>
                                        <span class="px-1.5 py-0.5 rounded font-bold text-[9px] shrink-0"
                                            :class="d.type === 'Retard' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700'">
                                            {{ d.type }}
                                        </span>
                                        <span class="flex-1 truncate" :class="sL(dark)">{{ d.motif }}</span>
                                        <span class="font-bold shrink-0"
                                            :class="d.couleur === 'rose' ? 'text-rose-500' : d.couleur === 'amber' ? 'text-amber-500' : 'text-emerald-500'">
                                            {{ d.impact }}
                                        </span>
                                    </div>
                                </div>
                                <p v-else-if="resumeAbsences.total_deduction === 0" class="text-[11px] font-semibold text-emerald-600">
                                    Aucune absence ni retard enregistré ce mois — salaire complet.
                                </p>
                            </div>

                            <!-- Éléments de rémunération -->
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Éléments de rémunération</p>
                                <div class="space-y-2">
                                    <div v-for="(label, key) in { salaire_base: 'Salaire de base *', prime_transport: 'Prime de transport', prime_logement: 'Prime de logement', prime_fonction: 'Prime de fonction', prime_rendement: 'Prime de rendement', prime_panier: 'Prime de panier', bonus_annuel: 'Bonus annuel', avantages_nature_montant: 'Avantages en nature (valeur)' }" :key="key"
                                        class="flex items-center gap-3">
                                        <label class="w-52 shrink-0 text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</label>
                                        <input v-model="form[key]" type="number" min="0" placeholder="0"
                                            class="flex-1 rounded-xl border px-3 py-2 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                        <span class="text-xs w-16 text-right" :class="sL(dark)">FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Heures supplémentaires -->
                            <div class="border-t pt-5" :class="div(dark)">
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Heures supplémentaires</p>
                                <div class="grid grid-cols-3 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Nb heures</label>
                                        <input v-model="form.heures_sup" type="number" min="0" step="0.5" placeholder="0"
                                            class="w-full rounded-xl border px-3 py-2 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Taux / heure (FCFA)</label>
                                        <input v-model="form.taux_heures_sup" type="number" min="0" placeholder="0"
                                            class="w-full rounded-xl border px-3 py-2 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                    </div>
                                    <div class="pb-2 text-right">
                                        <p class="text-[10px]" :class="sL(dark)">Montant</p>
                                        <p class="text-sm font-bold text-[#760078]">{{ fmt(montantHeuresSup) }} FCFA</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Retenues salariales -->
                            <div class="border-t pt-5" :class="div(dark)">
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Retenues salariales</p>
                                <div class="space-y-2">
                                    <div v-for="(label, key) in { cnps_salarie: 'CNPS salarié', assurance_maladie_salarie: 'Assurance maladie', igr: 'IGR (Impôt Général sur le Revenu)', is_salaire: 'IS (Impôt sur salaire)', avance_salaire: 'Avance sur salaire', pret_entreprise: 'Prêt entreprise', autres_retenues: 'Autres retenues' }" :key="key"
                                        class="flex items-center gap-3">
                                        <label class="w-52 shrink-0 text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</label>
                                        <input v-model="form[key]" type="number" min="0" placeholder="0"
                                            class="flex-1 rounded-xl border px-3 py-2 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                        <span class="text-xs w-16 text-right" :class="sL(dark)">FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Étape 3 : Cotisations patronales & Paiement ── -->
                        <div v-show="currentStep === 3" class="p-4 sm:p-6 space-y-6">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Cotisations patronales</p>
                                <div class="space-y-2">
                                    <div v-for="(label, key) in { cnps_employeur: 'CNPS employeur', accident_travail: 'Accident du travail', prestations_familiales: 'Prestations familiales', formation_professionnelle: 'Formation professionnelle' }" :key="key"
                                        class="flex items-center gap-3">
                                        <label class="w-52 shrink-0 text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</label>
                                        <input v-model="form[key]" type="number" min="0" placeholder="0"
                                            class="flex-1 rounded-xl border px-3 py-2 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                        <span class="text-xs w-16 text-right" :class="sL(dark)">FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t pt-5" :class="div(dark)">
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Modalités de paiement</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Mode de paiement</label>
                                        <select v-model="form.mode_paiement" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                            <option value="virement">Virement bancaire</option>
                                            <option value="especes">Espèces</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="cheque">Chèque</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Date de paiement</label>
                                        <input v-model="form.date_paiement" type="date"
                                            class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="px-6 py-4 border-t flex items-center justify-between" :class="div(dark)">
                            <button type="button" @click="prev" :disabled="currentStep === 1"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                                :class="dark ? 'border-[#30363d] text-slate-300 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Précédent
                            </button>
                            <div class="flex gap-1.5">
                                <div v-for="i in totalSteps" :key="i" class="h-1.5 rounded-full transition-all"
                                    :class="i === currentStep ? 'w-6 bg-[#760078]' : i < currentStep ? 'w-3 bg-[#760078]/40' : 'w-3 ' + (dark ? 'bg-[#30363d]' : 'bg-slate-200')"></div>
                            </div>
                            <button v-if="currentStep < totalSteps" type="button" @click="next" :disabled="!canProceed"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]">
                                Suivant
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                            <button v-else type="submit" :disabled="form.processing"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-60 bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ form.processing ? 'Génération...' : 'Générer le bulletin' }}
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Récapitulatif live -->
                <div class="rounded-2xl border transition-colors sticky top-4" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <div class="px-5 py-3 border-b" :class="div(dark)">
                        <p class="text-[10px] font-bold uppercase tracking-widest" :class="sL(dark)">Récapitulatif</p>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span :class="sL(dark)">Salaire brut</span>
                            <span class="font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ fmt(salaireBrut) }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span :class="sL(dark)">Total retenues</span>
                            <span class="font-bold text-rose-500">- {{ fmt(totalRetenues) }} FCFA</span>
                        </div>
                        <div class="border-t pt-3" :class="div(dark)">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Net à payer</span>
                                <span class="text-base font-extrabold text-[#760078]">{{ fmt(netAPayer) }} FCFA</span>
                            </div>
                        </div>
                        <div class="border-t pt-3" :class="div(dark)">
                            <div class="flex items-center justify-between text-xs">
                                <span :class="sL(dark)">Charges patronales</span>
                                <span class="font-semibold" :class="dark ? 'text-slate-300' : 'text-slate-600'">{{ fmt(coutPatronal) }} FCFA</span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-2">
                                <span class="font-bold" :class="sL(dark)">Coût total employeur</span>
                                <span class="font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">{{ fmt(coutTotal) }} FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
