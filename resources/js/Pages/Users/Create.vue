<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed, watch } from 'vue';

const { dark } = useTheme();
const props = defineProps({ departements: Array });

const currentStep = ref(1);
const totalSteps  = 4;

const steps = [
    { id: 1, label: 'Identité',               sub: 'Informations personnelles' },
    { id: 2, label: 'Poste & Contrat',        sub: 'Affectation et type' },
    { id: 3, label: 'Rémunération',           sub: 'Salaire et primes' },
    { id: 4, label: 'Avantages & Protection', sub: 'Couverture sociale' },
];

const departementId = ref('');

const postesFiltres = computed(() => {
    if (!departementId.value) return [];
    const dept = props.departements.find(d => d.id === departementId.value);
    return dept?.postes ?? [];
});

watch(departementId, () => { form.poste_id = ''; });

const form = useForm({
    nom: '', prenom: '', email: '', telephone: '',
    type_contrat: 'employe',
    poste_id: '',
    salaire_base: '',
    mode_paiement: 'fixe',
    prime_transport: '', prime_logement: '', prime_fonction: '',
    prime_rendement: '', prime_panier: '',  bonus_annuel: '',
    cnps: true, assurance_maladie: false,
    avantages_nature: [],
    date_fin_contrat: '',
});

watch(() => form.type_contrat, (type) => {
    form.mode_paiement = type === 'prestataire' ? 'par_prestation' : 'fixe';
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return form.nom && form.prenom && form.email && form.telephone;
    if (currentStep.value === 2) return departementId.value && form.poste_id;
    if (currentStep.value === 3) return form.salaire_base;
    return true;
});

const next   = () => { if (canProceed.value && currentStep.value < totalSteps) currentStep.value++; };
const prev   = () => { if (currentStep.value > 1) currentStep.value--; };
const submit = () => form.post('/users');

const avantagesOptions = [
    { value: 'vehicule',         label: 'Véhicule de service' },
    { value: 'carburant',        label: 'Carburant' },
    { value: 'telephone',        label: 'Téléphone professionnel' },
    { value: 'internet',         label: 'Internet' },
    { value: 'logement',         label: 'Logement' },
    { value: 'chauffeur',        label: 'Chauffeur' },
    { value: 'assurance_privee', label: 'Assurance privée' },
];

const primes = [
    { key: 'prime_transport', label: 'Transport' },
    { key: 'prime_logement',  label: 'Logement' },
    { key: 'prime_fonction',  label: 'Fonction' },
    { key: 'prime_rendement', label: 'Rendement' },
    { key: 'prime_panier',    label: 'Panier' },
    { key: 'bonus_annuel',    label: 'Bonus annuel' },
];

const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15';
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';
</script>

