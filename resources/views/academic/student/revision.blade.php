@extends('layouts.modern')

@section('title', 'Smart Revision Planner | BRACU HUB')

@section('extra_css')
<style>
/* ═══════════════════════════════════════════════════
   REVISION PLANNER — Main Styles
═══════════════════════════════════════════════════ */

/* ── Tab Navigation ─────────────────────────────── */
.tab-nav {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2.5rem;
    padding: 0.4rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    overflow-x: auto;
    scrollbar-width: none;
}

.tab-nav::-webkit-scrollbar { display: none; }

.tab-btn {
    flex: 1;
    min-width: max-content;
    padding: 0.7rem 1.25rem;
    background: transparent;
    border: none;
    border-radius: 12px;
    color: var(--text-dim);
    font-family: 'Outfit', sans-serif;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.05); }

.tab-btn.active {
    background: rgba(34, 211, 238, 0.12);
    color: var(--primary-neon);
    box-shadow: 0 0 0 1px rgba(34,211,238,0.3);
}

.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Course Selector ────────────────────────────── */
.course-selector {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.course-chip {
    padding: 0.5rem 1.25rem;
    border-radius: 999px;
    border: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.04);
    color: var(--text-dim);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Outfit', sans-serif;
}

.course-chip:hover { border-color: rgba(34,211,238,0.4); color: var(--text-main); }

.course-chip.active {
    border-color: var(--primary-neon);
    color: var(--primary-neon);
    background: rgba(34,211,238,0.08);
    box-shadow: 0 0 12px rgba(34,211,238,0.15);
}

/* ── Topic Checklist ────────────────────────────── */
.topic-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.topic-title {
    font-size: 1.3rem;
    font-weight: 600;
}

.progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: var(--text-dim);
}

.progress-track {
    width: 160px;
    height: 6px;
    background: rgba(255,255,255,0.1);
    border-radius: 999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary-neon);
    width: 0%;
    transition: width 0.4s ease;
    border-radius: 999px;
}

.topic-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(0,0,0,0.15);
}

.topic-item:hover {
    border-color: rgba(34,211,238,0.35);
    transform: translateX(4px);
    background: rgba(34,211,238,0.04);
}

.topic-item.weak-flag {
    border-color: rgba(251, 146, 60, 0.35);
    background: rgba(251,146,60,0.04);
}

.topic-item.weak-flag:hover { border-color: rgba(251,146,60,0.6); }

.topic-item.completed {
    opacity: 0.55;
    background: rgba(255,255,255,0.02);
}

.topic-item.completed .topic-name { text-decoration: line-through; color: var(--text-dim); }

.checkbox-circle {
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 2px solid var(--text-dim);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.topic-item.completed .checkbox-circle {
    background: var(--primary-neon);
    border-color: var(--primary-neon);
}

.topic-item.completed .checkbox-circle::after {
    content: '✓'; font-size: 13px; font-weight: 900; color: #0f172a;
}

.topic-name { font-weight: 500; flex: 1; }

.topic-badges { display: flex; gap: 0.5rem; align-items: center; }

.priority-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.badge-critical { background: rgba(251,146,60,0.2); color: #fb923c; border: 1px solid rgba(251,146,60,0.4); }
.badge-high     { background: rgba(250,204,21,0.15); color: #facc15; border: 1px solid rgba(250,204,21,0.35); }
.badge-normal   { background: rgba(148,163,184,0.1); color: var(--text-dim); border: 1px solid var(--glass-border); }

/* ── Quiz Performance ───────────────────────────── */
.quiz-perf-grid { display: grid; gap: 1.25rem; }

.quiz-perf-card {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    background: rgba(0,0,0,0.15);
    transition: all 0.2s;
}

.quiz-perf-card:hover {
    border-color: rgba(34,211,238,0.3);
    transform: translateY(-2px);
}

.quiz-perf-card.weak-card { border-color: rgba(251,146,60,0.3); }

.quiz-bar-wrap { margin-top: 0.5rem; }

.quiz-bar-track {
    height: 5px;
    background: rgba(255,255,255,0.08);
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.4rem;
}

.quiz-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.6s ease;
}

.quiz-score-display {
    text-align: right;
    min-width: 80px;
}

.quiz-score-number {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1;
}

.perf-badge-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    margin-top: 0.35rem;
}

.gpa-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    background: rgba(34,211,238,0.06);
    border: 1px solid rgba(34,211,238,0.2);
    border-radius: 12px;
    margin-bottom: 1.25rem;
}


