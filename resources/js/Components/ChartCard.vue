<script setup>
import { useTheme } from '@/composables/useTheme';
const { dark } = useTheme();
defineProps({
    title:    { type: String, required: true },
    subtitle: { type: String, default: '' },
    height:   { type: String, default: '120px' },
});
const sL = (d) => d ? 'text-slate-400' : 'text-slate-500';
</script>

<template>
    <div class="rounded-2xl border p-5 transition-colors h-full"
        :class="dark ? 'bg-[#161b22] border-[#21262d]' : 'bg-white border-slate-200 shadow-sm'">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold" :class="dark ? 'text-white' : 'text-slate-800'">{{ title }}</h3>
                <p v-if="subtitle" class="text-[11px] mt-0.5" :class="sL(dark)">{{ subtitle }}</p>
            </div>
            <slot name="badge" />
        </div>
        <div :style="{ height, position: 'relative' }">
            <slot />
        </div>
        <slot name="footer" />
    </div>
</template>
