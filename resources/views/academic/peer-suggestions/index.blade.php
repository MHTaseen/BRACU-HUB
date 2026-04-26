@extends('layouts.modern')

@section('title', 'Find Study Partners')

@section('extra_css')
<style>
/* ============================================================
   PAGE HEADER
   ============================================================ */
.ps-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.ps-header h1 { font-size: 2.25rem; margin: 0 0 0.4rem; }
.ps-header p  { color: var(--text-dim); max-width: 620px; margin: 0; font-size: 1rem; }

/* ============================================================
   HOW IT WORKS BANNER
   ============================================================ */
.ps-how {
    background: rgba(34,211,238,0.06);
    border: 1px solid rgba(34,211,238,0.18);
    border-radius: 18px;
    padding: 1.1rem 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    align-items: center;
    margin-bottom: 2rem;
}

.ps-how-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--primary-neon);
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 0.5rem;
}

.ps-signals {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem 1.5rem;
}

.ps-signal {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: var(--text-dim);
}

.ps-signal-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ============================================================
   FILTER BAR
   ============================================================ */
.ps-filter-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.75rem;
}

.ps-filter-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-dim);
    white-space: nowrap;
}

.ps-filter-select {
    padding: 0.55rem 1rem;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.05);
    color: #fff;
    font-family: inherit;
    font-size: 0.875rem;
    outline: none;
    cursor: pointer;
    transition: border-color 0.2s;
}

.ps-filter-select:focus { border-color: var(--primary-neon); }
.ps-filter-select option { background: #1e293b; }

.ps-count-label {
    margin-left: auto;
    font-size: 0.82rem;
    color: var(--text-dim);
}

/* ============================================================
   GRID
   ============================================================ */
.ps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

/* ============================================================
   PEER CARD
   ============================================================ */
.peer-card {
    border-radius: 24px;
    background: rgba(255,255,255,0.055);
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 16px 48px rgba(0,0,0,0.15);
    overflow: hidden;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    display: flex;
    flex-direction: column;
}

.peer-card:hover {
    transform: translateY(-4px);
    border-color: rgba(34,211,238,0.28);
    box-shadow: 0 24px 60px rgba(0,0,0,0.22);
}

/* Accent glow strip at top, colored by match tier */
.peer-card-accent {
    height: 3px;
    width: 100%;
}

.peer-card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

/* Top row: avatar + name + match badge */
.peer-card-top {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.peer-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 1.3rem;
    font-weight: 700;
    color: #0f172a;
    flex-shrink: 0;
    position: relative;
}

.peer-avatar-inner {
    width: 100%; height: 100%;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, var(--primary-neon), #818cf8);
}

.peer-name {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 0.2rem;
    color: #f1f5f9;
}

.peer-sub {
    font-size: 0.78rem;
    color: var(--text-dim);
}

.match-badge {
    margin-left: auto;
    text-align: center;
    flex-shrink: 0;
}

.match-pct {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1;
}

.match-tier {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-top: 0.2rem;
    white-space: nowrap;
}

/* Score bar breakdown */
.ps-breakdown {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.ps-breakdown-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.1rem;
}

.ps-bar-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.ps-bar-label {
    font-size: 0.75rem;
    color: var(--text-dim);
    width: 90px;
    flex-shrink: 0;
}

.ps-bar-track {
    flex: 1;
    height: 6px;
    border-radius: 999px;
    background: rgba(255,255,255,0.08);
    overflow: hidden;
}

.ps-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.ps-bar-val {
    font-size: 0.72rem;
    color: var(--text-dim);
    width: 28px;
    text-align: right;
    flex-shrink: 0;
}

/* Shared sections */
.ps-sections-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.45rem;
}

.ps-section-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 0.3rem 0.7rem;
    font-size: 0.78rem;
    color: #cbd5e1;
    margin: 0.2rem 0.2rem 0 0;
}

/* Room badge */
.ps-room-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: rgba(34,211,238,0.08);
    border: 1px solid rgba(34,211,238,0.2);
    border-radius: 999px;
    padding: 0.25rem 0.7rem;
    font-size: 0.72rem;
    color: var(--primary-neon);
    font-weight: 600;
}

/* Card footer */
.peer-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    display: flex;
    gap: 0.65rem;
    align-items: center;
}

