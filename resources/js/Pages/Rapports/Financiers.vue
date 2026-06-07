<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import AnimatedStat from '@/Components/AnimatedStat.vue';

Chart.register(...registerables);

const { dark } = useTheme();

const props = defineProps({
    parDept:      { type: Array,  default: () => [] },
    mensuel:      { type: Array,  default: () => [] },
    parCategorie: { type: Array,  default: () => [] },
    transactions: { type: Array,  default: () => [] },
    totaux:       { type: Object, default: () => ({}) },
    annees:       { type: Array,  default: () => [] },
    filtres:      { type: Object, default: () => ({}) },
});

// ── Filtres ──────────────────────────────────────────────────────────────────
const annee   = ref(props.filtres.annee   ?? new Date().getFullYear());
const moisDeb = ref(props.filtres.moisDeb ?? 1);
const moisFin = ref(props.filtres.moisFin ?? 12);

const moisLabels = [
    { v: 1, l: 'Janvier' }, { v: 2, l: 'Février' }, { v: 3, l: 'Mars' },
    { v: 4, l: 'Avril' },   { v: 5, l: 'Mai' },      { v: 6, l: 'Juin' },
    { v: 7, l: 'Juillet' }, { v: 8, l: 'Août' },     { v: 9, l: 'Septembre' },
    { v: 10, l: 'Octobre' },{ v: 11, l: 'Novembre' },{ v: 12, l: 'Décembre' },
];

const appliquer = () => {
    router.get('/rapports/financiers', {
        annee: annee.value, mois_debut: moisDeb.value, mois_fin: moisFin.value,
    }, { preserveScroll: true });
};

// ── Charts ───────────────────────────────────────────────────────────────────
const canvasBar  = ref(null);
const canvasLine = ref(null);
const canvasPie  = ref(null);
let chartBar = null, chartLine = null, chartPie = null;

const gridColor = computed(() => dark.value ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)');
const textColor = computed(() => dark.value ? '#94a3b8' : '#64748b');

const destroyCharts = () => {
    chartBar?.destroy();  chartBar  = null;
    chartLine?.destroy(); chartLine = null;
    chartPie?.destroy();  chartPie  = null;
};

const initCharts = () => {
    destroyCharts();
    nextTick(() => {
        if (!canvasBar.value || !canvasLine.value || !canvasPie.value) return;

        // Bar chart : Entrées vs Dépenses par département
        chartBar = new Chart(canvasBar.value, {
            type: 'bar',
            data: {
                labels: props.parDept.map(d => d.nom),
                datasets: [
                    {
                        label: 'Revenus',
                        data: props.parDept.map(d => d.revenus),
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderColor: 'rgb(16,185,129)',
                        borderWidth: 1, borderRadius: 6,
                    },
                    {
                        label: 'Dépenses',
                        data: props.parDept.map(d => d.depenses),
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderColor: 'rgb(239,68,68)',
                        borderWidth: 1, borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: textColor.value, font: { size: 11 } } } },
                scales: {
                    x: { ticks: { color: textColor.value, font: { size: 10 } }, grid: { color: gridColor.value } },
                    y: { ticks: { color: textColor.value, font: { size: 10 }, callback: v => fmtK(v) }, grid: { color: gridColor.value } },
                },
            },
        });

        // Line chart : Évolution mensuelle N vs N-1
        chartLine = new Chart(canvasLine.value, {
            type: 'line',
            data: {
                labels: props.mensuel.map(m => m.label ?? m.mois),
                datasets: [
                    {
                        label: 'Revenus N',
                        data: props.mensuel.map(m => m.rev ?? m.revenus),
                        borderColor: 'rgb(16,185,129)', backgroundColor: 'rgba(16,185,129,0.1)',
                        tension: 0.4, fill: true, pointRadius: 4, borderWidth: 2,
                    },
                    {
                        label: 'Dépenses N',
                        data: props.mensuel.map(m => m.dep ?? m.depenses),
                        borderColor: 'rgb(239,68,68)', backgroundColor: 'rgba(239,68,68,0.1)',
                        tension: 0.4, fill: true, pointRadius: 4, borderWidth: 2,
                    },
                    {
                        label: `Revenus N-1`,
                        data: props.mensuel.map(m => m.revNm1 ?? 0),
                        borderColor: 'rgba(16,185,129,0.4)', backgroundColor: 'transparent',
                        tension: 0.4, fill: false, pointRadius: 2, borderWidth: 1.5, borderDash: [5, 3],
                    },
                    {
                        label: `Dépenses N-1`,
                        data: props.mensuel.map(m => m.depNm1 ?? 0),
                        borderColor: 'rgba(239,68,68,0.4)', backgroundColor: 'transparent',
                        tension: 0.4, fill: false, pointRadius: 2, borderWidth: 1.5, borderDash: [5, 3],
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: textColor.value, font: { size: 10 } } } },
                scales: {
                    x: { ticks: { color: textColor.value, font: { size: 10 } }, grid: { color: gridColor.value } },
                    y: { ticks: { color: textColor.value, font: { size: 10 }, callback: v => fmtK(v) }, grid: { color: gridColor.value } },
                },
            },
        });

        // Pie chart : Dépenses par département
        const colors = ['#760078','#7677B7','#e91e8c','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6'];
        chartPie = new Chart(canvasPie.value, {
            type: 'doughnut',
            data: {
                labels: props.parDept.filter(d => d.depenses > 0).map(d => d.nom),
                datasets: [{
                    data: props.parDept.filter(d => d.depenses > 0).map(d => d.depenses),
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: dark.value ? '#161b22' : '#ffffff',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'right', labels: { color: textColor.value, font: { size: 10 }, padding: 12 } },
                },
            },
        });
    });
};

