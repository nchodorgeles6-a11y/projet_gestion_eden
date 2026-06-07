<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';

const { dark } = useTheme();

const props = defineProps({
    lignes:       { type: Array,  default: () => [] },
    totaux:       { type: Object, default: () => ({}) },
    departements: { type: Array,  default: () => [] },
    categories:   { type: Array,  default: () => [] },
    annees:       { type: Array,  default: () => [] },
    filtres:      { type: Object, default: () => ({}) },
});

// ── Filtres page ─────────────────────────────────────────────────────────────
const annee = ref(props.filtres.annee ?? new Date().getFullYear());
const mois  = ref(props.filtres.mois  ?? '');

const moisLabels = [
    { v: '',  l: 'Budget annuel' },
    { v: 1,  l: 'Janvier' }, { v: 2,  l: 'Février' },  { v: 3,  l: 'Mars' },
    { v: 4,  l: 'Avril' },  { v: 5,  l: 'Mai' },       { v: 6,  l: 'Juin' },
    { v: 7,  l: 'Juillet' },{ v: 8,  l: 'Août' },      { v: 9,  l: 'Septembre' },
    { v: 10, l: 'Octobre' },{ v: 11, l: 'Novembre' },  { v: 12, l: 'Décembre' },
];

const appliquer = () => {
    router.get('/budgets', { annee: annee.value, mois: mois.value || undefined }, { preserveScroll: true });
};

// ── Formulaire ajout budget ───────────────────────────────────────────────────
const showForm = ref(false);
const form = useForm({
    departement_id: '',
    annee:          props.filtres.annee,
    mois:           props.filtres.mois || '',
    categorie:      '',
    montant:        '',
    description:    '',
});

const soumettre = () => {
    form.post('/budgets', {
        onSuccess: () => { showForm.value = false; form.reset(); },
    });
};

