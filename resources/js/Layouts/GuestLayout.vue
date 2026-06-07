<script setup>
// Deux colonnes EdenCorporate : hero violet à gauche, formulaire blanc à droite.
// Slot #hero  → contenu central de la colonne gauche (titre, description, extras).
// Slot default → contenu de la colonne droite (formulaire).
</script>

<template>
    <div class="min-h-screen flex flex-col lg:flex-row font-sans">

        <!-- ── Colonne gauche : Hero ────────────────────────────────────────── -->
        <div class="hidden lg:relative lg:flex lg:flex-1 lg:flex-col justify-between px-10 py-8 lg:px-14 lg:py-10 overflow-hidden
                    bg-gradient-to-br from-[#760078] via-[#4a0068] to-[#7677B7]">

            <!-- Formes décoratives (statiques) -->
            <div class="absolute inset-0 pointer-events-none select-none">
                <div
                    class="absolute -bottom-16 -right-16 w-72 h-72 rounded-3xl bg-white/5 border border-white/10 rotate-12">
                </div>
                <div
                    class="absolute top-1/3 -right-10 w-48 h-48 rounded-2xl bg-white/5 border border-white/10 -rotate-6">
                </div>
                <div class="absolute top-20 -left-8 w-32 h-32 rounded-2xl bg-white/5 border border-white/10 rotate-45">
                </div>
                <div class="absolute top-1/2 left-1/3 w-56 h-56 rounded-full bg-[#760078]/30 blur-3xl -translate-y-1/2">
                </div>
                <div class="absolute bottom-10 right-1/4 w-40 h-40 rounded-full bg-[#7677B7]/40 blur-2xl"></div>
                <div class="absolute top-40 right-24 w-10 h-10 bg-white/10 rounded-md rotate-45"></div>
                <div class="absolute bottom-32 left-16 w-7 h-7 bg-white/10 rounded-md rotate-12"></div>
                <div class="absolute top-60 left-1/2 w-5 h-5 bg-white/15 rounded-sm -rotate-12"></div>
            </div>

            <!-- Logo (statique) -->
            <div class="relative z-10">
                <img src="/images/logo.svg" alt="Eden Corpor@te"
                     class="h-10 w-auto object-contain"
                     style="filter: brightness(0) invert(1);" />
            </div>

            <!-- Contenu héro — personnalisable via slot #hero -->
            <div class="relative z-10 flex-1 flex flex-col justify-center py-6">
                <slot name="hero">
                    <!-- Contenu par défaut (utilisé si aucun slot #hero n'est fourni) -->
                    <div class="inline-flex items-center self-start gap-2 px-3.5 py-1.5 mb-6
                                rounded-full bg-white/10 border border-white/20 backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-[#CDA071] animate-pulse"></span>
                        <span class="text-xs font-bold tracking-widest text-white/90 uppercase">
                            10 Ans d'Excellence · 2016 – 2026
                        </span>
                    </div>
                    <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-4 tracking-tight">
                        Bienvenue sur votre<br />
                        <span class="text-white/70 font-light">portail RH.</span>
                    </h1>
                    <p class="text-base text-white/75 leading-relaxed max-w-md">
                        Eden Corpor@te centralise la gestion de vos collaborateurs,
                        de vos paies et de vos activités en un seul espace sécurisé.
                    </p>
                </slot>
            </div>

            <!-- Footer gauche (statique) -->
            <div class="relative z-10 text-white/40 text-xs">
                © {{ new Date().getFullYear() }} Eden Corpor@te · Tous droits réservés
            </div>
        </div>

        <!-- ── Colonne droite : Formulaire ─────────────────────────────────── -->
        <div class="w-full lg:w-1/2 xl:w-1/2 flex flex-col items-center justify-center
                    min-h-screen bg-[#f8f9fa] px-6 sm:px-8 lg:px-14 overflow-y-auto">
            <div class="w-full max-w-sm py-8">
                <slot />
            </div>
        </div>

    </div>
</template>
