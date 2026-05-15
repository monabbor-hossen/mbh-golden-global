<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

        <!-- ========================================== -->
        <!-- STORIES PAGE (Blog)                        -->
        <!-- ========================================== -->
        <header class="pt-48 pb-20 bg-white border-b border-gray-100">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 fade-up perspective-[1000px]">
                <div class="inner-3d">
                    <span class="text-brand-cyan text-xs font-bold tracking-[0.3em] uppercase mb-6 block text-3d-light">Journal</span>
                    <h1 class="text-5xl md:text-8xl font-serif text-brand-navy mb-8 leading-tight text-3d-light">Travel <i class="font-bold text-brand-cyan">Stories</i></h1>
                    <p class="text-xl md:text-2xl text-gray-500 font-medium max-w-2xl leading-relaxed">Insights, tips, and inspiration from our global travels.</p>
                </div>
            </div>
        </header>

        <section class="py-24 bg-brand-sand min-h-screen relative z-10">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                
                <!-- Filter by Category -->
                <div class="mb-12 flex flex-wrap gap-4 items-center justify-center">
                    <a href="stories.php" class="px-6 py-2 rounded-full border border-brand-cyan bg-brand-cyan text-white font-bold text-xs uppercase tracking-wider hover:shadow-lg transition-all">All Stories</a>
                    <?php
                    try {
                        $catsStmt = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC");
                        while ($cat = $catsStmt->fetch()) {
                            echo "<a href='category.php?slug=" . htmlspecialchars($cat['slug']) . "' class='px-6 py-2 rounded-full border border-gray-300 bg-white text-brand-navy font-bold text-xs uppercase tracking-wider hover:border-brand-cyan hover:text-brand-cyan transition-all'>" . htmlspecialchars($cat['name']) . "</a>";
                        }
                    } catch (PDOException $e) {
                        // silently fail for category filters
                    }
                    ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 perspective-[2000px]">
                    <?php
                    try {
                        // Fetch all published stories from database with category info
                        $storiesStmt = $pdo->prepare("
                            SELECT stories.*, categories.name AS category_name, categories.slug AS category_slug 
                            FROM stories 
                            LEFT JOIN categories ON stories.category_id = categories.id 
                            WHERE stories.is_published = TRUE 
                            ORDER BY stories.published_date DESC
                        ");
                        $storiesStmt->execute();
                        $stories = $storiesStmt->fetchAll();

                        if (empty($stories)) {
                            echo "<p class='text-brand-navy col-span-full text-lg'>No stories published yet. Check back soon!</p>";
                        } else {
                            $delay = 0;
                            foreach ($stories as $story) {
                                $delayClass = $delay > 0 ? 'delay-' . ($delay * 100) : '';
                                $publishedDate = new DateTime($story['published_date']);
                                echo "
                                <article class='group cursor-pointer fade-up {$delayClass} card-premium bg-white rounded-[2rem] p-5 border border-gray-100 hover:border-brand-cyan transition-colors duration-500'>
                                    <a href='single-post.php?id=" . (int)$story['id'] . "' class='block relative h-64 rounded-[1.5rem] overflow-hidden mb-8 shadow-sm inner-3d group-hover:opacity-90'>
                                        <img src='" . htmlspecialchars($story['image_url']) . "' class='w-full h-full object-cover transition-transform duration-[1.5s] ease-apple group-hover:scale-105'>
                                        <div class='absolute top-4 left-4 bg-brand-cyan text-white px-4 py-2 rounded-xl font-bold text-[9px] tracking-[0.2em] uppercase'>" . htmlspecialchars($story['tag']) . "</div>
                                    </a>
                                    <div class='px-4 inner-3d'>
                                        <div class='flex items-center mb-4 gap-2'>
                                            <span class='text-gray-400 text-xs font-bold uppercase tracking-[0.15em] bg-brand-sand px-3 py-1.5 rounded-lg border border-white'>" . $publishedDate->format('M d, Y') . "</span>
                                            " . (!empty($story['category_name']) ? "<a href='category.php?slug=" . htmlspecialchars($story['category_slug']) . "' class='text-brand-cyan text-[10px] font-bold uppercase tracking-[0.15em] bg-brand-cyan/10 px-3 py-1.5 rounded-lg border border-brand-cyan/20 hover:bg-brand-cyan hover:text-white transition-colors z-10 relative'>" . htmlspecialchars($story['category_name']) . "</a>" : "") . "
                                        </div>
                                        <a href='single-post.php?id=" . (int)$story['id'] . "'>
                                            <h3 class='text-2xl font-serif text-brand-navy mb-4 font-bold group-hover:text-brand-cyan transition-colors leading-snug'>" . htmlspecialchars($story['title']) . "</h3>
                                        </a>
                                        <p class='text-gray-500 font-medium leading-relaxed mb-6 text-sm'>" . htmlspecialchars($story['excerpt']) . "</p>
                                        <a href='single-post.php?id=" . (int)$story['id'] . "' class='btn-outline w-full !py-3 !rounded-xl text-[10px]'>Read Story →</a>
                                    </div>
                                </article>
                                ";
                                $delay++;
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Stories fetch error: ' . $e->getMessage());
                        echo "<p class='text-red-500 col-span-full'>Error loading stories. Please try again later.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>
