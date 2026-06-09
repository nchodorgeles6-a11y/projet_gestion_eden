<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useTheme } from '@/composables/useTheme';

const { dark, toggle } = useTheme();
const page = usePage();

// ── Sidebar mobile ────────────────────────────────────────────────────────────
const sidebarOpen = ref(false);
const closeSidebar = () => { sidebarOpen.value = false; };
watch(() => page.url, closeSidebar);

// ── Navigation progress bar ──────────────────────────────────────────────────
const navLoading  = ref(false);
const navProgress = ref(0);
let navTimer      = null;

const startProgress = () => {
    navLoading.value  = true;
    navProgress.value = 15;
    navTimer = setTimeout(() => { navProgress.value = 65; }, 120);
    setTimeout(() => { navProgress.value = 85; }, 350);
};
const finishProgress = () => {
    clearTimeout(navTimer);
    navProgress.value = 100;
    setTimeout(() => { navLoading.value = false; navProgress.value = 0; }, 380);
};

// ── Flash toast notifications ────────────────────────────────────────────────
const toast     = ref(null); // { type, message }
let toastTimer  = null;

const showToast = (type, message) => {
    clearTimeout(toastTimer);
    toast.value  = { type, message };
    toastTimer   = setTimeout(() => { toast.value = null; }, 4500);
};
const dismissToast = () => { clearTimeout(toastTimer); toast.value = null; };

watch(() => page.props.flash?.success, (v) => { if (v) showToast('success', v); });
watch(() => page.props.flash?.error,   (v) => { if (v) showToast('error',   v); });

// ── Lifecycle ─────────────────────────────────────────────────────────────────
let unlistenStart, unlistenFinish;

onMounted(() => {
    unlistenStart  = router.on('start',  startProgress);
    unlistenFinish = router.on('finish', finishProgress);
});
onBeforeUnmount(() => {
    unlistenStart?.();
    unlistenFinish?.();
    clearTimeout(navTimer);
    clearTimeout(toastTimer);
});

// ── Rôles ─────────────────────────────────────────────────────────────────────
const userRoles   = computed(() => page.props.auth?.roles ?? []);
const hasRole     = (...roles) => roles.some(r => userRoles.value.includes(r));
const isAdmin     = computed(() => hasRole('admin'));
const isAdminOrRh = computed(() => hasRole('admin', 'rh'));

// ── Accordion ─────────────────────────────────────────────────────────────────
const openGroups = ref(new Set(initOpen()));

function initOpen() {
    const s = new Set();
    if (route().current('users.*') || route().current('affectations.*')) s.add('personnel');
    if (route().current('suivies.*'))                                     s.add('presences');
    if (route().current('departements.*') || route().current('postes.*')) s.add('structure');
    if (route().current('bulletins-paie.*') || route().current('paiements.*')) s.add('paie');
    if (route().current('rapports.*'))                                    s.add('rapports');
    if (route().current('budgets.*') || route().current('factures.*'))   s.add('compta');
    return s;
}

function toggleGroup(name) {
    const next = new Set(openGroups.value);
    next.has(name) ? next.delete(name) : next.add(name);
    openGroups.value = next;
}
const isOpen = name => openGroups.value.has(name);

// ── Page title ────────────────────────────────────────────────────────────────
const pageTitle = computed(() => {
    const map = {
        'dashboard':              'Dashboard',
        'users.index':            'Employés',
        'users.create':           'Nouvel Employé',
        'users.edit':             'Modifier Employé',
        'departements.index':     'Départements',
        'departements.create':    'Nouveau Département',
        'departements.edit':      'Modifier Département',
        'postes.index':           'Postes',
        'postes.create':          'Nouveau Poste',
        'roles.index':            'Rôles',
        'roles.create':           'Nouveau Rôle',
        'affectations.index':     'Affectations',
        'affectations.create':    'Nouvelle Affectation',
        'conges.index':           'Congés',
        'conges.create':          'Nouveau Congé',
        'suivies.index':          'Suivis de présence',
        'bulletins-paie.index':   'Bulletins de Paie',
        'bulletins-paie.create':  'Nouveau Bulletin',
        'paiements.index':        'Historique Paiements',
        'transactions.index':     'Transactions',
        'transactions.create':    'Nouvelle Transaction',
        'rapports.financiers':    'Rapport Financier',
        'rapports.rh':            'Rapport RH',
        'profile.edit':           'Mon Profil',
    };
    for (const [name, label] of Object.entries(map)) {
        if (route().current(name)) return label;
    }
    return null;
});

