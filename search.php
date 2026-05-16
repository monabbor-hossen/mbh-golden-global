<?php
require_once 'includes/db.php';
$pageTitle = 'Search Results | MBH Golden Global';
require_once 'includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$packages = [];
$stories = [];

if (!empty($q)) {
    try {
        $searchParam = "%" . $q . "%";

        // Search Packages
        $pkgStmt = $pdo->prepare("
            SELECT id, title, slug, location, duration, description, price, image_url 
            FROM packages 
            WHERE is_active = 1 
            AND (title LIKE :q1 OR location LIKE :q2 OR description LIKE :q3)
            ORDER BY created_at DESC
        ");
        $pkgStmt->execute([
            ':q1' => $searchParam,
            ':q2' => $searchParam,
            ':q3' => $searchParam
        ]);
        $packages = $pkgStmt->fetchAll();

        // Search Stories
        $storyStmt = $pdo->prepare("
            SELECT id, title, slug, excerpt, image_url, tag, published_date 
            FROM stories 
            WHERE is_published = 1 
            AND (title LIKE :q1 OR excerpt LIKE :q2 OR content LIKE :q3)
            ORDER BY published_date DESC
        ");
        $storyStmt->execute([
            ':q1' => $searchParam,
            ':q2' => $searchParam,
            ':q3' => $searchParam
        ]);
        $stories = $storyStmt->fetchAll();

    } catch (PDOException $e) {
        error_log('Search error: ' . $e->getMessage());
    }
}
?>

<main class="min-h-screen pt-32 pb-24 bg-brand-navy relative overflow-hidden">
    <!-- Ambient Floating Orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-[0]">
        <div class="orb-3d w-[40rem] h-[40rem] top-[10%] -left-[10%] animate-float opacity-30"
            style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.4), rgba(0, 130, 202, 0.2), rgba(0, 51, 85, 0.1));">
        </div>
        <div class="orb-3d w-[35rem] h-[35rem] bottom-[-10%] -right-[10%] animate-float-delayed opacity-30"
            style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.4), rgba(0, 130, 202, 0.2), rgba(0, 51, 85, 0.1));">
        </div>
    </div>

    <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 relative z-10">

        <!-- Header Section -->
        <div class="fade-up mb-16 text-center">
            <h1 class="text-4xl md:text-6xl font-serif text-white mb-4 text-3d-dark">
                <?php if (empty($q)): ?>
                    Please enter a search term
                <?php else: ?>
                    Search Results for <br><i class="text-brand-cyan font-light">"<?php echo htmlspecialchars($q); ?>"</i>
                <?php endif; ?>
            </h1>
            <div class="flex justify-center gap-4">
                <p class="text-white/70 font-medium text-lg">
                    <?php if (!empty($q)): ?>
                        Found <?php echo count($packages); ?> destination(s) and <?php echo count($stories); ?> story(s)
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (!empty($q)): ?>
            <?php if (empty($packages) && empty($stories)): ?>
                <!-- Empty State -->
                <div
                    class="fade-up delay-100 max-w-2xl mx-auto card-premium !bg-white/10 !backdrop-blur-md !border-white/20 p-12 text-center rounded-[2.5rem]">
                    <div class="w-20 h-20 bg-brand-cyan/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-search text-3xl text-brand-cyan"></i>
                    </div>
                    <h3 class="text-2xl font-serif text-white mb-4">No results found</h3>
                    <p class="text-white/70 mb-8">We couldn't find anything matching "<?php echo htmlspecialchars($q); ?>". Try
                        adjusting your search or explore our popular destinations.</p>
                    <a href="destinations.php" class="btn-primary !px-8">Browse Destinations</a>
                </div>
            <?php else: ?>

                <!-- Packages Section -->
                <?php if (!empty($packages)): ?>
                    <div class="mb-24">
                        <h2 class="text-2xl font-bold tracking-[0.2em] uppercase text-white mb-10 flex items-center gap-4 fade-up">
                            <span class="w-8 h-[2px] bg-brand-cyan"></span> Destinations
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 md:gap-12 perspective-[1000px]">
                            <?php foreach ($packages as $pkg): ?>
                                <a href="tour/<?php echo htmlspecialchars($pkg['slug']); ?>"
                                    class="block group cursor-pointer fade-up card-premium rounded-[2rem] p-4 bg-brand-sand border border-gray-100 hover:border-brand-cyan transition-colors">
                                    <div class="relative h-[25rem] rounded-[1.5rem] overflow-hidden mb-6 shadow-sm inner-3d">
                                        <img src="<?php echo htmlspecialchars($pkg['image_url']); ?>"
                                            class="w-full h-full object-cover transition-transform duration-[2s] ease-apple group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-brand-navy/80 to-transparent opacity-90">
                                        </div>
                                        <div class="absolute bottom-6 left-6 right-6 inner-3d flex flex-col items-start">
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="bg-brand-cyan/90 backdrop-blur-md border border-white/20 text-white text-[9px] font-bold tracking-[0.2em] uppercase inline-block px-4 py-2 rounded-full"><?php echo htmlspecialchars($pkg['location']); ?></span>
                                                <?php if (!empty($pkg['duration'])): ?>
                                                    <span class="bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-[10px] font-medium tracking-wider flex items-center gap-1.5 px-3 py-1.5 rounded-full"><i class="far fa-clock w-3 h-3 text-white/80"></i> <?php echo htmlspecialchars($pkg['duration']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <h3 class="text-3xl font-serif text-white mb-1 w-full">
                                                <?php echo htmlspecialchars($pkg['title']); ?></h3>
                                        </div>
                                    </div>
                                    <div class="px-3 pb-2 flex justify-between items-center inner-3d">
                                        <p class="text-gray-600 text-xs leading-relaxed max-w-[55%] font-medium">
                                            <?php echo htmlspecialchars(mb_substr($pkg['description'], 0, 60)); ?>...</p>
                                        <div class="bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100">
                                            <span class="block text-[9px] uppercase text-gray-400 font-bold mb-1">From</span>
                                            <span class="font-black text-lg text-brand-navy">SAR
                                                <?php echo number_format($pkg['price'], 0); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stories Section -->
                <?php if (!empty($stories)): ?>
                    <div>
                        <h2 class="text-2xl font-bold tracking-[0.2em] uppercase text-white mb-10 flex items-center gap-4 fade-up">
                            <span class="w-8 h-[2px] bg-brand-cyan"></span> Travel Stories
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 md:gap-12 perspective-[1000px]">
                            <?php foreach ($stories as $story):
                                $publishedDate = new DateTime($story['published_date']);
                                ?>
                                <article
                                    class="group cursor-pointer fade-up card-premium bg-white rounded-[2rem] p-5 border border-gray-100 hover:border-brand-cyan transition-colors duration-500">
                                    <a href="story/<?php echo htmlspecialchars($story['slug']); ?>"
                                        class="block relative h-64 rounded-[1.5rem] overflow-hidden mb-8 shadow-sm inner-3d group-hover:opacity-90">
                                        <img src="<?php echo htmlspecialchars($story['image_url']); ?>"
                                            class="w-full h-full object-cover transition-transform duration-[1.5s] ease-apple group-hover:scale-105">
                                        <div
                                            class="absolute top-4 left-4 bg-brand-cyan text-white px-4 py-2 rounded-xl font-bold text-[9px] tracking-[0.2em] uppercase">
                                            <?php echo htmlspecialchars($story['tag'] ?? 'Story'); ?></div>
                                    </a>
                                    <div class="px-4 inner-3d">
                                        <div class="flex items-center mb-4">
                                            <span
                                                class="text-gray-400 text-xs font-bold uppercase tracking-[0.15em] bg-brand-sand px-3 py-1.5 rounded-lg border border-white"><?php echo $publishedDate->format('M d, Y'); ?></span>
                                        </div>
                                        <a href="story/<?php echo htmlspecialchars($story['slug']); ?>" class="block mb-4">
                                            <h3
                                                class="text-2xl font-serif text-brand-navy font-bold group-hover:text-brand-cyan transition-colors leading-snug">
                                                <?php echo htmlspecialchars($story['title']); ?></h3>
                                        </a>
                                        <p class="text-gray-500 font-medium leading-relaxed mb-6 text-sm">
                                            <?php echo htmlspecialchars(mb_substr($story['excerpt'], 0, 100)); ?>...</p>
                                        <a href="story/<?php echo htmlspecialchars($story['slug']); ?>"
                                            class="btn-outline w-full !py-3 !rounded-xl text-[10px]">Read Story</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        <?php else: ?>
            <!-- No search query entered state -->
            <div
                class="fade-up delay-100 max-w-2xl mx-auto card-premium !bg-white/10 !backdrop-blur-md !border-white/20 p-12 text-center rounded-[2.5rem] mt-8">
                <i class="fas fa-search text-5xl text-white/50 mb-6"></i>
                <h3 class="text-2xl font-serif text-white mb-4">Discover Your Next Adventure</h3>
                <p class="text-white/70 mb-8">Click the search icon in the navigation bar to start exploring our
                    destinations and stories.</p>
                <button
                    onclick="document.getElementById('search-overlay').classList.remove('opacity-0', 'pointer-events-none'); document.getElementById('search-input').focus();"
                    class="btn-primary !px-8">Open Search</button>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>