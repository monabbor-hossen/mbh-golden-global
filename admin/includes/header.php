<?php
/**
 * Admin Universal Header
 *
 * Outputs the full HTML <head>, ambient background, mobile top bar,
 * sidebar include, and opens the <main> content wrapper.
 *
 * Expects:  $page_title (string) — set by the including page before require.
 * Optional: $page_description (string) — meta description for the page.
 *
 * NOTE: This file does NOT start the session, require db.php, or call
 * requireAdmin(). Each page is responsible for its own auth/DB setup
 * before including this file.
 */

$page_title = $page_title ?? 'Admin Panel | MBH Golden Global';
$page_description = $page_description ?? 'MBH Golden Global Admin Command Center';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,700&display=swap"
        rel="stylesheet">
    <!-- Quill Rich Text Editor CSS (JS loaded in footer.php) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Tailwind Config: Brand Tokens + Animations -->
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
                            sand: '#F4F7F9',
                        }
                    },
                    animation: {
                        'float': 'floatOrb 12s infinite ease-in-out alternate',
                        'float-delayed': 'floatOrb 15s infinite ease-in-out alternate-reverse',
                    },
                    keyframes: {
                        floatOrb: {
                            '0%': { transform: 'translateY(0) scale(1)' },
                            '100%': { transform: 'translateY(-20px) scale(1.05)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Global Admin Styles (Quill overrides + misc fixes) -->
    <style>
        /* ── Quill Toolbar ─────────────────────────────────── */
        .ql-toolbar.ql-snow {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: rgba(255, 255, 255, 0.4);
            border-radius: 0.75rem 0.75rem 0 0;
            display: flex;
            flex-wrap: wrap;
            /* responsive: wrap toolbar buttons on mobile */
        }

        .ql-container.ql-snow {
            border-color: rgba(255, 255, 255, 0.4);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }

        /* ── Quill Image Floating ──────────────────────────── */
        .ql-editor img {
            display: inline-block;
        }

        .ql-editor img[style*="float: left"],
        .ql-editor .ql-align-left {
            float: left !important;
            margin: 0.5rem 1.5rem 1rem 0 !important;
        }

        .ql-editor img[style*="float: right"],
        .ql-editor .ql-align-right {
            float: right !important;
            margin: 0.5rem 0 1rem 1.5rem !important;
        }

        .ql-editor::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body class="bg-[#003355] text-white min-h-screen overflow-x-hidden flex flex-col">

    <!-- ── Ambient Background Orbs ──────────────────────── -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute rounded-full w-[30rem] h-[30rem] top-[-10%] -left-20 animate-float mix-blend-screen opacity-20"
            style="background: radial-gradient(circle at center, rgba(0,130,202,0.5), transparent 70%);"></div>
        <div class="absolute rounded-full w-[45rem] h-[45rem] bottom-[-20%] -right-20 animate-float-delayed mix-blend-screen opacity-20"
            style="background: radial-gradient(circle at center, rgba(255,255,255,0.4), transparent 70%);"></div>
        <div
            class="absolute inset-0 bg-[url('../assets/img/bg1.avif')] bg-cover bg-center opacity-[0.15] mix-blend-overlay">
        </div>
    </div>

    <!-- ── Page Shell ────────────────────────────────────── -->
    <div class="flex flex-col min-h-screen relative z-10">

        <!-- Sidebar (desktop: fixed pill; mobile: off-canvas) -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- Mobile-only Top Bar -->
        <div
            class="md:hidden sticky top-0 z-30 w-full flex items-center justify-between p-4 bg-[#001a2d]/90 backdrop-blur-2xl border-b border-white/10">
            <span class="text-lg font-serif font-bold text-white">
                <img src="../assets/img/logo.png" alt="MBH"
                    class="h-12 object-contain mx-auto brightness-0 invert drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] mb-2">
            </span>
            <button id="hamburger-btn" aria-label="Open navigation menu"
                class="p-2 text-white hover:text-brand-cyan transition-colors rounded-lg hover:bg-white/10">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Main Content Wrapper (opened here, closed in footer.php) -->
        <main
            class="flex-1 w-full md:pl-80 p-4 sm:p-6 md:py-8 md:pr-8 min-h-screen flex flex-col relative z-10 transition-all duration-300">

            <!-- ── Universal Page Header Bar ─────────────────── -->
            <?php if (!empty($page_heading)): ?>
                <header
                    class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-4 md:p-6 flex flex-wrap gap-4 justify-between items-center mb-6 md:mb-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                    <div class="flex items-center gap-4 flex-wrap">
                        <h2 class="text-2xl md:text-3xl font-serif text-white"><?php echo $page_heading; ?></h2>
                        <?php if (!empty($page_actions))
                            echo $page_actions; ?>
                    </div>
                    <a href="logout.php"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white/5 hover:bg-red-500/20 border border-white/10 hover:border-red-500/50 text-white/80 hover:text-red-400 hover:shadow-[0_0_15px_rgba(239,68,68,0.2)] rounded-xl transition-all font-medium text-sm whitespace-nowrap">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </a>
                </header>
            <?php endif; ?>
            <?php
            // NOTE: </main>, </div>, </body>, </html> are closed by footer.php
//       (or by the page itself for pages that don't use footer.php).
            ?>