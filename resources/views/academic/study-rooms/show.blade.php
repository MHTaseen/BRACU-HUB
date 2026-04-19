@extends('layouts.modern')

@section('title', $studyRoom->name . ' — Study Room')

@section('extra_css')
<style>
/* ============================================================
   LAYOUT
   ============================================================ */
.room-wrap {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* Top bar */
.room-topbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
}

.room-title-block h1 {
    font-size: 1.9rem;
    margin: 0 0 0.3rem;
    line-height: 1.2;
}

.room-title-block .room-meta {
    font-size: 0.88rem;
    color: var(--text-dim);
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1.25rem;
    align-items: center;
}

.room-meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.28rem 0.8rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.badge-live {
    background: rgba(34,211,238,0.12);
    color: #22d3ee;
    border: 1px solid rgba(34,211,238,0.25);
}

.badge-archived {
    background: rgba(148,163,184,0.1);
    color: #94a3b8;
    border: 1px solid rgba(148,163,184,0.2);
}

.pulse-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #22d3ee;
    animation: pulse 1.5s ease infinite;
}

@keyframes pulse {
    0%,100% { opacity:1; transform: scale(1); }
    50% { opacity:0.4; transform: scale(0.7); }
}

.room-topbar-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

/* Panels */
.room-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    grid-template-rows: auto auto;
    gap: 1.25rem;
}

.panel {
    background: rgba(255,255,255,0.055);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 16px 48px rgba(0,0,0,0.16);
    display: flex;
    flex-direction: column;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.panel-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #e2e8f0;
}

.panel-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    font-size: 1rem;
    background: rgba(34,211,238,0.1);
}

/* ============================================================
   TABS (whiteboard / notes)
   ============================================================ */
.content-tabs {
    grid-column: 1 / 2;
    grid-row: 1 / 3;
}

.tab-bar {
    display: flex;
    gap: 0.4rem;
    margin-bottom: 1rem;
    background: rgba(0,0,0,0.2);
    padding: 0.35rem;
    border-radius: 14px;
    width: fit-content;
}

.tab-btn {
    padding: 0.5rem 1.15rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    background: transparent;
    color: var(--text-dim);
    transition: background 0.2s, color 0.2s;
}

.tab-btn.active {
    background: rgba(34,211,238,0.15);
    color: var(--primary-neon);
}

.content-pane { display: none; flex: 1; flex-direction: column; }
.content-pane.active { display: flex; }

/* ============================================================
   WHITEBOARD (Canvas)
   ============================================================ */
.wb-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.6rem 0.85rem;
    background: rgba(0,0,0,0.25);
    border-radius: 12px;
}

.wb-tool-btn {
    padding: 0.45rem 0.95rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.06);
    color: #cbd5e1;
    cursor: pointer;
    transition: background 0.18s, color 0.18s, border-color 0.18s;
}

.wb-tool-btn:hover { background: rgba(255,255,255,0.12); }
.wb-tool-btn.active {
    background: rgba(34,211,238,0.2);
    color: var(--primary-neon);
    border-color: rgba(34,211,238,0.4);
}

.wb-sep { width: 1px; height: 24px; background: rgba(255,255,255,0.12); margin: 0 0.25rem; }

.wb-color-input {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 2px solid rgba(255,255,255,0.15);
    cursor: pointer;
    background: transparent;
    padding: 0;
    overflow: hidden;
}

.wb-size-input {
    width: 80px;
    accent-color: var(--primary-neon);
    cursor: pointer;
}

#wb-canvas {
    flex: 1;
    border-radius: 14px;
    background: #0d1520;
    cursor: crosshair;
    border: 1px solid rgba(255,255,255,0.1);
    display: block;
    width: 100%;
    min-height: 460px;
}

.wb-status {
    font-size: 0.75rem;
    color: var(--text-dim);
    margin-top: 0.5rem;
    text-align: right;
    height: 18px;
}

/* ============================================================
   NOTES
   ============================================================ */
#notes-editor {
    flex: 1;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(13,21,32,0.95);
    color: #e2e8f0;
    padding: 1rem;
    font-size: 0.9rem;
    line-height: 1.7;
    resize: none;
    font-family: inherit;
    min-height: 460px;
    outline: none;
    transition: border-color 0.2s;
}

