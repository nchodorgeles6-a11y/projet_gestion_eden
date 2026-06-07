<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suivie;
use App\Models\Conge;
use App\Models\Motif;
use App\Models\User;
use App\Models\TypeSuivie;
use App\Models\Permission;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SuivieController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $usersQuery = User::with([
            'affectations.poste.departement',
            'conges.motif',
            'permissions',
        ])->orderBy('nom');

        $suivisDuJour = Suivie::with(['typeSuivie', 'motif', 'conge.motif', 'permission'])
            ->whereDate('date', $today)
            ->get()
            ->keyBy('user_id');

        $typeSuivies = TypeSuivie::orderBy('nom')->get();
        $motifs      = Motif::orderBy('nom')->get();

        $presence = $usersQuery->paginate(20)->through(function ($user) use ($suivisDuJour, $typeSuivies, $today) {
            $suivi       = $suivisDuJour->get($user->id);
            $typePresent = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'pr'));

            // Congés de l'employé — tous statuts, triés par actif puis récent
            $congesDisponibles = $user->conges->map(fn ($c) => [
                'id'         => $c->id,
                'date_debut' => $c->date_debut?->format('d/m/Y'),
                'date_fin'   => $c->date_fin?->format('d/m/Y'),
                'motif'      => $c->motif?->nom,
                'motif_id'   => $c->motif_id,
                'statut'     => $c->statut,
                'is_active'  => $c->date_debut?->toDateString() <= $today
                                && ($c->date_fin === null || $c->date_fin?->toDateString() >= $today)
                                && in_array($c->statut, ['pending', 'approuve']),
            ])->sortByDesc('is_active')->values();

            // Congé actif aujourd'hui (pour auto-détection)
            $congeActif = $user->conges->first(fn ($c) =>
                $c->date_debut?->toDateString() <= $today
                && ($c->date_fin === null || $c->date_fin?->toDateString() >= $today)
                && in_array($c->statut, ['pending', 'approuve'])
            );

            // Permissions actives aujourd'hui
            $permissionsDisponibles = $user->permissions
                ->filter(fn ($p) => $p->statut !== 'annule')
                ->map(fn ($p) => [
                    'id'           => $p->id,
                    'date_debut'   => $p->date_debut?->format('d/m/Y'),
                    'date_fin'     => $p->date_fin?->format('d/m/Y'),
                    'nombre_jours' => $p->nombre_jours,
                    'notes'        => $p->notes,
                    'statut'       => $p->statut,
                    'is_active'    => $p->is_active,
                ])->sortByDesc('is_active')->values();

            // Permission active aujourd'hui (auto-détectée)
            $permissionActive = $user->permissions
                ->first(fn ($p) => $p->is_active);

            return [
                'user'        => array_merge($user->toArray(), [
                    'poste'       => $user->affectations->sortByDesc('created_at')->first()?->poste?->nom,
                    'departement' => $user->affectations->sortByDesc('created_at')->first()?->poste?->departement?->nom,
                ]),
                'suivi_id'              => $suivi?->id,
                'type_suivie'           => $suivi?->typeSuivie ?? $typePresent,
                'motif'                 => $suivi?->motif,
                'justifiee'             => $suivi?->justifiee ?? false,
                'conge'                 => $suivi?->conge,
                'permission'            => $suivi?->permission,
                'est_explicite'         => $suivi !== null,
                'conges_disponibles'      => $congesDisponibles,
                'conge_actif'             => $congeActif ? [
                    'id'         => $congeActif->id,
                    'date_debut' => $congeActif->date_debut?->format('d/m/Y'),
                    'date_fin'   => $congeActif->date_fin?->format('d/m/Y'),
                    'motif'      => $congeActif->motif?->nom,
                    'motif_id'   => $congeActif->motif_id,
                    'statut'     => $congeActif->statut,
                ] : null,
                'permissions_disponibles' => $permissionsDisponibles,
                'permission_active_id'    => $permissionActive?->id,
            ];
        });

        return Inertia::render('Suivies/Index', [
            'presence'    => $presence,
            'typeSuivies' => $typeSuivies,
            'motifs'      => $motifs,
            'today'       => now()->locale('fr')->isoFormat('dddd D MMMM YYYY'),
            'todayRaw'    => $today,
        ]);
    }

    public function changerStatut(Request $request)
    {
        $request->validate([
            'user_id'               => 'required|exists:users,id',
            'type_suivie_id'        => 'required|exists:type_suivies,id',
            'date'                  => 'required|date',
            'motif_id'              => 'nullable|exists:motifs,id',
            'justifiee'             => 'boolean',
            'conge_id'              => 'nullable|exists:conges,id',
            'permission_id'         => 'nullable|exists:permissions,id',
            'revenir_present'       => 'boolean',
            // Champs pour créer une permission inline
            'new_permission_debut'  => 'nullable|date',
            'new_permission_jours'  => 'nullable|integer|min:1|max:365',
            'new_permission_notes'  => 'nullable|string|max:1000',
        ]);

        if ($request->boolean('revenir_present')) {
            Suivie::where('user_id', $request->user_id)
                ->whereDate('date', $request->date)
                ->delete();

            return back();
        }

        // Créer une permission à la volée si demandé
        $permissionId = $request->permission_id;

        if (!$permissionId && $request->filled('new_permission_debut') && $request->filled('new_permission_jours')) {
            $debut = Carbon::parse($request->new_permission_debut);
            $fin   = $debut->copy()->addDays((int) $request->new_permission_jours - 1);

            $perm = Permission::create([
                'id'           => Str::uuid(),
                'user_id'      => $request->user_id,
                'date_debut'   => $debut->toDateString(),
                'nombre_jours' => (int) $request->new_permission_jours,
                'date_fin'     => $fin->toDateString(),
                'notes'        => $request->new_permission_notes,
                'statut'       => 'active',
            ]);

            $permissionId = $perm->id;
        }

        // Auto-lier le congé actif si l'employé est absent sans congé spécifié
        $congeId   = $request->conge_id;
        $justifiee = $request->boolean('justifiee');

        if (!$congeId) {
            $type = TypeSuivie::find($request->type_suivie_id);
            if ($type && str_contains(strtolower($type->nom), 'abs')) {
                $congeActif = Conge::where('user_id', $request->user_id)
                    ->whereDate('date_debut', '<=', $request->date)
                    ->where(function ($q) use ($request) {
                        $q->whereNull('date_fin')
                          ->orWhereDate('date_fin', '>=', $request->date);
                    })
                    ->whereIn('statut', ['pending', 'approuve'])
                    ->first();

                if ($congeActif) {
                    $congeId   = $congeActif->id;
                    $justifiee = true;
                }
            }
        }

        $suivi = Suivie::firstOrNew([
            'user_id' => $request->user_id,
            'date'    => $request->date,
        ]);

        if (!$suivi->id) {
            $suivi->id = Str::uuid();
        }

        $suivi->type_suivie_id = $request->type_suivie_id;
        $suivi->motif_id       = $request->motif_id;
        $suivi->justifiee      = $justifiee;
        $suivi->conge_id       = $congeId;
        $suivi->permission_id  = $permissionId;
        $suivi->save();

        return back();
    }

    public function destroy(string $id)
    {
        Suivie::findOrFail($id)->delete();
        return back();
    }

    public function create()   { return redirect()->route('suivies.index'); }
    public function store(Request $request) { return redirect()->route('suivies.index'); }
    public function edit(string $id)        { return redirect()->route('suivies.index'); }
    public function update(Request $request, string $id) { return redirect()->route('suivies.index'); }
}
