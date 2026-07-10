@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h1 class="h3 fw-bold mb-3">Forum & Thesis App Features</h1>
                    <p class="text-muted">This project combines a modern community forum experience with a structured comparison study between session-based and token-based authentication.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h2 class="h5 mb-3">Forum Features</h2>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <strong>Discussion feed</strong> with post preview cards and category tags.
                                </li>
                                <li class="mb-3">
                                    <strong>Post creation and editing</strong> for authenticated users.
                                </li>
                                <li class="mb-3">
                                    <strong>Comment threads</strong> with edit and delete permissions.
                                </li>
                                <li class="mb-3">
                                    <strong>Category browsing</strong> and topic discovery.
                                </li>
                                <li class="mb-3">
                                    <strong>Responsive layout</strong> for desktop and mobile forum browsing.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h2 class="h5 mb-3">Authentication & Research</h2>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <strong>Session-based login</strong> for browser sessions and standard cookie auth.
                                </li>
                                <li class="mb-3">
                                    <strong>Token-based login</strong> for API access and mobile-style clients.
                                </li>
                                <li class="mb-3">
                                    <strong>API documentation</strong> and token demo page.
                                </li>
                                <li class="mb-3">
                                    <strong>Research dashboards</strong> for security, storage, and scalability comparisons.
                                </li>
                                <li class="mb-3">
                                    <strong>Thesis experiment pages</strong> for questions, methodology, evidence, and conclusions.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h2 class="h5 mb-3">Why this app is professional</h2>
                            <p class="text-muted">It is built to resemble an international community forum with a clear content hierarchy, easy access to topics, and a polished research UI for thesis presentation.</p>
                            <div class="row row-cols-1 row-cols-md-2 g-3">
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <strong>Community-first navigation</strong>
                                        <p class="mb-0 text-muted">Feed, categories, features, plus research sections are easy to reach.</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <strong>Data-driven comparison</strong>
                                        <p class="mb-0 text-muted">Built-in metrics track performance and security differences in real time.</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <strong>Modern UI</strong>
                                        <p class="mb-0 text-muted">Cards, badges, and compact panels create a clean forum experience.</p>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-light rounded">
                                        <strong>Clear feature roadmap</strong>
                                        <p class="mb-0 text-muted">A dedicated feature page documents the app’s capabilities for presentations.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
