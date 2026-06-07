<?php

namespace App\Http\Controllers;

use App\Models\Poste;
 use App\Models\Departement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;


class PosteController extends Controller
{ 

public function index()
    {
        $postes = Poste::orderBy('nom')->paginate(15);
        return Inertia::render('Postes/Index', [
            'postes' => $postes,
        ]);
    }
  

        public function create()
{
     $departements = Departement::all();

     return Inertia::render('Postes/Create', [

         'departements' => $departements

     ]);

   

}


    

    public function store(Request $request , Poste $poste)
    {
        $request->validate([
            'nom'=>'required',
            'description'=>'required',
            'departement_id'=>'required'
           
        ]);

        Poste::create([
            'id'=>Str::uuid(),
            'nom'=>$request->nom,
            'description'=>$request->description,
            'departement_id'=>$request->departement_id,
        ]);

        return redirect()->route('postes.index');
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
        $poste = Poste::find($id);
        return Inertia::render('Postes/Edit',[
            'poste'=>$poste
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $poste = Poste::find($id);
        $poste->update([
            'nom'=>$request->nom,
            'description'=>$request->description,
        ]);
        return redirect()->route('postes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $poste = Poste::find($id);
        $poste->delete();
        return redirect()->route('postes.index');
    }
}
