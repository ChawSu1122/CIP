@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">Thesis Research Questions</div>
                <div class="card-body">
                    <p>For this thesis, the comparison is based on the same community platform and two authentication methods.</p>
                    <ol>
                        <li><strong>RQ1:</strong> Which authentication method provides better performance?</li>
                        <li><strong>RQ2:</strong> Which authentication method provides better security characteristics?</li>
                        <li><strong>RQ3:</strong> Which authentication method is easier to implement and maintain?</li>
                    </ol>
                    <p>These questions are answered using the same system features for both session-based and token-based authentication:</p>
                    <ul>
                        <li>User registration</li>
                        <li>Login</li>
                        <li>Posts</li>
                        <li>Comments</li>
                        <li>Categories</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
