<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conge;
use App\Models\Motif;
use inertia\inertia;
use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\CongeApprouve;
use App\Mail\CongeRefuse;
use Illuminate\Support\Facades\Mail;

class CongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statut = request()->query('statut', '');

        $conges = Conge::with([
            'user.affectations.poste.departement',
            'motif',
        ])
        ->when($statut, fn ($q) => $q->where('statut', $statut))
        ->orderByDesc('created_at')
        ->paginate(15);

        $kpisTotal = [
            'total'    => Conge::count(),
            'pending'  => Conge::where('statut', 'pending')->count(),
            'approuve' => Conge::where('statut', 'approuve')->count(),
            'refuse'   => Conge::where('statut', 'refuse')->count(),
        ];

        // Soldes congés par employé (calculés pour l'année en cours)
        $soldes = User::withCount([
            'conges as jours_pris' => fn ($q) => $q
                ->where('statut', 'approuve')
                ->whereYear('date_debut', now()->year)
                ->selectRaw('COALESCE(SUM(DATEDIFF(COALESCE(date_fin, date_debut), date_debut) + 1), 0)'),
        ])->orderBy('nom')->get(['id', 'nom', 'prenom'])->map(fn ($u) => [
            'id'       => $u->id,
            'nom'      => $u->nom . ' ' . $u->prenom,
            'droit'    => 30,
            'utilises' => (int) min(30, $u->jours_pris ?? 0),
            'restant'  => max(0, 30 - (int) ($u->jours_pris ?? 0)),
        ]);

        return inertia::render('Conges/Index', [
            'conges'    => $conges,
            'kpisTotal' => $kpisTotal,
            'statut'    => $statut,
            'soldes'    => $soldes,
        ]);
      
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia::render('Conges/Create', [
            'motifs' => Motif::where('type', 'conge')->orderBy('nom')->get(),
            'users'  => User::orderBy('nom')->get(['id', 'nom', 'prenom']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'motif_id' => 'required',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        Conge::create([
            'id' => Str::uuid(),
            'user_id' => $request->user_id,
            'motif_id' => $request->motif_id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'pending'
        ]);
        return redirect()->route('conges.index')->with('success', 'Demande de congé créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $conge = Conge::find($id);
        $motifs = Motif::all();
        $users = User::all();
        return inertia::render('Conges/Edit', [
            'conge' => $conge,
            'motifs' => $motifs,
            'users' => $users
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required',
            'motif_id' => 'required',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $conge = Conge::find($id);
        $conge->update([
            'user_id' => $request->user_id,
            'motif_id' => $request->motif_id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
        ]);

        return redirect()->route('conges.index')->with('success', 'Demande de congé mise à jour avec succès.');
    }

    public function approuver(Conge $conge)
    {
        $conge->load(['user', 'motif']);
        $conge->update(['statut' => 'approuve']);

        if ($conge->user?->email) {
            Mail::to($conge->user->email)->queue(new CongeApprouve($conge));
        }

        return back()->with('success', 'Congé de ' . $conge->user->prenom . ' ' . $conge->user->nom . ' approuvé.');
    }

    public function refuser(Conge $conge)
    {
        $conge->load(['user', 'motif']);
        $conge->update(['statut' => 'refuse']);

        if ($conge->user?->email) {
            Mail::to($conge->user->email)->queue(new CongeRefuse($conge));
        }

        return back()->with('success', 'Congé de ' . $conge->user->prenom . ' ' . $conge->user->nom . ' refusé.');
    }

    public function printLettre(Conge $conge)
    {
        $conge->load(['user.affectations.poste.departement', 'motif']);
        $aff  = $conge->user->affectations()->with('poste.departement')->latest('date_debut')->first();
        $solde = $conge->user->soldeConges();

        return response()->view('documents.lettre-conge', [
            'conge'       => $conge,
            'poste'       => $aff?->poste?->nom,
            'departement' => $aff?->poste?->departement?->nom,
            'solde'       => $solde,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $conge = Conge::find($id);
        if ($conge) {
            $conge->delete();
            return redirect()->route('conges.index')->with('success', 'Demande de congé supprimée avec succès.');
        } else {
            return redirect()->route('conges.index')->with('error', 'Demande de congé non trouvée.');
        }
    }
}
