{{-- Newsletter Popup Modal --}}
<div id="newsletterModal" class="newsletter-modal" style="display: none;">
    <div class="newsletter-overlay"></div>
    <div class="newsletter-container">
        <button class="newsletter-close" id="closeNewsletter">&times;</button>

        <div class="newsletter-content">
            <div class="newsletter-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>

            <h2 class="newsletter-title">Stay Updated!</h2>
            <p class="newsletter-description">
                Get the latest football predictions, expert tips, and match analysis delivered straight to your inbox.
            </p>

            <form id="newsletterForm" class="newsletter-form">
                @csrf
                <div class="newsletter-input-group">
                    <input type="email" name="email" id="newsletterEmail" class="newsletter-input"
                        placeholder="Enter your email address" required>
                    <button type="submit" class="newsletter-submit" id="newsletterSubmitBtn">
                        <span class="submit-text">Subscribe</span>
                        <span class="submit-loader" style="display: none;">
                            <svg class="spinner" viewBox="0 0 50 50">
                                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            </svg>
                        </span>
                    </button>
                </div>

                <div id="newsletterMessage" class="newsletter-message" style="display: none;"></div>
            </form>

            <button class="newsletter-dismiss" id="dismissNewsletter">No thanks, maybe later</button>

            <p class="newsletter-privacy">
                We respect your privacy. Unsubscribe at any time.
            </p>
        </div>
    </div>
</div>

