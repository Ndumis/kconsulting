// ===== GOOGLE ANALYTICS MODULE =====
// This file should be imported as a module

// Listen for analytics-allowed event from cookie.js
window.addEventListener('analytics-allowed', function() {
    initGoogleAnalytics();
});

// Also check directly
function shouldLoadAnalytics() {
    const analyticalCookies = getCookie('analytical_cookies');
    return analyticalCookies === null || analyticalCookies === 'true';
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

function initGoogleAnalytics() {
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-E10GDCMVHB');
}

// Initialize if allowed
if (shouldLoadAnalytics()) {
    initGoogleAnalytics();
}