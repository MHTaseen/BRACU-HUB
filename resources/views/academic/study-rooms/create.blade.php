@extends('layouts.modern')

@section('title', 'Create Study Room')

@section('extra_css')
<style>
    .form-card {
        max-width: 720px;
        margin: 0 auto;
        padding: 2.25rem 2.5rem;
        border-radius: 28px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.09);
        box-shadow: 0 24px 60px rgba(0,0,0,0.2);
    }

    .form-group { margin-bottom: 1.6rem; }

    .form-group label {
        display: block;
        margin-bottom: 0.6rem;
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--text-main);
        letter-spacing: 0.01em;
    }

    .form-group .hint {
        font-size: 0.8rem;
        color: var(--text-dim);
        margin-top: 0.35rem;
    }

    .form-control {
        width: 100%;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.05);
        color: #fff;
        outline: none;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
        resize: vertical;
    }

    .form-control:focus {
        border-color: var(--primary-neon);
        box-shadow: 0 0 0 3px rgba(34,211,238,0.15);
    }

    .form-control::placeholder { color: rgba(148,163,184,0.6); }

    .form-control option { background: #1e293b; }

    .error-msg { color: #fca5a5; font-size: 0.82rem; margin-top: 0.4rem; }

    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.9rem;
        margin-top: 0.5rem;
    }

    .btn-primary {
        background: var(--primary-neon);
        color: #0f172a;
        padding: 0.9rem 1.8rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: transform 0.18s, box-shadow 0.18s;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(34,211,238,0.35);
    }

    .btn-secondary-link {
        background: rgba(255,255,255,0.07);
        color: #cbd5e1;
        border: 1px solid rgba(255,255,255,0.12);
        padding: 0.9rem 1.4rem;
        border-radius: 999px;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.95rem;
        transition: background 0.2s;
    }

    .btn-secondary-link:hover { background: rgba(255,255,255,0.12); }

    .char-counter {
        text-align: right;
        font-size: 0.78rem;
        color: var(--text-dim);
        margin-top: 0.3rem;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="margin-bottom: 1.75rem; text-align: left;">
    <h1 class="page-title">Create Study Room</h1>
    <p class="page-subtitle">Set up a shared space for your course section — collaborate on notes, draw on the whiteboard, and chat in real time.</p>
</div>

<div class="form-card">
    <form action="{{ route('study-rooms.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Room Name <span style="color:#ef4444;">*</span></label>
            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                value="{{ old('name') }}"
                placeholder="e.g. CSE110 Midterm Group Study"
                required
                maxlength="255"
            >
            @error('name')
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="section_id">Course Section <span style="color:#ef4444;">*</span></label>
            <select name="section_id" id="section_id" class="form-control" required>
                <option value="">— Select a section —</option>
                @foreach($sections as $section)
                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                    {{ $section->course->code }} {{ $section->course->title }} — Section {{ $section->section_number }} ({{ $section->schedule }})
                </option>
                @endforeach
            </select>
            @error('section_id')
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description <span style="color: var(--text-dim); font-weight: 400;">(optional)</span></label>
            <textarea
                name="description"
                id="description"
                class="form-control"
                rows="3"
                placeholder="What will this room focus on? e.g. Reviewing chapters 5–8 for the midterm..."
                maxlength="500"
                oninput="updateCounter(this)"
            >{{ old('description') }}</textarea>
            <div class="char-counter" id="desc-counter">0 / 500</div>
            @error('description')
                <p class="error-msg">{{ $message }}</p>
            @enderror
            <p class="hint">This appears on the room card so others know what to expect.</p>
        </div>

        <div class="form-footer">
            <a href="{{ route('study-rooms.index') }}" class="btn-secondary-link">Cancel</a>
            <button type="submit" class="btn-primary">Create Room →</button>
        </div>
    </form>
</div>
@endsection

@section('extra_js')
<script>
function updateCounter(el) {
    document.getElementById('desc-counter').textContent = el.value.length + ' / 500';
}
// Init counter on page load if old value exists
document.addEventListener('DOMContentLoaded', () => {
    const desc = document.getElementById('description');
    if (desc && desc.value) updateCounter(desc);
});
</script>
@endsection