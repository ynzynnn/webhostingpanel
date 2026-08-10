<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Display file browser view for a website.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $websites = $user->isAdmin()
            ? Website::with('user')->latest()->get()
            : Website::where('user_id', $user->id)->latest()->get();

        $selectedWebsiteId = $request->query('website_id');
        $selectedWebsite = $selectedWebsiteId 
            ? $websites->firstWhere('id', $selectedWebsiteId)
            : $websites->first();

        $path = $request->query('path', '');
        $fileData = [
            'current_path' => '',
            'items' => [],
        ];

        if ($selectedWebsite) {
            $fileData = $this->fileService->listDirectory($selectedWebsite, $path);
        }

        return view('files.index', compact('websites', 'selectedWebsite', 'fileData'));
    }

    /**
     * Create a new file inside selected directory.
     */
    public function createFile(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'filename' => 'required|string|max:255',
            'path' => 'nullable|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $success = $this->fileService->createFile($website, $request->path ?? '', $request->filename);

        return redirect()->route(
            auth()->user()->isAdmin() ? 'admin.files' : 'client.files',
            ['website_id' => $website->id, 'path' => $request->path ?? '']
        )->with($success ? 'success' : 'error', $success ? "File {$request->filename} berhasil dibuat!" : "Gagal membuat file {$request->filename}.");
    }

    /**
     * Create a new folder inside selected directory.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'foldername' => 'required|string|max:255',
            'path' => 'nullable|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $success = $this->fileService->createFolder($website, $request->path ?? '', $request->foldername);

        return redirect()->route(
            auth()->user()->isAdmin() ? 'admin.files' : 'client.files',
            ['website_id' => $website->id, 'path' => $request->path ?? '']
        )->with($success ? 'success' : 'error', $success ? "Folder {$request->foldername} berhasil dibuat!" : "Gagal membuat folder {$request->foldername}.");
    }

    /**
     * Upload files into selected directory.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'file' => 'required|file|max:51200', // 50MB max per file
            'path' => 'nullable|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $success = $this->fileService->uploadFile($website, $request->path ?? '', $request->file('file'));

        return redirect()->route(
            auth()->user()->isAdmin() ? 'admin.files' : 'client.files',
            ['website_id' => $website->id, 'path' => $request->path ?? '']
        )->with($success ? 'success' : 'error', $success ? "File berhasil diunggah!" : "Gagal mengunggah file.");
    }

    /**
     * Get content of a file for inline code editing.
     */
    public function getContent(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'filepath' => 'required|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $content = $this->fileService->getFileContent($website, $request->filepath);

        if ($content === null) {
            return response()->json(['success' => false, 'message' => 'Gagal membaca berkas.'], 404);
        }

        return response()->json(['success' => true, 'content' => $content, 'filepath' => $request->filepath]);
    }

    /**
     * Save updated code content to file.
     */
    public function saveContent(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'filepath' => 'required|string',
            'content' => 'nullable|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $content = $request->input('content', '');

        $success = $this->fileService->saveFileContent($website, $request->filepath, $content);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Perubahan berkas berhasil disimpan!' : 'Gagal menyimpan berkas.',
        ]);
    }

    /**
     * Delete a file or directory.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'filepath' => 'required|string',
            'current_path' => 'nullable|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $success = $this->fileService->deleteItem($website, $request->filepath);

        return redirect()->route(
            auth()->user()->isAdmin() ? 'admin.files' : 'client.files',
            ['website_id' => $website->id, 'path' => $request->current_path ?? '']
        )->with($success ? 'success' : 'error', $success ? "Item berhasil dihapus!" : "Gagal menghapus item.");
    }

    /**
     * Extract a ZIP file.
     */
    public function extractZip(Request $request)
    {
        $request->validate([
            'website_id' => 'required|exists:websites,id',
            'filepath' => 'required|string',
            'current_path' => 'nullable|string',
        ]);

        $website = Website::findOrFail($request->website_id);
        $this->authorizeWebsiteAccess($website);

        $success = $this->fileService->extractZip($website, $request->filepath);

        return redirect()->route(
            auth()->user()->isAdmin() ? 'admin.files' : 'client.files',
            ['website_id' => $website->id, 'path' => $request->current_path ?? '']
        )->with($success ? 'success' : 'error', $success ? "Arsip ZIP berhasil diekstrak!" : "Gagal mengekstrak ZIP.");
    }

    /**
     * Security check for website ownership.
     */
    protected function authorizeWebsiteAccess(Website $website): void
    {
        if (auth()->user()->isClient() && $website->user_id !== auth()->id()) {
            abort(403, 'Akses tidak sah.');
        }
    }
}
