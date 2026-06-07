<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Usercontroller;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\SuivieController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BulletinPaieController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MotifController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\FactureController;

// ── Page publique (login) ──────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canResetPassword' => Route::has('password.request'),
        'status'           => session('status'),
    ]);
});

require __DIR__.'/auth.php';

// ── Routes protégées ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ── Accessibles à tous les utilisateurs connectés ──────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Congés — tous les employés peuvent soumettre et consulter leurs congés
    Route::resource('conges', CongeController::class);
    Route::post('conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('conges.approuver')->middleware('role:admin,rh');
    Route::post('conges/{conge}/refuser',   [CongeController::class, 'refuser'])->name('conges.refuser')->middleware('role:admin,rh');
    Route::get('conges/{conge}/lettre',     [CongeController::class, 'printLettre'])->name('conges.lettre')->middleware('role:admin,rh');

    // ── Réservées aux RH et admin ───────────────────────────────────────────
    Route::middleware(['role:admin,rh'])->group(function () {

        // Structure RH
        Route::resource('departements', DepartementController::class);
        Route::resource('postes',       PosteController::class);
        Route::resource('affectations', AffectationController::class);

        // Présences et absences
        Route::post('suivies/changer-statut', [SuivieController::class, 'changerStatut'])->name('suivies.changer-statut');
        Route::resource('suivies', SuivieController::class);
        Route::post('permissions',               [PermissionController::class, 'store'])->name('permissions.store');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        // Paie
        Route::resource('bulletins-paie', BulletinPaieController::class);
        Route::post('bulletins-paie/{bulletin}/valider',  [BulletinPaieController::class, 'valider'])->name('bulletins-paie.valider');
        Route::post('bulletins-paie/{bulletin}/payer',    [BulletinPaieController::class, 'payer'])->name('bulletins-paie.payer');
        Route::get('bulletins-paie/{bulletinsPaie}/print', [BulletinPaieController::class, 'print'])->name('bulletins-paie.print');
        Route::resource('paiements', PaiementController::class);

        // Comptabilité
        Route::resource('transactions', TransactionController::class);
        Route::resource('factures', FactureController::class);
        Route::post('factures/{facture}/payer', [FactureController::class, 'payer'])->name('factures.payer');
    });

    // ── Réservées à l'admin uniquement ─────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {

        // Employés + accès système
        Route::post('users/{user}/autoriser-acces',        [Usercontroller::class, 'autoriserAcces'])->name('users.autoriser-acces');
        Route::post('users/{user}/retirer-acces',          [Usercontroller::class, 'retirerAcces'])->name('users.retirer-acces');
        Route::get('users/{user}/documents/contrat',       [Usercontroller::class, 'printContrat'])->name('users.contrat');
        Route::get('users/{user}/documents/attestation',   [Usercontroller::class, 'printAttestation'])->name('users.attestation');
        Route::get('users/{user}/documents/licenciement',  [Usercontroller::class, 'printLicenciement'])->name('users.licenciement');
        Route::resource('users', Usercontroller::class);

        // Rôles
        Route::resource('roles', RoleController::class);

        // Rapports
        Route::get('rapports/financiers', [RapportController::class, 'financiers'])->name('rapports.financiers');
        Route::get('rapports/rh',         [RapportController::class, 'rh'])->name('rapports.rh');
        Route::get('rapports/annuel',     [RapportController::class, 'annuel'])->name('rapports.annuel');
        Route::get('rapports/mensuel',    [RapportController::class, 'mensuel'])->name('rapports.mensuel');

        // Budgets prévisionnels
        Route::get('budgets',           [BudgetController::class, 'index'])->name('budgets.index');
        Route::post('budgets',          [BudgetController::class, 'store'])->name('budgets.store');
        Route::put('budgets/{budget}',  [BudgetController::class, 'update'])->name('budgets.update');
        Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

        // Exports CSV
        Route::get('users/export',        [Usercontroller::class,       'export'])->name('users.export');
        Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::get('bulletins-paie/export', [BulletinPaieController::class, 'export'])->name('bulletins-paie.export');

        // Motifs
        Route::resource('motifs', MotifController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});
