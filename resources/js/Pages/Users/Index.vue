<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { ref, computed, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import AnimatedStat from '@/Components/AnimatedStat.vue';

const { dark } = useTheme();
const page  = usePage();

const props = defineProps({
    users:  { type: Object, default: () => ({}) },
    roles:  { type: Array,  default: () => [] },
    totaux: { type: Object, default: () => ({}) },
    search: { type: String, default: '' },
});

// ── Filtres ───────────────────────────────────────────────────────────────────
const search   = ref(props.search);
const filAcces = ref('');

let searchTimer = null;
watch(search, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get('/users', { search: val }, { preserveState: true, replace: true });
    }, 400);
});

const filtered = computed(() =>
    (props.users.data ?? []).filter(u =>
        !filAcces.value || (filAcces.value === 'actif' ? u.acces_systeme : !u.acces_systeme)
    )
);

const employes  = computed(() => props.totaux.employes    ?? 0);
const presta    = computed(() => props.totaux.prestataires ?? 0);
const avecAcces = computed(() => props.totaux.avec_acces  ?? 0);

// ── Modals ────────────────────────────────────────────────────────────────────
const modalAutoriser = ref(false);
const modalCredentials = ref(false);
const userCible = ref(null);
const credentials = ref({ user: '', email: '', password: '' });

const formAcces = useForm({ role_id: '' });

const ouvrirAutoriser = (user) => {
    userCible.value = user;
    formAcces.role_id = '';
    modalAutoriser.value = true;
};

const confirmerAcces = () => {
    formAcces.post(`/users/${userCible.value.id}/autoriser-acces`, {
        onSuccess: () => { modalAutoriser.value = false; },
    });
};

const retirerAcces = (user) => {
    if (!confirm(`Retirer l'accès système de ${user.prenom} ${user.nom} ?`)) return;
    router.post(`/users/${user.id}/retirer-acces`);
};

// Afficher le mot de passe temporaire quand le flash arrive
watch(() => page.props.flash?.temp_password, (val) => {
    if (!val) return;
    credentials.value = {
        user:     page.props.flash.temp_user  ?? '',
        email:    page.props.flash.temp_email ?? '',
        password: val,
    };
    modalCredentials.value = true;
});

const copier = (text) => navigator.clipboard.writeText(text);

// ── Suppression ───────────────────────────────────────────────────────────────
const destroy = (id) => {
    if (confirm('Supprimer cet employé définitivement ?')) router.delete(`/users/${id}`);
};

// ── Helpers UI ────────────────────────────────────────────────────────────────
const fmt = (n) => n ? new Intl.NumberFormat('fr-CI').format(n) : '—';
const sL  = (d) => d ? 'text-slate-400' : 'text-slate-500';
const inp = (d) => d
    ? 'bg-[#0d1117] border-[#30363d] text-white placeholder-slate-600 focus:border-[#760078] focus:ring-[#760078]/20'
    : 'bg-slate-50 border-slate-200 text-slate-900 placeholder-slate-400 focus:border-[#760078] focus:ring-[#760078]/15';

const roleLabel = (nom) => ({
    admin: 'Administrateur', rh: 'Ressources Humaines',
    employe: 'Employé', prestataire: 'Prestataire',
})[nom] ?? nom;

const roleColor = (nom) => ({
    admin:       'bg-[#760078]/10 text-[#760078]',
    rh:          'bg-violet-100 text-violet-700',
    employe:     'bg-emerald-100 text-emerald-700',
    prestataire: 'bg-blue-100 text-blue-700',
})[nom] ?? 'bg-slate-100 text-slate-500';
</script>

