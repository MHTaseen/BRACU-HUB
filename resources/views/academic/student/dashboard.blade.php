@extends('layouts.modern')

@section('title', 'Academic Tracker | BRACU HUB')

@section('extra_css')
<style>
    /* ── Layout grids ────────────────────────────────────────────── */
    .top-panel-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 2rem;
    }

    /* ── Dashboard cards ─────────────────────────────────────────── */
    .dashboard-card {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .course-card {
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .course-card:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: var(--primary-neon)44;
    }

    /* ── Deadline list ───────────────────────────────────────────── */
    .deadline-list {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 1rem;
    }

    .deadline-list::-webkit-scrollbar { width: 6px; }
    .deadline-list::-webkit-scrollbar-thumb {
        background: var(--glass-border);
        border-radius: 4px;
    }

    .deadline-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--glass-border);
    }
    .deadline-item:last-child { border-bottom: none; }

    /* ── Circular progress ───────────────────────────────────────── */
    .stat-circle { position: relative; width: 100px; height: 100px; }
    .circle-svg  { transform: rotate(-90deg); width: 100%; height: 100%; }
    .circle-bg   { fill: none; stroke: rgba(255,255,255,0.05); stroke-width: 8; }
    .circle-progress {
        fill: none; stroke-width: 8; stroke-linecap: round;
        transition: stroke-dashoffset 1s ease-out;
    }
    .percentage-text {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.25rem; font-weight: 700;
    }

    .stat-circle-lg { width: 150px; height: 150px; margin: 0 auto; }
    .stat-circle-lg .circle-bg       { stroke-width: 12; }
    .stat-circle-lg .circle-progress  { stroke-width: 12; }
    .stat-circle-lg .percentage-text  { font-size: 2.2rem; }

    /* ── Mini stats ──────────────────────────────────────────────── */
    .mini-stats { display: flex; gap: 1.25rem; margin-top: 1.5rem; }
    .mini-stat-item { text-align: center; }
    .mini-stat-val {
        display: block; font-size: 1.1rem; font-weight: 700; margin-bottom: 0.25rem;
    }
    .mini-stat-label {
        font-size: 0.65rem; color: var(--text-dim);
        text-transform: uppercase; letter-spacing: 0.05em;
    }

    /* ── Grade Tracker ───────────────────────────────────────────── */
    .grade-tracker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(560px, 1fr));
        gap: 2rem;
        margin-top: 0;
    }

    .grade-card { padding: 2rem; }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        border-radius: 50%;
        font-size: 1.5rem;
        font-weight: 800;
        border: 3px solid;
        flex-shrink: 0;
    }

    .progress-bar-track {
        height: 10px;
        background: rgba(255,255,255,0.06);
        border-radius: 999px;
        overflow: hidden;
        margin: 0.75rem 0;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 1.2s cubic-bezier(0.4,0,0.2,1);
    }

    .breakdown-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
        margin-top: 1.25rem;
    }
    .breakdown-table th {
        color: var(--text-dim);
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.06em;
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--glass-border);
        text-align: left;
    }
    .breakdown-table td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        color: var(--text-main);
        vertical-align: middle;
    }
    .breakdown-table tr:last-child td { border-bottom: none; }
    .breakdown-table tr:hover td { background: rgba(255,255,255,0.03); }

    .type-chip {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .hint-box {
        background: rgba(34, 211, 238, 0.06);
        border: 1px solid rgba(34, 211, 238, 0.2);
        border-radius: 10px;
        padding: 0.85rem 1.1rem;
        font-size: 0.88rem;
        color: var(--primary-neon);
        margin-top: 1rem;
        line-height: 1.5;
    }
    .hint-box.danger  { background: rgba(239,68,68,0.06); border-color: rgba(239,68,68,0.25); color: #f87171; }
    .hint-box.success { background: rgba(16,185,129,0.06); border-color: rgba(16,185,129,0.25); color: #34d399; }

    .section-divider {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--glass-border);
        padding-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Personal <span class="neon-text">Academic Tracker</span></h1>
    <p class="page-subtitle">Your unified command center — attendance, deadlines, and live grade predictions.</p>
</div>

{{-- ══ Top Section: Global Health & Upcoming Deadlines ══════════════════════ --}}
<div class="top-panel-grid">
    {{-- Health Metric --}}
    <div class="glass-panel dashboard-card" style="text-align: center;">
        <h3 style="color: var(--text-dim); font-size: 1.1rem; margin-bottom: 1.5rem; letter-spacing: 0.1em; text-transform: uppercase;">
            Overall Academic Health
        </h3>

        @php
            $globalColor  = $globalHealth >= 80 ? 'var(--primary-neon)' : ($globalHealth >= 60 ? '#fbbf24' : '#ef4444');
            $circumference = 2 * pi() * 40;
            $offsetLg     = $circumference - ($globalHealth / 100) * $circumference;
        @endphp

        <div class="stat-circle stat-circle-lg">
            <svg class="circle-svg" viewBox="0 0 100 100">
                <circle class="circle-bg"       cx="50" cy="50" r="40" />
                <circle class="circle-progress" cx="50" cy="50" r="40"
                        style="stroke: {{ $globalColor }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $offsetLg }};" />
            </svg>
            <div class="percentage-text" style="color: {{ $globalColor }};">{{ $globalHealth }}%</div>
        </div>
        <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 1rem;">Average System Attendance</p>
    </div>

    {{-- Deadlines Panel --}}
    <div class="glass-panel dashboard-card" style="justify-content: flex-start;">
        <h3 style="color: var(--primary-neon); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            Upcoming Deadlines
            <span style="font-size: 0.85rem; padding: 0.25rem 0.75rem; background: rgba(34,211,238,0.1); border-radius: 999px;">{{ $deadlines->count() }} Tasks</span>
        </h3>

        <div class="deadline-list">
            @forelse($deadlines as $task)
                @php
                    $submission       = $task->submissions->first();
                    $isOverdue        = !$submission && $task->due_date < now();
                    $isUrgent         = !$submission && !$isOverdue && $task->due_date->diffInHours(now()) <= 48;
                    $isLateSubmission = $submission && $submission->created_at > $task->due_date;
                @endphp
                <div class="deadline-item">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                            {{ $task->section->course->code }} • {{ ucfirst($task->type) ?? 'Task' }}
                        </div>
                        <div style="font-weight: 600; font-size: 1.1rem; color: var(--text-main);">{{ $task->title }}</div>
                        <div style="font-size: 0.85rem; margin-top: 0.25rem; color: {{ $isUrgent || $isOverdue ? '#ef4444' : 'var(--text-dim)' }};">
                            Due: {{ $task->due_date->format('M d, Y h:i A') }} ({{ $task->due_date->diffForHumans() }})
                        </div>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                        @if($submission)
                            @if($isLateSubmission)
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(249,115,22,0.1); color: #f97316; font-size: 0.75rem; font-weight: 700; border: 1px solid #f97316;">LATE SUBMISSION</span>
                            @else
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(16,185,129,0.1); color: #10b981; font-size: 0.75rem; font-weight: 700; border: 1px solid #10b981;">SUBMITTED</span>
                            @endif
                            <a href="{{ route('submissions.show', $submission->id) }}" style="padding: 0.3rem 0.8rem; border-radius: 6px; background: rgba(255,255,255,0.1); color: white; font-size: 0.7rem; font-weight: 600; text-decoration: none; border: 1px solid var(--glass-border);">VIEW SUBMISSION</a>
                        @else
                            @if($isOverdue)
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(239,68,68,0.1); color: #ef4444; font-size: 0.75rem; font-weight: 700; border: 1px solid #ef4444;">OVERDUE</span>
                            @else
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(34,211,238,0.1); color: var(--primary-neon); font-size: 0.75rem; font-weight: 700; border: 1px solid var(--primary-neon);">PENDING</span>
                            @endif
                            <a href="{{ route('submissions.create', $task->id) }}" style="padding: 0.4rem 1rem; border-radius: 6px; background: var(--primary-neon); color: #000; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: inline-block;">SUBMIT WORK</a>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-dim); padding: 3rem 0;">
                    <span style="font-size: 2rem; display: block; margin-bottom: 1rem;">🌴</span>
                    No upcoming deadlines! Enjoy your free time.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ Grade Progress Tracker ════════════════════════════════════════════════ --}}