/* ── Flashcards ─────────────────────────────────── */
.flashcard-shell {
    max-width: 640px;
    margin: 0 auto;
}

.flashcard-viewport {
    perspective: 1200px;
    height: 280px;
    position: relative;
    margin-bottom: 2rem;
    cursor: pointer;
}

.flashcard-inner {
    width: 100%;
    height: 100%;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
}

.flashcard-inner.flipped { transform: rotateY(180deg); }

.flashcard-face {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 24px;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem;
    text-align: center;
    border: 1px solid var(--glass-border);
}

.card-front {
    background: linear-gradient(135deg, rgba(34,211,238,0.08), rgba(15,23,42,0.9));
}

.card-back {
    transform: rotateY(180deg);
    background: linear-gradient(135deg, rgba(168,85,247,0.08), rgba(15,23,42,0.9));
}

.card-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-dim);
    margin-bottom: 1rem;
}

.card-topic-name {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-neon);
    text-shadow: 0 0 20px rgba(34,211,238,0.4);
    margin-bottom: 0.5rem;
}

.card-course-label {
    font-size: 0.8rem;
    color: var(--text-dim);
}

.card-definition {
    font-size: 0.95rem;
    line-height: 1.7;
    color: var(--text-main);
}

.card-hint {
    position: absolute;
    bottom: 1rem;
    font-size: 0.7rem;
    color: var(--text-dim);
}

.flashcard-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.fc-nav-btn {
    background: rgba(255,255,255,0.06);
    border: 1px solid var(--glass-border);
    color: var(--text-dim);
    border-radius: 50%;
    width: 44px;
    height: 44px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Outfit', sans-serif;
}

.fc-nav-btn:hover { background: rgba(255,255,255,0.1); color: var(--text-main); border-color: rgba(255,255,255,0.2); }

.fc-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 0rem;
    justify-content: center;
    flex-wrap: wrap;
}

.fc-action-btn {
    padding: 0.65rem 1.5rem;
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 600;
    font-family: 'Outfit', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid;
}

.fc-know {
    background: rgba(74,222,128,0.1);
    border-color: rgba(74,222,128,0.4);
    color: #4ade80;
}
.fc-know:hover { background: rgba(74,222,128,0.2); }

.fc-review {
    background: rgba(251,146,60,0.1);
    border-color: rgba(251,146,60,0.4);
    color: #fb923c;
}
.fc-review:hover { background: rgba(251,146,60,0.2); }

.fc-counter {
    font-size: 0.88rem;
    color: var(--text-dim);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.fc-progress-dots {
    display: flex;
    gap: 0.4rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}

.fc-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    transition: all 0.2s;
}

.fc-dot.current { background: var(--primary-neon); box-shadow: 0 0 8px rgba(34,211,238,0.5); transform: scale(1.3); }
.fc-dot.known   { background: #4ade80; }
.fc-dot.review  { background: #fb923c; }

/* ── Resources ──────────────────────────────────── */
.resource-grid { display: grid; gap: 1rem; }

.resource-card {
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    background: rgba(0,0,0,0.12);
    transition: all 0.2s;
}

.resource-card:hover {
    border-color: rgba(34,211,238,0.3);
    transform: translateX(4px);
}

.resource-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.resource-topic { font-weight: 600; font-size: 1rem; }

.resource-links {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.resource-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 1rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid var(--glass-border);
    color: var(--text-dim);
    transition: all 0.2s;
    background: rgba(255,255,255,0.04);
}

.resource-link:hover {
    border-color: var(--primary-neon);
    color: var(--primary-neon);
    background: rgba(34,211,238,0.07);
}

/* ── Empty / No Courses ─────────────────────────── */
.no-data {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-dim);
}

.no-data-icon { font-size: 3rem; margin-bottom: 1rem; }

/* ── Misc ───────────────────────────────────────── */
.stat-row {
    display: flex;
    gap: 1.25rem;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
}

.stat-card {
    padding: 1.25rem 1.5rem;
    border-radius: 14px;
    border: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.03);
    flex: 1;
    min-width: 130px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-neon);
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-label { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; }
</style>
@endsection

