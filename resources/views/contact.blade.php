@extends('layouts.app')

@section('title', $metadata['page_title'] ?? '')
@section('description', $metadata['page_description'] ?? 'Get in touch with ' . config('app.name') . '. We\'re here to help you with any questions or feedback.')
@section('keywords', $metadata['page_keywords'] ?? 'contact us, customer support, football predictions')

@section('content')
    <div class="bm-contact-page">
        @if(session('success'))
            <div class="bm-contact-alert">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Hero --}}
        <div class="bm-contact-hero">
            <h1 class="bm-contact-hero-title">{{ $metadata['page_subheader'] ?? 'Contact Us' }}</h1>
            @if(isset($metadata['page_description']) && !empty($metadata['page_description']))
                <p class="bm-contact-hero-desc">{{ $metadata['page_description'] }}</p>
            @else
                <p class="bm-contact-hero-desc">Please feel free to send us any questions or suggestions! We are always in touch. All your requests will be processed as soon as possible.</p>
            @endif
        </div>

        {{-- Form Card (top) --}}
        <div class="bm-contact-card mb-4">
            <h3 class="bm-contact-card-title">Send Us a Message</h3>
            <p class="bm-contact-card-text">Fill out the form below and we'll get back to you as soon as possible.</p>

            <form method="POST" action="{{ route('contact.submit') }}" class="bm-contact-form">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="bm-contact-form-label">Your Name</label>
                        <input type="text" name="name" class="bm-contact-form-input" placeholder="John Doe" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="bm-contact-form-label">Your Email</label>
                        <input type="email" name="email" class="bm-contact-form-input" placeholder="john@example.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="bm-contact-form-label">Topic</label>
                    <select name="topic" class="bm-contact-form-input bm-contact-form-select" required>
                        <option value="" disabled selected>Select a topic</option>
                        <option value="general">General Inquiry</option>
                        <option value="support">Support</option>
                        <option value="advertising">Advertising</option>
                        <option value="partnership">Partnership</option>
                        <option value="feedback">Feedback</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="bm-contact-form-label">Message</label>
                    <textarea name="message" class="bm-contact-form-input bm-contact-form-textarea" rows="5" placeholder="Write your message here..." required></textarea>
                </div>

                <button type="submit" class="bm-contact-form-btn">
                    <i class="fa fa-paper-plane mr-2"></i> Send Message
                </button>
            </form>
        </div>

        {{-- Contact Info Card (bottom) --}}
        <div class="bm-contact-card">
            <h3 class="bm-contact-card-title">Get in Touch</h3>
            <p class="bm-contact-card-text">Have a question, feedback, or need help? Reach out to us through any of the channels below.</p>

            <div class="bm-contact-info-list">
                <div class="bm-contact-info-item">
                    <div class="bm-contact-info-icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div>
                        <div class="bm-contact-info-label">Email</div>
                        <a href="mailto:marketing@livescorestips.com" class="bm-contact-info-value">marketing@livescorestips.com</a>
                    </div>
                </div>

                <div class="bm-contact-info-item">
                    <div class="bm-contact-info-icon">
                        <i class="fa fa-whatsapp"></i>
                    </div>
                    <div>
                        <div class="bm-contact-info-label">WhatsApp</div>
                        <a href="https://wa.me/2348121855275" target="_blank" class="bm-contact-info-value">+234 812 185 5275</a>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($metadata['footer_content']) && !empty($metadata['footer_content']))
            <div class="mt-5 bm-contact-additional">
                {!! $metadata['footer_content'] !!}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .bm-contact-page {
        padding: 40px 0;
    }

    .bm-contact-hero {
        margin-bottom: 40px;
    }

    .bm-contact-hero-title {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        margin-bottom: 12px;
    }

    .bm-contact-hero-desc {
        color: rgba(255, 255, 255, 0.6);
        font-size: 15px;
        line-height: 1.6;
        max-width: 600px;
    }

    .bm-contact-card {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 32px;
    }

    .bm-contact-card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .bm-contact-card-text {
        color: rgba(255, 255, 255, 0.55);
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    .bm-contact-info-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .bm-contact-info-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .bm-contact-info-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #ff6b00;
    }

    .bm-contact-info-label {
        color: rgba(255, 255, 255, 0.4);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .bm-contact-info-value {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s;
    }

    .bm-contact-info-value:hover {
        color: #ff6b00;
    }

    .bm-contact-form-label {
        display: block;
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .bm-contact-form-input {
        width: 100%;
        padding: 12px 16px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .bm-contact-form-input:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
    }

    .bm-contact-form-input::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .bm-contact-form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px;
        padding-right: 40px;
        cursor: pointer;
    }

    .bm-contact-form-select option {
        background: #1a1a2e;
        color: #fff;
    }

    .bm-contact-form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .bm-contact-form-btn {
        display: inline-flex;
        align-items: center;
        padding: 14px 32px;
        background: #ff6b00;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
    }

    .bm-contact-form-btn:hover {
        background: #e66000;
        transform: translateY(-1px);
    }

    .bm-contact-alert {
        padding: 16px 20px;
        background: transparent;
        border: 1px solid rgba(40, 167, 69, 0.2);
        border-radius: 8px;
        color: #28a745;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 24px;
    }

    .bm-contact-additional {
        color: rgba(255, 255, 255, 0.6);
        font-size: 14px;
        line-height: 1.7;
    }

    {{-- Light theme overrides --}}
    .light-theme .bm-contact-hero-title {
        color: #222;
    }

    .light-theme .bm-contact-hero-desc {
        color: rgba(0, 0, 0, 0.55);
    }

    .light-theme .bm-contact-card {
        border-color: rgba(0, 0, 0, 0.08);
    }

    .light-theme .bm-contact-card-title {
        color: #222;
    }

    .light-theme .bm-contact-card-text {
        color: rgba(0, 0, 0, 0.55);
    }

    .light-theme .bm-contact-info-label {
        color: rgba(0, 0, 0, 0.4);
    }

    .light-theme .bm-contact-info-value {
        color: #222;
    }

    .light-theme .bm-contact-info-value:hover {
        color: #ff6b00;
    }

    .light-theme .bm-contact-form-label {
        color: rgba(0, 0, 0, 0.7);
    }

    .light-theme .bm-contact-form-input {
        background: transparent;
        border-color: rgba(0, 0, 0, 0.12);
        color: #222;
    }

    .light-theme .bm-contact-form-input:focus {
        border-color: #ff6b00;
        box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
    }

    .light-theme .bm-contact-form-input::placeholder {
        color: rgba(0, 0, 0, 0.3);
    }

    .light-theme .bm-contact-form-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(0,0,0,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    }

    .light-theme .bm-contact-form-select option {
        background: #ffffff;
        color: #222;
    }

    .light-theme .bm-contact-info-icon {
        border-color: rgba(0, 0, 0, 0.12);
    }

    .light-theme .bm-contact-additional {
        color: rgba(0, 0, 0, 0.6);
    }

    @media (max-width: 768px) {
        .bm-contact-hero-title {
            font-size: 22px;
        }

        .bm-contact-card {
            padding: 24px;
        }
    }
</style>
@endpush
