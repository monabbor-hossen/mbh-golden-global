<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

        <!-- ========================================== -->
        <!-- DESTINATIONS PAGE (Packages)               -->
        <!-- ========================================== -->
        <header class="pt-48 pb-20 bg-white border-b border-gray-100">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 fade-up perspective-[1000px]">
                <div class="inner-3d">
                    <span class="text-brand-cyan text-xs font-bold tracking-[0.3em] uppercase mb-6 block text-3d-light">Tour Packages</span>
                    <h1 class="text-5xl md:text-8xl font-serif text-brand-navy mb-8 leading-tight text-3d-light">Explore <i class="font-bold text-brand-cyan">Destinations</i></h1>
                    <p class="text-xl md:text-2xl text-gray-500 font-medium max-w-2xl leading-relaxed">Carefully planned packages designed for individuals, families, and groups.</p>
                </div>
            </div>
        </header>

        <section class="py-24 bg-brand-sand min-h-screen relative z-10">
            <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 md:gap-12 perspective-[2000px]">
                    <?php
                    try {
                        // Fetch all active packages from database
                        $packagesStmt = $pdo->prepare("
                            SELECT id, title, slug, location, duration, description, price, image_url, tag 
                            FROM packages 
                            WHERE is_active = TRUE 
                            ORDER BY created_at DESC
                        ");
                        $packagesStmt->execute();
                        $packages = $packagesStmt->fetchAll();

                        if (empty($packages)) {
                            echo "<p class='text-brand-navy col-span-full text-lg'>No packages available at the moment. Please check back soon!</p>";
                        } else {
                            $delay = 0;
                            foreach ($packages as $pkg) {
                                $delayClass = $delay> 0 ? 'delay-' . ($delay * 100) : '';
                                echo "
                                <a href='tour/" . htmlspecialchars($pkg['slug']) . "' class='block group cursor-pointer fade-up {$delayClass} card-premium rounded-[2rem] bg-white p-4 hover:border-brand-cyan transition-colors duration-500 border border-gray-100'>
                                    <div class='relative h-[24rem] rounded-[1.5rem] overflow-hidden mb-6 shadow-sm inner-3d'>
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
                                        <div class='bg-brand-sand px-5 py-3 rounded-2xl border border-white'>
                                            <span class='block text-[9px] uppercase text-gray-400 font-bold mb-1'>From</span>
                                            <span class='font-black text-lg text-brand-navy'>SAR " . number_format($pkg['price'], 0) . "</span>
                                        </div>
                                    </div>
                                </a>
                                ";
                                $delay++;
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Packages fetch error: ' . $e->getMessage());
                        echo "<p class='text-red-500 col-span-full'>Error loading packages. Please try again later.</p>";
                    }
                    ?>
                </div>
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>
