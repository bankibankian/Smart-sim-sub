/**
 * main.js - Premium Interactive Scripts for SIM Selling Landing Page
 */

document.addEventListener('DOMContentLoaded', () => {
    initHeaderScroll();
    initMobileMenu();
    initPricingToggler();
    initProfitCalculator();
    initSmoothScroll();
    initFormProcessing();
});

/**
 * Adds Scrolled class to Header on scroll to change styling gracefully
 */
function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;

    const handleScroll = () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Run initially
}

/**
 * Mobile Navigation Menu Toggler
 */
function initMobileMenu() {
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.querySelector('.nav-menu');
    
    if (!toggle || !menu) return;

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('active');
        
        // Animated hamburger toggle lines
        const spans = toggle.querySelectorAll('span');
        if (menu.classList.contains('active')) {
            spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
            spans[1].style.opacity = '0';
            spans[2].style.transform = 'rotate(-45deg) translate(7px, -7px)';
        } else {
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });

    // Close menu when clicking outside or clicking a link
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('active');
            resetHamburger();
        }
    });

    const links = menu.querySelectorAll('.nav-link');
    links.forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('active');
            resetHamburger();
        });
    });

    function resetHamburger() {
        const spans = toggle.querySelectorAll('span');
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
    }
}

/**
 * Pricing Card Swapper (Staff Wholesale vs Public Retail)
 */