// ── User display ──────────────────────────────────────────────────────────────
const userDisplay = computed(() => {
    const u = page.props.auth?.user;
    if (!u) return { name: '—', initial: '?' };
    const full = [u.prenom, u.nom].filter(Boolean).join(' ') || u.email || '?';
    return { name: full, initial: full.charAt(0).toUpperCase() };
});

const roleLabel = computed(() => {
    if (isAdmin.value)                           return 'Administrateur';
    if (userRoles.value.includes('rh'))          return 'Ressources Humaines';
    if (userRoles.value.includes('prestataire')) return 'Prestataire';
    return 'Employé';
});

// ── Nav link classes ──────────────────────────────────────────────────────────
const lk    = 'flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-[12.5px] font-medium transition-all duration-150';
const lkOn  = 'bg-[#760078]/20 text-white border-l-2 border-[#e91e8c] shadow-[0_0_12px_rgba(118,0,120,0.2)]';
const lkOff = 'text-slate-400 hover:text-white hover:bg-white/[0.06] border-l-2 border-transparent';
const sk    = 'flex items-center gap-2 px-2.5 py-1.5 rounded-md text-[11.5px] font-medium transition-all duration-150 w-full';
const skOn  = 'text-white bg-white/10';
const skOff = 'text-slate-500 hover:text-slate-200 hover:bg-white/5';
</script>

