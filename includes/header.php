<?php
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$pages = ['index', 'about', 'destinations', 'stories', 'contact'];
$pageTitles = [
    'index' => 'MBH Golden Global | Premium Travel Experiences',
    'about' => 'About Us | MBH Golden Global',
    'destinations' => 'Tour Packages | MBH Golden Global',
    'stories' => 'Travel Stories | MBH Golden Global',
    'contact' => 'Contact Us | MBH Golden Global',
];
$pageTitle = $pageTitle ?? ($pageTitles[$currentPage] ?? 'MBH Golden Global');
$isHomePage = $currentPage === 'index';

$siteSettings = [];
try {
    $settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
    $settingsStmt->execute();
    while ($setting = $settingsStmt->fetch()) {
        $siteSettings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $e) {
    error_log('Settings fetch error: ' . $e->getMessage());
}

$phone1 = $siteSettings['phone_1'] ?? '+966 536 785 506';
$phone2 = $siteSettings['phone_2'] ?? '+966 576 473 201';
$email1 = $siteSettings['email_1'] ?? 'mbhgoldenglobalcompany@gmail.com';
$email2 = $siteSettings['email_2'] ?? 'mbhgoldenglobal@gmail.com';
$address = $siteSettings['address'] ?? 'Buraydah, Al-Qassim, Saudi Arabia.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: {
                            navy: '#003355',
                            cyan: '#0082CA',
                            cyanDark: '#005a8d',
                            sand: '#F4F7F9',
                            white: '#FFFFFF',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 15px rgba(0, 130, 202, 0.2)',
                        'glow-lg': '0 0 30px rgba(0, 130, 202, 0.3)',
                        'glass-3d': '0 10px 30px -5px rgba(0, 51, 85, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6), inset 0 -1px 0 rgba(0, 0, 0, 0.02)',
                        '3d-puck': '0 4px 0 rgba(0, 51, 85, 0.05), 0 8px 15px rgba(0, 51, 85, 0.08), inset 0 2px 2px rgba(255, 255, 255, 0.8)',
                        '3d-puck-hover': '0 2px 0 rgba(0, 130, 202, 0.3), 0 5px 10px rgba(0, 130, 202, 0.15), inset 0 2px 2px rgba(255, 255, 255, 0.8)',
                        '3d-input': 'inset 0 2px 4px rgba(0, 0, 0, 0.03), 0 1px 0 rgba(255, 255, 255, 0.8)',
                    },
                    transitionTimingFunction: {
                        'apple': 'cubic-bezier(0.16, 1, 0.3, 1)',
                    },
                    animation: {
                        'float': 'floatOrb 12s infinite ease-in-out alternate',
                        'float-delayed': 'floatOrb 15s infinite ease-in-out alternate-reverse',
                    },
                    keyframes: {
                        floatOrb: {
                            '0%': { transform: 'translateY(0) rotateX(0) rotateY(0) scale(1)' },
                            '100%': { transform: 'translateY(-20px) rotateX(5deg) rotateY(-5deg) scale(1.01)' },
                        }
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer base {
            body {
                @apply bg-brand-sand min-h-screen overflow-x-hidden antialiased text-brand-navy;
            }
            ::selection {
                @apply bg-brand-cyan text-white;
            }
            /* Font Awesome icon sizing via w-*h-* Tailwind classes */
            i.fas, i.far, i.fab {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
                vertical-align: middle;
            }
            i.w-3  { font-size: 0.75rem;  }
            i.w-4  { font-size: 1rem;     }
            i.w-5  { font-size: 1.25rem;  }
            i.w-6  { font-size: 1.5rem;   }
            i.w-7  { font-size: 1.75rem;  }
            i.w-8  { font-size: 2rem;     }
        }

        @layer utilities {
            /* High-end reveal animations */
            .fade-up {
                @apply opacity-0 translate-y-12 transition-all duration-[1000ms] ease-apple;
            }
            .fade-up.visible {
                @apply opacity-100 translate-y-0;
            }
            .delay-100 { transition-delay: 100ms; }
            .delay-200 { transition-delay: 200ms; }
            .delay-300 { transition-delay: 300ms; }

            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

            /* Subtle 3D Extruded Text */
            .text-3d-light {
                text-shadow: 1px 1px 0 #fff, 2px 2px 5px rgba(0,0,0,0.05);
            }
            .text-3d-dark {
                text-shadow: 1px 1px 0 #001f33, 2px 2px 10px rgba(0,0,0,0.3);
            }
            .text-3d-cyan {
                text-shadow: 1px 1px 0 #006096, 2px 2px 10px rgba(0,130,202,0.2);
            }
        }

        @layer components {
            /* SPA Routing UI */
            .page-view {
                @apply hidden opacity-0 transition-all duration-500 ease-in-out scale-[0.98];
            }
            .page-view.active {
                @apply block opacity-100 scale-100;
            }

            /* Elegant Navigation Links */
            .nav-link { 
                @apply relative text-sm font-bold tracking-[0.15em] uppercase transition-all duration-300 transform text-brand-navy;
            }
            .nav-link.active-link { 
                @apply text-brand-cyan drop-shadow-sm; 
            }
            .nav-link::after {
                content: '';
                @apply absolute -bottom-2 left-1/2 -translate-x-1/2 w-0 h-[2px] bg-brand-cyan transition-all duration-300 rounded-full;
            }
            .nav-link:hover::after, .nav-link.active-link::after {
                @apply w-full;
            }

            /* Low-Profile Acrylic Buttons */
            .btn-primary {
                @apply inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-gradient-to-b from-[#00a2ff] to-[#0082CA] text-white text-xs font-bold tracking-[0.15em] uppercase transition-all duration-300 relative border border-[#00aaff];
                box-shadow: 0 3px 0 #005a8d, 0 8px 15px rgba(0, 130, 202, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.3);
                transform: translateY(0);
                will-change: transform, box-shadow;
            }
            .btn-primary:active {
                box-shadow: 0 0px 0 #005a8d, 0 2px 5px rgba(0, 130, 202, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.3);
                transform: translateY(3px);
                @apply border-[#0082CA] from-[#0082CA] to-[#006096];
            }
            
            .btn-outline {
                @apply inline-flex items-center justify-center px-8 py-3.5 rounded-full border border-gray-200 bg-gradient-to-b from-white to-gray-50 text-brand-navy text-xs font-bold tracking-[0.15em] uppercase transition-all duration-300 relative;
                box-shadow: 0 3px 0 #cbd5e1, 0 8px 15px rgba(0, 0, 0, 0.05), inset 0 1px 1px rgba(255, 255, 255, 1);
                transform: translateY(0);
                will-change: transform, box-shadow;
            }
            .btn-outline:active {
                box-shadow: 0 0px 0 #cbd5e1, 0 2px 5px rgba(0, 0, 0, 0.05), inset 0 1px 1px rgba(255, 255, 255, 1);
                transform: translateY(3px);
                @apply from-gray-50 to-gray-100;
            }

            /* Low-Profile Glass Cards */
            .card-premium {
                transform-style: preserve-3d;
                transform: perspective(1000px);
                transition: transform 0.15s ease-out, box-shadow 0.3s ease-out;
                will-change: transform;
                @apply bg-white/90 backdrop-blur-lg border border-white shadow-glass-3d;
            }
            .card-premium.resetting {
                transition: transform 0.6s ease-apple, box-shadow 0.6s ease-apple;
            }
            
            /* Reduced Z-Axis Depth */
            .inner-3d {
                transform: translateZ(15px);
                transition: transform 0.4s ease-apple;
                will-change: transform;
            }
            .card-premium:hover .inner-3d {
                transform: translateZ(30px); /* Lowered from 80px to 30px */
            }

            /* Ambient Orbs */
            .orb-3d {
                @apply absolute rounded-full -z-10 opacity-40;
                background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9), rgba(0, 130, 202, 0.1), rgba(0, 51, 85, 0.02));
            }

            /* Minimalist Form Inputs */
            .input-minimal {
                @apply w-full bg-brand-sand/50 backdrop-blur-sm border border-transparent shadow-3d-input py-4 px-5 text-brand-navy font-bold placeholder-gray-400 focus:outline-none focus:bg-white focus:shadow-[inset_0_2px_4px_rgba(0,130,202,0.05),0_0_0_1px_rgba(0,130,202,0.3)] transition-all rounded-xl;
            }
        }
    </style>
</head>

<body class="text-brand-navy relative" data-page="<?php echo $currentPage === 'index' ? 'home' : $currentPage; ?>">

    <!-- Ambient Background Spheres -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-[-1]">
        <div class="orb-3d w-80 h-80 top-[10%] -left-10 animate-float"></div>
        <div class="orb-3d w-[35rem] h-[35rem] top-[60%] -right-20 animate-float-delayed"
            style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,1), rgba(0,130,202,0.03), rgba(0,51,85,0.01));">
        </div>
    </div>

    <!-- Floating Navbar Pill -->
    <nav id="navbar"
        class="fixed w-full left-1/2 -translate-x-1/2 top-0 z-50 transition-all duration-500 border-b border-transparent bg-transparent py-4 px-6 sm:px-8">
        <div class="max-w-[90rem] mx-auto w-full flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center cursor-pointer group">
                <img id="nav-logo" src="./assets/img/logo.png" alt="MBH Golden Global"
                    class="h-16 md:h-20 object-contain transition-all duration-500 ease-apple group-hover:scale-105 brightness-0 invert"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">

                <div class="hidden font-serif font-bold text-2xl tracking-wide logo-text text-white text-3d-light">
                    <span class="text-brand-cyan">MBH</span> GLOBAL
                </div>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex space-x-10 items-center">
                <a href="index.php"
                    class="nav-link nav-text text-white<?php echo $currentPage === 'index' ? ' active-link' : ''; ?>">Home</a>
                <a href="about.php"
                    class="nav-link nav-text text-white<?php echo $currentPage === 'about' ? ' active-link' : ''; ?>">About
                    Us</a>
                <a href="destinations.php"
                    class="nav-link nav-text text-white<?php echo $currentPage === 'destinations' ? ' active-link' : ''; ?>">Destinations</a>
                <a href="stories.php"
                    class="nav-link nav-text text-white<?php echo $currentPage === 'stories' ? ' active-link' : ''; ?>">Stories</a>
                <a href="contact.php"
                    class="nav-link nav-text text-white<?php echo $currentPage === 'contact' ? ' active-link' : ''; ?>">Contact</a>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-6">
                <div
                    class="hidden md:flex items-center gap-6 border-l border-white/20 pl-6 nav-divider transition-colors duration-300">
                    <button class="nav-text text-white hover:text-brand-cyan transition-transform hover:scale-105"><i
                            class="fas fa-search w-6 h-6"></i></button>
                    <a href="destinations.php"
                        class="btn-primary !py-3 !px-8 hidden xl:inline-flex text-xs !shadow-none !translate-y-0 border-none">Book
                        Now</a>
                </div>
                <button
                    onclick="document.getElementById('mobile-menu').classList.toggle('opacity-0'); document.getElementById('mobile-menu').classList.toggle('pointer-events-none');"
                    class="lg:hidden nav-text p-2 -mr-2 text-white hover:text-brand-cyan transition-transform active:scale-90 bg-white/10 rounded-full border border-white/20 shadow-inner">
                    <i class="fas fa-bars w-5 h-5"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu"
        class="fixed inset-0 bg-white/95 backdrop-blur-xl z-[60] flex flex-col opacity-0 pointer-events-none transition-all duration-300 ease-apple">
        <img src="./assets/img/logo.png"
            class="absolute -right-20 top-1/2 -translate-y-1/2 w-[150%] opacity-5 pointer-events-none z-[-1]" alt="">

        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <img src="./assets/img/logo.png" alt="MBH Golden Global" class="h-20 object-contain"
                onerror="this.style.display='none';">
            <button
                onclick="document.getElementById('mobile-menu').classList.toggle('opacity-0'); document.getElementById('mobile-menu').classList.toggle('pointer-events-none');"
                class="p-2 text-brand-navy bg-brand-sand hover:bg-brand-cyan hover:text-white transition-colors rounded-full shadow-inner border border-white">
                <i class="fas fa-times w-5 h-5"></i>
            </button>
        </div>
        <div class="flex flex-col gap-6 px-8 py-12 overflow-y-auto h-full justify-center">
            <a href="index.php"
                class="text-3xl md:text-4xl font-serif text-brand-navy text-left hover:text-brand-cyan transition-colors text-3d-light">Home.</a>
            <a href="about.php"
                class="text-3xl md:text-4xl font-serif text-brand-navy text-left hover:text-brand-cyan transition-colors text-3d-light">About
                Us.</a>
            <a href="destinations.php"
                class="text-3xl md:text-4xl font-serif text-brand-navy text-left hover:text-brand-cyan transition-colors text-3d-light">Destinations.</a>
            <a href="stories.php"
                class="text-3xl md:text-4xl font-serif text-brand-navy text-left hover:text-brand-cyan transition-colors text-3d-light">Stories.</a>
            <a href="contact.php"
                class="text-3xl md:text-4xl font-serif text-brand-navy text-left hover:text-brand-cyan transition-colors text-3d-light">Contact.</a>

            <div class="mt-8 pt-8 border-t border-gray-100 flex flex-col gap-2">
                <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-brand-cyan">Direct Line</span>
                <p class="text-brand-navy text-lg tracking-wide font-bold"><?php echo htmlspecialchars($phone1); ?></p>
                <p class="text-gray-500 text-xs tracking-wide"><?php echo htmlspecialchars($email1); ?></p>
            </div>
        </div>
    </div>

    <!-- MAIN APP CONTAINER -->
    <main id="app-root">