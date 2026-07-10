@php
    $cookiePrice = (float) (\App\Models\Setting::get('cookie_price', '10'));
@endphp

{{-- Cookie Consent Banner --}}
<div id="cookieConsent" class="cookie-consent" style="display: none;">
    <div class="cookie-consent-container">
        <div class="cookie-consent-content">
            <div class="cookie-consent-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"></path>
                    <path d="M8.5 8.5v.01"></path>
                    <path d="M16 15.5v.01"></path>
                    <path d="M12 12v.01"></path>
                    <path d="M11 17v.01"></path>
                    <path d="M7 14v.01"></path>
                </svg>
            </div>
            <div class="cookie-consent-text">
                <h4>We Value Your Privacy</h4>
                <p>We use cookies to enhance your browsing experience, provide personalized content, and analyze our
                    traffic. By clicking "Accept All", you consent to our use of cookies.</p>
            </div>
        </div>
        <div class="cookie-consent-actions">
            @if($cookiePrice > 0)
                <button id="cookieReject" class="cookie-btn cookie-btn-secondary">
                    <span id="cookiePayText">Pay to Reject</span>
                    <span id="cookiePayLoader" style="display:none;">
                        <svg class="spinner" viewBox="0 0 50 50" width="18" height="18">
                            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                        </svg>
                    </span>
                </button>
            @endif
            <button id="cookieAccept" class="cookie-btn cookie-btn-primary">
                Accept All
            </button>
        </div>
    </div>
</div>

<style>
    .cookie-consent {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #121212;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        padding: 20px;
        animation: slideUp 0.5s ease-out;
        border-top: 3px solid #ff6b00;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }

        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }

    .cookie-consent.hiding {
        animation: slideDown 0.3s ease-out forwards;
    }

    .cookie-consent-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .cookie-consent-content {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        flex: 1;
        min-width: 300px;
    }

    .cookie-consent-icon {
        color: #ff6b00;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .cookie-consent-icon svg {
        width: 32px;
        height: 32px;
    }

    .cookie-consent-text h4 {
        color: #ffffff;
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 600;
        font-family: 'manrope', sans-serif;
    }

    .cookie-consent-text p {
        color: #bbb;
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
    }

    .cookie-consent-actions {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }

    .cookie-btn {
        padding: 12px 28px;
        border: none;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        font-family: 'manrope', sans-serif;
    }

    .cookie-btn-primary {
        background: linear-gradient(135deg, #ff6b00 0%, #e65c00 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
    }

    .cookie-btn-primary:hover {
        background: linear-gradient(135deg, #e65c00 0%, #cc5200 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 0, 0.4);
    }

    .cookie-btn-secondary {
        background: transparent;
        color: #ffffff;
        border: 2px solid #555;
    }

    .cookie-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: #888;
        transform: translateY(-2px);
    }

    .cookie-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    #cookiePayLoader .spinner {
        animation: cookieSpin 1s linear infinite;
        display: block;
    }

    #cookiePayLoader .spinner .path {
        stroke: #fff;
        stroke-linecap: round;
        animation: cookieDash 1.5s ease-in-out infinite;
    }

    @keyframes cookieSpin {
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes cookieDash {
        0% {
            stroke-dasharray: 1, 150;
            stroke-dashoffset: 0;
        }

        50% {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: -35;
        }

        100% {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: -124;
        }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .cookie-consent {
            padding: 15px;
        }

        .cookie-consent-container {
            flex-direction: column;
            align-items: stretch;
        }

        .cookie-consent-content {
            flex-direction: column;
            text-align: center;
            align-items: center;
        }

        .cookie-consent-icon {
            margin-top: 0;
        }

        .cookie-consent-text h4 {
            font-size: 16px;
        }

        .cookie-consent-text p {
            font-size: 13px;
        }

        .cookie-consent-actions {
            width: 100%;
            flex-direction: column-reverse;
        }

        .cookie-btn {
            width: 100%;
            padding: 14px 20px;
        }
    }

    @media (max-width: 480px) {
        .cookie-consent-icon svg {
            width: 28px;
            height: 28px;
        }

        .cookie-consent-text h4 {
            font-size: 15px;
        }

        .cookie-consent-text p {
            font-size: 12px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cookieConsent = document.getElementById('cookieConsent');
        const acceptBtn = document.getElementById('cookieAccept');
        const rejectBtn = document.getElementById('cookieReject');
        const payText = document.getElementById('cookiePayText');
        const payLoader = document.getElementById('cookiePayLoader');

        // Check for payment callback success
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('cookie_paid') === 'success') {
            localStorage.setItem('cookieConsent', 'rejected');
            localStorage.setItem('cookieConsentDate', new Date().toISOString());
            // Clean URL
            var url = window.location.pathname + window.location.hash;
            window.history.replaceState({}, '', url);
            if (cookieConsent) {
                cookieConsent.style.display = 'none';
            }
        }

        // Check if user has already made a choice
        const cookieChoice = localStorage.getItem('cookieConsent');

        if (!cookieChoice) {
            // Show banner after a short delay for better UX
            setTimeout(() => {
                cookieConsent.style.display = 'block';
            }, 1000);
        }

        // Accept cookies
        acceptBtn.addEventListener('click', function () {
            localStorage.setItem('cookieConsent', 'accepted');
            localStorage.setItem('cookieConsentDate', new Date().toISOString());
            hideBanner();
        });

        // Pay to Reject
        if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            rejectBtn.disabled = true;
            payText.style.display = 'none';
            payLoader.style.display = 'inline-block';

            window.location.href = '{{ route("cookie.pay") }}';
        });
        }

        function hideBanner() {
            cookieConsent.classList.add('hiding');
            setTimeout(() => {
                cookieConsent.style.display = 'none';
                cookieConsent.classList.remove('hiding');
            }, 300);
        }

        // Spinner animation (if not already defined by newsletter)
        if (!document.getElementById('cookieSpinnerStyle')) {
            var style = document.createElement('style');
            style.id = 'cookieSpinnerStyle';
            style.textContent = '@keyframes cookieSpin { 100% { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }
    });
</script>