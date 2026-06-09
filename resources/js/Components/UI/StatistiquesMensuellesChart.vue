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
    chartData: {
        type: Array,
        default: () => []
    }
})

const data = {
    labels: props.chartData.map(item => item.mois),

    datasets: [
        
    {
        label: 'Revenus',
        data: props.chartData.map(item => item.revenu_actuel),
        backgroundColor: '#3B82F6', // BLEU
        borderColor: '#3B82F6', // BLEU foncé
        borderWidth: 1
    },
    {
        label: 'Dépenses',
        data: props.chartData.map(item => item.depense_actuelle),
        backgroundColor: '#F59E0B', // ORANGE
        borderColor: '#f59e0b', // ORANGE foncé
        borderWidth: 1
    }
]
    
}

const options = {
    responsive: true,
    maintainAspectRatio: false
}
</script>

<template>
    <div style="height:400px">
        <Bar :data="data" :options="options" />
    </div>
</template>