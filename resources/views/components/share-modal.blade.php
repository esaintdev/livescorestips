<div id="bm-share-modal" class="bm-share-modal" style="display:none;">
    <div class="bm-share-backdrop"></div>
    <div class="bm-share-panel">
        <div class="bm-share-header">
            <h3 class="bm-share-title">Share</h3>
            <button id="bm-share-close" class="bm-share-close">&times;</button>
        </div>
        <div class="bm-share-body">
            <p class="bm-share-url">{{ url()->current() }}</p>
            <div class="bm-share-grid">
                <button class="bm-share-option" data-share="facebook">
                    <span class="bm-share-icon bm-share-icon-facebook">
                        <i class="fa fa-facebook"></i>
                    </span>
                    <span class="bm-share-label">Facebook</span>
                </button>
                <button class="bm-share-option" data-share="twitter">
                    <span class="bm-share-icon bm-share-icon-twitter">
                        <i class="fa fa-twitter"></i>
                    </span>
                    <span class="bm-share-label">Twitter / X</span>
                </button>
                <button class="bm-share-option" data-share="whatsapp">
                    <span class="bm-share-icon bm-share-icon-whatsapp">
                        <i class="fa fa-whatsapp"></i>
                    </span>
                    <span class="bm-share-label">WhatsApp</span>
                </button>
                <button class="bm-share-option" data-share="telegram">
                    <span class="bm-share-icon bm-share-icon-telegram">
                        <i class="fa fa-telegram"></i>
                    </span>
                    <span class="bm-share-label">Telegram</span>
                </button>
                <button class="bm-share-option" data-share="copy">
                    <span class="bm-share-icon bm-share-icon-copy">
                        <i class="fa fa-link"></i>
                    </span>
                    <span class="bm-share-label">Copy Link</span>
                </button>
                <button class="bm-share-option bm-share-option-native" data-share="native" id="bm-share-native-btn" style="display:none;">
                    <span class="bm-share-icon bm-share-icon-native">
                        <i class="fa fa-share-alt"></i>
                    </span>
                    <span class="bm-share-label">More</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .bm-share-modal {
        position: fixed;
        inset: 0;
        z-index: 1060;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bm-share-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.6);
    }
    .bm-share-panel {
        position: relative;
        background: #1a1a1a;
        border-radius: 20px;
        width: 100%;
        max-width: 420px;
        padding: 24px;
        animation: bmShareFadeIn 0.25s ease;
    }
    @keyframes bmShareFadeIn {
        from { opacity: 0; transform: scale(0.92); }
        to { opacity: 1; transform: scale(1); }
    }
    .light-theme .bm-share-panel {
        background: #ffffff;
    }
    .bm-share-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .bm-share-title {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin: 0;
    }
    .light-theme .bm-share-title {
        color: #222;
    }
    .bm-share-close {
        background: none;
        border: none;
        color: rgba(255,255,255,0.5);
        font-size: 28px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
    .light-theme .bm-share-close {
        color: rgba(0,0,0,0.4);
    }
    .bm-share-url {
        font-size: 12px;
        color: rgba(255,255,255,0.4);
        word-break: break-all;
        margin: 0 0 20px;
        padding: 8px 12px;
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
    }
    .light-theme .bm-share-url {
        color: rgba(0,0,0,0.4);
        background: rgba(0,0,0,0.04);
    }
    .bm-share-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .bm-share-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px 8px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.2s;
        -webkit-tap-highlight-color: transparent;
        color: inherit;
        font-family: inherit;
        font-size: 12px;
    }
    .bm-share-option:hover {
        background: rgba(255,255,255,0.08);
        transform: translateY(-2px);
    }
    .light-theme .bm-share-option {
        background: rgba(0,0,0,0.03);
        border-color: rgba(0,0,0,0.06);
    }
    .light-theme .bm-share-option:hover {
        background: rgba(0,0,0,0.06);
    }
    .bm-share-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        flex-shrink: 0;
    }
    .bm-share-icon-facebook { background: #1877f2; }
    .bm-share-icon-twitter { background: #000; }
    .bm-share-icon-whatsapp { background: #25d366; }
    .bm-share-icon-telegram { background: #0088cc; }
    .bm-share-icon-copy { background: #666; }
    .bm-share-icon-native { background: #ff6b00; }
    .bm-share-label {
        font-size: 11px;
        font-weight: 600;
        color: rgba(255,255,255,0.7);
        text-align: center;
    }
    .light-theme .bm-share-label {
        color: rgba(0,0,0,0.6);
    }
</style>
