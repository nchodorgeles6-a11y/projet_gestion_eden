<?php

namespace App\Http\Controllers;

use App\Models\Motif;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MotifController extends Controller
{
    public function index()
    {
        $motifs = Motif::orderBy('type')->orderBy('nom')->get();
        return Inertia::render('Motifs/Index', ['motifs' => $motifs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'  => 'required|string|max:100',
            'type' => 'required|in:conge,presence,retard',
        ]);

        Motif::create([
            'id'  => Str::uuid(),
            'nom' => trim($request->nom),
            'type'=> $request->type,
        ]);

        return back()->with('success', 'Motif ajouté.');
    }

    public function update(Request $request, Motif $motif)
    {
        $request->validate([
            'nom'  => 'required|string|max:100',
            'type' => 'required|in:conge,presence,retard',
        ]);

        $motif->update(['nom' => trim($request->nom), 'type' => $request->type]);
        return back()->with('success', 'Motif mis à jour.');
    }

    public function destroy(Motif $motif)
    {
        $motif->delete();
        return back()->with('success', 'Motif supprimé.');
    }
}
