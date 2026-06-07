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
                this.hidePopup(true); // true = skip animation
            } else {
                this.showPopup(true); // true = skip animation
            }
            
            // Add close button listener
            if (this.closeBtn) {
                this.closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.hidePopup();
                });
            }
            
            // Handle page visibility change (for when user comes back to tab)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !this.shouldBeHidden()) {
                    this.showPopup(true);
                }
            });
        }
        
        shouldBeHidden() {
            const stored = localStorage.getItem(CAMPAIGN_STORAGE_KEY);
            return stored === CAMPAIGN_VERSION;
        }
        
        hidePopup(skipAnimation = false) {
            // Save to localStorage
            localStorage.setItem(CAMPAIGN_STORAGE_KEY, CAMPAIGN_VERSION);
            
            // Apply hidden classes
            if (this.popup) {
                if (skipAnimation) {
                    this.popup.style.display = 'none';
                } else {
                    this.popup.classList.add('hidden');
                    // Actually hide after animation
                    setTimeout(() => {
                        this.popup.style.display = 'none';
                    }, 300);
                }
            }
            
            // Adjust header position
            if (this.header) {
                this.header.classList.add('popup-hidden');
            }
            
            // Adjust main content
            if (this.main) {
                this.main.classList.add('popup-hidden');
            }
            
            // Adjust hero sections
            if (this.hero) {
                this.hero.classList.add('popup-hidden');
            }
            
            // Dispatch event for other scripts to react
            document.dispatchEvent(new CustomEvent('campaign:closed'));
        }
        
        showPopup(skipAnimation = false) {
            // Remove hidden flag from localStorage
            localStorage.removeItem(CAMPAIGN_STORAGE_KEY);
            
            // Show popup
            if (this.popup) {
                if (skipAnimation) {
                    this.popup.style.display = 'flex';
                    this.popup.classList.remove('hidden');
                } else {
                    this.popup.style.display = 'flex';
                    setTimeout(() => {
                        this.popup.classList.remove('hidden');
                    }, 10);
                }
            }
            
            // Reset header position
            if (this.header) {
                this.header.classList.remove('popup-hidden');
            }
            
            // Reset main content
            if (this.main) {
                this.main.classList.remove('popup-hidden');
            }
            
            // Reset hero sections
            if (this.hero) {
                this.hero.classList.remove('popup-hidden');
            }
            
            // Dispatch event
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