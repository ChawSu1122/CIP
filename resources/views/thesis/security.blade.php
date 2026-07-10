@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">Thesis Security Comparison</div>
                <div class="card-body">
                    <p>This table compares security features for session-based and token-based authentication.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Security Feature</th>
                                    <th>Session</th>
                                    <th>Token</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CSRF Protection</td>
                                    <td>✓ required</td>
                                    <td>depends</td>
                                </tr>
                                <tr>
                                    <td>Stateless</td>
                                    <td>No</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>Server Storage</td>
                                    <td>Required</td>
                                    <td>Not required</td>
                                </tr>
                                <tr>
                                    <td>Token Theft Risk</td>
                                    <td>No</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>Session Hijacking Risk</td>
                                    <td>Yes</td>
                                    <td>Depends</td>
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
