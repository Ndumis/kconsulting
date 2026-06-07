// ===== MAIN SCRIPT FILE - KConsulting Website =====

(function() {
    'use strict';

    // ===== CUSTOM CURSOR WITH BUBBLE EFFECT =====
    function initCustomCursor() {
        const cursor = document.querySelector('.cursor');
        const follower = document.querySelector('.cursor-follower');
        
        if (!cursor || !follower) return;
        
        let mouseX = 0, mouseY = 0;
        let cursorX = 0, cursorY = 0;
        let followerX = 0, followerY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        function animate() {
            cursorX += (mouseX - cursorX) * 0.2;
            cursorY += (mouseY - cursorY) * 0.2;
            
            followerX += (mouseX - followerX) * 0.1;
            followerY += (mouseY - followerY) * 0.1;
            
            cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0) translate(-50%, -50%)`;
            follower.style.transform = `translate3d(${followerX}px, ${followerY}px, 0) translate(-50%, -50%)`;

            requestAnimationFrame(animate);
        }
        animate();

        document.addEventListener('mousedown', () => {
            cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0) translate(-50%, -50%) scale(0.8)`;
            follower.style.transform = `translate3d(${followerX}px, ${followerY}px, 0) translate(-50%, -50%) scale(1.5)`;
        });

        document.addEventListener('mouseup', () => {
            cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0) translate(-50%, -50%) scale(1)`;
            follower.style.transform = `translate3d(${followerX}px, ${followerY}px, 0) translate(-50%, -50%) scale(1)`;
        });

        const INTERACTIVE_SELECTOR = 'a, button, .service-card, .person, .method-card, .pillar, .case-card, .tier-card, .profile-tab, .quiz-option, .game-card, .social-link, .btn-primary, .btn-secondary, .btn-gold, .cta-button, .quiz-float-btn, .solution-card, .process-step';

        // Use event delegation so dynamically-created elements (e.g. cookie banner buttons) also react
        document.addEventListener('mouseover', (e) => {
            if (e.target.closest(INTERACTIVE_SELECTOR)) {
                cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0) translate(-50%, -50%) scale(1.5)`;
                cursor.style.borderColor = 'var(--primary-dark)';
                follower.style.transform = `translate3d(${followerX}px, ${followerY}px, 0) translate(-50%, -50%) scale(1.5)`;
                follower.style.background = 'var(--primary-dark)';
            }
        });
        document.addEventListener('mouseout', (e) => {
            if (e.target.closest(INTERACTIVE_SELECTOR)) {
                cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0) translate(-50%, -50%) scale(1)`;
                cursor.style.borderColor = 'var(--accent-grey)';
                follower.style.transform = `translate3d(${followerX}px, ${followerY}px, 0) translate(-50%, -50%) scale(1)`;
                follower.style.background = 'var(--accent-grey)';
            }
        });
    }

    // ===== FADE-IN ON SCROLL =====
    function initFadeInOnScroll() {
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        fadeElements.forEach(el => observer.observe(el));
    }

    // ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerOffset = 140;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // ===== HEADER SCROLL EFFECT =====
    function initHeaderScroll() {
        const header = document.querySelector('.main-header, header');
        if (!header) return;
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.05)';
            } else {
                header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        });
    }

    // ===== MOBILE NAVIGATION =====
    function initMobileNavigation() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const navMenu = document.querySelector('.nav-links, .nav-menu');
        
        if (!mobileToggle || !navMenu) return;

        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = mobileToggle.getAttribute('aria-expanded') !== 'true';
            
            mobileToggle.setAttribute('aria-expanded', isOpen);
            mobileToggle.classList.toggle('active', isOpen);
            navMenu.classList.toggle('active', isOpen);
            
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });

        document.addEventListener('click', (e) => {
            if (!mobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }

    // ===== CONTACT FORM HANDLING WITH AJAX =====
    function initContactForm() {
        const contactForm = document.getElementById('contactForm');
        if (!contactForm) return;

        // Remove any existing listeners by cloning
        const newForm = contactForm.cloneNode(true);
        contactForm.parentNode.replaceChild(newForm, contactForm);

        newForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const loadingSpinner = document.getElementById('loading-spinner');
            const formMessage = document.getElementById('form-message');

            // Clear previous messages
            if (formMessage) {
                formMessage.style.display = 'none';
                formMessage.className = 'form-message';
            }

            // Validation
            if (!data.name || data.name.trim() === '') {
                showFormMessage(formMessage, 'Please enter your name.', 'error');
                return;
            }

            if (!data.email || data.email.trim() === '') {
                showFormMessage(formMessage, 'Please enter your email address.', 'error');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(data.email)) {
                showFormMessage(formMessage, 'Please enter a valid email address.', 'error');
                return;
            }

            if (!data.message || data.message.trim() === '') {
                showFormMessage(formMessage, 'Please enter your message.', 'error');
                return;
            }

            // Show loading state
            if (buttonText && loadingSpinner) {
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';
                if (submitButton) submitButton.disabled = true;
            }

            try {
                const response = await fetch('php/submit_contact.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showFormMessage(formMessage, result.message, 'success');
                    newForm.reset();
                } else {
                    showFormMessage(formMessage, result.message || 'An error occurred. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showFormMessage(formMessage, 'Network error. Please check your connection and try again.', 'error');
            } finally {
                // Reset button state
                if (buttonText && loadingSpinner) {
                    buttonText.style.display = 'inline';
                    loadingSpinner.style.display = 'none';
                    if (submitButton) submitButton.disabled = false;
                }
            }
        });
    }

    // ===== QUALIFICATION/CONSULTATION FORM HANDLING WITH AJAX =====
    function initQualificationForm() {
        const qualificationForm = document.getElementById('qualificationForm');
        if (!qualificationForm) return;

        // Add a message container if it doesn't exist
        let formMessage = document.getElementById('qualification-form-message');
        if (!formMessage) {
            formMessage = document.createElement('div');
            formMessage.id = 'qualification-form-message';
            formMessage.className = 'form-message';
            formMessage.style.display = 'none';
            qualificationForm.parentNode.insertBefore(formMessage, qualificationForm);
        }

        // Remove any existing listeners by cloning
        const newForm = qualificationForm.cloneNode(true);
        qualificationForm.parentNode.replaceChild(newForm, qualificationForm);

        // Setup step navigation (pass the new form)
        setupStepNavigation(newForm);

        // Add submit event listener
        newForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Hide any previous messages
            formMessage.style.display = 'none';
            formMessage.className = 'form-message';
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn ? submitBtn.textContent : 'Submit';
            
            // Add qualification score if it exists
            if (window.qualificationScore) {
                data.qualificationScore = window.qualificationScore;
            }

            // Show loading state
            if (submitBtn) {
                submitBtn.textContent = 'Processing...';
                submitBtn.disabled = true;
            }

            try {
                const response = await fetch('php/submit_form.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success modal
                    showSuccessModal(result);
                    this.reset();
                    
                    // Reset to first step
                    if (window.currentStep !== undefined) {
                        window.currentStep = 0;
                        if (typeof window.showStep === 'function') {
                            window.showStep(0);
                        }
                    }
                    
                    // Show success message
                    showFormMessage(formMessage, result.message, 'success');
                } else {
                    // Show error message
                    showFormMessage(formMessage, result.message || 'An error occurred. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                // Show network error message
                showFormMessage(formMessage, 'Network error. Please check your connection and try again.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            }
        });
    }

    // ===== STEP NAVIGATION FOR CONSULTATION FORM =====
    function setupStepNavigation(form) {
        const steps = document.querySelectorAll('.form-step');
        if (steps.length === 0) return;

        window.currentStep = 0;
        window.showStep = function(step) {
            steps.forEach((s, idx) => {
                s.classList.toggle('active', idx === step);
            });
            window.currentStep = step;
        };

        // Step 1 Next
        const step1Next = document.getElementById('step1Next');
        if (step1Next) {
            step1Next.replaceWith(step1Next.cloneNode(true));
            const newStep1Next = document.getElementById('step1Next');
            
            newStep1Next.addEventListener('click', () => {
                const formMessage = document.getElementById('qualification-form-message');
                const company = document.getElementById('company');
                const industry = document.getElementById('industry');
                const companySize = document.getElementById('company-size');
                
                if (!company || !company.value.trim()) {
                    showFormMessage(formMessage, 'Please enter your company name.', 'error');
                    return;
                }
                if (!industry || !industry.value) {
                    showFormMessage(formMessage, 'Please select your industry.', 'error');
                    return;
                }
                if (!companySize || !companySize.value) {
                    showFormMessage(formMessage, 'Please select your company size.', 'error');
                    return;
                }
                
                // Clear any previous messages
                if (formMessage) formMessage.style.display = 'none';
                
                window.currentStep = 1;
                window.showStep(window.currentStep);
            });
        }

        // Step 2 navigation
        const step2Prev = document.getElementById('step2Prev');
        if (step2Prev) {
            step2Prev.replaceWith(step2Prev.cloneNode(true));
            const newStep2Prev = document.getElementById('step2Prev');
            
            newStep2Prev.addEventListener('click', () => {
                window.currentStep = 0;
                window.showStep(window.currentStep);
            });
        }

        const step2Next = document.getElementById('step2Next');
        if (step2Next) {
            step2Next.replaceWith(step2Next.cloneNode(true));
            const newStep2Next = document.getElementById('step2Next');
            
            newStep2Next.addEventListener('click', () => {
                const name = document.getElementById('name');
                const position = document.getElementById('position');
                const email = document.getElementById('email');
                const phone = document.getElementById('phone');
                
                if (!name || !name.value.trim()) {
                    alert('Please enter your full name.');
                    return;
                }
                if (!position || !position.value) {
                    alert('Please select your position.');
                    return;
                }
                if (!email || !email.value.trim()) {
                    alert('Please enter your email.');
                    return;
                }
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    alert('Please enter a valid email address.');
                    return;
                }
                if (!phone || !phone.value.trim()) {
                    alert('Please enter your phone number.');
                    return;
                }
                
                window.currentStep = 2;
                window.showStep(window.currentStep);
            });
        }

        // Step 3 navigation
        const step3Prev = document.getElementById('step3Prev');
        if (step3Prev) {
            step3Prev.replaceWith(step3Prev.cloneNode(true));
            const newStep3Prev = document.getElementById('step3Prev');
            
            newStep3Prev.addEventListener('click', () => {
                window.currentStep = 1;
                window.showStep(window.currentStep);
            });
        }

        const step3Next = document.getElementById('step3Next');
        if (step3Next) {
            step3Next.replaceWith(step3Next.cloneNode(true));
            const newStep3Next = document.getElementById('step3Next');
            
            newStep3Next.addEventListener('click', () => {
                const services = document.getElementById('services');
                const consultationType = document.getElementById('consultation-type');
                const timeline = document.getElementById('timeline');
                const budget = document.getElementById('budget');
                
                if (!services || !services.value) {
                    alert('Please select your primary service interest.');
                    return;
                }
                if (!consultationType || !consultationType.value) {
                    alert('Please select your consultation type.');
                    return;
                }
                if (!timeline || !timeline.value) {
                    alert('Please select your timeline.');
                    return;
                }
                if (!budget || !budget.value) {
                    alert('Please select your budget range.');
                    return;
                }
                
                window.currentStep = 3;
                window.showStep(window.currentStep);
            });
        }

        // Step 4 navigation
        const step4Prev = document.getElementById('step4Prev');
        if (step4Prev) {
            step4Prev.replaceWith(step4Prev.cloneNode(true));
            const newStep4Prev = document.getElementById('step4Prev');
            
            newStep4Prev.addEventListener('click', () => {
                window.currentStep = 2;
                window.showStep(window.currentStep);
            });
        }

        const step4Next = document.getElementById('step4Next');
        if (step4Next) {
            step4Next.replaceWith(step4Next.cloneNode(true));
            const newStep4Next = document.getElementById('step4Next');
            
            newStep4Next.addEventListener('click', () => {
                const challenges = document.getElementById('current-challenges');
                const outcomes = document.getElementById('desired-outcomes');
                
                if (!challenges || !challenges.value.trim()) {
                    alert('Please describe your current business challenges.');
                    return;
                }
                if (!outcomes || !outcomes.value.trim()) {
                    alert('Please describe your desired outcomes.');
                    return;
                }
                
                window.currentStep = 4;
                window.showStep(window.currentStep);
            });
        }

        // Step 5 navigation
        const step5Prev = document.getElementById('step5Prev');
        if (step5Prev) {
            step5Prev.replaceWith(step5Prev.cloneNode(true));
            const newStep5Prev = document.getElementById('step5Prev');
            
            newStep5Prev.addEventListener('click', () => {
                window.currentStep = 3;
                window.showStep(window.currentStep);
            });
        }

        const step5Next = document.getElementById('step5Next');
        if (step5Next) {
            step5Next.replaceWith(step5Next.cloneNode(true));
            const newStep5Next = document.getElementById('step5Next');
            
            newStep5Next.addEventListener('click', () => {
                const decisionMaker = document.getElementById('decision-maker');
                const decisionTimeline = document.getElementById('decision-timeline');
                
                if (!decisionMaker || !decisionMaker.value) {
                    alert('Please select your decision maker status.');
                    return;
                }
                if (!decisionTimeline || !decisionTimeline.value) {
                    alert('Please select your decision timeline.');
                    return;
                }
                
                window.currentStep = 5;
                window.showStep(window.currentStep);
            });
        }

        // Step 6 navigation
        const step6Prev = document.getElementById('step6Prev');
        if (step6Prev) {
            step6Prev.replaceWith(step6Prev.cloneNode(true));
            const newStep6Prev = document.getElementById('step6Prev');
            
            newStep6Prev.addEventListener('click', () => {
                window.currentStep = 4;
                window.showStep(window.currentStep);
            });
        }

        const step6Next = document.getElementById('step6Next');
        if (step6Next) {
            step6Next.replaceWith(step6Next.cloneNode(true));
            const newStep6Next = document.getElementById('step6Next');
            
            newStep6Next.addEventListener('click', () => {
                const meetingType = document.getElementById('meeting-type');
                
                if (!meetingType || !meetingType.value) {
                    alert('Please select your preferred meeting type.');
                    return;
                }
                
                // Populate review
                populateReview();
                window.currentStep = 6;
                window.showStep(window.currentStep);
            });
        }

        // Pricing cards scroll to form
        document.querySelectorAll('.pricing-card .btn-primary').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true));
        });
        
        document.querySelectorAll('.pricing-card .btn-primary').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                document.getElementById('qualification')?.scrollIntoView({ behavior: 'smooth' });
            });
        });
    }

    function populateReview() {
        const reviewContent = document.getElementById('reviewContent');
        if (!reviewContent) return;
        
        const name = document.getElementById('name')?.value || 'N/A';
        const email = document.getElementById('email')?.value || 'N/A';
        const company = document.getElementById('company')?.value || 'Not provided';
        const services = document.getElementById('services')?.options[document.getElementById('services')?.selectedIndex]?.text || 'N/A';
        const consultationType = document.getElementById('consultation-type')?.options[document.getElementById('consultation-type')?.selectedIndex]?.text || 'N/A';
        const timeline = document.getElementById('timeline')?.options[document.getElementById('timeline')?.selectedIndex]?.text || 'N/A';
        const budget = document.getElementById('budget')?.options[document.getElementById('budget')?.selectedIndex]?.text || 'N/A';

        reviewContent.innerHTML = `
            <p><strong>Name:</strong> ${escapeHtml(name)}</p>
            <p><strong>Email:</strong> ${escapeHtml(email)}</p>
            <p><strong>Company:</strong> ${escapeHtml(company)}</p>
            <p><strong>Service:</strong> ${escapeHtml(services)}</p>
            <p><strong>Consultation Type:</strong> ${escapeHtml(consultationType)}</p>
            <p><strong>Timeline:</strong> ${escapeHtml(timeline)}</p>
            <p><strong>Budget:</strong> ${escapeHtml(budget)}</p>
        `;
    }

    // ===== HELPER FUNCTIONS =====
    function showFormMessage(element, message, type) {
        if (!element) {
            alert(message);
            return;
        }
        element.textContent = message;
        element.className = `form-message ${type}`;
        element.style.display = 'block';
        
        if (type === 'success') {
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }
    }

    function showSuccessModal(result) {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            const closeBtn = document.getElementById('closeModal');
            if (closeBtn) {
                closeBtn.replaceWith(closeBtn.cloneNode(true));
                const newCloseBtn = document.getElementById('closeModal');
                
                newCloseBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            }
            
            // Close when clicking outside
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        } else {
            alert('Thank you! Your consultation request has been received. We\'ll contact you within 24 hours.');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ===== CURRENT YEAR IN FOOTER =====
    function setCurrentYear() {
        const yearEl = document.getElementById('currentYear');
        if (yearEl) {
            yearEl.textContent = new Date().getFullYear();
        }
    }

    // ===== CAMPAIGN POPUP CLOSE =====
    /**function initCampaignPopup() {
        const closeBtn = document.querySelector('.campaign-popup .close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                const popup = this.closest('.campaign-popup');
                popup.style.display = 'none';
            });
        }
    } */

    // ===== QUIZ MODAL =====
    function initQuizModal() {
        const modal    = document.getElementById('quizModal');
        const floatBtn = document.getElementById('quizFloatBtn');
        if (!modal || !floatBtn) return;

        // Update static text in the floating button and modal header
        const floatSpan = floatBtn.querySelector('span');
        if (floatSpan) floatSpan.textContent = 'Growth Check';
        const floatIcon = floatBtn.querySelector('i');
        if (floatIcon) floatIcon.className = 'fas fa-chart-line';

        const modalH2 = modal.querySelector('.quiz-modal-header h2');
        const modalSub = modal.querySelector('.quiz-modal-header p');
        if (modalH2) modalH2.textContent = "What's holding your growth back?";
        if (modalSub) modalSub.textContent = 'Answer 3 quick questions and get a personalised recommendation';

        const closeBtn   = modal.querySelector('.quiz-close');
        const modalBody  = modal.querySelector('.quiz-modal-body');
        const progressBar = document.getElementById('quizProgressBar');

        const questions = [
            {
                q: "What's your biggest online challenge right now?",
                opts: [
                    { text: 'Not getting enough website visitors',  icon: 'fa-eye-slash',      cat: 'seo' },
                    { text: 'Visitors arrive but never enquire',    icon: 'fa-filter',          cat: 'conversion' },
                    { text: 'Poor Google / search rankings',        icon: 'fa-search-minus',    cat: 'seo' },
                    { text: 'My business systems are disconnected', icon: 'fa-unlink',          cat: 'systems' }
                ]
            },
            {
                q: 'How would you describe your current website?',
                opts: [
                    { text: 'Outdated – needs a full redesign',           icon: 'fa-paint-brush',    cat: 'conversion' },
                    { text: 'Looks fine but generates no leads',          icon: 'fa-chart-bar',      cat: 'leads' },
                    { text: "Good-looking but I'm unsure what's wrong",   icon: 'fa-question-circle', cat: 'leads' },
                    { text: "I don't have a proper website yet",          icon: 'fa-globe',           cat: 'leads' }
                ]
            },
            {
                q: 'What result matters most to you in the next 90 days?',
                opts: [
                    { text: 'More qualified leads coming in consistently', icon: 'fa-users',     cat: 'leads' },
                    { text: 'Turn more visitors into paying customers',    icon: 'fa-handshake', cat: 'conversion' },
                    { text: 'Rank higher on Google and get found',         icon: 'fa-trophy',    cat: 'seo' },
                    { text: 'Automate and connect my business tools',      icon: 'fa-cogs',      cat: 'systems' }
                ]
            }
        ];

        const resultMap = {
            seo: {
                icon: 'fa-search',
                title: 'You need stronger SEO & online visibility',
                desc: "Your website isn't being found. We'll help you rank for the searches your ideal clients are already making.",
                cta: 'Get a Free Website Audit',
                link: 'free-website-audit.html'
            },
            conversion: {
                icon: 'fa-filter',
                title: 'Your site needs Conversion Optimisation',
                desc: "You're getting traffic but losing sales. We'll pinpoint exactly where visitors drop off and fix it.",
                cta: 'Book a Free Strategy Session',
                link: 'consultation.html'
            },
            leads: {
                icon: 'fa-users',
                title: 'You need a Lead Generation system',
                desc: "A great-looking site alone won't grow your business. Let's build a system that pulls in qualified enquiries every month.",
                cta: 'Get a Free Website Audit',
                link: 'free-website-audit.html'
            },
            systems: {
                icon: 'fa-cogs',
                title: 'Your business needs Systems Integration',
                desc: "Disconnected tools are costing you time and money. We'll connect your stack and automate the grind.",
                cta: 'Book a Free Strategy Session',
                link: 'consultation.html'
            }
        };

        let step = 0;
        let answers = []; // { idx, cat } per question

        function setProgress(fraction) {
            if (progressBar) progressBar.style.width = (Math.min(fraction, 1) * 100) + '%';
        }

        function renderStep() {
            const q = questions[step];
            let optHtml = '';
            q.opts.forEach((opt, idx) => {
                const sel = (answers[step] && answers[step].idx === idx) ? 'selected' : '';
                optHtml += `<div class="quiz-option ${sel}" data-idx="${idx}" data-cat="${opt.cat}">
                    <i class="fas ${opt.icon}"></i>
                    <span>${opt.text}</span>
                </div>`;
            });

            const isLast = step === questions.length - 1;
            modalBody.innerHTML = `
                <div class="quiz-question">${q.q}</div>
                <div class="quiz-options quiz-options-grid">${optHtml}</div>
                <p class="quiz-error quiz-inline-error" style="display:none;"></p>
                <div class="quiz-nav">
                    ${step > 0 ? '<button class="btn-secondary quiz-prev"><i class="fas fa-arrow-left"></i> Back</button>' : '<div></div>'}
                    <button class="btn-primary quiz-next">${isLast ? 'Almost done' : 'Next'} <i class="fas fa-arrow-right"></i></button>
                </div>
            `;
            setProgress((step + 1) / (questions.length + 1));

            modalBody.querySelectorAll('.quiz-option').forEach(el => {
                el.addEventListener('click', function () {
                    modalBody.querySelectorAll('.quiz-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    answers[step] = { idx: parseInt(this.dataset.idx), cat: this.dataset.cat };
                    modalBody.querySelector('.quiz-inline-error').style.display = 'none';
                });
            });

            const prevBtn = modalBody.querySelector('.quiz-prev');
            if (prevBtn) prevBtn.addEventListener('click', () => { step--; renderStep(); });

            modalBody.querySelector('.quiz-next').addEventListener('click', () => {
                if (!answers[step]) {
                    const err = modalBody.querySelector('.quiz-inline-error');
                    err.textContent = 'Please select an option to continue.';
                    err.style.display = 'block';
                    return;
                }
                if (step < questions.length - 1) { step++; renderStep(); }
                else { renderContactStep(); }
            });
        }

        function renderContactStep() {
            setProgress(questions.length / (questions.length + 1));
            modalBody.innerHTML = `
                <p class="quiz-contact-intro">Almost there! Where should we send your personalised growth plan?</p>
                <div class="quiz-contact-fields">
                    <input type="text"  id="quizName"  class="quiz-input" placeholder="Your name (optional)"      autocomplete="name">
                    <input type="email" id="quizEmail" class="quiz-input" placeholder="Email address *" required  autocomplete="email">
                    <input type="tel"   id="quizPhone" class="quiz-input" placeholder="Phone number (optional)"   autocomplete="tel">
                </div>
                <p class="quiz-error" id="quizContactErr" style="display:none;"></p>
                <div class="quiz-nav">
                    <button class="btn-secondary quiz-prev"><i class="fas fa-arrow-left"></i> Back</button>
                    <button class="btn-primary quiz-submit-btn">See My Results <i class="fas fa-chart-line"></i></button>
                </div>
                <p class="quiz-privacy"><i class="fas fa-lock"></i> We respect your privacy. No spam, ever.</p>
            `;

            modalBody.querySelector('.quiz-prev').addEventListener('click', () => { step = questions.length - 1; renderStep(); });
            modalBody.querySelector('.quiz-submit-btn').addEventListener('click', submitQuiz);
        }

        async function submitQuiz() {
            const emailEl  = document.getElementById('quizEmail');
            const nameEl   = document.getElementById('quizName');
            const phoneEl  = document.getElementById('quizPhone');
            const errEl    = document.getElementById('quizContactErr');
            const submitBtn = modalBody.querySelector('.quiz-submit-btn');

            const email = emailEl ? emailEl.value.trim() : '';
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errEl.textContent = 'Please enter a valid email address.';
                errEl.style.display = 'block';
                return;
            }
            errEl.style.display = 'none';

            const resultType = computeResult();
            const payload = {
                name:        nameEl  ? nameEl.value.trim()  : '',
                email,
                phone:       phoneEl ? phoneEl.value.trim() : '',
                result_type: resultType,
                answers:     answers.map((a, i) => ({
                    question: i + 1,
                    selected: questions[i].opts[a.idx].text,
                    category: a.cat
                }))
            };

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saving… <i class="fas fa-spinner fa-spin"></i>';

            try {
                await fetch('php/submit_quiz.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
            } catch (_) { /* graceful — show result regardless */ }

            showResult(resultType, payload.name);
        }

        function computeResult() {
            const counts = {};
            answers.forEach(a => { counts[a.cat] = (counts[a.cat] || 0) + 1; });
            return Object.entries(counts).sort((a, b) => b[1] - a[1])[0][0];
        }

        function showResult(type, name) {
            const r = resultMap[type] || resultMap.leads;
            const greeting = name
                ? `<p style="color:var(--accent-grey);margin-bottom:8px;">Hi ${escapeHtml(name)},</p>`
                : '';
            setProgress(1);
            modalBody.innerHTML = `
                <div class="quiz-result">
                    <div class="quiz-result-icon"><i class="fas ${r.icon}"></i></div>
                    ${greeting}
                    <h3>${r.title}</h3>
                    <p>${r.desc}</p>
                    <a href="${r.link}" class="quiz-result-cta">${r.cta} <i class="fas fa-arrow-right"></i></a>
                    <button class="quiz-restart-btn">Retake Quiz</button>
                </div>
            `;
            modalBody.querySelector('.quiz-restart-btn').addEventListener('click', () => {
                step = 0; answers = []; renderStep();
            });
        }

        floatBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
            step = 0; answers = [];
            renderStep();
        });

        if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');

        window.addEventListener('click', e => {
            if (e.target === modal) modal.style.display = 'none';
        });
    }

    // ===== RANDOM BUBBLE GENERATOR =====
    function initRandomBubbles() {
        const shapesContainer = document.querySelector('.floating-shapes');
        if (!shapesContainer) return;
        
        for (let i = 0; i < 8; i++) {
            setTimeout(() => {
                createRandomBubble(shapesContainer);
            }, i * 500);
        }
        
        setInterval(() => {
            createRandomBubble(shapesContainer);
        }, 4000);
    }

    function createRandomBubble(container) {
        const bubble = document.createElement('div');
        bubble.className = 'random-bubble';
        
        const size = Math.random() * 150 + 50;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const duration = Math.random() * 15 + 10;
        const delay = Math.random() * 5;
        const blur = Math.random() * 40 + 20;
        const opacity = Math.random() * 0.1 + 0.05;
        
        const startX = (Math.random() - 0.5) * 100;
        const startY = (Math.random() - 0.5) * 100;
        
        bubble.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: var(--accent-grey);
            opacity: ${opacity};
            filter: blur(${blur}px);
            left: ${posX}%;
            top: ${posY}%;
            transform: translate(${startX}px, ${startY}px);
            animation: randomFloat ${duration}s infinite alternate ease-in-out;
            animation-delay: ${delay}s;
            pointer-events: none;
            will-change: transform;
            z-index: 0;
        `;
        
        container.appendChild(bubble);
        
        setTimeout(() => {
            if (bubble.parentNode) {
                bubble.style.opacity = '0';
                bubble.style.transition = 'opacity 2s';
                setTimeout(() => {
                    if (bubble.parentNode) {
                        bubble.remove();
                    }
                }, 2000);
            }
        }, duration * 1000 * 2);
    }

    // ===== LAZY LOAD IMAGES =====
    function initLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '50px 0px' });

        images.forEach(img => imageObserver.observe(img));
    }
    
    // ===== SCROLL TO TOP BUTTON =====
    function initScrollToTop() {
        const button = document.getElementById('scrollToTop');
        if (!button) return;
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                button.classList.add('visible');
            } else {
                button.classList.remove('visible');
            }
        });
        
        button.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ===== REAL-TIME FORM VALIDATION =====
    function initFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                // Add real-time validation
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });
        });
    }

    function validateField(field) {
        const errorElement = field.nextElementSibling?.classList.contains('field-error') 
            ? field.nextElementSibling 
            : document.createElement('div');
        
        errorElement.className = 'field-error';
        
        if (!field.value.trim()) {
            field.classList.add('error');
            errorElement.textContent = 'This field is required';
            field.parentNode.insertBefore(errorElement, field.nextSibling);
            return false;
        }
        
        if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                field.classList.add('error');
                errorElement.textContent = 'Please enter a valid email';
                field.parentNode.insertBefore(errorElement, field.nextSibling);
                return false;
            }
        }
        
        field.classList.remove('error');
        if (errorElement.parentNode) {
            errorElement.remove();
        }
        return true;
    }

    // ===== INITIALIZE ALL =====
    document.addEventListener('DOMContentLoaded', function() {
        initCustomCursor();
        initFadeInOnScroll();
        initSmoothScroll();
        initHeaderScroll();
        initMobileNavigation();
        initContactForm();
        initQualificationForm();
        /*initCampaignPopup();*/
        setCurrentYear();
        initRandomBubbles();
        initQuizModal();
        initLazyLoading();
        initScrollToTop();
        initFormValidation();
    });

})();