#notes-editor:focus { border-color: rgba(34,211,238,0.35); }

.notes-status {
    font-size: 0.75rem;
    color: var(--text-dim);
    margin-top: 0.5rem;
    text-align: right;
    height: 18px;
}

/* ============================================================
   CHAT
   ============================================================ */
.chat-panel {
    grid-column: 2 / 3;
    grid-row: 1 / 2;
    max-height: 500px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding-right: 0.25rem;
    min-height: 320px;
    max-height: 360px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.12) transparent;
}

.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }

.chat-bubble-wrap {
    display: flex;
    flex-direction: column;
    max-width: 85%;
}

.chat-bubble-wrap.self { align-self: flex-end; align-items: flex-end; }
.chat-bubble-wrap.other { align-self: flex-start; align-items: flex-start; }

.chat-sender {
    font-size: 0.72rem;
    color: var(--text-dim);
    margin-bottom: 0.2rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.chat-avatar {
    width: 20px; height: 20px;
    border-radius: 50%;
    background: var(--primary-neon);
    color: #0f172a;
    font-size: 0.62rem;
    font-weight: 700;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.chat-bubble {
    padding: 0.55rem 0.9rem;
    border-radius: 16px;
    font-size: 0.875rem;
    line-height: 1.5;
    word-break: break-word;
}

.chat-bubble-wrap.self .chat-bubble {
    background: rgba(34,211,238,0.18);
    color: #e2e8f0;
    border-bottom-right-radius: 4px;
}

.chat-bubble-wrap.other .chat-bubble {
    background: rgba(255,255,255,0.08);
    color: #cbd5e1;
    border-bottom-left-radius: 4px;
}

.chat-time {
    font-size: 0.68rem;
    color: rgba(148,163,184,0.6);
    margin-top: 0.15rem;
}

.chat-empty {
    text-align: center;
    color: var(--text-dim);
    font-size: 0.85rem;
    padding: 2rem 0;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-input-row {
    display: flex;
    gap: 0.6rem;
    margin-top: 0.85rem;
    align-items: flex-end;
}

#chat-input {
    flex: 1;
    padding: 0.6rem 0.9rem;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.25);
    color: #fff;
    font-size: 0.875rem;
    font-family: inherit;
    outline: none;
    resize: none;
    max-height: 80px;
    transition: border-color 0.2s;
}

#chat-input:focus { border-color: rgba(34,211,238,0.35); }
#chat-input::placeholder { color: rgba(148,163,184,0.5); }

.btn-send {
    width: 38px; height: 38px;
    border-radius: 12px;
    background: var(--primary-neon);
    color: #0f172a;
    border: none;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 1rem;
    transition: transform 0.15s, box-shadow 0.15s;
    flex-shrink: 0;
}

.btn-send:hover {
    transform: scale(1.07);
    box-shadow: 0 4px 14px rgba(34,211,238,0.4);
}

/* ============================================================
   PARTICIPANTS
   ============================================================ */
.participants-panel {
    grid-column: 2 / 3;
    grid-row: 2 / 3;
}

.participants-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    max-height: 240px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.12) transparent;
}

.participant-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.55rem 0.75rem;
    border-radius: 12px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    transition: background 0.2s;
}

.participant-row:hover { background: rgba(255,255,255,0.07); }

.p-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-neon), #818cf8);
    color: #0f172a;
    font-weight: 700;
    font-size: 0.85rem;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.p-info { flex: 1; min-width: 0; }
.p-name { font-size: 0.875rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.p-time { font-size: 0.72rem; color: var(--text-dim); }

.p-online-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #4ade80;
    flex-shrink: 0;
    animation: pulse 1.8s ease infinite;
}

/* ============================================================
   SHARED BUTTONS
   ============================================================ */
.btn-action {
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: transform 0.18s, box-shadow 0.18s, background 0.2s;
}

.btn-primary { background: var(--primary-neon); color: #0f172a; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(34,211,238,0.3); }

.btn-danger { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.25); }
.btn-danger:hover { background: rgba(239,68,68,0.25); }