<template>
    <Head title="Ajouter un collaborateur" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6">

            <!-- En-tête -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Ajouter un collaborateur</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Étape {{ currentStep }} sur {{ totalSteps }} — {{ steps[currentStep - 1].sub }}</p>
                </div>
                <Link href="/users" class="text-decoration-none inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
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
                                :class="currentStep === step.id ? 'text-[#760078]' : sL(dark)">
                                {{ step.label }}
                            </span>
                        </div>
                        <div v-if="i < steps.length - 1" class="flex-1 h-0.5 mx-2 mb-5 rounded-full transition-all"
                            :class="currentStep > step.id ? 'bg-[#760078]' : dark ? 'bg-[#30363d]' : 'bg-slate-200'">
                        </div>
                    </template>
                </div>
            </div>

            <!-- Carte wizard -->
            <div class="rounded-2xl border transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <form @submit.prevent="submit">

                    <!-- ── Étape 1 : Identité ── -->
                    <div v-show="currentStep === 1" class="p-6">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-5" :class="sL(dark)">Informations personnelles</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Nom <span class="text-rose-500">*</span></label>
                                <input v-model="form.nom" type="text" placeholder="Dupont"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Prénom <span class="text-rose-500">*</span></label>
                                <input v-model="form.prenom" type="text" placeholder="Jean"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">E-mail <span class="text-rose-500">*</span></label>
                                <input v-model="form.email" type="email" placeholder="jean.dupont@eden.com"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Téléphone <span class="text-rose-500">*</span></label>
                                <input v-model="form.telephone" type="tel" placeholder="+225 07 00 00 00 00"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                            </div>
                        </div>
                    </div>

                    <!-- ── Étape 2 : Poste & Contrat ── -->
                    <div v-show="currentStep === 2" class="p-6 space-y-6">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Type de contrat</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="form.type_contrat = 'employe'"
                                    class="flex flex-col items-start gap-1.5 p-4 rounded-xl border-2 text-left transition-all"
                                    :class="form.type_contrat === 'employe' ? 'border-[#760078] bg-[#760078]/5' : dark ? 'border-[#30363d] hover:border-[#760078]/40' : 'border-slate-200 hover:border-[#760078]/40'">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                            :class="form.type_contrat === 'employe' ? 'border-[#760078]' : dark ? 'border-slate-600' : 'border-slate-300'">
                                            <div v-if="form.type_contrat === 'employe'" class="w-2 h-2 rounded-full bg-[#760078]"></div>
                                        </div>
                                        <span class="text-sm font-bold" :class="form.type_contrat === 'employe' ? 'text-[#760078]' : dark ? 'text-white' : 'text-slate-800'">Employé</span>
                                    </div>
                                    <p class="text-[11px] ml-6" :class="sL(dark)">Contrat CDD ou CDI</p>
                                </button>
                                <button type="button" @click="form.type_contrat = 'prestataire'"
                                    class="flex flex-col items-start gap-1.5 p-4 rounded-xl border-2 text-left transition-all"
                                    :class="form.type_contrat === 'prestataire' ? 'border-[#7677B7] bg-[#7677B7]/5' : dark ? 'border-[#30363d] hover:border-[#7677B7]/40' : 'border-slate-200 hover:border-[#7677B7]/40'">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                            :class="form.type_contrat === 'prestataire' ? 'border-[#7677B7]' : dark ? 'border-slate-600' : 'border-slate-300'">
                                            <div v-if="form.type_contrat === 'prestataire'" class="w-2 h-2 rounded-full bg-[#7677B7]"></div>
                                        </div>
                                        <span class="text-sm font-bold" :class="form.type_contrat === 'prestataire' ? 'text-[#7677B7]' : dark ? 'text-white' : 'text-slate-800'">Prestataire</span>
                                    </div>
                                    <p class="text-[11px] ml-6" :class="sL(dark)">Mission ou contrat de service</p>
                                </button>
                            </div>
                        </div>

                        <div class="border-t" :class="div(dark)"></div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Affectation</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                        {{ form.type_contrat === 'prestataire' ? "Département d'intervention" : 'Département' }} <span class="text-rose-500">*</span>
                                    </label>
                                    <select v-model="departementId" class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                        <option value="">Choisir un département</option>
                                        <option v-for="d in departements" :key="d.id" :value="d.id">{{ d.nom }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                        Poste <span class="text-rose-500">*</span>
                                    </label>
                                    <select v-model="form.poste_id" :disabled="!departementId"
                                        class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed" :class="inp(dark)">
                                        <option value="">{{ departementId ? 'Choisir un poste' : "Sélectionnez d'abord un département" }}</option>
                                        <option v-for="p in postesFiltres" :key="p.id" :value="p.id">{{ p.nom }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border-t" :class="div(dark)"></div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Durée du contrat</p>
                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                    Date de fin de contrat <span class="font-normal opacity-60">(optionnel — CDI si vide)</span>
                                </label>
                                <input v-model="form.date_fin_contrat" type="date"
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                <p class="text-[10px] mt-1.5" :class="sL(dark)">
                                    Une alerte apparaîtra dans la liste quand le contrat est proche de l'échéance.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Étape 3 : Rémunération ── -->
                    <div v-show="currentStep === 3" class="p-6 space-y-6">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Rémunération de base</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
                                <div>
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                        {{ form.type_contrat === 'prestataire' ? 'Montant de la prestation (FCFA)' : 'Salaire de base (FCFA)' }} <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="form.salaire_base" type="number" min="0" placeholder="Ex: 350 000"
                                        class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-3" :class="dark ? 'text-slate-300' : 'text-slate-700'">Mode de paiement</label>
                                    <div class="grid gap-3" :class="form.type_contrat === 'prestataire' ? 'grid-cols-2' : 'grid-cols-1'">
                                        <button type="button" @click="form.mode_paiement = 'fixe'"
                                            class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                            :class="form.mode_paiement === 'fixe' ? 'border-emerald-500 bg-emerald-50' : dark ? 'border-[#30363d] hover:border-emerald-500/40' : 'border-slate-200 hover:border-emerald-500/40'">
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                                :class="form.mode_paiement === 'fixe' ? 'border-emerald-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                                <div v-if="form.mode_paiement === 'fixe'" class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold" :class="form.mode_paiement === 'fixe' ? 'text-emerald-600' : dark ? 'text-white' : 'text-slate-700'">Salaire fixe</p>
                                                <p class="text-[10px]" :class="sL(dark)">Mensuel, chaque mois</p>
                                            </div>
                                        </button>
                                        <button v-if="form.type_contrat === 'prestataire'" type="button" @click="form.mode_paiement = 'par_prestation'"
                                            class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                            :class="form.mode_paiement === 'par_prestation' ? 'border-amber-500 bg-amber-50' : dark ? 'border-[#30363d] hover:border-amber-500/40' : 'border-slate-200 hover:border-amber-500/40'">
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                                :class="form.mode_paiement === 'par_prestation' ? 'border-amber-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                                <div v-if="form.mode_paiement === 'par_prestation'" class="w-2 h-2 rounded-full bg-amber-500"></div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold" :class="form.mode_paiement === 'par_prestation' ? 'text-amber-600' : dark ? 'text-white' : 'text-slate-700'">Par prestation</p>
                                                <p class="text-[10px]" :class="sL(dark)">À la mission ou livraison</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t" :class="div(dark)"></div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">Primes & Compléments</p>
                            <p class="text-[11px] mb-4" :class="dark ? 'text-slate-500' : 'text-slate-400'">Laisser vide si la prime ne s'applique pas</p>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                                <div v-for="p in primes" :key="p.key">
                                    <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ p.label }} (FCFA)</label>
                                    <input v-model="form[p.key]" type="number" min="0" placeholder="0"
                                        class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Étape 4 : Avantages & Protection ── -->
                    <div v-show="currentStep === 4" class="p-6 space-y-6">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Avantages en nature</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                <label v-for="opt in avantagesOptions" :key="opt.value"
                                    class="flex items-center gap-2.5 px-3 py-3 rounded-xl border cursor-pointer transition-all select-none"
                                    :class="form.avantages_nature.includes(opt.value)
                                        ? 'border-[#760078] bg-[#760078]/5'
                                        : dark ? 'border-[#30363d] hover:border-[#760078]/30' : 'border-slate-200 hover:border-[#760078]/30'">
                                    <input type="checkbox" :value="opt.value" v-model="form.avantages_nature" class="hidden" />
                                    <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                        :class="form.avantages_nature.includes(opt.value) ? 'border-[#760078] bg-[#760078]' : dark ? 'border-slate-600' : 'border-slate-300'">
                                        <svg v-if="form.avantages_nature.includes(opt.value)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium" :class="form.avantages_nature.includes(opt.value) ? 'text-[#760078]' : dark ? 'text-slate-300' : 'text-slate-700'">
                                        {{ opt.label }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t" :class="div(dark)"></div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Protections sociales</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <button type="button" @click="form.cnps = !form.cnps"
                                    class="flex items-start gap-3 p-4 rounded-xl border-2 text-left transition-all"
                                    :class="form.cnps ? 'border-blue-500 bg-blue-50' : dark ? 'border-[#30363d] hover:border-blue-500/40' : 'border-slate-200 hover:border-blue-400/40'">
                                    <div class="mt-0.5 w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                        :class="form.cnps ? 'border-blue-500 bg-blue-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                        <svg v-if="form.cnps" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold" :class="form.cnps ? 'text-blue-600' : dark ? 'text-white' : 'text-slate-800'">CNPS</p>
                                        <p class="text-[11px] mt-0.5" :class="sL(dark)">Caisse Nationale de Prévoyance Sociale — retraite, allocations, accident du travail</p>
                                    </div>
                                </button>
                                <button type="button" @click="form.assurance_maladie = !form.assurance_maladie"
                                    class="flex items-start gap-3 p-4 rounded-xl border-2 text-left transition-all"
                                    :class="form.assurance_maladie ? 'border-rose-500 bg-rose-50' : dark ? 'border-[#30363d] hover:border-rose-500/40' : 'border-slate-200 hover:border-rose-400/40'">
                                    <div class="mt-0.5 w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                        :class="form.assurance_maladie ? 'border-rose-500 bg-rose-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                        <svg v-if="form.assurance_maladie" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold" :class="form.assurance_maladie ? 'text-rose-600' : dark ? 'text-white' : 'text-slate-800'">Assurance maladie</p>
                                        <p class="text-[11px] mt-0.5" :class="sL(dark)">CNAM — consultations, hospitalisation, médicaments remboursés</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Navigation ── -->
                    <div class="px-6 py-4 border-t flex items-center justify-between" :class="div(dark)">
                        <button type="button" @click="prev" :disabled="currentStep === 1"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                            :class="dark ? 'border-[#30363d] text-slate-300 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Précédent
                        </button>

                        <!-- Pastilles de progression -->
                        <div class="flex gap-1.5">
                            <div v-for="i in totalSteps" :key="i"
                                class="h-1.5 rounded-full transition-all"
                                :class="i === currentStep ? 'w-6 bg-[#760078]' : i < currentStep ? 'w-3 bg-[#760078]/40' : 'w-3 ' + (dark ? 'bg-[#30363d]' : 'bg-slate-200')">
                            </div>
                        </div>

                        <button v-if="currentStep < totalSteps" type="button" @click="next" :disabled="!canProceed"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed
                                   bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]">
                            Suivant
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <button v-else type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-60
                                   bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ form.processing ? 'Enregistrement...' : 'Enregistrer le collaborateur' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
