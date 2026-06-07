<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { computed, ref } from 'vue';

const { dark } = useTheme();
const props = defineProps({ users: Array, postes: Array });

const form = useForm({
    date_debut:       '',
    date_fin:         '',
    user_id:          '',
    poste_id:         '',
    motif_changement: '',
});

const submit = () => form.post('/affectations');

const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15';

const sL = (d) => d ? 'text-slate-400' : 'text-slate-500';

const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(n) + ' FCFA' : '—';

// ── Service (département) filter ───────────────────────────────────────────
const selectedDept = ref('');

const departements = computed(() => {
    const seen = new Set();
    return props.postes
        .filter(p => p.departement)
        .map(p => p.departement)
        .filter(d => { if (seen.has(d.id)) return false; seen.add(d.id); return true; })
        .sort((a, b) => a.nom.localeCompare(b.nom));
});

const postesFiltres = computed(() =>
    selectedDept.value
        ? props.postes.filter(p => p.departement?.id === selectedDept.value)
        : props.postes
);

// Reset poste quand le service change
const onDeptChange = () => { form.poste_id = ''; };

// ── Aperçu employé sélectionné ─────────────────────────────────────────────
const selectedUser = computed(() => props.users.find(u => u.id === form.user_id) ?? null);
const totalPrimes  = computed(() => {
    if (!selectedUser.value) return 0;
    const u = selectedUser.value;
    return (u.prime_transport ?? 0) + (u.prime_logement ?? 0) + (u.prime_fonction ?? 0)
         + (u.prime_rendement ?? 0) + (u.prime_panier ?? 0) + (u.bonus_annuel ?? 0);
});

// ── Aperçu poste sélectionné ───────────────────────────────────────────────
const selectedPoste = computed(() => props.postes.find(p => p.id === form.poste_id) ?? null);
</script>

<template>
    <Head title="Ajouter Affectation" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6">

            <!-- En-tête -->
            <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                        Nouvelle affectation
                    </h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">
                        L'ancienne affectation active sera automatiquement clôturée
                    </p>
                </div>
                <Link href="/affectations"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour
                </Link>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">

                <!-- ── Formulaire (2/3) ── -->
                <form @submit.prevent="submit"
                    class="xl:col-span-2 rounded-2xl border p-5 sm:p-6 space-y-5 transition-colors"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">

                    <!-- Ligne 1 : Employé + Service -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                Employé *
                            </label>
                            <select v-model="form.user_id" required
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2"
                                :class="inp(dark)">
                                <option value="">Choisir un employé</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">
                                    {{ u.nom }} {{ u.prenom }}
                                </option>
                            </select>
                            <p v-if="form.errors.user_id" class="text-xs text-rose-500 mt-1">{{ form.errors.user_id }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                Service / Département
                            </label>
                            <select v-model="selectedDept" @change="onDeptChange"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2"
                                :class="inp(dark)">
                                <option value="">Tous les services</option>
                                <option v-for="d in departements" :key="d.id" :value="d.id">
                                    {{ d.nom }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Poste (filtré par service) -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                            Poste *
                            <span v-if="selectedDept" class="ml-2 text-[10px] font-normal text-[#760078]">
                                — {{ postesFiltres.length }} poste(s) dans ce service
                            </span>
                        </label>
                        <select v-model="form.poste_id" required
                            class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2"
                            :class="inp(dark)">
                            <option value="">Choisir un poste</option>
                            <option v-for="p in postesFiltres" :key="p.id" :value="p.id">
                                {{ p.nom }}
                                <template v-if="!selectedDept && p.departement"> — {{ p.departement.nom }}</template>
                            </option>
                        </select>
                        <p v-if="form.errors.poste_id" class="text-xs text-rose-500 mt-1">{{ form.errors.poste_id }}</p>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                Date de prise de poste *
                            </label>
                            <input v-model="form.date_debut" type="date" required
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2"
                                :class="inp(dark)" />
                            <p v-if="form.errors.date_debut" class="text-xs text-rose-500 mt-1">{{ form.errors.date_debut }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                Date de fin <span class="font-normal opacity-60">(optionnel)</span>
                            </label>
                            <input v-model="form.date_fin" type="date"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2"
                                :class="inp(dark)" />
                        </div>
                    </div>

                    <!-- Motif -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                            Motif du changement
                            <span class="font-normal opacity-60">(promotion, transfert, nomination…)</span>
                        </label>
                        <textarea v-model="form.motif_changement" rows="3"
                            placeholder="Ex : Promotion au poste de Responsable suite à 2 ans de bons résultats"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2 resize-none"
                            :class="inp(dark)"></textarea>
                        <p v-if="form.errors.motif_changement" class="text-xs text-rose-500 mt-1">{{ form.errors.motif_changement }}</p>
                    </div>

                    <div class="pt-1">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all
                                   bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]
                                   disabled:opacity-60 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ form.processing ? 'Enregistrement...' : "Enregistrer l'affectation" }}
                        </button>
                    </div>
                </form>

                <!-- ── Récapitulatif (1/3) ── -->
                <div class="space-y-4">

                    <!-- Aperçu employé -->
                    <div class="rounded-2xl border p-4 transition-colors"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Employé sélectionné</p>

                        <template v-if="selectedUser">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black text-white shrink-0
                                            bg-gradient-to-br from-[#760078] to-[#7677B7]">
                                    {{ selectedUser.prenom?.charAt(0) }}{{ selectedUser.nom?.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">
                                        {{ selectedUser.nom }} {{ selectedUser.prenom }}
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1.5 border-b" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                                    <span class="text-xs" :class="sL(dark)">Salaire de base</span>
                                    <span class="text-xs font-bold text-[#760078]">{{ fmt(selectedUser.salaire_base) }}</span>
                                </div>
                                <div v-if="totalPrimes > 0" class="flex items-center justify-between py-1.5 border-b" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                                    <span class="text-xs" :class="sL(dark)">Total primes</span>
                                    <span class="text-xs font-bold text-[#7677B7]">{{ fmt(totalPrimes) }}</span>
                                </div>
                            </div>
                            <p class="text-[10px] mt-3 italic" :class="sL(dark)">
                                Ces montants seront capturés dans l'historique de carrière.
                            </p>
                        </template>
                        <div v-else class="py-4 text-center text-xs" :class="sL(dark)">
                            Sélectionnez un employé
                        </div>
                    </div>

                    <!-- Aperçu poste -->
                    <div v-if="selectedPoste"
                        class="rounded-2xl border p-4 transition-colors"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Poste sélectionné</p>
                        <p class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ selectedPoste.nom }}</p>
                        <p v-if="selectedPoste.departement" class="text-xs mt-1" :class="sL(dark)">
                            Service : {{ selectedPoste.departement.nom }}
                        </p>
                        <p v-if="selectedPoste.description" class="text-xs mt-2 italic" :class="sL(dark)">
                            {{ selectedPoste.description }}
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
