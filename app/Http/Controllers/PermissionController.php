<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('user')
            ->orderByDesc('date_debut')
            ->get()
            ->map(fn ($p) => array_merge($p->toArray(), [
                'is_active'  => $p->is_active,
                'user_nom'   => $p->user->nom . ' ' . $p->user->prenom,
            ]));

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'date_debut'   => 'required|date',
            'nombre_jours' => 'required|integer|min:1|max:365',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $dateDebut = Carbon::parse($validated['date_debut']);
        $dateFin   = $dateDebut->copy()->addDays($validated['nombre_jours'] - 1);

        Permission::create([
            'id'           => Str::uuid(),
            'user_id'      => $validated['user_id'],
            'date_debut'   => $dateDebut->toDateString(),
            'nombre_jours' => $validated['nombre_jours'],
            'date_fin'     => $dateFin->toDateString(),
            'notes'        => $validated['notes'] ?? null,
            'statut'       => 'active',
        ]);

        return back()->with('success', 'Permission créée.');
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->update(['statut' => 'annule']);
        return back();
    }
}
