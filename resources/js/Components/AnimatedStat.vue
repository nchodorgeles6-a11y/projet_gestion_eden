<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    value:    { type: Number,  default: 0 },
    prefix:   { type: String,  default: '' },
    suffix:   { type: String,  default: '' },
    duration: { type: Number,  default: 1400 },
    decimals: { type: Number,  default: 0 },
    locale:   { type: String,  default: 'fr-CI' },
});

const el        = ref(null);
const displayed = ref(0);
let started = false;
let rafId   = null;

// easeOutQuart — starts fast, decelerates smoothly
const ease = (t) => 1 - Math.pow(1 - t, 4);

const fmt = (n) =>
    new Intl.NumberFormat(props.locale, {
        minimumFractionDigits: props.decimals,
        maximumFractionDigits: props.decimals,
    }).format(Math.round(n));

function start() {
    if (started || props.value === 0) {
        displayed.value = props.value;
        return;
    }
    started = true;
    const t0     = performance.now();
    const target = props.value;

    function tick(now) {
        const pct = Math.min((now - t0) / props.duration, 1);
        displayed.value = ease(pct) * target;
        if (pct < 1) {
            rafId = requestAnimationFrame(tick);
        } else {
            displayed.value = target;
        }
    }
    rafId = requestAnimationFrame(tick);
}

onMounted(() => {
    if (!el.value) return;

    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                start();
                observer.disconnect();
            }
        },
        { threshold: 0.15, rootMargin: '0px 0px -20px 0px' }
    );

    observer.observe(el.value);
});

onBeforeUnmount(() => {
    if (rafId) cancelAnimationFrame(rafId);
});
</script>

<template>
    <span ref="el">{{ prefix }}{{ fmt(displayed) }}{{ suffix }}</span>
</template>
