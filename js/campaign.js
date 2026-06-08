// ===== CAMPAIGN POPUP MANAGER =====
// This handles popup display across all pages with persistent state

(function() {
    'use strict';

    const CAMPAIGN_STORAGE_KEY = 'kconsulting_campaign_hidden';
    const CAMPAIGN_VERSION = 'v0.1'; // Change this to force popup to show again
    
    class CampaignManager {
        constructor() {
            this.popup = document.querySelector('.campaign-popup');
            this.header = document.querySelector('.main-header, header');
            this.main = document.querySelector('main, .main-content');
            this.hero = document.querySelector('.hero, .video-hero, .page-header');
            this.closeBtn = this.popup ? this.popup.querySelector('.close-btn') : null;
            
            this.init();
        }
        
        init() {
            if (!this.popup) return;

            // Check if popup should be hidden
            if (this.shouldBeHidden()) {
                this.hidePopup(true);
            } else {
                this.showPopup(true);
            }

            // Add close button listener
            if (this.closeBtn) {
                this.closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.hidePopup();
                });
            }

            // Recompute height on resize (popup wraps on narrow screens)
            window.addEventListener('resize', () => {
                if (!this.shouldBeHidden()) this.syncPopupHeight();
            });

            // Handle page visibility change
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !this.shouldBeHidden()) {
                    this.showPopup(true);
                }
            });
        }

        syncPopupHeight() {
            if (this.popup && this.popup.style.display !== 'none') {
                const h = this.popup.getBoundingClientRect().height;
                document.documentElement.style.setProperty('--popup-height', h + 'px');
            } else {
                document.documentElement.style.setProperty('--popup-height', '0px');
            }
        }
        
        shouldBeHidden() {
            const stored = localStorage.getItem(CAMPAIGN_STORAGE_KEY);
            return stored === CAMPAIGN_VERSION;
        }
        
        hidePopup(skipAnimation = false) {
            localStorage.setItem(CAMPAIGN_STORAGE_KEY, CAMPAIGN_VERSION);

            if (this.popup) {
                if (skipAnimation) {
                    this.popup.style.display = 'none';
                } else {
                    this.popup.classList.add('hidden');
                    setTimeout(() => { this.popup.style.display = 'none'; }, 300);
                }
            }

            if (this.header) this.header.classList.add('popup-hidden');
            if (this.main)   this.main.classList.add('popup-hidden');
            if (this.hero)   this.hero.classList.add('popup-hidden');

            document.documentElement.style.setProperty('--popup-height', '0px');
            document.dispatchEvent(new CustomEvent('campaign:closed'));
        }

        showPopup(skipAnimation = false) {
            localStorage.removeItem(CAMPAIGN_STORAGE_KEY);

            if (this.popup) {
                this.popup.style.display = 'flex';
                if (!skipAnimation) {
                    setTimeout(() => { this.popup.classList.remove('hidden'); }, 10);
                } else {
                    this.popup.classList.remove('hidden');
                }
            }

            if (this.header) this.header.classList.remove('popup-hidden');
            if (this.main)   this.main.classList.remove('popup-hidden');
            if (this.hero)   this.hero.classList.remove('popup-hidden');

            // Measure actual height after render and update the CSS variable
            requestAnimationFrame(() => { this.syncPopupHeight(); });

            document.dispatchEvent(new CustomEvent('campaign:opened'));
        }
        
        // Force show popup (useful for testing or after version change)
        forceShow() {
            localStorage.removeItem(CAMPAIGN_STORAGE_KEY);
            this.showPopup();
        }
        
        // Force hide popup (useful for testing)
        forceHide() {
            this.hidePopup();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.campaignManager = new CampaignManager();
        });
    } else {
        window.campaignManager = new CampaignManager();
    }
})();