@section('content')

{{-- ── Page Header ─────────────────────────────────────────────────── --}}
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Smart <span class="neon-text">Revision Planner</span></h1>
    <p class="page-subtitle">AI-driven study plan that adapts to your quiz performance and upcoming deadlines.</p>
</div>

@if($courses->isEmpty())
    <div class="glass-panel no-data" style="max-width: 500px; margin: 0 auto;">
        <div class="no-data-icon">📭</div>
        <h3 style="font-size: 1.4rem; margin-bottom: 0.5rem;">No Active Enrolments</h3>
        <p>You are not enrolled in any courses. Contact your faculty to get enrolled and start planning.</p>
    </div>
@else

{{-- ── Summary Stats ───────────────────────────────────────────────── --}}
@php
    $totalTopics  = $courses->sum(fn($c) => count($c['topics']));
    $totalWeak    = $courses->sum(fn($c) => $c['weak_count']);
    $totalQuizzes = $courses->sum(fn($c) => count($c['quiz_rows']));
    $avgGpa       = $courses->whereNotNull('avg_pct')->avg('avg_pct');
@endphp

<div class="stat-row">
    <div class="stat-card">
        <div class="stat-value">{{ $totalTopics }}</div>
        <div class="stat-label">Topics to Review</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #fb923c;">{{ $totalWeak }}</div>
        <div class="stat-label">Weak Areas Flagged</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $totalQuizzes }}</div>
        <div class="stat-label">Quizzes Tracked</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: {{ $avgGpa !== null ? ($avgGpa >= 60 ? '#4ade80' : '#f87171') : 'var(--text-dim)' }};">
            {{ $avgGpa !== null ? round($avgGpa, 1) . '%' : 'N/A' }}
        </div>
        <div class="stat-label">Avg Quiz Score</div>
    </div>
</div>

