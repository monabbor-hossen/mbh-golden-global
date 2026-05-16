<?php
/**
 * Single Story / Blog Post Page
 *
 * Fetches one published story from the DB by ?id=, renders it in the
 * project's Premium 3D Glassmorphism aesthetic.
 *
 * Security: input validated, PDO prepared statements, 404 redirect on any failure.
 */

require_once 'includes/db.php';

// ─── 1. Validate ?slug= ───────────────────────────────────────────────────────
$slug = $_GET['slug'] ?? null;

if (empty($slug)) {
    header('Location: 404.php');
    exit;
}

// ─── 2. Secure PDO query ──────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT id, title, slug, tag, excerpt, meta_description, content, image_url, published_date
        FROM   stories
        WHERE  slug = :slug
          AND  is_published = 1
        LIMIT  1
    ");
    $stmt->execute([':slug' => $slug]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Single story fetch error: ' . $e->getMessage());
    header('Location: 404.php');
    exit;
}

if (!$story) {
    header('Location: 404.php');
    exit;
}

// ─── 3. Prepare display data ──────────────────────────────────────────────────
$safeTitle     = htmlspecialchars($story['title']);
$safeTag       = htmlspecialchars($story['tag']);
$safeExcerpt   = htmlspecialchars($story['excerpt']);
$safeImageUrl  = htmlspecialchars($story['image_url']);
$bodyContent   = $story['content'];   // WYSIWYG HTML — rendered directly, NOT escaped
$publishedDate = new DateTime($story['published_date']);
$formattedDate = $publishedDate->format('F j, Y');

// Estimated reading time (~200 wpm)
$wordCount   = str_word_count(strip_tags($bodyContent));
$readingMins = max(1, (int) ceil($wordCount / 200));

// ─── 4. Dynamic <title> — respected by header.php via null-coalescing ─────────
$pageTitle = $story['title'] . ' | MBH Golden Global';
$meta_description = !empty($story['meta_description']) ? $story['meta_description'] : mb_strimwidth(strip_tags($story['excerpt']), 0, 160, '...');
$meta_image = $story['image_url'];

