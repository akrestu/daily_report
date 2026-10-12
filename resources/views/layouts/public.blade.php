<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Request Bantuan') — SiGAP</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            min-height: 100vh;
        }
        .public-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 2rem 1rem 3rem;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .brand-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .brand-title {
            color: white;
            font-weight: 700;
            font-size: 1.4rem;
            margin: 0.5rem 0 0.25rem;
            text-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        .brand-subtitle {
            color: rgba(255,255,255,0.85);
            font-size: 0.82rem;
        }
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 680px;
            padding: 2rem;
        }
        .section-title {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6c757d;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .priority-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .copyright {
            color: rgba(255,255,255,0.75);
            font-size: 0.75rem;
            text-align: center;
            margin-top: 1.5rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="public-wrapper">
        <div class="brand-header">
            <img src="{{ asset('Sigap.png') }}" alt="SiGAP Logo" class="brand-logo">
            <div class="brand-title">SiGAP</div>
            <div class="brand-subtitle">Smart Integrated Gateway for Activity &amp; Performance</div>
        </div>

        <div class="form-card">
            @yield('content')
        </div>

        <p class="copyright">
            &copy; {{ date('Y') }} SiGAP — Smart Integrated Gateway for Activity &amp; Performance
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
