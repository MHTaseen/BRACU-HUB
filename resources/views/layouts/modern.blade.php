<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'BRACU HUB')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <style>
        :root {
            --bg-deep: #0f172a;
            --bg-accent: #1e293b;
            --primary-neon: #22d3ee; /* Cyan for Students */
            --faculty-neon: #a855f7; /* Violet for Faculty */
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        @php
            $accent = (auth()->check() && auth()->user()->role === 'faculty') ? 'var(--faculty-neon)' : 'var(--primary-neon)';
        @endphp

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg-deep);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .glass-panel {
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
        }

        .navbar {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #fff, {{ $accent }});
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dim);
            font-weight: 500;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }

        .nav-link:hover, .nav-link.active {
            color: {{ $accent }};
        }

        .btn-logout {
            background: transparent;
            border: 1px solid {{ $accent }};
            color: {{ $accent }};
            padding: 0.5rem 1.25rem;
            border-radius: 999px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: {{ $accent }};
            color: #fff;
            box-shadow: 0 0 20px {{ $accent }}66;
        }

        .main-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .page-header {
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-dim);
            font-size: 1.1rem;
        }

        /* Common Elements */
        .neon-text {
            color: {{ $accent }};
            text-shadow: 0 0 10px {{ $accent }}aa;
        }

        .neon-border {
            border: 1px solid {{ $accent }};
            box-shadow: 0 0 15px {{ $accent }}33;
        }

        @yield('extra_css')
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/" class="nav-brand">BRACU <span style="font-weight: 300;">HUB</span></a>
        
        <div class="nav-links">
            @auth
                @if(auth()->user()->role === 'faculty')
                    <a href="{{ route('assignments.create') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">Assignments</a>
                    <a href="{{ route('attendance.create') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">Mark Attendance</a>
                @else
                    <a href="{{ route('attendance.student') }}" class="nav-link {{ request()->routeIs('attendance.student') ? 'active' : '' }}">My Progress</a>
                @endif
                <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">Academic Calendar</a>
                
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <button type="submit" class="btn-logout">Sign Out</button>
                </form>
            @endauth
        </div>
    </nav>

    <main class="main-container">
        @yield('content')
    </main>

    <!-- Alpine.js for dropdowns/interactivity -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @yield('extra_js')
</body>
</html>
