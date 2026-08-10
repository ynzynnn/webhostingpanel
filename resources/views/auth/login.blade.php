<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin — SeptaPanel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f1f5f9;
            margin: 0;
            padding: 1rem;
        }
        .login-box {
            background: #ffffff;
            padding: 2.25rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 380px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <div class="login-box">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-600 text-white font-bold text-xl shadow-sm mb-3">
                S
            </div>
            <h1 class="text-xl font-bold text-slate-900">Login Septa<span class="text-brand-600">Panel</span></h1>
            <p class="text-xs text-slate-500 mt-1">Control Panel VPS Hosting Light</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email / Username</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="admin@septapanel.local"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all">
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-2.5 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm transition-all duration-150">
                Masuk Dashboard
            </button>
        </form>

        <div class="text-center mt-6 text-[11px] text-slate-400 font-mono">
            SeptaPanel v1.0 &bull; Ubuntu VPS Edition
        </div>
    </div>

</body>
</html>
