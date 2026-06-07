<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const { dark } = useTheme();
const props = defineProps({ suivie: Object, users: Array, typeSuivies: Array, motifs: Array });
const form = useForm({
    user_id: props.suivie.user_id,
    type_suivie_id: props.suivie.type_suivie_id,
    motif_id: props.suivie.motif_id,
    date: props.suivie.date,
});
const submit = () => form.put(`/suivies/${props.suivie.id}`);

const inputCls = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15';
</script>

<template>
    <Head title="Modifier Suivi" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 max-w-2xl">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Modifier le suivi</h2>
                    <p class="text-[11px] mt-0.5" :class="dark ? 'text-slate-400' : 'text-slate-500'">Modifiez les informations ci-dessous</p>
                </div>
                <Link href="/suivies" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour
                </Link>
            </div>
            <div class="rounded-2xl border p-6 transition-colors"
                :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Employé</label>
                        <select v-model="form.user_id" required class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inputCls(dark)">
                            <option value="">Choisir un employé</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.nom }} {{ u.prenom }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Type de suivi</label>
                            <select v-model="form.type_suivie_id" required class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inputCls(dark)">
                                <option value="">Choisir un type</option>
                                <option v-for="t in typeSuivies" :key="t.id" :value="t.id">{{ t.nom }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Motif</label>
                            <select v-model="form.motif_id" required class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inputCls(dark)">
                                <option value="">Choisir un motif</option>
                                <option v-for="m in motifs" :key="m.id" :value="m.id">{{ m.nom }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" :class="dark ? 'text-slate-300' : 'text-slate-700'">Date</label>
                        <input v-model="form.date" type="date" required class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inputCls(dark)" />
                    </div>
                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] disabled:opacity-60 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ form.processing ? 'Enregistrement...' : 'Enregistrer les modifications' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