.btn-ghost {
    background: rgba(255,255,255,0.07);
    color: #94a3b8;
    border: 1px solid rgba(255,255,255,0.1);
}
.btn-ghost:hover { background: rgba(255,255,255,0.12); color: #cbd5e1; }

/* Archived overlay notice */
.archived-banner {
    background: rgba(251,191,36,0.1);
    border: 1px solid rgba(251,191,36,0.25);
    color: #fbbf24;
    border-radius: 14px;
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 1100px) {
    .room-grid {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
    }
    .content-tabs { grid-column: 1; grid-row: 1; }
    .chat-panel   { grid-column: 1; grid-row: 2; max-height: none; }
    .participants-panel { grid-column: 1; grid-row: 3; }
}
</style>
@endsection

@section('content')
<div class="room-wrap" id="room-wrap">

    {{-- ── TOP BAR ─────────────────────────────────────────────────────── --}}
    <div class="room-topbar">
        <div class="room-title-block">
            <h1>{{ $studyRoom->name }}</h1>
            <div class="room-meta">
                <span>{{ $studyRoom->course->code }} — {{ $studyRoom->course->title }}</span>
                <span style="color:rgba(255,255,255,0.2);">|</span>
                <span>Section {{ $studyRoom->section->section_number }}</span>
                <span style="color:rgba(255,255,255,0.2);">|</span>
                @if($studyRoom->is_active)
                <span class="room-meta-badge badge-live"><span class="pulse-dot"></span> Live</span>
                @else
                <span class="room-meta-badge badge-archived">Archived</span>
                @endif
            </div>
            @if($studyRoom->description)
            <p style="color: var(--text-dim); font-size: 0.875rem; margin-top: 0.5rem; max-width: 640px;">{{ $studyRoom->description }}</p>
            @endif
        </div>

        <div class="room-topbar-actions">
            @if($studyRoom->is_active)
                @if($studyRoom->created_by === auth()->id())
                <form action="{{ route('study-rooms.archive', $studyRoom) }}" method="POST" style="margin:0;" onsubmit="return confirm('Archive this room? All participants will be logged out.')">
                    @csrf
                    <button type="submit" class="btn-action btn-ghost">🗄 Archive Room</button>
                </form>
                @endif
                <form action="{{ route('study-rooms.leave', $studyRoom) }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-action btn-danger">← Leave Room</button>
                </form>
            @endif
            <a href="{{ route('study-rooms.index') }}" class="btn-action btn-ghost">↩ All Rooms</a>
        </div>
    </div>

    {{-- Archived banner --}}
    @unless($studyRoom->is_active)
    <div class="archived-banner">
        ⚠ This session is archived. Content is read-only.
        @if($studyRoom->archived_at)
        Archived {{ $studyRoom->archived_at->diffForHumans() }}.
        @endif
    </div>
    @endunless

    {{-- ── MAIN GRID ───────────────────────────────────────────────────── --}}
    <div class="room-grid">

        {{-- ── WHITEBOARD + NOTES (left column) ──────────────────────── --}}
        <div class="panel content-tabs">
            <div class="tab-bar">
                <button class="tab-btn active" onclick="showPane('whiteboard', this)">🖊 Whiteboard</button>
                <button class="tab-btn" onclick="showPane('notes', this)">📝 Notes</button>
            </div>

            {{-- WHITEBOARD PANE --}}
            <div id="pane-whiteboard" class="content-pane active">
                @if($studyRoom->is_active)
                <div class="wb-toolbar">
                    <button class="wb-tool-btn active" id="tool-pen" onclick="setTool('pen')">✏ Pen</button>
                    <button class="wb-tool-btn" id="tool-eraser" onclick="setTool('eraser')">⬜ Eraser</button>
                    <div class="wb-sep"></div>
                    <label style="font-size:0.78rem; color:var(--text-dim);">Color</label>
                    <input type="color" class="wb-color-input" id="wb-color" value="#22d3ee" title="Pen color">
                    <div class="wb-sep"></div>
                    <label style="font-size:0.78rem; color:var(--text-dim);">Size</label>
                    <input type="range" class="wb-size-input" id="wb-size" min="1" max="30" value="3" title="Brush size">
                    <div class="wb-sep"></div>
                    <button class="wb-tool-btn" onclick="clearCanvas()" style="color:#f87171;">🗑 Clear</button>
                </div>
                @endif

                <canvas id="wb-canvas"></canvas>
                <div class="wb-status" id="wb-status"></div>
            </div>

            {{-- NOTES PANE --}}
            <div id="pane-notes" class="content-pane">
                <textarea
                    id="notes-editor"
                    placeholder="Start taking shared notes... Changes auto-save every 5 seconds."
                    {{ $studyRoom->is_active ? '' : 'readonly' }}
                >{{ $studyRoom->notes_data ?? '' }}</textarea>
                <div class="notes-status" id="notes-status"></div>
            </div>
        </div>

        {{-- ── CHAT (right column, top) ───────────────────────────────── --}}
        <div class="panel chat-panel">
            <div class="panel-header">
                <h3 class="panel-title">
                    <span class="panel-icon">💬</span>
                    Live Chat
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-dim);" id="chat-count">0 messages</span>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="chat-empty" id="chat-empty">No messages yet. Say hello! 👋</div>
            </div>

            @if($studyRoom->is_active)
            <div class="chat-input-row">
                <textarea id="chat-input" placeholder="Type a message... (Enter to send)" rows="1"></textarea>
                <button class="btn-send" id="send-btn" title="Send message">➤</button>
            </div>
            @else
            <p style="font-size:0.8rem; color:var(--text-dim); margin-top:0.75rem; text-align:center;">Chat disabled for archived rooms.</p>
            @endif
        </div>

        {{-- ── PARTICIPANTS (right column, bottom) ────────────────────── --}}
        <div class="panel participants-panel">
            <div class="panel-header">
                <h3 class="panel-title">
                    <span class="panel-icon">👥</span>
                    Online Now
                </h3>
                <span style="font-size: 0.75rem; color: var(--text-dim);" id="participant-count">—</span>
            </div>

            <div class="participants-list" id="participants-list">
                @foreach($studyRoom->activeParticipants as $p)
                <div class="participant-row">
                    <div class="p-avatar">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                    <div class="p-info">
                        <div class="p-name">{{ $p->user->name }}</div>
                        <div class="p-time">Joined {{ $p->joined_at?->format('H:i') }}</div>
                    </div>
                    <div class="p-online-dot"></div>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /.room-grid --}}
