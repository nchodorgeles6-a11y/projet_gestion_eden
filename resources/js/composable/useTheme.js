import { ref } from 'vue';

// Module-level ref — shared across all components that import this composable
const dark = ref(
    typeof window !== 'undefined' && localStorage.getItem('eden-theme') === 'dark'
);

export function useTheme() {
    const toggle = () => {
        dark.value = !dark.value;
        if (typeof window !== 'undefined') {
            localStorage.setItem('eden-theme', dark.value ? 'dark' : 'light');
        }
    };
    return { dark, toggle };
}
