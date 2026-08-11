<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SeptaPanel') - Hosting Control Panel</title>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- TailwindCSS v3.4 CDN -->
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
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc8fc',
                            400: '#36affa',
                            500: '#0c94eb',
                            600: '#0077c9',
                            700: '#015fa3',
                            800: '#065186',
                            900: '#0b436f',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .card-box {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }
        .card-box-hover {
            transition: all 0.2s ease-in-out;
        }
        .card-box-hover:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
    </style>
</head>
<body class="h-full font-sans bg-slate-50 text-slate-800 antialiased" 
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', 
          toggleSidebar() { 
              this.sidebarCollapsed = !this.sidebarCollapsed; 
              localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed); 
          } 
      }">

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
        <aside :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full': !sidebarOpen,
                   'md:w-64': !sidebarCollapsed,
                   'md:w-20': sidebarCollapsed
               }" 
               class="fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 transform transition-all duration-300 ease-in-out md:translate-x-0 md:static md:inset-0 flex flex-col justify-between shadow-xs shrink-0">
            
            <div>
                <!-- Brand Header -->
                <div class="h-16 px-4 flex items-center justify-between border-b border-slate-100">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="flex items-center space-x-3 group min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-sm shrink-0">
                            S
                        </div>
                        <div class="flex flex-col min-w-0" x-show="!sidebarCollapsed" x-cloak>
                            <span class="font-extrabold text-lg tracking-tight text-slate-900 truncate">
                                Septa<span class="text-brand-600">Panel</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono tracking-wider uppercase font-semibold truncate">Hosting Control</span>
                        </div>
                    </a>

                    <!-- Desktop Minimize Toggle Button -->
                    <button @click="toggleSidebar()" class="hidden md:flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Minimalkan Sidebar'">
                        <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Mobile Close Button -->
                    <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Navigation Section Header -->
                <div class="px-4 py-3" x-show="!sidebarCollapsed" x-cloak>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest font-mono">Menu Navigasi</span>
                </div>

                <nav class="px-3 space-y-1 font-medium text-xs pt-2 md:pt-0">
                    @php
                        $user = auth()->user();
                        $isAdmin = $user->isAdmin();
                        $currentRoute = request()->route()->getName();
                    @endphp

                    <!-- Dashboard -->
                    <a href="{{ $isAdmin ? route('admin.dashboard') : route('client.dashboard') }}" 
                       :title="sidebarCollapsed ? 'Dashboard' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'dashboard') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'dashboard') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>Dashboard</span>
                    </a>

                    <!-- Websites -->
                    <a href="{{ $isAdmin ? route('admin.websites') : route('client.websites') }}" 
                       :title="sidebarCollapsed ? 'Websites' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'websites') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'websites') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>Websites</span>
                    </a>

                    <!-- Domains -->
                    <a href="{{ $isAdmin ? route('admin.domains') : route('client.domains') }}" 
                       :title="sidebarCollapsed ? 'Domains' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'domains') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'domains') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>Domains</span>
                    </a>

                    <!-- Databases -->
                    <a href="{{ $isAdmin ? route('admin.databases') : route('client.databases') }}" 
                       :title="sidebarCollapsed ? 'Databases' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'databases') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'databases') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>Databases</span>
                    </a>

                    <!-- File Manager -->
                    <a href="{{ $isAdmin ? route('admin.files') : route('client.files') }}" 
                       :title="sidebarCollapsed ? 'File Manager' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'files') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'files') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>File Manager</span>
                    </a>

                    <!-- SFTP Access -->
                    <a href="{{ $isAdmin ? route('admin.sftp') : route('client.sftp') }}" 
                       :title="sidebarCollapsed ? 'SFTP Access' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'sftp') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'sftp') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>SFTP Access</span>
                    </a>

                    <!-- SSL Certificates -->
                    <a href="{{ $isAdmin ? route('admin.ssl') : route('client.ssl') }}" 
                       :title="sidebarCollapsed ? 'SSL Certificates' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'ssl') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'ssl') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>SSL Certificates</span>
                    </a>

                    <!-- API Keys -->
                    <a href="{{ $isAdmin ? route('admin.api-keys') : route('client.api-keys') }}" 
                       :title="sidebarCollapsed ? 'API Keys' : ''"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'api-keys') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                        <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'api-keys') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span x-show="!sidebarCollapsed" class="truncate" x-cloak>API Keys</span>
                    </a>

                    @if($isAdmin)
                        <div class="pt-4 pb-2" x-show="!sidebarCollapsed" x-cloak>
                            <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest font-mono">Administrator</span>
                        </div>

                        <a href="{{ route('admin.clients') }}" 
                           :title="sidebarCollapsed ? 'Clients' : ''"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'clients') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                           :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'clients') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span x-show="!sidebarCollapsed" class="truncate" x-cloak>Clients</span>
                        </a>

                        <a href="{{ route('admin.audit-logs') }}" 
                           :title="sidebarCollapsed ? 'Audit Logs' : ''"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-semibold transition-all {{ str_contains($currentRoute, 'audit-logs') ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                           :class="sidebarCollapsed ? 'justify-center px-2' : ''">
                            <svg class="w-4 h-4 shrink-0 {{ str_contains($currentRoute, 'audit-logs') ? 'text-brand-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span x-show="!sidebarCollapsed" class="truncate" x-cloak>Audit Logs</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Footer User Section -->
            <div class="p-3 border-t border-slate-100 space-y-2">
                <a href="{{ route('account.index') }}" 
                   :title="sidebarCollapsed ? user.name : ''"
                   class="card-box flex items-center p-2 rounded-lg hover:border-brand-300 transition-all"
                   :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center font-bold text-white text-xs shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex flex-col min-w-0" x-show="!sidebarCollapsed" x-cloak>
                            <span class="text-xs font-bold text-slate-900 truncate">{{ $user->name }}</span>
                            <span class="text-[10px] text-slate-500 font-mono capitalize truncate">{{ $user->role }}</span>
                        </div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            :title="sidebarCollapsed ? 'Keluar Account' : ''"
                            class="w-full flex items-center space-x-2 px-3 py-2 rounded-lg text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition-all font-semibold text-xs border border-transparent hover:border-rose-100"
                            :class="sidebarCollapsed ? 'justify-center px-2' : 'justify-center'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak>Keluar Account</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Top App Bar Header -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-xs">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = true" class="md:hidden text-slate-500 hover:text-slate-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight font-mono">
                        @yield('page_title', 'Dashboard')
                    </h1>
                </div>

                <div class="flex items-center space-x-3">
                    <span class="hidden sm:inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-mono font-semibold {{ $isAdmin ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        <span>{{ strtoupper($user->role) }}</span>
                    </span>

                    <a href="{{ route('account.index') }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 flex items-center justify-center text-slate-700 font-bold text-xs transition-colors">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </a>
                </div>
            </header>

            <!-- Main Page Scrollable Container -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- Global Flash Notifications -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start space-x-3 text-xs text-emerald-900 shadow-xs font-mono">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start space-x-3 text-xs text-rose-900 shadow-xs font-mono">
                        <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 shadow-xs font-mono">
                        <div class="font-bold mb-1">Terdapat kesalahan pada input Anda:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

</body>
</html>
