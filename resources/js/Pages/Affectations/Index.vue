<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import Pagination from '@/Components/Pagination.vue';

const { dark } = useTheme();

defineProps({ affectations: Object });

const destroy = (id) => {
    if (confirm('Voulez-vous supprimer cette affectation ?')) router.delete(`/affectations/${id}`);
};
</script>

<template>
    <Head title="Affectations" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6">

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Affectations</h2>
                    <p class="text-[11px] mt-0.5" :class="dark ? 'text-slate-400' : 'text-slate-500'">
                        {{ affectations?.meta?.total ?? 0 }} enregistrement(s)
                    </p>
                </div>
                <Link href="/affectations/create"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c]
                           transition-all shadow-sm shadow-purple-900/20 text-decoration-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter
                </Link>
            </div>
            

          

            <div class="rounded-2xl border overflow-hidden transition-colors"
                :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="dark ? 'text-slate-500' : 'text-slate-400'">Employé</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="dark ? 'text-slate-500' : 'text-slate-400'">Poste</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="dark ? 'text-slate-500' : 'text-slate-400'">Département</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="dark ? 'text-slate-500' : 'text-slate-400'">Date début</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold tracking-wider uppercase" :class="dark ? 'text-slate-500' : 'text-slate-400'">Date fin</th>
                                <th class="px-4 py-2.5 text-right text-[10px] font-bold tracking-wider uppercase" :class="dark ? 'text-slate-500' : 'text-slate-400'">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(affectation, i) in affectations.data" :key="affectation.id"
                                class="row-anim border-t transition-colors"
                                :style="{ animationDelay: i * 35 + 'ms' }"
                                :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-100 hover:bg-slate-50'">
                                <td class="px-4 py-2.5 text-[12px] font-medium" :class="dark ? 'text-slate-200' : 'text-slate-700'">
                                    {{ affectation.user?.nom }} {{ affectation.user?.prenom }}
                                </td>
                                <td class="px-4 py-2.5 text-[12px]" :class="dark ? 'text-slate-300' : 'text-slate-600'">{{ affectation.poste?.nom }}</td>
                                <td class="px-4 py-2.5 text-[12px]" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ affectation.poste?.departement?.nom }}</td>
                                <td class="px-4 py-2.5 text-[12px]" :class="dark ? 'text-slate-400' : 'text-slate-500'">
                                    {{ new Date(affectation.date_debut).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="px-4 py-2.5 text-[12px]" :class="dark ? 'text-slate-400' : 'text-slate-500'">
                                    {{ new Date(affectation.date_fin).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <Link :href="`/affectations/${affectation.id}/edit`"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-amber-500/10 text-amber-500 border-amber-500/20 hover:bg-amber-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            Modifier
                                        </Link>
                                        <button @click="destroy(affectation.id)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all bg-rose-500/10 text-rose-500 border-rose-500/20 hover:bg-rose-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!affectations?.data?.length">
                                <td colspan="6" class="px-4 py-8 text-center text-sm" :class="dark ? 'text-slate-500' : 'text-slate-400'">Aucun enregistrement.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="affectations.links" :meta="affectations" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
