<header class="bm-top-navbar">
    <div class="bm-navbar-left">
        <div class="bm-navbar-logo">
            <a href="{{ route('home') }}">
                <img src="{{ asset($siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-logo-dark">
                <img src="{{ asset($siteLogoLight ?? $siteLogo ?? 'images/logo.png') }}" alt="{{ config('app.name') }}" class="bm-logo-light" style="display:none;">
            </a>
        </div>
        {{-- Hamburger: mobile only --}}
        <button id="bm-sidebar-toggle" class="bm-hamburger d-lg-none" title="Open sidebar" aria-label="Open sidebar">
            <span></span><span></span><span></span>
        </button>
    </div>
    <nav class="bm-horizontal-nav d-none d-lg-flex">
        <a href="{{ route('home') }}" class="bm-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
        <a href="{{ route('predictions.all') }}" class="bm-nav-link {{ request()->routeIs('predictions.all') ? 'active' : '' }}">Predictions</a>
        <a href="{{ route('vip.index') }}" class="bm-nav-link {{ request()->routeIs('vip.index') ? 'active' : '' }}">VIP</a>
        <a href="{{ route('blog.index') }}" class="bm-nav-link {{ request()->routeIs('blog.index') ? 'active' : '' }}">Blog</a>
        <a href="{{ route('recent-winnings.index') }}" class="bm-nav-link {{ request()->routeIs('recent-winnings.index') ? 'active' : '' }}">Winnings</a>
        @php
            $hasVipResults = isset($vipResults) && $vipResults->count() > 0;
        @endphp
        @if($hasVipResults || request()->routeIs('vip-results.*'))
            <a href="{{ route('vip-results.index') }}" class="bm-nav-link {{ request()->routeIs('vip-results.*') ? 'active' : '' }}">VIP Results</a>
        @endif
        {{-- Categories Dropdown --}}
        <div class="bm-nav-dropdown">
            <a href="#" class="bm-nav-link {{ request()->routeIs('category.*') ? 'active' : '' }}">
                Categories <i class="fa fa-caret-down" style="margin-left: 4px; font-size: 10px;"></i>
            </a>
            <div class="bm-nav-dropdown-menu">
                <a href="{{ route('banker.show') }}" class="bm-nav-dropdown-item {{ request()->routeIs('banker.*') ? 'active' : '' }}">Banker Tips</a>
                <div class="bm-nav-dropdown-divider"></div>
                <a href="{{ route('category.show', 'over-2-5-goals') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'over-2-5-goals' ? 'active' : '' }}">Over 2.5 Goals</a>
                <a href="{{ route('category.show', 'over-1-5-goals') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'over-1-5-goals' ? 'active' : '' }}">Over 1.5 Goals</a>
                <a href="{{ route('category.show', 'btts-gg') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'btts-gg' ? 'active' : '' }}">BTTS/GG</a>
                <a href="{{ route('category.show', 'double-chance') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'double-chance' ? 'active' : '' }}">Double Chance</a>
                <a href="{{ route('category.show', 'draws') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'draws' ? 'active' : '' }}">Draws</a>
                <a href="{{ route('category.show', 'home-wins') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'home-wins' ? 'active' : '' }}">Home Wins</a>
                <a href="{{ route('category.show', 'away-wins') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'away-wins' ? 'active' : '' }}">Away Wins</a>
                <a href="{{ route('category.show', 'correct-score') }}" class="bm-nav-dropdown-item {{ request()->route('categorySlug') === 'correct-score' ? 'active' : '' }}">Correct Score</a>
            </div>
        </div>
        <a href="{{ route('worldcup.index') }}" class="bm-nav-link {{ request()->routeIs('worldcup.*') ? 'active' : '' }}" style="color: #ff6b00;">FIFA World Cup 2026</a>
    </nav>
    <div class="bm-top-links">
        <a href="{{ route('vip.index') }}" class="bm-premium-btn">
            <span>
                Go Premium
            </span>
            <span>Get started for free</span>
        </a>

        {{-- Theme toggle (all screens) --}}
        <button id="bm-desktop-theme-toggle" class="bm-theme-toggle-btn d-none d-lg-flex" title="Toggle theme" aria-label="Toggle theme">
            <i class="fa fa-moon-o" id="bm-desktop-theme-icon"></i>
        </button>

        {{-- Settings gear icon (mobile only, opens modal) --}}
        <button id="bm-settings-btn" class="d-lg-none" title="Settings" aria-label="Settings">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </button>
        
        @auth('web')
            <a href="{{ route('vip.games') }}" class="bm-top-link" title="My VIP Games">
                <i class="fa fa-trophy"></i>
            </a>
            <a href="{{ route('account.index') }}" class="bm-top-link" title="My Account">
                <i class="fa fa-user-circle fa-lg text-warning"></i>
            </a>
        @else
            <div class="d-none d-md-flex align-items-center">
                <button id="bm-login-btn" class="text-white small font-weight-bold" style="background:none;border:none;cursor:pointer;padding:0;font-family:inherit;">Login</button>
                <span class="mx-2 text-muted">|</span>
                <button id="bm-register-btn" class="text-warning small font-weight-bold" style="background:none;border:none;cursor:pointer;padding:0;font-family:inherit;">Register</button>
            </div>
            <button id="bm-login-btn-mobile" class="bm-top-link d-md-none" title="Login" style="background:none;border:none;cursor:pointer;padding:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </button>
        @endauth
    </div>
