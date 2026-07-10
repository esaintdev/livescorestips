@extends('admin.layout')

@section('page-title', 'Settings')

@section('content')
<div class="as-page">
    {{-- General Settings --}}
    <section class="as-section">
        <div class="as-section-header">
            <div class="as-section-icon">
                <i class="fa fa-sliders"></i>
            </div>
            <div>
                <h2 class="as-section-title">General</h2>
                <p class="as-section-subtitle">Site name, logos, and social preview</p>
            </div>
        </div>
        <div class="as-card">
            <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="as-card-body">
                    <div class="as-row">
                        <div class="as-field">
                            <label class="as-label">Website Name</label>
                            <input type="text" name="site_name" class="as-input"
                                value="{{ $generalSettings['site_name'] ?? config('app.name') }}" required>
                            <span class="as-hint">Used in browser tabs, emails, and sidebar.</span>
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row as-row-2col">
                        <div class="as-field">
                            <label class="as-label">Logo (Dark)</label>
                            <div class="as-file-upload">
                                <input type="file" name="site_logo" id="site_logo" accept="image/*">
                                <div class="as-file-drop">
                                    <div class="as-file-icon">
                                        <i class="fa fa-cloud-upload"></i>
                                    </div>
                                    <span class="as-file-text">Choose image…</span>
                                    <span class="as-file-info">PNG, SVG &middot; 200x50px ideal</span>
                                </div>
                            </div>
                            @if(!empty($generalSettings['site_logo']))
                                <div class="as-file-preview">
                                    <span class="as-file-preview-label">Current</span>
                                    <img src="{{ asset($generalSettings['site_logo']) }}" height="28">
                                </div>
                            @endif
                        </div>
                        <div class="as-field">
                            <label class="as-label">Logo (Light)</label>
                            <div class="as-file-upload">
                                <input type="file" name="site_logo_light" id="site_logo_light" accept="image/*">
                                <div class="as-file-drop">
                                    <div class="as-file-icon">
                                        <i class="fa fa-cloud-upload"></i>
                                    </div>
                                    <span class="as-file-text">Choose image…</span>
                                    <span class="as-file-info">PNG, SVG &middot; dark text variant</span>
                                </div>
                            </div>
                            @if(!empty($generalSettings['site_logo_light']))
                                <div class="as-file-preview">
                                    <span class="as-file-preview-label">Current</span>
                                    <img src="{{ asset($generalSettings['site_logo_light']) }}" height="28" style="filter:invert(1)">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row">
                        <div class="as-field">
                            <label class="as-label">OG Image (Share Preview)</label>
                            <div class="as-file-upload">
                                <input type="file" name="og_image" id="og_image" accept="image/*">
                                <div class="as-file-drop">
                                    <div class="as-file-icon">
                                        <i class="fa fa-picture-o"></i>
                                    </div>
                                    <span class="as-file-text">Choose image…</span>
                                    <span class="as-file-info">1200x630px &middot; shown on Facebook, Twitter, WhatsApp</span>
                                </div>
                            </div>
                            @if(!empty($generalSettings['og_image']))
                                <div class="as-file-preview">
                                    <span class="as-file-preview-label">Current</span>
                                    <img src="{{ asset($generalSettings['og_image']) }}" height="66" style="border-radius:8px;max-width:220px;object-fit:cover;">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row as-row-2col">
                        <div class="as-field">
                            <label class="as-label">Cookie Pay-to-Reject Price</label>
                            <div class="as-input-group">
                                <span class="as-input-prefix">&pound;</span>
                                <input type="number" name="cookie_price" class="as-input as-input-prefixed"
                                    value="{{ $generalSettings['cookie_price'] ?? '10' }}"
                                    min="0" step="0.50" placeholder="10">
                            </div>
                            <span class="as-hint">Set to 0 to disable Pay to Reject.</span>
                        </div>
                    </div>
                </div>
                <div class="as-card-footer">
                    <button type="submit" class="as-btn">
                        <i class="fa fa-check"></i> Save General Settings
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Email Configuration --}}
    <section class="as-section">
        <div class="as-section-header">
            <div class="as-section-icon">
                <i class="fa fa-envelope-open"></i>
            </div>
            <div>
                <h2 class="as-section-title">Email (SMTP)</h2>
                <p class="as-section-subtitle">Configure mail servers and sender identities</p>
            </div>
        </div>
        <div class="as-card">
            <form action="{{ route('admin.settings.email.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="as-card-body">
                    <div class="as-segmented-control">
                        <button type="button" class="as-segmented-item active" data-tab="admin">
                            <i class="fa fa-shield"></i> Main
                        </button>
                        <button type="button" class="as-segmented-item" data-tab="advert">
                            <i class="fa fa-bullhorn"></i> Advert
                        </button>
                        <button type="button" class="as-segmented-item" data-tab="info">
                            <i class="fa fa-life-ring"></i> Support
                        </button>
                    </div>

                    {{-- Main Tab --}}
                    <div class="as-tab-panel active" id="tab-admin">
                        <div class="as-row as-row-2col">
                            <div class="as-field">
                                <label class="as-label">Driver</label>
                                <div class="as-select-wrap">
                                    <select name="mail_driver" class="as-input as-select" required>
                                        <option value="smtp" {{ ($emailSettings['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="sendmail" {{ ($emailSettings['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    </select>
                                    <i class="fa fa-chevron-down as-select-arrow"></i>
                                </div>
                            </div>
                            <div class="as-field">
                                <label class="as-label">Host</label>
                                <input type="text" name="mail_host" class="as-input"
                                    value="{{ $emailSettings['mail_host'] ?? '' }}" placeholder="mail.yourdomain.com" required>
                            </div>
                        </div>
                        <div class="as-row as-row-3col">
                            <div class="as-field">
                                <label class="as-label">Port</label>
                                <input type="number" name="mail_port" class="as-input"
                                    value="{{ $emailSettings['mail_port'] ?? '587' }}" placeholder="587" required>
                            </div>
                            <div class="as-field">
                                <label class="as-label">Encryption</label>
                                <div class="as-select-wrap">
                                    <select name="mail_encryption" class="as-input as-select">
                                        <option value="tls" {{ ($emailSettings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ ($emailSettings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="" {{ ($emailSettings['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                    </select>
                                    <i class="fa fa-chevron-down as-select-arrow"></i>
                                </div>
                            </div>
                            <div class="as-field">
                                <label class="as-label">Username</label>
                                <input type="text" name="mail_username" class="as-input"
                                    value="{{ $emailSettings['mail_username'] ?? '' }}" placeholder="info@livescorestips.com" required>
                            </div>
                        </div>
                        <div class="as-row as-row-3col">
                            <div class="as-field">
                                <label class="as-label">Password</label>
                                <input type="password" name="mail_password" class="as-input"
                                    value="{{ $emailSettings['mail_password'] ?? '' }}" placeholder="SMTP password" required>
                            </div>
                            <div class="as-field">
                                <label class="as-label">From Email</label>
                                <input type="email" name="mail_from_address" class="as-input"
                                    value="{{ $emailSettings['mail_from_address'] ?? '' }}" placeholder="info@livescorestips.com" required>
                            </div>
                            <div class="as-field">
                                <label class="as-label">From Name</label>
                                <input type="text" name="mail_from_name" class="as-input"
                                    value="{{ $emailSettings['mail_from_name'] ?? 'Admin' }}" placeholder="Site Admin" required>
                            </div>
                        </div>
                    </div>

                    {{-- Advert Tab --}}
                    <div class="as-tab-panel" id="tab-advert">
                        <div class="as-row as-row-3col">
                            <div class="as-field">
                                <label class="as-label">SMTP Username</label>
                                <input type="text" name="mail_advert_username" class="as-input"
                                    value="{{ $emailSettings['mail_advert_username'] ?? '' }}" placeholder="info@livescorestips.com">
                            </div>
                            <div class="as-field">
                                <label class="as-label">SMTP Password</label>
                                <input type="password" name="mail_advert_password" class="as-input"
                                    value="{{ $emailSettings['mail_advert_password'] ?? '' }}" placeholder="Password">
                            </div>
                            <div class="as-field">
                                <label class="as-label">From Email</label>
                                <input type="email" name="mail_advert_from_address" class="as-input"
                                    value="{{ $emailSettings['mail_advert_from_address'] ?? '' }}" placeholder="info@livescorestips.com">
                            </div>
                        </div>
                        <div class="as-row as-row-2col">
                            <div class="as-field">
                                <label class="as-label">From Name</label>
                                <input type="text" name="mail_advert_from_name" class="as-input"
                                    value="{{ $emailSettings['mail_advert_from_name'] ?? 'Advertisements' }}" placeholder="Marketing Team">
                            </div>
                            <div class="as-field as-field-note">
                                <span class="as-hint"><i class="fa fa-info-circle"></i> Uses the same Host, Port &amp; Encryption as Main SMTP.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Info/Support Tab --}}
                    <div class="as-tab-panel" id="tab-info">
                        <div class="as-row as-row-3col">
                            <div class="as-field">
                                <label class="as-label">SMTP Username</label>
                                <input type="text" name="mail_info_username" class="as-input"
                                    value="{{ $emailSettings['mail_info_username'] ?? '' }}" placeholder="info@livescorestips.com">
                            </div>
                            <div class="as-field">
                                <label class="as-label">SMTP Password</label>
                                <input type="password" name="mail_info_password" class="as-input"
                                    value="{{ $emailSettings['mail_info_password'] ?? '' }}" placeholder="Password">
                            </div>
                            <div class="as-field">
                                <label class="as-label">From Email</label>
                                <input type="email" name="mail_info_from_address" class="as-input"
                                    value="{{ $emailSettings['mail_info_from_address'] ?? '' }}" placeholder="marketing@livescorestips.com">
                            </div>
                        </div>
                        <div class="as-row as-row-2col">
                            <div class="as-field">
                                <label class="as-label">From Name</label>
                                <input type="text" name="mail_info_from_name" class="as-input"
                                    value="{{ $emailSettings['mail_info_from_name'] ?? 'Support Team' }}" placeholder="Help Center">
                            </div>
                            <div class="as-field as-field-note">
                                <span class="as-hint"><i class="fa fa-info-circle"></i> Uses the same Host, Port &amp; Encryption as Main SMTP.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="as-card-footer">
                    <button type="submit" class="as-btn">
                        <i class="fa fa-check"></i> Save All Email Settings
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Test Email --}}
    <section class="as-section">
        <div class="as-section-header">
            <div class="as-section-icon">
                <i class="fa fa-paper-plane"></i>
            </div>
            <div>
                <h2 class="as-section-title">Test Email</h2>
                <p class="as-section-subtitle">Verify your SMTP configuration</p>
            </div>
        </div>
        <div class="as-card">
            <form action="{{ route('admin.settings.email.test') }}" method="POST">
                @csrf
                <div class="as-card-body">
                    <div class="as-row as-row-3col">
                        <div class="as-field">
                            <label class="as-label">Recipient</label>
                            <input type="email" name="test_email" class="as-input"
                                value="{{ $emailSettings['mail_from_address'] ?? '' }}"
                                placeholder="you@example.com" required>
                        </div>
                        <div class="as-field">
                            <label class="as-label">Account</label>
                            <div class="as-select-wrap">
                                <select name="test_mailer" class="as-input as-select">
                                    <option value="smtp">Main SMTP</option>
                                    <option value="smtp_advert">Advert SMTP</option>
                                    <option value="smtp_info">Support SMTP</option>
                                </select>
                                <i class="fa fa-chevron-down as-select-arrow"></i>
                            </div>
                        </div>
                        <div class="as-field as-field-btn">
                            <button type="submit" class="as-btn as-btn-secondary">
                                <i class="fa fa-send"></i> Send Test
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Telegram --}}
    <section class="as-section">
        <div class="as-section-header">
            <div class="as-section-icon">
                <i class="fa fa-telegram"></i>
            </div>
            <div>
                <h2 class="as-section-title">Telegram</h2>
                <p class="as-section-subtitle">Bot integration and channel notifications</p>
            </div>
        </div>
        <div class="as-card">
            <form action="{{ route('admin.settings.telegram.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="as-card-body">
                    <div class="as-row">
                        <div class="as-field">
                            <div class="as-toggle-row">
                                <div>
                                    <label class="as-label" style="margin-bottom:2px;">Enable Telegram Notifications</label>
                                    <span class="as-hint" style="margin:0;">Auto-post predictions to your channel</span>
                                </div>
                                <label class="as-switch">
                                    <input type="checkbox" name="telegram_enabled" value="1"
                                        {{ ($telegramSettings['telegram_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <span class="as-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row as-row-2col">
                        <div class="as-field">
                            <label class="as-label">Bot Token</label>
                            <input type="text" name="telegram_bot_token" class="as-input"
                                value="{{ $telegramSettings['telegram_bot_token'] ?? '' }}"
                                placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                            <span class="as-hint">Get from <strong>@BotFather</strong> on Telegram</span>
                        </div>
                        <div class="as-field">
                            <label class="as-label">Channel ID</label>
                            <input type="text" name="telegram_channel_id" class="as-input"
                                value="{{ $telegramSettings['telegram_channel_id'] ?? '' }}"
                                placeholder="-1001234567890">
                            <span class="as-hint">Include the minus sign prefix</span>
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row">
                        <div class="as-field">
                            <label class="as-label">Public Telegram Link</label>
                            <input type="url" name="telegram_link" class="as-input"
                                value="{{ $telegramSettings['telegram_link'] ?? 'https://t.me/sportvillaprediction' }}"
                                placeholder="https://t.me/yourchannel">
                            <span class="as-hint">Displayed on hero banner, footer, and contact page.</span>
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row">
                        <div class="as-field">
                            <label class="as-label">Category Filter</label>
                            <input type="text" name="telegram_categories" class="as-input"
                                value="{{ $telegramSettings['telegram_categories'] ?? '' }}"
                                placeholder="Draw, Over 2.5 Goals, Double Chance">
                            <span class="as-hint">Leave empty for all. Separate categories with commas.</span>
                        </div>
                    </div>
                </div>
                <div class="as-card-footer">
                    <button type="submit" class="as-btn">
                        <i class="fa fa-check"></i> Save Telegram Settings
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Test Telegram --}}
    <section class="as-section">
        <div class="as-card as-card-plain">
            <form action="{{ route('admin.settings.telegram.test') }}" method="POST">
                @csrf
                <div class="as-card-body">
                    <div class="as-row">
                        <div class="as-field as-field-inline">
                            <div>
                                <label class="as-label" style="margin-bottom:2px;">Test Telegram Connection</label>
                                <span class="as-hint" style="margin:0;">Send a test message to verify your bot</span>
                            </div>
                            <button type="submit" class="as-btn as-btn-secondary">
                                <i class="fa fa-telegram"></i> Test Connection
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Auto-Prediction Settings --}}
    <section class="as-section">
        <div class="as-section-header">
            <div class="as-section-icon">
                <i class="fa fa-robot"></i>
            </div>
            <div>
                <h2 class="as-section-title">Auto-Prediction</h2>
                <p class="as-section-subtitle">Priority leagues for the auto-predictor</p>
            </div>
        </div>
        <div class="as-card">
            <form action="{{ route('admin.settings.prediction.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="as-card-body">
                    <div class="as-row">
                        <div class="as-field">
                            <div class="as-badge-grid">
                                <span class="as-badge" data-id="1">World Cup</span>
                                <span class="as-badge" data-id="4">Euro</span>
                                <span class="as-badge" data-id="9">Copa America</span>
                                <span class="as-badge" data-id="6">AFCON</span>
                                <span class="as-badge" data-id="39">Premier League</span>
                                <span class="as-badge" data-id="140">La Liga</span>
                                <span class="as-badge" data-id="135">Serie A</span>
                                <span class="as-badge" data-id="78">Bundesliga</span>
                                <span class="as-badge" data-id="61">Ligue 1</span>
                                <span class="as-badge" data-id="2">Champions League</span>
                                <span class="as-badge" data-id="3">Europa League</span>
                                <span class="as-badge" data-id="848">Conf. League</span>
                                <span class="as-badge" data-id="94">Primeira Liga</span>
                                <span class="as-badge" data-id="88">Eredivisie</span>
                            </div>
                        </div>
                    </div>
                    <div class="as-divider"></div>
                    <div class="as-row">
                        <div class="as-field">
                            <label class="as-label">Priority League IDs</label>
                            <input type="text" name="priority_leagues" id="priority_leagues" class="as-input as-input-mono"
                                value="{{ $predictionSettings['priority_leagues'] ?? '1,4,9,39,48,140,135,78,61,94,88,2,3,848' }}"
                                placeholder="1,4,9,39,140,135,78,61,2,3,848">
                            <span class="as-hint">Click a badge above to toggle. Comma-separated IDs.</span>
                        </div>
                    </div>
                </div>
                <div class="as-card-footer">
                    <button type="submit" class="as-btn">
                        <i class="fa fa-check"></i> Save Prediction Settings
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // File upload - show filename
    document.querySelectorAll('.as-file-upload input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const drop = this.closest('.as-file-upload').querySelector('.as-file-text');
            if (this.files && this.files[0]) {
                drop.textContent = this.files[0].name;
            }
        });
    });

    // Segmented control tabs
    const segments = document.querySelectorAll('.as-segmented-item');
    segments.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tab = this.getAttribute('data-tab');
            segments.forEach(function (s) { s.classList.remove('active'); });
            this.classList.add('active');
            document.querySelectorAll('.as-tab-panel').forEach(function (p) { p.classList.remove('active'); });
            const target = document.getElementById('tab-' + tab);
            if (target) target.classList.add('active');
        });
    });

    // Badge toggles for league IDs
    const input = document.getElementById('priority_leagues');
    if (input) {
        function syncBadges() {
            const vals = input.value.split(',').map(function (v) { return v.trim(); });
            document.querySelectorAll('.as-badge').forEach(function (badge) {
                const id = badge.getAttribute('data-id');
                if (vals.includes(id)) {
                    badge.classList.add('active');
                } else {
                    badge.classList.remove('active');
                }
            });
        }
        syncBadges();
        document.querySelectorAll('.as-badge').forEach(function (badge) {
            badge.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const vals = input.value.split(',').map(function (v) { return v.trim(); }).filter(Boolean);
                const idx = vals.indexOf(id);
                if (idx > -1) {
                    vals.splice(idx, 1);
                } else {
                    vals.push(id);
                }
                input.value = vals.join(',');
                syncBadges();
            });
        });
        input.addEventListener('input', syncBadges);
    }
});
</script>
@endpush

