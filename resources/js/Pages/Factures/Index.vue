<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const { dark } = useTheme();

const props = defineProps({
    factures:     { type: Object, default: () => ({}) },
    totaux:       { type: Object, default: () => ({}) },
    departements: { type: Array,  default: () => [] },
    categories:   { type: Array,  default: () => [] },
});

const search   = ref('');
const filStatut = ref('');

const liste = computed(() => {
    const q = search.value.toLowerCase();
    return (props.factures.data ?? []).filter(f =>
        (!q || f.fournisseur?.toLowerCase().includes(q) || f.numero?.toLowerCase().includes(q) || f.categorie?.toLowerCase().includes(q)) &&
        (!filStatut.value || f.statut === filStatut.value)
    );
});

const payer = (id) => {
    if (!confirm('Marquer cette facture comme payée ? Une transaction de dépense sera créée automatiquement.')) return;
    router.post(`/factures/${id}/payer`);
};

const supprimer = (id) => {
    if (!confirm('Supprimer cette facture ?')) return;
    router.delete(`/factures/${id}`);
};

const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(Math.round(n)) : '0';

const statutLabel = { en_attente: 'En attente', payee: 'Payée', annulee: 'Annulée' };
const statutColor = {
    en_attente: dark.value ? 'bg-amber-900/30 text-amber-400' : 'bg-amber-100 text-amber-700',
    payee:      dark.value ? 'bg-emerald-900/30 text-emerald-400' : 'bg-emerald-100 text-emerald-700',
    annulee:    dark.value ? 'bg-slate-700 text-slate-400' : 'bg-slate-100 text-slate-500',
};

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const div = (d) => d ? 'border-[#21262d]' : 'border-slate-200';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';
</script>

<template>
    <Head title="Factures fournisseurs" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">

            <!-- En-tête -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Factures fournisseurs</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">Suivi des achats, prestataires et charges externes</p>
                </div>
                <Link href="/factures/create"
                    class="text-decoration-none inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle facture
                </Link>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-for="(item, i) in [
                    { label: 'Total factures',  val: totaux.total,      suffix: '',       color: dark ? 'text-white' : 'text-slate-800', isCount: true },
                    { label: 'À payer',         val: totaux.en_attente, suffix: ' FCFA',  color: 'text-amber-500',   isCount: false },
                    { label: 'Payées',          val: totaux.payees,     suffix: ' FCFA',  color: 'text-emerald-500', isCount: false },
                    { label: 'En retard',       val: totaux.en_retard,  suffix: '',       color: 'text-rose-500',    isCount: true },
                ]" :key="i"
                    class="rounded-2xl border p-4 transition-all"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-1" :class="sL(dark)">{{ item.label }}</p>
                    <p class="text-xl font-black" :class="item.color">
                        {{ item.isCount ? item.val : fmt(item.val) + item.suffix }}
                    </p>
                </div>
            </div>

            <!-- Filtres -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Fournisseur, numéro, catégorie…"
                        class="pl-8 pr-3 py-1.5 rounded-xl border text-xs w-56 transition-all focus:outline-none focus:ring-2" :class="inp(dark)" />
                </div>
                <select v-model="filStatut" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                    <option value="">Tous statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="payee">Payées</option>
                    <option value="annulee">Annulées</option>
                </select>
                <button v-if="search || filStatut" @click="search = ''; filStatut = ''"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    Réinitialiser
                </button>
                <span class="text-[10px] ml-auto" :class="sL(dark)">{{ liste.length }} facture(s)</span>
            </div>

            <!-- Tableau -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <table class="w-full text-sm">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">N° / Fournisseur</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Catégorie</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Département</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Date</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Échéance</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Montant TTC</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Statut</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-wider" :class="sL(dark)">Actions</th>
                        </tr>
                    </thead>
                    <TransitionGroup tag="tbody" name="tbl">
                        <tr v-if="!liste.length" key="empty">
                            <td colspan="8" class="py-12 text-center text-xs" :class="sL(dark)">
                                Aucune facture trouvée.
                                <Link href="/factures/create" class="ml-2 text-[#760078] font-semibold hover:underline">Ajouter →</Link>
                            </td>
                        </tr>
                        <tr v-for="f in liste" :key="f.id"
                            class="border-t transition-colors"
                            :class="[div(dark), dark ? 'hover:bg-white/[0.02]' : 'hover:bg-slate-50',
                                f.en_retard ? (dark ? 'bg-rose-900/[0.05]' : 'bg-rose-50/50') : '']">
                            <td class="px-5 py-3">
                                <p class="text-xs font-bold" :class="dark ? 'text-slate-200' : 'text-slate-800'">{{ f.fournisseur }}</p>
                                <p class="text-[10px] font-mono" :class="sL(dark)">{{ f.numero }}</p>
                            </td>
                            <td class="px-5 py-3 text-xs" :class="sL(dark)">{{ f.categorie ?? '—' }}</td>
                            <td class="px-5 py-3 text-xs" :class="sL(dark)">{{ f.departement ?? '—' }}</td>
                            <td class="px-5 py-3 text-center text-[11px]" :class="sL(dark)">{{ f.date_facture }}</td>
                            <td class="px-5 py-3 text-center text-[11px]">
                                <span :class="f.en_retard ? 'text-rose-500 font-bold' : sL(dark)">
                                    {{ f.date_echeance ?? '—' }}
                                    <span v-if="f.en_retard" class="ml-1 text-[9px] bg-rose-100 text-rose-700 px-1 rounded">En retard</span>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm font-bold" :class="dark ? 'text-slate-200' : 'text-slate-800'">
                                {{ fmt(f.montant_ttc) }} FCFA
                                <p class="text-[10px] font-normal" :class="sL(dark)">HT: {{ fmt(f.montant_ht) }} · TVA {{ f.tva }}%</p>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold" :class="statutColor[f.statut]">
                                    {{ statutLabel[f.statut] }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button v-if="f.statut === 'en_attente'" @click="payer(f.id)"
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                                        Payer
                                    </button>
                                    <Link :href="`/factures/${f.id}/edit`"
                                        class="p-1.5 rounded-lg transition-colors"
                                        :class="dark ? 'text-slate-500 hover:text-slate-300 hover:bg-white/5' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-100'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </Link>
                                    <button @click="supprimer(f.id)"
                                        class="p-1.5 rounded-lg transition-colors"
                                        :class="dark ? 'text-slate-500 hover:text-rose-400 hover:bg-rose-900/20' : 'text-slate-400 hover:text-rose-600 hover:bg-rose-50'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </TransitionGroup>
                </table>
            </div>

            <Pagination :links="factures.links" :meta="factures" />

        </div>
    </AuthenticatedLayout>
</template>
