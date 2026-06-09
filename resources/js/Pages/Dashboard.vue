<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useTheme } from '@/composables/useTheme';
import Chart from 'chart.js/auto';
import AnimatedStat from '@/Components/AnimatedStat.vue';
import ChartCard from '@/Components/ChartCard.vue';

const { dark } = useTheme();

const props = defineProps({
    kpis:                { type: Object, default: () => ({}) },
    presenceToday:       { type: Object, default: () => ({}) },
    tendanceSalaires:    { type: Array,  default: () => [] },
    repartitionContrat:  { type: Array,  default: () => [] },
    bulletinsMensuels:   { type: Array,  default: () => [] },
    effectifsParDept:    { type: Array,  default: () => [] },
    repartitionGenre:    { type: Array,  default: () => [] },
    congesParMois:       { type: Array,  default: () => [] },
    absencesParMois:     { type: Array,  default: () => [] },
    recrutementsParMois: { type: Array,  default: () => [] },
    congesPending:       { type: Array,  default: () => [] },
    activites:           { type: Array,  default: () => [] },
});

// ── Canvas refs ───────────────────────────────────────────────────────────────
const salaryCanvas       = ref(null);
const bulletinsCanvas    = ref(null);
const donutCanvas        = ref(null);
const deptCanvas         = ref(null);
const genreCanvas        = ref(null);
const congesAbsCanvas    = ref(null);
const recrutementsCanvas = ref(null);

let salaryChart       = null;
let bulletinsChart    = null;
let donutChart        = null;
let deptChart         = null;
let genreChart        = null;
let congesAbsChart    = null;
let recrutementsChart = null;

// ── Palette ───────────────────────────────────────────────────────────────────
const BAR_COLORS = ['#760078', '#7677B7', '#a855f7', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];

const gridC  = () => dark.value ? '#21262d' : '#f1f5f9';
const tickC  = () => dark.value ? '#94a3b8' : '#64748b';
const bgCard = () => dark.value ? '#161b22' : '#ffffff';

const tooltipBase = {
    backgroundColor: '#1e293b',
    titleColor: '#94a3b8',
    bodyColor: '#ffffff',
    padding: 10,
    cornerRadius: 8,
};

// ── Masse salariale area chart (12 mois) ──────────────────────────────────────
function buildSalaryChart() {
    if (!salaryCanvas.value) return;
    salaryChart?.destroy(); salaryChart = null;
    const labels = props.tendanceSalaires.map(d => d.label ?? '');
    const data   = props.tendanceSalaires.map(d => d.montant ?? 0);
    salaryChart = new Chart(salaryCanvas.value, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data,
                borderColor: '#760078',
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    g.addColorStop(0, 'rgba(118,0,120,0.30)');
                    g.addColorStop(1, 'rgba(118,0,120,0.00)');
                    return g;
                },
                borderWidth: 2.5,
                pointBackgroundColor: '#760078',
                pointBorderColor: bgCard(),
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: { label: ctx => ' ' + new Intl.NumberFormat('fr-CI').format(ctx.raw) + ' FCFA' } },
            },
            scales: {
                x: { grid: { color: gridC() }, ticks: { color: tickC(), font: { size: 10 } }, border: { display: false } },
                y: {
                    grid: { color: gridC() }, ticks: { color: tickC(), font: { size: 10 }, callback: v => new Intl.NumberFormat('fr-CI', { notation: 'compact' }).format(v) },
                    border: { display: false }, beginAtZero: true,
                },
            },
        },
    });
}

// ── Bulletins bar ─────────────────────────────────────────────────────────────
function buildBulletinsChart() {
    if (!bulletinsCanvas.value) return;
    bulletinsChart?.destroy(); bulletinsChart = null;
    const labels = props.bulletinsMensuels.map(d => d.label ?? '');
    const data   = props.bulletinsMensuels.map(d => d.count ?? 0);
    bulletinsChart = new Chart(bulletinsCanvas.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ data, backgroundColor: data.map((_, i) => BAR_COLORS[i % BAR_COLORS.length]), borderRadius: 6, borderSkipped: false }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: { label: ctx => ` ${ctx.raw} bulletin(s)` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickC(), font: { size: 9 } }, border: { display: false } },
                y: { grid: { color: gridC() }, ticks: { color: tickC(), font: { size: 9 }, stepSize: 1 }, border: { display: false }, beginAtZero: true },
            },
        },
    });
}

