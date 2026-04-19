@extends('layouts.modern')

@section('title', 'Cross-Course Concept Map | BRACU HUB')

@section('extra_css')
<style>
    /* Override the default 1200px layout specifically for the map */
    .main-container {
        max-width: 1280px !important;
    }

    #concept-map-container {
        width: 100%;
        height: 68vh; /* Reduced height */
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        margin-top: 2rem;
    }

    .legend {
        display: flex;
        gap: 2rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-dim);
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
    }
</style>
<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Cross-Course <span class="neon-text" style="color: #a855f7; text-shadow: 0 0 10px #a855f7aa;">Concept Map</span></h1>
    <p class="page-subtitle">Visualize academic overlaps and discover connections between your courses and broader topics.</p>
</div>

<div id="concept-map-container"></div>

<div class="legend">
    @if(auth()->user()->role === 'student')
        <div class="legend-item">
            <div class="legend-color" style="background: #3b82f6; border: 1px solid #60a5fa;"></div>
            <span>Enrolled Course</span>
        </div>
    @endif
    <div class="legend-item">
        <div class="legend-color" style="background: rgba(59, 130, 246, 0.2); border: 1px solid #60a5fa;"></div>
        <span>Other Courses</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background: #a855f7; border: 1px solid #c084fc;"></div>
        <span>Academic Topic / Concept</span>
    </div>
</div>
@endsection

@section('extra_js')
<script type="text/javascript">
    // Parse PHP arrays to JSON for vis.js
    const nodesArray = @json($nodes);
    const edgesArray = @json($edges);

    // Create datasets
    var nodes = new vis.DataSet(nodesArray);
    var edges = new vis.DataSet(edgesArray);

    // Provide the data
    var data = {
        nodes: nodes,
        edges: edges
    };

    // Configuration options
    var options = {
        nodes: {
            shape: 'dot',
            size: 20,
            font: {
                size: 14,
                color: '#ffffff',
                face: 'Inter, sans-serif'
            },
            borderWidth: 2
        },
        edges: {
            width: 1.5,
            smooth: {
                type: 'continuous'
            }
        },
        physics: {
            barnesHut: {
                gravitationalConstant: -4000,
                centralGravity: 0.3,
                springLength: 150,
                springConstant: 0.04,
                damping: 0.09
            },
            stabilization: {
                iterations: 200
            }
        },
        interaction: {
            hover: true,
            tooltipDelay: 200,
            zoomView: true,
            dragView: true
        }
    };

    // Initialize network
    var container = document.getElementById('concept-map-container');
    var network = new vis.Network(container, data, options);
</script>
@endsection
