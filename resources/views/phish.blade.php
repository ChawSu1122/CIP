@extends('layouts.app')

@section('hideNavbar', true)

@section('content')
<div class="page-celebration-wrapper">
    <div class="flare flare-1"></div>
    <div class="flare flare-2"></div>
    <div class="flare flare-3"></div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="celebration-layer" aria-hidden="true">
                    <span class="confetti confetti-1">✨</span>
                    <span class="confetti confetti-2">🎁</span>
                    <span class="confetti confetti-3">💎</span>
                    <span class="confetti confetti-4">✨</span>
                    <span class="confetti confetti-5">🎉</span>
                    <span class="confetti confetti-6">✨</span>
                    <span class="confetti confetti-7">🎇</span>
                    <span class="confetti confetti-8">🎊</span>
                    <span class="confetti confetti-9">✨</span>
                    <span class="confetti confetti-10">🎉</span>
                    <span class="confetti confetti-11">🎁</span>
                    <span class="confetti confetti-12">🎊</span>
                </div>

                <div class="card shadow-lg border-0 rounded-4 overflow-hidden celebration-card">
                <div class="card-header bg-dark text-white border-0 rounded-top-4 py-3">
                    <h1 class="h4 mb-0">🎉 Congratulations! You’re a winner</h1>
                </div>
                <div class="card-body p-4 p-md-5 position-relative">

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
    </div>
</div>

<style>
.page-celebration-wrapper {
    min-height: 100vh;
    position: relative;
    background: #ffffff;
    overflow: hidden;
}

.page-celebration-wrapper::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 50% 90%, rgba(255, 200, 150, 0.2), transparent 28%),
        radial-gradient(circle at 20% 92%, rgba(255, 150, 70, 0.14), transparent 25%),
        radial-gradient(circle at 80% 94%, rgba(255, 220, 140, 0.1), transparent 30%);
    z-index: 0;
}

.page-celebration-wrapper::after {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(180deg, transparent 0%, rgba(255, 255, 255, 0.18) 30%, rgba(255, 255, 255, 0.75) 100%);
    opacity: 0.8;
    z-index: 0;
}

.page-celebration-wrapper .container {
    position: relative;
    z-index: 2;
}

.firework-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 1;
}

.firework {
    position: absolute;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(255,255,255,0.88) 28%, rgba(255,180,90,0.2) 60%, transparent 90%);
    filter: drop-shadow(0 0 30px rgba(255, 173, 51, 0.85));
    opacity: 0;
    animation: firework-pop 3s ease-out infinite;
}

.firework-1 { top: 12%; left: 18%; animation-delay: 0s; }
.firework-2 { top: 14%; right: 18%; animation-delay: 0.8s; }
.firework-3 { bottom: 18%; left: 16%; animation-delay: 1.6s; }
.firework-4 { bottom: 20%; right: 22%; animation-delay: 2.4s; }

.firework::before {
    content: '';
    position: absolute;
    inset: 50%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.95) 0%, rgba(255,180,90,0.4) 35%, transparent 70%);
    transform: translate(-50%, -50%) scale(3);
}

.firework::after {
    content: '';
    position: absolute;
    width: 120px;
    height: 120px;
    left: -52px;
    top: -52px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,245,220,0.45) 0%, transparent 60%);
}

.card {
    position: relative;
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 0 80px rgba(255, 255, 255, 0.22);
}

.celebration-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 0;
}

.confetti {
    position: absolute;
    display: inline-block;
    font-size: 2.0rem;
    animation: floatUp 2.4s ease-out infinite;
    opacity: 0;
    filter: drop-shadow(0 0 18px rgba(255, 120, 0, 0.75));
}

.confetti-1 { left: 8%; bottom: 4%; animation-delay: 0s; }
.confetti-2 { left: 18%; bottom: 10%; animation-delay: 0.18s; }
.confetti-3 { left: 28%; bottom: 6%; animation-delay: 0.33s; }
.confetti-4 { left: 42%; bottom: 8%; animation-delay: 0.5s; }
.confetti-5 { left: 56%; bottom: 5%; animation-delay: 0.22s; }
.confetti-6 { left: 66%; bottom: 11%; animation-delay: 0.4s; }
.confetti-7 { left: 76%; bottom: 7%; animation-delay: 0.62s; }
.confetti-8 { left: 86%; bottom: 9%; animation-delay: 0.8s; }
.confetti-9 { left: 50%; bottom: 2%; animation-delay: 0.1s; }
.confetti-10 { left: 70%; bottom: 3%; animation-delay: 0.28s; }
.confetti-11 { left: 35%; bottom: 1%; animation-delay: 0.55s; }
.confetti-12 { left: 90%; bottom: 12%; animation-delay: 0.72s; }

.phone-image {
    max-height: 320px;
    object-fit: cover;
    animation: pulseGlow 2s ease-in-out infinite;
}

@keyframes firework-pop {
    0% {
        transform: scale(0.05);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    30% {
        transform: scale(1.5);
        opacity: 1;
    }
    55% {
        transform: scale(1.2);
        opacity: 0.75;
    }
    100% {
        transform: scale(2.4);
        opacity: 0;
    }
}

@keyframes floatUp {
    0% {
        transform: translate3d(0, 120px, 0) rotate(0deg);
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    75% {
        opacity: 1;
    }
    100% {
        transform: translate3d(0, -520px, 0) rotate(780deg);
        opacity: 0;
    }
}

@keyframes pulseGlow {
    0%, 100% {
        transform: translateY(0) scale(1);
        box-shadow: 0 0 0 rgba(255, 120, 0, 0.16);
    }
    50% {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 24px 56px rgba(255, 120, 0, 0.28);
    }
}
</style>
@endsection