<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Departement;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'departement'])->orderByDesc('date_transaction');

        if ($request->filled('type'))    $query->where('type', $request->type);
        if ($request->filled('dept'))    $query->where('departement_id', $request->dept);
        if ($request->filled('source'))  $query->where('source', $request->source);

        $totaux = [
            'revenus'  => round((float) (clone $query)->where('type', 'revenu')->sum('montant'), 2),
            'depenses' => round((float) (clone $query)->where('type', 'dépense')->sum('montant'), 2),
        ];
        $totaux['solde'] = $totaux['revenus'] - $totaux['depenses'];

        $transactions = $query->paginate(15)->through(fn ($t) => [
            'id'               => $t->id,
            'type'             => $t->type,
            'montant'          => (float) $t->montant,
            'date_transaction' => $t->date_transaction?->format('d/m/Y'),
            'source'           => $t->source,
            'description'      => $t->description,
            'departement'      => $t->departement?->nom,
            'departement_id'   => $t->departement_id,
            'user'             => $t->user ? $t->user->nom . ' ' . $t->user->prenom : null,
        ]);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'totaux'       => $totaux,
            'departements' => Departement::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Transactions/Create', [
            'users'       => User::orderBy('nom')->get(['id', 'nom', 'prenom']),
            'departements'=> Departement::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'departement_id'   => 'required|exists:departements,id',
            'user_id'          => 'nullable|exists:users,id',
            'type'             => 'required|in:revenu,dépense',
            'montant'          => 'required|numeric|min:0',
            'description'      => 'required|string|max:500',
            'date_transaction' => 'required|date',
        ]);

        Transaction::create([
            'id'               => Str::uuid(),
            'departement_id'   => $request->departement_id,
            'user_id'          => $request->filled('user_id') ? $request->user_id : null,
            'type'             => $request->type,
            'montant'          => $request->montant,
            'description'      => $request->description,
            'date_transaction' => $request->date_transaction,
            'source'           => 'manuel',
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction enregistrée.');
    }

    public function destroy(string $id)
    {
        Transaction::findOrFail($id)->delete();
        return back()->with('success', 'Transaction supprimée.');
    }

    public function export()
    {
        $transactions = Transaction::with(['user', 'departement'])->orderByDesc('date_transaction')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transactions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($f, ['Date','Type','Source','Montant','Département','Description'], ';');

            foreach ($transactions as $t) {
                fputcsv($f, [
                    $t->date_transaction,
                    $t->type,
                    $t->source ?? '',
                    $t->montant,
                    $t->departement?->nom ?? '',
                    $t->description ?? '',
                ], ';');
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
