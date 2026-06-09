<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head , router } from '@inertiajs/vue3'
import { defineProps } from 'vue'
import StatistiquesMensuellesChart from '@/Components/UI/StatistiquesMensuellesChart.vue'


const props = defineProps({
    chartData: Array,
    annee: Number,
    revenusTotalActuel: Number,
    revenusTotalPrecedent: Number,

    depensesTotalActuel: Number,
    depensesTotalPrecedent: Number,

    beneficeActuel: Number,
    beneficePrecedent: Number,

    anneeActuelle: Number,
    anneePrecedente: Number,
})
const changerAnnee = (event) => {
    router.get(
        route('statistiques.index'),
        {
            annee: event.target.value
        },
        {
            preserveState: true,
            preserveScroll: true
        }
    )
}

</script>
<template>

    <Head title="Statistiques" />

    <AuthenticatedLayout>

        <div class="space-y-6">

            <!-- Titre -->

            <div class="bg-white rounded-xl shadow p-6">

                <h1 class="text-3xl font-bold">
                    📊 Statistiques financières
                </h1>

                <p class="text-gray-500 mt-2">
                    Comparaison entre
                    {{ anneeActuelle }}
                    et
                    {{ anneePrecedente }}
                </p>

            </div>  


            
            <div class="mt-4" >
                <label class="font-semibold mr-2">
                  Année :
                </label>

                    <select
                     :value="anneeActuelle"
                      @change="changerAnnee"
                     class="border rounded-lg px-9 py-2"
                    >
                     <option value="2024">2024</option>
                     <option value="2025">2025</option>
                     <option value="2026">2026</option>
                     <option value="2027">2027</option>
                     <option value="2028">2028</option>
                     <option value="2029">2029</option>
                 </select>
             </div>

            <!-- KPI -->

            <div
                class="grid grid-cols-1 md:grid-cols-3 gap-6"
            >

                <div class="bg-green-50 rounded-xl p-6 shadow">

                    <h3 class="text-gray-500">
                        Revenus {{ anneeActuelle }}
                    </h3>

                    <p class="text-2xl font-bold text-green-600">

                        {{ Number(revenusTotalActuel).toLocaleString('fr-FR') }}
                        FCFA

                    </p>

                </div>

                <div class="bg-red-50 rounded-xl p-6 shadow">

                    <h3 class="text-gray-500">
                        Dépenses {{ anneeActuelle }}
                    </h3>

                    <p class="text-2xl font-bold text-red-600">

                        {{ Number(depensesTotalActuel).toLocaleString('fr-FR') }}
                        FCFA

                    </p>

                </div>

                <div class="bg-blue-50 rounded-xl p-6 shadow">

                    <h3 class="text-gray-500">
                        Bénéfice / Déficit {{ anneeActuelle }}
                    </h3>

                    <p class="text-2xl font-bold text-blue-600">

                        {{ Number(beneficeActuel).toLocaleString('fr-FR') }}
                        FCFA

                    </p>

                </div>

            </div>

            
             

            <!-- Debug temporaire -->

            <div class="bg-white rounded-xl shadow p-6">

                <h2 class="text-xl font-semibold mb-4">

                    Statistiques mensuelles 

                </h2>

                  <StatistiquesMensuellesChart :chartData="chartData" />

            </div>

         </div> 
      

    </AuthenticatedLayout>

</template>
