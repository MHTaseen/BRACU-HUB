@extends('layouts.modern')

@section('title', 'Study Rooms')

@section('extra_css')
<style>
    .sr-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .sr-header h1 { font-size: 2.25rem; margin: 0; }
    .sr-header p { color: var(--text-dim); max-width: 620px; margin: 0.6rem 0 0; }

    /* Tab Switcher */
    .sr-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.75rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding-bottom: 0;
    }

    .sr-tab {
        padding: 0.65rem 1.35rem;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
        font-size: 0.92rem;
        cursor: pointer;
        border: none;
        background: transparent;
        color: var(--text-dim);
        position: relative;
        transition: color 0.2s;
    }

    .sr-tab.active {
        color: var(--primary-neon);
        background: rgba(34,211,238,0.07);
    }

    .sr-tab.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0; right: 0;
        height: 2px;
        background: var(--primary-neon);
        border-radius: 2px 2px 0 0;
    }

    .sr-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        background: rgba(255,255,255,0.1);
        color: var(--text-dim);
        font-size: 0.72rem;
        font-weight: 700;
        margin-left: 0.4rem;
        padding: 0 5px;
    }

    .sr-tab.active .sr-tab-badge {
        background: rgba(34,211,238,0.18);
        color: var(--primary-neon);
    }

    /* Grid */
    .sr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    /* Card */
    .sr-card {
        padding: 1.75rem;
        border-radius: 24px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        box-shadow: 0 16px 40px rgba(0,0,0,0.14);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .sr-card:hover {
        transform: translateY(-3px);
        border-color: rgba(34,211,238,0.3);
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }

    .sr-card.archived {
        opacity: 0.72;
        border-color: rgba(255,255,255,0.05);
    }

    .sr-card.archived:hover {
        border-color: rgba(255,255,255,0.14);
        transform: translateY(-2px);
    }

    /* Live glow strip */
    .sr-card.live::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-neon), #818cf8);
        border-radius: 24px 24px 0 0;
    }

    .sr-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .sr-card-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        color: #f1f5f9;
        line-height: 1.3;
    }

    .sr-status-badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .sr-status-badge.live {
        background: rgba(34,211,238,0.12);
        color: #22d3ee;
        border: 1px solid rgba(34,211,238,0.25);
    }

    .sr-status-badge.archived {
        background: rgba(148,163,184,0.1);
        color: #94a3b8;
        border: 1px solid rgba(148,163,184,0.2);
    }

    .live-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #22d3ee;
        animation: pulse-dot 1.5s ease infinite;
        flex-shrink: 0;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.7); }
    }

    .sr-meta {
        color: var(--text-dim);
        font-size: 0.875rem;
        margin: 0.25rem 0;
    }

    .sr-description {
        color: var(--text-dim);
        font-size: 0.85rem;
        margin: 0.75rem 0 0;
        line-height: 1.5;
        border-top: 1px solid rgba(255,255,255,0.05);
        padding-top: 0.75rem;
        font-style: italic;
    }

    .sr-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.35rem;
    }

    .sr-online {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.82rem;
        color: var(--text-dim);
    }

    .sr-online .dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #4ade80;
        animation: pulse-dot 1.8s ease infinite;
    }

    .sr-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .btn-join {
        padding: 0.65rem 1.3rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.88rem;
        background: var(--primary-neon);
        color: #0f172a;
        text-decoration: none;
        transition: transform 0.18s, box-shadow 0.18s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-join:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(34,211,238,0.35);
    }

    .btn-ghost {
        padding: 0.65rem 1.1rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.88rem;
        background: rgba(255,255,255,0.07);
        color: #cbd5e1;
        border: 1px solid rgba(255,255,255,0.12);
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-ghost:hover { background: rgba(255,255,255,0.12); }

    /* Empty state */
    .sr-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3.5rem 2rem;
        border-radius: 24px;
        background: rgba(255,255,255,0.03);
        border: 1px dashed rgba(255,255,255,0.1);
    }

    .sr-empty h3 { font-size: 1.4rem; margin-bottom: 0.75rem; }
    .sr-empty p  { color: var(--text-dim); margin-bottom: 1.5rem; }

    /* Tab panels */
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
</style>
@endsection

