<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const { dark } = useTheme();

const props = defineProps({
    transactions: { type: Object, default: () => ({}) },
    totaux:       { type: Object, default: () => ({}) },
    departements: { type: Array,  default: () => [] },
});

// ── Filtres locaux (sur la page courante) ─────────────────────────────────────
const search   = ref('');
const filType  = ref('');
const filDept  = ref('');
const filSrc   = ref('');

const liste = computed(() => {
    const q = search.value.toLowerCase();
    return (props.transactions.data ?? []).filter(t =>
        (!q || t.description?.toLowerCase().includes(q) || t.departement?.toLowerCase().includes(q) || t.user?.toLowerCase().includes(q)) &&
        (!filType.value || t.type === filType.value) &&
        (!filDept.value || t.departement === filDept.value) &&
        (!filSrc.value  || t.source === filSrc.value)
    );
});

const totalsFiltres = computed(() => ({
    revenus:  liste.value.filter(t => t.type === 'revenu').reduce((s, t) => s + t.montant, 0),
    depenses: liste.value.filter(t => t.type === 'dépense').reduce((s, t) => s + t.montant, 0),
}));

// ── Suppression ───────────────────────────────────────────────────────────────
const supprimerForm = useForm({});
const supprimer = (id) => {
    if (!confirm('Supprimer cette transaction ?')) return;
    supprimerForm.delete(`/transactions/${id}`);
};

// ── Formatage ─────────────────────────────────────────────────────────────────
const fmt = (v) => Number(v ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' FCFA';

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-200';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';

const sourceLabel = (s) => ({ manuel: 'Manuel', salaire: 'Salaire auto', paiement: 'Paiement' })[s] ?? s;
const sourceBadge = (s) => s === 'salaire'
    ? (dark.value ? 'bg-violet-900/30 text-violet-300' : 'bg-violet-100 text-violet-700')
    : s === 'paiement'
    ? (dark.value ? 'bg-blue-900/30 text-blue-300' : 'bg-blue-100 text-blue-700')
    : (dark.value ? 'bg-[#21262d] text-slate-400' : 'bg-slate-100 text-slate-500');
</script>

<template>
    <Head title="Transactions" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">

            <!-- En-tête -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Transactions</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Toutes les entrées et sorties de trésorerie de l'entreprise.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link href="/rapports/financiers"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                        Rapport
                    </Link>
                    <a href="/transactions/export"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </a>
                    <Link href="/transactions/create"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nouvelle transaction
                    </Link>
                </div>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div v-for="(item, i) in [
                    { label: 'Revenus (période)',  val: totaux.revenus,  color: 'text-emerald-500', icon: '↑', sub: 'Entrées de trésorerie' },
                    { label: 'Dépenses (période)', val: totaux.depenses, color: 'text-rose-500',    icon: '↓', sub: 'Sorties de trésorerie' },
                    { label: 'Solde',              val: totaux.solde,    color: totaux.solde >= 0 ? 'text-emerald-500' : 'text-rose-500', icon: '=', sub: 'Revenus − Dépenses' },
                ]" :key="i"
                    class="rounded-2xl border p-5"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg font-black" :class="item.color">{{ item.icon }}</span>
                        <p class="text-[10px] font-bold uppercase tracking-widest" :class="sL(dark)">{{ item.label }}</p>
                    </div>
                    <p class="text-xl font-black" :class="item.color">{{ fmt(item.val) }}</p>
                    <p class="text-[10px] mt-1" :class="sL(dark)">{{ item.sub }}</p>
                </div>
            </div>

            <!-- Filtres -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Description, département…"
                        class="pl-8 pr-3 py-1.5 rounded-xl border text-xs w-52 transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                </div>
                <select v-model="filType" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                    <option value="">Tous types</option>
                    <option value="revenu">Revenus</option>
                    <option value="dépense">Dépenses</option>
                </select>
                <select v-model="filDept" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                    <option value="">Tous départements</option>
                    <option v-for="d in departements" :key="d.id" :value="d.nom">{{ d.nom }}</option>
                </select>
                <select v-model="filSrc" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                    <option value="">Toutes sources</option>
                    <option value="manuel">Manuel</option>
                    <option value="salaire">Salaire auto</option>
                    <option value="paiement">Paiement</option>
                </select>
                <button v-if="search || filType || filDept || filSrc"
                    @click="search = ''; filType = ''; filDept = ''; filSrc = ''"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    Réinitialiser
                </button>
                <span class="text-[10px] ml-auto" :class="sL(dark)">
                    {{ liste.length }} transaction(s) · Revenus {{ fmt(totalsFiltres.revenus) }} · Dépenses {{ fmt(totalsFiltres.depenses) }}
                </span>
            </div>

            <!-- Tableau -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Date</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Description</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Département</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Type</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Source</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Montant</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Action</th>
                        </tr>
                    </thead>
                    <TransitionGroup tag="tbody" name="tbl">
                        <tr v-if="liste.length === 0" key="empty">
                            <td colspan="7" class="py-12 text-center text-xs" :class="sL(dark)">Aucune transaction trouvée.</td>
                        </tr>
                        <tr v-for="t in liste" :key="t.id"
                            class="border-t transition-colors"
                            :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50',
                                t.type === 'revenu'  ? (dark ? 'bg-emerald-900/[0.03]' : '') : '',
                                t.type === 'dépense' ? (dark ? 'bg-rose-900/[0.03]'    : '') : '']">
                            <td class="px-5 py-3 text-[11px]" :class="sL(dark)">{{ t.date_transaction }}</td>
                            <td class="px-5 py-3 max-w-xs">
                                <p class="text-xs font-medium truncate" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ t.description }}</p>
                                <p v-if="t.user" class="text-[10px]" :class="sL(dark)">{{ t.user }}</p>
                            </td>
                            <td class="px-5 py-3 text-xs" :class="sL(dark)">{{ t.departement }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="t.type === 'revenu' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                    {{ t.type === 'revenu' ? '↑ Entrée' : '↓ Sortie' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold" :class="sourceBadge(t.source)">
                                    {{ sourceLabel(t.source) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm font-bold"
                                :class="t.type === 'revenu' ? 'text-emerald-600' : 'text-rose-600'">
                                {{ t.type === 'revenu' ? '+' : '-' }}{{ fmt(t.montant) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <button v-if="t.source === 'manuel'" @click="supprimer(t.id)"
                                    class="p-1.5 rounded-lg transition-colors"
                                    :class="dark ? 'text-slate-500 hover:text-rose-400 hover:bg-rose-900/20' : 'text-slate-400 hover:text-rose-600 hover:bg-rose-50'"
                                    title="Supprimer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <span v-else class="text-[10px]" :class="sL(dark)">Auto</span>
                            </td>
                        </tr>
                    </TransitionGroup>
                </table>
                </div>

                <div class="px-5 py-2 border-t flex items-center" :class="[div(dark), dark ? 'bg-[#0d1117]' : 'bg-slate-50']">
                    <p class="text-[10px]" :class="sL(dark)">{{ liste.length }} transaction(s) sur cette page</p>
                </div>
            </div>

            <Pagination :links="transactions.links" :meta="transactions" />

        </div>
    </AuthenticatedLayout>
</template>
