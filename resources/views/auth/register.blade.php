@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <div style="position: relative;">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" style="padding-right: 40px;">
                                    <button type="button" id="toggle-password" class="btn btn-link" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; cursor: pointer; color: #666;">
                                        <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Password Requirements Checklist -->
                                <div id="password-requirements" class="mt-3 p-3" style="background-color: #f8f9fa; border-radius: 4px; border-left: 4px solid #ddd;">
                                    <p class="mb-2" style="font-size: 0.875rem; font-weight: 600; color: #333;">Password must:</p>
                                    <ul class="list-unstyled" style="margin: 0;">
                                        <li class="mb-2" style="font-size: 0.875rem;">
                                            <span id="check-length" style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; color: #ddd; margin-right: 8px; font-weight: bold;">✓</span>
                                            <span style="color: #666;">Minimum 8 characters</span>
                                        </li>
                                        <li class="mb-2" style="font-size: 0.875rem;">
                                            <span id="check-uppercase" style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; color: #ddd; margin-right: 8px; font-weight: bold;">✓</span>
                                            <span style="color: #666;">At least 1 uppercase letter</span>
                                        </li>
                                        <li class="mb-2" style="font-size: 0.875rem;">
                                            <span id="check-lowercase" style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; color: #ddd; margin-right: 8px; font-weight: bold;">✓</span>
                                            <span style="color: #666;">At least 1 lowercase letter</span>
                                        </li>
                                        <li class="mb-2" style="font-size: 0.875rem;">
                                            <span id="check-number" style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; color: #ddd; margin-right: 8px; font-weight: bold;">✓</span>
                                            <span style="color: #666;">At least 1 number</span>
                                        </li>
                                        <li style="font-size: 0.875rem;">
                                            <span id="check-special" style="display: inline-block; width: 20px; height: 20px; line-height: 20px; text-align: center; color: #ddd; margin-right: 8px; font-weight: bold;">✓</span>
                                            <span style="color: #666;">At least 1 special character (<code style="color: #d9534f;">@</code>, <code style="color: #d9534f;">#</code>, <code style="color: #d9534f;">$</code>, <code style="color: #d9534f;">!</code>)</span>
                                        </li>
                                    </ul>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <div style="position: relative;">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" style="padding-right: 40px;">
                                    <button type="button" id="toggle-password-confirm" class="btn btn-link" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; cursor: pointer; color: #666;">
                                        <svg id="eye-icon-confirm" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password-confirm');
        const togglePasswordBtn = document.getElementById('toggle-password');
        const togglePasswordConfirmBtn = document.getElementById('toggle-password-confirm');
        
        // Check elements
        const checks = {
            length: document.getElementById('check-length'),
            uppercase: document.getElementById('check-uppercase'),
            lowercase: document.getElementById('check-lowercase'),
            number: document.getElementById('check-number'),
            special: document.getElementById('check-special')
        };

        // Validation rules
        const rules = {
            length: (value) => value.length >= 8,
            uppercase: (value) => /[A-Z]/.test(value),
            lowercase: (value) => /[a-z]/.test(value),
            number: (value) => /[0-9]/.test(value),
            special: (value) => /[@#$!]/.test(value)
        };

        // Update check icon
        function updateCheck(checkElement, isValid) {
            if (isValid) {
                checkElement.style.color = '#28a745';
                checkElement.style.fontWeight = 'bold';
            } else {
                checkElement.style.color = '#ddd';
                checkElement.style.fontWeight = 'normal';
            }
        }

        // Toggle password visibility
        function togglePasswordVisibility(inputField, iconElement) {
            if (inputField.type === 'password') {
                inputField.type = 'text';
                // Change to eye-off icon (SVG)
                iconElement.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                inputField.type = 'password';
                // Change back to eye icon (SVG)
                iconElement.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        // Toggle password button click
        togglePasswordBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const eyeIcon = document.getElementById('eye-icon');
            togglePasswordVisibility(passwordInput, eyeIcon);
        });

        // Toggle confirm password button click
        togglePasswordConfirmBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const eyeIconConfirm = document.getElementById('eye-icon-confirm');
            togglePasswordVisibility(passwordConfirmInput, eyeIconConfirm);
        });

        // Validate password on input
        passwordInput.addEventListener('input', function () {
            const value = passwordInput.value;

            // Check each rule
            Object.keys(rules).forEach(rule => {
                const isValid = rules[rule](value);
                updateCheck(checks[rule], isValid);
            });
        });

        // Optional: Trigger validation on page load if there's a pre-filled value
        if (passwordInput.value) {
            passwordInput.dispatchEvent(new Event('input'));
        }
    });
</script>
        }
    });
</script>

@endsection