// ── Type contrat donut ────────────────────────────────────────────────────────
function buildDonutChart() {
    if (!donutCanvas.value) return;
    donutChart?.destroy(); donutChart = null;
    const items  = props.repartitionContrat;
    const data   = items.map(d => d.count ?? 0);
    const colors = items.map((d, i) => d.color ?? BAR_COLORS[i % BAR_COLORS.length]);
    donutChart = new Chart(donutCanvas.value, {
        type: 'doughnut',
        data: {
            labels: items.map(d => d.label ?? ''),
            datasets: [{ data: data.length ? data : [1], backgroundColor: data.length ? colors : ['#e2e8f0'], borderWidth: 3, borderColor: bgCard(), hoverOffset: 4 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: { label: ctx => data.length ? ` ${ctx.label}: ${ctx.raw} (${items[ctx.dataIndex]?.pct ?? 0}%)` : ' Aucun employé' } },
            },
        },
    });
}

// ── Effectifs par département (horizontal bar) ────────────────────────────────
function buildDeptChart() {
    if (!deptCanvas.value) return;
    deptChart?.destroy(); deptChart = null;
    const items  = props.effectifsParDept;
    const labels = items.map(d => d.dept ?? '');
    const data   = items.map(d => d.count ?? 0);
    if (!data.length) return;
    deptChart = new Chart(deptCanvas.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ data, backgroundColor: data.map((_, i) => BAR_COLORS[i % BAR_COLORS.length]), borderRadius: 5, borderSkipped: false }],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: { label: ctx => ` ${ctx.raw} employé(s)` } },
            },
            scales: {
                x: { grid: { color: gridC() }, ticks: { color: tickC(), font: { size: 9 }, stepSize: 1 }, border: { display: false }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { color: tickC(), font: { size: 9 } }, border: { display: false } },
            },
        },
    });
}

// ── Répartition Genre donut ───────────────────────────────────────────────────
function buildGenreChart() {
    if (!genreCanvas.value) return;
    genreChart?.destroy(); genreChart = null;
    const items  = props.repartitionGenre;
    const data   = items.map(d => d.count ?? 0);
    const colors = items.map(d => d.color ?? '#94a3b8');
    genreChart = new Chart(genreCanvas.value, {
        type: 'doughnut',
        data: {
            labels: items.map(d => d.label ?? ''),
            datasets: [{ data: data.length ? data : [1], backgroundColor: data.length ? colors : ['#e2e8f0'], borderWidth: 3, borderColor: bgCard(), hoverOffset: 4 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: { label: ctx => data.length ? ` ${ctx.label}: ${ctx.raw}` : ' Aucune donnée' } },
            },
        },
    });
}

// ── Congés + Absences grouped bar ─────────────────────────────────────────────
function buildCongesAbsChart() {
    if (!congesAbsCanvas.value) return;
    congesAbsChart?.destroy(); congesAbsChart = null;
    const labels = props.congesParMois.map(d => d.label ?? '');
    congesAbsChart = new Chart(congesAbsCanvas.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Congés',
                    data: props.congesParMois.map(d => d.count ?? 0),
                    backgroundColor: 'rgba(245,158,11,0.75)',
                    borderRadius: 4,
                },
                {
                    label: 'Absences',
                    data: props.absencesParMois.map(d => d.count ?? 0),
                    backgroundColor: 'rgba(239,68,68,0.65)',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true, position: 'top', align: 'end',
                    labels: { color: tickC(), font: { size: 10 }, boxWidth: 10, padding: 12 },
                },
                tooltip: { ...tooltipBase },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickC(), font: { size: 9 } }, border: { display: false } },
                y: { grid: { color: gridC() }, ticks: { color: tickC(), font: { size: 9 }, stepSize: 1 }, border: { display: false }, beginAtZero: true },
            },
        },
    });
}

