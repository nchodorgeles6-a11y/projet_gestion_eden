<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Affectation;
use App\Models\Departement;
use App\Models\BulletinPaie;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Usercontroller extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $users = User::with(['affectations.poste.departement', 'roles'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('prenom', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            }))
            ->orderBy('nom')
            ->paginate(15)
            ->through(function ($user) {
                $affectation = $user->affectations->sortByDesc('created_at')->first();
                $joursRestants = $user->date_fin_contrat
                    ? (int) now()->diffInDays($user->date_fin_contrat, false)
                    : null;
                return array_merge($user->toArray(), [
                    'poste'           => $affectation?->poste?->nom,
                    'departement'     => $affectation?->poste?->departement?->nom,
                    'roles'           => $user->roles->pluck('nom'),
                    'acces_systeme'   => (bool) $user->acces_systeme,
                    'jours_restants'  => $joursRestants,
                ]);
            });

        $totaux = [
            'employes'     => User::where('type_contrat', 'employe')->count(),
            'prestataires' => User::where('type_contrat', 'prestataire')->count(),
            'avec_acces'   => User::where('acces_systeme', true)->count(),
        ];

        return Inertia::render('Users/Index', [
            'users'  => $users,
            'roles'  => Role::orderBy('nom')->get(['id', 'nom']),
            'totaux' => $totaux,
            'search' => $search,
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'conges.motif',
            'paiements',
        ]);

        $bulletins = BulletinPaie::where('user_id', $user->id)
            ->orderByDesc('annee')->orderByDesc('created_at')->get();

        // Full career history ordered chronologically
        $career = $user->affectations()
            ->with(['poste.departement'])
            ->orderBy('date_debut')
            ->get()
            ->map(function ($a) {
                $dureeJours = $a->date_debut && $a->date_fin
                    ? $a->date_debut->diffInDays($a->date_fin)
                    : ($a->date_debut ? $a->date_debut->diffInDays(now()) : 0);
                $durMois = (int) round($dureeJours / 30);

                return [
                    'id'          => $a->id,
                    'poste'       => $a->poste?->nom ?? '—',
                    'departement' => $a->poste?->departement?->nom ?? '—',
                    'date_debut'  => $a->date_debut?->format('d/m/Y'),
                    'date_fin'    => $a->date_fin?->format('d/m/Y'),
                    'motif'       => $a->motif_changement,
                    'salaire'     => (float) ($a->salaire_capture ?? 0),
                    'primes'      => $a->primes_capture ?? [],
                    'actif'       => is_null($a->date_fin),
                    'duree'       => $a->date_fin
                        ? ($durMois >= 12
                            ? floor($durMois / 12) . ' an' . (floor($durMois / 12) > 1 ? 's' : '') . ($durMois % 12 > 0 ? ' ' . ($durMois % 12) . ' mois' : '')
                            : $durMois . ' mois')
                        : 'En cours',
                ];
            })
            ->values();

        return Inertia::render('Users/Show', [
            'user'      => $user,
            'bulletins' => $bulletins,
            'career'    => $career,
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'departements' => Departement::with('postes')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email',
            'telephone'        => 'required|string|max:20',
            'type_contrat'     => 'required|in:employe,prestataire',
            'poste_id'         => 'required|exists:postes,id',
            'salaire_base'     => 'required|numeric|min:0',
            'mode_paiement'    => 'required|in:fixe,par_prestation',
            'prime_transport'  => 'nullable|numeric|min:0',
            'prime_logement'   => 'nullable|numeric|min:0',
            'prime_fonction'   => 'nullable|numeric|min:0',
            'prime_rendement'  => 'nullable|numeric|min:0',
            'prime_panier'     => 'nullable|numeric|min:0',
            'bonus_annuel'     => 'nullable|numeric|min:0',
            'cnps'             => 'boolean',
            'assurance_maladie'=> 'boolean',
            'avantages_nature' => 'nullable|array',
            'date_fin_contrat' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'id'               => Str::uuid(),
                'nom'              => $request->nom,
                'prenom'           => $request->prenom,
                'email'            => $request->email,
                'telephone'        => $request->telephone,
                'type_contrat'     => $request->type_contrat,
                'salaire_base'     => $request->salaire_base,
                'mode_paiement'    => $request->mode_paiement,
                'prime_transport'  => $request->prime_transport ?? 0,
                'prime_logement'   => $request->prime_logement ?? 0,
                'prime_fonction'   => $request->prime_fonction ?? 0,
                'prime_rendement'  => $request->prime_rendement ?? 0,
                'prime_panier'     => $request->prime_panier ?? 0,
                'bonus_annuel'     => $request->bonus_annuel ?? 0,
                'cnps'             => $request->boolean('cnps'),
                'assurance_maladie'=> $request->boolean('assurance_maladie'),
                'avantages_nature' => $request->avantages_nature ?? [],
                'date_fin_contrat' => $request->date_fin_contrat,
                'lieu_habitation'  => 'Non défini',
                'sexe'             => 'homme',
                'date_embauche'    => now(),
                'date_naissance'   => now(),
                'password'         => Hash::make('password'),
            ]);

            // Capture salary snapshot for the initial affectation
            Affectation::create([
                'id'              => Str::uuid(),
                'user_id'         => $user->id,
                'poste_id'        => $request->poste_id,
                'date_debut'      => now(),
                'salaire_capture' => (float) $request->salaire_base,
                'primes_capture'  => [
                    'transport' => (float) ($request->prime_transport ?? 0),
                    'logement'  => (float) ($request->prime_logement ?? 0),
                    'fonction'  => (float) ($request->prime_fonction ?? 0),
                    'rendement' => (float) ($request->prime_rendement ?? 0),
                    'panier'    => (float) ($request->prime_panier ?? 0),
                    'bonus'     => (float) ($request->bonus_annuel ?? 0),
                ],
                'motif_changement' => 'Embauche initiale',
            ]);
        });

        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        $affectation = $user->affectations()->with('poste.departement')->latest()->first();

        return Inertia::render('Users/Edit', [
            'user'                 => $user,
            'departements'         => Departement::with('postes')->get(),
            'currentPosteId'       => $affectation?->poste_id,
            'currentDepartementId' => $affectation?->poste?->departement_id,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'telephone'        => 'required|string|max:20',
            'type_contrat'     => 'required|in:employe,prestataire',
            'poste_id'         => 'required|exists:postes,id',
            'salaire_base'     => 'required|numeric|min:0',
            'mode_paiement'    => 'required|in:fixe,par_prestation',
            'prime_transport'  => 'nullable|numeric|min:0',
            'prime_logement'   => 'nullable|numeric|min:0',
            'prime_fonction'   => 'nullable|numeric|min:0',
            'prime_rendement'  => 'nullable|numeric|min:0',
            'prime_panier'     => 'nullable|numeric|min:0',
            'bonus_annuel'     => 'nullable|numeric|min:0',
            'cnps'             => 'boolean',
            'assurance_maladie'=> 'boolean',
            'avantages_nature' => 'nullable|array',
            'date_fin_contrat' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'nom'              => $request->nom,
                'prenom'           => $request->prenom,
                'email'            => $request->email,
                'telephone'        => $request->telephone,
                'type_contrat'     => $request->type_contrat,
                'salaire_base'     => $request->salaire_base,
                'mode_paiement'    => $request->mode_paiement,
                'prime_transport'  => $request->prime_transport ?? 0,
                'prime_logement'   => $request->prime_logement ?? 0,
                'prime_fonction'   => $request->prime_fonction ?? 0,
                'prime_rendement'  => $request->prime_rendement ?? 0,
                'prime_panier'     => $request->prime_panier ?? 0,
                'bonus_annuel'     => $request->bonus_annuel ?? 0,
                'cnps'             => $request->boolean('cnps'),
                'assurance_maladie'=> $request->boolean('assurance_maladie'),
                'avantages_nature' => $request->avantages_nature ?? [],
                'date_fin_contrat' => $request->date_fin_contrat,
            ]);

            $affectation = $user->affectations()->latest()->first();
            if ($affectation) {
                $affectation->update(['poste_id' => $request->poste_id]);
            } else {
                Affectation::create([
                    'id'         => Str::uuid(),
                    'user_id'    => $user->id,
                    'poste_id'   => $request->poste_id,
                    'date_debut' => now(),
                ]);
            }
        });

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
    }

    public function autoriserAcces(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $password = Str::random(10);

        $user->update([
            'acces_systeme' => true,
            'password'      => Hash::make($password),
        ]);

        $user->roles()->sync([$request->role_id]);

        return back()->with([
            'success'       => 'Accès accordé à ' . $user->prenom . ' ' . $user->nom . '.',
            'temp_password' => $password,
            'temp_user'     => $user->prenom . ' ' . $user->nom,
            'temp_email'    => $user->email,
        ]);
    }

    public function retirerAcces(User $user)
    {
        $user->update(['acces_systeme' => false]);
        $user->roles()->detach();

        return back()->with('success', 'Accès retiré pour ' . $user->prenom . ' ' . $user->nom . '.');
    }

    private function getAffectationActuelle(User $user): array
    {
        $a = $user->affectations()->with('poste.departement')->latest('date_debut')->first();
        return [
            'poste'       => $a?->poste?->nom,
            'departement' => $a?->poste?->departement?->nom,
        ];
    }

    public function printContrat(User $user)
    {
        $user->load('affectations.poste.departement');
        $aff = $this->getAffectationActuelle($user);
        return response()->view('documents.contrat-travail', [
            'user'        => $user,
            'poste'       => $aff['poste'],
            'departement' => $aff['departement'],
        ]);
    }

    public function printAttestation(User $user)
    {
        $user->load('affectations.poste.departement');
        $aff = $this->getAffectationActuelle($user);
        return response()->view('documents.attestation-travail', [
            'user'        => $user,
            'poste'       => $aff['poste'],
            'departement' => $aff['departement'],
        ]);
    }

    public function printLicenciement(User $user)
    {
        $user->load('affectations.poste.departement');
        $aff = $this->getAffectationActuelle($user);
        return response()->view('documents.licenciement', [
            'user'        => $user,
            'poste'       => $aff['poste'],
            'departement' => $aff['departement'],
        ]);
    }

    public function export()
    {
        $users = User::with('affectations.poste.departement')->orderBy('nom')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="employes_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($users) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($f, ['Nom','Prénom','Email','Téléphone','Type contrat','Poste','Département','Salaire base','Date embauche'], ';');

            foreach ($users as $u) {
                $aff   = $u->affectations->sortByDesc('created_at')->first();
                $poste = $aff?->poste?->nom ?? '';
                $dept  = $aff?->poste?->departement?->nom ?? '';
                fputcsv($f, [
                    $u->nom, $u->prenom, $u->email, $u->telephone ?? '',
                    $u->type_contrat, $poste, $dept,
                    $u->salaire_base,
                    $u->date_embauche?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
