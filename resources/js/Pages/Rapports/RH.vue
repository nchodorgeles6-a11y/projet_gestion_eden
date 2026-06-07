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
    parEmploye:  { type: Array,  default: () => [] },
    joursOuvres: { type: Number, default: 0 },
    annees:      { type: Array,  default: () => [] },
    filtres:     { type: Object, default: () => ({}) },
});

// ── Filtres ──────────────────────────────────────────────────────────────────
const annee   = ref(props.filtres.annee   ?? new Date().getFullYear());
const moisDeb = ref(props.filtres.moisDeb ?? 1);
const moisFin = ref(props.filtres.moisFin ?? new Date().getMonth() + 1);

const moisLabels = [
    { v: 1, l: 'Janvier' }, { v: 2, l: 'Février' }, { v: 3, l: 'Mars' },
    { v: 4, l: 'Avril' },   { v: 5, l: 'Mai' },      { v: 6, l: 'Juin' },
    { v: 7, l: 'Juillet' }, { v: 8, l: 'Août' },     { v: 9, l: 'Septembre' },
    { v: 10, l: 'Octobre' },{ v: 11, l: 'Novembre' },{ v: 12, l: 'Décembre' },
];

const appliquer = () => {
    router.get('/rapports/rh', {
        annee: annee.value, mois_debut: moisDeb.value, mois_fin: moisFin.value,
    }, { preserveScroll: true });
};

// ── Recherche ────────────────────────────────────────────────────────────────
const search = ref('');
const sorted = ref('taux'); // 'taux' | 'absences' | 'nom'

const liste = computed(() => {
    const q = search.value.toLowerCase();
    let data = props.parEmploye.filter(e =>
        !q || e.nom.toLowerCase().includes(q) || e.poste.toLowerCase().includes(q) || e.departement.toLowerCase().includes(q)
    );
    if (sorted.value === 'taux')     data = [...data].sort((a, b) => a.taux_presence - b.taux_presence);
    if (sorted.value === 'absences') data = [...data].sort((a, b) => b.absences - a.absences);
    if (sorted.value === 'nom')      data = [...data].sort((a, b) => a.nom.localeCompare(b.nom));
    return data;
});

// ── Stats globales ───────────────────────────────────────────────────────────
const stats = computed(() => {
    const total = props.parEmploye.length;
    const absTot = props.parEmploye.reduce((s, e) => s + e.absences, 0);
    const retTot = props.parEmploye.reduce((s, e) => s + e.retards, 0);
    const jusTot = props.parEmploye.reduce((s, e) => s + e.justifiees, 0);
    const txMoyen = total > 0
        ? Math.round(props.parEmploye.reduce((s, e) => s + e.taux_presence, 0) / total)
        : 100;
    return { total, absTot, retTot, jusTot, txMoyen };
});

// ── Chart stacked bar ────────────────────────────────────────────────────────
const canvasChart = ref(null);
let chart = null;

const gridColor = computed(() => dark.value ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)');
const textColor = computed(() => dark.value ? '#94a3b8' : '#64748b');

const initChart = () => {
    chart?.destroy(); chart = null;
    nextTick(() => {
        if (!canvasChart.value) return;
        const emp = liste.value.slice(0, 15); // max 15 pour la lisibilité
        chart = new Chart(canvasChart.value, {
            type: 'bar',
            data: {
                labels: emp.map(e => e.nom.split(' ')[0]),
                datasets: [
                    {
                        label: 'Présents',
                        data: emp.map(e => e.presents),
                        backgroundColor: 'rgba(16,185,129,0.75)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Absents',
                        data: emp.map(e => e.absences),
                        backgroundColor: 'rgba(239,68,68,0.75)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Retards',
                        data: emp.map(e => e.retards),
                        backgroundColor: 'rgba(245,158,11,0.75)',
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: textColor.value, font: { size: 11 } } } },
                scales: {
                    x: { stacked: true, ticks: { color: textColor.value, font: { size: 10 } }, grid: { color: gridColor.value } },
                    y: { stacked: true, ticks: { color: textColor.value, font: { size: 10 } }, grid: { color: gridColor.value } },
                },
            },
        });
    });
};

onMounted(initChart);
onUnmounted(() => chart?.destroy());
watch([dark, liste], initChart);

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-200';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';