<template>
    <Head title="Collaborateurs" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">

            <!-- ── En-tête ── -->
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-base font-extrabold" :class="dark ? 'text-white' : 'text-slate-800'">Collaborateurs</h2>
                    <p class="text-[11px] mt-0.5" :class="sL(dark)">{{ users.meta?.total ?? 0 }} collaborateur(s) · {{ avecAcces }} avec accès système</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- KPIs mini -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs transition-all duration-200 hover:scale-[1.03]"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200'">
                        <div class="w-2 h-2 rounded-full bg-[#760078]"></div>
                        <span :class="dark ? 'text-slate-300' : 'text-slate-700'">
                            <span class="font-bold"><AnimatedStat :value="employes" :duration="900" /></span> employé(s)
                        </span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs transition-all duration-200 hover:scale-[1.03]"
                        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200'">
                        <div class="w-2 h-2 rounded-full bg-[#7677B7]"></div>
                        <span :class="dark ? 'text-slate-300' : 'text-slate-700'">
                            <span class="font-bold"><AnimatedStat :value="presta" :duration="1100" /></span> prestataire(s)
                        </span>
                    </div>

                    <!-- Recherche -->
                    <div class="relative">
                        <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" :class="sL(dark)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="search" type="text" placeholder="Nom, poste, département…"
                            class="pl-8 pr-3 py-1.5 rounded-xl border text-xs transition-all focus:outline-none focus:ring-2 w-52" :class="inp(dark)" />
                    </div>

                    <!-- Filtre accès -->
                    <select v-model="filAcces" class="rounded-xl border px-2 py-1.5 text-xs transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                        <option value="">Tous</option>
                        <option value="actif">Accès actif</option>
                        <option value="inactif">Sans accès</option>
                    </select>

                    <a href="/users/export"
                        class="text-decoration-none inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border transition-all"
                        :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-500 hover:text-slate-800'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </a>
                    <Link href="/users/create"
                        class="text-decoration-none inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter
                    </Link>
                </div>
            </div>

            <!-- ── Table ── -->
            <div class="rounded-2xl border overflow-hidden" :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Collaborateur</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Poste / Département</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Contrat</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Salaire</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Accès système</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold tracking-wider uppercase" :class="sL(dark)">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="6" class="py-12 text-center text-xs" :class="sL(dark)">
                                {{ search ? `Aucun résultat pour "${search}"` : 'Aucun collaborateur enregistré' }}
                            </td>
                        </tr>

                        <tr v-for="(user, i) in filtered" :key="user.id ?? user.email"
                            class="row-anim border-t transition-colors"
                            :style="{ animationDelay: i * 35 + 'ms' }"
                            :class="dark ? 'border-[#21262d] hover:bg-white/[0.02]' : 'border-slate-100 hover:bg-slate-50/80'">

                            <!-- Collaborateur -->
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-[11px] font-black text-white shrink-0 bg-gradient-to-br from-[#760078] to-[#7677B7]">
                                        {{ user.prenom?.charAt(0) }}{{ user.nom?.charAt(0) }}
                                    </div>
                                    <div>
                                        <Link :href="`/users/${user.id}`" class="text-xs font-bold hover:text-[#760078] transition-colors" :class="dark ? 'text-slate-100' : 'text-slate-800'">
                                            {{ user.nom }} {{ user.prenom }}
                                        </Link>
                                        <p class="text-[10px]" :class="sL(dark)">{{ user.email }}</p>
                                        <p class="text-[10px]" :class="sL(dark)">{{ user.telephone }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Poste / Département -->
                            <td class="px-5 py-3">
                                <p class="text-xs font-semibold" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ user.poste ?? '—' }}</p>
                                <p class="text-[10px] mt-0.5" :class="sL(dark)">{{ user.departement ?? 'Non affecté' }}</p>
                            </td>

                            <!-- Contrat -->
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="user.type_contrat === 'employe' ? 'bg-[#760078]/10 text-[#760078]' : 'bg-[#7677B7]/10 text-[#7677B7]'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="user.type_contrat === 'employe' ? 'bg-[#760078]' : 'bg-[#7677B7]'"></span>
                                    {{ user.type_contrat === 'employe' ? 'Employé' : 'Prestataire' }}
                                </span>
                                <p class="text-[10px] mt-1" :class="sL(dark)">{{ user.mode_paiement === 'fixe' ? 'Salaire fixe' : 'Par prestation' }}</p>
                                <!-- Alerte fin de contrat -->
                                <span v-if="user.jours_restants !== null && user.jours_restants <= 30"
                                    class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold"
                                    :class="user.jours_restants < 0
                                        ? 'bg-red-100 text-red-700'
                                        : user.jours_restants <= 7
                                            ? 'bg-orange-100 text-orange-700'
                                            : 'bg-amber-100 text-amber-700'">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    {{ user.jours_restants < 0 ? 'Expiré' : user.jours_restants === 0 ? 'Expire aujourd\'hui' : `${user.jours_restants}j` }}
                                </span>
                            </td>

                            <!-- Salaire -->
                            <td class="px-5 py-3 text-right">
                                <p class="text-xs font-extrabold text-[#760078]">{{ fmt(user.salaire_base) }}</p>
                                <p v-if="user.salaire_base" class="text-[9px] mt-0.5" :class="sL(dark)">FCFA / mois</p>
                            </td>

                            <!-- Accès système -->
                            <td class="px-5 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <!-- Badge statut -->
                                    <span v-if="user.acces_systeme"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Actif
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="dark ? 'bg-slate-700 text-slate-400' : 'bg-slate-100 text-slate-400'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Aucun accès
                                    </span>
                                    <!-- Rôle actuel -->
                                    <span v-for="r in user.roles" :key="r"
                                        class="inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold" :class="roleColor(r)">
                                        {{ roleLabel(r) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <Link :href="`/users/${user.id}`"
                                        class="text-decoration-none inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all"
                                        :class="dark ? 'border-[#760078]/30 text-[#760078] hover:bg-[#760078]/10' : 'border-[#760078]/20 text-[#760078] bg-[#760078]/5 hover:bg-[#760078]/10'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Fiche
                                    </Link>

                                    <!-- Autoriser / Retirer accès -->
                                    <button v-if="!user.acces_systeme" @click="ouvrirAutoriser(user)"
                                        class="text-decoration-none inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all"
                                        :class="dark ? 'border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10' : 'border-emerald-400/30 text-emerald-600 bg-emerald-50 hover:bg-emerald-100'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Autoriser
                                    </button>
                                    <button v-else @click="retirerAcces(user)"
                                        class="text-decoration-none inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all"
                                        :class="dark ? 'border-rose-500/30 text-rose-400 hover:bg-rose-500/10' : 'border-rose-200 text-rose-600 bg-rose-50 hover:bg-rose-100'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Retirer accès
                                    </button>

                                    <Link :href="`/users/${user.id}/edit`"
                                        class="text-decoration-none inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all"
                                        :class="dark ? 'border-amber-500/30 text-amber-400 hover:bg-amber-500/10' : 'border-amber-400/30 text-amber-600 bg-amber-50 hover:bg-amber-100'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Modifier
                                    </Link>
                                    <button @click="destroy(user.id)"
                                        class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] border transition-all"
                                        :class="dark ? 'border-rose-500/30 text-rose-400 hover:bg-rose-500/10' : 'border-rose-200 text-rose-500 hover:bg-rose-50'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <Pagination :links="users.links" :meta="users" />

        </div>

        <!-- ── Modal : Autoriser l'accès ── -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="modalAutoriser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="modalAutoriser = false">
                    <div class="w-full max-w-sm rounded-2xl border shadow-2xl p-6 space-y-5"
                        :class="dark ? 'bg-[#161b22] border-[#30363d]' : 'bg-white border-slate-200'">

                        <!-- Titre -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Autoriser l'accès</h3>
                                <p class="text-[11px]" :class="sL(dark)">{{ userCible?.prenom }} {{ userCible?.nom }}</p>
                            </div>
                        </div>

                        <!-- Email info -->
                        <div class="rounded-xl px-3 py-2.5 text-[11px]"
                            :class="dark ? 'bg-[#0d1117] border border-[#21262d]' : 'bg-slate-50 border border-slate-200'">
                            <p :class="sL(dark)">Identifiant de connexion</p>
                            <p class="font-semibold mt-0.5" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ userCible?.email }}</p>
                        </div>

                        <!-- Rôle -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest mb-2" :class="sL(dark)">
                                Rôle à attribuer <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="formAcces.role_id"
                                class="w-full rounded-xl border px-3 py-2.5 text-sm transition-all focus:outline-none focus:ring-2" :class="inp(dark)">
                                <option value="">Sélectionner un rôle…</option>
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ roleLabel(r.nom) }}</option>
                            </select>
                            <p v-if="formAcces.errors.role_id" class="text-[11px] text-rose-500 mt-1">{{ formAcces.errors.role_id }}</p>
                        </div>

                        <p class="text-[10px] rounded-lg px-3 py-2" :class="dark ? 'bg-amber-900/20 text-amber-400' : 'bg-amber-50 text-amber-700'">
                            Un mot de passe temporaire sera généré. Notez-le et transmettez-le à l'employé.
                        </p>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-1">
                            <button @click="modalAutoriser = false"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-semibold border transition-all"
                                :class="dark ? 'border-[#30363d] text-slate-400 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900'">
                                Annuler
                            </button>
                            <button @click="confirmerAcces"
                                :disabled="!formAcces.role_id || formAcces.processing"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all disabled:opacity-40">
                                {{ formAcces.processing ? 'En cours…' : 'Accorder l\'accès' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Modal : Identifiants temporaires ── -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="modalCredentials" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="w-full max-w-sm rounded-2xl border shadow-2xl p-6 space-y-4"
                        :class="dark ? 'bg-[#161b22] border-[#30363d]' : 'bg-white border-slate-200'">

                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">Accès accordé</h3>
                                <p class="text-[11px]" :class="sL(dark)">Transmettez ces identifiants à l'employé</p>
                            </div>
                        </div>

                        <p class="text-[11px] rounded-lg px-3 py-2" :class="dark ? 'bg-rose-900/20 text-rose-400' : 'bg-rose-50 text-rose-700'">
                            Ce mot de passe ne sera plus affiché. Notez-le maintenant.
                        </p>

                        <!-- Email -->
                        <div class="rounded-xl border overflow-hidden" :class="dark ? 'border-[#30363d]' : 'border-slate-200'">
                            <div class="flex items-center justify-between px-3 py-2.5"
                                :class="dark ? 'bg-[#0d1117]' : 'bg-slate-50'">
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-widest" :class="sL(dark)">Email</p>
                                    <p class="text-xs font-semibold mt-0.5" :class="dark ? 'text-slate-200' : 'text-slate-700'">{{ credentials.email }}</p>
                                </div>
                                <button @click="copier(credentials.email)" class="p-1.5 rounded-lg transition-colors" :class="dark ? 'hover:bg-white/10 text-slate-400' : 'hover:bg-slate-200 text-slate-500'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-between px-3 py-2.5 border-t"
                                :class="dark ? 'border-[#30363d] bg-[#0d1117]' : 'border-slate-200 bg-slate-50'">
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-widest" :class="sL(dark)">Mot de passe temporaire</p>
                                    <p class="text-sm font-black tracking-widest mt-0.5 text-[#760078]">{{ credentials.password }}</p>
                                </div>
                                <button @click="copier(credentials.password)" class="p-1.5 rounded-lg transition-colors" :class="dark ? 'hover:bg-white/10 text-slate-400' : 'hover:bg-slate-200 text-slate-500'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        </div>

                        <button @click="modalCredentials = false"
                            class="w-full px-4 py-2 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-[#760078] to-[#7677B7] hover:from-[#5a005c] hover:to-[#5a5b9c] transition-all">
                            J'ai noté les identifiants
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AuthenticatedLayout>
</template>
