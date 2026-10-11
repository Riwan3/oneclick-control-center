<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteConnectionController;
use App\Http\Controllers\ModuleObservatoryController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SiteConfigurationController;
use App\Http\Controllers\ConfigurationSnapshotController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\ReleaseChangelogController;
use Illuminate\Support\Facades\Route;

// GET /login serves the SPA shell (Vue Router renders Login.vue) and is
// named 'login' purely so Laravel's auth middleware has a valid redirect
// target if it's ever invoked for a non-JSON request. POST /login is the
// actual credential submission endpoint.
Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');

// Public Health Check Endpoint
Route::get('/api/health', HealthController::class);

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/user', [UserController::class, 'show']);

    // Live status of connected sites, filtered to the caller's effective site access.
    Route::get('/site-connections', [SiteConnectionController::class, 'index']);
    Route::get('/site-connections/{site}', [SiteConnectionController::class, 'show']);
    Route::get('/module-observatory/{moduleKey}', [ModuleObservatoryController::class, 'show']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::patch('/users/{user}', [UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);

        // Read-only catalogue listings for the site/module access
        // pickers in the user create/edit forms — see SiteController/
        // ModuleController for why these are not new catalogues.
        Route::get('/sites', [SiteController::class, 'index']);
        Route::get('/modules', [ModuleController::class, 'index']);

        // Site Configuration Foundation — persists per-site overrides
        // against the (frontend-only) Master Baseline Registry. Write
        // access restricted to super_admin only, matching the
        // site_configuration_manage capability (true only for
        // super_admin in config/roles.js).
        Route::post('/site-connections/{site}/check', [SiteConnectionController::class, 'check']);

        Route::get('/site-configurations', [SiteConfigurationController::class, 'index']);
        Route::patch('/site-configurations/{site}/modules', [SiteConfigurationController::class, 'toggleModule']);
        Route::patch('/site-configurations/{site}/menus', [SiteConfigurationController::class, 'toggleMenu']);

        // STEP 3R-1 — Configuration Snapshot Foundation. Read + create
        // only; no PATCH/PUT/DELETE route exists for a snapshot at all,
        // by design (see ConfigurationSnapshotController). {snapshot}
        // binds by uuid, scoped to its parent {site} so a snapshot from
        // one site can never be fetched through another site's URL.
        Route::get('/configuration-snapshots/{site}', [ConfigurationSnapshotController::class, 'index']);
        Route::post('/configuration-snapshots/{site}', [ConfigurationSnapshotController::class, 'store']);
        // Route param is named to match Site::configurationSnapshots()
        // — Laravel's scoped-binding resolution looks for a
        // relationship method named after this parameter.
        Route::get('/configuration-snapshots/{site}/{configurationSnapshot:uuid}', [ConfigurationSnapshotController::class, 'show'])
            ->scopeBindings();

        // STEP 3R-2 — Release Version + Changelog Foundation. Write
        // access restricted to super_admin only, via the new
        // release_manage capability (see config/roles.js) — no existing
        // "manage" capability fit this domain. Read access is granted
        // more broadly below (role:super_admin,management), matching
        // version_view.
        Route::post('/releases', [ReleaseController::class, 'store']);
        Route::patch('/releases/{release}', [ReleaseController::class, 'update']);

        // {changelog} binds by id, scoped to its parent {release} via
        // Release::changelogs() — Laravel's scoped-binding resolution
        // already matches this param name to that relation, no renaming
        // needed (unlike {configurationSnapshot} above).
        Route::post('/releases/{release}/changelogs', [ReleaseChangelogController::class, 'store']);
        Route::patch('/releases/{release}/changelogs/{changelog}', [ReleaseChangelogController::class, 'update'])
            ->scopeBindings();
        Route::delete('/releases/{release}/changelogs/{changelog}', [ReleaseChangelogController::class, 'destroy'])
            ->scopeBindings();
    });

    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/audit-logs', [AuditController::class, 'index']);

        // Read-only — matches the existing version_view capability
        // (also used by the Deployments page), which is true for
        // management as well as super_admin.
        Route::get('/releases', [ReleaseController::class, 'index']);
        Route::get('/releases/{release}', [ReleaseController::class, 'show']);
    });
});

// Future API Version 1 Route Group for Operational Modules
Route::middleware('auth')->prefix('api/v1')->group(function () {
    // Operational data modules (One-Manpower, One-Fleet, One-DMS, One-QCC) and external integrations
    // Example: Route::get('/manpower/roster', [ManpowerController::class, 'index']);
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