@section('content')
<div class="sr-header">
    <div>
        <h1 class="page-title">Study Rooms</h1>
        <p class="page-subtitle">Create or join collaborative rooms for your course sections. Take shared notes, draw on the whiteboard, and chat in real time.</p>
    </div>
    <a href="{{ route('study-rooms.create') }}" class="btn-join" style="align-self: flex-start;">
        + Create Room
    </a>
</div>

{{-- Tab Navigation --}}
<div class="sr-tabs">
    <button class="sr-tab active" onclick="switchTab('active', this)">
        Live Rooms
        <span class="sr-tab-badge">{{ $activeRooms->count() }}</span>
    </button>
    <button class="sr-tab" onclick="switchTab('archived', this)">
        Archived Sessions
        <span class="sr-tab-badge">{{ $archivedRooms->count() }}</span>
    </button>
</div>

{{-- Active Rooms Panel --}}
<div id="tab-active" class="tab-panel active">
    <div class="sr-grid">
        @forelse($activeRooms as $room)
        <div class="sr-card live">
            <div class="sr-card-top">
                <h3 class="sr-card-title">{{ $room->name }}</h3>
                <span class="sr-status-badge live">
                    <span class="live-dot"></span> Live
                </span>
            </div>

            <p class="sr-meta"><strong>Course:</strong> {{ $room->course->code }} — {{ $room->course->title }}</p>
            <p class="sr-meta"><strong>Section:</strong> {{ $room->course->code }} — Sec {{ $room->section->section_number }}</p>
            <p class="sr-meta"><strong>Created by:</strong> {{ $room->creator->name }}</p>

            @if($room->description)
            <p class="sr-description">{{ $room->description }}</p>
            @endif

            <div class="sr-footer">
                <div class="sr-online">
                    <span class="dot"></span>
                    {{ $room->activeParticipants->count() }} online now
                </div>

                <div class="sr-actions">
                    <a href="{{ route('study-rooms.show', $room) }}" class="btn-join">
                        Join Room →
                    </a>
                    @if($room->created_by === auth()->id())
                    <form action="{{ route('study-rooms.archive', $room) }}" method="POST" style="margin:0;" onsubmit="return confirm('Archive this room? Participants will be logged out.')">
                        @csrf
                        <button type="submit" class="btn-ghost">Archive</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="sr-empty">
            <h3>No active rooms yet</h3>
            <p>Start a room to collaborate with your classmates in real time.</p>
            <a href="{{ route('study-rooms.create') }}" class="btn-join">Create Your First Room</a>
        </div>
        @endforelse
    </div>
</div>

{{-- Archived Rooms Panel --}}
<div id="tab-archived" class="tab-panel">
    <div class="sr-grid">
        @forelse($archivedRooms as $room)
        <div class="sr-card archived">
            <div class="sr-card-top">
                <h3 class="sr-card-title">{{ $room->name }}</h3>
                <span class="sr-status-badge archived">Archived</span>
            </div>

            <p class="sr-meta"><strong>Course:</strong> {{ $room->course->code }} — {{ $room->course->title }}</p>
            <p class="sr-meta"><strong>Section:</strong> {{ $room->course->code }} — Sec {{ $room->section->section_number }}</p>
            <p class="sr-meta"><strong>Created by:</strong> {{ $room->creator->name }}</p>
            @if($room->archived_at)
            <p class="sr-meta" style="margin-top: 0.5rem; font-size: 0.8rem;">
                Archived {{ $room->archived_at->diffForHumans() }}
            </p>
            @endif

            @if($room->description)
            <p class="sr-description">{{ $room->description }}</p>
            @endif

            <div class="sr-footer">
                <span style="color: var(--text-dim); font-size: 0.82rem;">Session closed</span>
                {{-- View archived notes (still accessible, read-only implied) --}}
                <a href="{{ route('study-rooms.show', $room) }}" class="btn-ghost">View Archive</a>
            </div>
        </div>
        @empty
        <div class="sr-empty">
            <h3>No archived sessions</h3>
            <p>Rooms you archive will appear here for reference.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('extra_js')
<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sr-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    btn.classList.add('active');
}
</script>
@endsection