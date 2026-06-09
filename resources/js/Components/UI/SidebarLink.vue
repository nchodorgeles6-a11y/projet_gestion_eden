<script setup>
import { Link, usePage } from '@inertiajs/vue3'

defineProps({
    href: String,
    icon: String,
})

const page = usePage()
</script>

<template>
    <Link
        :href="href"
        :class="[
            'flex items-center gap-3 px-4 py-3 rounded-lg text-decoration-none transition',
            page.url.startsWith(href)
                ? 'bg-blue-600 text-white'
                : 'text-white hover:bg-slate-700'
        ]"
    >
        <span>{{ icon }}</span>
        <slot />
    </Link>
</template>