.btn-invite {
    flex: 1;
    padding: 0.65rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    background: var(--primary-neon);
    color: #0f172a;
    text-decoration: none;
    text-align: center;
    border: none;
    cursor: pointer;
    transition: transform 0.16s, box-shadow 0.16s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.btn-invite:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(34,211,238,0.35);
}

/* ============================================================
   EMPTY STATE
   ============================================================ */
.ps-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    border-radius: 24px;
    background: rgba(255,255,255,0.03);
    border: 1px dashed rgba(255,255,255,0.1);
}

.ps-empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.ps-empty h3   { font-size: 1.4rem; margin-bottom: 0.75rem; }
.ps-empty p    { color: var(--text-dim); max-width: 480px; margin: 0 auto 1.5rem; }

/* ============================================================
   ANIMATIONS
   ============================================================ */
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.peer-card {
    animation: fadeSlideUp 0.4s ease both;
}
</style>
@endsection

@section('content')

{{-- PAGE HEADER --}}
<div class="ps-header">
    <div>
        <h1 class="page-title">Find Study Partners</h1>
        <p class="page-subtitle">
            Students are ranked by academic compatibility — how many courses you share,
            how similar your workload and attendance patterns are, and whether you've
            collaborated before.
        </p>
    </div>
</div>

{{-- HOW IT WORKS --}}
<div class="ps-how">
    <div>
        <div class="ps-how-title">How matches are scored</div>
        <div class="ps-signals">
            <div class="ps-signal">
                <div class="ps-signal-dot" style="background:#22d3ee;"></div>
                Shared Courses (40 pts)
            </div>
            <div class="ps-signal">
                <div class="ps-signal-dot" style="background:#a78bfa;"></div>
                Workload Similarity (30 pts)
            </div>
            <div class="ps-signal">
                <div class="ps-signal-dot" style="background:#4ade80;"></div>
                Attendance Similarity (15 pts)
            </div>
            <div class="ps-signal">
                <div class="ps-signal-dot" style="background:#fb923c;"></div>
                Submission Rate (15 pts)
            </div>
            <div class="ps-signal">
                <div class="ps-signal-dot" style="background:#f472b6;"></div>
                Study Room Bonus (+5 pts)
            </div>
        </div>
    </div>
</div>

@if($peers->isEmpty())
    {{-- EMPTY STATE --}}
    <div class="ps-grid">
        <div class="ps-empty">
            <div class="ps-empty-icon">🤝</div>
            @if($mySections->isEmpty())
                <h3>You're not enrolled in any sections yet</h3>
                <p>Once you're enrolled in a course section, we'll find students with similar academic profiles to collaborate with.</p>
            @else
                <h3>No suitable study partners found yet</h3>
                <p>We couldn't find other students in your sections with a high enough compatibility score. As more students join your sections, recommendations will appear here.</p>
                <a href="{{ route('study-rooms.index') }}" class="btn-invite" style="display:inline-flex; max-width: 220px; margin: 0 auto;">
                    Browse Study Rooms
                </a>
            @endif
        </div>
    </div>
