<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const { dark } = useTheme();
const props = defineProps({ bulletins: Object });

const search = ref('');

const filtered = computed(() =>
    (props.bulletins.data ?? []).filter(b => {
        const q = search.value.toLowerCase();
        return !q || b.user?.nom?.toLowerCase().includes(q) || b.user?.prenom?.toLowerCase().includes(q) || b.reference?.toLowerCase().includes(q);
    })
);

const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(n) : '0';

const statutLabel = { brouillon: 'Brouillon', valide: 'Validé', paye: 'Payé' };
const statutColor = {
    brouillon: 'bg-slate-100 text-slate-600',
    valide:    'bg-blue-100 text-blue-700',
    paye:      'bg-emerald-100 text-emerald-700',
};

const destroy = (id) => {
    if (confirm('Supprimer ce bulletin ?')) router.delete(`/bulletins-paie/${id}`);
};

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-100';
</script>

<template>
    <Head title="Bulletins de paie" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6">

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Bulletins de paie</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ bulletins.meta?.total ?? 0 }} bulletin(s) au total</p>
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="search" type="text" placeholder="Rechercher…"
                        class="rounded-xl border px-3 py-1.5 text-xs transition-all focus:outline-none focus:ring-2 w-44"
                        :class="dark ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20' : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15'" />
                    <a href="/bulletins-paie/export"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </a>
                    <Link href="/bulletins-paie/create"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white
                               bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nouveau bulletin
                    </Link>
                </div>
            </div>

            <div class="rounded-2xl border overflow-hidden transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Référence</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Employé</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Période</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Brut</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Net à payer</th>
                            <th class="px-5 py-2.5 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Statut</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Actions</th>
                        </tr>
                    </thead>
                    <TransitionGroup tag="tbody" name="tbl">
                        <tr v-if="filtered.length === 0" key="empty">
                            <td colspan="7" class="px-5 py-10 text-center text-sm" :class="sL(dark)">Aucun bulletin trouvé</td>
                        </tr>
                        <tr v-for="b in filtered" :key="b.id"
                            class="border-t transition-colors"
                            :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-100 hover:bg-slate-50'">
                            <td class="px-5 py-3">
                                <span class="font-mono text-[11px] font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ b.reference }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <Link :href="`/users/${b.user_id}`" class="font-semibold text-xs hover:text-[#760078] transition-colors" :class="dark ? 'text-slate-200' : 'text-slate-800'">
                                    {{ b.user?.nom }} {{ b.user?.prenom }}
                                </Link>
                            </td>
                            <td class="px-5 py-3 text-xs" :class="dark ? 'text-slate-300' : 'text-slate-700'">
                                {{ b.mois }} {{ b.annee }}
                            </td>
                            <td class="px-5 py-3 text-right text-xs font-semibold" :class="dark ? 'text-slate-300' : 'text-slate-600'">
                                {{ fmt(b.salaire_brut) }} FCFA
                            </td>
                            <td class="px-5 py-3 text-right text-xs font-extrabold text-[#760078]">
                                {{ fmt(b.net_a_payer) }} FCFA
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold" :class="statutColor[b.statut]">
                                    {{ statutLabel[b.statut] }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="`/bulletins-paie/${b.id}`"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all"
                                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Voir
                                    </Link>
                                    <button @click="destroy(b.id)"
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all border-rose-200 text-rose-500 hover:bg-rose-50">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </TransitionGroup>
                </table>
                </div>
            </div>

            <Pagination :links="bulletins.links" :meta="bulletins" />
        </div>
    </AuthenticatedLayout>
</template>