<style>
    .newsletter-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .newsletter-modal.hiding {
        animation: fadeOut 0.3s ease-out forwards;
    }

    .newsletter-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
    }

    .newsletter-container {
        position: relative;
        background: linear-gradient(135deg, #1a1a1a 0%, #222 100%);
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        animation: slideUp 0.4s ease-out;
        overflow: hidden;
        border: 1px solid #333;
    }

    .newsletter-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.08);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 24px;
        color: #999;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .newsletter-close:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        transform: rotate(90deg);
    }

    .newsletter-content {
        padding: 40px 30px;
        text-align: center;
    }

    .newsletter-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #ff6b00 0%, #e65c00 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 4px 20px rgba(255, 107, 0, 0.3);
    }

    .newsletter-title {
        font-size: 28px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 12px 0;
        font-family: 'manrope', sans-serif;
    }

    .newsletter-description {
        font-size: 15px;
        color: #999;
        line-height: 1.6;
        margin: 0 0 30px 0;
        font-family: 'manrope', sans-serif;
    }

    .newsletter-form {
        margin-bottom: 20px;
    }

    .newsletter-input-group {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .newsletter-input {
        flex: 1;
        padding: 14px 18px;
        border: 2px solid #333;
        border-radius: 50px;
        font-size: 15px;
        font-family: 'manrope', sans-serif;
        transition: all 0.3s ease;
        outline: none;
        background: #111;
        color: #fff;
    }

    .newsletter-input:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
    }

    .newsletter-input::placeholder {
        color: #666;
    }

    .newsletter-submit {
        padding: 14px 32px;
        background: linear-gradient(135deg, #ff6b00 0%, #e65c00 100%);
        border: none;
        border-radius: 50px;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'manrope', sans-serif;
        white-space: nowrap;
        min-width: 120px;
        position: relative;
    }

    .newsletter-submit:hover:not(:disabled) {
        background: linear-gradient(135deg, #e65c00 0%, #cc5200 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 0, 0.4);
    }

    .newsletter-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .newsletter-message {
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        margin-top: 15px;
        font-family: 'manrope', sans-serif;
    }

    .newsletter-message.success {
        background: rgba(16, 185, 129, 0.1);
        border-left: 4px solid #10b981;
        color: #34d399;
    }

    .newsletter-message.error {
        background: rgba(239, 68, 68, 0.1);
        border-left: 4px solid #ef4444;
        color: #f87171;
    }

    .newsletter-dismiss {
        background: transparent;
        border: none;
        color: #666;
        font-size: 14px;
        cursor: pointer;
        padding: 8px;
        transition: color 0.3s ease;
        font-family: 'manrope', sans-serif;
    }

    .newsletter-dismiss:hover {
        color: #ff6b00;
        text-decoration: underline;
    }

    .newsletter-privacy {
        font-size: 12px;
        color: #555;
        margin: 15px 0 0 0;
        font-family: 'manrope', sans-serif;
    }

    /* Spinner Animation */
    .spinner {
        animation: rotate 2s linear infinite;
        width: 20px;
        height: 20px;
    }

    .spinner .path {
        stroke: #ff6b00;
        stroke-linecap: round;
        animation: dash 1.5s ease-in-out infinite;
    }

    @keyframes rotate {
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes dash {
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
        .newsletter-container {
            max-width: 95%;
            border-radius: 15px;
        }

        .newsletter-content {
            padding: 30px 20px;
        }

        .newsletter-icon {
            width: 60px;
            height: 60px;
        }

        .newsletter-icon svg {
            width: 32px;
            height: 32px;
        }

        .newsletter-title {
            font-size: 24px;
        }

        .newsletter-description {
            font-size: 14px;
        }

        .newsletter-input-group {
            flex-direction: column;
        }

        .newsletter-submit {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .newsletter-title {
            font-size: 22px;
        }

        .newsletter-description {
            font-size: 13px;
        }

        .newsletter-input {
            padding: 12px 16px;
            font-size: 14px;
        }

        .newsletter-submit {
            padding: 12px 24px;
            font-size: 14px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('newsletterModal');
        const closeBtn = document.getElementById('closeNewsletter');
        const dismissBtn = document.getElementById('dismissNewsletter');
        const form = document.getElementById('newsletterForm');
        const emailInput = document.getElementById('newsletterEmail');
        const submitBtn = document.getElementById('newsletterSubmitBtn');
        const messageDiv = document.getElementById('newsletterMessage');

        // Check if user has already subscribed or dismissed
        const hasSubscribed = localStorage.getItem('newsletterSubscribed');
        const hasDismissed = localStorage.getItem('newsletterDismissed');

        // Only show once the visitor is engaged (scrolled half the page or
        // spent 45s on it) and never on top of the cookie banner: an
        // immediate full-screen popup is an intrusive interstitial for Google.
        if (!hasSubscribed && !hasDismissed) {
            let shown = false;
            let engaged = false;

            function cookieBannerVisible() {
                const banner = document.getElementById('cookieConsent');
                return banner && getComputedStyle(banner).display !== 'none';
            }

            function tryShow() {
                if (shown || !engaged) return;
                if (cookieBannerVisible()) {
                    setTimeout(tryShow, 3000);
                    return;
                }
                shown = true;
                window.removeEventListener('scroll', onScroll);
                modal.style.display = 'flex';
            }

            function onScroll() {
                const scrollable = document.documentElement.scrollHeight - window.innerHeight;
                if (scrollable > 0 && window.scrollY / scrollable >= 0.5) {
                    engaged = true;
                    tryShow();
                }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            setTimeout(() => {
                engaged = true;
                tryShow();
            }, 45000);
        }

        // Close modal function
        function closeModal() {
            modal.classList.add('hiding');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('hiding');
            }, 300);
        }

        // Close button click
        closeBtn.addEventListener('click', function () {
            localStorage.setItem('newsletterDismissed', 'true');
            closeModal();
        });

        // Dismiss button click
        dismissBtn.addEventListener('click', function () {
            localStorage.setItem('newsletterDismissed', 'true');
            closeModal();
        });

        // Close on overlay click
        modal.addEventListener('click', function (e) {
            if (e.target === modal || e.target.classList.contains('newsletter-overlay')) {
                localStorage.setItem('newsletterDismissed', 'true');
                closeModal();
            }
        });

        // Form submission
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const email = emailInput.value.trim();

            if (!email) {
                showMessage('Please enter your email address.', 'error');
                return;
            }

            // Disable submit button and show loader
            submitBtn.disabled = true;
            submitBtn.querySelector('.submit-text').style.display = 'none';
            submitBtn.querySelector('.submit-loader').style.display = 'inline-block';
            messageDiv.style.display = 'none';

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value;

            // Submit via AJAX
            fetch('{{ route("subscribe.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message || data.success) {
                        showMessage(data.message || 'Successfully subscribed to our newsletter!', 'success');
                        localStorage.setItem('newsletterSubscribed', 'true');
                        localStorage.setItem('newsletterEmail', email);

                        // Close modal after 2 seconds
                        setTimeout(() => {
                            closeModal();
                        }, 2000);
                    } else if (data.errors) {
                        const errorMessage = Object.values(data.errors).flat().join(' ');
                        showMessage(errorMessage, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred. Please try again later.', 'error');
                })
                .finally(() => {
                    // Re-enable submit button and hide loader
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.submit-text').style.display = 'inline';
                    submitBtn.querySelector('.submit-loader').style.display = 'none';
                });
        });

        // Show message function
        function showMessage(message, type) {
            messageDiv.textContent = message;
            messageDiv.className = 'newsletter-message ' + type;
            messageDiv.style.display = 'block';
        }
    });
</script>