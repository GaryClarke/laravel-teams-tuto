<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\TeamMemberController;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::patch('teams/{team}/set-current', [TeamController::class, 'setCurrent'])->name('team.set-current');
    Route::get('team', [TeamController::class, 'edit'])->name('team.edit');
    Route::patch('team/{team}', [TeamController::class, 'update'])->name('team.update');
    Route::post('team/{team}/leave', [TeamController::class, 'leave'])->name('team.leave');

    Route::delete('team/{team}/members/{member}', [TeamMemberController::class, 'destroy'])
        ->name('team.members.destroy');

    Route::patch('team/{team}/members/{member}', [TeamMemberController::class, 'update'])
        ->name('team.members.update');

    Route::post('team/{team}/invites', [TeamInviteController::class, 'store'])
        ->name('team.invites.store');

    Route::delete('team/{team}/invites/{invite}', [TeamInviteController::class, 'destroy'])
        ->name('team.invites.destroy');

    Route::get('team/invites/accept', [TeamInviteController::class, 'accept'])
        ->name('team.invites.accept')
        ->middleware('signed');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
