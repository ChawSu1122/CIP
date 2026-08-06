@extends('layouts.app')

@section('hideNavbar', true)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden celebration-card">
                <div class="card-header bg-dark text-white border-0 rounded-top-4 py-3">
                    <h1 class="h4 mb-0">🎉 Congratulations! You’re a winner</h1>
                </div>
                <div class="card-body p-4 p-md-5 position-relative">
                    <div class="celebration-layer" aria-hidden="true">
                        <span class="confetti confetti-1">✨</span>
                        <span class="confetti confetti-2">🎁</span>
                        <span class="confetti confetti-3">💎</span>
                        <span class="confetti confetti-4">✨</span>
                    </div>

                    <div class="row align-items-center g-4">
                        <div class="col-md-6 text-center">
                            <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=900&q=80"
                                 alt="Real iPhone 17 style smartphone"
                                 class="img-fluid rounded-4 shadow-sm phone-image">
                        </div>

                        <div class="col-md-6">
                            <p class="lead mb-3">You have been selected to receive a brand-new iPhone 17.</p>
                            <p class="text-muted mb-4">Your prize is being prepared and will be shipped within 48 hours. Please confirm your delivery details to claim it.</p>

                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item"><strong>Prize:</strong> iPhone 17</li>
                                <li class="list-group-item"><strong>Delivery:</strong> Free expedited shipping</li>
                                <li class="list-group-item"><strong>Status:</strong> Reserved for your account</li>
                            </ul>

                            <div class="d-flex flex-column gap-3">
                                <!-- <a href="{{ route('home') }}" class="btn btn-success btn-lg px-4">Claim Prize</a> -->
                                <a href="{{ route('home') }}" class="btn btn-secondary btn-lg px-4">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.celebration-card {
    position: relative;
    overflow: hidden;
}

.celebration-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.confetti {
    position: absolute;
    display: inline-block;
    font-size: 1.2rem;
    animation: floatUp 2.6s ease-out infinite;
    opacity: 0;
}

.confetti-1 { left: 10%; top: 15%; animation-delay: 0s; }
.confetti-2 { left: 45%; top: 8%; animation-delay: 0.4s; }
.confetti-3 { left: 70%; top: 20%; animation-delay: 0.8s; }
.confetti-4 { left: 85%; top: 12%; animation-delay: 1.2s; }

.phone-image {
    max-height: 320px;
    object-fit: cover;
    animation: pulseGlow 2s ease-in-out infinite;
}

@keyframes floatUp {
    0% {
        transform: translate3d(0, 20px, 0) rotate(0deg);
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    100% {
        transform: translate3d(0, -220px, 0) rotate(360deg);
        opacity: 0;
    }
}

@keyframes pulseGlow {
    0%, 100% {
        transform: translateY(0) scale(1);
        box-shadow: 0 0 0 rgba(13, 110, 253, 0.15);
    }
    50% {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 16px 40px rgba(13, 110, 253, 0.25);
    }
}
</style>
@endsection