</div>{{-- /.room-wrap --}}
@endsection

@section('extra_js')
<script>
// ============================================================
//  CONSTANTS & STATE
// ============================================================
const ROOM_ID   = {{ $studyRoom->id }};
const MY_ID     = {{ auth()->id() }};
const IS_ACTIVE = {{ $studyRoom->is_active ? 'true' : 'false' }};
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;

const ROUTES = {
    updates  : '{{ route('study-rooms.get-updates',   $studyRoom) }}',
    notes    : '{{ route('study-rooms.update-notes',  $studyRoom) }}',
    wb       : '{{ route('study-rooms.update-whiteboard', $studyRoom) }}',
    message  : '{{ route('study-rooms.send-message',  $studyRoom) }}',
};

// ============================================================
//  TAB SWITCHING (Whiteboard / Notes)
// ============================================================
function showPane(id, btn) {
    document.querySelectorAll('.content-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('pane-' + id).classList.add('active');
    btn.classList.add('active');
    if (id === 'whiteboard') resizeCanvas();
}

// ============================================================
//  CANVAS WHITEBOARD
// ============================================================
const canvas  = document.getElementById('wb-canvas');
const ctx     = canvas.getContext('2d');
let drawing   = false;
let tool      = 'pen';
let lastX = 0, lastY = 0;

function resizeCanvas() {
    const rect   = canvas.parentElement.getBoundingClientRect();
    const dpr    = window.devicePixelRatio || 1;
    const w      = rect.width;
    const h      = Math.max(460, window.innerHeight * 0.52);

    // Save current image
    const imgData = canvas.toDataURL();

    canvas.style.width  = w + 'px';
    canvas.style.height = h + 'px';
    canvas.width  = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    ctx.scale(dpr, dpr);

    // Restore
    const img = new Image();
    img.onload = () => ctx.drawImage(img, 0, 0, w, h);
    img.src = imgData;
}

function getPos(e) {
    const r   = canvas.getBoundingClientRect();
    const src = e.touches ? e.touches[0] : e;
    return [src.clientX - r.left, src.clientY - r.top];
}

function setTool(t) {
    tool = t;
    document.querySelectorAll('[id^="tool-"]').forEach(b => b.classList.remove('active'));
    document.getElementById('tool-' + t)?.classList.add('active');
    canvas.style.cursor = t === 'eraser' ? 'cell' : 'crosshair';
}

function startDraw(e) {
    if (!IS_ACTIVE) return;
    e.preventDefault();
    drawing = true;
    [lastX, lastY] = getPos(e);
}

function draw(e) {
    if (!drawing || !IS_ACTIVE) return;
    e.preventDefault();
    const [x, y] = getPos(e);
    const color  = document.getElementById('wb-color')?.value || '#22d3ee';
    const size   = parseInt(document.getElementById('wb-size')?.value || '3');

    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.strokeStyle = tool === 'eraser' ? '#0d1520' : color;
    ctx.lineWidth   = tool === 'eraser' ? size * 5 : size;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';
    ctx.stroke();
    [lastX, lastY] = [x, y];
    wbDirty = true;
}

function stopDraw(e) { drawing = false; }

function clearCanvas() {
    if (!confirm('Clear the whiteboard for everyone?')) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    wbDirty = true;
}

// Events — mouse
canvas.addEventListener('mousedown',  startDraw);
canvas.addEventListener('mousemove',  draw);
canvas.addEventListener('mouseup',    stopDraw);
canvas.addEventListener('mouseleave', stopDraw);
// Events — touch
canvas.addEventListener('touchstart', startDraw, { passive: false });
canvas.addEventListener('touchmove',  draw,      { passive: false });
canvas.addEventListener('touchend',   stopDraw);

// ============================================================
//  NOTES
// ============================================================
const notesEl = document.getElementById('notes-editor');

// ============================================================
//  DIRTY FLAGS & LAST-SERVER STATE
// ============================================================
let wbDirty     = false;
let notesDirty  = false;
let lastWb      = '';
let lastNotes   = '';
let lastChatTs  = 0;     // timestamp of newest message we've rendered

if (notesEl) {
    notesEl.addEventListener('input', () => { notesDirty = true; });
}

// ============================================================
//  SAVE FUNCTIONS
// ============================================================
function saveNotes() {
    if (!notesDirty || !notesEl || !IS_ACTIVE) return;
    notesDirty = false;
    const val  = notesEl.value;
    setStatus('notes-status', 'Saving…');
    fetch(ROUTES.notes, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ notes: val })
    }).then(r => r.json()).then(() => setStatus('notes-status', 'Saved ✓', true));
}

