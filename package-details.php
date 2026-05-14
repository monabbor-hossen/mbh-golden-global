<?php
/**
 * Single Package Details Page
 *
 * Fetches one active package from the DB by ?id=, renders it in the
 * project's Premium 3D Glassmorphism aesthetic.
 *
 * Security: input validated, PDO prepared statements, 404 redirect on any failure.
 */

require_once 'includes/db.php';

// ─── 1. Validate ?id= ─────────────────────────────────────────────────────────
$rawId = $_GET['id'] ?? null;

if ($rawId === null || !ctype_digit((string)$rawId) || (int)$rawId <= 0) {
    header('Location: 404.php');
    exit;
}

$packageId = (int)$rawId;

// ─── 2. Secure PDO query ──────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT id, title, location, description, price, image_url, tag 
        FROM   packages
        WHERE  id = :id
          AND  is_active = 1
        LIMIT  1
    ");
    $stmt->execute([':id' => $packageId]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Package fetch error: ' . $e->getMessage());
    header('Location: 404.php');
    exit;
}

if (!$package) {
    header('Location: 404.php');
    exit;
}

// ─── 3. Prepare display data ──────────────────────────────────────────────────
$safeTitle     = htmlspecialchars($package['title']);
$safeLocation  = htmlspecialchars($package['location']);
$safeTag       = htmlspecialchars($package['tag']);
$safeImageUrl  = htmlspecialchars($package['image_url']);
$safePrice     = number_format($package['price'], 0);
$bodyContent   = $package['description']; // WYSIWYG HTML — rendered directly, NOT escaped

// Note: Duration and Group Size are not in the DB schema, providing placeholder static data for the UI
$safeDuration  = "7 Days, 6 Nights"; 
$safeGroupSize = "Up to 12 People"; 

// ─── 4. Dynamic <title> — respected by header.php via null-coalescing ─────────
$pageTitle = $package['title'] . ' | MBH Golden Global';

require_once 'includes/header.php';
?>

        <!-- ============================================================ -->
        <!--  PACKAGE DETAILS PAGE                                        -->
        <!-- ============================================================ -->

        <!-- ── Hero ──────────────────────────────────────────────────── -->
        <section class="relative h-[80vh] min-h-[600px] w-full overflow-hidden">
            <!-- Cover image as background -->
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-[10s] ease-out scale-105" 
                 style="background-image: url('<?= $safeImageUrl ?>');" id="hero-img"></div>

            <!-- Layered gradient overlay to melt into navy body -->
            <div class="absolute inset-0 bg-gradient-to-t from-brand-navy via-brand-navy/60 to-transparent"></div>
            <div class="absolute inset-0 shadow-[inset_0_-100px_100px_-50px_rgba(0,51,85,1)]"></div>
        </section>

        <!-- ── Dark Navy Content Section ─────────────────────────────── -->
        <section class="bg-brand-navy relative min-h-screen pb-24 -mt-32">

            <!-- Ambient glowing orbs -->
            <div class="absolute top-20 left-10 w-[500px] h-[500px] rounded-full
                        blur-3xl opacity-20 mix-blend-screen pointer-events-none"
                 style="background: radial-gradient(circle, rgba(0,130,202,0.8), transparent 70%);
                        animation: floatOrb 14s ease-in-out infinite alternate;"></div>
            <div class="absolute bottom-20 right-[-10%] w-[600px] h-[600px] rounded-full
                        blur-3xl opacity-10 mix-blend-screen pointer-events-none"
                 style="background: radial-gradient(circle, rgba(255,255,255,0.5), transparent 70%);
                        animation: floatOrb 18s ease-in-out infinite alternate-reverse;"></div>

            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 relative z-10">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                    
                    <!-- ── Left Column (Content) ─────────────────────────── -->
                    <div class="lg:col-span-8 fade-up" style="transition-delay:100ms">
                        
                        <!-- Top Tag -->
                        <?php if(!empty($package['tag'])): ?>
                        <div class="mb-4">
                            <span class="inline-flex items-center gap-2 bg-brand-cyan/20 backdrop-blur-md
                                         border border-brand-cyan/40 text-brand-cyan text-[10px] font-black
                                         tracking-[0.25em] uppercase px-4 py-2 rounded-full">
                                <i data-lucide="award" class="w-3 h-3"></i>
                                <?= $safeTag ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <!-- Title -->
                        <h1 class="font-serif text-white text-5xl md:text-6xl lg:text-7xl
                                   font-bold leading-tight mb-10 text-3d-cyan">
                            <?= $safeTitle ?>
                        </h1>

                        <!-- Quick Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                            <!-- Destination -->
                            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 flex items-center gap-5 hover:bg-white/10 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-brand-cyan/20 border border-brand-cyan/30 flex flex-shrink-0 items-center justify-center">
                                    <i data-lucide="map-pin" class="w-6 h-6 text-brand-cyan"></i>
                                </div>
                                <div>
                                    <span class="block text-[10px] text-white/50 uppercase tracking-[0.2em] font-bold mb-1">Destination</span>
                                    <span class="block text-white font-medium text-sm"><?= $safeLocation ?></span>
                                </div>
                            </div>
                            
                            <!-- Duration -->
                            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 flex items-center gap-5 hover:bg-white/10 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-brand-cyan/20 border border-brand-cyan/30 flex flex-shrink-0 items-center justify-center">
                                    <i data-lucide="clock" class="w-6 h-6 text-brand-cyan"></i>
                                </div>
                                <div>
                                    <span class="block text-[10px] text-white/50 uppercase tracking-[0.2em] font-bold mb-1">Duration</span>
                                    <span class="block text-white font-medium text-sm"><?= $safeDuration ?></span>
                                </div>
                            </div>
                            
                            <!-- Group Size -->
                            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 flex items-center gap-5 hover:bg-white/10 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-brand-cyan/20 border border-brand-cyan/30 flex flex-shrink-0 items-center justify-center">
                                    <i data-lucide="users" class="w-6 h-6 text-brand-cyan"></i>
                                </div>
                                <div>
                                    <span class="block text-[10px] text-white/50 uppercase tracking-[0.2em] font-bold mb-1">Group Size</span>
                                    <span class="block text-white font-medium text-sm"><?= $safeGroupSize ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="h-px bg-gradient-to-r from-white/20 via-white/10 to-transparent w-full mb-12"></div>

                        <!-- Description WYSIWYG -->
                        <div class="prose prose-invert prose-lg max-w-none text-white/80 leading-relaxed font-sans package-content wysiwyg-content">
                            <?= $bodyContent ?>
                        </div>

                    </div>

                    <!-- ── Right Column (Sticky Booking Card) ────────────── -->
                    <div class="lg:col-span-4 fade-up" style="transition-delay:300ms">
                        <div class="sticky top-32 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-[2rem] p-8 shadow-[0_20px_50px_rgba(0,130,202,0.15)] flex flex-col">
                            
                            <div class="mb-8">
                                <span class="block text-white/60 text-xs uppercase tracking-[0.2em] font-bold mb-2">Starting from</span>
                                <div class="flex items-end gap-2 text-white">
                                    <span class="text-2xl font-medium text-brand-cyan mb-1">SAR</span>
                                    <span class="text-5xl font-bold font-serif"><?= $safePrice ?></span>
                                </div>
                                <span class="block text-white/40 text-xs mt-2">*Price per person, taxes included.</span>
                            </div>

                            <ul class="space-y-4 mb-10 text-sm text-white/70 font-medium">
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-cyan"></i>
                                    Expert Local Guides
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-cyan"></i>
                                    Premium Accommodation
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-cyan"></i>
                                    24/7 Concierge Support
                                </li>
                            </ul>

                            <a href="contact.php?package_id=<?= $packageId ?>"
                               class="w-full flex items-center justify-center py-5 bg-gradient-to-b from-[#00a2ff] to-[#0082CA] 
                                      border border-[#00aaff] text-white text-xs font-black tracking-[0.2em] uppercase rounded-xl
                                      transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_10px_30px_rgba(0,130,202,0.4)]
                                      active:scale-95 active:shadow-none">
                                Inquire About This Package
                            </a>

                            <div class="mt-6 flex items-center justify-center gap-3 text-white/40 text-[10px] uppercase tracking-[0.1em] font-semibold">
                                <i data-lucide="shield-check" class="w-4 h-4"></i> Secure Booking Guaranteed
                            </div>
                        </div>
                    </div>

                </div><!-- /grid -->
            </div><!-- /container -->
        </section><!-- /dark section -->