function initPricingToggler() {
    const toggler = document.querySelector('.pricing-toggle-wrapper');
    const cardsContainer = document.querySelector('.pricing-grid');
    if (!toggler || !cardsContainer) return;

    const basePricing = {
        staff: [
            { price: '350', period: '/sim', desc: 'Perfect for local staff starting out with direct community sales.', features: ['Min order: 50 SIMs', 'Standard 4G LTE/5G network', 'Basic admin dashboard access', 'WhatsApp staff support group', 'Weekly payouts'] },
            { price: '280', period: '/sim', desc: 'Optimized for full-time staff and community sub-distributors.', features: ['Min order: 250 SIMs', 'Priority bulk SIM dispatch', 'Advanced inventory tracking', 'Dedicated account specialist', 'Same-day payouts', 'Co-branded marketing kits'] },
            { price: '200', period: '/sim', desc: 'Enterprise wholesale tier for mega sales staff with large networks.', features: ['Min order: 1000 SIMs', 'Lowest custom SIM cost', 'API integration access', 'White-label SIM carrier options', 'Instant automated payouts', 'Full marketing & event support'] }
        ],
        public: [
            { price: '1,500', period: '/one-time', desc: 'Standard retail SIM card configuration for daily public usage.', features: ['Includes 10GB activation data', 'N2,000 airtime welcome bonus', 'No registration contract', '24/7 client helpline access', 'Self-service SIM swap active'] },
            { price: '3,000', period: '/one-time', desc: 'Premium retail SIM card configuration with extra bonuses.', features: ['Includes 25GB activation data', 'N5,000 airtime welcome bonus', 'Premium VIP number pool', '24/7 client helpline access', 'Self-service SIM swap active', 'Free eSIM profiling optional'] },
            { price: '7,500', period: '/one-time', desc: 'Ultimate retail SIM card with large data bundle and perks.', features: ['Includes 100GB activation data', 'N15,000 airtime welcome bonus', 'Gold tier number selection', 'Priority routing network support', 'Unlimited SMS (30 days)', 'Free international call bonus (60 mins)'] }
        ]
    };

    toggler.addEventListener('click', () => {
        toggler.classList.toggle('toggle-active');
        const isActive = toggler.classList.contains('toggle-active');
        const mode = isActive ? 'staff' : 'public';
        
        updatePricingCards(basePricing[mode]);
    });

    function updatePricingCards(data) {
        const cards = cardsContainer.querySelectorAll('.pricing-card');
        cards.forEach((card, idx) => {
            const priceElement = card.querySelector('.pricing-amount');
            const periodElement = card.querySelector('.pricing-period');
            const descElement = card.querySelector('.pricing-desc');
            const featuresList = card.querySelector('.pricing-features-list');

            if (priceElement && data[idx]) {
                // Add minor fade effect during transition
                card.style.opacity = '0.5';
                card.style.transform = 'translateY(5px)';
                
                setTimeout(() => {
                    priceElement.textContent = data[idx].price;
                    periodElement.textContent = data[idx].period;
                    descElement.textContent = data[idx].desc;
                    
                    // Re-render features
                    featuresList.innerHTML = '';
                    data[idx].features.forEach((feature, fIdx) => {
                        const li = document.createElement('li');
                        li.className = 'pricing-feature-item';
                        li.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>${feature}</span>
                        `;
                        featuresList.appendChild(li);
                    });

                    card.style.opacity = '1';
                    card.style.transform = card.classList.contains('featured') ? 'scale(1.03)' : 'none';
                }, 200);
            }
        });
    }
}

/**
 * Data savings calculator for consumers
 */
function initProfitCalculator() {
    const rangeSims = document.getElementById('calc-range-sims');
    const rangeMarkup = document.getElementById('calc-range-markup');
    
    const displaySims = document.getElementById('display-sims');
    const displayMarkup = document.getElementById('display-markup');
    
    const valCost = document.getElementById('val-cost');
    const valRevenue = document.getElementById('val-revenue');
    const valProfit = document.getElementById('val-profit');

    if (!rangeSims || !rangeMarkup) return;

    const recalculate = () => {
        const dataCount = parseInt(rangeSims.value);
        const monthsCount = parseInt(rangeMarkup.value);
        
        displaySims.textContent = dataCount + ' GB';
        displayMarkup.textContent = monthsCount + (monthsCount === 1 ? ' Month' : ' Months');

        const totalCost = 1200 * dataCount * monthsCount;      // Standard Network Cost (₦1,200 per GB)
        const totalRevenue = 400 * dataCount * monthsCount;    // SmartSIM Cost (₦400 per GB)
        const totalProfit = totalCost - totalRevenue;          // Total Cash Saved

        // Animate the counters or simply insert
        animateCounter(valCost, totalCost);
        animateCounter(valRevenue, totalRevenue);
        animateCounter(valProfit, totalProfit);
    };

    rangeSims.addEventListener('input', recalculate);
    rangeMarkup.addEventListener('input', recalculate);
    
    recalculate(); // Init calculator values
}

/**
 * Simple Number Ticker animation for calculator outputs
 */
function animateCounter(element, targetValue) {
    if (!element) return;
    
    const currentValue = parseInt(element.getAttribute('data-value') || '0');
    if (currentValue === targetValue) return;

    element.setAttribute('data-value', targetValue);
    
    const duration = 400; // ms
    const startTime = performance.now();

    const updateValue = (currentTime) => {
        const elapsedTime = currentTime - startTime;
        const progress = Math.min(elapsedTime / duration, 1);
        // Easing function: easeOutQuad
        const easeProgress = progress * (2 - progress);
        
        const currentValueCalculated = Math.round(currentValue + (targetValue - currentValue) * easeProgress);
        element.textContent = '₦' + currentValueCalculated.toLocaleString('en-US');

        if (progress < 1) {
            requestAnimationFrame(updateValue);
        }
    };

    requestAnimationFrame(updateValue);
}

/**
 * Smooth Scroll Interaction
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (!target) return;

            e.preventDefault();
            const headerOffset = 80;
            const elementPosition = target.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        });
    });
}

/**
 * Global form submission processing state visual handler
 */
function initFormProcessing() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                // Prevent double submission
                if (submitBtn.classList.contains('is-processing')) {
                    e.preventDefault();
                    return;
                }
                
                submitBtn.classList.add('is-processing');
                submitBtn.style.opacity = '0.8';
                submitBtn.style.cursor = 'not-allowed';
                
                const textSpan = submitBtn.querySelector('span') || submitBtn;
                const originalText = textSpan.innerText || submitBtn.textContent;
                
                if (textSpan === submitBtn) {
                    submitBtn.textContent = 'Processing...';
                } else {
                    textSpan.textContent = 'Processing...';
                }
                
                const icon = submitBtn.querySelector('svg');
                let originalIconHTML = '';
                if (icon) {
                    originalIconHTML = icon.outerHTML;
                    icon.innerHTML = `<path class="opacity-25" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>`;
                    icon.classList.add('animate-spin');
                }
                
                // Listen to form reset to restore original state
                form.addEventListener('reset', () => {
                    submitBtn.classList.remove('is-processing');
                    submitBtn.style.opacity = '';
                    submitBtn.style.cursor = '';
                    if (textSpan === submitBtn) {
                        submitBtn.textContent = originalText;
                    } else {
                        textSpan.textContent = originalText;
                    }
                    if (icon) {
                        icon.outerHTML = originalIconHTML;
                    }
                });
                
                // For the mock inline alert form, let's restore the button state after a short timeout so they can submit again
                if (form.getAttribute('onsubmit') && form.getAttribute('onsubmit').includes('alert')) {
                    setTimeout(() => {
                        form.reset();
                    }, 500);
                }
            }
        });
    });
}