{{-- ── Tab Navigation ─────────────────────────────────────────────── --}}
<div class="tab-nav" id="tabNav">
    <button class="tab-btn active" onclick="switchTab('checklist')" id="tab-btn-checklist">
        📋 Topic Checklist
    </button>
    <button class="tab-btn" onclick="switchTab('quiz')" id="tab-btn-quiz">
        📊 Quiz Performance
    </button>

    <button class="tab-btn" onclick="switchTab('flashcards')" id="tab-btn-flashcards">
        🎴 Flashcards
    </button>
    <button class="tab-btn" onclick="switchTab('resources')" id="tab-btn-resources">
        🔗 Resources
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TAB 1 — TOPIC CHECKLIST
══════════════════════════════════════════════════════════════════ --}}
<div class="tab-panel active" id="panel-checklist">

    <div class="course-selector" id="checklist-course-selector">
        <button class="course-chip active" onclick="filterCourse('all', this, 'checklist')" id="chip-checklist-all">All Courses</button>
        @foreach($courses as $courseData)
        <button class="course-chip" onclick="filterCourse('{{ $courseData['course']->id }}', this, 'checklist')" id="chip-checklist-{{ $courseData['course']->id }}">
            {{ $courseData['course']->code }}
        </button>
        @endforeach
    </div>

    @foreach($courses as $courseData)
    @php $c = $courseData['course']; @endphp
    <div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;" data-checklist-course="{{ $c->id }}">
        <div class="topic-header">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.3rem;">
                    {{ $c->code }} &bull; Section {{ $courseData['section']->section_number }}
                    @if($courseData['weak_count'] > 0)
                        <span style="color: #fb923c; margin-left: 0.5rem;">⚠ {{ $courseData['weak_count'] }} weak quiz area{{ $courseData['weak_count'] > 1 ? 's' : '' }}</span>
                    @endif
                </div>
                <h2 class="topic-title">{{ $c->title }}</h2>
            </div>
            <div class="progress-wrap">
                <div class="progress-track">
                    <div class="progress-fill" id="pf-{{ $c->id }}"></div>
                </div>
                <span id="pt-{{ $c->id }}" style="min-width: 34px;">0%</span>
            </div>
        </div>

        @forelse($courseData['topics'] as $topicData)
        @php
            $tag   = $topicData['tag'];
            $score = $topicData['priority_score'];
            $weak  = $topicData['is_weak'];
            $urg   = $topicData['urgency'];
        @endphp
        <div class="topic-item {{ $weak ? 'weak-flag' : '' }}"
             data-course="{{ $c->id }}"
             onclick="toggleTopic(this, '{{ $c->id }}')"
             id="topic-{{ $tag->id }}">
            <div class="checkbox-circle"></div>
            <span class="topic-name">{{ $tag->name }}</span>
            <div class="topic-badges">
                @if($score >= 6)
                    <span class="priority-badge badge-critical">🔥 Critical</span>
                @elseif($score >= 3)
                    <span class="priority-badge badge-high">⚡ High</span>
                @else
                    <span class="priority-badge badge-normal">Normal</span>
                @endif
                @if($weak)
                    <span class="priority-badge" style="background: rgba(251,146,60,0.15); color: #fb923c; border: 1px solid rgba(251,146,60,0.4);">⚠ Weak Area</span>
                @endif
            </div>
        </div>
        @empty
        <div class="no-data" style="padding: 2rem;">
            <div>No concept tags defined for this course yet.</div>
        </div>
        @endforelse
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TAB 2 — QUIZ PERFORMANCE
══════════════════════════════════════════════════════════════════ --}}
<div class="tab-panel" id="panel-quiz">

    <div class="course-selector">
        <button class="course-chip active" onclick="filterCourse('all', this, 'quiz')" id="chip-quiz-all">All Courses</button>
        @foreach($courses as $courseData)
        <button class="course-chip" onclick="filterCourse('{{ $courseData['course']->id }}', this, 'quiz')" id="chip-quiz-{{ $courseData['course']->id }}">
            {{ $courseData['course']->code }}
        </button>
        @endforeach
    </div>

    @foreach($courses as $courseData)
    @php $c = $courseData['course']; @endphp
    <div data-quiz-course="{{ $c->id }}" style="margin-bottom: 2.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h2 style="font-size: 1.2rem; font-weight: 600;">{{ $c->code }} — {{ $c->title }}</h2>
            @if($courseData['avg_pct'] !== null)
            <div class="gpa-row" style="margin: 0; padding: 0.5rem 1rem; gap: 0.75rem;">
                <span style="font-size: 0.8rem; color: var(--text-dim);">Course Quiz Avg</span>
                <span style="font-size: 1.1rem; font-weight: 700; color: {{ $courseData['avg_pct'] >= 60 ? '#4ade80' : '#f87171' }};">{{ $courseData['avg_pct'] }}%</span>
            </div>
            @endif
        </div>

        @forelse($courseData['quiz_rows'] as $qr)
        @php
            $pct  = $qr['pct'];
            $bl   = $qr['badge_label'];
            $bc   = $qr['badge_color'];
            $bi   = $qr['badge_icon'];
            $barW = $pct !== null ? $pct : 0;
        @endphp
        <div class="quiz-perf-card {{ $qr['is_weak'] ? 'weak-card' : '' }}">
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.2rem;">{{ $qr['quiz']->title }}</div>
                <div style="font-size: 0.78rem; color: var(--text-dim); margin-bottom: 0.5rem;">
                    Due {{ $qr['quiz']->due_date->format('M j, Y') }} &bull;
                    Weight {{ $qr['quiz']->weight }}% &bull;
                    {{ $qr['days_ago'] }}
                </div>
                @if($pct !== null)
                <div class="quiz-bar-wrap">
                    <div class="quiz-bar-track">
                        <div class="quiz-bar-fill" style="width: {{ $barW }}%; background: {{ $bc }};"></div>
                    </div>
                </div>
                @endif
            </div>
            <div class="quiz-score-display">
                @if($qr['is_missing'])
                    <div class="quiz-score-number" style="color: #f87171; font-size: 1.2rem;">Missing</div>
                @elseif($pct !== null)
                    <div class="quiz-score-number" style="color: {{ $bc }};">{{ $pct }}%</div>
                @else
                    <div class="quiz-score-number" style="color: var(--text-dim); font-size: 1.1rem;">Not Graded</div>
                @endif
                <div>
                    <span class="perf-badge-sm" style="background: {{ $bc }}20; color: {{ $bc }}; border: 1px solid {{ $bc }}50;">
                        {{ $bi }} {{ $bl }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="no-data" style="padding: 2rem;">No quiz assignments in this section yet.</div>
        @endforelse
    </div>
    @endforeach
</div>


{{-- ══════════════════════════════════════════════════════════════════
     TAB 4 — FLASHCARDS
══════════════════════════════════════════════════════════════════ --}}
<div class="tab-panel" id="panel-flashcards">

    @php
        // Flatten all topics across all courses into JS-consumable structure
        $allCards = [];
        foreach ($courses as $courseData) {
            foreach ($courseData['topics'] as $t) {
                $allCards[] = [
                    'name'       => $t['tag']->name,
                    'course'     => $courseData['course']->code,
                    'definition' => $t['definition'],
                    'is_weak'    => $t['is_weak'],
                ];
            }
        }
    @endphp

    @if(empty($allCards))
    <div class="no-data glass-panel" style="padding: 4rem;">
        <div class="no-data-icon">🎴</div>
        <div>No topics available for flashcards yet. Your instructor needs to add concept tags.</div>
    </div>
    @else
    <div class="flashcard-shell">

        <div class="flashcard-controls">
            <button class="fc-nav-btn" onclick="prevCard()" id="fc-prev">←</button>
            <div class="fc-counter">
                <span id="fc-current">1</span> / <span id="fc-total">{{ count($allCards) }}</span>
            </div>
            <button class="fc-nav-btn" onclick="nextCard()" id="fc-next">→</button>
        </div>

        <div class="flashcard-viewport" onclick="flipCard()">
            <div class="flashcard-inner" id="flashcard">
                <div class="flashcard-face card-front">
                    <div class="card-label">Topic — Click to reveal definition</div>
                    <div class="card-topic-name" id="fc-topic-name">Loading…</div>
                    <div class="card-course-label" id="fc-course-label"></div>
                    <div class="card-hint">Click to flip ↻</div>
                </div>
                <div class="flashcard-face card-back">
                    <div class="card-label" style="color: rgba(168,85,247,0.7);" id="fc-back-label">Definition</div>
                    <div class="card-definition" id="fc-definition">Loading…</div>
                    <div id="fc-wiki-badge" style="display:none; margin-top: 0.75rem;">
                        <a id="fc-wiki-link" href="#" target="_blank" rel="noopener"
                           style="font-size: 0.72rem; color: rgba(168,85,247,0.7); text-decoration: none;
                                  border: 1px solid rgba(168,85,247,0.3); padding: 0.2rem 0.65rem;
                                  border-radius: 999px; transition: all 0.2s;"
                           onmouseover="this.style.color='#a855f7'" onmouseout="this.style.color='rgba(168,85,247,0.7)'">
                            📖 Read on Wikipedia
                        </a>
                    </div>
                    <div class="card-hint" style="color: rgba(168,85,247,0.5);">Click to flip back ↺</div>
                </div>
            </div>
        </div>

        <div class="fc-actions">
            <button class="fc-action-btn fc-know" onclick="markCard('known')">✓ I Know This</button>
            <button class="fc-action-btn fc-review" onclick="markCard('review')">↩ Review Again</button>
        </div>

        <div class="fc-progress-dots" id="fc-dots"></div>

        {{-- Summary bar --}}
        <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 1.5rem; font-size: 0.82rem; color: var(--text-dim);">
            <span>✓ Known: <b id="fc-known-count" style="color: #4ade80;">0</b></span>
            <span>↩ Review: <b id="fc-review-count" style="color: #fb923c;">0</b></span>
            <span>Remaining: <b id="fc-remaining" style="color: var(--primary-neon);">{{ count($allCards) }}</b></span>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TAB 5 — RESOURCES
══════════════════════════════════════════════════════════════════ --}}
<div class="tab-panel" id="panel-resources">

    <div class="course-selector">
        <button class="course-chip active" onclick="filterCourse('all', this, 'res')" id="chip-res-all">All Topics</button>
        @foreach($courses as $courseData)
        <button class="course-chip" onclick="filterCourse('{{ $courseData['course']->id }}', this, 'res')" id="chip-res-{{ $courseData['course']->id }}">
            {{ $courseData['course']->code }}
        </button>
        @endforeach
    </div>

    @foreach($courses as $courseData)
    @php $c = $courseData['course']; @endphp
    <div data-res-course="{{ $c->id }}" style="margin-bottom: 2.5rem;">
        <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1rem;">
            {{ $c->code }} — {{ $c->title }}
        </div>
        <div class="resource-grid">
            @forelse($courseData['topics'] as $topicData)
            @php $tag = $topicData['tag']; $res = $topicData['resources']; @endphp
            <div class="resource-card">
                <div class="resource-card-header">
                    <div class="resource-topic">{{ $tag->name }}</div>
                    @if($topicData['is_weak'])
                    <span style="font-size: 0.72rem; color: #fb923c; background: rgba(251,146,60,0.1); border: 1px solid rgba(251,146,60,0.3); padding: 0.15rem 0.55rem; border-radius: 999px; font-weight: 600;">⚠ Weak Area</span>
                    @endif
                </div>
                <div class="resource-links">
                    @if(isset($res['video']))
                    <a href="{{ $res['video'] }}" target="_blank" rel="noopener" class="resource-link">🎥 Video</a>
                    @endif
                    @if(isset($res['wiki']))
                    <a href="{{ $res['wiki'] }}" target="_blank" rel="noopener" class="resource-link">📖 Wikipedia</a>
                    @endif
                    @if(isset($res['reading']))
                    <a href="{{ $res['reading'] }}" target="_blank" rel="noopener" class="resource-link">📚 Reading</a>
                    @endif
                    @if(isset($res['practice']))
                    <a href="{{ $res['practice'] }}" target="_blank" rel="noopener" class="resource-link">🧪 Practice</a>
                    @endif
                </div>
            </div>
            @empty
            <div class="no-data" style="padding: 2rem;">No topics defined for this course.</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

