<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\BulletinPaie;
use App\Models\Conge;
use App\Models\Departement;
use App\Models\Suivie;
use App\Models\Transaction;
use App\Models\TypeSuivie;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // ── KPIs principaux ───────────────────────────────────────────────────
        $kpis = [
            'employes'          => User::count(),
            'departements'      => Departement::count(),
            'conge_pending'     => Conge::where('statut', 'pending')->count(),
            'bulletins_valider' => BulletinPaie::where('statut', 'brouillon')->count(),
            'masse_salariale'   => (int) BulletinPaie::where('statut', 'paye')
                ->whereYear('date_paiement', now()->year)
                ->whereMonth('date_paiement', now()->month)
                ->sum('net_a_payer'),
            'solde_tresorerie'  => (int) (
                Transaction::where('type', 'revenu')->sum('montant') -
                Transaction::where('type', 'dépense')->sum('montant')
            ),
        ];

        // ── Présence aujourd'hui ──────────────────────────────────────────────
        $typeSuivies = TypeSuivie::get();
        $idPresent   = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'pr'))?->id;
        $idAbsent    = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'abs'))?->id;
        $idRetard    = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'ret'))?->id;

        $suivisToday = Suivie::whereDate('date', $today)->get();
        $totalEmp    = $kpis['employes'];

        $presenceToday = [
            'total'    => $totalEmp,
            'presents' => $suivisToday->where('type_suivie_id', $idPresent)->count(),
            'absents'  => $suivisToday->where('type_suivie_id', $idAbsent)->count(),
            'retards'  => $suivisToday->where('type_suivie_id', $idRetard)->count(),
        ];
        $presenceToday['non_marques'] = max(0,
            $totalEmp - $presenceToday['presents'] - $presenceToday['absents'] - $presenceToday['retards']
        );

        // ── Tendance masse salariale (12 derniers mois) ───────────────────────
        $tendanceSalaires = collect(range(11, 0))->map(function ($n) {
            $date = now()->subMonths($n);
            $sum  = (int) BulletinPaie::where('statut', 'paye')
                ->whereYear('date_paiement', $date->year)
                ->whereMonth('date_paiement', $date->month)
                ->sum('net_a_payer');
            return ['label' => $date->locale('fr')->isoFormat('MMM YY'), 'montant' => $sum];
        })->values();

        // ── Répartition type contrat ──────────────────────────────────────────
        $nbEmploye     = User::where('type_contrat', 'employe')->count();
        $nbPrestataire = User::where('type_contrat', 'prestataire')->count();
        $nbTotal       = max(1, $nbEmploye + $nbPrestataire);

        $repartitionContrat = [
            ['label' => 'Employé',     'count' => $nbEmploye,     'pct' => round($nbEmploye     / $nbTotal * 100), 'color' => '#760078'],
            ['label' => 'Prestataire', 'count' => $nbPrestataire, 'pct' => round($nbPrestataire / $nbTotal * 100), 'color' => '#7677B7'],
        ];

        // ── Bulletins créés par mois (6 mois) ────────────────────────────────
        $bulletinsMensuels = collect(range(5, 0))->map(function ($n) {
            $date  = now()->subMonths($n);
            $count = BulletinPaie::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            return ['label' => $date->locale('fr')->isoFormat('MMM'), 'count' => $count];
        })->values();

        // ── Effectifs par département (affectations actives) ──────────────────
        $effectifsParDept = Departement::orderBy('nom')->get()->map(function ($dept) {
            $posteIds = $dept->postes()->pluck('id');
            $count    = Affectation::whereIn('poste_id', $posteIds)
                ->whereNull('date_fin')
                ->distinct('user_id')
                ->count('user_id');
            return ['dept' => $dept->nom, 'count' => $count];
        })->filter(fn ($d) => $d['count'] > 0)->values();

        // ── Répartition Homme / Femme ─────────────────────────────────────────
        $nbHomme = User::whereIn('sexe', ['M', 'Homme', 'homme', 'masculin'])->count();
        $nbFemme = User::whereIn('sexe', ['F', 'Femme', 'femme', 'féminin', 'feminin'])->count();
        $repartitionGenre = [];
        if ($nbHomme > 0) $repartitionGenre[] = ['label' => 'Hommes', 'count' => $nbHomme, 'color' => '#7677B7'];
        if ($nbFemme > 0) $repartitionGenre[] = ['label' => 'Femmes', 'count' => $nbFemme, 'color' => '#e91e8c'];
        if (empty($repartitionGenre)) {
            $repartitionGenre[] = ['label' => 'Non renseigné', 'count' => User::count(), 'color' => '#94a3b8'];
        }

        // ── Congés par mois (6 derniers mois) ────────────────────────────────
        $congesParMois = collect(range(5, 0))->map(function ($n) {
            $date  = now()->subMonths($n);
            $count = Conge::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            return ['label' => $date->locale('fr')->isoFormat('MMM'), 'count' => $count];
        })->values();

        // ── Absences par mois (6 derniers mois) ──────────────────────────────
        $absencesParMois = collect(range(5, 0))->map(function ($n) use ($idAbsent) {
            $date  = now()->subMonths($n);
            $count = Suivie::when($idAbsent, fn ($q) => $q->where('type_suivie_id', $idAbsent))
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->count();
            return ['label' => $date->locale('fr')->isoFormat('MMM'), 'count' => $count];
        })->values();

        // ── Recrutements par mois (6 derniers mois) ───────────────────────────
        $recrutementsParMois = collect(range(5, 0))->map(function ($n) {
            $date  = now()->subMonths($n);
            $count = User::whereYear('date_embauche', $date->year)
                ->whereMonth('date_embauche', $date->month)
                ->count();
            return ['label' => $date->locale('fr')->isoFormat('MMM'), 'count' => $count];
        })->values();

        // ── Congés en attente (5 plus récents) ───────────────────────────────
        $congesPending = Conge::with('user')
            ->where('statut', 'pending')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'id'         => $c->id,
                'employe'    => trim(($c->user?->nom ?? '') . ' ' . ($c->user?->prenom ?? '')),
                'date_debut' => $c->date_debut?->format('d/m/Y'),
                'date_fin'   => $c->date_fin?->format('d/m/Y'),
                'depuisLe'   => $c->created_at->locale('fr')->diffForHumans(),
            ]);

        // ── Activité récente ──────────────────────────────────────────────────
        $activites = collect();

        Conge::with('user')->orderByDesc('created_at')->take(5)->get()->each(function ($c) use (&$activites) {
            $activites->push([
                'ev'     => 'Congé — ' . ($c->user?->nom ?? '?') . ' ' . ($c->user?->prenom ?? ''),
                'module' => 'Congés',
                'statut' => match ($c->statut) { 'approuve' => 'Approuvé', 'refuse' => 'Refusé', default => 'En attente' },
                'color'  => match ($c->statut) { 'approuve' => 'emerald', 'refuse' => 'rose', default => 'amber' },
                'ts'     => $c->created_at,
                'date'   => $c->created_at->locale('fr')->diffForHumans(),
            ]);
        });

        BulletinPaie::with('user')->orderByDesc('created_at')->take(5)->get()->each(function ($b) use (&$activites) {
            $activites->push([
                'ev'     => 'Bulletin — ' . ($b->user?->nom ?? '?') . ' ' . ($b->user?->prenom ?? ''),
                'module' => 'Bulletins de paie',
                'statut' => match ($b->statut) { 'valide' => 'Validé', 'paye' => 'Payé', default => 'Brouillon' },
                'color'  => match ($b->statut) { 'valide' => 'blue', 'paye' => 'emerald', default => 'slate' },
                'ts'     => $b->created_at,
                'date'   => $b->created_at->locale('fr')->diffForHumans(),
            ]);
        });

        $activites = $activites
            ->sortByDesc('ts')
            ->take(8)
            ->map(fn ($a) => collect($a)->except('ts')->toArray())
            ->values();

        return Inertia::render('Dashboard', [
            'kpis'                => $kpis,
            'presenceToday'       => $presenceToday,
            'tendanceSalaires'    => $tendanceSalaires,
            'repartitionContrat'  => $repartitionContrat,
            'bulletinsMensuels'   => $bulletinsMensuels,
            'effectifsParDept'    => $effectifsParDept,
            'repartitionGenre'    => $repartitionGenre,
            'congesParMois'       => $congesParMois,
            'absencesParMois'     => $absencesParMois,
            'recrutementsParMois' => $recrutementsParMois,
            'congesPending'       => $congesPending,
            'activites'           => $activites,
        ]);
    }
}
