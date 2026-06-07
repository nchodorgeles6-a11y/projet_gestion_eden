<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Affectation;
use App\Models\User;
use App\Models\Poste;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AffectationController extends Controller
{
    public function index()
    {
        $affectations = Affectation::with(['user', 'poste.departement'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Affectations/Index', [
            'affectations' => $affectations,
        ]);
    }

    public function create()
    {
        $users = User::orderBy('nom')->get([
            'id', 'nom', 'prenom', 'salaire_base',
            'prime_transport', 'prime_logement', 'prime_fonction',
            'prime_rendement', 'prime_panier', 'bonus_annuel',
        ]);
        $postes = Poste::with('departement')->orderBy('nom')->get();

        return Inertia::render('Affectations/Create', [
            'users'  => $users,
            'postes' => $postes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'poste_id'         => 'required|exists:postes,id',
            'date_debut'       => 'required|date',
            'date_fin'         => 'nullable|date|after_or_equal:date_debut',
            'motif_changement' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($request->user_id);

        // Auto-close the employee's current active affectation
        Affectation::where('user_id', $request->user_id)
            ->whereNull('date_fin')
            ->update([
                'date_fin' => Carbon::parse($request->date_debut)->subDay()->toDateString(),
            ]);

        // Snapshot salary and all primes at this moment in time
        $primes = [
            'transport' => (float) ($user->prime_transport ?? 0),
            'logement'  => (float) ($user->prime_logement ?? 0),
            'fonction'  => (float) ($user->prime_fonction ?? 0),
            'rendement' => (float) ($user->prime_rendement ?? 0),
            'panier'    => (float) ($user->prime_panier ?? 0),
            'bonus'     => (float) ($user->bonus_annuel ?? 0),
        ];

        Affectation::create([
            'id'               => Str::uuid(),
            'user_id'          => $request->user_id,
            'poste_id'         => $request->poste_id,
            'date_debut'       => $request->date_debut,
            'date_fin'         => $request->date_fin,
            'motif_changement' => $request->motif_changement,
            'salaire_capture'  => (float) $user->salaire_base,
            'primes_capture'   => $primes,
        ]);

        return redirect()->route('affectations.index')
            ->with('success', 'Affectation enregistrée avec succès.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $affectation = Affectation::findOrFail($id);
        $users = User::orderBy('nom')->get([
            'id', 'nom', 'prenom', 'salaire_base',
            'prime_transport', 'prime_logement', 'prime_fonction',
            'prime_rendement', 'prime_panier', 'bonus_annuel',
        ]);
        $postes = Poste::with('departement')->orderBy('nom')->get();

        return Inertia::render('Affectations/Edit', [
            'affectation' => $affectation,
            'users'       => $users,
            'postes'      => $postes,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'poste_id'         => 'required|exists:postes,id',
            'date_debut'       => 'required|date',
            'date_fin'         => 'nullable|date|after_or_equal:date_debut',
            'motif_changement' => 'nullable|string|max:500',
        ]);

        $affectation = Affectation::findOrFail($id);
        $affectation->update([
            'user_id'          => $request->user_id,
            'poste_id'         => $request->poste_id,
            'date_debut'       => $request->date_debut,
            'date_fin'         => $request->date_fin,
            'motif_changement' => $request->motif_changement,
        ]);

        return redirect()->route('affectations.index')
            ->with('success', 'Affectation modifiée avec succès.');
    }

    public function destroy(string $id)
    {
        Affectation::findOrFail($id)->delete();

        return redirect()->route('affectations.index')
            ->with('success', 'Affectation supprimée.');
    }
}
