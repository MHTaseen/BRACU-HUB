@extends('layouts.modern')

@section('title', 'Academic Tracker | BRACU HUB')

@section('extra_css')
<style>
    /* Global layout grids */
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

    /* Cards */
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

    /* Deadline items */
    .deadline-list {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 1rem;
    }
    
    .deadline-list::-webkit-scrollbar {
        width: 6px;
    }
    
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

    .deadline-item:last-child {
        border-bottom: none;
    }

    /* Circle Graphs */
    .stat-circle {
        position: relative;
        width: 100px;
        height: 100px;
    }

    .circle-svg {
        transform: rotate(-90deg);
        width: 100%;
        height: 100%;
    }

    .circle-bg {
        fill: none;
        stroke: rgba(255, 255, 255, 0.05);
        stroke-width: 8;
    }

    .circle-progress {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease-out;
    }

    .percentage-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.25rem;
        font-weight: 700;
    }

    /* Large Global Circle */
    .stat-circle-lg { width: 150px; height: 150px; margin: 0 auto; }
    .stat-circle-lg .circle-bg { stroke-width: 12; }
    .stat-circle-lg .circle-progress { stroke-width: 12; }
    .stat-circle-lg .percentage-text { font-size: 2.2rem; }

    /* Mini Stats in Course Card */
    .mini-stats {
        display: flex;
        gap: 1.25rem;
        margin-top: 1.5rem;
    }

    .mini-stat-item { text-align: center; }

    .mini-stat-val {
        display: block;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .mini-stat-label {
        font-size: 0.65rem;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Personal <span class="neon-text">Academic Tracker</span></h1>
    <p class="page-subtitle">Your unified command center for upcoming deadlines and velocity.</p>
</div>

<!-- Top Section: Global Health & Upcoming Deadlines -->
<div class="top-panel-grid">
    <!-- Health Metric -->
    <div class="glass-panel dashboard-card" style="text-align: center;">
        <h3 style="color: var(--text-dim); font-size: 1.1rem; margin-bottom: 1.5rem; letter-spacing: 0.1em; text-transform: uppercase;">
            Overall Academic Health
        </h3>
        
        @php
            $globalColor = $globalHealth >= 80 ? 'var(--primary-neon)' : ($globalHealth >= 60 ? '#fbbf24' : '#ef4444');
            $circumference = 2 * pi() * 40; // Approx based on viewbox
            $offsetLg = $circumference - ($globalHealth / 100) * $circumference;
        @endphp
        
        <div class="stat-circle stat-circle-lg">
            <svg class="circle-svg" viewBox="0 0 100 100">
                <circle class="circle-bg" cx="50" cy="50" r="40" />
                <circle class="circle-progress" cx="50" cy="50" r="40" 
                        style="stroke: {{ $globalColor }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $offsetLg }};" />
            </svg>
            <div class="percentage-text" style="color: {{ $globalColor }};">{{ $globalHealth }}%</div>
        </div>
        <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 1rem;">Average System Attendance</p>
    </div>

    <!-- Deadlines Panel -->
    <div class="glass-panel dashboard-card" style="justify-content: flex-start;">
        <h3 style="color: var(--primary-neon); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            Upcoming Deadlines
            <span style="font-size: 0.85rem; padding: 0.25rem 0.75rem; background: rgba(34, 211, 238, 0.1); border-radius: 999px;">{{ $deadlines->count() }} Tasks</span>
        </h3>
        
        <div class="deadline-list">
            @forelse($deadlines as $task)
                @php
                    $submission = $task->submissions->first();
                    $isOverdue = !$submission && $task->due_date < now();
                    $isUrgent = !$submission && !$isOverdue && $task->due_date->diffInHours(now()) <= 48;
                    $isLateSubmission = $submission && $submission->created_at > $task->due_date;
                @endphp
                <div class="deadline-item">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                            {{ $task->section->course->code }} • {{ ucfirst($task->type) ?? 'Task' }}
                        </div>
                        <div style="font-weight: 600; font-size: 1.1rem; color: var(--text-main);">
                            {{ $task->title }}
                        </div>
                        <div style="font-size: 0.85rem; margin-top: 0.25rem; color: {{ $isUrgent || $isOverdue ? '#ef4444' : 'var(--text-dim)' }};">
                            Due: {{ $task->due_date->format('M d, Y h:i A') }} ({{ $task->due_date->diffForHumans() }})
                        </div>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                        @if($submission)
                            @if($isLateSubmission)
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(249, 115, 22, 0.1); color: #f97316; font-size: 0.75rem; font-weight: 700; border: 1px solid #f97316;">LATE SUBMISSION</span>
                            @else
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.75rem; font-weight: 700; border: 1px solid #10b981;">SUBMITTED</span>
                            @endif
                            <a href="{{ route('submissions.show', $submission->id) }}" style="padding: 0.3rem 0.8rem; border-radius: 6px; background: rgba(255,255,255,0.1); color: white; font-size: 0.7rem; font-weight: 600; text-decoration: none; border: 1px solid var(--glass-border);">VIEW SUBMISSION</a>
                        @else
                            @if($isOverdue)
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 0.75rem; font-weight: 700; border: 1px solid #ef4444;">OVERDUE</span>
                            @else
                                <span style="padding: 0.4rem 1rem; border-radius: 999px; background: rgba(34, 211, 238, 0.1); color: var(--primary-neon); font-size: 0.75rem; font-weight: 700; border: 1px solid var(--primary-neon);">PENDING</span>
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

<!-- Bottom Section: Detailed Sector Syncs -->
<h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem;">
    Personal Velocity <span style="font-size: 1rem; color: var(--text-dim); font-weight: 400;">(Attendance Log)</span>
</h2>

<div class="course-grid">
    @forelse($attendanceData as $index => $data)
        @php
            $percentage = $data['percentage'];
            $color = $percentage >= 80 ? '#10b981' : ($percentage >= 60 ? '#fbbf24' : '#ef4444');
            $circumference = 2 * pi() * 42;
            $offset = $circumference - ($percentage / 100) * $circumference;
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
                    <circle class="circle-bg" cx="50" cy="50" r="42" />
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