require_once 'includes/header.php';
?>

        <!-- ============================================================ -->
        <!--  SINGLE STORY PAGE                                           -->
        <!-- ============================================================ -->

        <!-- Reading Progress Bar -->
        <div id="reading-progress"
             class="fixed top-0 left-0 h-[3px] w-0 z-[100] transition-none"
             style="background: linear-gradient(90deg, #0082CA, #00c6ff);
                    box-shadow: 0 0 8px rgba(0,130,202,0.6);"></div>

        <!-- ── Hero ──────────────────────────────────────────────────── -->
        <section class="relative h-[75vh] min-h-[520px] overflow-hidden">

            <!-- Cover image -->
            <img src="<?= $safeImageUrl ?>"
                 alt="<?= $safeTitle ?>"
                 class="absolute inset-0 w-full h-full object-cover scale-105 transition-transform duration-[8s] ease-out"
                 id="hero-img">

            <!-- Layered gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#003355] via-[#003355]/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#003355]/50 via-transparent to-transparent"></div>

            <!-- Hero content (bottom-aligned) -->
            <div class="relative z-10 h-full flex flex-col justify-end pb-14 md:pb-20
                        px-6 sm:px-10 lg:px-16 max-w-5xl mx-auto w-full">

                <!-- Tag pill -->
                <div class="mb-5 fade-up" style="transition-delay:0ms">
                    <span class="inline-flex items-center gap-2 bg-brand-cyan/20 backdrop-blur-md
                                 border border-brand-cyan/40 text-brand-cyan text-[10px] font-black
                                 tracking-[0.25em] uppercase px-4 py-2 rounded-full">
                        <i class="fas fa-tag w-3 h-3"></i>
                        <?= $safeTag ?>
                    </span>
                </div>

                <!-- Title -->
                <h1 class="font-serif text-white text-4xl sm:text-5xl md:text-6xl lg:text-7xl
                           font-bold leading-[1.1] mb-6 text-3d-dark fade-up"
                    style="transition-delay:100ms">
                    <?= $safeTitle ?>
                </h1>

                <!-- Meta row -->
                <div class="flex flex-wrap items-center gap-5 text-white/60 text-xs
                            font-bold tracking-[0.15em] uppercase fade-up"
                     style="transition-delay:200ms">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-calendar w-4 h-4 text-brand-cyan"></i>
                        <?= $formattedDate ?>
                    </span>
                    <span class="w-px h-4 bg-white/20"></span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-clock w-4 h-4 text-brand-cyan"></i>
                        <?= $readingMins ?> min read
                    </span>
                    <span class="w-px h-4 bg-white/20"></span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-book-open w-4 h-4 text-brand-cyan"></i>
                        <?= number_format($wordCount) ?> words
                    </span>
                </div>
            </div>
        </section>

        <!-- ── Dark Navy Content Section ─────────────────────────────── -->
        <section class="bg-brand-navy relative overflow-hidden">

            <!-- Ambient glowing orbs -->
            <div class="absolute top-0 left-1/4 w-[500px] h-[500px] rounded-full
                        blur-3xl opacity-20 mix-blend-screen pointer-events-none"
                 style="background: radial-gradient(circle, rgba(0,130,202,0.8), transparent 70%);
                        animation: floatOrb 14s ease-in-out infinite alternate;"></div>
            <div class="absolute bottom-1/4 right-[-5%] w-[600px] h-[600px] rounded-full
                        blur-3xl opacity-10 mix-blend-screen pointer-events-none"
                 style="background: radial-gradient(circle, rgba(255,255,255,0.5), transparent 70%);
                        animation: floatOrb 18s ease-in-out infinite alternate-reverse;"></div>
            <div class="absolute top-1/2 left-[-5%] w-[300px] h-[300px] rounded-full
                        blur-3xl opacity-15 mix-blend-screen pointer-events-none"
                 style="background: radial-gradient(circle, rgba(0,198,255,0.4), transparent 70%);
                        animation: floatOrb 10s ease-in-out infinite alternate;"></div>

            <div class="max-w-4xl mx-auto px-6 sm:px-8 py-14 md:py-20 relative z-10">

                <!-- Top bar: back button + share -->
                <div class="flex items-center justify-between mb-10 fade-up">
                    <a href="stories.php"
                       class="group inline-flex items-center gap-3 text-white/60 hover:text-white
                              text-xs font-black tracking-[0.2em] uppercase transition-all duration-300
                              hover:-translate-y-0.5 hover:drop-shadow-[0_0_10px_rgba(0,130,202,0.6)]">
                        <span class="flex items-center justify-center w-9 h-9 rounded-full
                                     bg-white/10 border border-white/20 backdrop-blur-md
                                     group-hover:bg-brand-cyan/20 group-hover:border-brand-cyan/40
                                     transition-all duration-300">
                            <i class="fas fa-arrow-left w-4 h-4"></i>
                        </span>
                        Back to Stories
                    </a>

                    <!-- Share hint -->
                    <div class="hidden sm:flex items-center gap-3 text-white/30 text-[10px]
                                font-bold tracking-[0.2em] uppercase">
                        <span>Share</span>
                        <div class="flex gap-2">
                            <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>showCopied())"
                                    class="w-8 h-8 rounded-full bg-white/10 border border-white/20 flex items-center
                                           justify-center text-white/50 hover:text-brand-cyan hover:bg-brand-cyan/10
                                           hover:border-brand-cyan/30 transition-all duration-300"
                                    title="Copy link">
                                <i class="fas fa-link w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <span id="copied-msg"
                              class="text-brand-cyan opacity-0 transition-opacity duration-300 text-[9px]">
                            Copied!
                        </span>
                    </div>
                </div>

                <!-- ── Main glassmorphic article card ─────────────────── -->
                <article class="bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl
                                p-8 md:p-12 shadow-[0_20px_50px_rgba(0,0,0,0.5)]
                                fade-up" style="transition-delay:100ms">

                    <!-- Pull-quote excerpt -->
                    <div class="border-l-[3px] border-brand-cyan pl-6 mb-10">
                        <p class="text-white/70 text-lg md:text-xl leading-relaxed font-medium italic">
                            <?= $safeExcerpt ?>
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="flex items-center gap-4 mb-10">
                        <div class="flex-1 h-px bg-white/10"></div>
                        <i class="fas fa-plane w-4 h-4 text-brand-cyan/50"></i>
                        <div class="flex-1 h-px bg-white/10"></div>
                    </div>

                    <!-- WYSIWYG body content -->
                    <div class="story-content wysiwyg-content">
                        <?= $bodyContent ?>
                    </div>

                </article>

                <!-- ── Post footer: tags + CTA ────────────────────────── -->
                <div class="mt-12 flex flex-col sm:flex-row items-start sm:items-center
                            justify-between gap-6 fade-up" style="transition-delay:200ms">

                    <!-- Tag -->
                    <div class="flex items-center gap-3">
                        <span class="text-white/30 text-[10px] font-black tracking-[0.2em] uppercase">Filed under</span>
                        <span class="inline-flex items-center gap-1.5 bg-brand-cyan/10 border border-brand-cyan/30
                                     text-brand-cyan text-[10px] font-black tracking-[0.2em] uppercase
                                     px-4 py-2 rounded-full">
                            <i class="fas fa-tag w-3 h-3"></i>
                            <?= $safeTag ?>
                        </span>
                    </div>

                    <!-- Back button (bottom) -->
                    <a href="stories.php"
                       class="group inline-flex items-center gap-3 bg-white/10 hover:bg-brand-cyan/20
                              border border-white/20 hover:border-brand-cyan/40 backdrop-blur-md
                              text-white text-[10px] font-black tracking-[0.2em] uppercase
                              px-6 py-3 rounded-full transition-all duration-300
                              hover:-translate-y-1 hover:shadow-[0_0_20px_rgba(0,130,202,0.3)]">
                        <i class="fas fa-book-reader w-4 h-4 text-brand-cyan"></i>
                        More Stories
                    </a>
                </div>

                <!-- ── Explore CTA card ───────────────────────────────── -->
                <div class="mt-16 bg-gradient-to-br from-brand-cyan/20 to-white/5
                            backdrop-blur-xl border border-brand-cyan/20 rounded-3xl p-8 md:p-10
                            text-center shadow-[0_10px_40px_rgba(0,0,0,0.3)] fade-up"
                     style="transition-delay:300ms">
                    <div class="w-14 h-14 rounded-2xl bg-brand-cyan/20 border border-brand-cyan/30
                                flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-globe w-7 h-7 text-brand-cyan"></i>
                    </div>
                    <h3 class="font-serif text-white text-2xl md:text-3xl font-bold mb-3">
                        Ready to <i class="text-brand-cyan font-light">experience</i> it yourself?
                    </h3>
                    <p class="text-white/50 text-sm leading-relaxed max-w-lg mx-auto mb-8">
                        Turn inspiration into adventure. Browse our handcrafted tour packages and let us take care of every detail.
                    </p>
                    <a href="destinations.php"
                       class="inline-flex items-center gap-3 bg-gradient-to-b from-[#00a2ff] to-[#0082CA]
                              border border-[#00aaff] text-white text-xs font-black tracking-[0.2em] uppercase
                              px-8 py-4 rounded-full transition-all duration-300
                              hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(0,130,202,0.5)]">
                        <i class="fas fa-map w-4 h-4"></i>
                        Explore Packages
                    </a>
                </div>

            </div><!-- /max-w-4xl -->
        </section><!-- /dark section -->