@endif {{-- end main @if --}}

@endsection

@section('extra_js')
<script>
// ══════════════════════════════════════════════════════
// TAB SWITCHING
// ══════════════════════════════════════════════════════
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    document.getElementById('tab-btn-' + name).classList.add('active');
    if (name === 'flashcards') initFlashcards();
}

// ══════════════════════════════════════════════════════
// COURSE FILTER
// ══════════════════════════════════════════════════════
function filterCourse(courseId, btn, prefix) {
    // Update active chip
    document.querySelectorAll(`#chip-${prefix}-all, [id^="chip-${prefix}-"]`).forEach(c => c.classList.remove('active'));
    btn.classList.add('active');

    const attr = `data-${prefix === 'checklist' ? 'checklist' : (prefix === 'quiz' ? 'quiz' : 'res')}-course`;
    document.querySelectorAll(`[${attr}]`).forEach(el => {
        el.style.display = courseId === 'all' || el.getAttribute(attr) === courseId ? '' : 'none';
    });
}

// ══════════════════════════════════════════════════════
// TOPIC CHECKLIST
// ══════════════════════════════════════════════════════
function toggleTopic(el, courseId) {
    el.classList.toggle('completed');
    updateProgress(courseId);
}

function updateProgress(courseId) {
    const container = document.querySelector(`[data-checklist-course="${courseId}"]`);
    if (!container) return;
    const all       = container.querySelectorAll('.topic-item');
    const done      = container.querySelectorAll('.topic-item.completed');
    const pct       = all.length ? Math.round((done.length / all.length) * 100) : 0;
    const fill      = document.getElementById('pf-' + courseId);
    const text      = document.getElementById('pt-' + courseId);
    if (fill) fill.style.width = pct + '%';
    if (text) text.textContent = pct + '%';
}

