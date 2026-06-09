<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { computed } from 'vue';

const { dark } = useTheme();
const props = defineProps({ bulletin: Object });

const b = props.bulletin;
const user = b.user;

const affectation = user?.affectations?.slice(-1)[0] ?? null;
const dept = affectation?.poste?.departement?.nom ?? '—';
const poste = affectation?.poste?.nom ?? '—';

const fmt = (n) => new Intl.NumberFormat('fr-CI').format(Math.round(Number(n) || 0));

const statutLabel = { brouillon: 'Brouillon', valide: 'Validé', paye: 'Payé' };
const statutColor = {
    brouillon: 'bg-slate-100 text-slate-600 border-slate-200',
    valide:    'bg-blue-100 text-blue-700 border-blue-200',
    paye:      'bg-emerald-100 text-emerald-700 border-emerald-200',
};

const modeLabel = { virement: 'Virement bancaire', especes: 'Espèces', mobile_money: 'Mobile Money', cheque: 'Chèque' };

const valider  = () => router.post(`/bulletins-paie/${b.id}/valider`);
const payer    = () => router.post(`/bulletins-paie/${b.id}/payer`);
const imprimer = () => window.open(`/bulletins-paie/${b.id}/print`, '_blank');

const div = (d) => d ? 'border-[#21262d]' : 'border-slate-200';
</script>

