<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\WebsiteController;
use App\Http\Middleware\EnsureRole;
use App\Models\AuditLog;
use App\Models\DatabaseModel;
use App\Models\Domain;
use App\Models\SslCertificate;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('client.dashboard');
    });

    // Account Management
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');

    // Website Shared Actions
    Route::get('/websites/create', [WebsiteController::class, 'create'])->name('websites.create');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::get('/websites/{website}', [WebsiteController::class, 'show'])->name('websites.show');
    Route::post('/websites/{website}/toggle-suspend', [WebsiteController::class, 'toggleSuspend'])->name('websites.toggle-suspend');
    Route::post('/websites/{website}/issue-ssl', [WebsiteController::class, 'issueSsl'])->name('websites.issue-ssl');
    Route::delete('/websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy');

    // Admin Group
    Route::middleware([EnsureRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/websites', [WebsiteController::class, 'index'])->name('websites');

        Route::get('/clients', function () {
            $clients = User::where('role', 'client')->latest()->get();
            return view('clients.index', compact('clients'));
        })->name('clients');

        Route::get('/audit-logs', function () {
            $auditLogs = AuditLog::with('user')->latest()->paginate(20);
            return view('audit-logs.index', compact('auditLogs'));
        })->name('audit-logs');
    });

    // Client Group
    Route::middleware([EnsureRole::class . ':client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/websites', [WebsiteController::class, 'index'])->name('websites');

        Route::get('/domains', function () {
            $domains = Domain::where('user_id', auth()->id())->latest()->get();
            return view('domains.index', compact('domains'));
        })->name('domains');

        Route::get('/databases', function () {
            $databases = DatabaseModel::where('user_id', auth()->id())->latest()->get();
            return view('databases.index', compact('databases'));
        })->name('databases');

        Route::get('/files', function () {
            $websites = Website::where('user_id', auth()->id())->latest()->get();
            return view('files.index', compact('websites'));
        })->name('files');

        Route::get('/ssl', function () {
            $sslCertificates = SslCertificate::where('user_id', auth()->id())->latest()->get();
            return view('ssl.index', compact('sslCertificates'));
        })->name('ssl');
    });
});
