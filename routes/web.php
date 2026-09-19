<?php

use App\Http\Controllers\AdminFixtureController;
use App\Http\Controllers\AdminMasterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LiveMatchControlController;
use App\Http\Controllers\PublicTournamentController;
use Illuminate\Support\Facades\Route;

// --- PUBLIC FAN CENTER & LIVE TELEMETRY ---
Route::get('/', [PublicTournamentController::class, 'index'])->name('home');
Route::get('/matches/{id}', [PublicTournamentController::class, 'showMatch'])->name('matches.show');
Route::get('/api/matches/{id}/live', [PublicTournamentController::class, 'liveFeed'])->name('api.matches.live');
Route::get('/api/matches/live-score', [PublicTournamentController::class, 'allLiveScores'])->name('api.matches.live-score');
Route::get('/api/categories/{id}/standings', [PublicTournamentController::class, 'standingsFeed'])->name('api.categories.standings');

// --- AUTHENTICATION ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/login/quick/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
});
Route::get('/captcha', [AuthController::class, 'captcha'])->name('captcha');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// --- OPERATOR & ADMIN AREA ---
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Routes restricted to Administrator Turnamen
    Route::middleware('admin')->group(function () {
        // Dashboard & Master Data Management
        Route::get('/', [AdminMasterController::class, 'index'])->name('dashboard');

        // Categories
        Route::post('/categories', [AdminMasterController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}', [AdminMasterController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminMasterController::class, 'destroyCategory'])->name('categories.destroy');

        // Teams
        Route::post('/teams', [AdminMasterController::class, 'storeTeam'])->name('teams.store');
        Route::put('/teams/{id}', [AdminMasterController::class, 'updateTeam'])->name('teams.update');
        Route::delete('/teams/{id}', [AdminMasterController::class, 'destroyTeam'])->name('teams.destroy');

        // Players
        Route::post('/players', [AdminMasterController::class, 'storePlayer'])->name('players.store');
        Route::put('/players/{id}', [AdminMasterController::class, 'updatePlayer'])->name('players.update');
        Route::delete('/players/{id}', [AdminMasterController::class, 'destroyPlayer'])->name('players.destroy');

        // Venues (Master Lapangan)
        Route::post('/venues', [AdminMasterController::class, 'storeVenue'])->name('venues.store');
        Route::put('/venues/{id}', [AdminMasterController::class, 'updateVenue'])->name('venues.update');
        Route::delete('/venues/{id}', [AdminMasterController::class, 'destroyVenue'])->name('venues.destroy');

        // Operators (Wasit Meja)
        Route::post('/operators', [AdminMasterController::class, 'storeOperator'])->name('operators.store');
        Route::put('/operators/{id}', [AdminMasterController::class, 'updateOperator'])->name('operators.update');
        Route::delete('/operators/{id}', [AdminMasterController::class, 'destroyOperator'])->name('operators.destroy');

        // Referees (Wasit di Lapangan)
        Route::post('/referees', [AdminMasterController::class, 'storeReferee'])->name('referees.store');
        Route::put('/referees/{id}', [AdminMasterController::class, 'updateReferee'])->name('referees.update');
        Route::delete('/referees/{id}', [AdminMasterController::class, 'destroyReferee'])->name('referees.destroy');

        // Stages (Babak / Tahapan Turnamen)
        Route::post('/stages', [AdminMasterController::class, 'storeStage'])->name('stages.store');
        Route::put('/stages/{id}', [AdminMasterController::class, 'updateStage'])->name('stages.update');
        Route::delete('/stages/{id}', [AdminMasterController::class, 'destroyStage'])->name('stages.destroy');

        // Fixtures Mutations (Create, Update, Delete)
        Route::post('/fixtures', [AdminFixtureController::class, 'store'])->name('fixtures.store');
        Route::put('/fixtures/{id}', [AdminFixtureController::class, 'update'])->name('fixtures.update');
        Route::delete('/fixtures/{id}', [AdminFixtureController::class, 'destroy'])->name('fixtures.destroy');
    });

    // Routes accessible to both Admin and Operator (Wasit Meja)
    // Fixtures Schedule List
    Route::get('/fixtures', [AdminFixtureController::class, 'index'])->name('fixtures');

    // Live Match Control Room (Wasit Meja)
    Route::get('/matches/{id}/control', [LiveMatchControlController::class, 'show'])->name('matches.control');
    Route::post('/matches/{id}/status', [LiveMatchControlController::class, 'updateStatus'])->name('matches.status');
    Route::post('/matches/{id}/timer/toggle', [LiveMatchControlController::class, 'toggleTimer'])->name('matches.timer.toggle');
    Route::post('/matches/{id}/timer/set', [LiveMatchControlController::class, 'setTimer'])->name('matches.timer.set');
    Route::post('/matches/{id}/minute', [LiveMatchControlController::class, 'updateMinute'])->name('matches.minute');
    Route::post('/matches/{id}/score', [LiveMatchControlController::class, 'updateScore'])->name('matches.score');
    Route::post('/matches/{id}/penalty-score', [LiveMatchControlController::class, 'updatePenaltyScore'])->name('matches.penalty_score');
    Route::post('/matches/{id}/events', [LiveMatchControlController::class, 'addEvent'])->name('matches.events.store');
    Route::delete('/matches/{id}/events/{eventId}', [LiveMatchControlController::class, 'deleteEvent'])->name('matches.events.destroy');
});
