@extends('layouts.modern')

@section('title', 'My Attendance | BRACU HUB')

@section('extra_css')
<style>
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 2rem;
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

    .mini-stats {
        display: flex;
        gap: 1.25rem;
        margin-top: 1.5rem;
    }

    .mini-stat-item {
        text-align: center;
    }

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
<div class="page-header">
    <h1 class="page-title">Personal <span class="neon-text">Velocity</span></h1>
    <p class="page-subtitle">Monitoring your attendance synchronization with the academic hub.</p>
</div>

<div class="course-grid">
    @forelse($attendanceData as $index => $data)
        @php
            $percentage = $data['percentage'];
            $color = $percentage >= 80 ? 'var(--secondary)' : ($percentage >= 60 ? 'var(--warning)' : 'var(--danger)');
            $circumference = 2 * pi() * 42;
            $offset = $circumference - ($percentage / 100) * $circumference;
        @endphp

        <div class="glass-panel course-card">
            <div style="flex: 1;">
                <div style="font-size: 0.7rem; color: var(--text-dim); margin-bottom: 0.5rem; letter-spacing: 0.1em; text-transform: uppercase;">
                    COURSE SIGNAL {{ $data['section']->course->code }}
                </div>
                <h3 style="font-size: 1.4rem; margin-bottom: 0.25rem;">{{ $data['section']->course->title }}</h3>
                <p style="color: var(--text-dim); font-size: 0.9rem;">Section {{ $data['section']->section_number }} • ID: {{ $data['section']->id }}</p>

                <div class="mini-stats">
                    <div class="mini-stat-item">
                        <span class="mini-stat-val" style="color: var(--secondary);">{{ $data['present'] }}</span>
                        <span class="mini-stat-label">Present</span>
                    </div>
                    <div class="mini-stat-item">
                        <span class="mini-stat-val" style="color: var(--warning);">{{ $data['late'] }}</span>
                        <span class="mini-stat-label">Late</span>
                    </div>
                    <div class="mini-stat-item">
                        <span class="mini-stat-val" style="color: var(--danger);">{{ $data['absent'] }}</span>
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
