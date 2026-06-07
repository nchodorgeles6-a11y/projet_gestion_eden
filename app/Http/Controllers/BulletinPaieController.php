<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulletinPaie;
use App\Models\Departement;
use App\Models\User;
use App\Models\Suivie;
use App\Models\Transaction;
use App\Mail\BulletinPaye as BulletinPayeMail;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BulletinPaieController extends Controller
{
    public function index()
    {
        $bulletins = BulletinPaie::with('user')->orderByDesc('created_at')->paginate(15);
        return Inertia::render('BulletinsPaie/Index', ['bulletins' => $bulletins]);
    }

    public function create(Request $request)
    {
        $userId  = $request->query('user_id');
        $moisNom = $request->query('mois', $this->moisActuel());
        $annee   = (int) $request->query('annee', date('Y'));

        $user  = $userId ? User::with('affectations.poste.departement')->find($userId) : null;
        $users = User::orderBy('nom')->get(['id', 'nom', 'prenom', 'salaire_base',
            'prime_transport', 'prime_logement', 'prime_fonction', 'prime_rendement',
            'prime_panier', 'bonus_annuel', 'cnps', 'assurance_maladie']);

        $resume = ($user && $moisNom && $annee)
            ? $this->calculerDeductions($user, $this->nomMoisToNum($moisNom), $annee)
            : null;

        return Inertia::render('BulletinsPaie/Create', [
            'users'          => $users,
            'selectedUser'   => $user,
            'resumeAbsences' => $resume,
        ]);
    }

    // ── Calcul automatique des déductions d'absences ─────────────────────────

    private function moisActuel(): string
    {
        $noms = ['Janvier','Février','Mars','Avril','Mai','Juin',
                 'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        return $noms[(int) date('m') - 1];
    }

    private function nomMoisToNum(string $nom): int
    {
        $map = [
            'Janvier'=>1,'Février'=>2,'Mars'=>3,'Avril'=>4,
            'Mai'=>5,'Juin'=>6,'Juillet'=>7,'Août'=>8,
            'Septembre'=>9,'Octobre'=>10,'Novembre'=>11,'Décembre'=>12,
        ];
        return $map[$nom] ?? (int) date('m');
    }

    private function calculerDeductions(User $user, int $mois, int $annee): array
    {
        $suivies = Suivie::with(['typeSuivie', 'motif', 'conge.motif'])
            ->where('user_id', $user->id)
            ->whereYear('date', $annee)
            ->whereMonth('date', $mois)
            ->get();

        $joursOuvrables = 26;
        $salaireBase    = (float) $user->salaire_base;
        $salaireJour    = $salaireBase > 0 ? $salaireBase / $joursOuvrables : 0;
        $salaireHeure   = $salaireJour / 8;

        $absencesDeduites  = 0;
        $absencesPayees    = 0;
        $retardsNonJust    = 0;
        $retardsJustifies  = 0;
        $detail            = [];

        foreach ($suivies as $s) {
            $typeNom   = strtolower($s->typeSuivie?->nom ?? '');
            $motifType = $s->motif?->type ?? '';
            $motifNom  = $s->motif?->nom  ?? '';
            $justifiee = $s->justifiee;
            $dateStr   = $s->date?->format('d/m') ?? '—';
            $congeMotif = $s->conge?->motif?->nom ?? '';

            if (str_contains($typeNom, 'abs')) {
                // Congé sans solde → déduction même si justifiée
                if (str_contains(strtolower($congeMotif), 'sans solde')
                    || str_contains(strtolower($motifNom), 'sans solde')) {
                    $absencesDeduites++;
                    $detail[] = ['date' => $dateStr, 'type' => 'Absence', 'motif' => $motifNom ?: $congeMotif ?: 'Congé sans solde', 'impact' => 'Déduit', 'couleur' => 'rose'];
                }
                // Absence non justifiée → déduction
                elseif (!$justifiee) {
                    $absencesDeduites++;
                    $detail[] = ['date' => $dateStr, 'type' => 'Absence', 'motif' => $motifNom ?: 'Non justifiée', 'impact' => 'Déduit', 'couleur' => 'rose'];
                }
                // Congé payé, permission, maladie → pas de déduction
                else {
                    $absencesPayees++;
                    $label = $congeMotif ?: $motifNom ?: 'Justifiée';
                    $detail[] = ['date' => $dateStr, 'type' => 'Absence', 'motif' => $label, 'impact' => 'Non déduit', 'couleur' => 'emerald'];
                }
            } elseif (str_contains($typeNom, 'ret')) {
                if (!$justifiee) {
                    $retardsNonJust++;
                    $detail[] = ['date' => $dateStr, 'type' => 'Retard', 'motif' => $motifNom ?: 'Non justifié', 'impact' => '−1h', 'couleur' => 'amber'];
                } else {
                    $retardsJustifies++;
                    $detail[] = ['date' => $dateStr, 'type' => 'Retard', 'motif' => $motifNom ?: 'Justifié', 'impact' => 'Non déduit', 'couleur' => 'emerald'];
                }
            }
        }

        $deductionAbsences = (int) round($absencesDeduites * $salaireJour);
        $deductionRetards  = (int) round($retardsNonJust  * $salaireHeure); // 1h/retard
        $totalDeduction    = $deductionAbsences + $deductionRetards;

        return [
            'mois'                    => $mois,
            'annee'                   => $annee,
            'jours_ouvrables'         => $joursOuvrables,
            'salaire_journalier'      => (int) round($salaireJour),
            'salaire_horaire'         => (int) round($salaireHeure),
            'absences_deduites'       => $absencesDeduites,
            'absences_payees'         => $absencesPayees,
            'retards_non_justifies'   => $retardsNonJust,
            'retards_justifies'       => $retardsJustifies,
            'deduction_absences'      => $deductionAbsences,
            'deduction_retards'       => $deductionRetards,
            'total_deduction'         => $totalDeduction,
            'detail'                  => $detail,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'mois'         => 'required|string',
            'annee'        => 'required|integer',
            'salaire_base' => 'required|numeric|min:0',
        ]);

        $montantHeuresSup = ($request->heures_sup ?? 0) * ($request->taux_heures_sup ?? 0);

        $salaireBrut = collect([
            $request->salaire_base,
            $request->prime_transport ?? 0,
            $request->prime_logement ?? 0,
            $request->prime_fonction ?? 0,
            $request->prime_rendement ?? 0,
            $request->prime_panier ?? 0,
            $request->bonus_annuel ?? 0,
            $montantHeuresSup,
            $request->avantages_nature_montant ?? 0,
        ])->sum();

        $totalRetenues = collect([
            $request->cnps_salarie ?? 0,
            $request->assurance_maladie_salarie ?? 0,
            $request->igr ?? 0,
            $request->is_salaire ?? 0,
            $request->avance_salaire ?? 0,
            $request->pret_entreprise ?? 0,
            $request->autres_retenues ?? 0,
        ])->sum();

        $netAPayer = $salaireBrut - $totalRetenues;

        $coutTotalEmployeur = $salaireBrut + collect([
            $request->cnps_employeur ?? 0,
            $request->accident_travail ?? 0,
            $request->prestations_familiales ?? 0,
            $request->formation_professionnelle ?? 0,
        ])->sum();

        BulletinPaie::create([
            'id'                       => Str::uuid(),
            'reference'                => 'BP-' . date('Ym') . '-' . strtoupper(Str::random(6)),
            'user_id'                  => $request->user_id,
            'mois'                     => $request->mois,
            'annee'                    => $request->annee,
            'salaire_base'             => $request->salaire_base,
            'prime_transport'          => $request->prime_transport ?? 0,
            'prime_logement'           => $request->prime_logement ?? 0,
            'prime_fonction'           => $request->prime_fonction ?? 0,
            'prime_rendement'          => $request->prime_rendement ?? 0,
            'prime_panier'             => $request->prime_panier ?? 0,
            'bonus_annuel'             => $request->bonus_annuel ?? 0,
            'heures_sup'               => $request->heures_sup ?? 0,
            'taux_heures_sup'          => $request->taux_heures_sup ?? 0,
            'avantages_nature_montant' => $request->avantages_nature_montant ?? 0,
            'salaire_brut'             => $salaireBrut,
            'cnps_salarie'             => $request->cnps_salarie ?? 0,
            'assurance_maladie_salarie'=> $request->assurance_maladie_salarie ?? 0,
            'igr'                      => $request->igr ?? 0,
            'is_salaire'               => $request->is_salaire ?? 0,
            'avance_salaire'           => $request->avance_salaire ?? 0,
            'pret_entreprise'          => $request->pret_entreprise ?? 0,
            'autres_retenues'          => $request->autres_retenues ?? 0,
            'total_retenues'           => $totalRetenues,
            'net_a_payer'              => $netAPayer,
            'cnps_employeur'           => $request->cnps_employeur ?? 0,
            'accident_travail'         => $request->accident_travail ?? 0,
            'prestations_familiales'   => $request->prestations_familiales ?? 0,
            'formation_professionnelle'=> $request->formation_professionnelle ?? 0,
            'cout_total_employeur'     => $coutTotalEmployeur,
            'mode_paiement'            => $request->mode_paiement ?? 'virement',
            'date_paiement'            => $request->date_paiement,
            'statut'                   => $request->statut ?? 'brouillon',
        ]);

        return redirect()->route('bulletins-paie.index');
    }

    public function show(BulletinPaie $bulletinsPaie)
    {
        $bulletinsPaie->load('user.affectations.poste.departement');
        return Inertia::render('BulletinsPaie/Show', ['bulletin' => $bulletinsPaie]);
    }

    public function print(BulletinPaie $bulletinsPaie)
    {
        $bulletinsPaie->load('user.affectations.poste.departement');

        $affectation = $bulletinsPaie->user?->affectations?->sortByDesc('created_at')->first();
        $poste = $affectation?->poste?->nom ?? '—';
        $dept  = $affectation?->poste?->departement?->nom ?? '—';

        return response()->view('bulletins.print', [
            'bulletin' => $bulletinsPaie,
            'poste'    => $poste,
            'dept'     => $dept,
        ]);
    }

    public function edit(BulletinPaie $bulletinsPaie)
    {
        $bulletinsPaie->load('user.affectations.poste.departement');
        $users = User::orderBy('nom')->get(['id', 'nom', 'prenom']);
        return Inertia::render('BulletinsPaie/Edit', [
            'bulletin' => $bulletinsPaie,
            'users'    => $users,
        ]);
    }

    public function update(Request $request, BulletinPaie $bulletinsPaie)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'mois'         => 'required|string',
            'annee'        => 'required|integer',
            'salaire_base' => 'required|numeric|min:0',
        ]);

        $montantHeuresSup = ($request->heures_sup ?? 0) * ($request->taux_heures_sup ?? 0);

        $salaireBrut = collect([
            $request->salaire_base,
            $request->prime_transport ?? 0,
            $request->prime_logement ?? 0,
            $request->prime_fonction ?? 0,
            $request->prime_rendement ?? 0,
            $request->prime_panier ?? 0,
            $request->bonus_annuel ?? 0,
            $montantHeuresSup,
            $request->avantages_nature_montant ?? 0,
        ])->sum();

        $totalRetenues = collect([
            $request->cnps_salarie ?? 0,
            $request->assurance_maladie_salarie ?? 0,
            $request->igr ?? 0,
            $request->is_salaire ?? 0,
            $request->avance_salaire ?? 0,
            $request->pret_entreprise ?? 0,
            $request->autres_retenues ?? 0,
        ])->sum();

        $coutTotalEmployeur = $salaireBrut + collect([
            $request->cnps_employeur ?? 0,
            $request->accident_travail ?? 0,
            $request->prestations_familiales ?? 0,
            $request->formation_professionnelle ?? 0,
        ])->sum();

        $bulletinsPaie->update([
            'user_id'                  => $request->user_id,
            'mois'                     => $request->mois,
            'annee'                    => $request->annee,
            'salaire_base'             => $request->salaire_base,
            'prime_transport'          => $request->prime_transport ?? 0,
            'prime_logement'           => $request->prime_logement ?? 0,
            'prime_fonction'           => $request->prime_fonction ?? 0,
            'prime_rendement'          => $request->prime_rendement ?? 0,
            'prime_panier'             => $request->prime_panier ?? 0,
            'bonus_annuel'             => $request->bonus_annuel ?? 0,
            'heures_sup'               => $request->heures_sup ?? 0,
            'taux_heures_sup'          => $request->taux_heures_sup ?? 0,
            'avantages_nature_montant' => $request->avantages_nature_montant ?? 0,
            'salaire_brut'             => $salaireBrut,
            'cnps_salarie'             => $request->cnps_salarie ?? 0,
            'assurance_maladie_salarie'=> $request->assurance_maladie_salarie ?? 0,
            'igr'                      => $request->igr ?? 0,
            'is_salaire'               => $request->is_salaire ?? 0,
            'avance_salaire'           => $request->avance_salaire ?? 0,
            'pret_entreprise'          => $request->pret_entreprise ?? 0,
            'autres_retenues'          => $request->autres_retenues ?? 0,
            'total_retenues'           => $totalRetenues,
            'net_a_payer'              => $salaireBrut - $totalRetenues,
            'cnps_employeur'           => $request->cnps_employeur ?? 0,
            'accident_travail'         => $request->accident_travail ?? 0,
            'prestations_familiales'   => $request->prestations_familiales ?? 0,
            'formation_professionnelle'=> $request->formation_professionnelle ?? 0,
            'cout_total_employeur'     => $coutTotalEmployeur,
            'mode_paiement'            => $request->mode_paiement ?? 'virement',
            'date_paiement'            => $request->date_paiement,
            'statut'                   => $request->statut ?? $bulletinsPaie->statut,
        ]);

        return redirect()->route('bulletins-paie.show', $bulletinsPaie->id)
            ->with('success', 'Bulletin mis à jour.');
    }

    public function destroy(BulletinPaie $bulletinsPaie)
    {
        $bulletinsPaie->delete();
        return redirect()->route('bulletins-paie.index');
    }

    public function valider(BulletinPaie $bulletin)
    {
        $bulletin->update(['statut' => 'valide']);
        return back();
    }

    public function payer(BulletinPaie $bulletin)
    {
        $bulletin->load('user.affectations.poste.departement');
        $bulletin->update(['statut' => 'paye', 'date_paiement' => now()]);

        // Auto-enregistrement de la dépense dans les transactions
        $dept = $bulletin->user?->affectations?->sortByDesc('created_at')->first()?->poste?->departement
            ?? Departement::where('nom', 'Ressources Humaines')->first()
            ?? Departement::first();

        if ($dept) {
            Transaction::create([
                'id'               => Str::uuid(),
                'departement_id'   => $dept->id,
                'user_id'          => $bulletin->user_id,
                'type'             => 'dépense',
                'montant'          => $bulletin->net_a_payer,
                'date_transaction' => now()->toDateString(),
                'source'           => 'salaire',
                'description'      => 'Salaires & Rémunérations — Salaire ' . $bulletin->mois . ' ' . $bulletin->annee
                                    . ' · ' . $bulletin->user?->nom . ' ' . $bulletin->user?->prenom,
                'meta'             => ['bulletin_id' => $bulletin->id, 'reference' => $bulletin->reference],
            ]);
        }

        if ($bulletin->user?->email) {
            Mail::to($bulletin->user->email)->queue(new BulletinPayeMail($bulletin));
        }

        return back();
    }

    public function export()
    {
        $bulletins = BulletinPaie::with('user')->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bulletins_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($bulletins) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($f, ['Référence','Employé','Mois','Année','Salaire brut','Net à payer','Statut','Date paiement'], ';');

            foreach ($bulletins as $b) {
                fputcsv($f, [
                    $b->reference,
                    ($b->user?->nom ?? '') . ' ' . ($b->user?->prenom ?? ''),
                    $b->mois,
                    $b->annee,
                    $b->salaire_brut,
                    $b->net_a_payer,
                    $b->statut,
                    $b->date_paiement?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
