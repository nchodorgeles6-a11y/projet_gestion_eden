<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { computed, ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import AnimatedStat from '@/Components/AnimatedStat.vue';

const { dark } = useTheme();
const page = usePage();

const props = defineProps({
    conges:    { type: Object, default: () => ({}) },
    kpisTotal: { type: Object, default: () => ({}) },
    statut:    { type: String, default: '' },
});

// ── Filtre statut (server-side) ───────────────────────────────────────────────
const filtreStatut = ref(props.statut);

const appliquerFiltre = (val) => {
    filtreStatut.value = val === filtreStatut.value ? '' : val;
    router.get('/conges', { statut: filtreStatut.value }, { preserveState: true, replace: true });
};

const liste = computed(() => props.conges.data ?? []);

// ── KPIs (totaux globaux depuis le serveur) ───────────────────────────────────
const kpis = computed(() => props.kpisTotal);

// ── Helpers ───────────────────────────────────────────────────────────────────
const statutLabel = (s) => s === 'approuve' ? 'Approuvé' : s === 'refuse' ? 'Refusé' : 'En attente';
const statutStyle = (s) => s === 'approuve'
    ? (dark.value ? 'bg-emerald-900/30 text-emerald-400' : 'bg-emerald-50 text-emerald-700')
    : s === 'refuse'
        ? (dark.value ? 'bg-rose-900/30 text-rose-400' : 'bg-rose-50 text-rose-700')
        : (dark.value ? 'bg-amber-900/30 text-amber-400' : 'bg-amber-50 text-amber-700');

const isAdminOrRh = computed(() => {
    const roles = page.props.auth?.roles ?? [];
    return roles.includes('admin') || roles.includes('rh');
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—';

// ── Actions ───────────────────────────────────────────────────────────────────
const approuver = (id) => router.post(`/conges/${id}/approuver`);
const refuser   = (id) => router.post(`/conges/${id}/refuser`);
const destroy   = (id) => {
    if (confirm('Supprimer cette demande de congé ?')) router.delete(`/conges/${id}`);
};

// ── Styles ────────────────────────────────────────────────────────────────────
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';
</script>

<template>
    <Head title="Congés" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">

            <!-- En-tête -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                        Demandes de Congé
                    </h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">
                        {{ kpis.total }} demande(s) au total
                    </p>
                </div>
                <Link href="/conges/create"
                    class="text-decoration-none inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]
                           transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle demande
                </Link>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-for="(item, i) in [
                    { label: 'Total',       val: kpis.total,    color: 'text-slate-600',    filter: '',         dur: 900  },
                    { label: 'En attente',  val: kpis.pending,  color: 'text-amber-600',    bar: 'bg-amber-500',   filter: 'pending',  dur: 1000 },
                    { label: 'Approuvés',   val: kpis.approuve, color: 'text-emerald-600',  bar: 'bg-emerald-500', filter: 'approuve', dur: 1100 },
                    { label: 'Refusés',     val: kpis.refuse,   color: 'text-rose-600',     bar: 'bg-rose-500',    filter: 'refuse',   dur: 1200 },
                ]" :key="i"
                    class="kpi-card rounded-2xl border p-4 shadow-sm cursor-pointer transition-all duration-300 hover:scale-[1.03]"
                    :class="[
                        dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200',
                        filtreStatut === item.filter ? 'ring-2 ring-[#760078]/40' : ''
                    ]"
                    @click="appliquerFiltre(item.filter)">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">{{ item.label }}</p>
                    <p class="text-3xl font-black" :class="item.color">
                        <AnimatedStat :value="item.val ?? 0" :duration="item.dur" />
                    </p>
                    <div v-if="item.bar && kpis.total > 0" class="mt-2 h-1.5 rounded-full" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                        <div class="h-1.5 rounded-full transition-all" :class="item.bar"
                            :style="{ width: Math.round((item.val / kpis.total) * 100) + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- Filtre actif -->
            <div v-if="filtreStatut" class="flex items-center gap-2">
                <span class="text-xs font-semibold px-3 py-1 rounded-full"
                    :class="dark ? 'bg-[#21262d] text-slate-300' : 'bg-slate-100 text-slate-600'">
                    Filtre : {{ statutLabel(filtreStatut) }}
                </span>
                <button @click="appliquerFiltre('')"
                    class="text-[10px] font-bold transition-colors"
                    :class="dark ? 'text-slate-500 hover:text-white' : 'text-slate-400 hover:text-slate-700'">
                    ✕ Réinitialiser
                </button>
            </div>

            <!-- Tableau -->
            <div class="rounded-2xl border overflow-hidden"
                :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Employé</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Poste / Département</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Motif</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Période</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Jours</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Statut</th>
                            <th class="px-4 py-2.5 text-right text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!liste.length">
                            <td colspan="7" class="px-4 py-8 text-center text-sm" :class="sL(dark)">Aucune demande de congé.</td>
                        </tr>
                        <tr v-for="(conge, i) in liste" :key="conge.id"
                            class="row-anim border-t transition-colors"
                            :style="{ animationDelay: i * 35 + 'ms' }"
                            :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50',
                                conge.statut === 'approuve' ? (dark ? 'bg-emerald-900/5' : 'bg-emerald-50/30') : '',
                                conge.statut === 'refuse'   ? (dark ? 'bg-rose-900/5'    : 'bg-rose-50/30')    : '']">

                            <!-- Employé -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[10px] font-black text-white shrink-0 bg-gradient-to-br from-[#760078] to-[#7677B7]">
                                        {{ conge.user?.prenom?.charAt(0) }}{{ conge.user?.nom?.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold" :class="dark ? 'text-slate-100' : 'text-slate-800'">
                                            {{ conge.user?.nom }} {{ conge.user?.prenom }}
                                        </p>
                                        <p class="text-[10px]" :class="sL(dark)">{{ conge.user?.email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Poste / Département -->
                            <td class="px-4 py-3">
                                <p class="text-xs font-medium" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                    {{ conge.user?.affectations?.length ? conge.user.affectations[0].poste?.nom : '—' }}
                                </p>
                                <p class="text-[10px]" :class="sL(dark)">
                                    {{ conge.user?.affectations?.length ? conge.user.affectations[0].poste?.departement?.nom : '' }}
                                </p>
                            </td>

                            <!-- Motif -->
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-semibold"
                                    :class="dark ? 'bg-[#21262d] text-slate-300' : 'bg-slate-100 text-slate-600'">
                                    {{ conge.motif?.nom ?? '—' }}
                                </span>
                            </td>

                            <!-- Période -->
                            <td class="px-4 py-3">
                                <p class="text-[11px] font-medium" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                    {{ formatDate(conge.date_debut) }}
                                </p>
                                <p class="text-[10px]" :class="sL(dark)">→ {{ formatDate(conge.date_fin) }}</p>
                            </td>

                            <!-- Durée -->
                            <td class="px-4 py-3 text-center">
                                <template v-if="conge.date_debut && conge.date_fin">
                                    <span class="text-xs font-bold" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                        {{ Math.ceil((new Date(conge.date_fin) - new Date(conge.date_debut)) / 86400000) + 1 }}j
                                    </span>
                                </template>
                                <span v-else class="text-[10px]" :class="sL(dark)">—</span>
                            </td>

                            <!-- Statut -->
                            <td class="px-4 py-3 text-center">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full"
                                    :class="statutStyle(conge.statut)">
                                    {{ statutLabel(conge.statut) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5 flex-wrap justify-end">
                                    <!-- Approuver / Refuser — RH + admin, seulement si en attente -->
                                    <template v-if="isAdminOrRh && conge.statut === 'pending'">
                                        <button @click="approuver(conge.id)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-emerald-500/10 text-emerald-600 border-emerald-500/20 hover:bg-emerald-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Approuver
                                        </button>
                                        <button @click="refuser(conge.id)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-rose-500/10 text-rose-500 border-rose-500/20 hover:bg-rose-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Refuser
                                        </button>
                                    </template>

                                    <!-- Lettre de congé — si approuvé -->
                                    <a v-if="isAdminOrRh && conge.statut === 'approuve'"
                                        :href="`/conges/${conge.id}/lettre`" target="_blank"
                                        class="text-decoration-none inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-[#760078]/10 text-[#760078] border-[#760078]/20 hover:bg-[#760078]/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        Lettre
                                    </a>

                                    <Link :href="`/conges/${conge.id}/edit`"
                                        class="text-decoration-none inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-amber-500/10 text-amber-500 border-amber-500/20 hover:bg-amber-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Modifier
                                    </Link>
                                    <button @click="destroy(conge.id)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-slate-500/10 text-slate-500 border-slate-500/20 hover:bg-rose-500/20 hover:text-rose-500 hover:border-rose-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="px-4 py-2 border-t flex items-center" :class="[div(dark), dark ? 'bg-[#0d1117]' : 'bg-slate-50']">
                    <p class="text-[10px]" :class="sL(dark)">{{ conges.meta?.from }}–{{ conges.meta?.to }} sur {{ conges.meta?.total }} demande(s)</p>
                </div>
            </div>

            <Pagination :links="conges.links" :meta="conges" />

        </div>
    </AuthenticatedLayout>
</template>
