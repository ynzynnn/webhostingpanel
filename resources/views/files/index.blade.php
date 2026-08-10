@extends('layouts.app')

@section('title', 'File Manager Web')
@section('page_title', 'File Manager Web')

@section('content')
<div class="space-y-6" x-data="{ 
    showFileModal: false, 
    showFolderModal: false, 
    showUploadModal: false, 
    showEditorModal: false, 
    editFilepath: '', 
    editContent: '', 
    saving: false, 
    saveMsg: '', 
    openEditor(filepath) {
        this.editFilepath = filepath;
        this.saveMsg = 'Memuat isi berkas...';
        this.showEditorModal = true;
        fetch('{{ route('files.get-content') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ website_id: '{{ $selectedWebsite ? $selectedWebsite->id : '' }}', filepath: filepath })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                this.editContent = data.content;
                this.saveMsg = '';
            } else {
                this.saveMsg = data.message || 'Gagal memuat berkas.';
            }
        })
        .catch(() => { this.saveMsg = 'Gagal memuat berkas.'; });
    },
    saveFile() {
        this.saving = true;
        this.saveMsg = 'Menyimpan...';
        fetch('{{ route('files.save-content') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ website_id: '{{ $selectedWebsite ? $selectedWebsite->id : '' }}', filepath: this.editFilepath, content: this.editContent })
        })
        .then(res => res.json())
        .then(data => {
            this.saving = false;
            this.saveMsg = data.message;
            if(data.success) setTimeout(() => { this.saveMsg = '' }, 2000);
        })
        .catch(() => { this.saving = false; this.saveMsg = 'Gagal menyimpan.'; });
    }
}">

    <!-- Top Action & Website Selector Bar -->
    <div class="card-box p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Website Selector Form -->
        <form method="GET" action="{{ auth()->user()->isAdmin() ? route('admin.files') : route('client.files') }}" class="flex items-center space-x-3">
            <span class="text-xs font-bold text-slate-700 font-mono uppercase tracking-wider shrink-0">Website:</span>
            <select name="website_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs font-bold font-mono focus:outline-none focus:border-brand-600">
                @forelse($websites as $w)
                    <option value="{{ $w->id }}" {{ $selectedWebsite && $selectedWebsite->id === $w->id ? 'selected' : '' }}>
                        {{ $w->domain_name }} ({{ $w->system_user }})
                    </option>
                @empty
                    <option value="" disabled selected>Belum ada website</option>
                @endforelse
            </select>
        </form>

        <!-- Actions Toolbar -->
        @if($selectedWebsite)
            <div class="flex items-center space-x-2">
                <button @click="showFileModal = true" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 transition-all font-mono flex items-center space-x-1">
                    <span>📄 File Baru</span>
                </button>
                <button @click="showFolderModal = true" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 transition-all font-mono flex items-center space-x-1">
                    <span>📁 Folder Baru</span>
                </button>
                <button @click="showUploadModal = true" class="px-3 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all font-mono flex items-center space-x-1">
                    <span>📤 Upload File</span>
                </button>
            </div>
        @endif
    </div>

    @if($selectedWebsite)
        <!-- Breadcrumb Path Bar -->
        <div class="card-box p-4 flex items-center space-x-2 text-xs font-mono bg-slate-900 text-slate-200 border-slate-800 shadow-md">
            <span class="text-slate-400">Path:</span>
            <a href="{{ route(auth()->user()->isAdmin() ? 'admin.files' : 'client.files', ['website_id' => $selectedWebsite->id, 'path' => '']) }}" class="text-brand-400 font-bold hover:underline">/public_html</a>

            @php
                $parts = array_filter(explode('/', $fileData['current_path']));
                $accumulated = '';
            @endphp

            @foreach($parts as $p)
                @php $accumulated .= '/' . $p; @endphp
                <span class="text-slate-600">/</span>
                <a href="{{ route(auth()->user()->isAdmin() ? 'admin.files' : 'client.files', ['website_id' => $selectedWebsite->id, 'path' => ltrim($accumulated, '/')]) }}" class="text-slate-200 font-bold hover:underline">
                    {{ $p }}
                </a>
            @endforeach
        </div>

        <!-- Files Table Card -->
        <div class="card-box overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider font-mono">
                            <th class="py-3 px-4">Nama File / Folder</th>
                            <th class="py-3 px-4">Ukuran</th>
                            <th class="py-3 px-4">Terakhir Diubah</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-mono">
                        <!-- Go Up Directory Row -->
                        @if($fileData['current_path'] !== '')
                            @php
                                $parentParts = explode('/', $fileData['current_path']);
                                array_pop($parentParts);
                                $parentPath = implode('/', $parentParts);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td colspan="4" class="py-3 px-4">
                                    <a href="{{ route(auth()->user()->isAdmin() ? 'admin.files' : 'client.files', ['website_id' => $selectedWebsite->id, 'path' => $parentPath]) }}" class="text-brand-600 font-bold hover:underline flex items-center space-x-2">
                                        <span>📁 .. (Ke Folder Induk)</span>
                                    </a>
                                </td>
                            </tr>
                        @endif

                        @forelse($fileData['items'] as $item)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4">
                                    @if($item['is_dir'])
                                        <a href="{{ route(auth()->user()->isAdmin() ? 'admin.files' : 'client.files', ['website_id' => $selectedWebsite->id, 'path' => $item['relative_path']]) }}" class="font-bold text-slate-900 hover:text-brand-600 flex items-center space-x-2">
                                            <span>📁 {{ $item['name'] }}</span>
                                        </a>
                                    @else
                                        <button @click="openEditor('{{ $item['relative_path'] }}')" class="font-semibold text-slate-800 hover:text-brand-600 flex items-center space-x-2 text-left">
                                            <span>{{ $item['is_zip'] ? '📦' : '📄' }} {{ $item['name'] }}</span>
                                        </button>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $item['size'] }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ $item['modified_at'] }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        @if(!$item['is_dir'])
                                            <button @click="openEditor('{{ $item['relative_path'] }}')" title="Edit Kode Inline" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold border border-slate-200">
                                                Edit
                                            </button>
                                        @endif

                                        @if(isset($item['is_zip']) && $item['is_zip'])
                                            <form method="POST" action="{{ route('files.extract-zip') }}" onsubmit="return confirm('Ekstrak arsip ZIP ini di direktori saat ini?')">
                                                @csrf
                                                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                                                <input type="hidden" name="filepath" value="{{ $item['relative_path'] }}">
                                                <input type="hidden" name="current_path" value="{{ $fileData['current_path'] }}">
                                                <button type="submit" title="Ekstrak ZIP" class="px-2 py-1 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold border border-emerald-200">
                                                    Ekstrak ZIP
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Hapus File/Folder -->
                                        <form method="POST" action="{{ route('files.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $item['name'] }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                                            <input type="hidden" name="filepath" value="{{ $item['relative_path'] }}">
                                            <input type="hidden" name="current_path" value="{{ $fileData['current_path'] }}">
                                            <button type="submit" title="Hapus" class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold border border-rose-200">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 font-mono">
                                    Folder ini kosong.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card-box p-12 text-center text-slate-400 font-mono">
            Pilih website di atas untuk membuka File Manager.
        </div>
    @endif

    <!-- Modal File Baru -->
    <div x-show="showFileModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60" @click="showFileModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-4 font-mono text-xs">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Buat File Baru</h3>
                <form method="POST" action="{{ route('files.create-file') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="website_id" value="{{ $selectedWebsite ? $selectedWebsite->id : '' }}">
                    <input type="hidden" name="path" value="{{ $fileData['current_path'] }}">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700">Nama File (dengan ekstensi):</label>
                        <input type="text" name="filename" required placeholder="contoh: index.php atau style.css" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showFileModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold">Buat File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Folder Baru -->
    <div x-show="showFolderModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60" @click="showFolderModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-4 font-mono text-xs">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Buat Folder Baru</h3>
                <form method="POST" action="{{ route('files.create-folder') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="website_id" value="{{ $selectedWebsite ? $selectedWebsite->id : '' }}">
                    <input type="hidden" name="path" value="{{ $fileData['current_path'] }}">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700">Nama Folder:</label>
                        <input type="text" name="foldername" required placeholder="contoh: assets atau images" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showFolderModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold">Buat Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Upload File -->
    <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60" @click="showUploadModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-4 font-mono text-xs">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Upload File ke Server</h3>
                <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="website_id" value="{{ $selectedWebsite ? $selectedWebsite->id : '' }}">
                    <input type="hidden" name="path" value="{{ $fileData['current_path'] }}">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700">Pilih Berkas File (Maksimal 50MB):</label>
                        <input type="file" name="file" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showUploadModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold">Upload Berkas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Code Editor Modal -->
    <div x-show="showEditorModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" @click="showEditorModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-slate-950 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-800 p-6 space-y-4 font-mono text-xs text-white">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="text-brand-400 font-bold">✏️ Edit Kode:</span>
                        <span class="text-slate-300" x-text="editFilepath"></span>
                    </div>
                    <span class="text-[11px] font-bold" :class="saving ? 'text-amber-400' : 'text-emerald-400'" x-text="saveMsg"></span>
                </div>

                <div class="space-y-1">
                    <textarea x-model="editContent" rows="18" class="w-full p-4 rounded-lg bg-slate-900 border border-slate-800 text-emerald-400 text-xs font-mono focus:outline-none focus:border-brand-500 leading-relaxed tracking-wide shadow-inner" placeholder="Tulis kode di sini..."></textarea>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                    <span class="text-[10px] text-slate-500">Tekan 'Simpan Kode' untuk memperbarui file secara langsung di VPS.</span>
                    <div class="flex space-x-2">
                        <button type="button" @click="showEditorModal = false" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition-all">Tutup</button>
                        <button type="button" @click="saveFile()" :disabled="saving" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all flex items-center space-x-1">
                            <span>💾 Simpan Kode</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
