<?php

namespace App\Http\Controllers;

use App\Models\DatabaseModel;
use App\Models\Website;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DatabaseController extends Controller
{
    public function __construct(
        protected DatabaseService $databaseService
    ) {}

    /**
     * Display list of databases.
     */
    public function index()
    {
        $query = DatabaseModel::with(['website', 'user'])->latest();

        if (auth()->user()->isClient()) {
            $query->where('user_id', auth()->id());
            $websites = Website::where('user_id', auth()->id())->latest()->get();
        } else {
            $websites = Website::latest()->get();
        }

        $databases = $query->get();

        return view('databases.index', compact('databases', 'websites'));
    }

    /**
     * Store a new MariaDB database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'db_name' => 'required|string|max:20|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:8',
            'website_id' => 'nullable|exists:websites,id',
        ], [
            'db_name.regex' => 'Nama database hanya boleh berisi huruf, angka, dan underscore (_).',
            'password.min' => 'Password database minimal 8 karakter.',
        ]);

        $user = auth()->user();
        $websiteId = $request->filled('website_id') ? $request->website_id : null;

        // Security check for client
        if ($websiteId && $user->isClient()) {
            $website = Website::findOrFail($websiteId);
            if ($website->user_id !== $user->id) {
                abort(403, 'Akses tidak sah.');
            }
        }

        $result = $this->databaseService->createDatabase($user, $request->db_name, $request->password, $websiteId);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Delete a database.
     */
    public function destroy(DatabaseModel $database)
    {
        if (auth()->user()->isClient() && $database->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }

        $result = $this->databaseService->deleteDatabase($database);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Export / Download database SQL backup dump.
     */
    public function export(DatabaseModel $database)
    {
        if (auth()->user()->isClient() && $database->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }

        $exportPath = $this->databaseService->exportDatabase($database);

        if ($exportPath && file_exists($exportPath)) {
            return response()->download($exportPath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal mengekspor database SQL backup.');
    }

    /**
     * Import .sql file to database.
     */
    public function import(Request $request, DatabaseModel $database)
    {
        if (auth()->user()->isClient() && $database->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }

        $request->validate([
            'sql_file' => 'required|file|max:51200',
        ], [
            'sql_file.required' => 'Pilih file .sql untuk diimpor.',
            'sql_file.max' => 'Ukuran file SQL maksimal 50MB.',
        ]);

        $filePath = $request->file('sql_file')->getRealPath();
        $result = $this->databaseService->importDatabase($database, $filePath);

        return redirect()->back()->with('success', $result['message']);
    }
}
