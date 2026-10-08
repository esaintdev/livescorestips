<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ config('app.name', 'LiveScores Tips') }}</title>
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

        .admin-container a { text-decoration: none !important; color: inherit !important; }
        .admin-container a:not(.btn):not(.as-btn):not(.as-action-btn):hover { color: #ff6b00 !important; }

        body {
            background-color: #121212 !important;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(255, 107, 0, 0.04) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(255, 107, 0, 0.03) 0%, transparent 50%) !important;
            font-family: 'Sofia Sans', sans-serif !important;
            color: #e0e0e0 !important;
            min-height: 100vh !important;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            background: #1a1a1a !important;
            height: 100vh !important;
            overflow-y: auto !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 260px !important;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.5) !important;
            z-index: 1000 !important;
            border-right: 1px solid #2d2d2d !important;
        }

        .sidebar-logo {
            padding: 30px 20px !important;
            text-align: center !important;
            border-bottom: 1px solid #2d2d2d !important;
        }

        .sidebar-logo img {
            height: 38px !important;
            margin-bottom: 12px !important;
        }

        .sidebar-logo h6 {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            letter-spacing: 2px !important;
            margin: 0 !important;
            opacity: 0.8 !important;
        }

        .sidebar-profile {
            padding: 24px 20px !important;
            text-align: center !important;
            border-bottom: 1px solid #2d2d2d !important;
            background: rgba(0, 0, 0, 0.2) !important;
            margin-bottom: 10px !important;
        }

        .sidebar-profile .admin-name {
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            margin-bottom: 12px !important;
        }

        .sidebar-profile .btn-profile {
            background: rgba(255, 107, 0, 0.08) !important;
            border: 1px solid rgba(255, 107, 0, 0.2) !important;
            color: #ff6b00 !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding: 7px 18px !important;
            border-radius: 20px !important;
            transition: all 0.3s !important;
        }

        .sidebar-nav .nav-link {
            color: #bbb !important;
            padding: 14px 24px !important;
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            transition: all 0.3s !important;
            border-left: 4px solid transparent !important;
            font-size: 0.92rem !important;
            font-weight: 500 !important;
            text-decoration: none !important;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.03) !important;
            color: #ffffff !important;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255, 107, 0, 0.05) !important;
            color: #ff6b00 !important;
            border-left-color: #ff6b00 !important;
            font-weight: 600 !important;
        }

        .sidebar-divider {
            height: 1px;
            background: #2d2d2d !important;
            margin: 15px 0 !important;
        }

        .btn-logout {
            width: 100% !important;
            background: transparent !important;
            border: none !important;
            color: #ff4d4d !important;
            padding: 14px 24px !important;
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            transition: all 0.3s !important;
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            border-left: 4px solid transparent !important;
        }

        .btn-logout:hover {
            background: rgba(255, 77, 77, 0.05) !important;
            color: #ff3333 !important;
        }

        /* Main Content Area - Fixing the white background issue */
        .admin-main {
            margin-left: 260px !important;
            padding: 40px !important;
            min-height: 100vh !important;
            position: relative !important;
            z-index: 1 !important;
            background: transparent !important;
            /* Allow body background to show */
        }

        .page-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 40px !important;
            padding-bottom: 24px !important;
            border-bottom: 1px solid #2d2d2d !important;
        }

        .page-header h4 {
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 1.8rem !important;
            margin: 0 !important;
        }

        /* ===== Premium Toast Notifications ===== */
        .admin-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;
            min-width: 320px;
            max-width: 420px;
            background: #1a1a1a;
            border: 1px solid #2d2d2d;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 18px 26px 18px;
            overflow: hidden;
            animation: toastSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        .admin-toast.hide {
            animation: toastSlideOut 0.4s ease forwards;
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes toastSlideOut {
            from {
                opacity: 1;
                transform: translateX(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateX(110%) scale(0.9);
            }
        }

        .toast-success {
            border-left: 4px solid #22c55e;
        }

        .toast-error {
            border-left: 4px solid #ef4444;
        }

        .toast-icon {
            font-size: 1.6rem;
            flex-shrink: 0;
        }

        .toast-success .toast-icon {
            color: #22c55e;
        }

        .toast-error .toast-icon {
            color: #ef4444;
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #fff;
            margin-bottom: 3px;
        }

        .toast-message {
            font-size: 0.9rem;
            color: #aaa;
            line-height: 1.4;
        }

        .toast-close {
            background: none;
            border: none;
            color: #666;
            font-size: 1.2rem;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            align-self: flex-start;
            transition: color 0.2s;
        }

        .toast-close:hover {
            color: #fff;
        }

        .toast-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 100%;
            border-radius: 0 0 14px 14px;
            animation: toastProgress 5s linear forwards;
        }

        .toast-success .toast-progress-bar {
            background: linear-gradient(90deg, #22c55e, #16a34a);
        }

        .toast-error .toast-progress-bar {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        @keyframes toastProgress {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        /* ===================================== */

        /* Force everything inside cards to be light */
        .card {
            background: #1a1a1a !important;
            border: 1px solid #2d2d2d !important;
            color: #e0e0e0 !important;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid #2d2d2d !important;
            color: #ffffff !important;
        }

        /* Table overrides inside the main area */
        .table {
            color: #e0e0e0 !important;
            background-color: transparent !important;
        }

        .table thead th {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border-bottom: 1px solid #2d2d2d !important;
            color: #888 !important;
        }

        .table tbody td {
            border-bottom: 1px solid #2d2d2d !important;
            color: #ccc !important;
        }

        /* Form overrides - Apple Inspired */
        .form-control {
            background: rgba(255,255,255,0.04) !important;
            border: 1.5px solid rgba(255,255,255,0.06) !important;
            border-radius: 10px !important;
            color: #e0e0e0 !important;
            font-weight: 500 !important;
            padding: 10px 14px !important;
            transition: all 0.2s !important;
            -webkit-appearance: none !important;
        }

        .form-control:focus {
            border-color: #ff6b00 !important;
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.12) !important;
            background: rgba(255,255,255,0.06) !important;
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.2) !important;
        }

        select.form-control {
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 16px center !important;
            padding-right: 40px !important;
        }

        textarea.form-control {
            border-radius: 12px !important;
        }

        /* Global Dark Theme Overrides for sub-pages */
        .bg-light,
        .bg-white,
        .card-header.bg-white,
        .table thead.bg-light {
            background-color: transparent !important;
        }

        .card {
            background: #1a1a1a !important;
            border: 1px solid #2d2d2d !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.03) !important;
            border-bottom: 1px solid #2d2d2d !important;
        }

        .card-header.bg-white,
        .card-body.bg-light,
        .card-footer.bg-white {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .text-dark,
        .text-muted,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .card-title {
            color: #ffffff !important;
        }

        .text-muted {
            opacity: 0.6 !important;
            color: #888 !important;
        }

        /* Table Global Overrides — Modern Soft */
        .table {
            color: #ccc !important;
            background-color: transparent !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        .table thead th {
            background: rgba(255, 255, 255, 0.04) !important;
            color: #e0e0e0 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            padding: 14px 16px !important;
            letter-spacing: 0.02em !important;
        }

        .table thead th:first-child {
            border-radius: 10px 0 0 0;
        }
        .table thead th:last-child {
            border-radius: 0 10px 0 0;
        }

        .table tbody td {
            border-top: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            vertical-align: middle !important;
            padding: 12px 16px !important;
        }

        .table tbody tr:last-child td:first-child {
            border-radius: 0 0 0 10px;
        }
        .table tbody tr:last-child td:last-child {
            border-radius: 0 0 10px 0;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.015) !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
            color: #fff !important;
        }

        .table-bordered {
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 12px !important;
            overflow: hidden !important;
        }

        .table-responsive {
            border-radius: 12px !important;
        }

        .input-group-text {
            background-color: #1a1a1a !important;
            border-color: #2d2d2d !important;
            color: #888 !important;
        }

        .border,
        .border-top,
        .border-bottom,
        .border-left,
        .border-right {
            border-color: #2d2d2d !important;
        }

        /* Icon Shape global helper */
        .icon-shape {
            background: rgba(255, 107, 0, 0.1) !important;
            color: #ff6b00 !important;
        }

        /* Date Navigation fixes */
        .date-navigation .btn-outline-primary {
            background: rgba(255, 107, 0, 0.05) !important;
            border-color: rgba(255, 107, 0, 0.2) !important;
            color: #ff6b00 !important;
        }

        /* Predict Button fix */
        .btn-predict {
            background: rgba(255, 107, 0, 0.1) !important;
            border: 1px solid #ff6b00 !important;
            color: #ff6b00 !important;
            padding: 8px 16px !important;
            border-radius: 8px !important;
            transition: all 0.3s !important;
        }

        .btn-predict:hover {
            background: #ff6b00 !important;
            color: #000 !important;
        }

        /* Premium Theme Button */
        .btn-theme {
            background: linear-gradient(135deg, #ff6b00 0%, #e65e00 100%) !important;
            border: none !important;
            border-radius: 50px !important;
            color: #000000 !important;
            font-weight: 700 !important;
            padding: 10px 24px !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            letter-spacing: 0.02em !important;
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.2) !important;
        }

        .btn-theme:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(255, 107, 0, 0.4) !important;
            background: linear-gradient(135deg, #FFE033 0%, #FFB833 100%) !important;
            color: #000 !important;
            text-decoration: none !important;
        }

        .btn-theme:active {
            transform: translateY(0) !important;
        }

        .btn {
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            padding: 10px 24px !important;
            transition: all 0.25s ease !important;
            letter-spacing: 0.01em !important;
        }
        .btn:hover {
            transform: translateY(-1.5px);
        }
        .btn:active {
            transform: scale(0.97);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff6b00, #e65e00) !important;
            border: none !important;
            color: #000 !important;
            box-shadow: 0 4px 14px rgba(255, 107, 0, 0.25) !important;
        }
        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(255, 107, 0, 0.4) !important;
            color: #000 !important;
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25) !important;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25) !important;
        }
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border: none !important;
            color: #000 !important;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25) !important;
        }
        .btn-sm {
            padding: 6px 16px !important;
            font-size: 12px !important;
        }
        .btn-lg {
            padding: 14px 32px !important;
            font-size: 15px !important;
        }

        .card {
            border-radius: 16px !important;
            overflow: hidden !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3) !important;
        }
        .card-header {
            border-radius: 16px 16px 0 0 !important;
            padding: 20px 24px !important;
        }
        .card-header .card-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }
        .card-header .card-title i {
            color: #ff6b00 !important;
            font-size: 16px !important;
        }
        .card-body {
            padding: 24px !important;
        }
        .card-body .btn {
            margin-top: 4px;
        }

        /* Badge styling */
        .badge {
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 5px 12px !important;
            border-radius: 20px !important;
            letter-spacing: 0.02em !important;
        }
        .badge-success {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #10b981 !important;
        }
        .badge-danger {
            background: rgba(239, 68, 68, 0.12) !important;
            color: #ef4444 !important;
        }
        .badge-warning {
            background: rgba(245, 158, 11, 0.12) !important;
            color: #f59e0b !important;
        }
        .badge-info {
            background: rgba(14, 165, 233, 0.12) !important;
            color: #0ea5e9 !important;
        }
        .badge-primary {
            background: rgba(255, 107, 0, 0.12) !important;
            color: #ff6b00 !important;
        }

        /* Alert styling */
        .alert {
            border-radius: 12px !important;
            border: none !important;
            padding: 16px 20px !important;
            font-weight: 500 !important;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.08) !important;
            color: #fca5a5 !important;
        }
        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .form-group label {
            color: #bbb !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 8px !important;
            display: block !important;
        }

        /* Tabs styling */
        .nav-tabs {
            border-bottom: 1px solid #2d2d2d !important;
            gap: 10px !important;
        }

        .nav-tabs .nav-link {
            color: #888 !important;
            border: none !important;
            transition: all 0.3s !important;
            font-weight: 700 !important;
            padding: 12px 24px !important;
            text-transform: uppercase !important;
            font-size: 0.8rem !important;
            letter-spacing: 0.05em !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            border-radius: 8px 8px 0 0 !important;
            position: relative !important;
        }

        .nav-tabs .nav-link:hover {
            color: #ccc !important;
            background: rgba(255, 107, 0, 0.03) !important;
        }

        .nav-tabs .nav-link.active {
            background-color: rgba(255, 107, 0, 0.08) !important;
            color: #ff6b00 !important;
        }

        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #ff6b00, #e65e00);
            border-radius: 3px 3px 0 0;
        }

        .nav-tabs .nav-link i {
            font-size: 1rem !important;
        }

        /* Quill Editor Global Dark Theme */
        .ql-toolbar.ql-snow {
            background: #2d2d2d !important;
            border: 1px solid #2d2d2d !important;
            border-bottom: none !important;
        }

        .ql-container.ql-snow {
            border: 1px solid #2d2d2d !important;
            background: #1a1a1a !important;
            color: #eee !important;
        }

        .ql-editor,
        .ql-editor p,
        .ql-editor h1,
        .ql-editor h2,
        .ql-editor h3,
        .ql-editor h4,
        .ql-editor h5,
        .ql-editor h6,
        .ql-editor li {
            color: #eee !important;
        }

        .ql-editor span:not([style*="color"]) {
            color: #eee !important;
        }

        .ql-editor.ql-blank::before {
            color: #888 !important;
            font-style: normal !important;
        }

        .ql-snow .ql-stroke {
            stroke: #ccc !important;
        }

        .ql-snow .ql-fill {
            fill: #ccc !important;
        }

        .ql-snow .ql-picker {
            color: #ccc !important;
        }

        .ql-snow .ql-picker-options {
            background-color: #1a1a1a !important;
            border-color: #2d2d2d !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
        }

        .ql-snow .ql-picker-item {
            color: #ccc !important;
        }

        .ql-snow .ql-picker-item:hover,
        .ql-snow .ql-picker-label:hover {
            color: #ff6b00 !important;
        }

        .ql-snow .ql-active .ql-stroke {
            stroke: #ff6b00 !important;
        }

        .ql-snow .ql-active .ql-fill {
            fill: #ff6b00 !important;
        }

        .ql-snow .ql-active,
        .ql-snow .ql-picker-label.ql-active {
            color: #ff6b00 !important;
        }

        .ql-snow a {
            color: #ff6b00 !important;
        }


        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
                padding: 24px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .page-header h4 {
                font-size: 1.5rem;
            }
        }

    /* ============================================
       Apple-Inspired Design System
       ============================================ */

    /* ── Section ── */
    .as-section {
        margin-bottom: 40px;
    }
    .as-section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
        padding: 0 4px;
    }
    .as-section-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        background: rgba(255, 107, 0, 0.1);
        color: #ff6b00;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .as-section-title {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        margin: 0;
        letter-spacing: -0.01em;
    }
    .as-section-subtitle {
        font-size: 13px;
        color: rgba(255,255,255,0.4);
        margin: 2px 0 0;
    }

    /* ── Card ── */
    .as-card {
        background: #1a1a1a;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
    .as-card-plain {
        box-shadow: none;
        border-color: rgba(255,255,255,0.04);
    }
    .as-card-body {
        padding: 28px 32px;
    }
    .as-card-footer {
        padding: 16px 32px;
        border-top: 1px solid rgba(255,255,255,0.04);
        background: rgba(255,255,255,0.015);
    }

    /* ── Rows ── */
    .as-row {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .as-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .as-row-3col {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }
    .as-divider {
        height: 1px;
        background: rgba(255,255,255,0.05);
        margin: 24px 0;
    }

    /* ── Field ── */
    .as-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .as-field-btn {
        justify-content: flex-end;
        padding-top: 22px;
    }
    .as-field-note {
        justify-content: center;
    }
    .as-field-inline {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    /* ── Label ── */
    .as-label {
        font-size: 13px;
        font-weight: 600;
        color: rgba(255,255,255,0.65);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin: 0;
    }

    /* ── Input ── */
    .as-input {
        width: 100%;
        background: rgba(255,255,255,0.04);
        border: 1.5px solid rgba(255,255,255,0.06);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 500;
        color: #fff;
        font-family: inherit;
        transition: all 0.2s;
        outline: none;
        -webkit-appearance: none;
    }
    .as-input:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.12);
        background: rgba(255,255,255,0.06);
    }
    .as-input::placeholder {
        color: rgba(255,255,255,0.2);
    }
    .as-input-mono {
        font-family: 'SF Mono', 'Menlo', 'Monaco', 'Courier New', monospace;
        font-size: 13px;
        letter-spacing: 0.02em;
    }

    /* ── Input Group ── */
    .as-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }
    .as-input-prefix {
        position: absolute;
        left: 14px;
        font-size: 15px;
        font-weight: 700;
        color: rgba(255,255,255,0.4);
        pointer-events: none;
        z-index: 1;
    }
    .as-input-prefixed {
        padding-left: 32px;
    }

    /* ── Select ── */
    .as-select-wrap {
        position: relative;
    }
    .as-select {
        padding-right: 36px;
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    .as-select-arrow {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        color: rgba(255,255,255,0.25);
        pointer-events: none;
    }

    /* ── Hint ── */
    .as-hint {
        font-size: 12px;
        color: rgba(255,255,255,0.3);
        line-height: 1.4;
    }

    /* ── Button ── */
    .as-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        font-family: inherit;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        background: linear-gradient(135deg, #ff6b00, #e65e00);
        color: #000;
        box-shadow: 0 4px 14px rgba(255, 107, 0, 0.25);
        letter-spacing: 0.01em;
    }
    .as-btn:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 8px 24px rgba(255, 107, 0, 0.35);
    }
    .as-btn:active {
        transform: scale(0.97);
    }
    .as-btn i {
        font-size: 14px;
    }
    .as-btn-secondary {
        background: rgba(255,255,255,0.04);
        color: rgba(255,255,255,0.8);
        box-shadow: none;
        border: 1.5px solid rgba(255,255,255,0.08);
    }
    .as-btn-secondary:hover {
        background: rgba(255,255,255,0.08);
        color: #fff;
        box-shadow: none;
        transform: none;
    }

    /* ── Segmented Control ── */
    .as-segmented-control {
        display: inline-flex;
        background: rgba(255,255,255,0.04);
        border-radius: 10px;
        padding: 3px;
        gap: 2px;
        margin-bottom: 24px;
        width: 100%;
    }
    .as-segmented-item {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.4);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
        letter-spacing: 0.02em;
    }
    .as-segmented-item.active {
        background: #1a1a1a;
        color: #ff6b00;
        box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    }
    .as-segmented-item:hover:not(.active) {
        color: rgba(255,255,255,0.6);
    }
    .as-tab-panel { display: none; }
    .as-tab-panel.active { display: block; }

    /* ── File Upload ── */
    .as-file-upload {
        position: relative;
    }
    .as-file-upload input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }
    .as-file-drop {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 18px;
        border: 1.5px dashed rgba(255,255,255,0.08);
        border-radius: 12px;
        background: rgba(255,255,255,0.015);
        transition: all 0.2s;
        text-align: center;
    }
    .as-file-upload:hover .as-file-drop {
        border-color: rgba(255, 107, 0, 0.3);
        background: rgba(255, 107, 0, 0.03);
    }
    .as-file-icon {
        font-size: 20px;
        color: rgba(255,255,255,0.2);
    }
    .as-file-text {
        font-size: 13px;
        font-weight: 600;
        color: rgba(255,255,255,0.5);
    }
    .as-file-info {
        font-size: 11px;
        color: rgba(255,255,255,0.2);
    }
    .as-file-preview {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        padding: 10px 14px;
        background: rgba(255,255,255,0.02);
        border-radius: 10px;
    }
    .as-file-preview-label {
        font-size: 11px;
        font-weight: 600;
        color: rgba(255,255,255,0.3);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* ── Toggle Switch ── */
    .as-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
    }
    .as-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 30px;
        flex-shrink: 0;
    }
    .as-switch input { opacity: 0; width: 0; height: 0; }
    .as-switch-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: rgba(255,255,255,0.1);
        border-radius: 30px;
        transition: 0.3s;
    }
    .as-switch-slider::before {
        content: '';
        position: absolute;
        left: 3px;
        bottom: 3px;
        width: 24px;
        height: 24px;
        background: #fff;
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .as-switch input:checked + .as-switch-slider {
        background: #ff6b00;
    }
    .as-switch input:checked + .as-switch-slider::before {
        transform: translateX(18px);
    }

    /* ── Badge Grid ── */
    .as-badge-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .as-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.4);
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        cursor: pointer;
        transition: all 0.2s;
        user-select: none;
    }
    .as-badge:hover {
        border-color: rgba(255, 107, 0, 0.3);
        color: rgba(255,255,255,0.6);
    }
    .as-badge.active {
        background: rgba(255, 107, 0, 0.12);
        border-color: rgba(255, 107, 0, 0.25);
        color: #ff6b00;
    }

    /* ── Stat Cards (Dashboard & Performance) ── */
    .as-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }
    .as-stat-card {
        background: #1a1a1a;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    .as-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 35px rgba(0,0,0,0.35);
        border-color: rgba(255,255,255,0.1);
    }
    .as-stat-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        transition: transform 0.3s;
    }
    .as-stat-card:hover .as-stat-icon {
        transform: scale(1.1);
    }
    .as-stat-icon.orange { background: rgba(255,107,0,0.1); color: #ff6b00; }
    .as-stat-icon.green { background: rgba(16,185,129,0.1); color: #10b981; }
    .as-stat-icon.blue { background: rgba(14,165,233,0.1); color: #0ea5e9; }
    .as-stat-icon.yellow { background: rgba(245,158,11,0.1); color: #f59e0b; }
    .as-stat-icon.red { background: rgba(239,68,68,0.1); color: #ef4444; }

    .as-stat-content { flex: 1; }
    .as-stat-label {
        font-size: 11px;
        font-weight: 700;
        color: rgba(255,255,255,0.4);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 4px;
    }
    .as-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        margin: 0;
        letter-spacing: -0.01em;
    }
    .as-stat-sub {
        font-size: 11px;
        color: rgba(255,255,255,0.25);
        margin-top: 6px;
        display: block;
    }

    /* ── Link button ── */
    .as-link {
        color: #ff6b00;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.3s;
        opacity: 0.8;
        letter-spacing: 0.03em;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .as-link:hover {
        color: #fff;
        opacity: 1;
        text-decoration: none;
    }

    /* ── Tip badge ── */
    .as-tip {
        background: rgba(255,107,0,0.08);
        color: #ff6b00;
        padding: 5px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        display: inline-block;
    }
    .as-odds {
        color: #fff;
        font-weight: 800;
        font-size: 16px;
    }

    /* ── Page wrapper ── */
    .as-page {
        max-width: 820px;
        margin: 0 auto;
    }

    /* ── Light Theme Overrides ── */
    .light-theme .as-section-title { color: #222; }
    .light-theme .as-section-subtitle { color: rgba(0,0,0,0.35); }
    .light-theme .as-card {
        background: #fff;
        border-color: rgba(0,0,0,0.06);
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .light-theme .as-card-footer {
        border-top-color: rgba(0,0,0,0.04);
        background: rgba(0,0,0,0.015);
    }
    .light-theme .as-label { color: rgba(0,0,0,0.5); }
    .light-theme .as-input {
        background: rgba(0,0,0,0.03);
        border-color: rgba(0,0,0,0.08);
        color: #222;
    }
    .light-theme .as-input:focus {
        border-color: #ff6b00;
        background: rgba(255,107,0,0.03);
    }
    .light-theme .as-input::placeholder { color: rgba(0,0,0,0.15); }
    .light-theme .as-hint { color: rgba(0,0,0,0.3); }
    .light-theme .as-divider { background: rgba(0,0,0,0.05); }
    .light-theme .as-file-drop {
        border-color: rgba(0,0,0,0.08);
        background: rgba(0,0,0,0.015);
    }
    .light-theme .as-file-icon { color: rgba(0,0,0,0.15); }
    .light-theme .as-file-text { color: rgba(0,0,0,0.4); }
    .light-theme .as-file-info { color: rgba(0,0,0,0.2); }
    .light-theme .as-file-preview { background: rgba(0,0,0,0.02); }
    .light-theme .as-file-preview-label { color: rgba(0,0,0,0.25); }
    .light-theme .as-switch-slider { background: rgba(0,0,0,0.12); }
    .light-theme .as-switch-slider::before { background: #fff; }
    .light-theme .as-badge {
        color: rgba(0,0,0,0.4);
        border-color: rgba(0,0,0,0.08);
        background: rgba(0,0,0,0.02);
    }
    .light-theme .as-badge:hover { border-color: rgba(255,107,0,0.3); }
    .light-theme .as-badge.active {
        background: rgba(255,107,0,0.08);
        border-color: rgba(255,107,0,0.2);
        color: #ff6b00;
    }
    .light-theme .as-segmented-control { background: rgba(0,0,0,0.04); }
    .light-theme .as-segmented-item { color: rgba(0,0,0,0.35); }
    .light-theme .as-segmented-item.active { background: #fff; color: #ff6b00; }
    .light-theme .as-btn-secondary {
        background: rgba(0,0,0,0.03);
        color: rgba(0,0,0,0.5);
        border-color: rgba(0,0,0,0.08);
    }
    .light-theme .as-btn-secondary:hover {
        background: rgba(0,0,0,0.06);
        color: rgba(0,0,0,0.7);
    }
    .light-theme .as-section-icon { background: rgba(255,107,0,0.08); }
    .light-theme .as-select-arrow { color: rgba(0,0,0,0.2); }
    .light-theme .as-input-prefix { color: rgba(0,0,0,0.3); }
    .light-theme .as-toggle-row .as-hint { color: rgba(0,0,0,0.3); }
    .light-theme .as-stat-card {
        background: #fff;
        border-color: rgba(0,0,0,0.06);
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }
    .light-theme .as-stat-card:hover {
        border-color: rgba(0,0,0,0.1);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .light-theme .as-stat-label { color: rgba(0,0,0,0.35); }
    .light-theme .as-stat-value { color: #222; }
    .light-theme .as-stat-sub { color: rgba(0,0,0,0.2); }
    .light-theme .as-link:hover { color: #222; }

    /* ── Apple Table ── */
    .as-table-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.06);
    }
    .as-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }
    .as-table thead th {
        background: rgba(255,255,255,0.03);
        color: rgba(255,255,255,0.5);
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 14px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        white-space: nowrap;
    }
    .as-table tbody td {
        padding: 14px 18px;
        color: rgba(255,255,255,0.75);
        border-bottom: 1px solid rgba(255,255,255,0.03);
        vertical-align: middle;
    }
    .as-table tbody tr:last-child td {
        border-bottom: none;
    }
    .as-table tbody tr:hover {
        background: rgba(255,255,255,0.015);
    }
    .as-table .as-checkbox {
        width: 20px; height: 20px;
        border-radius: 4px;
        border: 1.5px solid rgba(255,255,255,0.15);
        background: transparent;
        cursor: pointer;
        accent-color: #ff6b00;
    }
    .as-table .as-checkbox:checked {
        background: #ff6b00;
        border-color: #ff6b00;
    }

    /* ── Apple Action Buttons ── */
    .as-action-group {
        display: inline-flex;
        gap: 6px;
        align-items: center;
    }
    .as-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px; height: 34px;
        border-radius: 8px;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .as-action-btn.edit {
        background: rgba(255,107,0,0.08);
        color: #ff6b00;
    }
    .as-action-btn.edit:hover {
        background: rgba(255,107,0,0.15);
        color: #ff6b00;
        transform: scale(1.05);
    }
    .as-action-btn.delete {
        background: rgba(239,68,68,0.08);
        color: #ef4444;
    }
    .as-action-btn.delete:hover {
        background: rgba(239,68,68,0.15);
        color: #ef4444;
        transform: scale(1.05);
    }
    .as-action-btn.info {
        background: rgba(14,165,233,0.08);
        color: #0ea5e9;
    }
    .as-action-btn.info:hover {
        background: rgba(14,165,233,0.15);
        color: #0ea5e9;
        transform: scale(1.05);
    }
    .as-action-btn.warning {
        background: rgba(245,158,11,0.08);
        color: #f59e0b;
    }
    .as-action-btn.warning:hover {
        background: rgba(245,158,11,0.15);
        color: #f59e0b;
        transform: scale(1.05);
    }
    .as-action-btn.success {
        background: rgba(16,185,129,0.08);
        color: #10b981;
    }
    .as-action-btn.success:hover {
        background: rgba(16,185,129,0.15);
        color: #10b981;
        transform: scale(1.05);
    }

    /* ── Apple Table Badges ── */
    .as-table-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .as-table-badge.success {
        background: rgba(16,185,129,0.1);
        color: #10b981;
    }
    .as-table-badge.danger {
        background: rgba(239,68,68,0.1);
        color: #ef4444;
    }
    .as-table-badge.warning {
        background: rgba(245,158,11,0.1);
        color: #f59e0b;
    }
    .as-table-badge.info {
        background: rgba(14,165,233,0.1);
        color: #0ea5e9;
    }
    .as-table-badge.primary {
        background: rgba(255,107,0,0.1);
        color: #ff6b00;
    }
    .as-table-badge.secondary {
        background: rgba(255,255,255,0.05);
        color: rgba(255,255,255,0.4);
    }

    /* ── Apple Empty State ── */
    .as-empty {
        text-align: center;
        padding: 48px 24px;
    }
    .as-empty-icon {
        font-size: 36px;
        color: rgba(255,255,255,0.08);
        margin-bottom: 16px;
    }
    .as-empty-title {
        font-size: 16px;
        font-weight: 700;
        color: rgba(255,255,255,0.4);
        margin-bottom: 6px;
    }
    .as-empty-text {
        font-size: 13px;
        color: rgba(255,255,255,0.2);
        margin-bottom: 20px;
    }

    /* ── Apple Filter Bar ── */
    .as-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: end;
        padding-bottom: 0;
    }
    .as-filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 140px;
    }
    .as-filter-group label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(255,255,255,0.3);
        margin: 0;
    }
    .as-filter-group .as-input {
        padding: 8px 12px;
        font-size: 13px;
    }

    /* ── Pill tab filters ── */
    .as-pill-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 12px 0;
    }
    .as-pill-tab {
        padding: 7px 18px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        border: 1.5px solid rgba(255,255,255,0.06);
        background: transparent;
        color: rgba(255,255,255,0.35);
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
        letter-spacing: 0.02em;
        text-decoration: none;
    }
    .as-pill-tab:hover {
        border-color: rgba(255,107,0,0.3);
        color: rgba(255,255,255,0.6);
        text-decoration: none;
    }
    .as-pill-tab.active {
        background: rgba(255,107,0,0.12);
        border-color: rgba(255,107,0,0.25);
        color: #ff6b00;
    }
    .as-pill-tab i {
        margin-right: 4px;
    }

    /* ── Pagination ── */
    .as-pagination {
        display: flex;
        justify-content: center;
        padding: 20px 0 4px;
    }
    .as-pagination .pagination {
        margin: 0;
        gap: 4px;
    }
    .as-pagination .page-link {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        color: rgba(255,255,255,0.5);
        border-radius: 8px !important;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .as-pagination .page-link:hover {
        background: rgba(255,255,255,0.06);
        border-color: rgba(255,107,0,0.3);
        color: #ff6b00;
    }
    .as-pagination .page-item.active .page-link {
        background: rgba(255,107,0,0.12);
        border-color: rgba(255,107,0,0.25);
        color: #ff6b00;
    }
    .as-pagination .page-item.disabled .page-link {
        background: rgba(255,255,255,0.015);
        border-color: rgba(255,255,255,0.03);
        color: rgba(255,255,255,0.15);
    }

    /* ── Date Separator ── */
    .as-date-sep td {
        padding: 10px 18px !important;
        background: rgba(255,107,0,0.04);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #ff6b00;
    }

    /* ── Light theme overrides ── */
    .light-theme .as-table thead th {
        background: rgba(0,0,0,0.02);
        color: rgba(0,0,0,0.4);
        border-bottom-color: rgba(0,0,0,0.06);
    }
    .light-theme .as-table tbody td {
        color: rgba(0,0,0,0.6);
        border-bottom-color: rgba(0,0,0,0.03);
    }
    .light-theme .as-table tbody tr:hover {
        background: rgba(0,0,0,0.01);
    }
    .light-theme .as-table-wrap {
        border-color: rgba(0,0,0,0.06);
    }
    .light-theme .as-table-badge.secondary {
        background: rgba(0,0,0,0.03);
        color: rgba(0,0,0,0.35);
    }
    .light-theme .as-empty-icon { color: rgba(0,0,0,0.08); }
    .light-theme .as-empty-title { color: rgba(0,0,0,0.35); }
    .light-theme .as-empty-text { color: rgba(0,0,0,0.2); }
    .light-theme .as-pill-tab {
        border-color: rgba(0,0,0,0.08);
        color: rgba(0,0,0,0.35);
    }
    .light-theme .as-pill-tab:hover {
        border-color: rgba(255,107,0,0.3);
        color: rgba(0,0,0,0.5);
    }
    .light-theme .as-pill-tab.active {
        background: rgba(255,107,0,0.08);
        border-color: rgba(255,107,0,0.2);
        color: #ff6b00;
    }
    .light-theme .as-pagination .page-link {
        background: rgba(0,0,0,0.02);
        border-color: rgba(0,0,0,0.06);
        color: rgba(0,0,0,0.4);
    }
    .light-theme .as-pagination .page-link:hover {
        background: rgba(0,0,0,0.04);
        border-color: rgba(255,107,0,0.2);
        color: #ff6b00;
    }
    .light-theme .as-pagination .page-item.active .page-link {
        background: rgba(255,107,0,0.08);
        border-color: rgba(255,107,0,0.2);
        color: #ff6b00;
    }
    .light-theme .as-pagination .page-item.disabled .page-link {
        background: rgba(0,0,0,0.01);
        border-color: rgba(0,0,0,0.03);
        color: rgba(0,0,0,0.15);
    }
    .light-theme .as-filter-group label { color: rgba(0,0,0,0.25); }
    .light-theme .as-date-sep td {
        background: rgba(255,107,0,0.04);
        color: #ff6b00;
    }

    /* ── Responsive ── */
    @media (max-width: 992px) {
        .as-stat-grid { grid-template-columns: repeat(2, 1fr); }
        .as-row-2col { grid-template-columns: 1fr; }
        .as-row-3col { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) {
        .as-stat-grid { grid-template-columns: 1fr; }
        .as-card-body { padding: 20px; }
        .as-card-footer { padding: 14px 20px; }
    }
    </style>
    @stack('styles')
</head>

<body>
    <div class="admin-container">
        {{-- Sidebar --}}
        <div class="admin-sidebar">
            <div class="sidebar-logo">
                <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="Logo" class="bm-logo-dark">
                <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="Logo" class="bm-logo-light" style="display:none;">
                <h6>ADMIN PANEL</h6>
            </div>

            <div class="sidebar-profile">
                <div class="admin-name">{{ session('admin_name') }}</div>
                <a href="{{ route('admin.profile') }}" class="btn btn-sm btn-profile">Edit Profile</a>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.predictions.index') }}"
                    class="nav-link {{ request()->routeIs('admin.predictions.index') ? 'active' : '' }}">
                    <i class="fa fa-list"></i>
                    <span>All Predictions</span>
                </a>
                <a href="{{ route('admin.predictions.bankers') }}"
                    class="nav-link {{ request()->routeIs('admin.predictions.bankers') ? 'active' : '' }}">
                    <i class="fa fa-shield text-warning"></i>
                    <span>Banker Tips</span>
                </a>
                <a href="{{ route('admin.fixtures.index') }}"
                    class="nav-link {{ request()->routeIs('admin.fixtures.*') ? 'active' : '' }}">
                    <i class="fa fa-soccer-ball-o"></i>
                    <span>Manage Fixtures</span>
                </a>
                <a href="{{ route('admin.predictions.create') }}"
                    class="nav-link {{ request()->routeIs('admin.predictions.create') ? 'active' : '' }}">
                    <i class="fa fa-plus"></i>
                    <span>New Prediction</span>
                </a>
                <a href="{{ route('admin.vip-plans.index') }}"
                    class="nav-link {{ request()->routeIs('admin.vip-plans.*') ? 'active' : '' }}">
                    <i class="fa fa-star"></i>
                    <span>VIP Plans</span>
                </a>

                {{-- MEMBERSHIP Dropdown --}}
                @php
                    $membershipActive = request()->routeIs('admin.vip-subscriptions.*');
                @endphp
                <a href="#membershipMenu"
                    class="nav-link d-flex align-items-center justify-content-between {{ $membershipActive ? '' : 'collapsed' }}"
                    data-toggle="collapse" role="button" aria-expanded="{{ $membershipActive ? 'true' : 'false' }}"
                    aria-controls="membershipMenu">
                    <span><i class="fa fa-users mr-2"></i> Membership</span>
                    <i class="fa fa-chevron-down small"></i>
                </a>
                <div class="collapse {{ $membershipActive ? 'show' : '' }}" id="membershipMenu">
                    <div class="pl-3 border-left border-secondary ml-3 mt-1 mb-1">
                        <a href="{{ route('admin.vip-subscriptions.create') }}"
                            class="nav-link py-1 {{ request()->routeIs('admin.vip-subscriptions.create') ? 'active' : '' }}">
                            <i class="fa fa-user-plus mr-1 small"></i>
                            <span class="small">Add New Member</span>
                        </a>
                        <a href="{{ route('admin.vip-subscriptions.index') }}"
                            class="nav-link py-1 {{ request()->routeIs('admin.vip-subscriptions.index') && !request()->query('filter') ? 'active' : '' }}">
                            <i class="fa fa-table mr-1 small"></i>
                            <span class="small">All Members</span>
                        </a>
                        <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'subscribed']) }}"
                            class="nav-link py-1 {{ request()->query('filter') === 'subscribed' ? 'active' : '' }}">
                            <i class="fa fa-check-circle mr-1 small text-success"></i>
                            <span class="small">Subscribed Members</span>
                        </a>
                        <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'disabled']) }}"
                            class="nav-link py-1 {{ request()->query('filter') === 'disabled' ? 'active' : '' }}">
                            <i class="fa fa-ban mr-1 small text-danger"></i>
                            <span class="small">Disabled Members</span>
                        </a>
                        <a href="{{ route('admin.vip-subscriptions.index', ['filter' => 'flagged']) }}"
                            class="nav-link py-1 {{ request()->query('filter') === 'flagged' ? 'active' : '' }}">
                            <i class="fa fa-flag mr-1 small text-warning"></i>
                            <span class="small">Flagged Members</span>
                        </a>
                    </div>
                </div>
                <a href="{{ route('admin.vip-results.index') }}"
                    class="nav-link {{ request()->routeIs('admin.vip-results.*') ? 'active' : '' }}">
                    <i class="fa fa-history"></i>
                    <span>VIP Results</span>
                </a>
                <a href="{{ route('admin.performance') }}"
                    class="nav-link {{ request()->routeIs('admin.performance') ? 'active' : '' }}">
                    <i class="fa fa-line-chart"></i>
                    <span>Performance</span>
                </a>
                <a href="{{ route('admin.predictions.update-status') }}"
                    class="nav-link {{ request()->routeIs('admin.predictions.update-status') ? 'active' : '' }}">
                    <i class="fa fa-refresh"></i>
                    <span>Update Status</span>
                </a>
                <a href="{{ route('admin.articles.index') }}"
                    class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                    <i class="fa fa-newspaper-o"></i>
                    <span>Blog Articles</span>
                </a>
                <a href="{{ route('admin.article-categories.index') }}"
                    class="nav-link {{ request()->routeIs('admin.article-categories.*') ? 'active' : '' }}">
                    <i class="fa fa-folder-open"></i>
                    <span>Blog Categories</span>
                </a>
                <a href="{{ route('admin.site-links.index') }}"
                    class="nav-link {{ request()->routeIs('admin.site-links.*') ? 'active' : '' }}">
                    <i class="fa fa-link"></i>
                    <span>Site Links</span>
                </a>
                <a href="{{ route('admin.scripts.index') }}"
                    class="nav-link {{ request()->routeIs('admin.scripts.*') ? 'active' : '' }}">
                    <i class="fa fa-code"></i>
                    <span>Scripts</span>
                </a>
                <a href="{{ route('admin.seo-pages.index') }}"
                    class="nav-link {{ request()->routeIs('admin.seo-pages.*') ? 'active' : '' }}">
                    <i class="fa fa-google"></i>
                    <span>SEO Pages</span>
                </a>
                <a href="{{ route('admin.metadata.index') }}"
                    class="nav-link {{ request()->routeIs('admin.metadata.*') ? 'active' : '' }}">
                    <i class="fa fa-tags"></i>
                    <span>Metadata</span>
                </a>
                <a href="{{ route('admin.settings.index') }}"
                    class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa fa-cog"></i>
                    <span>Settings</span>
                </a>

                @if(session('admin_role') === 'super_admin')
                    <div class="sidebar-divider"></div>
                    <a href="{{ route('admin.admins.index') }}"
                        class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                        <i class="fa fa-users"></i>
                        <span>Manage Admins</span>
                    </a>
                @endif

                <div class="sidebar-divider"></div>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fa fa-sign-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </div>

        {{-- Main Content --}}
        <div class="admin-main">
            <div class="page-header">
                <h4>@yield('page-title', 'Dashboard')</h4>
                <span class="welcome-text">Welcome, {{ session('admin_name') }}</span>
            </div>

            @if(session('success') || session('error'))
                <div id="toast-notification" class="admin-toast {{ session('error') ? 'toast-error' : 'toast-success' }}">
                    <div class="toast-icon">
                        @if(session('error'))
                            <i class="fa fa-times-circle"></i>
                        @else
                            <i class="fa fa-check-circle"></i>
                        @endif
                    </div>
                    <div class="toast-body">
                        <div class="toast-title">{{ session('error') ? 'Error' : 'Success' }}</div>
                        <div class="toast-message">{{ session('success') ?? session('error') }}</div>
                    </div>
                    <button class="toast-close" onclick="closeToast()">&times;</button>
                    <div class="toast-progress-bar" id="toast-progress"></div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <x-confirm-modal />
    @stack('scripts')
    <script>
        function closeToast() {
            var toast = document.getElementById('toast-notification');
            if (toast) {
                toast.classList.add('hide');
                setTimeout(function () { toast.remove(); }, 400);
            }
        }
        // Auto-dismiss after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            var toast = document.getElementById('toast-notification');
            if (toast) {
                setTimeout(closeToast, 5000);
            }
        });
    </script>
</body>

</html>