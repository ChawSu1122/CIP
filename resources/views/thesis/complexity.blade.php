@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">Implementation Complexity</div>
                <div class="card-body">
                    <p>This page summarizes the relative effort and complexity for both authentication approaches.</p>
                    <ul>
                        <li><strong>Session-based authentication:</strong> uses Laravel's built-in web guard and cookie session handling. Implementation is straightforward for browser-based apps.</li>
                        <li><strong>Token-based authentication:</strong> requires API token generation, header handling, and token verification. It is more flexible for API clients and mobile apps.</li>
                    </ul>
                    <p>Scoring is derived from:</p>
                    <ul>
                        <li>Time to implement</li>
                        <li>Number of files changed</li>
                        <li>Configuration complexity</li>
                    </ul>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Criterion</th>
                                    <th>Session</th>
                                    <th>Token</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Implementation difficulty</td>
                                    <td>Low</td>
                                    <td>Medium</td>
                                </tr>
                                <tr>
                                    <td>Maintenance complexity</td>
                                    <td>Low</td>
                                    <td>Medium</td>
                                </tr>
                                <tr>
                                    <td>Client compatibility</td>
                                    <td>Browser only</td>
                                    <td>Browser + API clients</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