// ── Recrutements line ─────────────────────────────────────────────────────────
function buildRecrutementsChart() {
    if (!recrutementsCanvas.value) return;
    recrutementsChart?.destroy(); recrutementsChart = null;
    const labels = props.recrutementsParMois.map(d => d.label ?? '');
    const data   = props.recrutementsParMois.map(d => d.count ?? 0);
    recrutementsChart = new Chart(recrutementsCanvas.value, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data,
                borderColor: '#06b6d4',
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    g.addColorStop(0, 'rgba(6,182,212,0.25)');
                    g.addColorStop(1, 'rgba(6,182,212,0.00)');
                    return g;
                },
                borderWidth: 2,
                pointBackgroundColor: '#06b6d4',
                pointBorderColor: bgCard(),
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: { label: ctx => ` ${ctx.raw} recrutement(s)` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickC(), font: { size: 9 } }, border: { display: false } },
                y: { grid: { color: gridC() }, ticks: { color: tickC(), font: { size: 9 }, stepSize: 1 }, border: { display: false }, beginAtZero: true },
            },
        },
    });
}

function buildAll() {
    buildSalaryChart();
    buildBulletinsChart();
    buildDonutChart();
    buildDeptChart();
    buildGenreChart();
    buildCongesAbsChart();
    buildRecrutementsChart();
}

onMounted(() => nextTick(buildAll));
onBeforeUnmount(() => {
    [salaryChart, bulletinsChart, donutChart, deptChart, genreChart, congesAbsChart, recrutementsChart]
        .forEach(c => c?.destroy());
});
watch(dark, () => nextTick(buildAll));

// ── Helpers ───────────────────────────────────────────────────────────────────
const trendPct = computed(() => {
    const vals = props.tendanceSalaires.map(d => d.montant ?? 0);
    if (vals.length < 2) return null;
    const prev = vals[vals.length - 2];
    const curr = vals[vals.length - 1];
    if (!prev) return null;
    return { pct: ((curr - prev) / prev * 100).toFixed(1), up: curr >= prev };
});

const presencePct = computed(() => {
    const t = props.presenceToday.total || 1;
    return {
        presents: Math.round((props.presenceToday.presents || 0) / t * 100),
        retards:  Math.round((props.presenceToday.retards  || 0) / t * 100),
        absents:  Math.round((props.presenceToday.absents  || 0) / t * 100),
    };
});

const totalGenre = computed(() => props.repartitionGenre.reduce((s, g) => s + (g.count ?? 0), 0));

const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(n) : '0';
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';

