{{-- Premium Confirmation Modal --}}
<div id="confirmDeleteModal" style="display:none;" aria-hidden="true">

    {{-- Backdrop --}}
    <div id="confirmModalBackdrop" style="
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
        z-index: 99998;
        animation: fadeIn 0.2s ease;
    "></div>

    {{-- Modal Card --}}
    <div id="confirmModalCard" style="
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        pointer-events: none;
    ">
        <div style="
            background: #1a1a1a;
            border: 1px solid #2d2d2d;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.8), 0 0 0 1px rgba(255,255,255,0.04);
            overflow: hidden;
            pointer-events: all;
            animation: modalPopIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        ">
            {{-- Icon Header --}}
            <div id="confirmModalHeader" style="
                padding: 36px 32px 20px;
                text-align: center;
                position: relative;
            ">
                <div id="confirmModalIconWrap" style="
                    width: 64px;
                    height: 64px;
                    border-radius: 50%;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.6rem;
                    margin-bottom: 16px;
                    background: rgba(255, 107, 0, 0.12);
                    border: 2px solid rgba(255, 107, 0, 0.25);
                    color: #ff6b00;
                ">
                    <i class="fa fa-trash" id="confirmBtnIcon"></i>
                </div>
                <h4 id="confirmModalTitle" style="
                    color: #fff;
                    font-weight: 800;
                    font-size: 1.2rem;
                    margin: 0 0 8px;
                ">Confirm Action</h4>
                <p id="confirmDeleteMessage" style="
                    color: #bbb;
                    font-size: 0.95rem;
                    margin: 0;
                    line-height: 1.5;
                ">Are you sure you want to delete this item?</p>
                <p id="confirmModalSubtext" style="
                    color: #555;
                    font-size: 0.82rem;
                    margin: 8px 0 0;
                ">This action cannot be undone.</p>
            </div>

            {{-- Actions --}}
            <div style="
                padding: 0 32px 32px;
                display: flex;
                gap: 12px;
            ">
                <button type="button" data-dismiss="modal" style="
                    flex: 1;
                    padding: 12px;
                    background: transparent;
                    border: 1px solid #3d3d3d;
                    border-radius: 10px;
                    color: #aaa;
                    font-weight: 700;
                    font-size: 0.9rem;
                    cursor: pointer;
                    transition: all 0.2s;
                    font-family: inherit;
                " onmouseover="this.style.borderColor='#666';this.style.color='#fff';"
                   onmouseout="this.style.borderColor='#3d3d3d';this.style.color='#aaa';">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn" style="
                    flex: 1;
                    padding: 12px;
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    border: none;
                    border-radius: 10px;
                    color: #fff;
                    font-weight: 700;
                    font-size: 0.9rem;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    font-family: inherit;
                    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
                " onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 25px rgba(239,68,68,0.4)';"
                   onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(239,68,68,0.3)';">
                    <i class="fa fa-trash" id="confirmBtnIconBtn"></i>
                    <span id="confirmBtnText">Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes modalPopIn {
        from { opacity: 0; transform: scale(0.88) translateY(20px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    @keyframes alertIn {
        from { opacity: 0; transform: translateY(-10px) scale(0.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>

<script>
    (function () {
        var deleteFormToSubmit = null;
        var confirmResolve = null;
        var modal       = document.getElementById('confirmDeleteModal');
        var confirmBtn  = document.getElementById('confirmDeleteBtn');
        var messageEl   = document.getElementById('confirmDeleteMessage');
        var titleEl     = document.getElementById('confirmModalTitle');
        var btnTextEl   = document.getElementById('confirmBtnText');
        var btnIconEl   = document.getElementById('confirmBtnIconBtn');
        var iconWrapEl  = document.getElementById('confirmModalIconWrap');
        var subtextEl   = document.getElementById('confirmModalSubtext');
        var backdrop    = document.getElementById('confirmModalBackdrop');

        function applyColor(color) {
            if (confirmBtn) {
                confirmBtn.style.background = 'linear-gradient(135deg, ' + color + ' 0%, ' + color + 'cc 100%)';
                confirmBtn.style.boxShadow  = '0 4px 15px ' + color + '55';
                confirmBtn.addEventListener('mouseover', function() {
                    this.style.boxShadow = '0 8px 25px ' + color + '88';
                });
            }
            if (iconWrapEl) {
                iconWrapEl.style.background   = color + '1a';
                iconWrapEl.style.borderColor  = color + '44';
                iconWrapEl.style.color        = color;
            }
        }

        function hideModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        function showModal() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        window.showConfirmModal = function (form, options) {
            try {
                deleteFormToSubmit = form;
                var defaults = {
                    title:   'Confirm Action',
                    message: 'Are you sure you want to proceed?',
                    btnText: 'Confirm',
                    btnIcon: 'fa-check',
                    color:   '#ff6b00',
                    subtext: 'This action cannot be undone.'
                };
                var s = Object.assign({}, defaults, options);

                if (messageEl)  messageEl.textContent  = s.message;
                if (titleEl)    titleEl.textContent    = s.title;
                if (btnTextEl)  btnTextEl.textContent  = s.btnText;
                if (btnIconEl)  btnIconEl.className    = 'fa ' + s.btnIcon;
                if (iconWrapEl) iconWrapEl.querySelector('i').className = 'fa ' + s.btnIcon;
                if (subtextEl) {
                    subtextEl.textContent    = s.subtext;
                    subtextEl.style.display  = s.subtext ? 'block' : 'none';
                }
                applyColor(s.color);

                showModal();
            } catch (e) {
                console.error(e);
                if (confirm('Are you sure?')) form.submit();
            }
        };

        window.showDeleteModal = function (form, itemName) {
            showConfirmModal(form, {
                title:   'Confirm Delete',
                message: 'Are you sure you want to delete this ' + (itemName || 'item') + '?',
                btnText: 'Delete',
                btnIcon: 'fa-trash',
                color:   '#ef4444',
                subtext: 'This action cannot be undone.'
            });
        };

        // Override browser confirm() with custom modal — returns Promise<boolean>
        window.confirm = function (message) {
            return new Promise(function (resolve) {
                confirmResolve = resolve;
                var defaults = {
                    title:   'Confirm',
                    message: message || 'Are you sure?',
                    btnText: 'Confirm',
                    btnIcon: 'fa-check',
                    color:   '#ff6b00',
                    subtext: null
                };
                if (messageEl)  messageEl.textContent  = defaults.message;
                if (titleEl)    titleEl.textContent    = defaults.title;
                if (btnTextEl)  btnTextEl.textContent  = defaults.btnText;
                if (btnIconEl)  btnIconEl.className    = 'fa ' + defaults.btnIcon;
                if (iconWrapEl) iconWrapEl.querySelector('i').className = 'fa ' + defaults.btnIcon;
                if (subtextEl) {
                    subtextEl.textContent    = '';
                    subtextEl.style.display  = 'none';
                }
                applyColor(defaults.color);
                // Show Cancel button (it may be hidden from alert override)
                var cancelBtn = modal.querySelector('[data-dismiss="modal"]');
                if (cancelBtn) cancelBtn.style.display = '';
                showModal();
            });
        };

        // Override browser alert() with custom styled notification
        (function () {
            var alertContainer = document.createElement('div');
            alertContainer.id = 'bm-alert-container';
            alertContainer.style.cssText = 'position:fixed;top:100px;left:50%;transform:translateX(-50%);z-index:100000;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(alertContainer);

            window.alert = function (message) {
                var el = document.createElement('div');
                el.style.cssText = 'background:#1a1a1a;border:1px solid #ff6b00;border-radius:12px;padding:16px 24px;color:#fff;font-size:15px;font-family:inherit;box-shadow:0 8px 32px rgba(0,0,0,0.5);pointer-events:all;animation:alertIn 0.3s ease-out;max-width:480px;text-align:center;';
                el.textContent = message;
                alertContainer.appendChild(el);
                setTimeout(function () {
                    el.style.transition = 'opacity 0.3s,transform 0.3s';
                    el.style.opacity = '0';
                    el.style.transform = 'scale(0.95)';
                    setTimeout(function () { el.remove(); }, 300);
                }, 3000);
            };
        })();

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (deleteFormToSubmit) {
                    deleteFormToSubmit.submit();
                }
                if (confirmResolve) {
                    confirmResolve(true);
                    confirmResolve = null;
                }
                hideModal();
                // Restore Cancel button visibility
                var cancelBtn = modal.querySelector('[data-dismiss="modal"]');
                if (cancelBtn) cancelBtn.style.display = '';
            });
        }

        // Cancel / close buttons
        modal.querySelectorAll('[data-dismiss="modal"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (confirmResolve) {
                    confirmResolve(false);
                    confirmResolve = null;
                }
                hideModal();
                // Restore Cancel button visibility
                var cancelBtn = modal.querySelector('[data-dismiss="modal"]');
                if (cancelBtn) cancelBtn.style.display = '';
            });
        });

        // Click backdrop to close
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                if (confirmResolve) {
                    confirmResolve(false);
                    confirmResolve = null;
                }
                hideModal();
                var cancelBtn = modal.querySelector('[data-dismiss="modal"]');
                if (cancelBtn) cancelBtn.style.display = '';
            });
        }

        // Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                if (confirmResolve) {
                    confirmResolve(false);
                    confirmResolve = null;
                }
                hideModal();
                var cancelBtn = modal.querySelector('[data-dismiss="modal"]');
                if (cancelBtn) cancelBtn.style.display = '';
            }
        });
    })();
</script>