<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { computed, watch } from 'vue';

const { dark } = useTheme();

const props = defineProps({
    facture:      { type: Object, default: null },
    departements: { type: Array,  default: () => [] },
    categories:   { type: Array,  default: () => [] },
    numero:       { type: String, default: '' },
});

const isEdit = computed(() => !!props.facture);

const form = useForm({
    numero:         props.facture?.numero         ?? props.numero,
    fournisseur:    props.facture?.fournisseur    ?? '',
    description:    props.facture?.description    ?? '',
    montant_ht:     props.facture?.montant_ht     ?? '',
    tva:            props.facture?.tva            ?? 0,
    date_facture:   props.facture?.date_facture   ?? '',
    date_echeance:  props.facture?.date_echeance  ?? '',
    statut:         props.facture?.statut         ?? 'en_attente',
    categorie:      props.facture?.categorie      ?? '',
    departement_id: props.facture?.departement_id ?? '',
});

// Auto-calcul TTC
const montantTTC = computed(() => {
    const ht  = parseFloat(form.montant_ht) || 0;
    const tva = parseFloat(form.tva) || 0;
    return (ht * (1 + tva / 100)).toFixed(0);
});

const soumettre = () => {
    if (isEdit.value) {
        form.put(`/factures/${props.facture.id}`, { onSuccess: () => {} });
    } else {
        form.post('/factures', { onSuccess: () => {} });
    }
};

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-white border-slate-200 text-slate-900 focus:border-[#760078] focus:ring-[#760078]/15';
</script>

<template>
    <Head :title="isEdit ? 'Modifier la facture' : 'Nouvelle facture'" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 max-w-3xl mx-auto space-y-4 sm:space-y-5">

            <!-- En-tête -->
            <div class="flex items-center gap-3">
                <Link href="/factures" class="p-2 rounded-xl border transition-all text-decoration-none"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-900'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">
                        {{ isEdit ? 'Modifier la facture' : 'Nouvelle facture fournisseur' }}
                    </h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ isEdit ? facture?.numero : numero }}</p>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="rounded-2xl border p-6 transition-colors"
                :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <form @submit.prevent="soumettre" class="space-y-5">

                    <!-- Numéro + Fournisseur -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">N° Facture *</label>
                            <input v-model="form.numero" type="text" required :readonly="isEdit"
                                class="w-full rounded-xl border px-3 py-2 text-xs font-mono focus:outline-none focus:ring-2" :class="inp(dark)" />
                            <p v-if="form.errors.numero" class="text-[10px] text-rose-500 mt-1">{{ form.errors.numero }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Fournisseur *</label>
                            <input v-model="form.fournisseur" type="text" required
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)"
                                placeholder="Nom du fournisseur ou prestataire" />
                            <p v-if="form.errors.fournisseur" class="text-[10px] text-rose-500 mt-1">{{ form.errors.fournisseur }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Description</label>
                        <textarea v-model="form.description" rows="2"
                            class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2 resize-none" :class="inp(dark)"
                            placeholder="Objet de la facture…"></textarea>
                    </div>

                    <!-- Montants -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Montant HT (FCFA) *</label>
                            <input v-model="form.montant_ht" type="number" min="0" step="1" required
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)" placeholder="0" />
                            <p v-if="form.errors.montant_ht" class="text-[10px] text-rose-500 mt-1">{{ form.errors.montant_ht }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">TVA (%)</label>
                            <input v-model="form.tva" type="number" min="0" max="100" step="0.5"
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)" placeholder="0" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Montant TTC (calculé)</label>
                            <div class="rounded-xl border px-3 py-2 text-xs font-bold text-[#760078]"
                                :class="dark ? 'bg-[#760078]/10 border-[#760078]/30' : 'bg-purple-50 border-purple-200'">
                                {{ Number(montantTTC).toLocaleString('fr-FR') }} FCFA
                            </div>
                        </div>
                    </div>

                    <!-- Dates + Statut -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Date facture *</label>
                            <input v-model="form.date_facture" type="date" required
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)" />
                            <p v-if="form.errors.date_facture" class="text-[10px] text-rose-500 mt-1">{{ form.errors.date_facture }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Date d'échéance</label>
                            <input v-model="form.date_echeance" type="date"
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Statut *</label>
                            <select v-model="form.statut" required
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="en_attente">En attente</option>
                                <option value="payee">Payée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                        </div>
                    </div>

                    <!-- Catégorie + Département -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Catégorie de charge</label>
                            <select v-model="form.categorie"
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="">Non catégorisé</option>
                                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider mb-1" :class="sL(dark)">Département</label>
                            <select v-model="form.departement_id"
                                class="w-full rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="">Général / Non affecté</option>
                                <option v-for="d in departements" :key="d.id" :value="d.id">{{ d.nom }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Note statut payée -->
                    <div v-if="form.statut === 'payee'" class="flex items-start gap-2 rounded-xl p-3 text-xs"
                        :class="dark ? 'bg-emerald-900/20 text-emerald-400 border border-emerald-800/30' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Une transaction de dépense sera automatiquement créée dans le journal comptable.
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all disabled:opacity-50">
                            {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Mettre à jour' : 'Enregistrer la facture') }}
                        </button>
                        <Link href="/factures" class="text-decoration-none px-5 py-2 rounded-xl text-xs font-semibold border transition-all"
                            :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-900'">
                            Annuler
                        </Link>
                    </div>
                </form>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