onMounted(initCharts);
onUnmounted(destroyCharts);
watch(dark, initCharts);

// ── Formatage ────────────────────────────────────────────────────────────────
const fmt  = (v) => Number(v ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA';
const fmtK = (v) => v >= 1_000_000 ? (v / 1_000_000).toFixed(1) + 'M' : v >= 1_000 ? (v / 1_000).toFixed(0) + 'K' : v;

// ── Classement ───────────────────────────────────────────────────────────────
const topRevenu  = computed(() => [...props.parDept].sort((a, b) => b.revenus  - a.revenus)[0]);
const topDepense = computed(() => [...props.parDept].sort((a, b) => b.depenses - a.depenses)[0]);
const plusEquil  = computed(() => [...props.parDept].sort((a, b) => Math.abs(a.solde) - Math.abs(b.solde))[0]);

// ── Filtre table ─────────────────────────────────────────────────────────────
const search  = ref('');
const filtype = ref('');
const filteredTx = computed(() => {
    const q = search.value.toLowerCase();
    return props.transactions.filter(t =>
        (!q || t.description?.toLowerCase().includes(q) || t.departement?.toLowerCase().includes(q)) &&
        (!filtype.value || t.type === filtype.value)
    );
});

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-200';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';
</script>

<template>
    <Head title="Rapport Financier" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

            <!-- En-tête + Filtres -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Rapport Financier</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Analyse des entrées et sorties de trésorerie par département et par période.</p>
                </div>
                <!-- Filtres période -->
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Rapports PDF -->
                    <a :href="`/rapports/mensuel?annee=${annee}&mois=${moisDeb}`" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Mensuel PDF
                    </a>
                    <a :href="`/rapports/annuel?annee=${annee}`" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all text-white
                               bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Annuel PDF
                    </a>
                    <select v-model="annee" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
                    </select>
                    <select v-model="moisDeb" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option v-for="m in moisLabels" :key="m.v" :value="m.v">{{ m.l }}</option>
                    </select>
                    <span class="text-xs" :class="sL(dark)">→</span>
                    <select v-model="moisFin" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option v-for="m in moisLabels" :key="m.v" :value="m.v">{{ m.l }}</option>
                    </select>
                    <button @click="appliquer"
                        class="px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                        Appliquer
                    </button>
                </div>
            </div>

            <!-- KPIs globaux + N vs N-1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <div v-for="(item, i) in [
                    { label: 'Total Revenus',  val: totaux.revenus,  valNm1: totaux.revNm1,  color: 'text-emerald-500', icon: '↑', bg: 'bg-emerald-500/10', dur: 1400 },
                    { label: 'Total Dépenses', val: totaux.depenses, valNm1: totaux.depNm1,  color: 'text-rose-500',    icon: '↓', bg: 'bg-rose-500/10',    dur: 1600 },
                    { label: 'Solde Net',      val: totaux.solde,    valNm1: totaux.soldeNm1, color: totaux.solde >= 0 ? 'text-emerald-500' : 'text-rose-500', icon: '=', bg: 'bg-slate-500/10', dur: 1800 },
                ]" :key="i"
                    class="kpi-card rounded-2xl border p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-lg"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-sm" :class="[item.bg, item.color]">{{ item.icon }}</div>
                        <p class="text-[10px] font-bold uppercase tracking-widest" :class="sL(dark)">{{ item.label }}</p>
                    </div>
                    <p class="text-2xl font-black" :class="item.color">
                        <AnimatedStat :value="item.val ?? 0" :duration="item.dur" />
                        <span class="text-sm font-semibold ml-1 opacity-70">FCFA</span>
                    </p>
                    <!-- Comparaison N-1 -->
                    <div v-if="item.valNm1 != null" class="mt-2 flex items-center gap-1.5">
                        <span class="text-[10px]" :class="sL(dark)">vs {{ totaux.anneeNm1 }} :</span>
                        <span class="text-[10px] font-bold" :class="item.val >= item.valNm1 ? 'text-emerald-500' : 'text-rose-500'">
                            {{ item.val >= item.valNm1 ? '↑' : '↓' }}
                            {{ item.valNm1 > 0 ? Math.abs(((item.val - item.valNm1) / item.valNm1) * 100).toFixed(1) + '%' : '—' }}
                        </span>
                        <span class="text-[10px]" :class="sL(dark)">({{ fmt(item.valNm1) }})</span>
                    </div>
                </div>
            </div>

            <!-- Classement des départements -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <div v-for="(item, i) in [
                    { label: '🏆 Plus de revenus',  dept: topRevenu,  val: topRevenu?.revenus,  color: 'text-emerald-500' },
                    { label: '📉 Plus de dépenses', dept: topDepense, val: topDepense?.depenses, color: 'text-rose-500'    },
                    { label: '⚖️ Plus équilibré',   dept: plusEquil,  val: plusEquil?.solde,     color: 'text-blue-500'   },
                ]" :key="i"
                    class="rounded-2xl border p-4"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">{{ item.label }}</p>
                    <p class="text-sm font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">{{ item.dept?.nom ?? '—' }}</p>
                    <p class="text-xs font-semibold mt-0.5" :class="item.color">{{ item.val != null ? fmt(item.val) : '—' }}</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Évolution mensuelle -->
                <div class="rounded-2xl border p-5" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <p class="text-xs font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Évolution mensuelle</p>
                    <div class="h-52">
                        <canvas ref="canvasLine"></canvas>
                    </div>
                </div>
                <!-- Dépenses par département (pie) -->
                <div class="rounded-2xl border p-5" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <p class="text-xs font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Dépenses par département</p>
                    <div class="h-52">
                        <canvas ref="canvasPie"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bar chart (pleine largeur) -->
            <div class="rounded-2xl border p-5" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <p class="text-xs font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Revenus vs Dépenses par département</p>
                <div class="h-56">
                    <canvas ref="canvasBar"></canvas>
                </div>
            </div>

            <!-- Tableau par département -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="px-5 py-3 border-b" :class="div(dark)">
                    <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Récapitulatif par département</p>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Département</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-emerald-500">Revenus</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-rose-500">Dépenses</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Solde</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in parDept" :key="d.id" class="border-t transition-colors" :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50']">
                            <td class="px-5 py-3 text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ d.nom }}</td>
                            <td class="px-5 py-3 text-right text-xs font-semibold text-emerald-600">{{ fmt(d.revenus) }}</td>
                            <td class="px-5 py-3 text-right text-xs font-semibold text-rose-600">{{ fmt(d.depenses) }}</td>
                            <td class="px-5 py-3 text-right text-xs font-semibold"
                                :class="d.solde >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                {{ d.solde >= 0 ? '+' : '' }}{{ fmt(d.solde) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="dark ? 'bg-[#21262d] text-slate-300' : 'bg-slate-100 text-slate-600'">
                                    {{ d.count }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Répartition par catégorie de charges -->
            <div v-if="parCategorie.length" class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="px-5 py-3 border-b" :class="div(dark)">
                    <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Dépenses par catégorie de charge</p>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Répartition des sorties de trésorerie sur la période</p>
                </div>
                <div class="p-5 space-y-3">
                    <div v-for="c in parCategorie" :key="c.categorie" class="flex items-center gap-3">
                        <div class="w-40 shrink-0 text-xs font-medium truncate" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ c.categorie }}</div>
                        <div class="flex-1 h-2 rounded-full overflow-hidden" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                            <div class="h-2 rounded-full bg-gradient-to-r from-[#760078] to-[#7677B7] transition-all duration-700"
                                :style="{ width: totaux.depenses > 0 ? (c.montant / totaux.depenses * 100) + '%' : '0%' }"></div>
                        </div>
                        <div class="text-xs font-bold text-rose-500 w-32 text-right shrink-0">{{ fmt(c.montant) }}</div>
                        <div class="text-[10px] w-10 text-right shrink-0" :class="sL(dark)">
                            {{ totaux.depenses > 0 ? (c.montant / totaux.depenses * 100).toFixed(1) : 0 }}%
                        </div>
                        <div class="text-[10px] w-12 text-right shrink-0" :class="sL(dark)">{{ c.count }} op.</div>
                    </div>
                </div>
            </div>

            <!-- Historique des transactions -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="px-5 py-3 border-b flex items-center justify-between gap-3" :class="div(dark)">
                    <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Détail des transactions</p>
                    <div class="flex items-center gap-2">
                        <input v-model="search" placeholder="Rechercher…" type="text"
                            class="rounded-xl border px-3 py-1 text-xs w-40 transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                        <select v-model="filtype" class="rounded-xl border px-2 py-1 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                            <option value="">Tous</option>
                            <option value="revenu">Revenus</option>
                            <option value="dépense">Dépenses</option>
                        </select>
                    </div>
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0" :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <tr>
                                <th class="px-5 py-2 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Date</th>
                                <th class="px-5 py-2 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Description</th>
                                <th class="px-5 py-2 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Département</th>
                                <th class="px-5 py-2 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Type</th>
                                <th class="px-5 py-2 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredTx.length === 0">
                                <td colspan="5" class="py-8 text-center text-xs" :class="sL(dark)">Aucune transaction.</td>
                            </tr>
                            <tr v-for="t in filteredTx" :key="t.id" class="border-t transition-colors" :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50']">
                                <td class="px-5 py-2 text-[11px]" :class="sL(dark)">{{ t.date_transaction }}</td>
                                <td class="px-5 py-2 text-xs max-w-xs truncate" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ t.description }}</td>
                                <td class="px-5 py-2 text-xs" :class="sL(dark)">{{ t.departement }}</td>
                                <td class="px-5 py-2 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="t.type === 'revenu' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                        {{ t.type === 'revenu' ? '↑ Entrée' : '↓ Sortie' }}
                                    </span>
                                </td>
                                <td class="px-5 py-2 text-right text-xs font-bold"
                                    :class="t.type === 'revenu' ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ t.type === 'revenu' ? '+' : '-' }}{{ fmt(t.montant) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