<?php require_once 'includes/footer.php'; ?>

<!-- ── WYSIWYG content typography ──────────────────────────────────────────── -->
<style>
    /* Prose styles for raw WYSIWYG HTML rendered inside .story-content */
    .story-content {
        color: rgba(255,255,255,0.80);
        font-family: 'Inter', sans-serif;
        font-size: 1.0625rem;
        line-height: 1.85;
    }
    .story-content h1,
    .story-content h2,
    .story-content h3,
    .story-content h4 {
        font-family: 'Playfair Display', serif;
        color: #ffffff;
        margin-top: 2.25rem;
        margin-bottom: 0.875rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .story-content h1 { font-size: 2rem; }
    .story-content h2 { font-size: 1.6rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; }
    .story-content h3 { font-size: 1.3rem; color: #0082CA; }
    .story-content h4 { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.85rem; color: rgba(255,255,255,0.5); }

    .story-content p {
        margin-bottom: 1.5rem;
        color: rgba(255,255,255,0.78);
    }
    .story-content a {
        color: #0082CA;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s;
    }
    .story-content a:hover { color: #00c6ff; }

    .story-content ul,
    .story-content ol {
        margin: 1.25rem 0 1.5rem 1.5rem;
        color: rgba(255,255,255,0.75);
    }
    .story-content ul { list-style: disc; }
    .story-content ol { list-style: decimal; }
    .story-content li { margin-bottom: 0.5rem; padding-left: 0.25rem; }

    .story-content blockquote {
        border-left: 3px solid #0082CA;
        margin: 2rem 0;
        padding: 1rem 1.5rem;
        background: rgba(0,130,202,0.08);
        border-radius: 0 0.75rem 0.75rem 0;
        color: rgba(255,255,255,0.65);
        font-style: italic;
        font-size: 1.1rem;
    }



    .story-content strong { color: #ffffff; font-weight: 700; }
    .story-content em { color: rgba(255,255,255,0.85); }

    .story-content code {
        background: rgba(0,130,202,0.15);
        border: 1px solid rgba(0,130,202,0.2);
        color: #00c6ff;
        padding: 0.15em 0.45em;
        border-radius: 0.35rem;
        font-size: 0.875em;
    }

    .story-content pre {
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 1rem;
        padding: 1.5rem;
        overflow-x: auto;
        margin: 2rem 0;
    }
    .story-content pre code {
        background: transparent;
        border: none;
        padding: 0;
        color: rgba(255,255,255,0.85);
    }

    .story-content hr {
        border: none;
        border-top: 1px solid rgba(255,255,255,0.1);
        margin: 2.5rem 0;
    }

    .story-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
        font-size: 0.9rem;
    }
    .story-content th {
        background: rgba(0,130,202,0.15);
        color: #ffffff;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(255,255,255,0.1);
        text-align: left;
    }
    .story-content td {
        padding: 0.65rem 1rem;
        border: 1px solid rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.7);
    }
    .story-content tr:nth-child(even) td {
        background: rgba(255,255,255,0.03);
    }

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
    // ── Reading progress bar ──────────────────────────────────────────────────
    const progressBar = document.getElementById('reading-progress');
    window.addEventListener('scroll', () => {
        const scrollTop    = window.scrollY;
        const docHeight    = document.documentElement.scrollHeight - window.innerHeight;
        const progress     = docHeight> 0 ? (scrollTop / docHeight) * 100 : 0;
        progressBar.style.width = progress + '%';
    }, { passive: true });

    // ── Hero image subtle parallax ────────────────────────────────────────────
    const heroImg = document.getElementById('hero-img');
    if (heroImg) {
        window.addEventListener('scroll', () => {
            const offset = window.scrollY * 0.25;
            heroImg.style.transform = `scale(1.05) translateY(${offset}px)`;
        }, { passive: true });
    }

    // ── Copy link feedback ────────────────────────────────────────────────────
    function showCopied() {
        const msg = document.getElementById('copied-msg');
        if (!msg) return;
        msg.style.opacity = '1';
        setTimeout(() => { msg.style.opacity = '0'; }, 2000);
    }


</script>

<!-- Schema.org JSON-LD for SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "<?= htmlspecialchars($story['title']) ?>",
  "image": "<?= htmlspecialchars($story['image_url']) ?>",
  "description": "<?= htmlspecialchars($meta_description) ?>",
  "datePublished": "<?= $story['published_date'] ?>",
  "author": {
    "@type": "Organization",
    "name": "MBH Golden Global"
  }
}
</script>
