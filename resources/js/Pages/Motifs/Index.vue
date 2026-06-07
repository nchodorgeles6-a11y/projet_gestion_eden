<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref } from 'vue';

const { dark } = useTheme();
const props = defineProps({ motifs: { type: Array, default: () => [] } });

const typeLabels = { conge: 'Congé', presence: 'Présence', retard: 'Retard' };
const typeColors = {
    conge:    { badge: 'bg-blue-100 text-blue-700',    dot: 'bg-blue-500' },
    presence: { badge: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500' },
    retard:   { badge: 'bg-amber-100 text-amber-700',  dot: 'bg-amber-500' },
};

// ── Ajout ──────────────────────────────────────────────────────────────────
const addForm = useForm({ nom: '', type: 'conge' });
const addSubmit = () => addForm.post('/motifs', { onSuccess: () => addForm.reset() });

// ── Édition inline ─────────────────────────────────────────────────────────
const editId   = ref(null);
const editForm = useForm({ nom: '', type: 'conge' });

const startEdit = (m) => {
    editId.value   = m.id;
    editForm.nom   = m.nom;
    editForm.type  = m.type;
};
const cancelEdit = () => { editId.value = null; };
const saveEdit   = (m) => editForm.put(`/motifs/${m.id}`, { onSuccess: () => { editId.value = null; } });

const destroy = (id) => {
    if (confirm('Supprimer ce motif ?')) router.delete(`/motifs/${id}`);
};

const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white focus:border-[#760078]'
    : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-[#760078]';
</script>

<template>
    <Head title="Motifs" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-5">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Gestion des motifs</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ motifs.length }} motif(s) · Congés, Présences, Retards</p>
                </div>
            </div>

            <!-- ── Formulaire d'ajout ─────────────────────────────────── -->
            <div class="rounded-2xl border p-5 transition-colors" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <p class="text-[10px] font-bold uppercase tracking-widest mb-4" :class="sL(dark)">Ajouter un motif</p>
                <form @submit.prevent="addSubmit" class="flex flex-col sm:flex-row sm:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Nom du motif</label>
                        <input v-model="addForm.nom" type="text" placeholder="Ex : Congé maladie, Permission familiale…" required
                            class="w-full rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#760078]/20 transition-all" :class="inp(dark)" />
                        <p v-if="addForm.errors.nom" class="text-rose-500 text-[10px] mt-1">{{ addForm.errors.nom }}</p>
                    </div>
                    <div class="sm:w-44">
                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Type</label>
                        <select v-model="addForm.type" class="w-full rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#760078]/20 transition-all" :class="inp(dark)">
                            <option value="conge">Congé</option>
                            <option value="presence">Présence</option>
                            <option value="retard">Retard</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="addForm.processing"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] transition-all disabled:opacity-50 shrink-0">
                        Ajouter
                    </button>
                </form>
            </div>

            <!-- ── Liste par type ─────────────────────────────────────── -->
            <div v-for="type in ['conge', 'presence', 'retard']" :key="type">
                <div v-if="motifs.filter(m => m.type === type).length > 0"
                    class="rounded-2xl border overflow-hidden transition-colors"
                    :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">

                    <div class="px-5 py-3 border-b flex items-center gap-2" :class="dark ? 'border-[#21262d] bg-[#0d1117]' : 'border-slate-100 bg-slate-50'">
                        <span class="w-2 h-2 rounded-full" :class="typeColors[type].dot"></span>
                        <p class="text-[10px] font-bold uppercase tracking-widest" :class="sL(dark)">{{ typeLabels[type] }}s</p>
                        <span class="ml-auto text-[10px] font-semibold px-2 py-0.5 rounded-full" :class="typeColors[type].badge">
                            {{ motifs.filter(m => m.type === type).length }}
                        </span>
                    </div>

                    <div v-for="(m, i) in motifs.filter(m => m.type === type)" :key="m.id"
                        class="card-anim flex items-center gap-3 px-5 py-3 border-b last:border-0 transition-colors"
                        :style="{ animationDelay: i * 35 + 'ms' }"
                        :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-50 hover:bg-slate-50'">

                        <!-- Mode lecture -->
                        <template v-if="editId !== m.id">
                            <span class="flex-1 text-sm font-medium" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ m.nom }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="typeColors[m.type].badge">{{ typeLabels[m.type] }}</span>
                            <button @click="startEdit(m)" class="p-1.5 rounded-lg border transition-all"
                                :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-400 hover:text-slate-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="destroy(m.id)" class="p-1.5 rounded-lg border border-rose-200 text-rose-400 hover:text-rose-600 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </template>

                        <!-- Mode édition inline -->
                        <template v-else>
                            <input v-model="editForm.nom" type="text" class="flex-1 rounded-xl border px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#760078]/20" :class="inp(dark)" />
                            <select v-model="editForm.type" class="w-36 rounded-xl border px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#760078]/20" :class="inp(dark)">
                                <option value="conge">Congé</option>
                                <option value="presence">Présence</option>
                                <option value="retard">Retard</option>
                            </select>
                            <button @click="saveEdit(m)" :disabled="editForm.processing"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] disabled:opacity-50">
                                Sauver
                            </button>
                            <button @click="cancelEdit" class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                                :class="dark ? 'border-[#30363d] text-slate-400' : 'border-slate-200 text-slate-500'">
                                Annuler
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div v-if="!motifs.length" class="text-center py-12 text-sm" :class="sL(dark)">
                Aucun motif enregistré. Ajoutez-en un ci-dessus.
            </div>

        </div>
    </AuthenticatedLayout>
</template>