const statusBadge = (color) => ({
    emerald: dark.value ? 'bg-emerald-900/30 text-emerald-400' : 'bg-emerald-50 text-emerald-700',
    amber:   dark.value ? 'bg-amber-900/30 text-amber-400'     : 'bg-amber-50 text-amber-700',
    blue:    dark.value ? 'bg-blue-900/30 text-blue-400'       : 'bg-blue-50 text-blue-700',
    rose:    dark.value ? 'bg-rose-900/30 text-rose-400'       : 'bg-rose-50 text-rose-700',
    slate:   dark.value ? 'bg-slate-700 text-slate-400'        : 'bg-slate-100 text-slate-500',
})[color] ?? (dark.value ? 'bg-slate-700 text-slate-400' : 'bg-slate-100 text-slate-500');
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="p-4 space-y-4 transition-colors duration-300 min-h-full" :class="dark ? 'text-white' : 'text-slate-800'">

            <!-- ── En-tête ─────────────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-extrabold tracking-tight" :class="dark ? 'text-white' : 'text-slate-900'">Eden Dashboard</h1>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Vue d'ensemble · Système SIRH</p>
                </div>
                <p class="hidden sm:block text-xs font-semibold" :class="sL(dark)">
                    {{ new Date().toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) }}
                </p>
            </div>

            <!-- ── KPI Cards ──────────────────────────────────────────────── -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 ">

                <Link href="/users"
                    class="text-decoration-none kpi-card relative overflow-hidden rounded-2xl p-3.5 bg-gradient-to-br from-[#760078] to-[#5a005c] shadow-lg shadow-purple-900/20 block transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl hover:shadow-purple-900/30  text-decoration-none" >
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full"></div>
                    <div class="absolute -left-5 -bottom-5 w-20 h-20 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="w-7 h-7 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-xl font-extrabold text-white"><AnimatedStat :value="kpis.employes ?? 0" :duration="1200" /></p>
                        <p class="text-[11px] text-white/70 font-medium mt-0.5">Collaborateurs</p>
                        <p class="text-[10px] text-white/50 mt-0.5">{{ kpis.departements ?? 0 }} département(s)</p>
                    </div>
                </Link>

                <Link href="/conges?statut=pending"
                    class="text-decoration-none kpi-card relative overflow-hidden rounded-2xl p-3.5 bg-gradient-to-br from-amber-500 to-amber-700 shadow-lg shadow-amber-900/20 block transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl hover:shadow-amber-900/30  text-decoration-none">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full"></div>
                    <div class="absolute -left-5 -bottom-5 w-20 h-20 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="w-7 h-7 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xl font-extrabold text-white"><AnimatedStat :value="kpis.conge_pending ?? 0" :duration="1000" /></p>
                        <p class="text-[11px] text-white/70 font-medium mt-0.5">Congés en attente</p>
                        <span v-if="(kpis.conge_pending ?? 0) > 0" class="text-[10px] font-bold text-white/80 bg-white/20 px-1.5 py-0.5 rounded-full">Action requise</span>
                    </div>
                </Link>

                <Link href="/bulletins-paie"
                    class="text-decoration-none kpi-card relative overflow-hidden rounded-2xl p-3.5 bg-gradient-to-br from-[#7677B7] to-[#5a5b9c] shadow-lg shadow-indigo-900/20 block transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl hover:shadow-indigo-900/30 text-decoration-none">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full"></div>
                    <div class="absolute -left-5 -bottom-5 w-20 h-20 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="w-7 h-7 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-xl font-extrabold text-white"><AnimatedStat :value="kpis.bulletins_valider ?? 0" :duration="1000" /></p>
                        <p class="text-[11px] text-white/70 font-medium mt-0.5">Bulletins brouillon</p>
                    </div>
                </Link>

                <Link href="/transactions"
                    class="kpi-card relative overflow-hidden rounded-2xl p-3.5 shadow-lg block transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl text-decoration-none"
                    :class=" fmt(kpis.solde_tresorerie) >= 0 ? 'bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-emerald-900/20' : 'bg-gradient-to-br from-rose-500 to-rose-700 shadow-rose-900/20'">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full"></div>
                    <div class="absolute -left-5 -bottom-5 w-20 h-20 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="w-7 h-7 bg-white/20 rounded-xl flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-base font-extrabold text-white leading-tight"><AnimatedStat :value="kpis.solde_tresorerie ?? 0" :duration="1600" /></p>
                        <p class="text-[9px] text-white/60 mt-0.5">FCFA</p>
                        <p class="text-[11px] text-white/70 font-medium mt-0.5">Solde trésorerie</p>
                    </div>
                </Link>
            </div>

            <!-- ── Présence du jour ───────────────────────────────────────── -->
            <div class="rounded-2xl border p-5 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Présence aujourd'hui</h3>
                        <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ presenceToday.total ?? 0 }} collaborateurs au total</p>
                    </div>
                    <Link href="/suivies" class="text-decoration-none text-[11px] font-semibold px-3 py-1.5 rounded-xl border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-900 text-decoration-none'">
                        Gérer →
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    <div v-for="(item, i) in [
                        { label: 'Présents',    val: presenceToday.presents,    text: 'text-emerald-600' },
                        { label: 'Retards',     val: presenceToday.retards,     text: 'text-amber-600' },
                        { label: 'Absents',     val: presenceToday.absents,     text: 'text-rose-600' },
                        { label: 'Non marqués', val: presenceToday.non_marques, text: 'text-slate-500' },
                    ]" :key="i"
                        class="stat-card rounded-xl p-3 text-center border transition-all duration-300 hover:scale-[1.04]"
                        :class="dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-slate-50 border-slate-100'">
                        <p class="text-2xl font-extrabold" :class="item.text"><AnimatedStat :value="item.val ?? 0" :duration="900" /></p>
                        <p class="text-[10px] mt-0.5 font-medium" :class="sL(dark)">{{ item.label }}</p>
                    </div>
                </div>
                <div class="h-2.5 rounded-full overflow-hidden flex gap-0.5" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                    <div class="bg-emerald-500 transition-all duration-700 rounded-l-full" :style="{ width: presencePct.presents + '%' }"></div>
                    <div class="bg-amber-500 transition-all duration-700"                  :style="{ width: presencePct.retards  + '%' }"></div>
                    <div class="bg-rose-500 transition-all duration-700"                   :style="{ width: presencePct.absents  + '%' }"></div>
                    <div class="bg-slate-300 transition-all duration-700 rounded-r-full flex-1"></div>
                </div>
            </div>

            <!-- ── Masse salariale + Contrat + Bulletins ──────────────────── -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

                <!-- Masse salariale (2/3) -->
                <div class="xl:col-span-2 rounded-2xl p-5 border transition-colors"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Masse salariale nette</h3>
                            <p class="text-xs mt-0.5" :class="sL(dark)">Bulletins payés · 12 derniers mois</p>
                        </div>
                        <span v-if="trendPct" class="text-xs font-bold px-2.5 py-1 rounded-full"
                            :class="trendPct.up ? (dark ? 'bg-emerald-900/30 text-emerald-400' : 'bg-emerald-50 text-emerald-600') : (dark ? 'bg-rose-900/30 text-rose-400' : 'bg-rose-50 text-rose-600')">
                            {{ trendPct.up ? '↑' : '↓' }} {{ Math.abs(trendPct.pct) }}%
                        </span>
                        <span v-else class="text-xs px-2.5 py-1 rounded-full" :class="sL(dark)">—</span>
                    </div>
                    <div style="height: 130px; position: relative;">
                        <canvas ref="salaryCanvas"></canvas>
                    </div>
                    <div class="mt-3 pt-3 border-t flex items-center justify-between" :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                        <p class="text-[11px]" :class="sL(dark)">Masse salariale ce mois</p>
                        <p class="text-sm font-extrabold text-[#760078]">{{ fmt(kpis.masse_salariale) }} FCFA</p>
                    </div>
                </div>

                <!-- Contrat + Bulletins (1/3) -->
                <div class="flex flex-col gap-4">
                    <div class="rounded-2xl p-5 border flex-1 transition-colors"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <h3 class="text-sm font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Type de contrat</h3>
                        <div class="flex items-center gap-5">
                            <div class="relative w-24 h-24 shrink-0">
                                <canvas ref="donutCanvas"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-sm font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">{{ kpis.employes ?? 0 }}</span>
                                    <span class="text-[8px]" :class="sL(dark)">total</span>
                                </div>
                            </div>
                            <div class="space-y-2.5 text-xs flex-1">
                                <div v-for="item in repartitionContrat" :key="item.label" class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-sm shrink-0" :style="{ background: item.color }"></span>
                                    <span :class="dark ? 'text-slate-300' : 'text-slate-600'">{{ item.label }}</span>
                                    <span class="ml-auto font-bold" :class="dark ? 'text-white' : 'text-slate-800'">
                                        {{ item.count }} <span class="font-normal opacity-60">({{ item.pct }}%)</span>
                                    </span>
                                </div>
                                <div v-if="!repartitionContrat.length" class="text-[10px]" :class="sL(dark)">Aucun employé</div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl p-5 border transition-colors"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                        <h3 class="text-sm font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Bulletins / mois</h3>
                        <div style="height: 80px; position: relative;">
                            <canvas ref="bulletinsCanvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Analyses RH ─────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 items-stretch">

                <!-- Effectifs par département (2/4) -->
                <!-- <div class="xl:col-span-2">
                    <ChartCard title="Effectifs par département" subtitle="Affectations actives" height="160px">
                        <canvas ref="deptCanvas"></canvas>
                        <template #footer>
                            <div v-if="!effectifsParDept.length" class="absolute inset-0 flex items-center justify-center text-xs" :class="sL(dark)">
                                Aucune donnée disponible
                            </div>
                        </template>
                    </ChartCard>
                </div> -->

                <!-- Répartition H/F (1/4) -->
                <div>
                    <ChartCard title="Répartition H/F" subtitle="Par genre" height="auto">
                        <div class="flex items-center gap-4 pt-1 pb-2">
                            <div class="relative w-20 h-20 shrink-0">
                                <canvas ref="genreCanvas"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-sm font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">{{ totalGenre }}</span>
                                    <span class="text-[8px]" :class="sL(dark)">total</span>
                                </div>
                            </div>
                            <div class="space-y-2 flex-1 text-xs">
                                <div v-for="g in repartitionGenre" :key="g.label" class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: g.color }"></span>
                                    <span :class="dark ? 'text-slate-300' : 'text-slate-600'">{{ g.label }}</span>
                                    <span class="ml-auto font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ g.count }}</span>
                                </div>
                                <div v-if="!repartitionGenre.length" class="text-[10px]" :class="sL(dark)">Non renseigné</div>
                            </div>
                        </div>
                    </ChartCard>
                </div>

                <!-- Recrutements (1/4) -->
                <div>
                    <ChartCard title="Recrutements" subtitle="6 derniers mois" height="120px">
                        <canvas ref="recrutementsCanvas"></canvas>
                        <template #badge>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                :class="dark ? 'bg-cyan-900/30 text-cyan-400' : 'bg-cyan-50 text-cyan-600'">
                                {{ recrutementsParMois.reduce((s, d) => s + (d.count ?? 0), 0) }} total
                            </span>
                        </template>
                    </ChartCard>
                </div>
            </div>

            <!-- ── Congés & Absences par mois ─────────────────────────────── -->
            <ChartCard title="Congés & Absences par mois" subtitle="6 derniers mois · comparaison" height="130px">
                <canvas ref="congesAbsCanvas"></canvas>
            </ChartCard>

            <!-- ── Congés en attente + Activité récente ───────────────────── -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

                <!-- Congés en attente -->
                <div class="rounded-2xl border overflow-hidden transition-colors"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <div class="px-5 py-4 border-b flex items-center justify-between"
                        :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                        <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Congés en attente</h3>
                        <Link href="/conges?statut=pending"
                            class="text-decoration-none text-[10px] font-semibold px-2 py-1 rounded-lg transition-all"
                            :class="dark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-900'">
                            Tout voir →
                        </Link>
                    </div>
                    <div v-if="!congesPending.length" class="px-5 py-8 text-center text-xs" :class="sL(dark)">
                        Aucune demande en attente
                    </div>
                    <div v-for="(c, i) in congesPending" :key="c.id"
                        class="card-anim flex items-center gap-3 px-5 py-3 border-b transition-colors"
                        :style="{ animationDelay: i * 40 + 'ms' }"
                        :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-50 hover:bg-slate-50'">
                        <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-semibold truncate" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ c.employe }}</p>
                            <p class="text-[10px]" :class="sL(dark)">{{ c.date_debut }} → {{ c.date_fin }}</p>
                        </div>
                        <Link :href="`/conges/${c.id}/edit`"
                            class="text-decoration-none px-2 py-1 rounded-lg text-[10px] font-semibold border transition-all shrink-0"
                            :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-900'">
                            Voir
                        </Link>
                    </div>
                </div>

                <!-- Activité récente (2/3) -->
                <div class="xl:col-span-2 rounded-2xl border overflow-hidden transition-colors"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <div class="px-5 py-4 border-b flex items-center justify-between"
                        :class="dark ? 'border-[#21262d]' : 'border-slate-100'">
                        <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Activité récente</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                            :class="dark ? 'bg-[#21262d] text-slate-400' : 'bg-slate-100 text-slate-500'">
                            Congés + Bulletins
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-max">
                        <thead>
                            <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                <th class="px-4 py-1.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Événement</th>
                                <th class="px-4 py-1.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Module</th>
                                <th class="px-4 py-1.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Statut</th>
                                <th class="px-4 py-1.5 text-right text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!activites.length">
                                <td colspan="4" class="px-4 py-8 text-center text-xs" :class="sL(dark)">Aucune activité récente</td>
                            </tr>
                            <tr v-for="(item, i) in activites" :key="i"
                                class="row-anim border-t transition-colors"
                                :style="{ animationDelay: i * 30 + 'ms' }"
                                :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-50 hover:bg-slate-50'">
                                <td class="px-4 py-2 font-medium text-[11px] max-w-[200px]" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                    <span class="truncate block">{{ item.ev }}</span>
                                </td>
                                <td class="px-4 py-2 text-[11px]" :class="sL(dark)">{{ item.module }}</td>
                                <td class="px-4 py-2">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="statusBadge(item.color)">{{ item.statut }}</span>
                                </td>
                                <td class="px-4 py-2 text-right text-[10px]" :class="sL(dark)">{{ item.date }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