<template>
    <Head :title="`Bulletin ${b.reference}`" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6">

            <!-- Barre d'actions -->
            <div class="flex flex-wrap items-start justify-between gap-3 mb-5 print:hidden">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                        Bulletin de paie — {{ b.mois }} {{ b.annee }}
                    </h2>
                    <p class="text-[11px] mt-0.5" :class="dark ? 'text-slate-400' : 'text-slate-500'">
                        {{ b.reference }} · {{ user?.nom }} {{ user?.prenom }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="statutColor[b.statut]">
                        {{ statutLabel[b.statut] }}
                    </span>
                    <Link :href="`/bulletins-paie/${b.id}/edit`"
                        class="text-decoration-none inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Modifier
                    </Link>
                    <button v-if="b.statut === 'brouillon'" @click="valider"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 border-blue-500 text-blue-600 hover:bg-blue-50 transition-all">
                        Valider
                    </button>
                    <button v-if="b.statut === 'valide'" @click="payer"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition-all">
                        Marquer payé
                    </button>
                    <button @click="imprimer"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Télécharger PDF
                    </button>
                    <Link href="/bulletins-paie"text-decoration-none class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Retour
                    </Link>
                </div>
            </div>

            <!-- ══ BULLETIN ══ -->
            <div id="bulletin-print" class="max-w-4xl mx-auto rounded-2xl border overflow-hidden print:border-0 print:shadow-none"
                :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">

                <!-- En-tête entreprise -->
                <div class="p-6 border-b flex items-start justify-between" :class="div(dark)">
                    <div>
                        <div class="mb-2">
                            <div class="inline-block rounded-xl px-2 py-1" :class="dark ? 'bg-white' : ''">
                                <img src="/images/logo.svg" alt="EdenCorporate" class="h-10 w-auto object-contain" />
                            </div>
                            <p class="text-[10px] mt-1" :class="dark ? 'text-slate-400' : 'text-slate-500'">
                                Abidjan, Côte d'Ivoire
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black tracking-tight" :class="dark ? 'text-white' : 'text-slate-800'">BULLETIN DE PAIE</p>
                        <p class="text-xs mt-0.5" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ b.mois }} {{ b.annee }}</p>
                        <p class="font-mono text-[11px] mt-1 text-[#760078] font-bold">{{ b.reference }}</p>
                    </div>
                </div>

                <!-- Informations salarié -->
                <div class="p-6 border-b" :class="div(dark)">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="dark ? 'text-slate-500' : 'text-slate-400'">Informations du salarié</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-2">
                        <div v-for="([label, val]) in [['Nom & Prénom', (user?.nom ?? '') + ' ' + (user?.prenom ?? '')], ['Poste', poste], ['Service / Département', dept], ['Type de contrat', user?.type_contrat === 'employe' ? 'CDI / CDD' : 'Prestataire'], ['Période de paie', b.mois + ' ' + b.annee], ['Référence', b.reference]]"
                            :key="label">
                            <p class="text-[10px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">{{ label }}</p>
                            <p class="text-xs font-semibold col-span-2" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ val }}</p>
                        </div>
                    </div>
                </div>

                <!-- Éléments de rémunération -->
                <div class="border-b" :class="div(dark)">
                    <div class="px-6 py-3 border-b" :class="dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-slate-50 border-slate-200'">
                        <p class="text-[10px] font-bold uppercase tracking-widest" :class="dark ? 'text-slate-400' : 'text-slate-500'">Éléments de rémunération</p>
                    </div>
                    <table class="w-full text-xs">
                        <thead>
                            <tr :class="dark ? 'border-b border-[#21262d] text-slate-500' : 'border-b border-slate-100 text-slate-400'">
                                <th class="px-6 py-2 text-left font-semibold uppercase text-[9px] tracking-wider">Désignation</th>
                                <th class="px-6 py-2 text-center font-semibold uppercase text-[9px] tracking-wider">Base</th>
                                <th class="px-6 py-2 text-right font-semibold uppercase text-[9px] tracking-wider">Montant (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="([label, base, val]) in [
                                ['Salaire de base', '', b.salaire_base],
                                ['Prime de transport', '', b.prime_transport],
                                ['Prime de logement', '', b.prime_logement],
                                ['Prime de fonction', '', b.prime_fonction],
                                ['Prime de rendement', '', b.prime_rendement],
                                ['Prime de panier', '', b.prime_panier],
                                ['Bonus annuel', '', b.bonus_annuel],
                                ['Heures supplémentaires', b.heures_sup > 0 ? b.heures_sup + ' h' : '', b.heures_sup * b.taux_heures_sup],
                                ['Avantages en nature', '', b.avantages_nature_montant],
                            ].filter(([,, v]) => Number(v) > 0)" :key="label"
                                class="border-b transition-colors" :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-50 hover:bg-slate-50'">
                                <td class="px-6 py-2.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</td>
                                <td class="px-6 py-2.5 text-center" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ base }}</td>
                                <td class="px-6 py-2.5 text-right font-semibold" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ fmt(val) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2" :class="dark ? 'border-[#760078]/40 bg-[#760078]/5' : 'border-[#760078]/20 bg-[#760078]/3'">
                                <td class="px-6 py-3 font-extrabold text-sm" :class="dark ? 'text-white' : 'text-slate-800'">Salaire Brut</td>
                                <td></td>
                                <td class="px-6 py-3 text-right font-extrabold text-sm text-[#760078]">{{ fmt(b.salaire_brut) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Retenues salariales -->
                <div class="border-b" :class="div(dark)">
                    <div class="px-6 py-3 border-b" :class="dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-slate-50 border-slate-200'">
                        <p class="text-[10px] font-bold uppercase tracking-widest" :class="dark ? 'text-slate-400' : 'text-slate-500'">Retenues salariales</p>
                    </div>
                    <table class="w-full text-xs">
                        <tbody>
                            <tr v-for="([label, base, val]) in [
                                ['CNPS salarié', fmt(b.salaire_brut), b.cnps_salarie],
                                ['Assurance maladie', fmt(b.salaire_brut), b.assurance_maladie_salarie],
                                ['IGR (Impôt Général sur le Revenu)', '', b.igr],
                                ['IS (Impôt sur salaire)', '', b.is_salaire],
                                ['Avance sur salaire', '', b.avance_salaire],
                                ['Prêt entreprise', '', b.pret_entreprise],
                                ['Autres retenues', '', b.autres_retenues],
                            ].filter(([,, v]) => Number(v) > 0)" :key="label"
                                class="border-b transition-colors" :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-50 hover:bg-slate-50'">
                                <td class="px-6 py-2.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">{{ label }}</td>
                                <td class="px-6 py-2.5 text-center" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ base }}</td>
                                <td class="px-6 py-2.5 text-right font-semibold text-rose-500">{{ fmt(val) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2" :class="dark ? 'border-rose-500/30 bg-rose-500/5' : 'border-rose-200 bg-rose-50/50'">
                                <td class="px-6 py-3 font-extrabold text-sm" :class="dark ? 'text-white' : 'text-slate-800'">Total retenues</td>
                                <td></td>
                                <td class="px-6 py-3 text-right font-extrabold text-sm text-rose-500">{{ fmt(b.total_retenues) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Net à payer -->
                <div class="p-6 border-b" :class="div(dark)">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xl font-black" :class="dark ? 'text-white' : 'text-slate-800'">NET À PAYER</p>
                            <p class="text-xs mt-0.5" :class="dark ? 'text-slate-400' : 'text-slate-500'">Mode : {{ modeLabel[b.mode_paiement] }} · {{ b.date_paiement ?? 'Date non définie' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black text-[#760078]">{{ fmt(b.net_a_payer) }} FCFA</p>
                        </div>
                    </div>
                </div>

                <!-- Cotisations patronales -->
                <div class="p-6 border-b" :class="div(dark)">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-3" :class="dark ? 'text-slate-500' : 'text-slate-400'">Cotisations patronales (non déduites du salarié)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1.5">
                        <div v-for="([label, val]) in [['CNPS Employeur', b.cnps_employeur], ['Accident du travail', b.accident_travail], ['Prestations familiales', b.prestations_familiales], ['Formation professionnelle', b.formation_professionnelle]]" :key="label"
                            class="flex items-center justify-between text-xs">
                            <span :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ label }}</span>
                            <span class="font-semibold" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ fmt(val) }} FCFA</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t text-sm font-bold" :class="dark ? 'border-[#21262d] text-white' : 'border-slate-200 text-slate-800'">
                        <span>Coût total pour l'entreprise</span>
                        <span>{{ fmt(b.cout_total_employeur) }} FCFA</span>
                    </div>
                </div>

                <!-- Signatures -->
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <p class="text-xs font-bold mb-6" :class="dark ? 'text-slate-300' : 'text-slate-700'">L'employeur</p>
                            <div class="border-t-2 border-dashed pt-2" :class="dark ? 'border-[#30363d]' : 'border-slate-300'">
                                <p class="text-[10px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">Nom & Signature</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold mb-6" :class="dark ? 'text-slate-300' : 'text-slate-700'">Le salarié</p>
                            <div class="border-t-2 border-dashed pt-2" :class="dark ? 'border-[#30363d]' : 'border-slate-300'">
                                <p class="text-[10px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">Nom & Signature</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-[9px] mt-6" :class="dark ? 'text-slate-600' : 'text-slate-300'">
                        Bulletin généré le {{ new Date().toLocaleDateString('fr-CI') }} — Conservez ce document pendant 5 ans
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

