<x-guest-layout>
    <div class="text-center mb-4">
        <div class="auth-logo mx-auto mb-3">
            <i class="fas fa-clipboard-list text-primary" style="font-size: 24px;"></i>
        </div>
        <h4 class="fw-bold mb-1">Daily Job Report</h4>
        <p class="text-muted small">Sign in to your account</p>
    </div>
    
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-3 text-center">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <form method="POST" action="{{ route('login') }}" class="login-form w-100" id="loginForm">
        @csrf

        <div class="mb-3 text-center">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-user text-primary"></i>
                </span>
                <input id="user_id" type="text" 
                    class="form-control border-start-0 ps-0 @error('user_id') is-invalid @enderror" 
                    name="user_id" 
                    value="{{ old('user_id') }}" 
                    required 
                    autocomplete="user_id" 
                    autofocus 
                    placeholder="User ID">
                @error('user_id')
                    <div class="invalid-feedback text-center">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="mb-3 text-center">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="fas fa-lock text-primary"></i>
                </span>
                <input id="password" 
                    type="password" 
                    class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="Password">
                <button class="btn btn-light border-start-0" type="button" id="togglePassword">
                    <i class="fas fa-eye text-muted"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback text-center">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="mb-3 text-center">
            <div class="form-check d-inline-block">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small" for="remember">
                    Remember me
                </label>
            </div>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="fas fa-sign-in-alt me-2"></i>Login
                <span class="spinner-border spinner-border-sm ms-2 d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
            </button>
        </div>
        
        <!-- Hidden overlay to prevent flash -->
        <div id="flash-protector" style="display: none;"></div>
    </form>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Add focus effects to input groups - but only for desktop
            const formControls = document.querySelectorAll('.form-control');
            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            
            if (!isMobile) {
                formControls.forEach(control => {
                    control.addEventListener('focus', function() {
                        this.closest('.input-group').classList.add('shadow-sm');
                        this.closest('.input-group').style.borderColor = '#0d6efd';
                    });
                    
                    control.addEventListener('blur', function() {
                        this.closest('.input-group').classList.remove('shadow-sm');
                        this.closest('.input-group').style.borderColor = '';
                    });
                });
            }
            
            // Login form submission - show spinner
            const loginForm = document.querySelector('.login-form');
            const loginBtn = document.getElementById('loginBtn');
            const loginSpinner = document.getElementById('loginSpinner');
            const loginBtnContent = loginBtn.innerHTML; // Simpan konten asli tombol
            
            loginForm.addEventListener('submit', function(e) {
                // Show spinner, tapi jangan mengganti seluruh konten tombol pada mobile
                if (isMobile) {
                    // Aktifkan spinner tapi tetap pertahankan ikon dan teks
                    loginSpinner.classList.remove('d-none');
                    
                    // Disable button untuk mencegah submit ganda
                    loginBtn.setAttribute('disabled', 'disabled');
                    
                    // Anti-flicker dengan z-index lebih rendah (jangan terlalu tinggi)
                    if (document.getElementById('flash-protector')) {
                        // Posisikan flash protector di belakang form
                        document.getElementById('flash-protector').style.display = 'block';
                        document.getElementById('flash-protector').style.zIndex = '-5';
                    }
                    
                    // Pastikan mobile-anti-flicker tidak menutupi form
                    if (window.parent.document.getElementById('mobile-anti-flicker')) {
                        window.parent.document.getElementById('mobile-anti-flicker').style.zIndex = '-10';
                    }
                } else {
                    // Behavior desktop seperti sebelumnya
                    loginSpinner.classList.remove('d-none');
                    loginBtn.setAttribute('disabled', 'disabled');
                    loginBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...`;
                }
            });
            
            // Add active states for mobile touch but without animation effects
            if (isMobile) {
                // Pre-apply mobile-friendly input styling
                document.querySelectorAll('.input-group').forEach(group => {
                    group.classList.add('mobile-input-group');
                });
            }
        });
    </script>
    
    <style>
        .auth-logo {
            width: 70px;
            height: 70px;
            background: rgba(13, 110, 253, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 16px rgba(13, 110, 253, 0.2);
        }
        
        .login-form .input-group-text,
        .login-form .form-control,
        .login-form .btn {
            border-radius: 8px;
            font-size: 0.9rem;
            border-color: #ced4da; /* Warna abu-abu standard untuk semua elemen */
        }
        
        /* Memastikan tombol view password dengan style yang konsisten */
        .login-form .btn-light {
            background-color: #f8f9fa;
            border-color: #ced4da;
        }
        
        /* Desktop transition effects */
        @media (min-width: 769px) {
            .login-form .input-group {
                transition: all 0.2s ease;
            }
            
            .login-form .input-group:focus-within {
                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            }
        }
        
        .login-form .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        
        .login-form .btn-primary {
            background: linear-gradient(to right, #0d6efd, #6610f2);
            border: none;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            padding: 0.6rem 1rem;
        }
        
        .login-form .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
            background: linear-gradient(to right, #0d6efd, #6610f2, #6f42c1);
        }
        
        .login-form .btn-primary:active,
        .login-form .btn-primary:focus {
            background: linear-gradient(to right, #0d6efd, #6610f2) !important;
        }
        
        .text-primary {
            color: #0d6efd !important;
        }
        
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        /* Flash protector overlay - prevent white flash on form submission */
        #flash-protector {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-bg);
            z-index: -5;
            pointer-events: none;
        }
        
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .auth-logo {
                width: 60px;
                height: 60px;
            }
            
            .login-form .input-group-text,
            .login-form .btn-light {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .login-form .form-control {
                font-size: 16px !important; /* Prevent zooming */
                height: calc(3rem + 2px);
                -webkit-appearance: none; /* Prevent iOS default styling */
            }
            
            /* Fix the flickering background issue */
            .login-form .input-group {
                background-color: transparent; /* Ensure consistent background */
                transition: none; /* Disable transitions on mobile */
            }
            
            .login-form .form-control:focus {
                box-shadow: none !important; 
                -webkit-tap-highlight-color: transparent; /* Remove tap highlight */
            }
            
            /* Fix white overlay issue on left side */
            .login-form .input-group {
                overflow: hidden; /* Prevent any overflow that might cause white edges */
                border-radius: 8px; /* Ensure consistent border radius */
                box-shadow: none; /* Remove any shadow that might cause white edges */
            }
            
            /* Ensure no white borders around input groups */
            .login-form .input-group-text,
            .login-form .form-control,
            .login-form .btn-light {
                border-color: transparent; /* Make borders transparent to prevent white edges */
                box-shadow: none;
            }
            
            /* Apply static styling for mobile inputs */
            .mobile-input-group {
                background-color: #fff; /* Fixed background color */
                border: 1px solid #ced4da; /* Use single border around entire group instead */
                overflow: hidden; /* Contain everything within the border */
            }
            
            .login-form .btn-primary {
                padding: 0.8rem 1rem;
                font-size: 1rem;
                border-radius: 8px; /* Ensure consistent border radius */
            }
            
            /* Improved touch targets for checkboxes */
            .form-check-input {
                width: 1.2em;
                height: 1.2em;
                margin-top: 0.15em;
            }
            
            .form-check-label {
                padding-top: 3px;
                padding-left: 5px;
            }
            
            /* No white flash on tap */
            * {
                -webkit-tap-highlight-color: transparent;
            }
            
            /* Fix flash protector to prevent any white edges */
            #flash-protector {
                display: block !important;
                width: 100vw; /* Full viewport width */
                height: 100vh; /* Full viewport height */
                left: 0;
                top: 0;
                margin: 0;
                padding: 0;
                overflow: hidden;
                border: none;
            }
        }
        
        /* Prevent zooming on iOS when focusing inputs */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }
    </style>
</x-guest-layout>