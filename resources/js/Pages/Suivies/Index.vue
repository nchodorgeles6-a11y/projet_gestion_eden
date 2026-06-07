<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const { dark } = useTheme();

const props = defineProps({
    presence:    { type: Object, default: () => ({}) },
    typeSuivies: { type: Array,  default: () => [] },
    motifs:      { type: Array,  default: () => [] },
    today:       { type: String, default: '' },
    todayRaw:    { type: String, default: '' },
});

// ── Filtres ──────────────────────────────────────────────────────────────────
const search     = ref('');
const filtreDept = ref('');

const departements = computed(() => {
    const set = new Set();
    (props.presence.data ?? []).forEach(p => { if (p.user?.departement) set.add(p.user.departement); });
    return [...set].sort();
});

const liste = computed(() => {
    const q = search.value.toLowerCase().trim();
    return (props.presence.data ?? []).filter(p => {
        const matchQ = !q ||
            p.user?.nom?.toLowerCase().includes(q) ||
            p.user?.prenom?.toLowerCase().includes(q) ||
            p.user?.poste?.toLowerCase().includes(q);
        const matchD = !filtreDept.value || p.user?.departement === filtreDept.value;
        return matchQ && matchD;
    });
});

// ── Stats ────────────────────────────────────────────────────────────────────
const stats = computed(() => {
    const total    = liste.value.length;
    const enConge  = liste.value.filter(p => isEnConge(p)).length;
    const absents  = liste.value.filter(p => isAbsent(p) && !isEnConge(p)).length;
    const retards  = liste.value.filter(p => isRetard(p)).length;
    const presents = total - enConge - absents - retards;
    return { total, presents, enConge, absents, retards };
});

// ── Helpers statut ───────────────────────────────────────────────────────────
const nomType        = (p) => p.type_suivie?.nom?.toLowerCase() ?? 'présent';
const isPresent      = (p) => !p.est_explicite || nomType(p).includes('pr');
const isAbsent       = (p) => p.est_explicite  && nomType(p).includes('abs');
const isRetard       = (p) => p.est_explicite  && nomType(p).includes('ret');
const isEnConge      = (p) => isAbsent(p) && !!p.conge;
const hasCongeNonLie = (p) => !p.est_explicite && !!p.conge_actif;