<?php require_once 'includes/footer.php'; ?>

<!-- ── WYSIWYG content typography ──────────────────────────────────────────── -->
<style>
    /* Prose styles for raw WYSIWYG HTML rendered inside .package-content */
    .package-content {
        color: rgba(255,255,255,0.80);
        font-family: 'Inter', sans-serif;
        font-size: 1.0625rem;
        line-height: 1.85;
    }
    .package-content h1,
    .package-content h2,
    .package-content h3,
    .package-content h4 {
        font-family: 'Playfair Display', serif;
        color: #ffffff;
        margin-top: 2.25rem;
        margin-bottom: 0.875rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .package-content h1 { font-size: 2rem; }
    .package-content h2 { font-size: 1.6rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; }
    .package-content h3 { font-size: 1.3rem; color: #0082CA; }
    .package-content h4 { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); }

    .package-content p {
        margin-bottom: 1.5rem;
        color: rgba(255,255,255,0.78);
    }
    .package-content a {
        color: #0082CA;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s;
    }
    .package-content a:hover { color: #00c6ff; }

    .package-content ul,
    .package-content ol {
        margin: 1.25rem 0 1.5rem 1.5rem;
        color: rgba(255,255,255,0.75);
    }
    .package-content ul { list-style: disc; }
    .package-content ol { list-style: decimal; }
    .package-content li { margin-bottom: 0.5rem; padding-left: 0.25rem; }

    .wysiwyg-content .ql-align-center { display: block; margin: 0 auto; text-align: center; }
    .wysiwyg-content .ql-align-justify { text-align: justify; }

    /* Override Tailwind's default block display for Quill images */
    .wysiwyg-content img {
        display: inline-block; 
        max-width: 100%;
        height: auto;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    /* Force text wrapping for left-aligned images */
    .wysiwyg-content img[style*="float: left"],
    .wysiwyg-content .ql-align-left {
        float: left !important;
        margin: 0.5rem 1.5rem 1rem 0 !important;
        display: block !important;
    }

    /* Force text wrapping for right-aligned images */
    .wysiwyg-content img[style*="float: right"],
    .wysiwyg-content .ql-align-right {
        float: right !important;
        margin: 0.5rem 0 1rem 1.5rem !important;
        display: block !important;
    }

    /* Ensure the container stretches to fit floated images */
    .wysiwyg-content::after {
        content: "";
        display: table;
        clear: both;
    }
</style>

<script>
    // ── Hero image subtle parallax ────────────────────────────────────────────
    const heroImg = document.getElementById('hero-img');
    if (heroImg) {
        window.addEventListener('scroll', () => {
            const offset = window.scrollY * 0.25;
            heroImg.style.transform = `scale(1.05) translateY(${offset}px)`;
        }, { passive: true });
    }

    // ── Initialise Lucide icons ───────────────────────────────────────────────
    lucide.createIcons();
</script>
