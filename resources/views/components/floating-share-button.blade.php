<button id="bm-share-fab" class="bm-share-fab" title="Share" aria-label="Share">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="18" cy="5" r="3"/>
        <circle cx="6" cy="12" r="3"/>
        <circle cx="18" cy="19" r="3"/>
        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
    </svg>
</button>

<style>
    .bm-share-fab {
        position: fixed;
        bottom: 90px;
        right: 20px;
        z-index: 1040;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff6b00 0%, #e65e00 100%);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 16px rgba(255, 107, 0, 0.4);
        transition: all 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .bm-share-fab:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(255, 107, 0, 0.5);
    }

    .bm-share-fab:active {
        transform: scale(0.95);
    }

    @media (min-width: 992px) {
        .bm-share-fab {
            bottom: 30px;
            right: 30px;
        }
    }
</style>
