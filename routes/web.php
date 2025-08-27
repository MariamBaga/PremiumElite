<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DossierRaccordementController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientImportController;
use App\Http\Controllers\TeamController;

    // routes/web.php (dans le group auth)
    use App\Http\Controllers\TicketController;

    use App\Http\Controllers\MapController;



/*
|--------------------------------------------------------------------------
| Public / Welcome
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('welcome'))->name('welcome');

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Dossiers
    |----------------------------------------------------------------------
    */
    Route::get('dossiers',                  [DossierRaccordementController::class,'index'])->name('dossiers.index');
    Route::get('dossiers/create',           [DossierRaccordementController::class,'create'])->name('dossiers.create');
    Route::post('dossiers',                 [DossierRaccordementController::class,'store'])->name('dossiers.store');
    Route::get('dossiers/{dossier}',        [DossierRaccordementController::class,'show'])->name('dossiers.show');
    Route::get('dossiers/{dossier}/edit',   [DossierRaccordementController::class,'edit'])->name('dossiers.edit');
    Route::put('dossiers/{dossier}',        [DossierRaccordementController::class,'update'])->name('dossiers.update');
    Route::delete('dossiers/{dossier}',     [DossierRaccordementController::class,'destroy'])->name('dossiers.destroy');

    Route::post('dossiers/{dossier}/assign',   [DossierRaccordementController::class,'assign'])->name('dossiers.assign');
    Route::post('dossiers/{dossier}/status',   [DossierRaccordementController::class,'updateStatus'])->name('dossiers.status');
    Route::post('dossiers/{dossier}/tentatives',    [DossierRaccordementController::class,'storeTentative'])->name('dossiers.tentatives.store');
    Route::post('dossiers/{dossier}/interventions', [DossierRaccordementController::class,'storeIntervention'])->name('dossiers.interventions.store');

    /*
    |----------------------------------------------------------------------
    | Clients
    |----------------------------------------------------------------------
    */
    Route::get('clients',                 [ClientController::class,'index'])->name('clients.index');
    Route::get('clients/create',          [ClientController::class,'create'])->name('clients.create');
    Route::post('clients',                [ClientController::class,'store'])->name('clients.store');
    Route::get('clients/{client}',        [ClientController::class,'show'])->name('clients.show');
    Route::get('clients/{client}/edit',   [ClientController::class,'edit'])->name('clients.edit');
    Route::put('clients/{client}',        [ClientController::class,'update'])->name('clients.update');
    Route::delete('clients/{client}',     [ClientController::class,'destroy'])->name('clients.destroy');

    // Actions additionnelles clients
    Route::delete('/clients/delete-all',  [ClientController::class, 'deleteAll'])->name('clients.deleteAll');
    Route::post('/clients/import',        [ClientImportController::class, 'store'])->name('clients.import');

    /*
    |----------------------------------------------------------------------
    | Teams (équipes) — explicites avec permissions Spatie
    |----------------------------------------------------------------------
    */
   // Teams (équipes) — routes spécifiques AVANT {team}
Route::get('teams/trash', [TeamController::class,'trash'])
->middleware('permission:teams.view')->name('teams.trash');

Route::get('teams/create', [TeamController::class,'create'])
->middleware('permission:teams.create')->name('teams.create');

Route::post('teams', [TeamController::class,'store'])
->middleware('permission:teams.create')->name('teams.store');

Route::get('teams', [TeamController::class,'index'])
->middleware('permission:teams.view')->name('teams.index');

Route::get('teams/{team}/edit', [TeamController::class,'edit'])
->middleware('permission:teams.update')->name('teams.edit');

Route::put('teams/{team}', [TeamController::class,'update'])
->middleware('permission:teams.update')->name('teams.update');

Route::delete('teams/{team}', [TeamController::class,'destroy'])
->middleware('permission:teams.delete')->name('teams.destroy');

Route::post('teams/{id}/restore', [TeamController::class,'restore'])
->middleware('permission:teams.restore')->name('teams.restore');

Route::delete('teams/{id}/force-delete', [TeamController::class,'forceDelete'])
->middleware('permission:teams.force-delete')->name('teams.force-delete');

// Chef & membres
Route::post('teams/{team}/lead/{user}', [TeamController::class,'setLead'])
->middleware('permission:teams.assign-lead')->name('teams.set-lead');

Route::post('teams/{team}/members', [TeamController::class,'addMember'])
->middleware('permission:teams.manage-members')->name('teams.members.add');

Route::delete('teams/{team}/members/{user}', [TeamController::class,'removeMember'])
->middleware('permission:teams.manage-members')->name('teams.members.remove');


// 🆕 Créer un nouvel utilisateur et l’ajouter à l’équipe
Route::post('teams/{team}/members/create-user', [TeamController::class,'createAndAddMember'])
    ->middleware('permission:teams.manage-members')->name('teams.members.create-user');

// En dernier : la show (paramétrée)
Route::get('teams/{team}', [TeamController::class,'show'])
->middleware('permission:teams.view')->name('teams.show');



Route::post('dossiers/{dossier}/assign-team',
    [DossierRaccordementController::class,'assignTeam']
)->middleware('permission:dossiers.assign')->name('dossiers.assign-team');
// (ou ->middleware('can:dossiers.assign') si tu n’utilises pas l’alias Spatie)

    /*
    |----------------------------------------------------------------------
    | Profile (Breeze/Fortify)
    |----------------------------------------------------------------------
    */
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});



use App\Http\Controllers\TeamInboxController;

Route::prefix('teams/{team}')->middleware(['auth','verified'])->group(function () {
    Route::get('inbox', [TeamInboxController::class,'index'])
        ->middleware('permission:teams.view')->name('teams.inbox');

    Route::post('inbox/{dossier}/close', [TeamInboxController::class,'close'])
        ->middleware('permission:teams.manage-members')->name('teams.inbox.close');

    Route::post('inbox/{dossier}/constraint', [TeamInboxController::class,'constraint'])
        ->middleware('permission:teams.manage-members')->name('teams.inbox.constraint');

    Route::post('inbox/{dossier}/reschedule', [TeamInboxController::class,'reschedule'])
        ->middleware('permission:teams.manage-members')->name('teams.inbox.reschedule');
});




//     Route::middleware(['auth','verified'])->group(function(){
//         Route::get('tickets', [TicketController::class,'index'])->name('tickets.index');
//         Route::get('tickets/create', [TicketController::class,'create'])->name('tickets.create');
//         Route::post('tickets', [TicketController::class,'store'])->name('tickets.store');
//         Route::get('tickets/{ticket}', [TicketController::class,'show'])->name('tickets.show');
//         Route::put('tickets/{ticket}', [TicketController::class,'update'])->name('tickets.update');
//     });






// Route::middleware(['auth','verified'])->group(function(){
//     Route::get('map', [MapController::class,'index'])->name('map.index');
//     Route::get('map/data', [MapController::class,'data'])->name('map.data'); // GeoJSON
// });
/*
|--------------------------------------------------------------------------
| Auth scaffolding
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
