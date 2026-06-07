<script setup>
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const { dark } = useTheme();

defineProps({
    links: { type: Array, default: () => [] },
    meta:  { type: Object, default: () => ({}) },
});
</script>

<template>
    <div v-if="meta.last_page > 1" class="flex items-center justify-between px-1 mt-4">
        <p class="text-[11px]" :class="dark ? 'text-slate-500' : 'text-slate-400'">
            {{ meta.from }}–{{ meta.to }} sur {{ meta.total }} résultat(s)
        </p>
        <div class="flex items-center gap-1">
            <template v-for="link in links" :key="link.label">
                <span v-if="!link.url"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold opacity-30 cursor-default select-none"
                    :class="dark ? 'text-slate-400' : 'text-slate-500'"
                    v-html="link.label" />
                <Link v-else
                    :href="link.url"
                    preserve-scroll
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all"
                    :class="link.active
                        ? 'bg-gradient-to-r from-[#760078] to-[#7677B7] text-white shadow-sm'
                        : dark
                            ? 'text-slate-400 hover:text-white hover:bg-white/10'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    v-html="link.label" />
            </template>
        </div>
    </div>
</template>
