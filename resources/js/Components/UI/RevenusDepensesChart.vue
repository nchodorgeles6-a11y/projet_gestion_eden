<script setup>
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js'

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
)

const props = defineProps({
    revenus: Number,
    depenses: Number
})

const chartData = {
    labels: ['Revenus', 'Dépenses'],
    datasets: [
        {
            label: 'Comparaison Revenus vs Dépenses',
            data: [props.revenus, props.depenses],
            backgroundColor: ['#3B82F6', '#F59E0B'], // BLEU et ORANGE
            borderColor: ['#3B82F6', '#F59E0B'],
        }
    ]
}

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false
}
</script>

<template>
    <div class="h-96">
        <Bar
            :data="chartData"
            :options="chartOptions"
        />
    </div>
</template>