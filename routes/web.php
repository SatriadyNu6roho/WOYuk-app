<?php

use Illuminate\Support\Facades\Route;
use App\Activity;

/*
|--------------------------------------------------------------------------
| Web Routes - WOYuk!
|--------------------------------------------------------------------------
*/

// 1. GUEST ROUTES (Hanya bisa diakses jika BELUM login)
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        $runningCount = Activity::where('status', 'completed')->where('sport_type', 'running')->count();
        $walkingCount = Activity::where('status', 'completed')->where('sport_type', 'walking')->count();
        $cyclingCount = Activity::where('status', 'completed')->where('sport_type', 'cycling')->count();

        return view('landing', compact('runningCount', 'walkingCount', 'cyclingCount'));
    })->name('landing');

    // Registration Routes
    Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('/register', 'Auth\RegisterController@register')->middleware('throttle:5,1');

    // Login Routes
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\LoginController@login')->middleware('throttle:5,1');
});

// 2. GOOGLE OAUTH ROUTES
Route::get('/auth/google', 'Auth\GoogleController@redirectToGoogle')->name('auth.google')->middleware('throttle:10,1');
Route::get('/auth/google/callback', 'Auth\GoogleController@handleGoogleCallback');

// PROTECTED ROUTES (Hanya bisa diakses jika SUDAH login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    Route::post('/target/update', 'DashboardController@updateTarget')->name('target.update');

    Route::get('/archive', 'ArchiveController@index')->name('archive.index');
    Route::get('/api/activity/{id}/route', 'ActivityApiController@getRoutePoints')->name('activity.route.points');
    
    Route::get('/profile', 'ProfileController@show')->name('profile.show');
    Route::get('/settings', 'ProfileController@editSettings')->name('settings.edit');
    Route::post('/settings/profile/update', 'ProfileController@updateProfile')->name('profile.update');
    Route::post('/settings/system/update', 'ProfileController@updateSettings')->name('settings.update');

    Route::post('/activity/start', 'ActivitySessionController@start')->name('activity.start');
    Route::get('/activity/test-start', 'ActivitySessionController@start')->name('activity.test');
    Route::post('/activity/{id}/stop', 'ActivitySessionController@stop')->name('activity.stop');
    Route::get('/activity/{id}/result', 'ActivitySessionController@result')->name('activity.result');

    Route::post('/api/activity/{id}/sync', 'ActivityApiController@syncPoints')->name('activity.sync');

    Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('/api/users/search', 'UserSearchController@search')->name('api.users.search');
    Route::get('/user/{public_id}', 'UserSearchController@showProfile')->name('user.profile.show');
});

// ADMIN ROUTES (Terproteksi Middleware Admin & Auth)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', 'AdminDashboardController@index')->name('admin.dashboard');
    Route::post('/users/create-admin', 'AdminDashboardController@storeAdmin')->name('admin.create');
});

// 4. FALLBACK ROUTE (/home bawaan laravel ke /dashboard)
Route::get('/home', function() {
    return redirect()->route('dashboard');
});