const supprimer = (id) => {
    if (!confirm('Supprimer ce budget ?')) return;
    router.delete(`/budgets/${id}`);
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(Math.round(n)) : '0';

const ecartClass = (ecart) => {
    if (ecart > 0) return dark.value ? 'text-emerald-400' : 'text-emerald-600';
    if (ecart < 0) return dark.value ? 'text-rose-400'    : 'text-rose-600';
    return dark.value ? 'text-slate-400' : 'text-slate-500';
};

const pctBar = (pct) => Math.min(100, Math.max(0, pct));
const pctColor = (pct) => {
    if (pct <= 75) return 'bg-emerald-500';
    if (pct <= 95) return 'bg-amber-500';
    return 'bg-rose-500';
};

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-200';
</script>

<template>
    <Head title="Budgets prévisionnels" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">

            <!-- En-tête -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Budgets prévisionnels</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Comparaison prévisionnel / réalisé par catégorie de charges</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <select v-model="annee" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
                    </select>
                    <select v-model="mois" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option v-for="m in moisLabels" :key="m.v" :value="m.v">{{ m.l }}</option>
                    </select>
                    <button @click="appliquer"
                        class="px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                        Appliquer
                    </button>
                    <button @click="showForm = !showForm"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter un budget
                    </button>
                </div>
            </div>

            <!-- Totaux -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div v-for="(item, i) in [
                    { label: 'Budget total',   val: totaux.budget,  color: dark ? 'text-[#7677B7]' : 'text-[#760078]' },
                    { label: 'Réalisé',        val: totaux.realise, color: dark ? 'text-slate-200' : 'text-slate-800' },
                    { label: 'Écart restant',  val: totaux.ecart,   color: totaux.ecart >= 0 ? (dark ? 'text-emerald-400' : 'text-emerald-600') : (dark ? 'text-rose-400' : 'text-rose-600') },
                ]" :key="i"
                    class="rounded-2xl border p-5"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">{{ item.label }}</p>
                    <p class="text-xl font-black" :class="item.color">{{ fmt(item.val) }} FCFA</p>
                </div>
            </div>

            <!-- Formulaire ajout (collapse) -->
            <Transition name="nav-acc">
                <div v-if="showForm"
                    class="rounded-2xl border p-5 transition-colors"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <h3 class="text-sm font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Nouveau budget</h3>
                    <form @submit.prevent="soumettre" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Catégorie *</label>
                            <select v-model="form.categorie" required class="w-full rounded-xl border px-3 py-1.5 text-xs focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="">Choisir…</option>
                                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                            </select>
                            <p v-if="form.errors.categorie" class="text-[10px] text-rose-500 mt-1">{{ form.errors.categorie }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Montant prévisionnel *</label>
                            <input v-model="form.montant" type="number" min="0" step="1000" required
                                class="w-full rounded-xl border px-3 py-1.5 text-xs focus:outline-none focus:ring-2" :class="inp(dark)" placeholder="0" />
                            <p v-if="form.errors.montant" class="text-[10px] text-rose-500 mt-1">{{ form.errors.montant }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Département</label>
                            <select v-model="form.departement_id" class="w-full rounded-xl border px-3 py-1.5 text-xs focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="">Tous / Global</option>
                                <option v-for="d in departements" :key="d.id" :value="d.id">{{ d.nom }}</option>
                            </select>
                        </div>
                        <div class="xl:col-span-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Description</label>
                            <input v-model="form.description" type="text"
                                class="w-full rounded-xl border px-3 py-1.5 text-xs focus:outline-none focus:ring-2" :class="inp(dark)" placeholder="Notes optionnelles…" />
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] transition-all disabled:opacity-50">
                                {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                            </button>
                            <button type="button" @click="showForm = false"
                                class="px-4 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                                :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-900'">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </Transition>

            <!-- Tableau budgets vs réalisé -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <table class="w-full text-sm">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Catégorie</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Département</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-[#760078]">Budget</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Réalisé</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Avancement</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Écart</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!lignes.length">
                            <td colspan="7" class="py-12 text-center text-xs" :class="sL(dark)">
                                Aucun budget configuré pour cette période.
                                <button @click="showForm = true" class="ml-2 text-[#760078] font-semibold hover:underline">Ajouter →</button>
                            </td>
                        </tr>
                        <tr v-for="(ligne, i) in lignes" :key="ligne.id"
                            class="row-anim border-t transition-colors"
                            :style="{ animationDelay: i * 30 + 'ms' }"
                            :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50']">
                            <td class="px-5 py-3">
                                <p class="text-xs font-semibold" :class="dark ? 'text-slate-200' : 'text-slate-800'">{{ ligne.categorie }}</p>
                                <p v-if="ligne.description" class="text-[10px] mt-0.5" :class="sL(dark)">{{ ligne.description }}</p>
                            </td>
                            <td class="px-5 py-3 text-xs" :class="sL(dark)">{{ ligne.departement ?? 'Global' }}</td>
                            <td class="px-5 py-3 text-right text-xs font-bold text-[#760078]">{{ fmt(ligne.montant) }}</td>
                            <td class="px-5 py-3 text-right text-xs font-semibold" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ fmt(ligne.realise) }}</td>
                            <td class="px-5 py-3 w-36">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 rounded-full" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                                        <div class="h-1.5 rounded-full transition-all" :class="pctColor(ligne.pct)"
                                            :style="{ width: pctBar(ligne.pct) + '%' }"></div>
                                    </div>
                                    <span class="text-[10px] font-bold w-8 text-right" :class="pctColor(ligne.pct).replace('bg-', 'text-')">{{ ligne.pct }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right text-xs font-bold" :class="ecartClass(ligne.ecart)">
                                {{ ligne.ecart >= 0 ? '+' : '' }}{{ fmt(ligne.ecart) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <button @click="supprimer(ligne.id)"
                                    class="p-1.5 rounded-lg transition-colors"
                                    :class="dark ? 'text-slate-500 hover:text-rose-400 hover:bg-rose-900/20' : 'text-slate-400 hover:text-rose-600 hover:bg-rose-50'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