function saveWhiteboard() {
    if (!wbDirty || !IS_ACTIVE) return;
    wbDirty = false;
    const data = canvas.toDataURL('image/png');
    setStatus('wb-status', 'Saving…');
    fetch(ROUTES.wb, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ whiteboard: data })
    }).then(r => r.json()).then(() => setStatus('wb-status', 'Saved ✓', true));
}

function setStatus(id, txt, fade = false) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = txt;
    if (fade) setTimeout(() => { el.textContent = ''; }, 3000);
}

// ============================================================
//  CHAT
// ============================================================
function renderChat(messages) {
    const container = document.getElementById('chat-messages');
    const emptyEl   = document.getElementById('chat-empty');
    if (!container) return;

    // Find messages newer than what we've rendered
    const newMsgs = messages.filter(m => (m.ts || 0) > lastChatTs);
    if (newMsgs.length === 0) return;

    if (emptyEl) emptyEl.remove();

    newMsgs.forEach(msg => {
        const isSelf = msg.user_id === MY_ID;
        const wrap = document.createElement('div');
        wrap.className = 'chat-bubble-wrap ' + (isSelf ? 'self' : 'other');
        wrap.innerHTML = `
            <div class="chat-sender">
                <div class="chat-avatar">${escHtml(msg.initial)}</div>
                ${escHtml(msg.name)}
            </div>
            <div class="chat-bubble">${escHtml(msg.text)}</div>
            <div class="chat-time">${escHtml(msg.time)}</div>
        `;
        container.appendChild(wrap);
        if (msg.ts) lastChatTs = Math.max(lastChatTs, msg.ts);
    });

    // Update count label
    const countEl = document.getElementById('chat-count');
    if (countEl) countEl.textContent = messages.length + ' message' + (messages.length !== 1 ? 's' : '');

    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    if (!input || !IS_ACTIVE) return;
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    input.style.height = 'auto';

    fetch(ROUTES.message, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ message: text })
    }).then(r => r.json()).then(() => poll()); // immediate poll to show own message
}