const tauxStyle = (tx) => {
    if (tx >= 95) return 'text-emerald-600';
    if (tx >= 80) return 'text-amber-600';
    return 'text-rose-600';
};
const tauxBg = (tx) => {
    if (tx >= 95) return 'bg-emerald-500';
    if (tx >= 80) return 'bg-amber-500';
    return 'bg-rose-500';
};
</script>

<template>
    <Head title="Rapport RH" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

            <!-- En-tête + Filtres -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Rapport RH — Présences</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ joursOuvres }} jours ouvrés sur la période · {{ parEmploye.length }} employé(s)</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
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

            <!-- KPIs -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
                <div v-for="(item, i) in [
                    { label: 'Taux moyen',    val: stats.txMoyen, suffix: '%', color: tauxStyle(stats.txMoyen), dur: 1000 },
                    { label: 'Employés',       val: stats.total,   suffix: '',  color: dark ? 'text-white' : 'text-slate-800', dur: 900 },
                    { label: 'Total absences', val: stats.absTot,  suffix: '',  color: 'text-rose-500',   dur: 1100 },
                    { label: 'Total retards',  val: stats.retTot,  suffix: '',  color: 'text-amber-500',  dur: 1200 },
                    { label: 'Justifiées',     val: stats.jusTot,  suffix: '',  color: 'text-blue-500',   dur: 1300 },
                ]" :key="i"
                    class="kpi-card rounded-2xl border p-4 transition-all duration-300 hover:scale-[1.03]"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">{{ item.label }}</p>
                    <p class="text-2xl font-black" :class="item.color">
                        <AnimatedStat :value="item.val" :suffix="item.suffix" :duration="item.dur" />
                    </p>
                </div>
            </div>

            <!-- Chart -->
            <div class="rounded-2xl border p-5" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <p class="text-xs font-bold mb-4" :class="dark ? 'text-white' : 'text-slate-800'">Présences / Absences / Retards par employé (15 premiers)</p>
                <div class="h-60">
                    <canvas ref="canvasChart"></canvas>
                </div>
            </div>

            <!-- Tableau détaillé -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="px-5 py-3 border-b flex items-center gap-3" :class="div(dark)">
                    <input v-model="search" type="text" placeholder="Nom, poste, département…"
                        class="rounded-xl border px-3 py-1.5 text-xs w-52 transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                    <select v-model="sorted" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option value="taux">Trier : Taux ↑</option>
                        <option value="absences">Trier : Absences ↓</option>
                        <option value="nom">Trier : Nom</option>
                    </select>
                    <p class="text-[10px] ml-auto" :class="sL(dark)">{{ liste.length }} employé(s)</p>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Employé</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Poste / Département</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-emerald-500">Présents</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-rose-500">Absences</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-amber-500">Retards</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-blue-500">Justifiées</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Taux présence</th>
                        </tr>
                    </thead>
                    <TransitionGroup tag="tbody" name="tbl">
                        <tr v-if="liste.length === 0" key="empty">
                            <td colspan="7" class="py-10 text-center text-xs" :class="sL(dark)">Aucun résultat.</td>
                        </tr>
                        <tr v-for="e in liste" :key="e.id"
                            class="border-t transition-colors"
                            :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50']">
                            <td class="px-5 py-3">
                                <p class="text-xs font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ e.nom }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-xs" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ e.poste }}</p>
                                <p class="text-[10px]" :class="sL(dark)">{{ e.departement }}</p>
                            </td>
                            <td class="px-5 py-3 text-center text-xs font-semibold text-emerald-600">{{ e.presents }}</td>
                            <td class="px-5 py-3 text-center text-xs font-semibold text-rose-600">{{ e.absences }}</td>
                            <td class="px-5 py-3 text-center text-xs font-semibold text-amber-600">{{ e.retards }}</td>
                            <td class="px-5 py-3 text-center">
                                <span v-if="e.justifiees > 0" class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                    {{ e.justifiees }}
                                </span>
                                <span v-else class="text-[10px]" :class="sL(dark)">—</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 rounded-full" :class="dark ? 'bg-[#21262d]' : 'bg-slate-100'">
                                        <div class="h-1.5 rounded-full transition-all" :class="tauxBg(e.taux_presence)"
                                            :style="{ width: e.taux_presence + '%' }"></div>
                                    </div>
                                    <span class="text-[10px] font-bold w-10 text-right" :class="tauxStyle(e.taux_presence)">
                                        {{ e.taux_presence }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </TransitionGroup>
                </table>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