</header>

{{-- Theme toggle button styles --}}
<style>
    .bm-theme-toggle-btn {
        background: none;
        border: none;
        color: rgba(255,255,255,0.7);
        font-size: 18px;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 8px;
        transition: all 0.2s;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bm-theme-toggle-btn:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }
    .light-theme .bm-theme-toggle-btn {
        color: #555;
    }
    .light-theme .bm-theme-toggle-btn:hover {
        color: #222;
        background: rgba(0,0,0,0.06);
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ========================================
    // SETTINGS MODAL
    // ========================================
    const settingsBtn = document.getElementById('bm-settings-btn');
    const settingsModal = document.getElementById('bm-settings-modal');
    const settingsClose = document.getElementById('bm-settings-close');
    const settingsBackdrop = settingsModal?.querySelector('.bm-settings-backdrop');
    const filterRow = document.getElementById('bm-settings-filter-row');
    const filterToggle = document.getElementById('bm-settings-filter-toggle');
    const filterBar = document.getElementById('bm-filter-bar');

    if (settingsBtn && settingsModal) {
        function openSettings() {
            settingsModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Show filter option only if filter bar exists on this page
            if (filterRow && filterBar) {
                filterRow.style.display = 'flex';
                const isVisible = filterBar.classList.contains('bm-filter-bar-visible');
                if (filterToggle) {
                    filterToggle.innerHTML = '<span>Filters</span>';
                }
            }
        }
        
        function closeSettings() {
            settingsModal.style.display = 'none';
            document.body.style.overflow = '';
        }
        
        settingsBtn.addEventListener('click', openSettings);
        if (settingsClose) settingsClose.addEventListener('click', closeSettings);
        if (settingsBackdrop) settingsBackdrop.addEventListener('click', closeSettings);
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSettings();
        });
        
        // Filter toggle inside settings: open filter modal instead
        if (filterToggle && filterBar) {
            filterToggle.addEventListener('click', function() {
                closeSettings();
                openFilterModal();
            });
        }
    }

    // ========================================
    // FILTER MODAL
    // ========================================
    const filterModal = document.getElementById('bm-filter-modal');
    const filterClose = document.getElementById('bm-filter-close');
    const filterBackdrop = filterModal?.querySelector('.bm-settings-backdrop');
    
    function populateMarketsDropdown() {
        var marketsDD = document.querySelector('#bm-filter-item-markets .bm-fm-dropdown');
        if (!marketsDD) return;
        // Read market options from the filter toolbar dropdown
        var sourceItems = document.querySelectorAll('#bm-dd-markets .bm-dd-item');
        if (sourceItems.length === 0) return;
        // Only rebuild if empty or options changed
        var currentFirstVal = sourceItems[0]?.getAttribute('data-value');
        var existingFirst = marketsDD.querySelector('li');
        if (existingFirst && existingFirst.getAttribute('data-value') === currentFirstVal) return;
        marketsDD.innerHTML = '';
        sourceItems.forEach(function(src) {
            var li = document.createElement('li');
            li.setAttribute('data-value', src.getAttribute('data-value') || '');
            li.textContent = src.textContent.trim();
            if (src.classList.contains('active')) li.classList.add('active');
            marketsDD.appendChild(li);
        });
        // Re-bind click events for new items
        bindMarketsDropdownItems(marketsDD);
    }
    
    function bindMarketsDropdownItems(dd) {
        var items = dd.querySelectorAll('li');
        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                var val = item.getAttribute('data-value');
                // Update active state
                items.forEach(function(i) { i.classList.remove('active'); });
                item.classList.add('active');
                // Close dropdown
                dd.classList.remove('open');
                // Dispatch market selection
                window.dispatchEvent(new CustomEvent('bm-filter-action', {
                    detail: { type: 'markets', value: val }
                }));
            });
        });
    }
    
    function openFilterModal() {
        if (!filterModal) return;
        populateMarketsDropdown();
        filterModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeFilterModal() {
        if (!filterModal) return;
        filterModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    if (filterClose) filterClose.addEventListener('click', closeFilterModal);
    if (filterBackdrop) filterBackdrop.addEventListener('click', closeFilterModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeFilterModal();
    });
    
    // ========================================
    // FILTER MODAL DROPDOWNS
    // ========================================
    var fmRows = document.querySelectorAll('.bm-fm-row');
    var fmDropdowns = document.querySelectorAll('.bm-fm-dropdown');
    
    // Toggle dropdown on row click
    fmRows.forEach(function(row) {
        var rowClickTarget = row.querySelector('.bm-settings-item');
        if (!rowClickTarget) return;
        
        rowClickTarget.addEventListener('click', function(e) {
            e.stopPropagation();
            var dd = row.querySelector('.bm-fm-dropdown');
            if (!dd) return; // Date row has no dropdown
            // Close all other dropdowns
            fmDropdowns.forEach(function(other) {
                if (other !== dd) other.classList.remove('open');
            });
            dd.classList.toggle('open');
        });
    });
    
    // Select dropdown item
    fmDropdowns.forEach(function(dd) {
        var items = dd.querySelectorAll('li');
        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                var val = item.getAttribute('data-value');
                var forType = dd.getAttribute('data-for');
                
                // Update active state
                items.forEach(function(i) { i.classList.remove('active'); });
                item.classList.add('active');
                
                // Close dropdown
                dd.classList.remove('open');
                
                // Dispatch action to filter toolbar with selected value
                window.dispatchEvent(new CustomEvent('bm-filter-action', {
                    detail: { type: forType, value: val }
                }));
            });
        });
    });
    
    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        fmDropdowns.forEach(function(dd) {
            if (!dd.closest('.bm-fm-row')?.contains(e.target)) {
                dd.classList.remove('open');
            }
        });
    });
    
    // Date buttons
    var datePrev = document.getElementById('bm-filter-date-prev');
    var dateNext = document.getElementById('bm-filter-date-next');
    if (datePrev) {
        datePrev.addEventListener('click', function(e) {
            e.stopPropagation();
            window.dispatchEvent(new CustomEvent('bm-filter-action', { detail: { type: 'date-prev' } }));
        });
    }
    if (dateNext) {
        dateNext.addEventListener('click', function(e) {
            e.stopPropagation();
            window.dispatchEvent(new CustomEvent('bm-filter-action', { detail: { type: 'date-next' } }));
        });
    }
    var datePicker = document.getElementById('bm-filter-date-picker-btn');
    if (datePicker) {
        datePicker.addEventListener('click', function(e) {
            e.stopPropagation();
            window.dispatchEvent(new CustomEvent('bm-filter-action', { detail: { type: 'date-picker' } }));
        });
    }
    
    // Clear button
    var clearAll = document.getElementById('bm-filter-modal-clear');
    if (clearAll) {
        clearAll.addEventListener('click', function(e) {
            e.stopPropagation();
            window.dispatchEvent(new CustomEvent('bm-filter-action', { detail: { type: 'clear' } }));
            closeFilterModal();
        });
    }

    // Listen for filter value updates from the toolbar
    window.addEventListener('bm-filter-update', function(e) {
        var detail = e.detail;
        if (detail.sortVal) {
            var sortDesc = document.getElementById('bm-filter-val-sort');
            if (sortDesc) sortDesc.textContent = detail.sortVal.charAt(0).toUpperCase() + detail.sortVal.slice(1);
        }
        if (detail.probVal !== undefined) {
            var probDesc = document.getElementById('bm-filter-val-prob');
            if (probDesc) probDesc.textContent = detail.probVal === '0' ? 'All' : detail.probVal + '%+';
        }
        if (detail.oddsVal !== undefined) {
            var oddsDesc = document.getElementById('bm-filter-val-odds');
            if (oddsDesc) oddsDesc.textContent = detail.oddsVal === '0' ? 'Any' : '>' + detail.oddsVal;
        }
        if (detail.marketVal) {
            var marketDesc = document.getElementById('bm-filter-val-markets');
            if (marketDesc) marketDesc.textContent = detail.marketVal;
        }
        if (detail.dateVal) {
            var dateDesc = document.getElementById('bm-filter-val-date');
            if (dateDesc) dateDesc.textContent = detail.dateVal;
        }
        if (detail.hasFilters !== undefined) {
            var badge = document.querySelector('.bm-filter-badge');
            if (badge) {
                if (detail.hasFilters) badge.classList.add('show');
                else badge.classList.remove('show');
            }
        }
    });

    // ========================================
    // THEME TOGGLE (settings modal + desktop)
    // ========================================
    const themeToggle = document.getElementById('bm-settings-theme-toggle');
    const themeText = document.getElementById('bm-settings-theme-text');
    const desktopToggle = document.getElementById('bm-desktop-theme-toggle');
    const desktopIcon = document.getElementById('bm-desktop-theme-icon');
    
    function applyTheme(isLight) {
        const body = document.body;
        if (isLight) {
            body.classList.add('light-theme');
        } else {
            body.classList.remove('light-theme');
        }
        localStorage.setItem('bm_theme', isLight ? 'light' : 'dark');
        if (themeText) themeText.textContent = isLight ? 'Light' : 'Dark';
        if (desktopIcon) {
            desktopIcon.className = isLight ? 'fa fa-sun-o' : 'fa fa-moon-o';
        }
    }
    
    var stored = localStorage.getItem('bm_theme');
    if (stored === 'light') {
        applyTheme(true);
    }
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            var isLight = !document.body.classList.contains('light-theme');
            applyTheme(isLight);
        });
    }
    
    if (desktopToggle) {
        desktopToggle.addEventListener('click', function() {
            var isLight = !document.body.classList.contains('light-theme');
            applyTheme(isLight);
        });
    }

    // ========================================
    // SIDEBAR DRAWER TOGGLE (mobile)
    // ========================================
    var toggleBtn  = document.getElementById('bm-sidebar-toggle');
    var closeBtn   = document.getElementById('bm-sidebar-close');
    var sidebar    = document.getElementById('bm-sidebar');
    var overlay    = document.getElementById('bm-sidebar-overlay');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        document.body.style.overflow = '';
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn)  closeBtn.addEventListener('click', closeSidebar);
    if (overlay)   overlay.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeSidebar();
    });

    // ========================================
    // LOGIN MODAL
    // ========================================
    var loginModal = document.getElementById('bm-login-modal');
    var loginBtn = document.getElementById('bm-login-btn');
    var loginBtnMobile = document.getElementById('bm-login-btn-mobile');
    var loginClose = document.getElementById('bm-login-close');
    var loginBackdrop = loginModal?.querySelector('.bm-settings-backdrop');
    var loginForm = document.getElementById('bm-login-form');

    function openLoginModal() {
        if (!loginModal) return;
        loginModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        // Clear previous errors
        document.querySelectorAll('.bm-login-field-error').forEach(function(el) {
            el.style.display = 'none';
            el.textContent = '';
        });
        var generalError = document.getElementById('bm-login-general-error');
        if (generalError) { generalError.style.display = 'none'; generalError.textContent = ''; }
        // Focus email field
        setTimeout(function() {
            var emailInput = loginForm?.querySelector('input[name="email"]');
            if (emailInput) emailInput.focus();
        }, 300);
    }

    function closeLoginModal() {
        if (!loginModal) return;
        loginModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    if (loginBtn) loginBtn.addEventListener('click', openLoginModal);
    if (loginBtnMobile) loginBtnMobile.addEventListener('click', openLoginModal);
    if (loginClose) loginClose.addEventListener('click', closeLoginModal);
    if (loginBackdrop) loginBackdrop.addEventListener('click', closeLoginModal);

    // ========================================
    // REGISTER MODAL
    // ========================================
    var registerModal = document.getElementById('bm-register-modal');
    var registerBtn = document.getElementById('bm-register-btn');
    var registerClose = document.getElementById('bm-register-close');
    var registerBackdrop = registerModal?.querySelector('.bm-settings-backdrop');
    var registerForm = document.getElementById('bm-register-form');
    var toRegisterBtn = document.getElementById('bm-to-register-btn');
    var toLoginBtn = document.getElementById('bm-to-login-btn');

    function openRegisterModal() {
        if (loginModal) loginModal.style.display = 'none';
        if (!registerModal) return;
        registerModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        document.querySelectorAll('#bm-register-modal .bm-login-error').forEach(function(el) {
            el.style.display = 'none'; el.textContent = '';
        });
        setTimeout(function() {
            var nameInput = registerForm?.querySelector('input[name="name"]');
            if (nameInput) nameInput.focus();
        }, 300);
    }

    function closeRegisterModal() {
        if (!registerModal) return;
        registerModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    if (registerBtn) registerBtn.addEventListener('click', openRegisterModal);
    if (registerClose) registerClose.addEventListener('click', closeRegisterModal);
    if (registerBackdrop) registerBackdrop.addEventListener('click', closeRegisterModal);
    if (toRegisterBtn) toRegisterBtn.addEventListener('click', openRegisterModal);
    if (toLoginBtn) toLoginBtn.addEventListener('click', function() {
        closeRegisterModal();
        openLoginModal();
    });

    // Register AJAX
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = registerForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating account...';
            submitBtn.disabled = true;

            document.querySelectorAll('#bm-register-modal .bm-login-error').forEach(function(el) {
                el.style.display = 'none'; el.textContent = '';
            });
            var generalError = document.getElementById('bm-reg-general-error');
            if (generalError) { generalError.style.display = 'none'; generalError.textContent = ''; }

            var formData = new FormData(registerForm);

            fetch(registerForm.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(response) {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                return response.json().then(function(data) {
                    if (response.ok) {
                        if (data.redirect) window.location.href = data.redirect;
                        else window.location.reload();
                        return;
                    }
                    if (data.errors) {
                        if (data.errors.name) {
                            var el = document.getElementById('bm-reg-name-error');
                            if (el) { el.textContent = data.errors.name[0]; el.style.display = 'flex'; }
                        }
                        if (data.errors.email) {
                            var el = document.getElementById('bm-reg-email-error');
                            if (el) { el.textContent = data.errors.email[0]; el.style.display = 'flex'; }
                        }
                        if (data.errors.phone) {
                            var el = document.getElementById('bm-reg-phone-error');
                            if (el) { el.textContent = data.errors.phone[0]; el.style.display = 'flex'; }
                        }
                        if (data.errors.password) {
                            var el = document.getElementById('bm-reg-password-error');
                            if (el) { el.textContent = data.errors.password[0]; el.style.display = 'flex'; }
                        }
                    }
                    if (data.message && generalError) {
                        generalError.textContent = data.message;
                        generalError.style.display = 'flex';
                    }
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            }).catch(function() {
                registerForm.submit();
            });
        });
    }

    // Global Escape key for all modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closeLoginModal(); closeRegisterModal(); }
    });

    // AJAX form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = loginForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = 'Signing in...';
            submitBtn.disabled = true;

            // Clear previous errors
            document.querySelectorAll('.bm-login-field-error').forEach(function(el) {
                el.style.display = 'none';
                el.textContent = '';
            });
            var generalError = document.getElementById('bm-login-general-error');
            if (generalError) { generalError.style.display = 'none'; generalError.textContent = ''; }

            var formData = new FormData(loginForm);

            fetch(loginForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function(response) {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                return response.json().then(function(data) {
                    if (response.ok) {
                        // Success with redirect URL
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                        return;
                    }
                    // Show validation errors
                    if (data.errors) {
                        if (data.errors.email) {
                            var emailErr = document.getElementById('bm-login-email-error');
                            if (emailErr) { emailErr.textContent = data.errors.email[0]; emailErr.style.display = 'block'; }
                        }
                        if (data.errors.password) {
                            var passErr = document.getElementById('bm-login-password-error');
                            if (passErr) { passErr.textContent = data.errors.password[0]; passErr.style.display = 'block'; }
                        }
                    }
                    if (data.message) {
                        if (generalError) { generalError.textContent = data.message; generalError.style.display = 'block'; }
                    }
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            }).catch(function() {
                // Network error - fallback to normal form submit
                loginForm.submit();
            });
        });
    }
});
</script>
@endpush