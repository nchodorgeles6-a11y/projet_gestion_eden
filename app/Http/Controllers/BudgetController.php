<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Departement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public const CATEGORIES = [
        'Salaires et charges sociales',
        'Loyer et charges locatives',
        'Services informatiques',
        'Marketing et communication',
        'Équipement et fournitures',
        'Services externes / Prestataires',
        'Frais de déplacement',
        'Formation et développement',
        'Autres charges',
    ];

    public function index(Request $request)
    {
        $annee = (int) ($request->annee ?? now()->year);
        $mois  = $request->mois ? (int) $request->mois : null;

        // Budgets de la période
        $budgets = Budget::with('departement')
            ->where('annee', $annee)
            ->when($mois, fn ($q) => $q->where('mois', $mois))
            ->when(!$mois, fn ($q) => $q->whereNull('mois'))
            ->orderBy('categorie')
            ->get();

        // Réalisé par catégorie sur la même période
        $txQuery = Transaction::where('type', 'dépense')
            ->whereYear('date_transaction', $annee);

        if ($mois) {
            $txQuery->whereMonth('date_transaction', $mois);
        }

        $realise = $txQuery->get()
            ->groupBy('categorie')
            ->map(fn ($g) => $g->sum('montant'));

        // Fusion : budget + réalisé + écart
        $lignes = $budgets->map(function ($b) use ($realise) {
            $montantRealise = (float) ($realise[$b->categorie] ?? 0);
            $ecart = (float) $b->montant - $montantRealise;
            return [
                'id'          => $b->id,
                'categorie'   => $b->categorie,
                'departement' => $b->departement?->nom,
                'montant'     => (float) $b->montant,
                'realise'     => $montantRealise,
                'ecart'       => $ecart,
                'pct'         => $b->montant > 0 ? round($montantRealise / (float) $b->montant * 100, 1) : 0,
                'description' => $b->description,
            ];
        });

        // Totaux globaux
        $totalBudget  = $lignes->sum('montant');
        $totalRealise = $lignes->sum('realise');

        return Inertia::render('Budgets/Index', [
            'lignes'       => $lignes->values(),
            'totaux'       => [
                'budget'  => $totalBudget,
                'realise' => $totalRealise,
                'ecart'   => $totalBudget - $totalRealise,
            ],
            'departements' => Departement::orderBy('nom')->get(['id', 'nom']),
            'categories'   => self::CATEGORIES,
            'annees'       => range(now()->year - 2, now()->year + 1),
            'filtres'      => ['annee' => $annee, 'mois' => $mois],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'departement_id' => 'nullable|uuid|exists:departements,id',
            'annee'          => 'required|integer|min:2000|max:2100',
            'mois'           => 'nullable|integer|min:1|max:12',
            'categorie'      => 'required|string|max:100',
            'montant'        => 'required|numeric|min:0',
            'description'    => 'nullable|string|max:500',
        ]);

        Budget::create($data);

        return redirect()->back()->with('success', 'Budget créé avec succès.');
    }

    public function update(Request $request, Budget $budget)
    {
        $data = $request->validate([
            'departement_id' => 'nullable|uuid|exists:departements,id',
            'categorie'      => 'required|string|max:100',
            'montant'        => 'required|numeric|min:0',
            'description'    => 'nullable|string|max:500',
        ]);

        $budget->update($data);

        return redirect()->back()->with('success', 'Budget mis à jour.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->back()->with('success', 'Budget supprimé.');
    }
}
