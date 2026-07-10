<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LiveScores Tips</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Sofia+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #121212;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(255, 107, 0, 0.04) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(255, 107, 0, 0.03) 0%, transparent 50%);
            height: 100vh;
            font-family: 'Sofia Sans', sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Subtle grid overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 10;
            /* Put it above the background but below form if possible, or just keep it over everything */
        }

        .login-container {
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .login-card {
            background: transparent;
            border-radius: 20px;
            padding: 48px 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header img {
            height: 44px;
            margin-bottom: 18px;
        }

        .login-header h2 {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #888;
            font-size: 1rem;
        }

        /* Divider under header */
        .login-header::after {
            content: '';
            display: block;
            width: 40px;
            height: 2px;
            background: linear-gradient(135deg, #ff6b00, #e65e00);
            border-radius: 2px;
            margin: 16px auto 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #bbb;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 7px;
        }

        .form-control {
            background: #0f0f0f !important;
            border: 1px solid #2d2d2d !important;
            border-radius: 10px !important;
            color: #e0e0e0 !important;
            padding: 13px 14px !important;
            font-size: 0.95rem !important;
            font-family: 'Sofia Sans', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
        }

        .form-control::placeholder {
            color: #555 !important;
        }

        .form-control:focus {
            background: #0f0f0f !important;
            border-color: #ff6b00 !important;
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1) !important;
            outline: none;
            color: #e0e0e0 !important;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ff6b00 0%, #e65e00 100%);
            border: none;
            border-radius: 10px;
            color: #000000;
            font-weight: 700;
            font-size: 1.05rem;
            font-family: 'Sofia Sans', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 15px;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(255, 107, 0, 0.35);
            background: linear-gradient(135deg, #FFE033 0%, #FFB833 100%);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 0.875rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #555;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #bbb;
            text-decoration: none;
        }

        .back-link i {
            margin-right: 6px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 22px;
            }
        }
    </style>
</head>

<body>
    <div style="display: flex; align-items: center; justify-content: center; height: 100vh;">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="mb-3 bm-logo-dark"
                        style="height: 45px;">
                    <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="mb-3 bm-logo-light"
                        style="height: 45px; display:none;">
                    <h2>Admin Portal</h2>
                    <p>Sign in to access your dashboard</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('admin.login.submit') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email"
                            value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-login">
                        Sign In <i class="fa fa-sign-in ml-2"></i>
                    </button>
                </form>

                <a href="{{ route('home') }}" class="back-link">
                    <i class="fa fa-arrow-left"></i> Back to Website
                </a>

            </div>
        </div>
    </div>
</body>

</html>