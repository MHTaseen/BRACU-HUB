@extends('layouts.modern')

@section('title', 'Quiz Grades | BRACU HUB')

@section('extra_css')
<style>
    /* ── Page Layout ─────────────────────────────────── */
    .grades-hero {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-block {
        margin-bottom: 3rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .section-pill {
        background: rgba(168, 85, 247, 0.15);
        border: 1px solid rgba(168, 85, 247, 0.4);
        color: var(--faculty-neon);
        border-radius: 999px;
        padding: 0.3rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* ── Quiz Group Card ─────────────────────────────── */
    .quiz-group {
        margin-bottom: 1.5rem;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        background: rgba(255,255,255,0.03);
    }

    .quiz-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: rgba(168, 85, 247, 0.08);
        border-bottom: 1px solid var(--glass-border);
        cursor: pointer;
        user-select: none;
    }

    .quiz-group-header:hover {
        background: rgba(168, 85, 247, 0.14);
    }

    .quiz-group-name {
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .quiz-type-tag {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        background: rgba(168, 85, 247, 0.2);
        color: var(--faculty-neon);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .quiz-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.8rem;
        color: var(--text-dim);
    }

    .toggle-icon {
        transition: transform 0.3s;
        color: var(--text-dim);
        font-size: 1.2rem;
    }

    .quiz-group.collapsed .toggle-icon { transform: rotate(-90deg); }

    /* ── Grade Table ─────────────────────────────────── */
    .grade-table {
        width: 100%;
        border-collapse: collapse;
    }

    .grade-table th {
        padding: 0.75rem 1.5rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-dim);
        border-bottom: 1px solid var(--glass-border);
    }

    .grade-table td {
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .grade-table tr:last-child td {
        border-bottom: none;
    }

    .grade-table tr:hover td {
        background: rgba(255,255,255,0.02);
    }

    /* ── Student Info ────────────────────────────────── */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(168,85,247,0.4), rgba(34,211,238,0.4));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        border: 1px solid var(--glass-border);
    }

    .student-name { font-weight: 500; }
    .student-email { font-size: 0.75rem; color: var(--text-dim); }

    /* ── Submission Status ───────────────────────────── */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
    }

    .status-submitted {
        background: rgba(34, 211, 238, 0.1);
        color: #22d3ee;
        border: 1px solid rgba(34,211,238,0.3);
    }

    .status-missing {
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
        border: 1px solid rgba(248,113,113,0.3);
    }

    /* ── Grade Input Row ─────────────────────────────── */
    .grade-input-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .grade-input {
        width: 70px;
        padding: 0.4rem 0.6rem;
        background: rgba(255,255,255,0.07);
        border: 1px solid var(--glass-border);
        border-radius: 8px;
        color: var(--text-main);
        font-size: 0.9rem;
        font-family: 'Outfit', sans-serif;
        text-align: center;
        transition: border-color 0.2s;
    }

    .grade-input:focus {
        outline: none;
        border-color: var(--faculty-neon);
        box-shadow: 0 0 0 3px rgba(168,85,247,0.15);
    }

    .divider-slash {
        color: var(--text-dim);
        font-size: 0.9rem;
    }

    .save-btn {
        padding: 0.4rem 0.9rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        font-family: 'Outfit', sans-serif;
        cursor: pointer;
        border: 1px solid var(--faculty-neon);
        background: rgba(168, 85, 247, 0.1);
        color: var(--faculty-neon);
        transition: all 0.2s;
        white-space: nowrap;
    }

    .save-btn:hover {
        background: var(--faculty-neon);
        color: white;
        box-shadow: 0 0 12px rgba(168,85,247,0.4);
    }

    .save-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* ── Performance Badge ───────────────────────────── */
    .perf-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
    }

    /* ── Toast ───────────────────────────────────────── */
    .toast-container {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        z-index: 9999;
    }

    .toast {
        padding: 0.85rem 1.25rem;
        border-radius: 12px;
        font-size: 0.88rem;
        font-weight: 500;
        box-shadow: 0 4px 24px rgba(0,0,0,0.4);
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        max-width: 320px;
    }

    .toast-success {
        background: rgba(74, 222, 128, 0.15);
        border: 1px solid rgba(74, 222, 128, 0.4);
        color: #4ade80;
    }

    .toast-error {
        background: rgba(248, 113, 113, 0.15);
        border: 1px solid rgba(248, 113, 113, 0.4);
        color: #f87171;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }

    /* ── Empty State ─────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1.25rem;
        filter: drop-shadow(0 0 20px rgba(168,85,247,0.3));
    }
</style>
@endsection

@section('content')
<div class="grades-hero">
    <h1 class="page-title">Quiz <span class="neon-text" style="color: var(--faculty-neon); text-shadow: 0 0 10px rgba(168,85,247,0.5);">Grades</span></h1>
    <p class="page-subtitle">Enter marks for quiz submissions — scores are instantly reflected in the Smart Revision Planner.</p>
</div>

@if(empty($gradingData))
    <div class="glass-panel empty-state">
        <div class="empty-icon">📋</div>
        <h3 style="font-size: 1.5rem; margin-bottom: 0.75rem;">No Quiz Submissions Yet</h3>
        <p style="color: var(--text-dim); max-width: 400px; margin: 0 auto;">
            There are no quiz-type assignments in your sections, or no students have submitted yet.
            Create a Quiz assignment to start grading.
        </p>
    </div>
@else
    @foreach($gradingData as $sectionData)
    <div class="section-block">
        <div class="section-header">
            <span class="section-pill">{{ $sectionData['section']->course->code }}</span>
            <h2 class="section-title">{{ $sectionData['section']->course->title }}</h2>
            <span style="color: var(--text-dim); font-size: 0.85rem;">Section {{ $sectionData['section']->section_number }}</span>
        </div>

        @foreach($sectionData['quiz_rows'] as $quizData)
        @php
            $quiz = $quizData['quiz'];
            $quizId = $quiz->id;
            $totalDefault = $quizData['total_marks'];
        @endphp
        <div class="quiz-group glass-panel" id="quiz-group-{{ $quizId }}">
            <div class="quiz-group-header" onclick="toggleGroup({{ $quizId }})">
                <div class="quiz-group-name">
                    <span class="quiz-type-tag">Quiz</span>
                    {{ $quiz->title }}
                </div>
                <div class="quiz-meta">
                    <span>Due: {{ $quiz->due_date->format('M j, Y') }}</span>
                    <span>Weight: {{ $quiz->weight }}%</span>
                    <span>{{ count($quizData['rows']) }} students</span>
                    <span class="toggle-icon">▼</span>
                </div>
            </div>

            <div class="quiz-group-body" id="quiz-body-{{ $quizId }}">
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Submission</th>
                            <th>Marks (Obtained / Total)</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quizData['rows'] as $row)
                        @php
                            $student    = $row['student'];
                            $sub        = $row['submission'];
                            $graded     = $row['graded'];
                            $pct        = $row['pct'];
                            $initials   = strtoupper(substr($student->name, 0, 1)) . strtoupper(substr(strrchr($student->name, ' ') ?: $student->name, -1));
                            $obtDefault = $graded ? $sub->marks_obtained : '';
                            $totDefault = $graded ? $sub->total_marks : $totalDefault;

                            if ($pct === null)   { $bl = 'Not Graded'; $bc = '#64748b'; $bi = '—'; }
                            elseif ($pct >= 80)  { $bl = 'Excellent';  $bc = '#4ade80'; $bi = '🔥'; }
                            elseif ($pct >= 60)  { $bl = 'Passed';     $bc = '#22d3ee'; $bi = '✅'; }
                            elseif ($pct >= 40)  { $bl = 'Needs Work'; $bc = '#fb923c'; $bi = '⚠️'; }
                            else                 { $bl = 'Critical';   $bc = '#f87171'; $bi = '❌'; }
                        @endphp
                        <tr id="row-{{ $quizId }}-{{ $student->id }}">
                            {{-- Student --}}
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">{{ $initials }}</div>
                                    <div>
                                        <div class="student-name">{{ $student->name }}</div>
                                        <div class="student-email">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Submission Status --}}
                            <td>
                                @if($sub)
                                    <span class="status-pill status-submitted">✓ Submitted</span>
                                @else
                                    <span class="status-pill status-missing">✗ Missing</span>
                                @endif
                            </td>

                            {{-- Grade Inputs --}}
                            <td>
                                <div class="grade-input-group">
                                    <input
                                        type="number"
                                        class="grade-input"
                                        id="obt-{{ $quizId }}-{{ $student->id }}"
                                        placeholder="0"
                                        value="{{ $obtDefault }}"
                                        min="0"
                                        step="0.5"
                                    >
                                    <span class="divider-slash">/</span>
                                    <input
                                        type="number"
                                        class="grade-input"
                                        id="tot-{{ $quizId }}-{{ $student->id }}"
                                        placeholder="100"
                                        value="{{ $totDefault }}"
                                        min="1"
                                        step="0.5"
                                    >
                                    <button
                                        class="save-btn"
                                        id="save-{{ $quizId }}-{{ $student->id }}"
                                        onclick="saveGrade({{ $quizId }}, {{ $student->id }})"
                                    >Save</button>
                                </div>
                            </td>

                            {{-- Performance --}}
                            <td>
                                <span
                                    class="perf-badge"
                                    id="badge-{{ $quizId }}-{{ $student->id }}"
                                    style="background: {{ $bc }}20; color: {{ $bc }}; border: 1px solid {{ $bc }}50;"
                                >{{ $bi }} {{ $bl }}</span>
                                <span
                                    id="pct-text-{{ $quizId }}-{{ $student->id }}"
                                    style="font-size: 0.8rem; color: var(--text-dim); margin-left: 0.5rem;"
                                >{{ $pct !== null ? $pct . '%' : '' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
@endif

{{-- Toast container --}}
<div class="toast-container" id="toastContainer"></div>
@endsection

@section('extra_js')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Toggle quiz card collapse ──────────────────────────────────────────────
function toggleGroup(quizId) {
    const group = document.getElementById('quiz-group-' + quizId);
    const body  = document.getElementById('quiz-body-' + quizId);
    group.classList.toggle('collapsed');
    body.style.display = group.classList.contains('collapsed') ? 'none' : '';
}

// ── Save grade via AJAX ────────────────────────────────────────────────────
function saveGrade(quizId, studentId) {
    const obt     = parseFloat(document.getElementById('obt-' + quizId + '-' + studentId).value);
    const tot     = parseFloat(document.getElementById('tot-' + quizId + '-' + studentId).value);
    const saveBtn = document.getElementById('save-' + quizId + '-' + studentId);

    if (isNaN(obt) || isNaN(tot)) {
        showToast('Please enter valid marks.', 'error');
        return;
    }

    if (obt > tot) {
        showToast('Marks obtained cannot exceed total marks.', 'error');
        return;
    }

    saveBtn.disabled    = true;
    saveBtn.textContent = '…';

    fetch('/assignments/' + quizId + '/grade', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            student_id:     studentId,
            marks_obtained: obt,
            total_marks:    tot,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update badge
            const badge   = document.getElementById('badge-' + quizId + '-' + studentId);
            const pctText = document.getElementById('pct-text-' + quizId + '-' + studentId);

            badge.textContent = getBadgeIcon(data.pct) + ' ' + data.badge_label;
            badge.style.background = data.badge_color + '20';
            badge.style.color      = data.badge_color;
            badge.style.border     = '1px solid ' + data.badge_color + '50';

            pctText.textContent = data.pct + '%';

            showToast('✓ Grade saved successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to save grade.', 'error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'))
    .finally(() => {
        saveBtn.disabled    = false;
        saveBtn.textContent = 'Save';
    });
}

function getBadgeIcon(pct) {
    if (pct === null) return '—';
    if (pct >= 80)   return '🔥';
    if (pct >= 60)   return '✅';
    if (pct >= 40)   return '⚠️';
    return '❌';
}

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast     = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity    = '0';
        toast.style.transform  = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Allow pressing Enter in grade inputs to trigger save
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                const [, quizId, studentId] = input.id.split('-');
                saveGrade(quizId, studentId);
            }
        });
    });
});
</script>
@endsection