<h2 class="section-divider">
    📊 Grade Progress <span style="font-size: 1rem; color: var(--text-dim); font-weight: 400;">(Predicted · Weighted Average)</span>
</h2>

@if(count($gradeData) > 0)
<div class="grade-tracker-grid">
    @foreach($gradeData as $gd)
        @php
            $pct   = $gd['predicted_pct'];
            $grade = $gd['predicted_grade'];
            $next  = $gd['next_boundary'];

            // Color palette per grade tier
            if (!$grade) {
                $gradeColor = 'var(--text-dim)';
            } elseif (in_array($grade, ['A+','A'])) {
                $gradeColor = '#10b981';   // emerald
            } elseif (in_array($grade, ['A-','B+'])) {
                $gradeColor = '#22d3ee';   // cyan
            } elseif (in_array($grade, ['B','B-'])) {
                $gradeColor = '#fbbf24';   // amber
            } elseif (in_array($grade, ['C+','C','C-'])) {
                $gradeColor = '#f97316';   // orange
            } else {
                $gradeColor = '#ef4444';   // red
            }

            // Progress of pct toward next boundary min (or 100 if A+)
            $progressTarget = $next ? $next['min'] : 100;
            $progressPct    = $pct ? min(round(($pct / $progressTarget) * 100), 100) : 0;
        @endphp

        <div class="glass-panel grade-card">
            {{-- Course header --}}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem;">
                <div style="flex: 1;">
                    <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.4rem;">
                        GRADE SIGNAL · {{ $gd['section']->course->code }}
                    </div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 0.2rem;">{{ $gd['section']->course->title }}</h3>
                    <p style="color: var(--text-dim); font-size: 0.85rem;">
                        Section {{ $gd['section']->section_number }} &nbsp;·&nbsp;
                        {{ $gd['graded_weight'] }}<span style="color: var(--text-dim);">/{{ $gd['total_weight'] }}%</span> graded
                    </p>

                    @if($pct !== null)
                        {{-- Progress bar toward next grade --}}
                        <div style="margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.3rem;">
                                <span>Current: <b style="color: {{ $gradeColor }};">{{ $pct }}%</b></span>
                                <span>Target: <b style="color: white;">{{ $next ? $next['min'].'% ('.$next['letter'].')' : 'A+ ✨' }}</b></span>
                            </div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width: {{ $progressPct }}%; background: {{ $gradeColor }};"></div>
                            </div>
                        </div>

                        {{-- Hint: marks needed --}}
                        @if($next && $gd['avg_needed_pct'] !== null)
                            @if($gd['avg_needed_pct'] <= 0)
                                <div class="hint-box success">
                                    🎯 You're already on track for <b>{{ $next['letter'] }}</b> even if you score 0 on remaining work!
                                </div>
                            @elseif($gd['avg_needed_pct'] > 100)
                                <div class="hint-box danger">
                                    ⚠️ Reaching <b>{{ $next['letter'] }}</b> is no longer mathematically possible from remaining assessments.
                                </div>
                            @else
                                <div class="hint-box">
                                    💡 Score at least <b>{{ $gd['avg_needed_pct'] }}%</b> average on remaining
                                    <b>{{ $gd['ungraded_weight'] }}%</b> weight to reach <b>{{ $next['letter'] }}</b>
                                    ({{ $next['min'] }}%)
                                </div>
                            @endif
                        @elseif(!$next)
                            <div class="hint-box success">🏆 You're predicted <b>A+</b> — the highest grade! Keep it up!</div>
                        @elseif($gd['ungraded_weight'] <= 0)
                            <div class="hint-box">All assessments have been graded. This is your final predicted grade.</div>
                        @endif
                    @else
                        <div style="color: var(--text-dim); font-size: 0.88rem; margin-top: 0.75rem; font-style: italic;">
                            ⏳ No marks recorded yet — predictions will appear once faculty grades assessments.
                        </div>
                    @endif
                </div>

                {{-- Grade Badge --}}
                <div class="grade-badge" style="color: {{ $gradeColor }}; border-color: {{ $gradeColor }}; box-shadow: 0 0 20px {{ $gradeColor }}44;">
                    {{ $grade ?? '—' }}
                </div>
            </div>

            {{-- Breakdown table --}}
            @if(count($gd['breakdown']) > 0)
                <div style="border-top: 1px solid var(--glass-border); margin-top: 1.5rem; padding-top: 1rem;">
                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th>Assessment</th>
                                <th>Type</th>
                                <th style="text-align: center;">Weight</th>
                                <th style="text-align: center;">Marks</th>
                                <th style="text-align: center;">Score%</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gd['breakdown'] as $item)
                                @php
                                    $scorePct = $item['pct_contrib'];
                                    $rowColor = $scorePct === null
                                        ? 'var(--text-dim)'
                                        : ($scorePct >= 85 ? '#10b981' : ($scorePct >= 70 ? '#fbbf24' : '#ef4444'));
                                @endphp
                                <tr>
                                    <td style="font-weight: 500;">{{ $item['title'] }}</td>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'Quiz'       => '#fbbf24',
                                                'Assignment' => '#22d3ee',
                                                'Midterm'    => '#f97316',
                                                'Final'      => '#ef4444',
                                            ];
                                            $tc = $typeColors[$item['type']] ?? '#94a3b8';
                                        @endphp
                                        <span class="type-chip" style="background: {{ $tc }}22; color: {{ $tc }}; border: 1px solid {{ $tc }}55;">
                                            {{ $item['type'] }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; color: var(--primary-neon); font-weight: 600;">{{ $item['weight'] }}%</td>
                                    <td style="text-align: center;">
                                        @if($item['marks_obtained'] !== null)
                                            <span style="color: {{ $rowColor }}; font-weight: 700;">{{ $item['marks_obtained'] }}</span>
                                            <span style="color: var(--text-dim);">/{{ $item['max_marks'] }}</span>
                                        @else
                                            <span style="color: var(--text-dim);">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: {{ $rowColor }};">
                                        {{ $scorePct !== null ? round($scorePct, 1).'%' : '—' }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($item['marks_obtained'] !== null)
                                            <span style="color: #10b981; font-size: 0.75rem; font-weight: 700;">GRADED</span>
                                        @elseif($item['submitted'])
                                            <span style="color: #fbbf24; font-size: 0.75rem; font-weight: 700;">PENDING</span>
                                        @else
                                            <span style="color: var(--text-dim); font-size: 0.75rem;">NOT SUBMITTED</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach
</div>

@else
<div class="glass-panel" style="padding: 5rem; text-align: center; margin-top: 0;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No courses found</h3>
    <p style="color: var(--text-dim);">You are not enrolled in any sections with assignments yet.</p>
</div>
@endif

{{-- ══ Attendance Log ═════════════════════════════════════════════════════════ --}}
<h2 class="section-divider" style="margin-top: 3rem;">
    Personal Velocity <span style="font-size: 1rem; color: var(--text-dim); font-weight: 400;">(Attendance Log)</span>
</h2>

<div class="course-grid">
    @forelse($attendanceData as $data)
        @php
            $percentage    = $data['percentage'];
            $color         = $percentage >= 80 ? '#10b981' : ($percentage >= 60 ? '#fbbf24' : '#ef4444');
            $circumference = 2 * pi() * 42;
            $offset        = $circumference - ($percentage / 100) * $circumference;
        @endphp

        <div class="glass-panel course-card">
            <div style="flex: 1;">
                <div style="font-size: 0.7rem; color: var(--text-dim); margin-bottom: 0.5rem; letter-spacing: 0.1em; text-transform: uppercase;">
                    COURSE SIGNAL {{ $data['section']->course->code }}
                </div>
                <h3 style="font-size: 1.4rem; margin-bottom: 0.25rem;">{{ $data['section']->course->title }}</h3>
                <p style="color: var(--text-dim); font-size: 0.9rem;">Section {{ $data['section']->section_number }}</p>

                <div class="mini-stats">
                    <div class="mini-stat-item">
                        <span class="mini-stat-val" style="color: #10b981;">{{ $data['present'] }}</span>
                        <span class="mini-stat-label">Present</span>
                    </div>
                    <div class="mini-stat-item">
                        <span class="mini-stat-val" style="color: #fbbf24;">{{ $data['late'] }}</span>
                        <span class="mini-stat-label">Late</span>
                    </div>
                    <div class="mini-stat-item">
                        <span class="mini-stat-val" style="color: #ef4444;">{{ $data['absent'] }}</span>
                        <span class="mini-stat-label">Absent</span>
                    </div>
                </div>
            </div>

            <div class="stat-circle">
                <svg class="circle-svg" viewBox="0 0 100 100">
                    <circle class="circle-bg"       cx="50" cy="50" r="42" />
                    <circle class="circle-progress" cx="50" cy="50" r="42"
                            style="stroke: {{ $color }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $offset }};" />
                </svg>
                <div class="percentage-text" style="color: {{ $color }};">{{ $percentage }}%</div>
            </div>
        </div>
    @empty
        <div class="glass-panel" style="grid-column: 1/-1; padding: 5rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🛰️</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No signals found</h3>
            <p style="color: var(--text-dim);">You are not currently enrolled in any course squadrons.</p>
        </div>
    @endforelse
</div>
@endsection
