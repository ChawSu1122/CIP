@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header">Seminar Presentation Summary</div>
                <div class="card-body">
                    <h5>Project title</h5>
                    <p><strong>Comparison between session-based authentication and token-based authentication on a community-based platform.</strong></p>

                    <h5>Problem statement</h5>
                    <p>The project evaluates two common authentication approaches for a community platform with posts, comments, and categories.</p>

                    <h5>Thesis criteria</h5>
                    <ul>
                        <li>Scalability</li>
                        <li>Storage</li>
                        <li>Security</li>
                    </ul>

                    <h5>Implementation</h5>
                    <ul>
                        <li>Session authentication is used for the web interface via Laravel login and session cookies.</li>
                        <li>Token authentication is implemented with API endpoints and bearer tokens for API access.</li>
                        <li>The application includes posts, comments, and categories to represent a real community platform.</li>
                    </ul>

                    <h5>Key findings</h5>
                    <ul>
                        <li>Session auth stores state on the server, so it is easy for browser workflows but requires session storage management.</li>
                        <li>Token auth is stateless and scales well for APIs, but tokens must be protected carefully.</li>
                        <li>Security differences include CSRF handling for sessions and token leakage risk for bearer tokens.</li>
                    </ul>

                    <h5>How to show it in the seminar</h5>
                    <ol>
                        <li>Open the web app and show browser login to prove session-based authentication.</li>
                        <li>Open <code>/token-demo</code> to demonstrate API login and bearer-token usage.</li>
                        <li>Open <code>/comparison</code> to discuss scalability, storage, and security differences.</li>
                        <li>Use this summary page as the project conclusion and learning points.</li>
                    </ol>

                    <h5>Conclusion</h5>
                    <p>The final app supports both authentication types, making it easy to compare their tradeoffs during the thesis seminar.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