// ══════════════════════════════════════════════════════
// FLASHCARDS
// ══════════════════════════════════════════════════════
const CARDS = @json($allCards ?? []);
let fcIndex   = 0;
let fcFlipped = false;
const fcState = {}; // { index: 'known' | 'review' }
let fcInited  = false;

function initFlashcards() {
    if (fcInited || CARDS.length === 0) return;
    fcInited = true;
    renderCard();
    buildDots();
}

function renderCard() {
    const c = CARDS[fcIndex];
    document.getElementById('fc-topic-name').textContent   = c.name;
    document.getElementById('fc-course-label').textContent = c.course;
    document.getElementById('fc-definition').textContent   = c.definition;
    document.getElementById('fc-current').textContent      = fcIndex + 1;
    document.getElementById('fc-total').textContent        = CARDS.length;

    // Reset flip
    fcFlipped = false;
    document.getElementById('flashcard').classList.remove('flipped');

    // Dots
    document.querySelectorAll('.fc-dot').forEach((d, i) => {
        d.classList.toggle('current', i === fcIndex);
        d.classList.toggle('known',   fcState[i] === 'known');
        d.classList.toggle('review',  fcState[i] === 'review');
    });

    updateCounts();
}

function buildDots() {
    const container = document.getElementById('fc-dots');
    container.innerHTML = '';
    CARDS.forEach((_, i) => {
        const d = document.createElement('div');
        d.className = 'fc-dot' + (i === 0 ? ' current' : '');
        container.appendChild(d);
    });
}

