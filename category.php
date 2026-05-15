<?php
require_once 'includes/db.php';

// Securely read $_GET['slug']
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Fetch the category details
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: index.php');
        exit;
    }

    // Fetch all stories belonging to this category
    $storiesStmt = $pdo->prepare("
        SELECT id, title, slug, excerpt, image_url, tag, published_date 
        FROM stories 
        WHERE category_id = ? AND is_published = TRUE 
        ORDER BY published_date DESC
    ");
    $storiesStmt->execute([$category['id']]);
    $stories = $storiesStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Category fetch error: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

$page_title = htmlspecialchars($category['name']) . ' - MBH Golden Global';
require_once 'includes/header.php';
?>

        <!-- ========================================== -->
        <!-- CATEGORY HERO                              -->
        <!-- ========================================== -->
        <header class="relative pt-48 pb-32 <?php echo empty($category['image']) ? 'bg-brand-navy' : ''; ?> border-b border-white/10 overflow-hidden">
            <?php if (!empty($category['image'])): ?>
                <div class="absolute inset-0 z-0">
                    <img src="assets/uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" alt="Category Background" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                </div>
            <?php endif; ?>
            
            <div class="relative z-10 max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 fade-up perspective-[1000px]">
                <div class="inner-3d">
                    <span class="text-brand-cyan text-xs font-bold tracking-[0.3em] uppercase mb-6 block text-3d-light">Category</span>
                    <h1 class="text-5xl md:text-8xl font-serif text-white mb-8 leading-tight text-3d-light"><?php echo htmlspecialchars($category['name']); ?></h1>
                </div>
            </div>
        </header>

        <section class="py-24 bg-brand-sand min-h-screen relative z-10">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 perspective-[2000px]">
                    <?php
                    if (empty($stories)) {
                        echo "<div class='col-span-full py-12 text-center border-2 border-dashed border-gray-200 rounded-3xl'>";
                        echo "<p class='text-brand-navy text-lg font-medium'>Check back soon for new stories in this category.</p>";
                        echo "</div>";
                    } else {
                        $delay = 0;
                        foreach ($stories as $story) {
                            $delayClass = $delay > 0 ? 'delay-' . ($delay * 100) : '';
                            $publishedDate = new DateTime($story['published_date']);
                            echo "
                            <article class='group cursor-pointer fade-up {$delayClass} card-premium bg-white rounded-[2rem] p-5 border border-gray-100 hover:border-brand-cyan transition-colors duration-500'>
                                <div class='relative h-64 rounded-[1.5rem] overflow-hidden mb-8 shadow-sm inner-3d'>
                                    <img src='" . htmlspecialchars($story['image_url']) . "' class='w-full h-full object-cover transition-transform duration-[1.5s] ease-apple group-hover:scale-105'>
                                    <div class='absolute top-4 left-4 bg-brand-cyan text-white px-4 py-2 rounded-xl font-bold text-[9px] tracking-[0.2em] uppercase'>" . htmlspecialchars($story['tag']) . "</div>
                                </div>
                                <div class='px-4 inner-3d'>
                                    <div class='flex items-center mb-4'>
                                        <span class='text-gray-400 text-xs font-bold uppercase tracking-[0.15em] bg-brand-sand px-3 py-1.5 rounded-lg border border-white'>" . $publishedDate->format('M d, Y') . "</span>
                                    </div>
                                    <h3 class='text-2xl font-serif text-brand-navy mb-4 font-bold group-hover:text-brand-cyan transition-colors leading-snug'>" . htmlspecialchars($story['title']) . "</h3>
                                    <p class='text-gray-500 font-medium leading-relaxed mb-6 text-sm'>" . htmlspecialchars($story['excerpt']) . "</p>
                                    <a href='single-post.php?id=" . (int)$story['id'] . "' class='btn-outline w-full !py-3 !rounded-xl text-[10px]'>Read Story →</a>
                                </div>
                            </article>
                            ";
                            $delay++;
                        }
                    }
                    ?>
                </div>
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>