const typeStyle = (p) => {
    if (isEnConge(p)) return { bg: 'bg-blue-100',    text: 'text-blue-700',    dot: 'bg-blue-500',    border: 'border-blue-200'    };
    if (isAbsent(p))  return { bg: 'bg-rose-100',    text: 'text-rose-700',    dot: 'bg-rose-500',    border: 'border-rose-200'    };
    if (isRetard(p))  return { bg: 'bg-amber-100',   text: 'text-amber-700',   dot: 'bg-amber-500',   border: 'border-amber-200'   };
    return                   { bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500', border: 'border-emerald-200' };
};
const typeLabel = (p) => {
    if (isEnConge(p)) return 'En congé';
    if (isAbsent(p))  return 'Absent';
    if (isRetard(p))  return 'Retard';
    return 'Présent';
};

// ── Modal ─────────────────────────────────────────────────────────────────────
const modalOpen = ref(false);
const selected  = ref(null);
const permTab   = ref('select'); // 'select' | 'create'
const categorie = ref('');       // 'conge' | 'permission' | 'maladie' | 'autre'

const form = useForm({
    user_id:              '',
    type_suivie_id:       '',
    date:                 '',
    motif_id:             '',
    justifiee:            false,
    conge_id:             '',
    permission_id:        '',
    revenir_present:      false,
    new_permission_debut: '',
    new_permission_jours: '',
    new_permission_notes: '',
});

// ── Motifs filtrés par catégorie ─────────────────────────────────────────────
const motifsMaladie = computed(() => props.motifs.filter(m => m.type === 'maladie'));
const motifsAutre   = computed(() => props.motifs.filter(m => m.type === 'autre'));
const motifsRetard  = computed(() => props.motifs.filter(m => m.type === 'retard'));

// ── Type sélectionné ─────────────────────────────────────────────────────────
const isTypeRetard = computed(() => {
    const t = props.typeSuivies.find(t => t.id === form.type_suivie_id);
    return t?.nom?.toLowerCase().includes('ret') ?? false;
});

// ── Congé sélectionné et statut de paiement ──────────────────────────────────
const congeSelectionne = computed(() =>
    (selected.value?.conges_disponibles ?? []).find(c => c.id === form.conge_id)
);
const estCongePayé = computed(() => congeSelectionne.value?.statut === 'approuve');

// ── Changement de catégorie ───────────────────────────────────────────────────
const onCategorieChange = (cat) => {
    categorie.value    = cat;
    form.motif_id      = '';
    form.conge_id      = '';
    form.permission_id = '';
    permTab.value      = 'select';

    if (cat === 'conge' && selected.value) {
        const actif = (selected.value.conges_disponibles ?? []).find(c => c.is_active);
        if (actif) {
            form.conge_id  = actif.id;
            if (actif.motif_id) form.motif_id = actif.motif_id;
            form.justifiee = actif.statut === 'approuve';
        }
    }
    if (cat === 'permission' && selected.value) {
        const actif = (selected.value.permissions_disponibles ?? []).find(p => p.is_active);
        if (actif) form.permission_id = actif.id;
        else permTab.value = 'create';
    }
};

// ── Ouverture du modal ────────────────────────────────────────────────────────
const ouvrirModal = (p) => {
    selected.value            = p;
    permTab.value             = 'select';
    categorie.value           = '';
    form.user_id              = p.user.id;
    form.date                 = props.todayRaw;
    form.justifiee            = p.justifiee ?? false;
    form.conge_id             = p.conge?.id ?? '';
    form.permission_id        = p.permission?.id ?? p.permission_active_id ?? '';
    form.revenir_present      = false;
    form.new_permission_debut = props.todayRaw;
    form.new_permission_jours = '';
    form.new_permission_notes = '';

    const typeAbsent = props.typeSuivies.find(t => t.nom?.toLowerCase().includes('abs'));
    const typeRetard = props.typeSuivies.find(t => t.nom?.toLowerCase().includes('ret'));

    if (isAbsent(p)) {
        form.type_suivie_id = typeAbsent?.id ?? '';
        form.motif_id       = p.motif?.id ?? '';
        if      (p.conge)                          categorie.value = 'conge';
        else if (p.permission)                     categorie.value = 'permission';
        else if (p.motif?.type === 'maladie')      categorie.value = 'maladie';
        else if (p.motif?.type === 'autre')        categorie.value = 'autre';
        else if (p.motif?.type === 'conge')        categorie.value = 'conge';
        else if (p.motif?.type === 'permission')   categorie.value = 'permission';
    } else if (isRetard(p)) {
        form.type_suivie_id = typeRetard?.id ?? '';
        form.motif_id       = p.motif?.id ?? '';
        // les motifs type 'retard' s'affichent dans la carte "autre"
        if      (p.motif?.type === 'retard' || p.motif?.type === 'autre') categorie.value = 'autre';
        else if (p.motif?.type === 'maladie')    categorie.value = 'maladie';
        else if (p.motif?.type === 'permission') categorie.value = 'permission';
    } else {
        form.type_suivie_id = typeAbsent?.id ?? '';
        form.motif_id       = '';
        if (p.conge_actif) {
            categorie.value = 'conge';
            form.conge_id   = p.conge_actif.id;
            if (p.conge_actif.motif_id) form.motif_id = p.conge_actif.motif_id;
            form.justifiee  = true;
        }
    }

    modalOpen.value = true;
};

const fermerModal = () => { modalOpen.value = false; selected.value = null; };

const dateFin = computed(() => {
    if (!form.new_permission_debut || !form.new_permission_jours) return '';
    const d = new Date(form.new_permission_debut);
    d.setDate(d.getDate() + Number(form.new_permission_jours) - 1);
    return d.toLocaleDateString('fr-FR');
});

const soumettre = () => {
    form.post('/suivies/changer-statut', { onSuccess: fermerModal });
};

const revenirPresent = () => {
    form.revenir_present = true;
    form.post('/suivies/changer-statut', { onSuccess: fermerModal });
};

// ── Styles utilitaires ───────────────────────────────────────────────────────
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';
</script>

<template>
    <Head title="Présence du jour" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 min-h-full w-full">

            <!-- En-tête -->
            <div>
                <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                    Présence du jour
                </h2>
                <p class="text-xs mt-0.5 font-semibold capitalize text-[#760078]">{{ today }}</p>
                <p class="text-[11px] mt-0.5" :class="sL(dark)">
                    Tous les employés sont présents par défaut — marquez uniquement les absences et retards.
                </p>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">
                <div v-for="(item, i) in [
                    { label: 'Total',    val: stats.total,    color: 'text-slate-600' },
                    { label: 'Présents', val: stats.presents, color: 'text-emerald-600', bar: 'bg-emerald-500' },
                    { label: 'En congé', val: stats.enConge,  color: 'text-blue-600',    bar: 'bg-blue-500'    },
                    { label: 'Absents',  val: stats.absents,  color: 'text-rose-600',    bar: 'bg-rose-500'    },
                    { label: 'Retards',  val: stats.retards,  color: 'text-amber-600',   bar: 'bg-amber-500'   },
                ]" :key="i"
                    class="rounded-2xl border p-4 shadow-sm"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200'">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">{{ item.label }}</p>
                    <p class="text-3xl font-black" :class="item.color">{{ item.val }}</p>
                    <div v-if="item.bar && stats.total > 0" class="mt-2 h-1.5 rounded-full" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                        <div class="h-1.5 rounded-full transition-all" :class="item.bar"
                            :style="{ width: Math.round((item.val / stats.total) * 100) + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Nom, poste…"
                        class="pl-8 pr-3 py-1.5 rounded-xl border text-xs transition-all focus:outline-none focus:ring-2 w-52"
                        :class="inp(dark)" />
                </div>
                <select v-model="filtreDept" class="rounded-xl border px-3 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                    <option value="">Tous les départements</option>
                    <option v-for="d in departements" :key="d" :value="d">{{ d }}</option>
                </select>
                <button v-if="search || filtreDept" @click="search = ''; filtreDept = ''"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    Réinitialiser
                </button>
            </div>

            <!-- Tableau -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Employé</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Poste / Département</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Statut</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Motif / Document</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Justifiée</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="liste.length === 0">
                            <td colspan="6" class="py-12 text-center text-sm" :class="sL(dark)">Aucun employé trouvé.</td>
                        </tr>
                        <tr v-for="(p, i) in liste" :key="p.user.id"
                            class="row-anim border-t transition-colors"
                            :style="{ animationDelay: i * 35 + 'ms' }"
                            :class="[div(dark),
                                dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50/80',
                                isAbsent(p) ? (dark ? 'bg-rose-900/5' : 'bg-rose-50/40') : '',
                                isRetard(p) ? (dark ? 'bg-amber-900/5' : 'bg-amber-50/40') : '']">

                            <!-- Employé -->
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-[11px] font-black text-white shrink-0 bg-gradient-to-br from-[#760078] to-[#7677B7]">
                                        {{ p.user.prenom?.charAt(0) }}{{ p.user.nom?.charAt(0) }}
                                    </div>
                                    <div>
                                        <Link :href="`/users/${p.user.id}`" class="text-xs font-bold hover:text-[#760078] transition-colors" :class="dark ? 'text-slate-100' : 'text-slate-800'">
                                            {{ p.user.nom }} {{ p.user.prenom }}
                                        </Link>
                                        <p class="text-[10px]" :class="sL(dark)">{{ p.user.email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Poste -->
                            <td class="px-5 py-3">
                                <p class="text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ p.user.poste ?? '—' }}</p>
                                <p class="text-[10px]" :class="sL(dark)">{{ p.user.departement ?? '' }}</p>
                            </td>

                            <!-- Statut -->
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                    :class="typeStyle(p).bg + ' ' + typeStyle(p).text + ' ' + typeStyle(p).border">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="typeStyle(p).dot"></span>
                                    {{ typeLabel(p) }}
                                </span>
                                <div v-if="hasCongeNonLie(p)"
                                    class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-600 border border-blue-200">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Congé en cours
                                </div>
                            </td>

                            <!-- Motif / Document -->
                            <td class="px-5 py-3">
                                <template v-if="isAbsent(p) || isRetard(p)">
                                    <span v-if="p.motif" class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-semibold"
                                        :class="dark ? 'bg-[#21262d] text-slate-300' : 'bg-slate-100 text-slate-600'">
                                        {{ p.motif.nom }}
                                    </span>
                                    <div v-if="p.conge" class="mt-1.5 rounded-lg px-2 py-1 border text-[10px]"
                                        :class="dark ? 'bg-blue-900/10 border-blue-500/20 text-blue-300' : 'bg-blue-50 border-blue-200 text-blue-700'">
                                        <div class="flex items-center gap-1 font-bold">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ p.conge.motif?.nom ?? 'Congé' }}
                                        </div>
                                        <p class="mt-0.5 opacity-80">Du {{ p.conge.date_debut?.substring(0,10) }} au {{ p.conge.date_fin?.substring(0,10) ?? '—' }}</p>
                                    </div>
                                    <div v-if="p.permission" class="mt-1 text-[10px] flex items-center gap-1 text-violet-500">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Permission {{ p.permission.nombre_jours }}j
                                    </div>
                                </template>
                                <span v-else class="text-[10px]" :class="sL(dark)">—</span>
                            </td>

                            <!-- Justifiée -->
                            <td class="px-5 py-3 text-center">
                                <template v-if="isAbsent(p) || isRetard(p)">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="p.justifiee ? 'bg-blue-100 text-blue-700' : dark ? 'bg-[#21262d] text-slate-500' : 'bg-slate-100 text-slate-400'">
                                        {{ p.justifiee ? '✓ Oui' : '✗ Non' }}
                                    </span>
                                </template>
                                <span v-else class="text-[10px]" :class="sL(dark)">—</span>
                            </td>

                            <!-- Action -->
                            <td class="px-5 py-3 text-right">
                                <button @click="ouvrirModal(p)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                                    :class="isPresent(p)
                                        ? dark ? 'border-[#30363d] text-slate-400 hover:border-rose-500/40 hover:text-rose-400' : 'border-slate-200 text-slate-500 hover:border-rose-300 hover:text-rose-600'
                                        : dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    {{ isPresent(p) ? 'Marquer absent' : 'Modifier' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                </div>
                <div class="px-5 py-2 border-t flex items-center" :class="[div(dark), dark ? 'bg-[#0d1117]' : 'bg-slate-50']">
                    <p class="text-[10px]" :class="sL(dark)">{{ liste.length }} employé(s) sur cette page</p>
                </div>
            </div>

            <Pagination :links="presence.links" :meta="presence" />
        </div>

        <!-- ══════════ MODAL ══════════ -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
                        leave-active-class="transition duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="fermerModal"></div>

                    <div class="relative w-full max-w-lg rounded-2xl border shadow-2xl z-10 max-h-[90vh] flex flex-col"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200'">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b shrink-0" :class="div(dark)">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[11px] font-black text-white bg-gradient-to-br from-[#760078] to-[#7677B7]">
                                    {{ selected?.user.prenom?.charAt(0) }}{{ selected?.user.nom?.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">
                                        {{ selected?.user.nom }} {{ selected?.user.prenom }}
                                    </p>
                                    <p class="text-[10px]" :class="sL(dark)">{{ today }}</p>
                                </div>
                            </div>
                            <button @click="fermerModal" class="p-1.5 rounded-lg transition-colors"
                                :class="dark ? 'text-slate-400 hover:text-white hover:bg-[#21262d]' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-100'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Body scrollable -->
                        <div class="overflow-y-auto flex-1 p-6 space-y-5">

                            <!-- Statut actuel -->
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">Statut actuel</p>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border"
                                    :class="typeStyle(selected).bg + ' ' + typeStyle(selected).text + ' ' + typeStyle(selected).border">
                                    <span class="w-2 h-2 rounded-full" :class="typeStyle(selected).dot"></span>
                                    {{ typeLabel(selected) }}
                                </span>
                            </div>

                            <!-- Choix du nouveau statut -->
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">Nouveau statut</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <button v-for="t in typeSuivies.filter(t => !t.nom?.toLowerCase().includes('pr'))" :key="t.id"
                                        type="button"
                                        @click="form.type_suivie_id = t.id; form.motif_id = ''; form.conge_id = ''; form.permission_id = ''; permTab = 'select'; categorie = ''"
                                        class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                        :class="form.type_suivie_id === t.id
                                            ? t.nom?.toLowerCase().includes('abs') ? 'border-rose-500 bg-rose-50' : 'border-amber-500 bg-amber-50'
                                            : dark ? 'border-[#30363d] hover:border-slate-500' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="w-3 h-3 rounded-full shrink-0"
                                            :class="t.nom?.toLowerCase().includes('abs') ? 'bg-rose-500' : 'bg-amber-500'"></div>
                                        <span class="text-sm font-bold"
                                            :class="form.type_suivie_id === t.id
                                                ? t.nom?.toLowerCase().includes('abs') ? 'text-rose-700' : 'text-amber-700'
                                                : dark ? 'text-white' : 'text-slate-800'">
                                            {{ t.nom }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- ── CATÉGORIE ─────────────────────────────────────────── -->
                            <div v-if="form.type_suivie_id">
                                <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="sL(dark)">
                                    {{ isTypeRetard ? 'Motif du retard' : 'Catégorie' }}
                                </p>
                                <div class="grid gap-2" :class="isTypeRetard ? 'grid-cols-3' : 'grid-cols-2'">
                                    <!-- Congé — uniquement pour les absences (pas les retards) -->
                                    <button v-if="!isTypeRetard" type="button" @click="onCategorieChange('conge')"
                                        class="flex flex-col items-start gap-1.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                        :class="categorie === 'conge'
                                            ? 'border-blue-500 bg-blue-50'
                                            : dark ? 'border-[#30363d] hover:border-blue-500/40' : 'border-slate-200 hover:border-blue-300'">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                :class="categorie === 'conge' ? 'bg-blue-500' : dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                                                <svg class="w-3.5 h-3.5" :class="categorie === 'conge' ? 'text-white' : dark ? 'text-slate-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold" :class="categorie === 'conge' ? 'text-blue-700' : dark ? 'text-white' : 'text-slate-800'">Congé</span>
                                        </div>
                                        <p class="text-[10px] leading-snug pl-9" :class="categorie === 'conge' ? 'text-blue-500' : sL(dark)">Annuel, maternité, maladie…</p>
                                    </button>

                                    <!-- Permission -->
                                    <button type="button" @click="onCategorieChange('permission')"
                                        class="flex flex-col items-start gap-1.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                        :class="categorie === 'permission'
                                            ? 'border-violet-500 bg-violet-50'
                                            : dark ? 'border-[#30363d] hover:border-violet-500/40' : 'border-slate-200 hover:border-violet-300'">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                :class="categorie === 'permission' ? 'bg-violet-500' : dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                                                <svg class="w-3.5 h-3.5" :class="categorie === 'permission' ? 'text-white' : dark ? 'text-slate-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold" :class="categorie === 'permission' ? 'text-violet-700' : dark ? 'text-white' : 'text-slate-800'">Permission</span>
                                        </div>
                                        <p class="text-[10px] leading-snug pl-9" :class="categorie === 'permission' ? 'text-violet-500' : sL(dark)">Exceptionnelle, autorisée</p>
                                    </button>

                                    <!-- Maladie -->
                                    <button type="button" @click="onCategorieChange('maladie')"
                                        class="flex flex-col items-start gap-1.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                        :class="categorie === 'maladie'
                                            ? 'border-rose-500 bg-rose-50'
                                            : dark ? 'border-[#30363d] hover:border-rose-500/40' : 'border-slate-200 hover:border-rose-300'">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                :class="categorie === 'maladie' ? 'bg-rose-500' : dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                                                <svg class="w-3.5 h-3.5" :class="categorie === 'maladie' ? 'text-white' : dark ? 'text-slate-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold" :class="categorie === 'maladie' ? 'text-rose-700' : dark ? 'text-white' : 'text-slate-800'">Maladie</span>
                                        </div>
                                        <p class="text-[10px] leading-snug pl-9" :class="categorie === 'maladie' ? 'text-rose-500' : sL(dark)">Certificat, accident, hospit.</p>
                                    </button>

                                    <!-- Autre / Raison du retard -->
                                    <button type="button" @click="onCategorieChange('autre')"
                                        class="flex flex-col items-start gap-1.5 px-4 py-3 rounded-xl border-2 text-left transition-all"
                                        :class="categorie === 'autre'
                                            ? 'border-slate-500 bg-slate-50'
                                            : dark ? 'border-[#30363d] hover:border-slate-500/40' : 'border-slate-200 hover:border-slate-400'">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                :class="categorie === 'autre' ? 'bg-slate-500' : dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                                                <svg class="w-3.5 h-3.5" :class="categorie === 'autre' ? 'text-white' : dark ? 'text-slate-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold" :class="categorie === 'autre' ? 'text-slate-700' : dark ? 'text-white' : 'text-slate-800'">
                                                {{ isTypeRetard ? 'Raison' : 'Autre motif' }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] leading-snug pl-9" :class="categorie === 'autre' ? 'text-slate-500' : sL(dark)">
                                            {{ isTypeRetard ? 'Transport, urgence, force majeure…' : 'Mission, télétravail…' }}
                                        </p>
                                    </button>
                                </div>
                            </div>

                            <!-- ── PANEL CONGÉ ──────────────────────────────────────── -->
                            <div v-if="categorie === 'conge'"
                                class="rounded-xl border-2 p-4 space-y-3"
                                :class="dark ? 'border-blue-500/30 bg-blue-900/5' : 'border-blue-200 bg-blue-50/50'">

                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-xs font-bold" :class="dark ? 'text-blue-300' : 'text-blue-700'">Congé associé</p>
                                </div>

                                <!-- Badge congé payé -->
                                <div v-if="estCongePayé"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Congé payé — aucune déduction salariale
                                </div>

                                <template v-if="selected?.conges_disponibles?.length">
                                    <div class="space-y-2 max-h-44 overflow-y-auto">
                                        <label v-for="c in selected.conges_disponibles" :key="c.id"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-all"
                                            :class="form.conge_id === c.id
                                                ? 'border-blue-500 bg-blue-50'
                                                : dark ? 'border-[#30363d] hover:border-blue-500/40' : 'border-slate-200 hover:border-blue-400/40'">
                                            <input type="radio" :value="c.id" v-model="form.conge_id" class="hidden"
                                                @change="if (c.motif_id) form.motif_id = c.motif_id; form.justifiee = c.statut === 'approuve'" />
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                                :class="form.conge_id === c.id ? 'border-blue-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                                <div v-if="form.conge_id === c.id" class="w-2 h-2 rounded-full bg-blue-500"></div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs font-semibold flex items-center gap-1.5 flex-wrap" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                                    {{ c.motif ?? 'Congé' }}
                                                    <span v-if="c.is_active" class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700">En cours</span>
                                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                                                        :class="c.statut === 'approuve'
                                                            ? 'bg-blue-100 text-blue-700'
                                                            : c.statut === 'refuse'
                                                                ? 'bg-rose-100 text-rose-700'
                                                                : 'bg-amber-100 text-amber-700'">
                                                        {{ c.statut === 'approuve' ? 'Approuvé' : c.statut === 'refuse' ? 'Refusé' : 'En attente' }}
                                                    </span>
                                                </p>
                                                <p class="text-[10px]" :class="sL(dark)">Du {{ c.date_debut }} au {{ c.date_fin ?? '—' }}</p>
                                            </div>
                                        </label>
                                    </div>
                                </template>
                                <div v-else class="text-[11px]" :class="sL(dark)">
                                    Aucun congé enregistré.
                                    <Link href="/conges/create" class="text-[#760078] hover:underline ml-1">Créer un congé →</Link>
                                </div>
                            </div>

                            <!-- ── PANEL PERMISSION ─────────────────────────────────── -->
                            <div v-if="categorie === 'permission'"
                                class="rounded-xl border-2 p-4 space-y-3"
                                :class="dark ? 'border-violet-500/30 bg-violet-900/5' : 'border-violet-200 bg-violet-50/50'">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <p class="text-xs font-bold" :class="dark ? 'text-violet-300' : 'text-violet-700'">Permission</p>
                                    </div>
                                    <div class="flex rounded-lg overflow-hidden border text-[10px] font-bold"
                                        :class="dark ? 'border-[#30363d]' : 'border-slate-200'">
                                        <button type="button" @click="permTab = 'select'"
                                            class="px-3 py-1 transition-colors"
                                            :class="permTab === 'select' ? 'bg-violet-500 text-white' : dark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-800'">
                                            Sélectionner
                                        </button>
                                        <button type="button" @click="permTab = 'create'; form.permission_id = ''"
                                            class="px-3 py-1 transition-colors"
                                            :class="permTab === 'create' ? 'bg-violet-500 text-white' : dark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-800'">
                                            + Créer
                                        </button>
                                    </div>
                                </div>

                                <template v-if="permTab === 'select'">
                                    <template v-if="selected?.permissions_disponibles?.length">
                                        <div class="space-y-2 max-h-40 overflow-y-auto">
                                            <label v-for="perm in selected.permissions_disponibles" :key="perm.id"
                                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-all"
                                                :class="form.permission_id === perm.id
                                                    ? 'border-violet-500 bg-violet-50'
                                                    : dark ? 'border-[#30363d] hover:border-violet-500/40' : 'border-slate-200 hover:border-violet-400/40'">
                                                <input type="radio" :value="perm.id" v-model="form.permission_id" class="hidden" />
                                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                                    :class="form.permission_id === perm.id ? 'border-violet-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                                    <div v-if="form.permission_id === perm.id" class="w-2 h-2 rounded-full bg-violet-500"></div>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs font-semibold flex items-center gap-1.5" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                                        {{ perm.nombre_jours }} jour(s)
                                                        <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                                                            :class="perm.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                                            {{ perm.is_active ? 'Active' : perm.statut }}
                                                        </span>
                                                    </p>
                                                    <p class="text-[10px]" :class="sL(dark)">Du {{ perm.date_debut }} au {{ perm.date_fin }}</p>
                                                    <p v-if="perm.notes" class="text-[10px] italic mt-0.5" :class="sL(dark)">{{ perm.notes }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </template>
                                    <div v-else class="text-[11px] text-center py-2" :class="sL(dark)">
                                        Aucune permission existante.
                                        <button type="button" @click="permTab = 'create'" class="text-violet-500 hover:underline ml-1">En créer une →</button>
                                    </div>
                                </template>

                                <template v-if="permTab === 'create'">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">Date de début</label>
                                            <input type="date" v-model="form.new_permission_debut"
                                                class="w-full rounded-xl border px-3 py-2 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">Nombre de jours</label>
                                            <input type="number" v-model="form.new_permission_jours" min="1" max="365" placeholder="ex: 3"
                                                class="w-full rounded-xl border px-3 py-2 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">Notes (optionnel)</label>
                                        <textarea v-model="form.new_permission_notes" rows="2" placeholder="Raison de la permission…"
                                            class="w-full rounded-xl border px-3 py-2 text-xs resize-none transition-all focus:outline-none focus:ring-2" :class="inp(dark)"></textarea>
                                    </div>
                                    <p v-if="dateFin" class="text-[10px] font-semibold text-violet-500">
                                        → Date de fin calculée : {{ dateFin }}
                                    </p>
                                </template>
                            </div>

                            <!-- ── PANEL MALADIE ────────────────────────────────────── -->
                            <div v-if="categorie === 'maladie'"
                                class="rounded-xl border-2 p-4 space-y-3"
                                :class="dark ? 'border-rose-500/30 bg-rose-900/5' : 'border-rose-200 bg-rose-50/50'">

                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <p class="text-xs font-bold" :class="dark ? 'text-rose-300' : 'text-rose-700'">Type de maladie / accident</p>
                                </div>

                                <div class="space-y-2">
                                    <label v-for="m in motifsMaladie" :key="m.id"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-all"
                                        :class="form.motif_id === m.id
                                            ? 'border-rose-500 bg-rose-50'
                                            : dark ? 'border-[#30363d] hover:border-rose-500/40' : 'border-slate-200 hover:border-rose-400/40'">
                                        <input type="radio" :value="m.id" v-model="form.motif_id" class="hidden" />
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                            :class="form.motif_id === m.id ? 'border-rose-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                            <div v-if="form.motif_id === m.id" class="w-2 h-2 rounded-full bg-rose-500"></div>
                                        </div>
                                        <span class="text-xs font-semibold" :class="form.motif_id === m.id ? 'text-rose-700' : dark ? 'text-slate-200' : 'text-slate-700'">
                                            {{ m.nom }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- ── PANEL AUTRE / RETARD ───────────────────────────── -->
                            <div v-if="categorie === 'autre'"
                                class="rounded-xl border-2 p-4 space-y-3"
                                :class="dark ? 'border-slate-500/30 bg-slate-800/20' : 'border-slate-300 bg-slate-50'">

                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                                    </svg>
                                    <p class="text-xs font-bold" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                        {{ isTypeRetard ? 'Raison du retard' : 'Motif' }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label v-for="m in (isTypeRetard ? motifsRetard : motifsAutre)" :key="m.id"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-all"
                                        :class="form.motif_id === m.id
                                            ? 'border-slate-500 bg-slate-100'
                                            : dark ? 'border-[#30363d] hover:border-slate-500/40' : 'border-slate-200 hover:border-slate-400/40'">
                                        <input type="radio" :value="m.id" v-model="form.motif_id" class="hidden" />
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                            :class="form.motif_id === m.id ? 'border-slate-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                            <div v-if="form.motif_id === m.id" class="w-2 h-2 rounded-full bg-slate-500"></div>
                                        </div>
                                        <span class="text-xs font-semibold" :class="form.motif_id === m.id ? 'text-slate-700' : dark ? 'text-slate-200' : 'text-slate-700'">
                                            {{ m.nom }}
                                        </span>
                                    </label>
                                    <p v-if="isTypeRetard && !motifsRetard.length" class="text-[11px] text-center py-2" :class="sL(dark)">
                                        Aucun motif de retard disponible. Lancez le seeder.
                                    </p>
                                </div>
                            </div>

                            <!-- Absence justifiée -->
                            <div v-if="form.type_suivie_id">
                                <button type="button" @click="form.justifiee = !form.justifiee"
                                    class="flex items-center gap-3 w-full px-4 py-3 rounded-xl border-2 text-left transition-all"
                                    :class="form.justifiee
                                        ? 'border-blue-500 bg-blue-50'
                                        : dark ? 'border-[#30363d] hover:border-blue-500/40' : 'border-slate-200 hover:border-blue-400/40'">
                                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                        :class="form.justifiee ? 'border-blue-500 bg-blue-500' : dark ? 'border-slate-600' : 'border-slate-300'">
                                        <svg v-if="form.justifiee" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold" :class="form.justifiee ? 'text-blue-700' : dark ? 'text-white' : 'text-slate-800'">
                                            Absence justifiée
                                        </p>
                                        <p class="text-[10px]" :class="sL(dark)">Un justificatif a été fourni ou est attendu</p>
                                    </div>
                                </button>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between gap-3 px-6 py-4 border-t shrink-0" :class="div(dark)">
                            <button v-if="selected && !isPresent(selected)"
                                type="button" @click="revenirPresent"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all disabled:opacity-50"
                                :class="dark ? 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10' : 'border-emerald-400 text-emerald-700 hover:bg-emerald-50'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Rétablir présent
                            </button>
                            <div v-else></div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="fermerModal"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all"
                                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                                    Annuler
                                </button>
                                <button type="button" @click="soumettre"
                                    :disabled="form.processing || !form.type_suivie_id"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-40 bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>

    </AuthenticatedLayout>
</template>
