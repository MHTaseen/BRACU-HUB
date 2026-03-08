@extends('layouts.modern')

@section('title', 'Academic Calendar | BRACU HUB')

@section('extra_css')
<style>
    .event-card {
        padding: 2rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .event-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 255, 255, 0.2);
    }
    .event-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-neon);
    }
    .event-exam::before { background: var(--danger); }
    .event-holiday::before { background: var(--secondary); }
    .event-deadline::before { background: var(--warning); }

    .event-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 2rem;
    }

    .status-badge {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: inline-block;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .date-box {
        background: rgba(0, 0, 0, 0.2);
        padding: 0.75rem 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Academic <span class="neon-text">Universe</span></h1>
    <p class="page-subtitle">Track upcoming exams, deadlines, and university events in real-time.</p>
</div>

<div class="event-grid">
    @forelse($events as $event)
        <div class="glass-panel event-card event-{{ strtolower($event->type) }}">
            <div class="status-badge" style="color: {{ 
                $event->type == 'exam' ? 'var(--danger)' : 
                ($event->type == 'deadline' ? 'var(--warning)' : 
                ($event->type == 'holiday' ? 'var(--secondary)' : 'var(--primary-neon)')) 
            }}">
                {{ $event->type }}
            </div>
            
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">{{ $event->title }}</h3>
            
            <div class="date-box">
                <div style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 0.25rem;">SCHEDULED FOR</div>
                <div style="font-weight: 600;">
                    {{ $event->start_date->format('M d, Y') }} 
                    <span style="color: var(--text-dim); margin: 0 0.5rem;">•</span>
                    {{ $event->start_date->format('h:i A') }}
                </div>
            </div>

            <p style="color: var(--text-dim); line-height: 1.6; font-size: 0.95rem;">
                {{ $event->description }}
            </p>
        </div>
    @empty
        <div class="glass-panel" style="grid-column: 1/-1; padding: 5rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🌌</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">The universe is quiet...</h3>
            <p style="color: var(--text-dim);">No upcoming events or deadlines detected on your radar.</p>
        </div>
    @endforelse
</div>
@endsection
