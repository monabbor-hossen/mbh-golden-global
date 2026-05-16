<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

        <!-- ========================================== -->
        <!-- HOME PAGE                                  -->
        <!-- ========================================== -->
        <!-- Full Bleed Hero Video -->
        <header class="relative h-screen w-full overflow-hidden bg-brand-navy perspective-[1000px]"> 
            <!-- Dark gradient overlays to ensure header visibility -->
            <div class="absolute inset-0 bg-brand-navy/40 z-10 pointer-events-none"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-brand-navy/90 via-brand-navy/20 to-brand-sand z-10 pointer-events-none"></div>
            
            <video class="absolute top-0 left-0 w-full h-full object-cover z-0 opacity-90 transform scale-105" autoplay loop muted playsinline>
                <source src="https://monabbor-hossen.github.io/mbh-golden-global/mp_.mp4" type="video/mp4">
                <source src="https://assets.mixkit.co/videos/preview/mixkit-airplane-flying-over-the-clouds-in-the-sky-27886-large.mp4" type="video/mp4">
            </video>
            
            <div class="relative z-20 h-full flex flex-col items-center justify-center text-center px-6 max-w-5xl mx-auto fade-up" style="transform: translateZ(30px);">
                <div class="bg-white/90 backdrop-blur-md border border-white/50 px-6 py-2 rounded-full mb-8 shadow-sm flex items-center gap-3">
                    <img src="https://monabbor-hossen.github.io/mbh-golden-global/Logo%20png-01.png" class="h-4 object-contain">
                    <span class="text-brand-navy text-[10px] md:text-xs tracking-[0.2em] uppercase font-bold">
                        Welcome to MBH Global
                    </span>
                </div>
                
                <h1 class="text-5xl sm:text-6xl md:text-[7.5rem] font-serif font-bold text-white mb-6 leading-[1.0] text-3d-dark tracking-tight">
                    Explore the <br><i class="font-medium text-brand-cyan">Extraordinary</i>
                </h1>
                <p class="text-white/90 text-base md:text-xl font-medium mb-10 max-w-2xl leading-relaxed drop-shadow-md">
                    Simple, comfortable, and affordable journeys to the world's most captivating destinations.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 perspective-[500px]">
                    <a href="destinations.php" class="btn-primary !px-10 !py-4" style="transform: translateZ(10px);">Discover Destinations</a>
                </div>
            </div>

            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center gap-3 animate-bounce">
                <span class="text-brand-navy text-[9px] uppercase tracking-[0.3em] font-bold">Scroll</span>
                <div class="w-[2px] h-10 bg-gradient-to-b from-brand-cyan to-transparent rounded-full"></div>
            </div>
        </header>

        <!-- Curated Categories -->
        <section class="py-20 bg-brand-sand relative z-10 border-b border-white">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 fade-up">
                    <div class="max-w-2xl">
                        <span class="text-brand-cyan text-xs font-bold tracking-[0.2em] uppercase mb-4 block text-3d-light">Experiences</span>
                        <h2 class="text-4xl md:text-6xl font-serif text-brand-navy leading-tight text-3d-light">Find what makes <br><i class="font-light text-brand-cyan">your heart beat</i></h2>
                    </div>
                </div>

                <div class="flex overflow-x-auto gap-6 pb-12 no-scrollbar fade-up delay-100 px-4 -mx-4 perspective-[1000px]">
                    <?php
                    try {
                        // Fetch 6 categories from database
                        $categoriesStmt = $pdo->prepare("SELECT id, name, slug, image FROM categories ORDER BY name ASC LIMIT 6");
                        $categoriesStmt->execute();
                        $categories = $categoriesStmt->fetchAll();

                        if (empty($categories)) {
                            echo "<p class='text-brand-navy'>No categories available.</p>";
                        } else {
                            foreach ($categories as $category) {
                                $img = !empty($category['image']) ? 'assets/uploads/categories/' . $category['image'] : 'assets/img/bg1.avif';
                                echo "
                                <a href='category.php?slug=" . htmlspecialchars($category['slug']) . "' class='min-w-[140px] md:min-w-[180px] flex flex-col items-center gap-5 group cursor-pointer'>
                                    <div class='w-32 h-32 md:w-44 md:h-44 rounded-full overflow-hidden relative border-[4px] border-white bg-white shadow-3d-puck group-hover:shadow-3d-puck-hover group-hover:-translate-y-1 transition-all duration-300'>
                                        <img src='" . htmlspecialchars($img) . "' class='w-full h-full object-cover transition-transform duration-[1.5s] ease-apple opacity-90 group-hover:opacity-100 group-hover:scale-105'>
                                    </div>
                                    <span class='text-xs font-bold tracking-[0.2em] uppercase text-brand-navy group-hover:text-brand-cyan transition-colors'>" . htmlspecialchars($category['name']) . "</span>
                                </a>
                                ";
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Categories fetch error: ' . $e->getMessage());
                        echo "<p class='text-red-500'>Error loading categories.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Featured Packages (Low-Profile 3D Cards) -->
        <section class="py-24 bg-white relative z-20">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                <div class="flex justify-between items-end mb-16 fade-up">
                    <h2 class="text-4xl md:text-6xl font-serif text-brand-navy leading-tight text-3d-light">Curated <br><i class="font-light text-brand-cyan">Destinations</i></h2>
                    <a href="destinations.php" class="btn-outline !py-3 !px-6">View All <i class="fas fa-arrow-right w-4 h-4 ml-2"></i></a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 md:gap-12 perspective-[1000px]">
                    <?php
                    try {
                        // Fetch 3 featured packages
                        $packagesStmt = $pdo->prepare("
                            SELECT id, title, location, duration, description, price, image_url, tag 
                            FROM packages 
                            WHERE is_active = TRUE 
                            ORDER BY created_at DESC 
                            LIMIT 3
                        ");
                        $packagesStmt->execute();
                        $packages = $packagesStmt->fetchAll();

                        $delayClasses = ['', 'delay-100', 'delay-200'];
                        $marginTopClass = ['', 'lg:mt-12', ''];

                        if (empty($packages)) {
                            echo "<p class='text-brand-navy col-span-full'>No packages available.</p>";
                        } else {
                            foreach ($packages as $idx => $pkg) {
                                $delay = $delayClasses[$idx] ?? '';
                                $marginTop = $marginTopClass[$idx] ?? '';
                                echo "
                                <div class='group cursor-pointer fade-up {$delay} {$marginTop} mt-0 card-premium rounded-[2rem] p-4 bg-brand-sand border border-gray-100'>
                                    <div class='relative h-[25rem] rounded-[1.5rem] overflow-hidden mb-6 shadow-sm inner-3d'>
                                        <img src='" . htmlspecialchars($pkg['image_url']) . "' class='w-full h-full object-cover transition-transform duration-[2s] ease-apple group-hover:scale-105'>
                                        <div class='absolute inset-0 bg-gradient-to-t from-brand-navy/80 to-transparent opacity-90'></div>
                                        <div class='absolute bottom-6 left-6 right-6 inner-3d flex flex-col items-start'>
                                            <div class='flex flex-wrap items-center gap-2 mb-3'>
                                                <span class='bg-brand-cyan/90 backdrop-blur-md border border-white/20 text-white text-[9px] font-bold tracking-[0.2em] uppercase inline-block px-4 py-2 rounded-full'>" . htmlspecialchars($pkg['location']) . "</span>
                                                " . (!empty($pkg['duration']) ? "<span class='bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-[10px] font-medium tracking-wider flex items-center gap-1.5 px-3 py-1.5 rounded-full'><i class='far fa-clock w-3 h-3 text-white/80'></i> " . htmlspecialchars($pkg['duration']) . "</span>" : "") . "
                                            </div>
                                            <h3 class='text-3xl font-serif text-white mb-1 w-full'>" . htmlspecialchars($pkg['title']) . "</h3>
                                        </div>
                                    </div>
                                    <div class='px-3 pb-2 flex justify-between items-center inner-3d'>
                                        <p class='text-gray-600 text-xs leading-relaxed max-w-[55%] font-medium'>" . htmlspecialchars(mb_strimwidth(strip_tags($pkg['description']), 0, 100, '...')) . "</p>
                                        <div class='bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100'>
                                            <span class='block text-[9px] uppercase text-gray-400 font-bold mb-1'>From</span>
                                            <span class='font-black text-lg text-brand-navy'>SAR " . number_format($pkg['price'], 0) . "</span>
                                        </div>
                                    </div>
                                </div>
                                ";
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Packages fetch error: ' . $e->getMessage());
                        echo "<p class='text-red-500 col-span-full'>Error loading packages.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Latest Stories (Home Page) -->
        <section class="py-24 bg-brand-sand relative z-20 border-t border-white/50">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                <div class="flex justify-between items-end mb-16 fade-up">
                    <h2 class="text-4xl md:text-6xl font-serif text-brand-navy leading-tight text-3d-light">Latest <br><i class="font-light text-brand-cyan">Stories</i></h2>
                    <a href="stories.php" class="btn-outline !py-3 !px-6 hidden md:inline-flex">View All <i class="fas fa-arrow-right w-4 h-4 ml-2"></i></a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 md:gap-12 perspective-[1000px]">
                    <?php
                    try {
                        // Fetch 3 latest stories
                        $storiesStmt = $pdo->prepare("
                            SELECT id, title, slug, excerpt, image_url, tag, published_date 
                            FROM stories 
                            WHERE is_published = TRUE 
                            ORDER BY published_date DESC 
                            LIMIT 6
                        ");
                        $storiesStmt->execute();
                        $stories = $storiesStmt->fetchAll();

                        $delayClasses = ['', 'delay-100', 'delay-200'];

                        if (empty($stories)) {
                            echo "<p class='text-brand-navy col-span-full'>No stories available.</p>";
                        } else {
                            foreach ($stories as $idx => $story) {
                                $delay = $delayClasses[$idx] ?? '';
                                $publishedDate = new DateTime($story['published_date']);
                                echo "
                                <article class='group cursor-pointer fade-up {$delay} card-premium bg-white rounded-[2rem] p-5 border border-gray-100 hover:border-brand-cyan transition-colors duration-500'>
                                    <a href='single-post.php?id=" . (int)$story['id'] . "' class='block relative h-64 rounded-[1.5rem] overflow-hidden mb-8 shadow-sm inner-3d group-hover:opacity-90'>
                                        <img src='" . htmlspecialchars($story['image_url']) . "' class='w-full h-full object-cover transition-transform duration-[1.5s] ease-apple group-hover:scale-105'>
                                        <div class='absolute top-4 left-4 bg-brand-cyan text-white px-4 py-2 rounded-xl font-bold text-[9px] tracking-[0.2em] uppercase'>" . htmlspecialchars($story['tag']) . "</div>
                                    </a>
                                    <div class='px-4 inner-3d'>
                                        <div class='flex items-center mb-4'>
                                            <span class='text-gray-400 text-xs font-bold uppercase tracking-[0.15em] bg-brand-sand px-3 py-1.5 rounded-lg border border-white'>" . $publishedDate->format('M d, Y') . "</span>
                                        </div>
                                        <a href='single-post.php?id=" . (int)$story['id'] . "'>
                                            <h3 class='text-2xl font-serif text-brand-navy mb-4 font-bold group-hover:text-brand-cyan transition-colors leading-snug'>" . htmlspecialchars($story['title']) . "</h3>
                                        </a>
                                        <p class='text-gray-500 font-medium leading-relaxed mb-6 text-sm'>" . htmlspecialchars(mb_strimwidth(strip_tags($story['excerpt']), 0, 120, '...')) . "</p>
                                        <a href='single-post.php?id=" . (int)$story['id'] . "' class='btn-outline w-full !py-3 !rounded-xl text-[10px]'>Read Story</a>
                                    </div>
                                </article>
                                ";
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Stories fetch error: ' . $e->getMessage());
                        echo "<p class='text-red-500 col-span-full'>Error loading stories.</p>";
                    }
                    ?>
                </div>
                
                <div class="mt-12 text-center md:hidden fade-up">
                    <a href="stories.php" class="btn-outline w-full !py-3 !px-6">View All Stories <i class="fas fa-arrow-right w-4 h-4 ml-2"></i></a>
                </div>
            </div>
        </section>

        <!-- Split Section (Soft Depth) -->
        <section class="py-24 bg-brand-sand text-brand-navy border-y border-white relative z-30 overflow-hidden">
            <img src="https://monabbor-hossen.github.io/mbh-golden-global/Logo%20png-01.png" class="absolute -right-20 top-1/2 -translate-y-1/2 w-[600px] opacity-[0.03] pointer-events-none z-0" alt="">
            
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center perspective-[1000px]">
                    <div class="fade-up card-premium !bg-transparent !border-none !shadow-none">
                        <div class="inner-3d">
                            <span class="bg-white border border-gray-200 text-brand-cyan text-[10px] font-bold tracking-[0.3em] uppercase mb-6 inline-block px-4 py-2 rounded-full">Our Promise</span>
                            <h2 class="text-4xl md:text-6xl font-serif mb-8 leading-[1.1] text-brand-navy text-3d-light">Travel with <br><i class="font-bold text-brand-cyan">Confidence</i> <br>and Ease.</h2>
                            <p class="text-gray-600 text-lg font-medium leading-relaxed mb-10 max-w-md">
                                We are a trusted travel and tourism service provider dedicated to making your journeys simple, comfortable, and affordable. We specialize in air ticket bookings offering the best routes.
                            </p>
                            <a href="about.php" class="btn-outline">Discover Our Story</a>
                        </div>
                    </div>
                    <div class="relative h-[35rem] rounded-[2.5rem] overflow-hidden fade-up delay-100 shadow-md border-[4px] border-white group card-premium bg-white">
                        <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1000&q=80" class="w-full h-full object-cover transition-transform duration-[3s] ease-apple group-hover:scale-105 inner-3d">
                    </div>
                </div>
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>
