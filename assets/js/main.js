
// Very Subtle 3D Mouse Tracking Logic for Cards
const tiltCards = document.querySelectorAll('.card-premium');

tiltCards.forEach(card => {
    card.addEventListener('mousemove', e => {
        if (window.innerWidth < 1024) return; // Only on desktop
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left; 
        const y = e.clientY - rect.top;  
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        // Reduced rotation threshold (Max 3 degrees)
        const rotateX = ((y - centerY) / centerY) * -3; 
        const rotateY = ((x - centerX) / centerX) * 3;
        
        card.classList.remove('resetting');
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.01, 1.01, 1.01)`;
        
        // Soft floating shadow
        card.style.boxShadow = `${-rotateY * 1.5}px ${rotateX * 1.5}px 20px rgba(0, 51, 85, 0.05)`;
    });

    card.addEventListener('mouseleave', () => {
        if (window.innerWidth < 1024) return;
        card.classList.add('resetting');
        card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
        card.style.boxShadow = ``; 
    });
});

// 3D Floating Navbar Scroll Effect 
const navbar = document.getElementById('navbar');
const navTexts = document.querySelectorAll('.nav-text');
const navDivider = document.querySelector('.nav-divider');
const navLogo = document.getElementById('nav-logo');

function updateNavbar() {
    const homePage = document.getElementById('home');
    const isHome = homePage && homePage.classList.contains('active');
    
    // Get current page from body data or script name
    const currentPage = document.body.getAttribute('data-page') || 'home';
    const isHomePage = currentPage === 'home' || currentPage === '404';
    
    if (window.scrollY> 50) {
        // Scrolled State - Floating Glass Pill
        navbar.classList.add('bg-white/95', 'shadow-glass-3d', 'border-white', 'backdrop-blur-xl');
        navbar.classList.remove('py-4', 'border-transparent', 'border-b', 'w-full', 'rounded-none', 'bg-transparent', 'top-0');
        navbar.classList.add('py-2', 'w-[95%]', 'max-w-[90rem]', 'rounded-[2rem]', 'top-4', 'md:top-6', 'border');
        
        navTexts.forEach(el => {
            el.classList.remove('text-white');
            el.classList.add('text-brand-navy');
        });
        if(navDivider) navDivider.classList.replace('border-white/20', 'border-gray-200');
        if(navLogo) navLogo.classList.remove('brightness-0', 'invert');
        
    } else {
        // Top State
        navbar.classList.remove('bg-white/95', 'shadow-glass-3d', 'border-white', 'border', 'w-[95%]', 'max-w-[90rem]', 'rounded-[2rem]', 'top-4', 'md:top-6', 'py-2', 'backdrop-blur-xl');
        navbar.classList.add('py-4', 'border-transparent', 'border-b', 'w-full', 'rounded-none', 'top-0', 'bg-transparent');
        
        if (isHomePage) {
            // Home page at top: Dark background, needs White text & logo
            navTexts.forEach(el => {
                el.classList.remove('text-brand-navy');
                el.classList.add('text-white');
            });
            if(navDivider) navDivider.classList.replace('border-gray-200', 'border-white/20');
            if(navLogo) navLogo.classList.add('brightness-0', 'invert');
        } else {
            // Other pages at top: Light background, needs Dark text & logo
            navTexts.forEach(el => {
                el.classList.remove('text-white');
                el.classList.add('text-brand-navy');
            });
            if(navDivider) navDivider.classList.replace('border-white/20', 'border-gray-200');
            if(navLogo) navLogo.classList.remove('brightness-0', 'invert');
        }
    }
}

window.addEventListener('scroll', updateNavbar);

// Scroll Animation Logic
const observerOptions = { root: null, rootMargin: '0px', threshold: 0.1 };
const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
    });
}, observerOptions);

function attachAnimations() {
    const elements = document.querySelectorAll('.fade-up');
    elements.forEach(el => observer.observe(el));
}
attachAnimations();

// Mobile Menu Logic
const mobileMenu = document.getElementById('mobile-menu');
let isMobileMenuOpen = false;

function toggleMobileMenu() {
    isMobileMenuOpen = !isMobileMenuOpen;
    if (isMobileMenuOpen) {
        mobileMenu.classList.remove('opacity-0', 'pointer-events-none');
        mobileMenu.classList.add('opacity-100');
        document.body.style.overflow = 'hidden'; 
    } else {
        mobileMenu.classList.add('opacity-0', 'pointer-events-none');
        mobileMenu.classList.remove('opacity-100');
        document.body.style.overflow = 'auto';
    }
}

// Initialize navbar on load
updateNavbar();