<template>
    <!-- ── Navigation progress bar ──────────────────────────────────────────── -->
    <div v-if="navLoading"
        class="nav-progress-bar"
        :style="{ width: navProgress + '%', opacity: navLoading ? 1 : 0 }">
    </div>

    <!-- ── Toast notifications ───────────────────────────────────────────────── -->
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[9998] space-y-2 pointer-events-none">
            <Transition name="toast">
                <div v-if="toast" class="pointer-events-auto min-w-[280px] max-w-sm">
                    <div class="flex items-start gap-3 px-4 py-3 rounded-2xl shadow-xl border backdrop-blur-sm"
                        :class="toast.type === 'success'
                            ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                            : 'bg-rose-50 border-rose-200 text-rose-800'">
                        <div class="shrink-0 mt-0.5">
                            <svg v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg v-else class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="flex-1 text-sm font-semibold leading-snug">{{ toast.message }}</p>
                        <button @click="dismissToast" class="shrink-0 p-0.5 rounded-md hover:bg-black/5 transition-colors">
                            <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>

    <div class="flex min-h-screen transition-colors duration-300"
        :class="dark ? 'bg-[#0d1117]' : 'bg-slate-100'">

        <!-- Overlay mobile -->
        <Transition name="fade">
            <div v-if="sidebarOpen"
                class="fixed inset-0 bg-black/60 z-20 lg:hidden backdrop-blur-sm"
                @click="closeSidebar">
            </div>
        </Transition>

        <!-- ══════════════════ SIDEBAR ══════════════════ -->
        <aside class="fixed top-0 left-0 h-screen w-64 flex flex-col border-r z-30 transition-all duration-300"
            :class="[
                dark ? 'bg-[#0d1117] border-[#21262d]' : 'bg-slate-950 border-slate-900',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]">

            <!-- Logo + fermeture mobile -->
            <div class="px-4 py-3 border-b shrink-0 flex items-center justify-between " 
                :class="dark ? 'border-[#21262d]' : 'border-slate-900/80'">
                <Link href="/" class="flex items-center py-1">
                    <img src="/images/EdenCorporate2.png" alt="Eden Corpor@te" class=" h-[70px] w-auto object-contain mx-auto"
                          
                          />
                </Link>
                <button @click="closeSidebar"
                    class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">

                <!-- ── GÉNÉRAL ──────────────────────────── -->
                <div class="space-y-0.5">
                    <p class="px-3 text-[9px] font-bold text-slate-600 tracking-widest uppercase mb-2">Général</p>

                    <Link :href="route('dashboard')"
                        :class="[lk, route().current('dashboard') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                        </svg>
                        <span class="flex-1">Dashboard</span>
                        <span v-if="route().current('dashboard')" class="nav-active-dot w-1.5 h-1.5 rounded-full bg-[#e91e8c]"></span>
                    </Link>

                    <Link :href="route('conges.index')"
                        :class="[lk, route().current('conges.*') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="flex-1">Congés</span>
                        <span v-if="route().current('conges.*')" class="nav-active-dot w-1.5 h-1.5 rounded-full bg-[#e91e8c]"></span>
                    </Link>
                </div>

                <!-- ── RESSOURCES HUMAINES ─────────────── -->
                <div v-if="isAdminOrRh" class="space-y-0.5">
                    <p class="px-3 text-[9px] font-bold text-slate-600 tracking-widest uppercase mb-2">Ressources Humaines</p>

                    <!-- Personnel -->
                    <button @click="toggleGroup('personnel')"
                        :class="[lk, 'w-full', route().current('users.*') || route().current('affectations.*') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="flex-1 text-left">Personnel</span>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                            :class="isOpen('personnel') ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <Transition name="nav-acc">
                        <div v-show="isOpen('personnel')" class="mt-0.5 ml-3 pl-3 border-l border-slate-700/50 space-y-0.5">
                            <Link v-if="isAdmin" :href="route('users.index')"
                                :class="[sk, route().current('users.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('users.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Employés
                            </Link>
                            <Link :href="route('affectations.index')"
                                :class="[sk, route().current('affectations.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('affectations.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Affectations
                            </Link>
                        </div>
                    </Transition>

                    <!-- Présences -->
                    <button @click="toggleGroup('presences')"
                        :class="[lk, 'w-full', route().current('suivies.*') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span class="flex-1 text-left">Présences</span>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                            :class="isOpen('presences') ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <Transition name="nav-acc">
                        <div v-show="isOpen('presences')" class="mt-0.5 ml-3 pl-3 border-l border-slate-700/50 space-y-0.5">
                            <Link :href="route('suivies.index')"
                                :class="[sk, route().current('suivies.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('suivies.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Suivis de présence
                            </Link>
                            <Link :href="route('conges.index')"
                                :class="[sk, route().current('conges.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('conges.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Demandes de congé
                            </Link>
                        </div>
                    </Transition>

                    <!-- Structure RH -->
                    <button @click="toggleGroup('structure')"
                        :class="[lk, 'w-full', route().current('departements.*') || route().current('postes.*') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                        <span class="flex-1 text-left">Structure RH</span>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                            :class="isOpen('structure') ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <Transition name="nav-acc">
                        <div v-show="isOpen('structure')" class="mt-0.5 ml-3 pl-3 border-l border-slate-700/50 space-y-0.5">
                            <Link :href="route('departements.index')"
                                :class="[sk, route().current('departements.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('departements.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Départements
                            </Link>
                            <Link :href="route('postes.index')"
                                :class="[sk, route().current('postes.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('postes.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Postes
                            </Link>
                        </div>
                    </Transition>
                </div>

                <!-- ── PAIE ─────────────────────────────── -->
                <div v-if="isAdminOrRh" class="space-y-0.5">
                    <p class="px-3 text-[9px] font-bold text-slate-600 tracking-widest uppercase mb-2">Paie</p>

                    <button @click="toggleGroup('paie')"
                        :class="[lk, 'w-full', route().current('bulletins-paie.*') || route().current('paiements.*') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                        </svg>
                        <span class="flex-1 text-left">Gestion Paie</span>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                            :class="isOpen('paie') ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <Transition name="nav-acc">
                        <div v-show="isOpen('paie')" class="mt-0.5 ml-3 pl-3 border-l border-slate-700/50 space-y-0.5">
                            <Link :href="route('bulletins-paie.index')"
                                :class="[sk, route().current('bulletins-paie.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('bulletins-paie.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Bulletins de paie
                            </Link>
                            <Link :href="route('paiements.index')"
                                :class="[sk, route().current('paiements.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('paiements.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Historique paiements
                            </Link>
                        </div>
                    </Transition>
                </div>

                <!-- ── COMPTABILITÉ ─────────────────────── -->
                <div v-if="isAdminOrRh" class="space-y-0.5">
                    <p class="px-3 text-[9px] font-bold text-slate-600 tracking-widest uppercase mb-2">Comptabilité</p>

                    <button @click="toggleGroup('compta')"
                        :class="[lk, 'w-full', (route().current('transactions.*') || route().current('budgets.*') || route().current('factures.*')) ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="flex-1 text-left">Comptabilité</span>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                            :class="isOpen('compta') ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <Transition name="nav-acc">
                        <div v-show="isOpen('compta')" class="mt-0.5 ml-3 pl-3 border-l border-slate-700/50 space-y-0.5">
                            <Link :href="route('transactions.index')"
                                :class="[sk, route().current('transactions.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('transactions.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Transactions
                            </Link>
                            <Link href="/factures"
                                :class="[sk, route().current('factures.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('factures.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Factures fournisseurs
                            </Link>
                            <Link href="/budgets"
                                :class="[sk, route().current('budgets.*') ? skOn : skOff]">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                    :class="route().current('budgets.*') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                Budgets prévisionnels
                            </Link>
                        </div>
                    </Transition>

                    <template v-if="isAdmin">
                        <button @click="toggleGroup('rapports')"
                            :class="[lk, 'w-full', route().current('rapports.*') ? lkOn : lkOff]">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                            </svg>
                            <span class="flex-1 text-left">Rapports</span>
                            <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                                :class="isOpen('rapports') ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <Transition name="nav-acc">
                            <div v-show="isOpen('rapports')" class="mt-0.5 ml-3 pl-3 border-l border-slate-700/50 space-y-0.5">
                                <Link :href="route('rapports.financiers')"
                                    :class="[sk, route().current('rapports.financiers') ? skOn : skOff]">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="route().current('rapports.financiers') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                    Rapport Financier
                                </Link>
                                <Link :href="route('rapports.rh')"
                                    :class="[sk, route().current('rapports.rh') ? skOn : skOff]">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="route().current('rapports.rh') ? 'bg-[#e91e8c]' : 'bg-current'"></span>
                                    Rapport RH
                                </Link>
                            </div>
                        </Transition>
                    </template>
                </div>

                <!-- ── ADMINISTRATION ───────────────────── -->
                <div v-if="isAdmin" class="space-y-0.5">
                    <p class="px-3 text-[9px] font-bold text-slate-600 tracking-widest uppercase mb-2">Administration</p>

                    <Link :href="route('roles.index')"
                        :class="[lk, route().current('roles.*') ? lkOn : lkOff]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="flex-1">Rôles &amp; Accès</span>
                        <span v-if="route().current('roles.*')" class="nav-active-dot w-1.5 h-1.5 rounded-full bg-[#e91e8c]"></span>
                    </Link>
                </div>

            </nav>

            <!-- Pied de sidebar -->
            <div class="px-3 py-3 border-t shrink-0"
                :class="dark ? 'border-[#21262d]' : 'border-slate-900'">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#760078] to-[#7677B7]
                                flex items-center justify-center font-bold text-[11px] text-white select-none shrink-0
                                ring-2 ring-[#760078]/30">
                        {{ userDisplay.initial }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11.5px] font-semibold text-white truncate leading-tight">{{ userDisplay.name }}</p>
                        <p class="text-[9.5px] text-[#7677B7] font-medium truncate leading-tight">{{ roleLabel }}</p>
                    </div>
                    <Link :href="route('profile.edit')"
                        class="text-slate-500 hover:text-slate-300 transition-colors p-1.5 rounded-lg hover:bg-white/5" title="Profil">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button"
                        class="text-slate-500 hover:text-rose-400 transition-colors p-1.5 rounded-lg hover:bg-rose-500/10" title="Déconnexion">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ══════════════════ ZONE PRINCIPALE ══════════════════ -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

            <!-- TOPBAR -->
            <header class="px-4 sm:px-5 py-2 flex justify-between items-center sticky top-0 z-20 border-b transition-colors duration-300"
                :class="dark
                    ? 'bg-[#161b22] border-[#21262d]'
                    : 'bg-white border-slate-200 shadow-sm'">

                <div class="flex items-center gap-2 text-xs">
                    <!-- Hamburger mobile -->
                    <button @click="sidebarOpen = true"
                        class="lg:hidden p-1.5 rounded-xl mr-1 transition-all"
                        :class="dark ? 'text-slate-400 hover:text-white hover:bg-white/10' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span class="font-semibold" :class="dark ? 'text-slate-400' : 'text-slate-500'">EdenCorp SIRH</span>
                    <template v-if="pageTitle">
                        <svg class="w-3 h-3 shrink-0" :class="dark ? 'text-slate-600' : 'text-slate-400'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ pageTitle }}</span>
                    </template>

                    <!-- Navigation loading indicator -->
                    <span v-if="navLoading" class="ml-2 flex items-center gap-1.5">
                        <span class="w-1 h-1 rounded-full bg-[#760078] animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1 h-1 rounded-full bg-[#7677B7] animate-bounce" style="animation-delay:100ms"></span>
                        <span class="w-1 h-1 rounded-full bg-[#e91e8c] animate-bounce" style="animation-delay:200ms"></span>
                    </span>
                    <template v-else>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 ml-1"></span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :class="dark ? 'bg-emerald-900/40 text-emerald-400' : 'bg-emerald-50 text-emerald-600'">
                            En ligne
                        </span>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Bascule thème -->
                    <button @click="toggle"
                        class="w-8 h-8 rounded-xl flex items-center justify-center transition-all hover:scale-110"
                        :class="dark ? 'bg-[#21262d] text-yellow-400 hover:bg-[#30363d]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        :title="dark ? 'Mode clair' : 'Mode sombre'">
                        <svg v-if="dark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 110 8 4 4 0 010-8z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Utilisateur connecté -->
                    <div class="flex items-center gap-2 pl-3 border-l"
                        :class="dark ? 'border-[#30363d]' : 'border-slate-200'">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#760078] to-[#7677B7]
                                    flex items-center justify-center font-bold text-[10px] text-white select-none
                                    ring-2 ring-[#760078]/20">
                            {{ userDisplay.initial }}
                        </div>
                        <div class="hidden sm:flex flex-col">
                            <span class="text-[11.5px] font-bold leading-tight"
                                :class="dark ? 'text-white' : 'text-slate-800'">
                                {{ userDisplay.name }}
                            </span>
                            <span class="text-[9.5px] text-[#7677B7] font-medium leading-tight">{{ roleLabel }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenu principal avec transition de page -->
            <main class="flex-1 overflow-y-auto">
                <Transition name="page" mode="out-in">
                    <div :key="$page.component" class="min-h-full">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>
    </div>
</template>
