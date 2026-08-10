<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\FileController;
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

    // Domain Management Shared Actions
    Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
    Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
    Route::post('/domains/{domain}/check-dns', [DomainController::class, 'checkDns'])->name('domains.check-dns');
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

    // Database Management Shared Actions
    Route::get('/databases', [DatabaseController::class, 'index'])->name('databases.index');
    Route::post('/databases', [DatabaseController::class, 'store'])->name('databases.store');
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');

    // File Manager Shared Actions
    Route::get('/files', [FileController::class, 'index'])->name('files.index');
    Route::post('/files/create-file', [FileController::class, 'createFile'])->name('files.create-file');
    Route::post('/files/create-folder', [FileController::class, 'createFolder'])->name('files.create-folder');
    Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('/files/get-content', [FileController::class, 'getContent'])->name('files.get-content');
    Route::post('/files/save-content', [FileController::class, 'saveContent'])->name('files.save-content');
    Route::delete('/files/delete', [FileController::class, 'destroy'])->name('files.destroy');
    Route::post('/files/extract-zip', [FileController::class, 'extractZip'])->name('files.extract-zip');

    // Admin Group
    Route::middleware([EnsureRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/websites', [WebsiteController::class, 'index'])->name('websites');
        Route::get('/domains', [DomainController::class, 'index'])->name('domains');
        Route::get('/databases', [DatabaseController::class, 'index'])->name('databases');
        Route::get('/files', [FileController::class, 'index'])->name('files');

        Route::get('/ssl', function () {
            $sslCertificates = SslCertificate::with(['website', 'user'])->latest()->get();
            return view('ssl.index', compact('sslCertificates'));
        })->name('ssl');

        Route::get('/clients', function () {
            $clients = User::where('role', 'client')->latest()->get();
            return view('clients.index', compact('clients'));
        })->name('clients');
        Route::post('/clients', [AdminDashboardController::class, 'storeClient'])->name('clients.store');
        Route::put('/clients/{user}/quota', [AdminDashboardController::class, 'updateClientQuota'])->name('clients.quota');

        Route::get('/audit-logs', function () {
            $auditLogs = AuditLog::with('user')->latest()->paginate(20);
            return view('audit-logs.index', compact('auditLogs'));
        })->name('audit-logs');
    });

    // Client Group
    Route::middleware([EnsureRole::class . ':client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/websites', [WebsiteController::class, 'index'])->name('websites');
        Route::get('/domains', [DomainController::class, 'index'])->name('domains');
        Route::get('/databases', [DatabaseController::class, 'index'])->name('databases');
        Route::get('/files', [FileController::class, 'index'])->name('files');

        Route::get('/ssl', function () {
            $sslCertificates = SslCertificate::where('user_id', auth()->id())->latest()->get();
            return view('ssl.index', compact('sslCertificates'));
        })->name('ssl');
    });
});
