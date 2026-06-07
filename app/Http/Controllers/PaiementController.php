<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paiement;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paiements = Paiement::with([
            'user.affectations.poste.departement',
        ])->orderByDesc('created_at')->paginate(15);

        return Inertia::render('Paiements/Index', [
            'paiements' => $paiements,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();

        return Inertia::render('Paiements/Create', [
            'users' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'user_id' => 'required',
            'montant' => 'required|numeric',
            'mois' => 'required',
            'annee' => 'required',
        ]);

        Paiement::create([

            'id' => Str::uuid(),

            'user_id' => $request->user_id,

            'montant' => $request->montant,

            'mois' => $request->mois,

            'annee' => $request->annee,

            'reference' => 'PAY-' . strtoupper(Str::random(8)),

        ]);

        return redirect()->route('paiements.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $paiement = Paiement::find($id);

        $users = User::all();

        return Inertia::render('Paiements/Edit', [

            'paiement' => $paiement,
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
            'montant' => 'required|numeric',
            'mois' => 'required',
            'annee' => 'required',

        ]);

        $paiement = Paiement::find($id);

        $paiement->update([

            'user_id' => $request->user_id,

            'montant' => $request->montant,

            'mois' => $request->mois,

            'annee' => $request->annee,

        ]);

        return redirect()->route('paiements.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paiement = Paiement::find($id);

        $paiement->delete();

        return redirect()->route('paiements.index');
    }
}