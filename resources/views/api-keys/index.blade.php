@extends('layouts.app')

@section('title', 'Manajemen REST API & API Keys')
@section('page_title', 'Manajemen REST API & API Keys Integration')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, keyName: '' }">

    <!-- Header Actions -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Kredensial REST API SeptaPanel</h2>
            <p class="text-xs text-slate-500 mt-0.5">Integrasikan SeptaPanel ke website utama, sistem billing WHMCS, atau aplikasi kustom Anda secara otomatis.</p>
        </div>

        <button @click="showModal = true" class="py-2.5 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Generate API Key Baru</span>
        </button>
    </div>

    <!-- Active API Keys Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider font-mono">
                        <th class="py-3 px-4">Nama Token / Kredensial</th>
                        <th class="py-3 px-4">API Token Key</th>
                        <th class="py-3 px-4">Terakhir Digunakan</th>
                        @if(auth()->user()->isAdmin())
                            <th class="py-3 px-4">Pemilik (User)</th>
                        @endif
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($apiKeys as $k)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $k->name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200 text-slate-800 font-mono text-[11px] select-all font-semibold">{{ $k->key }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $k->last_used_at ? $k->last_used_at->diffForHumans() : 'Belum Pernah' }}
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="py-3.5 px-4 text-slate-600 font-sans font-semibold">
                                    {{ $k->user ? $k->user->name : '-' }}
                                </td>
                            @endif
                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="{{ route('api-keys.destroy', $k) }}" onsubmit="return confirm('Hapus API Key {{ $k->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] border border-rose-200 transition-all font-bold">
                                        🗑️ Hapus Key
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 5 : 4 }}" class="py-8 text-center text-slate-400 font-mono">
                                Belum ada API Key dibuat. Klik "Generate API Key Baru" untuk mulai integrasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Documentation & Code Examples -->
    <div class="card-box p-6 space-y-6">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Dokumentasi & Contoh Kode Integrasi REST API</h3>
            <p class="text-xs text-slate-500 mt-1">Gunakan endpoint berikut untuk mengendalikan provisi website & client dari website utama Anda.</p>
        </div>

        <!-- Endpoints List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-mono text-xs">
            <div class="p-4 rounded-lg bg-slate-900 text-slate-200 space-y-2 border border-slate-800">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">POST /api/v1/websites</span>
                <p class="text-[11px] text-slate-400 font-sans">Provisi website baru secara otomatis.</p>
                <div class="text-[10px] text-slate-300 bg-slate-950 p-2 rounded overflow-x-auto">
                    { "domain_name": "clientdomain.com", "php_version": "8.3", "client_email": "user@gmail.com" }
                </div>
            </div>

            <div class="p-4 rounded-lg bg-slate-900 text-slate-200 space-y-2 border border-slate-800">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30 uppercase">GET /api/v1/websites</span>
                <p class="text-[11px] text-slate-400 font-sans">Ambil daftar & status seluruh website.</p>
                <div class="text-[10px] text-slate-300 bg-slate-950 p-2 rounded overflow-x-auto">
                    Header: X-API-Key: septa_xxx
                </div>
            </div>

            <div class="p-4 rounded-lg bg-slate-900 text-slate-200 space-y-2 border border-slate-800">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">POST /api/v1/clients</span>
                <p class="text-[11px] text-slate-400 font-sans">Buat akun client baru di SeptaPanel.</p>
                <div class="text-[10px] text-slate-300 bg-slate-950 p-2 rounded overflow-x-auto">
                    { "name": "Budi Client", "email": "budi@domain.com", "password": "Password123!" }
                </div>
            </div>

            <div class="p-4 rounded-lg bg-slate-900 text-slate-200 space-y-2 border border-slate-800">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30 uppercase">GET /api/v1/system/status</span>
                <p class="text-[11px] text-slate-400 font-sans">Monitoring beban CPU, RAM, & Disk server VPS.</p>
                <div class="text-[10px] text-slate-300 bg-slate-950 p-2 rounded overflow-x-auto">
                    Header: X-API-Key: septa_xxx
                </div>
            </div>
        </div>

        <!-- PHP Integration Code Sample -->
        <div class="space-y-2 font-mono text-xs">
            <h4 class="font-bold text-slate-800 font-sans text-xs">Contoh Kode PHP (Integrasi Website Utama):</h4>
            <pre class="p-4 rounded-lg bg-slate-950 text-emerald-400 text-[11px] overflow-x-auto border border-slate-800"><code>&lt;?php
$apiKey = "septa_YOUR_API_KEY_HERE";
$endpoint = "https://cp.septacloud.net/api/v1/websites";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-API-Key: {$apiKey}",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "domain_name" =&gt; "clientbaru.com",
    "php_version" =&gt; "8.3",
    "client_email" =&gt; "client@gmail.com"
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
</code></pre>
        </div>
    </div>

    <!-- Modal Generate API Key Baru -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-5 font-mono text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Generate API Key Baru</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('api-keys.store') }}" class="space-y-4 font-sans">
                    @csrf

                    <div class="space-y-1 font-mono">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Nama Kredensial / Website Utama</label>
                        <input type="text" name="name" x-model="keyName" required placeholder="contoh: Website Utama WHMCS / Billing System" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                    </div>

                    <div class="pt-3 flex justify-end space-x-2 font-mono">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Generate API Key</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
