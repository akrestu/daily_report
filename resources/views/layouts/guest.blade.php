<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daily Job Report System</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('web/site.webmanifest') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Styles -->
    @if(app()->environment('local') && file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @php
            // Use Vite's manifest file to get the correct asset paths
            $manifest = null;
            try {
                if(file_exists(public_path('build/.vite/manifest.json'))) {
                    $manifest = json_decode(file_get_contents(public_path('build/.vite/manifest.json')), true);
                }
            } catch (\Exception $e) {
                // Silently fail if manifest can't be read
            }
            
            $cssFile = $manifest && isset($manifest['resources/css/app.css']) 
                ? 'build/' . $manifest['resources/css/app.css']['file'] 
                : (file_exists(public_path('build/assets/app-PYGI7GKI.css')) 
                    ? 'build/assets/app-PYGI7GKI.css' 
                    : 'css/app.css');
                    
            $jsFile = $manifest && isset($manifest['resources/js/app.js']) 
                ? 'build/' . $manifest['resources/js/app.js']['file'] 
                : (file_exists(public_path('build/assets/app-BW-0XrbD.js')) 
                    ? 'build/assets/app-BW-0XrbD.js' 
                    : 'js/app.js');
        @endphp
        <link rel="stylesheet" href="{{ asset($cssFile) }}">
        <script src="{{ asset($jsFile) }}" defer></script>
    @endif
    
    <style>
        :root {
            --mobile-vh: 100vh;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            min-height: var(--mobile-vh);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(125deg, #0d6efd, #6610f2);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            -webkit-tap-highlight-color: transparent;
            margin: 0;
            padding: 0;
        }
        
        /* Animated Background */
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Abstract shapes */
        .shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            opacity: 0.2;
            border-radius: 50%;
            filter: blur(80px);
        }
        
        .shape-1 {
            background: #6f42c1;
            width: 600px;
            height: 600px;
            top: -200px;
            right: -100px;
            animation: floating 15s infinite ease-in-out;
        }
        
        .shape-2 {
            background: #0d6efd;
            width: 450px;
            height: 450px;
            bottom: -150px;
            left: -100px;
            animation: floating 18s infinite ease-in-out reverse;
        }
        
        .shape-3 {
            background: #6610f2;
            width: 300px;
            height: 300px;
            top: 40%;
            left: 60%;
            animation: floating 20s infinite ease-in-out;
        }
        
        @keyframes floating {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(10px, 15px) rotate(5deg); }
            50% { transform: translate(20px, 0) rotate(0deg); }
            75% { transform: translate(10px, -15px) rotate(-5deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }
        
        /* Animated light effect */
        .light-effect {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0) 60%);
            z-index: -1;
            animation: light-rotate 20s infinite linear;
        }
        
        @keyframes light-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Digital circuit lines */
        .circuit-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            opacity: 0.12;
            overflow: hidden;
        }
        
        .circuit {
            position: absolute;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
        }
        
        .circuit-1 {
            width: 200px;
            height: 200px;
            top: 20%;
            left: 10%;
            animation: circuit-float 30s infinite linear;
        }
        
        .circuit-2 {
            width: 300px;
            height: 100px;
            bottom: 30%;
            right: 10%;
            animation: circuit-float 20s infinite linear reverse;
        }
        
        .circuit-3 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            left: 30%;
            animation: circuit-float 25s infinite linear;
        }
        
        @keyframes circuit-float {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-20px, 20px) rotate(90deg); }
            50% { transform: translate(0, 40px) rotate(180deg); }
            75% { transform: translate(20px, 20px) rotate(270deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        
        .container {
            max-width: 100%;
            padding: 0 15px;
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 340px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: card-appear 1s ease-out forwards;
        }
        
        @keyframes card-appear {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-body {
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .logo-text {
            background: linear-gradient(to right, #4e73df, #224abe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
        
        .copyright {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-bottom: 0.2rem;
            color: white;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            animation: fade-in 1.5s ease-out forwards;
        }
        
        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 0.9;
            }
        }
        
        .heart {
            color: #ff6b6b;
            animation: heartbeat 1.5s infinite ease-in-out;
        }
        
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        /* Mobile optimizations */
        @media (max-width: 576px) {
            /* Mobile background fixes for anti-flicker */
            html {
                background-color: #0d6efd; /* Fallback */
                background-image: var(--gradient-bg);
            }
            
            body {
                /* Center perfectly */
                justify-content: center;
                align-items: center;
                height: 100vh;
                height: var(--mobile-vh);
                padding: 0 !important;
                margin: 0 !important;
                /* Disable background animation for mobile */
                animation: none !important;
                background: linear-gradient(135deg, #0d6efd, #6610f2) !important;
            }
            
            /* Anti-flicker overlay only for mobile */
            #mobile-anti-flicker {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw; /* Menggunakan viewport width untuk memastikan menutupi seluruh layar */
                height: 100vh; /* Menggunakan viewport height untuk memastikan menutupi seluruh layar */
                margin: 0;
                padding: 0;
                background: var(--gradient-bg);
                /* Pastikan z-index lebih rendah agar tidak menutupi form */
                z-index: -10;
                pointer-events: none; /* Tambahkan ini agar tidak menghalangi interaksi */
                overflow: hidden;
                border: none; /* Hapus border yang mungkin menyebabkan garis putih */
            }
            
            /* Disable all animations for mobile */
            .shape, .light-effect, .circuit, .heart, .card {
                animation: none !important;
                transform: none !important;
                transition: none !important;
            }
            
            /* Hide decorative elements on mobile */
            .circuit-container, .light-effect {
                display: none !important;
            }
            
            /* Simplify shapes on mobile */
            .shapes {
                opacity: 0.15;
                position: fixed;
            }
            
            .shape {
                filter: blur(40px);
                transform: none !important;
            }
            
            .shape-1 {
                width: 300px;
                height: 300px;
                top: -100px;
                right: -50px;
            }
            
            .shape-2 {
                width: 250px;
                height: 250px;
                bottom: -50px;
                left: -50px;
            }
            
            .shape-3 {
                display: none;
            }
            
            .heart {
                color: #ff6b6b;
                transform: none !important;
            }
            
            .container {
                padding: 0;
                height: 100%;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .login-container {
                height: auto;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) !important; /* Keep this transform */
                width: 100%;
                max-width: 100%;
                padding: 0 20px;
            }
            
            .card {
                width: 100%;
                max-width: 320px;
                border-radius: 20px;
                margin: 0 auto;
                /* Adjust shadow for better performance */
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                /* Remove backdrop blur for better performance */
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }
            
            .card-body {
                padding: 1.75rem 1.25rem;
            }
            
            .copyright {
                margin-top: 15px;
                width: 100%;
                text-align: center;
                animation: none !important;
                opacity: 0.9;
            }
        }
        
        /* Prevent iOS scrolling issues */
        @supports (-webkit-touch-callout: none) {
            body {
                height: -webkit-fill-available;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile anti-flicker layer (only displays on mobile) -->
    <div id="mobile-anti-flicker" style="display: none;"></div>
    
    <!-- Light effect -->
    <div class="light-effect"></div>
    
    <!-- Abstract Shapes -->
    <div class="shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <!-- Circuit animations -->
    <div class="circuit-container">
        <div class="circuit circuit-1"></div>
        <div class="circuit circuit-2"></div>
        <div class="circuit circuit-3"></div>
    </div>
    
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body">
                    {{ $slot }}
                </div>
            </div>
            
            <div class="text-center mt-3">
                <p class="copyright">
                    &copy; {{ date('Y') }} Created with <span class="heart">♥</span> by ak.restu
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Mobile optimizations script -->
    <script>
        // Deteksi apakah ini adalah perangkat mobile
        const isMobile = window.matchMedia('(max-width: 576px)').matches;
        
        // Fix for iOS viewport height issues
        function setVH() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--mobile-vh', `${window.innerHeight}px`);
        }
        
        // Set on initial load
        setVH();
        
        // Update on resize and orientation change
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', function() {
            // Small delay to ensure the height is correct after orientation change
            setTimeout(setVH, 100);
        });
        
        // Prevent bouncing/scrolling on iOS
        document.addEventListener('touchmove', function(e) {
            if (e.target.closest('.card')) {
                // Allow scrolling inside card if needed
                return;
            }
            e.preventDefault();
        }, { passive: false });
        
        // Tampilkan anti-flicker hanya di mobile
        if (isMobile) {
            document.getElementById('mobile-anti-flicker').style.display = 'block';
            
            // Page Visibility API - prevent white flash during transitions (mobile only)
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') {
                    // When page is about to be hidden (during navigation/reload)
                    document.documentElement.style.background = 'var(--gradient-bg)';
                    document.body.style.background = 'var(--gradient-bg)';
                }
            });
            
            // Mobile-specific: Ensure background is preserved during page unload
            window.addEventListener('beforeunload', function() {
                document.documentElement.style.background = 'var(--gradient-bg)';
                document.body.style.background = 'var(--gradient-bg)';
                
                // Hanya buat splash screen jika form tidak sedang disubmit
                // untuk mencegah menutupi form login
                const isLoginFormSubmitted = document.querySelector('.login-form button[disabled]');
                if (!isLoginFormSubmitted) {
                    // Create a splash screen element that will persist during reload
                    const splash = document.createElement('div');
                    splash.style.position = 'fixed';
                    splash.style.top = '0';
                    splash.style.left = '0';
                    splash.style.width = '100%';
                    splash.style.height = '100%';
                    splash.style.zIndex = '-5'; // Nilai lebih rendah agar tidak menutupi form
                    splash.style.background = 'var(--gradient-bg)';
                    splash.id = 'page-transition-splash';
                    document.body.appendChild(splash);
                }
                
                // Store gradient background in local storage for persistence
                if (window.localStorage) {
                    localStorage.setItem('lastBg', 'gradient');
                }
            });
        }
        
        // Additional positioning check on page load
        window.addEventListener('load', function() {
            if (isMobile) {
                // Force reflow for mobile centering
                const loginContainer = document.querySelector('.login-container');
                if (loginContainer) {
                    loginContainer.style.display = 'flex';
                }
                // Trigger height recalculation
                setVH();
                
                // Apply solid background color to HTML element for extra protection against flicker
                document.documentElement.style.backgroundColor = '#0d6efd';
            }
        });
    </script>
</body>
</html>