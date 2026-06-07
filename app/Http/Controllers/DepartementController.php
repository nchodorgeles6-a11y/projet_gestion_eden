<?php

namespace App\Http\Controllers;
use App\Models\Departement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departements = Departement::orderBy('nom')->paginate(15);
        return Inertia::render('Departements/Index', [
            'departements' => $departements,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Departements/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Departement $departement)
    {
        $request->validate([
            'nom'=>'required',
        ]);

        Departement::create([
            'id'=>Str::uuid(),
            'nom'=>$request->nom
       
       
       
            ]);

        return redirect()->route('departements.index');
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
        $departement = Departement::find($id);
        return Inertia::render('Departements/Edit',[
            'departement'=>$departement
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $departement = Departement::find($id);
        $departement->update([
            'nom'=>$request->nom,
        ]);
        return redirect()->route('departements.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $departement = Departement::find($id);
        $departement->delete();
        return redirect()->route('departements.index');
    }
}
