<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-800 selection:bg-blue-600 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SeptaPanel</title>
    
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .card-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease-in-out;
        }
        .card-box-hover:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }
    </style>
</head>
<body class="h-full font-sans bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row bg-slate-50">

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs md:hidden" x-cloak></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform transition-transform duration-200 ease-in-out md:translate-x-0 md:static md:inset-0 flex flex-col justify-between shadow-xs">
            
            <div>
                <!-- Brand Header -->
                <div class="h-16 px-6 flex items-center justify-between border-b border-slate-100">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            S
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-lg tracking-tight text-slate-900">
                                Septa<span class="text-brand-600">Panel</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono tracking-wider uppercase font-semibold">Hosting Control</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Navigation Section -->
                <div class="px-4 py-3">
                    <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest font-mono">Menu Navigasi</span>
                </div>

                <nav class="px-3 space-y-1 font-medium text-xs">
                    @php
                        $user = auth()->user();
                        $isAdmin = $user->isAdmin();
                        $currentRoute = request()->route()->getName();
                    @endphp

                    <!-- Dashboard -->
                    <a href="{{ $isAdmin ? route('admin.dashboard') : route('client.dashboard') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'dashboard') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 {{ str_contains($currentRoute, 'dashboard') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Websites -->
                    <a href="{{ $isAdmin ? route('admin.websites') : route('client.websites') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'websites') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 {{ str_contains($currentRoute, 'websites') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <span>Websites</span>
                    </a>

                    <!-- Domains -->
                    <a href="{{ $isAdmin ? route('admin.domains') : route('client.domains') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'domains') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 {{ str_contains($currentRoute, 'domains') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span>Domains</span>
                    </a>

                    <!-- Databases -->
                    <a href="{{ route('client.databases') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'databases') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 {{ str_contains($currentRoute, 'databases') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <span>Databases</span>
                    </a>

                    <!-- File Manager -->
                    <a href="{{ route('client.files') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'files') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 {{ str_contains($currentRoute, 'files') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span>File Manager</span>
                    </a>

                    <!-- SSL Certificates -->
                    <a href="{{ route('client.ssl') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'ssl') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 {{ str_contains($currentRoute, 'ssl') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>SSL Certificates</span>
                    </a>

                    @if($isAdmin)
                        <div class="pt-4 pb-2">
                            <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest font-mono">Administrator</span>
                        </div>

                        <a href="{{ route('admin.clients') }}" 
                           class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'clients') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 {{ str_contains($currentRoute, 'clients') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Clients</span>
                        </a>

                        <a href="{{ route('admin.audit-logs') }}" 
                           class="flex items-center space-x-3 px-3.5 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'audit-logs') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 {{ str_contains($currentRoute, 'audit-logs') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Audit Logs</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Footer User Section -->
            <div class="p-4 border-t border-slate-100 space-y-2">
                <a href="{{ route('account.index') }}" class="card-box flex items-center justify-between p-2.5 rounded-lg hover:border-brand-300 transition-all">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center font-bold text-white text-xs">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-slate-900 truncate">{{ $user->name }}</span>
                            <span class="text-[10px] text-slate-500 font-mono capitalize">{{ $user->role }}</span>
                        </div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-lg text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition-all font-semibold text-xs border border-transparent hover:border-rose-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar Account</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Clean Header -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-slate-500 hover:text-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Status Badge -->
                    <div class="flex items-center space-x-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-mono font-medium text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Server Active</span>
                    </div>

                    <!-- Role Badge -->
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono tracking-wider uppercase border {{ $isAdmin ? 'bg-brand-50 text-brand-700 border-brand-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                        {{ $user->role }}
                    </span>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
                @if(session('success'))
                    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                        @foreach($errors->all() as $error)
                            <p class="flex items-center space-x-2">
                                <svg class="w-4 h-4 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $error }}</span>
                            </p>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>
</body>
</html>