@else

    {{-- FILTER + COUNT --}}
    <div class="ps-filter-bar">
        <span class="ps-filter-label">Filter by section:</span>
        <select class="ps-filter-select" id="section-filter" onchange="filterPeers(this.value)">
            <option value="all">All Sections</option>
            @foreach($mySections as $sec)
            <option value="{{ $sec->id }}">
                {{ $sec->course->code }} — Sec {{ $sec->section_number }}
            </option>
            @endforeach
        </select>
        <span class="ps-count-label" id="peer-count">
            {{ $peers->count() }} {{ $peers->count() === 1 ? 'partner' : 'partners' }} found
        </span>
    </div>

    {{-- PEER CARDS GRID --}}
    <div class="ps-grid" id="peers-grid">
        @foreach($peers as $i => $peer)
        @php
            $animDelay = $i * 60; // stagger
        @endphp
        <div
            class="peer-card"
            style="animation-delay: {{ $animDelay }}ms;"
            data-sections="{{ implode(',', $peer['shared_sections']->pluck('label')->toArray()) }}"
            data-section-ids="{{ $peer['first_shared_section_id'] }}"
        >
            {{-- Accent strip --}}
            <div class="peer-card-accent" style="background: linear-gradient(90deg, {{ $peer['match_color'] }}, #818cf8);"></div>

            <div class="peer-card-body">

                {{-- Top: avatar + name + match badge --}}
                <div class="peer-card-top">
                    <div class="peer-avatar">
                        <div class="peer-avatar-inner">{{ $peer['initial'] }}</div>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <p class="peer-name">{{ $peer['name'] }}</p>
                        <p class="peer-sub">
                            {{ $peer['shared_count'] }} shared {{ $peer['shared_count'] === 1 ? 'section' : 'sections' }}
                            @if($peer['shared_rooms'] > 0)
                            &nbsp;·&nbsp;
                            <span style="color: var(--primary-neon);">{{ $peer['shared_rooms'] }} shared {{ $peer['shared_rooms'] === 1 ? 'room' : 'rooms' }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="match-badge">
                        <div class="match-pct" style="color: {{ $peer['match_color'] }};">
                            {{ $peer['icon'] ?? $peer['match_icon'] }}{{ $peer['total'] }}%
                        </div>
                        <div class="match-tier" style="color: {{ $peer['match_color'] }};">
                            {{ $peer['match_label'] }}
                        </div>
                    </div>
                </div>

                {{-- Score breakdown bars --}}
                <div class="ps-breakdown">
                    <div class="ps-breakdown-title">Compatibility Breakdown</div>

                    @php
                        $bars = [
                            ['Shared Courses',   $peer['breakdown']['shared'],   40,  '#22d3ee'],
                            ['Workload',         $peer['breakdown']['workload'],  30,  '#a78bfa'],
                            ['Attendance',       $peer['breakdown']['att'],       15,  '#4ade80'],
                            ['Submissions',      $peer['breakdown']['sub'],       15,  '#fb923c'],
                        ];
                        if ($peer['breakdown']['room'] > 0) {
                            $bars[] = ['Study Rooms', $peer['breakdown']['room'], 5, '#f472b6'];
                        }
                    @endphp

                    @foreach($bars as [$label, $val, $max, $color])
                    <div class="ps-bar-row">
                        <span class="ps-bar-label">{{ $label }}</span>
                        <div class="ps-bar-track">
                            <div class="ps-bar-fill"
                                 style="width: {{ $max > 0 ? round(($val / $max) * 100) : 0 }}%; background: {{ $color }};"
                            ></div>
                        </div>
                        <span class="ps-bar-val">{{ $val }}/{{ $max }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Shared sections --}}
                <div>
                    <div class="ps-sections-title">Shared Course Sections</div>
                    @foreach($peer['shared_sections'] as $sec)
                    <span class="ps-section-chip" title="{{ $sec['schedule'] }}">
                        📘 {{ $sec['label'] }}
                    </span>
                    @endforeach
                </div>

                {{-- Study room badge if applicable --}}
                @if($peer['shared_rooms'] > 0)
                <div>
                    <span class="ps-room-badge">
                        🏠 Previously collaborated in {{ $peer['shared_rooms'] }} study {{ $peer['shared_rooms'] === 1 ? 'room' : 'rooms' }}
                    </span>
                </div>
                @endif

            </div>{{-- /.peer-card-body --}}

            {{-- Card footer: invite button --}}
            <div class="peer-card-footer">
                <a href="{{ route('study-rooms.create') }}" class="btn-invite">
                    🏠 Invite to Study Room
                </a>
            </div>
        </div>{{-- /.peer-card --}}
        @endforeach
    </div>{{-- /#peers-grid --}}

@endif
@endsection

@section('extra_js')
<script>
// ── Section filter ────────────────────────────────────────────────────────────
function filterPeers(sectionId) {
    const cards    = document.querySelectorAll('.peer-card');
    let   visible  = 0;

    cards.forEach(card => {
        if (sectionId === 'all') {
            card.style.display = '';
            visible++;
            return;
        }
        const secIds = (card.dataset.sectionIds || '').split(',').map(s => s.trim());
        if (secIds.includes(String(sectionId))) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    const countEl = document.getElementById('peer-count');
    if (countEl) {
        countEl.textContent = visible + (visible === 1 ? ' partner found' : ' partners found');
    }
}

// ── Animate bars on load ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Bars start at 0 width via CSS, this triggers the transition
    document.querySelectorAll('.ps-bar-fill').forEach(bar => {
        const target = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = target; }, 200);
    });
});
</script>
@endsection
