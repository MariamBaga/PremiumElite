<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    DossierRaccordementController,
    ClientController,
    ClientImportController,
    DossierController,
    TeamController,
    TicketController,
    DashboardChefEquipeController,
    MapController,
    ExtensionController,
    DossierImportController,
    TeamInboxController
};
use App\Http\Controllers\RapportController;
use App\Http\Controllers\Ftth\{
    FicheController as FtthFicheController,
    CreateController as FtthCreateController,
    IndexController as FtthIndexController
};

use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('welcome'))->name('welcome');

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
    Route::get('/dashboard/chef', [DashboardChefEquipeController::class,'index'])->name('dashboard.chef');

    /*
    |--------------------------------------------------------------------------
    | Dossiers
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->group(function () {
        Route::get('/',                  [DossierRaccordementController::class,'index'])->name('dossiers.index');
        Route::get('/create',            [DossierRaccordementController::class,'create'])->name('dossiers.create');
        Route::post('/',                 [DossierRaccordementController::class,'store'])->name('dossiers.store');
        Route::get('/{dossier}',         [DossierRaccordementController::class,'show'])->name('dossiers.show');
        Route::get('/{dossier}/edit',    [DossierRaccordementController::class,'edit'])->name('dossiers.edit');
        Route::put('/{dossier}',         [DossierRaccordementController::class,'update'])->name('dossiers.update');
        Route::delete('/{dossier}',      [DossierRaccordementController::class,'destroy'])->name('dossiers.destroy');

        // Actions spécifiques
        Route::post('/{dossier}/assign',        [DossierRaccordementController::class,'assign'])->name('dossiers.assign');
        Route::post('/{dossier}/status',        [DossierRaccordementController::class,'updateStatus'])->name('dossiers.status');
        Route::post('/{dossier}/tentatives',    [DossierRaccordementController::class,'storeTentative'])->name('dossiers.tentatives.store');
        Route::post('/{dossier}/interventions', [DossierRaccordementController::class,'storeIntervention'])->name('dossiers.interventions.store');
        Route::post('/{dossier}/assign-team',   [DossierRaccordementController::class,'assignTeam'])->middleware('permission:dossiers.assign')->name('dossiers.assign-team');
        Route::post('/{dossier}/cloturer',      [DossierRaccordementController::class,'cloturer'])->middleware('permission:dossiers.update')->name('dossiers.cloturer');
        Route::post('/{dossier}/contrainte',    [DossierRaccordementController::class,'notifierContrainte'])->middleware('permission:dossiers.update')->name('dossiers.contrainte');

        // Génération de rapport & nouveau RDV
        Route::post('/rapport',      [DossierRaccordementController::class,'storeRapport'])->name('dossiers.rapport');
        Route::post('/nouveau_rdv',  [DossierRaccordementController::class,'storeNouveauRdv'])->name('dossiers.nouveau_rdv');
        Route::post(
            '/dossiers/depassement-lineaire',
            [DossierRaccordementController::class, 'storeDepassementLineaire']
        )->name('dossiers.depassement.store');

        // Injoignable
Route::post('/injoignable', [DossierRaccordementController::class,'storeInjoignable'])->name('dossiers.injoignable');

// PBO saturé
Route::post('/pbo_sature', [DossierRaccordementController::class,'storePboSature'])->name('dossiers.pbo_sature');

// Zone dépourvue
Route::post('/zone_depourvue', [DossierRaccordementController::class,'storeZoneDepourvue'])->name('dossiers.zone_depourvue');

Route::post('/realise', [DossierRaccordementController::class,'storeRealise'])->name('dossiers.realise');


Route::get('/dossiers/rapports-signes', [DossierRaccordementController::class, 'listRapportsSignes'])
    ->name('dossiers.rapports-signes');


// web.php
Route::get('/dossiers/rapports-rdv', [DossierRaccordementController::class, 'listRapportsRdv'])
    ->name('dossiers.rapports_rdv');
    Route::delete('/dossiers/{dossier}/delete-rapport', [DossierRaccordementController::class, 'deleteRapport'])->name('dossiers.deleteRapport');
    Route::delete('/dossiers/delete-all-rapports', [DossierRaccordementController::class, 'deleteAllRapports'])->name('dossiers.deleteAllRapports');

        // Import
        Route::post('/import', [DossierImportController::class,'import'])->name('dossiers.import');

       ;


    });
    Route::post('/dossiers/indisponible',
    [DossierRaccordementController::class, 'storeIndisponible']
)->name('dossiers.indisponible.store');

Route::delete('teams/{team}/dossiers/{dossier}', [TeamController::class, 'removeDossier'])
    ->name('teams.remove-dossier')
  ;
  // Ajouter un membre texte
Route::post('teams/{team}/members/add-text', [TeamController::class, 'addMember'])
->name('teams.add-member-text')
;

// Retirer un membre texte
Route::delete('teams/{team}/members/remove-text', [TeamController::class, 'removeMember'])
->name('teams.remove-member-text')
;



    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
    ->name('notifications.read');

Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');

    Route::get('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
    ->name('notifications.readAll');  // <-- c'est ce qu'il manquait


    Route::get('/rapports/activite', [RapportController::class, 'index'])->name('rapports.index');
    Route::post('/rapports/activite/export', [RapportController::class, 'export'])->name('rapports.export');

    Route::get('/dashboard/export', [DashboardController::class, 'exportExcel'])
    ->name('dashboard.export')
    ->middleware('role:coordinateur'); //
    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    */

    Route::delete('clients/delete-multiple', [ClientController::class, 'deleteMultiple'])
    ->name('clients.delete-multiple')
    ->middleware('can:clients.delete');

    Route::delete('/clients/purge-all', [ClientController::class, 'purgeAll'])
    ->name('clients.purgeAll')
    ->middleware('can:clients.delete');

    Route::prefix('clients')->group(function () {
        Route::get('/',          [ClientController::class,'index'])->name('clients.index');
        Route::get('/create',    [ClientController::class,'create'])->name('clients.create');
        Route::post('/',         [ClientController::class,'store'])->name('clients.store');


        Route::get('/dossiers/active',        [ClientController::class,'active'])->name('clients.dossiers.active');
    Route::get('/dossiers/realise',       [ClientController::class,'realise'])->name('clients.dossiers.realise');
    Route::get('/dossiers/nouveau_rdv',   [ClientController::class,'nouveauRdv'])->name('clients.dossiers.nouveau_rdv');

    Route::get('/en-appel', [ClientController::class, 'enAppel'])->name('clients.en_appel');
    Route::get('/injoignables', [ClientController::class, 'injoignables'])->name('clients.injoignables');

    Route::get('/dossiers/indisponible', [ClientController::class, 'indisponible'])->name('clients.dossiers.indisponible');
Route::get('/dossiers/pbo-sature', [ClientController::class, 'pboSature'])->name('clients.dossiers.pbo_sature');
Route::get('/dossiers/zone-depourvue', [ClientController::class, 'zoneDepourvue'])->name('clients.dossiers.zone_depourvue');
Route::get('/dossiers/en-equipe', [ClientController::class, 'enEquipe'])->name('clients.dossiers.en_equipe');

        Route::get('/{client}',  [ClientController::class,'show'])->name('clients.show');
        Route::get('/{client}/edit', [ClientController::class,'edit'])->name('clients.edit');
        Route::put('/{client}',  [ClientController::class,'update'])->name('clients.update');
        Route::delete('/{client}', [ClientController::class,'destroy'])->name('clients.destroy');
         // Routes par statut



        // Extra actions
        Route::delete('/delete-all', [ClientController::class,'deleteAll'])->name('clients.deleteAll');
        Route::post('/import',       [ClientImportController::class,'store'])->name('clients.import');
        Route::get('/data',          [ClientController::class,'data'])->name('clients.data');
    });

    /*
    |--------------------------------------------------------------------------
    | FTTH
    |--------------------------------------------------------------------------
    */
    Route::prefix('ftth')->group(function () {
        Route::get('/',       FtthIndexController::class)->name('ftth.index');
        Route::get('/create', FtthCreateController::class)->name('ftth.create');
        Route::get('/fiche',  FtthFicheController::class)->name('ftth.fiche');
    });

    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    */
    Route::prefix('teams')->group(function () {
        Route::get('/',          [TeamController::class,'index'])->middleware('permission:teams.view')->name('teams.index');
        Route::get('/trash',     [TeamController::class,'trash'])->middleware('permission:teams.view')->name('teams.trash');
        Route::get('/create',    [TeamController::class,'create'])->middleware('permission:teams.create')->name('teams.create');
        Route::post('/',         [TeamController::class,'store'])->middleware('permission:teams.create')->name('teams.store');

        Route::get('/{team}/edit', [TeamController::class,'edit'])->middleware('permission:teams.update')->name('teams.edit');
        Route::put('/{team}',      [TeamController::class,'update'])->middleware('permission:teams.update')->name('teams.update');
        Route::delete('/{team}',   [TeamController::class,'destroy'])->middleware('permission:teams.delete')->name('teams.destroy');

        // Restore & force delete
        Route::post('/{id}/restore',        [TeamController::class,'restore'])->middleware('permission:teams.restore')->name('teams.restore');
        Route::delete('/{id}/force-delete', [TeamController::class,'forceDelete'])->middleware('permission:teams.force-delete')->name('teams.force-delete');

        // Gestion des membres
        Route::post('/{team}/lead/{user}', [TeamController::class,'setLead'])->middleware('permission:teams.assign-lead')->name('teams.set-lead');
        Route::post('/{team}/members', [TeamController::class,'addMember'])->middleware('permission:teams.manage-members')->name('teams.members.add');
        Route::delete('/{team}/members/{user}', [TeamController::class,'removeMember'])->middleware('permission:teams.manage-members')->name('teams.members.remove');
        Route::post('/{team}/members/create-user', [TeamController::class,'createAndAddMember'])->middleware('permission:teams.manage-members')->name('teams.members.create-user');

        // Show
        Route::get('/{team}', [TeamController::class,'show'])->middleware('permission:teams.view')->name('teams.show');

        /*
        |--------------------------------------------------------------------------
        | Inbox équipe
        |--------------------------------------------------------------------------
        */
        Route::prefix('{team}/inbox')->group(function () {
            Route::get('/', [TeamInboxController::class,'index'])->name('teams.inbox');
            Route::post('/{dossier}/close', [TeamInboxController::class,'close'])->name('teams.inbox.close');
            Route::post('/{dossier}/constraint', [TeamInboxController::class,'constraint'])->name('teams.inbox.constraint');
            Route::post('/{dossier}/reschedule', [TeamInboxController::class,'reschedule'])->name('teams.inbox.reschedule');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Tickets
    |--------------------------------------------------------------------------
    */
    Route::prefix('tickets')->group(function () {
        Route::get('/',       [TicketController::class,'index'])->name('tickets.index');
        Route::get('/create', [TicketController::class,'create'])->name('tickets.create');
        Route::post('/',      [TicketController::class,'store'])->name('tickets.store');
        Route::get('/{ticket}', [TicketController::class,'show'])->name('tickets.show');
        Route::put('/{ticket}', [TicketController::class,'update'])->name('tickets.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Carte (Map)
    |--------------------------------------------------------------------------
    */
    Route::prefix('map')->group(function () {
        Route::get('/', [MapController::class,'index'])->name('map.index');
        Route::get('/data', [MapController::class,'data'])->name('map.data'); // GeoJSON
    });

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    */
    Route::resource('extensions', ExtensionController::class);

    /*
    |--------------------------------------------------------------------------
    | Profil
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class,'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class,'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class,'destroy'])->name('profile.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Superadmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified','role:superadmin|admin'])
    ->prefix('superadmin')
    ->group(function () {
        Route::resource('users', UserController::class)
            ->except('show')
            ->names('admin.users');
    });



/*
|--------------------------------------------------------------------------
| Auth scaffolding
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
