// ===== COOKIE CONSENT AND ANALYTICS MANAGEMENT =====

function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function deleteCookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

// Load Google Analytics if allowed
function loadGoogleAnalytics() {
    const analytical = getCookie('analytical_cookies');
    if (analytical === null || analytical === 'true') {
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=G-E10GDCMVHB';
        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-E10GDCMVHB');
    }
}

// Track page view
function trackPageView() {
    if (window.gtag && (getCookie('analytical_cookies') === null || getCookie('analytical_cookies') === 'true')) {
        gtag('event', 'page_view', {
            page_title: document.title,
            page_location: window.location.href,
            page_path: window.location.pathname
        });
    }
}

function dismissBanner(banner) {
    banner.style.display = 'none';
    document.body.classList.remove('cookie-banner-active');
}

// Create cookie consent banner
function createCookieBanner() {
    // Return existing banner if already in DOM
    const existing = document.getElementById('cookie-consent-banner');
    if (existing) return existing;

    const banner = document.createElement('div');
    banner.id = 'cookie-consent-banner';
    banner.style.cssText = [
        'position:fixed', 'bottom:0', 'left:0', 'right:0',
        'background:#1a1a1a',
        'border-top:1px solid rgba(107,114,128,0.3)',
        'color:#fff',
        'padding:16px 20px',
        'box-shadow:0 -4px 24px rgba(0,0,0,0.4)',
        'z-index:100000',
        'display:none',
        'font-family:Inter,sans-serif'
    ].join(';');

    banner.innerHTML = `
        <div style="max-width:1100px;margin:0 auto;display:flex;flex-direction:column;gap:14px;">
            <p style="margin:0;font-size:0.88rem;line-height:1.6;color:rgba(255,255,255,0.85);">
                <strong style="color:#fff;">We use cookies</strong> to enhance your experience and analyse site traffic.
                <a href="cookie.html" style="color:#D4AF37;text-decoration:none;font-weight:600;white-space:nowrap;">Learn more</a>
            </p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button id="reject-all-cookies"
                    style="flex:1;min-width:90px;padding:11px 16px;border:1px solid rgba(255,255,255,0.25);border-radius:8px;
                    cursor:pointer;font-weight:600;background:transparent;color:rgba(255,255,255,0.75);
                    font-family:Inter,sans-serif;font-size:0.85rem;transition:all 0.2s;">Reject</button>
                <button id="cookie-settings-btn"
                    style="flex:1;min-width:90px;padding:11px 16px;border:1px solid rgba(255,255,255,0.25);border-radius:8px;
                    cursor:pointer;font-weight:600;background:transparent;color:rgba(255,255,255,0.75);
                    font-family:Inter,sans-serif;font-size:0.85rem;transition:all 0.2s;">Settings</button>
                <button id="accept-all-cookies"
                    style="flex:2;min-width:120px;padding:11px 20px;border:none;border-radius:8px;cursor:pointer;
                    font-weight:700;background:#6b7280;color:#0a0a0a;
                    font-family:Inter,sans-serif;font-size:0.85rem;transition:all 0.2s;">Accept All</button>
            </div>
        </div>
    `;

    document.body.appendChild(banner);

    document.getElementById('accept-all-cookies').addEventListener('click', function() {
        setCookie('cookie_consent', 'true', 365);
        setCookie('essential_cookies', 'true', 365);
        setCookie('analytical_cookies', 'true', 365);
        setCookie('marketing_cookies', 'true', 365);
        dismissBanner(banner);
        loadGoogleAnalytics();
        trackPageView();
    });

    document.getElementById('reject-all-cookies').addEventListener('click', function() {
        setCookie('cookie_consent', 'true', 365);
        setCookie('essential_cookies', 'true', 365);
        setCookie('analytical_cookies', 'false', 365);
        setCookie('marketing_cookies', 'false', 365);
        dismissBanner(banner);
    });

    document.getElementById('cookie-settings-btn').addEventListener('click', function() {
        window.location.href = 'cookie.html';
    });

    // Add hover effects for buttons
    [document.getElementById('reject-all-cookies'), document.getElementById('cookie-settings-btn')].forEach(btn => {
        btn.addEventListener('mouseenter', function() { this.style.borderColor = '#6b7280'; this.style.color = '#6b7280'; });
        btn.addEventListener('mouseleave', function() { this.style.borderColor = 'rgba(255,255,255,0.25)'; this.style.color = 'rgba(255,255,255,0.7)'; });
    });

    return banner;
}

// Check if user has already made a cookie choice
function checkCookieConsent() {
    const consentGiven = getCookie('cookie_consent');
    if (!consentGiven) {
        const banner = createCookieBanner();
        banner.style.display = 'block';
        document.body.classList.add('cookie-banner-active');
    } else {
        loadGoogleAnalytics();
        trackPageView();
    }
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkCookieConsent);
} else {
    checkCookieConsent();
}
