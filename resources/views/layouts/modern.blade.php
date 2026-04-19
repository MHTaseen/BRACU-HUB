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

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            position: absolute;
            right: 0;
            top: 2.5rem;
            background-color: var(--bg-accent);
            min-width: 300px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.5);
            z-index: 200;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            max-height: 400px;
            overflow-y: auto;
        }

        .dropdown-content .notif-item {
            color: var(--text-main);
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            border-bottom: 1px solid var(--glass-border);
            transition: background 0.2s;
        }

        .dropdown-content .notif-item:last-child {
            border-bottom: none;
        }

        .dropdown-content .notif-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .notif-dismiss {
            flex-shrink: 0;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.15);
            color: var(--text-dim);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            margin-top: 2px;
        }

        .notif-dismiss:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
        }
        
        .bell-icon {
            position: relative;
            cursor: pointer;
            color: var(--text-dim);
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .bell-icon:hover {
            color: {{ $accent }};
        }
        
        .badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 0.65rem;
            font-weight: 700;
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
                    <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.*') || request()->routeIs('sections.*') ? 'active' : '' }}">Curriculum Hub</a>
                    <a href="{{ route('assignments.create') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">Assignments</a>
                    <a href="{{ route('attendance.create') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">Mark Attendance</a>
                    <a href="{{ route('quiz.grades.index') }}" class="nav-link {{ request()->routeIs('quiz.grades.*') ? 'active' : '' }}">Quiz Grades</a>
                @else
                    <a href="{{ route('student.tracker') }}" class="nav-link {{ request()->routeIs('student.tracker') ? 'active' : '' }}">Academic Tracker</a>
                    <a href="{{ route('revision.index') }}" class="nav-link {{ request()->routeIs('revision.index') ? 'active' : '' }}">Revision Planner</a>
                    <a href="{{ route('assistant.index') }}" class="nav-link {{ request()->routeIs('assistant.*') ? 'active' : '' }}">AI Assistant</a>
                    <a href="{{ route('study-rooms.index') }}" class="nav-link {{ request()->routeIs('study-rooms.*') ? 'active' : '' }}">Study Rooms</a>
                    <a href="{{ route('peer.suggestions') }}" class="nav-link {{ request()->routeIs('peer.suggestions') ? 'active' : '' }}">Find Partners</a>
                @endif
                <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">Academic Calendar</a>
                <a href="{{ route('concept-map.index') }}" class="nav-link {{ request()->routeIs('concept-map.index') ? 'active' : '' }}">Concept Map</a>
                
                <div class="dropdown" x-data="{ open: false }" @click.outside="open = false">
                    <div @click="open = !open" class="bell-icon">
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="badge">{{ Auth::user()->unreadNotifications->count() }}</span>
                        @endif
                    </div>
                    <div class="dropdown-content" x-show="open" style="display: none;">
                        <div style="font-size: 0.85rem; color: var(--text-dim); padding: 12px 16px; border-bottom: 1px solid var(--glass-border);">Notifications</div>
                        @forelse (Auth::user()->notifications()->take(10)->get() as $notification)
                            <div class="notif-item" id="notif-{{ $notification->id }}">
                                <div style="flex: 1;">
                                    <div style="font-weight: {{ $notification->unread() ? '700' : '500' }}; font-size: 0.9rem;">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 4px;">{{ $notification->data['message'] ?? '' }}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-dim); margin-top: 4px;">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                                <button
                                    class="notif-dismiss"
                                    title="Dismiss"
                                    onclick="dismissNotification('{{ $notification->id }}', this)"
                                >&times;</button>
                            </div>
                        @empty
                            <div class="notif-item" id="notif-empty" style="justify-content: center; color: var(--text-dim); font-size: 0.85rem;">No notifications</div>
                        @endforelse
                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <form method="POST" action="{{ route('notifications.mark-read') }}" style="padding: 8px; border-top: 1px solid var(--glass-border);">
                                @csrf
                                <button type="submit" style="width: 100%; background: transparent; color: {{ $accent }}; border: none; cursor: pointer; padding: 8px; font-size: 0.85rem; font-weight: 500;">Mark all as read</button>
                            </form>
                        @endif
                    </div>
                </div>

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
    <script>
        function dismissNotification(id, btn) {
            const item = document.getElementById('notif-' + id);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/notifications/' + id + '/dismiss', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    item.style.transition = 'opacity 0.3s, transform 0.3s';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        item.remove();
                        // Check if we need to show the empty placeholder
                        const dropdown = document.querySelector('.dropdown-content');
                        const items = dropdown.querySelectorAll('.notif-item');
                        if (items.length === 0) {
                            const empty = document.createElement('div');
                            empty.className = 'notif-item';
                            empty.id = 'notif-empty';
                            empty.style.justifyContent = 'center';
                            empty.style.color = 'var(--text-dim)';
                            empty.style.fontSize = '0.85rem';
                            empty.textContent = 'No notifications';
                            dropdown.insertBefore(empty, dropdown.querySelector('form'));
                        }
                        // Update badge count
                        const badge = document.querySelector('.badge');
                        if (badge) {
                            const count = parseInt(badge.textContent) - 1;
                            if (count <= 0) badge.remove();
                            else badge.textContent = count;
                        }
                    }, 300);
                }
            });
        }
    </script>
    @yield('extra_js')
</body>
</html>
