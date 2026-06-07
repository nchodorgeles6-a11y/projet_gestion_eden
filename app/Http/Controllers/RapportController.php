<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Suivie;
use App\Models\User;
use App\Models\Departement;
use App\Models\TypeSuivie;
use App\Models\BulletinPaie;
use App\Models\Conge;
use Inertia\Inertia;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function financiers(Request $request)
    {
        $annee   = (int) ($request->annee  ?? now()->year);
        $moisDeb = (int) ($request->mois_debut ?? 1);
        $moisFin = (int) ($request->mois_fin   ?? 12);

        $debut = Carbon::create($annee, $moisDeb, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $moisFin, 1)->endOfMonth();

        // Période N
        $transactions = Transaction::with(['departement', 'user'])
            ->whereBetween('date_transaction', [$debut, $fin])
            ->orderBy('date_transaction')
            ->get();

        // Période N-1 (même mois, année précédente)
        $debutNm1 = $debut->copy()->subYear();
        $finNm1   = $fin->copy()->subYear();
        $transNm1 = Transaction::whereBetween('date_transaction', [$debutNm1, $finNm1])->get();

        $departements = Departement::orderBy('nom')->get();

        // Agrégation par département
        $parDept = $departements->map(function ($dept) use ($transactions) {
            $txDept   = $transactions->where('departement_id', $dept->id);
            $revenus  = (float) $txDept->where('type', 'revenu')->sum('montant');
            $depenses = (float) $txDept->where('type', 'dépense')->sum('montant');
            return [
                'id'       => $dept->id,
                'nom'      => $dept->nom,
                'revenus'  => round($revenus, 2),
                'depenses' => round($depenses, 2),
                'solde'    => round($revenus - $depenses, 2),
                'count'    => $txDept->count(),
            ];
        });

        // Évolution mensuelle N
        $mensuel = collect(range($moisDeb, $moisFin))->map(function ($mois) use ($transactions, $transNm1, $annee) {
            $label   = Carbon::create($annee, $mois, 1)->locale('fr')->isoFormat('MMM');
            $txMois  = $transactions->filter(fn ($t) => $t->date_transaction->month === $mois);
            $txNm1   = $transNm1->filter(fn ($t) => $t->date_transaction->month === $mois);
            $rev     = round((float) $txMois->where('type', 'revenu')->sum('montant'), 2);
            $dep     = round((float) $txMois->where('type', 'dépense')->sum('montant'), 2);
            $revNm1  = round((float) $txNm1->where('type', 'revenu')->sum('montant'), 2);
            $depNm1  = round((float) $txNm1->where('type', 'dépense')->sum('montant'), 2);
            return compact('label', 'rev', 'dep', 'revNm1', 'depNm1') + ['mois' => $label];
        })->values();

        // Répartition par catégorie (dépenses)
        $parCategorie = $transactions->where('type', 'dépense')
            ->groupBy('categorie')
            ->map(fn ($g, $cat) => [
                'categorie' => $cat ?: 'Non catégorisé',
                'montant'   => round((float) $g->sum('montant'), 2),
                'count'     => $g->count(),
            ])
            ->sortByDesc('montant')
            ->values();

        $totalRevenus  = round((float) $transactions->where('type', 'revenu')->sum('montant'), 2);
        $totalDepenses = round((float) $transactions->where('type', 'dépense')->sum('montant'), 2);
        $totalRevNm1   = round((float) $transNm1->where('type', 'revenu')->sum('montant'), 2);
        $totalDepNm1   = round((float) $transNm1->where('type', 'dépense')->sum('montant'), 2);

        return Inertia::render('Rapports/Financiers', [
            'parDept'      => $parDept,
            'mensuel'      => $mensuel,
            'parCategorie' => $parCategorie,
            'transactions' => $transactions->map(fn ($t) => [
                'id'               => $t->id,
                'type'             => $t->type,
                'categorie'        => $t->categorie,
                'montant'          => (float) $t->montant,
                'date_transaction' => $t->date_transaction?->format('d/m/Y'),
                'description'      => $t->description,
                'source'           => $t->source,
                'departement'      => $t->departement?->nom,
                'user'             => $t->user ? $t->user->nom . ' ' . $t->user->prenom : null,
            ]),
            'totaux' => [
                'revenus'   => $totalRevenus,
                'depenses'  => $totalDepenses,
                'solde'     => round($totalRevenus - $totalDepenses, 2),
                'revNm1'    => $totalRevNm1,
                'depNm1'    => $totalDepNm1,
                'soldeNm1'  => round($totalRevNm1 - $totalDepNm1, 2),
                'anneeNm1'  => $annee - 1,
            ],
            'annees'  => range(now()->year - 3, now()->year + 1),
            'filtres' => compact('annee', 'moisDeb', 'moisFin'),
        ]);
    }

    // Rapport mensuel téléchargeable (impression navigateur)
    public function mensuel(Request $request)
    {
        $annee = (int) ($request->annee ?? now()->year);
        $mois  = (int) ($request->mois  ?? now()->month);

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = $debut->copy()->endOfMonth();

        $transactions = Transaction::with(['departement', 'user'])
            ->whereBetween('date_transaction', [$debut, $fin])
            ->orderBy('date_transaction')
            ->get();

        $departements = Departement::orderBy('nom')->get();

        $totalRevenus  = round((float) $transactions->where('type', 'revenu')->sum('montant'), 0);
        $totalDepenses = round((float) $transactions->where('type', 'dépense')->sum('montant'), 0);
        $soldeNet      = $totalRevenus - $totalDepenses;

        $parDept = $departements->map(function ($dept) use ($transactions) {
            $txDept   = $transactions->where('departement_id', $dept->id);
            $rev      = round((float) $txDept->where('type', 'revenu')->sum('montant'), 0);
            $dep      = round((float) $txDept->where('type', 'dépense')->sum('montant'), 0);
            return ['nom' => $dept->nom, 'revenus' => $rev, 'depenses' => $dep, 'solde' => $rev - $dep, 'nb' => $txDept->count()];
        })->filter(fn ($d) => $d['nb'] > 0)->values();

        $parCategorie = $transactions->where('type', 'dépense')
            ->groupBy('categorie')
            ->map(fn ($g, $cat) => ['categorie' => $cat ?: 'Non catégorisé', 'montant' => round((float) $g->sum('montant'), 0)])
            ->sortByDesc('montant')
            ->values();

        $masseSalariale = BulletinPaie::where('statut', 'paye')
            ->whereYear('date_paiement', $annee)->whereMonth('date_paiement', $mois)->sum('net_a_payer');

        $moisLabel = $debut->locale('fr')->isoFormat('MMMM YYYY');

        return view('rapports.mensuel', compact(
            'annee', 'mois', 'moisLabel',
            'totalRevenus', 'totalDepenses', 'soldeNet',
            'parDept', 'parCategorie', 'transactions', 'masseSalariale'
        ));
    }

    public function annuel(Request $request)
    {
        $annee = (int) ($request->annee ?? now()->year);
        $debut = Carbon::create($annee, 1, 1)->startOfYear();
        $fin   = Carbon::create($annee, 12, 31)->endOfYear();

        $transactions = Transaction::with(['departement', 'user'])
            ->whereBetween('date_transaction', [$debut, $fin])
            ->orderBy('date_transaction')
            ->get();

        $totalRevenus  = round((float) $transactions->where('type', 'revenu')->sum('montant'), 0);
        $totalDepenses = round((float) $transactions->where('type', 'dépense')->sum('montant'), 0);
        $soldeNet      = round($totalRevenus - $totalDepenses, 0);

        $mensuel = collect(range(1, 12))->map(function ($mois) use ($transactions, $annee) {
            $txMois    = $transactions->filter(fn ($t) => $t->date_transaction->month === $mois);
            $rev       = round((float) $txMois->where('type', 'revenu')->sum('montant'), 0);
            $dep       = round((float) $txMois->where('type', 'dépense')->sum('montant'), 0);
            return [
                'mois'     => Carbon::create($annee, $mois, 1)->locale('fr')->isoFormat('MMMM'),
                'revenus'  => $rev,
                'depenses' => $dep,
                'solde'    => $rev - $dep,
                'nb'       => $txMois->count(),
            ];
        });

        $departements = Departement::orderBy('nom')->get();
        $parDept = $departements->map(function ($dept) use ($transactions) {
            $txDept   = $transactions->where('departement_id', $dept->id);
            $rev      = round((float) $txDept->where('type', 'revenu')->sum('montant'), 0);
            $dep      = round((float) $txDept->where('type', 'dépense')->sum('montant'), 0);
            return ['nom' => $dept->nom, 'revenus' => $rev, 'depenses' => $dep, 'solde' => $rev - $dep, 'nb' => $txDept->count()];
        })->filter(fn ($d) => $d['nb'] > 0)->values();

        $nbEmployes        = User::count();
        $nbDepartements    = Departement::count();
        $masseSalariale    = BulletinPaie::where('statut', 'paye')->where('annee', $annee)->sum('net_a_payer');
        $nbCongesApprouves = Conge::where('statut', 'approuve')->whereYear('date_debut', $annee)->count();

        return view('rapports.annuel', compact(
            'annee', 'totalRevenus', 'totalDepenses', 'soldeNet',
            'mensuel', 'parDept', 'nbEmployes', 'nbDepartements',
            'masseSalariale', 'nbCongesApprouves'
        ));
    }

    public function rh(Request $request)
    {
        $annee   = (int) ($request->annee  ?? now()->year);
        $moisDeb = (int) ($request->mois_debut ?? 1);
        $moisFin = (int) ($request->mois_fin   ?? now()->month);

        $debut = Carbon::create($annee, $moisDeb, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $moisFin, 1)->endOfMonth();

        $typeSuivies = TypeSuivie::orderBy('nom')->get();
        $typePresent = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'pr'));
        $typeAbsent  = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'abs'));
        $typeRetard  = $typeSuivies->first(fn ($t) => str_contains(strtolower($t->nom), 'ret'));

        $users = User::with(['affectations.poste.departement'])->orderBy('nom')->get();

        $joursOuvres = 0;
        $cursor = $debut->copy();
        while ($cursor <= $fin) {
            if (!$cursor->isWeekend()) $joursOuvres++;
            $cursor->addDay();
        }

        $suivies = Suivie::with(['typeSuivie', 'motif'])
            ->whereBetween('date', [$debut, $fin])
            ->get()
            ->groupBy('user_id');

        $parEmploye = $users->map(function ($user) use ($suivies, $typeAbsent, $typeRetard, $joursOuvres) {
            $suivisUser = $suivies->get($user->id, collect());
            $absences   = $suivisUser->filter(fn ($s) => $s->type_suivie_id === $typeAbsent?->id)->count();
            $retards    = $suivisUser->filter(fn ($s) => $s->type_suivie_id === $typeRetard?->id)->count();
            $justifiees = $suivisUser->where('justifiee', true)->count();
            $presents   = max(0, $joursOuvres - $absences - $retards);
            $affectation = $user->affectations->sortByDesc('created_at')->first();
            return [
                'id'           => $user->id,
                'nom'          => $user->nom . ' ' . $user->prenom,
                'poste'        => $affectation?->poste?->nom ?? '—',
                'departement'  => $affectation?->poste?->departement?->nom ?? '—',
                'presents'     => $presents,
                'absences'     => $absences,
                'retards'      => $retards,
                'justifiees'   => $justifiees,
                'total'        => $joursOuvres,
                'taux_presence' => $joursOuvres > 0 ? round($presents / $joursOuvres * 100, 1) : 100,
            ];
        })->sortBy('taux_presence')->values();

        return Inertia::render('Rapports/RH', [
            'parEmploye'  => $parEmploye,
            'joursOuvres' => $joursOuvres,
            'annees'      => range(now()->year - 3, now()->year + 1),
            'filtres'     => compact('annee', 'moisDeb', 'moisFin'),
        ]);
    }
}
