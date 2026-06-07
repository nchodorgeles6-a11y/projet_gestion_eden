<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Facture;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $factures = Facture::with('departement')
            ->orderByDesc('date_facture')
            ->paginate(20)
            ->withQueryString();

        $totaux = [
            'total'      => Facture::count(),
            'en_attente' => Facture::where('statut', 'en_attente')->sum('montant_ttc'),
            'payees'     => Facture::where('statut', 'payee')->sum('montant_ttc'),
            'en_retard'  => Facture::where('statut', 'en_attente')
                ->whereNotNull('date_echeance')
                ->where('date_echeance', '<', now()->toDateString())
                ->count(),
        ];

        return Inertia::render('Factures/Index', [
            'factures'     => $factures->through(fn ($f) => [
                'id'            => $f->id,
                'numero'        => $f->numero,
                'fournisseur'   => $f->fournisseur,
                'description'   => $f->description,
                'montant_ht'    => (float) $f->montant_ht,
                'tva'           => (float) $f->tva,
                'montant_ttc'   => (float) $f->montant_ttc,
                'date_facture'  => $f->date_facture?->format('d/m/Y'),
                'date_echeance' => $f->date_echeance?->format('d/m/Y'),
                'statut'        => $f->statut,
                'categorie'     => $f->categorie,
                'departement'   => $f->departement?->nom,
                'en_retard'     => $f->statut === 'en_attente' && $f->date_echeance?->isPast(),
            ]),
            'totaux'       => $totaux,
            'departements' => Departement::orderBy('nom')->get(['id', 'nom']),
            'categories'   => \App\Http\Controllers\BudgetController::CATEGORIES,
        ]);
    }

    public function create()
    {
        return Inertia::render('Factures/Create', [
            'departements' => Departement::orderBy('nom')->get(['id', 'nom']),
            'categories'   => \App\Http\Controllers\BudgetController::CATEGORIES,
            'numero'       => 'FAC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero'         => 'required|string|max:50|unique:factures,numero',
            'fournisseur'    => 'required|string|max:200',
            'description'    => 'nullable|string',
            'montant_ht'     => 'required|numeric|min:0',
            'tva'            => 'required|numeric|min:0|max:100',
            'date_facture'   => 'required|date',
            'date_echeance'  => 'nullable|date|after_or_equal:date_facture',
            'statut'         => 'required|in:en_attente,payee,annulee',
            'categorie'      => 'nullable|string|max:100',
            'departement_id' => 'nullable|uuid|exists:departements,id',
        ]);

        $data['montant_ttc'] = round($data['montant_ht'] * (1 + $data['tva'] / 100), 2);

        $facture = Facture::create($data);

        // Si facture déjà payée → créer transaction dépense automatiquement
        if ($data['statut'] === 'payee') {
            $this->creerTransaction($facture);
        }

        return redirect('/factures')->with('success', 'Facture enregistrée avec succès.');
    }

    public function edit(Facture $facture)
    {
        return Inertia::render('Factures/Create', [
            'facture'      => $facture->load('departement'),
            'departements' => Departement::orderBy('nom')->get(['id', 'nom']),
            'categories'   => \App\Http\Controllers\BudgetController::CATEGORIES,
            'numero'       => $facture->numero,
        ]);
    }

    public function update(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'fournisseur'    => 'required|string|max:200',
            'description'    => 'nullable|string',
            'montant_ht'     => 'required|numeric|min:0',
            'tva'            => 'required|numeric|min:0|max:100',
            'date_facture'   => 'required|date',
            'date_echeance'  => 'nullable|date|after_or_equal:date_facture',
            'statut'         => 'required|in:en_attente,payee,annulee',
            'categorie'      => 'nullable|string|max:100',
            'departement_id' => 'nullable|uuid|exists:departements,id',
        ]);

        $data['montant_ttc'] = round($data['montant_ht'] * (1 + $data['tva'] / 100), 2);

        $ancienStatut = $facture->statut;
        $facture->update($data);

        // Si passage à "payée" → créer transaction
        if ($ancienStatut !== 'payee' && $data['statut'] === 'payee') {
            $this->creerTransaction($facture->fresh());
        }

        return redirect('/factures')->with('success', 'Facture mise à jour.');
    }

    public function destroy(Facture $facture)
    {
        $facture->delete();
        return redirect('/factures')->with('success', 'Facture supprimée.');
    }

    public function payer(Facture $facture)
    {
        if ($facture->statut === 'payee') {
            return redirect()->back()->with('error', 'Cette facture est déjà payée.');
        }

        $facture->update(['statut' => 'payee']);
        $this->creerTransaction($facture);

        return redirect()->back()->with('success', 'Facture marquée comme payée · transaction créée.');
    }

    private function creerTransaction(Facture $facture): void
    {
        Transaction::create([
            'id'               => Str::uuid(),
            'departement_id'   => $facture->departement_id,
            'type'             => 'dépense',
            'categorie'        => $facture->categorie,
            'montant'          => $facture->montant_ttc,
            'date_transaction' => $facture->date_facture ?? now()->toDateString(),
            'source'           => 'manuel',
            'description'      => 'Facture ' . $facture->numero . ' — ' . $facture->fournisseur,
        ]);
    }
}
