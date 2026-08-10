<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\SftpService;
use Illuminate\Http\Request;

class SftpController extends Controller
{
    public function __construct(
        protected SftpService $sftpService
    ) {}

    /**
     * Display SFTP access management view.
     */
    public function index()
    {
        $user = auth()->user();

        $websites = $user->isAdmin()
            ? Website::with('user')->latest()->get()
            : Website::where('user_id', $user->id)->latest()->get();

        return view('sftp.index', compact('websites'));
    }

    /**
     * Reset SFTP password for a website.
     */
    public function resetPassword(Request $request, Website $website)
    {
        $user = auth()->user();
        if ($user->isClient() && $website->user_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ], [
            'password.min' => 'Password SFTP minimal 8 karakter.',
        ]);

        $result = $this->sftpService->resetSftpPassword($website, $request->password);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
