<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\ROle;
use inertia\Inertia;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('nom')->paginate(15);
        return inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
        

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia::render('Roles/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Role $role)
    {
          $request->validate([
            'nom'=>'required',
        ]);

        Role::create([
            'id'=>Str::uuid(),
            'nom'=>$request->nom
       
       
       
            ]);

        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         $role= Role::find($id);
        return Inertia::render('Roles/Edit',[
            'role'=>$role
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
          $roles = Role::find($id);
        $roles->update([
            'nom'=>$request->nom,
        ]);
        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
          $role = Role::find($id);
        $role->delete();
        return redirect()->route('roles.index');
    }
}
