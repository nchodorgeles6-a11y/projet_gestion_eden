<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;

class StatistiqueController extends Controller
{
    public function index()
    {
        $annee = request('annee', now()->year);

        // REVENUS
        $revenusActuels = Transaction::where('type', 'revenu')
            ->whereYear('created_at', $annee)
            ->selectRaw('MONTH(created_at) mois, SUM(montant) total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        $revenusPrecedents = Transaction::where('type', 'revenu')
            ->whereYear('created_at', $annee - 1)
            ->selectRaw('MONTH(created_at) mois, SUM(montant) total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        // DEPENSES
        $depensesActuelles = Transaction::where('type', 'depense')
            ->whereYear('created_at', $annee)
            ->selectRaw('MONTH(created_at) mois, SUM(montant) total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        $depensesPrecedentes = Transaction::where('type', 'depense')
            ->whereYear('created_at', $annee - 1)
            ->selectRaw('MONTH(created_at) mois, SUM(montant) total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        // TOTAUX
        $revenusTotalActuel = Transaction::where('type', 'revenu')
            ->whereYear('created_at', $annee)
            ->sum('montant');

        $revenusTotalPrecedent = Transaction::where('type', 'revenu')
            ->whereYear('created_at', $annee - 1)
            ->sum('montant');

        $depensesTotalActuel = Transaction::where('type', 'depense')
            ->whereYear('created_at', $annee)
            ->sum('montant');

        $depensesTotalPrecedent = Transaction::where('type', 'depense')
            ->whereYear('created_at', $annee - 1)
            ->sum('montant');

        // BENEFICES
        $beneficeActuel = $revenusTotalActuel - $depensesTotalActuel;
        $beneficePrecedent = $revenusTotalPrecedent - $depensesTotalPrecedent;

        // GRAPHIQUE
        $chartData = [];

        for ($i = 1; $i <= 12; $i++) {

            $revenuActuel = $revenusActuels[$i] ?? 0;
            $revenuPrecedent = $revenusPrecedents[$i] ?? 0;

            $depenseActuelle = $depensesActuelles[$i] ?? 0;
            $depensePrecedente = $depensesPrecedentes[$i] ?? 0;

            $chartData[] = [
                'mois' => Carbon::create()->month($i)->translatedFormat('F'),

                'revenu_actuel' => $revenuActuel,
                'revenu_precedent' => $revenuPrecedent,

                'depense_actuelle' => $depenseActuelle,
                'depense_precedente' => $depensePrecedente,

                'benefice_actuel' => $revenuActuel - $depenseActuelle,
                'benefice_precedent' => $revenuPrecedent - $depensePrecedente,
            ];
        }

        return Inertia::render('Statistiques/Index', [

            'chartData' => $chartData,

            'revenusTotalActuel' => $revenusTotalActuel,
            'revenusTotalPrecedent' => $revenusTotalPrecedent,

            'depensesTotalActuel' => $depensesTotalActuel,
            'depensesTotalPrecedent' => $depensesTotalPrecedent,

            'beneficeActuel' => $beneficeActuel,
            'beneficePrecedent' => $beneficePrecedent,

            'annee' => $annee,
            'anneeActuelle' => $annee,
            'anneePrecedente' => $annee - 1,
        ]);
    }
}
