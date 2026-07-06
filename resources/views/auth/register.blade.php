<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register – {{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Prevent flash of light mode in dark mode -->
    <script>
        if (localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-tr from-indigo-50 via-slate-50 to-emerald-50 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950 min-h-screen flex items-center justify-center p-4 relative overflow-hidden transition-colors">

    <!-- Ambient background blobs for depth -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-400/20 dark:bg-purple-900/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-40 w-96 h-96 bg-emerald-400/15 dark:bg-emerald-900/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-blue-400/20 dark:bg-blue-900/10 rounded-full blur-3xl"></div>
    </div>

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-green-600 dark:text-green-400">{{ config('app.name') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Join the community today</p>
    </div>

    <!-- Card -->
    <x-liquid-glass-card class="p-8">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-6">Create your account</h2>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-905 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500
                              @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-905 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500
                              @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-905 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500
                              @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-905 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 dark:focus:ring-green-500">
            </div>

            <!-- Submit -->
            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-xl transition">
                Create Account
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-green-600 dark:text-green-400 font-medium hover:underline">Sign in</a>
        </p>
    </x-liquid-glass-card>
</div>

<!-- Global Structural Layer for Liquid Optics Distortion -->
<svg style="position: absolute; width: 0; height: 0; visibility: hidden; pointer-events: none;" aria-hidden="true">
  <defs>
    <filter id="liquid-lens-refraction" x="-20%" y="-20%" width="140%" height="140%">
      <!-- Generates dynamic, natural visual turbulence fields -->
      <feTurbulence type="fractalNoise" baseFrequency="0.012" numOctaves="3" result="fluid_noise" />
      <!-- Alters coordinate vectors of the background elements using noise fields -->
      <feDisplacementMap in="SourceGraphic" in2="fluid_noise" scale="22" xChannelSelector="R" yChannelSelector="G" />
    </filter>
  </defs>
</svg>

</body>
</html>