// Per-session definition cache (avoids re-fetching the same topic)
const wikiCache = {};

function flipCard() {
    fcFlipped = !fcFlipped;
    document.getElementById('flashcard').classList.toggle('flipped', fcFlipped);

    // Fetch Wikipedia definition when flipping to the back
    if (fcFlipped) {
        fetchWikiDefinition(fcIndex);
    }
}

// ── Wikipedia API fetch ────────────────────────────────────────────────────
function fetchWikiDefinition(index) {
    const card       = CARDS[index];
    const defEl      = document.getElementById('fc-definition');
    const badgeEl    = document.getElementById('fc-wiki-badge');
    const linkEl     = document.getElementById('fc-wiki-link');
    const labelEl    = document.getElementById('fc-back-label');

    // Already cached in this session?
    if (wikiCache[index] !== undefined) {
        applyDefinition(wikiCache[index], defEl, badgeEl, linkEl, labelEl, card);
        return;
    }

    // Show spinner while fetching
    defEl.innerHTML = '<span style="opacity:0.5; font-size:0.85rem;">🔍 Fetching Wikipedia…</span>';
    badgeEl.style.display = 'none';
    labelEl.textContent   = 'Definition';

    fetch('/wiki-summary?topic=' + encodeURIComponent(card.name), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        wikiCache[index] = data;
        applyDefinition(data, defEl, badgeEl, linkEl, labelEl, card);
    })
    .catch(() => {
        wikiCache[index] = { summary: null, source: 'fallback', wiki_url: null };
        applyDefinition(wikiCache[index], defEl, badgeEl, linkEl, labelEl, card);
    });
}

function applyDefinition(data, defEl, badgeEl, linkEl, labelEl, card) {
    if (data.summary) {
        // ✅ Wikipedia result
        defEl.textContent   = data.summary;
        labelEl.textContent = 'Wikipedia Definition';
        badgeEl.style.display = 'block';
        linkEl.href         = data.wiki_url || '#';
    } else {
        // ⚡ Fallback to hardcoded definition
        defEl.textContent   = card.definition;
        labelEl.textContent = 'Definition';
        badgeEl.style.display = 'none';
    }
}

function prevCard() {
    fcIndex = (fcIndex - 1 + CARDS.length) % CARDS.length;
    renderCard();
}

function nextCard() {
    fcIndex = (fcIndex + 1) % CARDS.length;
    renderCard();
}

function markCard(status) {
    fcState[fcIndex] = status;
    nextCard();
}

function updateCounts() {
    const known   = Object.values(fcState).filter(s => s === 'known').length;
    const review  = Object.values(fcState).filter(s => s === 'review').length;
    const remaining = CARDS.length - known - review;
    document.getElementById('fc-known-count').textContent   = known;
    document.getElementById('fc-review-count').textContent  = review;
    document.getElementById('fc-remaining').textContent     = remaining;
}

// Keyboard navigation for flashcards
document.addEventListener('keydown', e => {
    const fcPanel = document.getElementById('panel-flashcards');
    if (!fcPanel.classList.contains('active')) return;
    if (e.key === 'ArrowLeft')  prevCard();
    if (e.key === 'ArrowRight') nextCard();
    if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); flipCard(); }
    if (e.key === 'k' || e.key === 'K')    markCard('known');
    if (e.key === 'r' || e.key === 'R')    markCard('review');
});
</script>
@endsection
