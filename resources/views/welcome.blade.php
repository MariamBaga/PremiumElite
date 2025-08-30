<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>OptimuX</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>


    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col"
    style="background: url('{{ asset('images/fibreoptique.png') }}') no-repeat center center fixed; background-size: cover;">
        <!-- Header -->
        <!-- <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-1.5 border rounded-sm text-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-1.5 border rounded-sm text-sm">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-1.5 border rounded-sm text-sm">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header> -->

        <!-- Section Bienvenue -->
        <main class="text-center">
            <h1 class="text-4xl font-bold mb-4">Bienvenue sur <span class="text-[#F53003]">OptimuX</span></h1>
            <p class="text-lg text-[#706f6c]">Connectez-vous pour accéder à votre espace personnel</p>

            <div class="mt-6 flex gap-4 justify-center">
                @guest
                    <a href="{{ route('login') }}" class="px-6 py-2 bg-[#1b1b18] text-white rounded-md hover:bg-black">
                        Se connecter
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-6 py-2 border border-[#1b1b18] rounded-md hover:bg-[#1b1b18] hover:text-white">
                            S’inscrire
                        </a>
                    @endif
                @else
                    <a href="{{ url('/dossiers') }}" class="px-6 py-2 bg-[#F53003] border border-[#1b1b18] rounded-md hover:bg-[#1b1b18] hover:text-white">
                        Aller au Dashboard
                    </a>
                @endguest
            </div>
        </main>
    </body>
</html>