// Chat input events
const chatInput = document.getElementById('chat-input');
if (chatInput) {
    // Auto-resize textarea
    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 80) + 'px';
    });
    // Send on Enter (Shift+Enter for newline)
    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
}

const sendBtn = document.getElementById('send-btn');
if (sendBtn) sendBtn.addEventListener('click', sendMessage);

// ============================================================
//  PARTICIPANTS
// ============================================================
function renderParticipants(list) {
    const container = document.getElementById('participants-list');
    const countEl   = document.getElementById('participant-count');
    if (!container) return;

    container.innerHTML = '';
    list.forEach(p => {
        const row = document.createElement('div');
        row.className = 'participant-row';
        row.innerHTML = `
            <div class="p-avatar">${escHtml(p.initial)}</div>
            <div class="p-info">
                <div class="p-name">${escHtml(p.name)}${p.id === MY_ID ? ' <span style="color:var(--primary-neon);font-size:0.72rem;">(you)</span>' : ''}</div>
                <div class="p-time">Since ${escHtml(p.joined_at)}</div>
            </div>
            <div class="p-online-dot"></div>
        `;
        container.appendChild(row);
    });

    if (countEl) countEl.textContent = list.length + ' online';
}

// ============================================================
//  POLLING
// ============================================================
function poll() {
    fetch(ROUTES.updates, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        // Notes: update only if we don't have unsaved local changes
        if (!notesDirty && notesEl && data.notes !== undefined) {
            if (data.notes !== notesEl.value) {
                notesEl.value = data.notes;
            }
        }

        // Whiteboard: restore from server only if we haven't drawn since last save
        if (!wbDirty && data.whiteboard && data.whiteboard !== lastWb) {
            lastWb = data.whiteboard;
            const img = new Image();
            img.onload = () => {
                const dpr = window.devicePixelRatio || 1;
                const w   = canvas.width / dpr;
                const h   = canvas.height / dpr;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, w, h);
            };
            img.src = data.whiteboard;
        }

        // Chat
        if (data.chat && data.chat.length > 0) {
            renderChat(data.chat);
        }

        // Participants
        if (data.participants) {
            renderParticipants(data.participants);
        }

        // If room was archived remotely, show notice
        if (!data.is_active && IS_ACTIVE) {
            showArchivedNotice();
        }
    })
    .catch(() => {}); // silent fail — don't spam console on tab hidden
}

function showArchivedNotice() {
    if (document.getElementById('remote-archived-banner')) return;
    const banner = document.createElement('div');
    banner.id = 'remote-archived-banner';
    banner.className = 'archived-banner';
    banner.style.marginBottom = '1rem';
    banner.textContent = '⚠ This room was just archived by the creator. Reload to view the archive.';
    document.getElementById('room-wrap').insertBefore(banner, document.getElementById('room-wrap').children[1]);
}

// ============================================================
//  UTILS
// ============================================================
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ============================================================
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    // Resize canvas to fit the panel
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Restore saved whiteboard image from server
    @if($studyRoom->whiteboard_data)
    const savedWb = @json($studyRoom->whiteboard_data);
    if (savedWb && typeof savedWb === 'string' && savedWb.startsWith('data:')) {
        const img = new Image();
        img.onload = () => {
            const dpr = window.devicePixelRatio || 1;
            ctx.drawImage(img, 0, 0, canvas.width / dpr, canvas.height / dpr);
        };
        img.src = savedWb;
        lastWb = savedWb;
    }
    @endif

    // Render initial chat messages from server
    @if($studyRoom->chat_messages)
    const initialChat = @json($studyRoom->chat_messages);
    if (Array.isArray(initialChat) && initialChat.length > 0) {
        renderChat(initialChat);
    }
    @endif

    // Start polling (only for active rooms)
    if (IS_ACTIVE) {
        poll(); // immediate first poll
        setInterval(poll,      3000); // poll for updates
        setInterval(saveNotes, 5000); // auto-save notes
        setInterval(saveWhiteboard, 5000); // auto-save whiteboard
    }
});
</script>
@endsection