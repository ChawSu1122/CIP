@extends('dashboard.layout')

@section('title', 'Security Testing Dashboard')

@section('content')
    <div>
        <h1 class="page-title">Security Testing Dashboard</h1>
        <p class="page-copy">This standalone dashboard includes the overall dashboard view and links to Revocation Latency and Data Exposure Risk pages with the same layout.</p>
    </div>

    <div class="panel">
        <p class="panel-text">Select a page from the sidebar to view dashboard content for each security testing area.</p>
    </div>
@endsection
