<?php
<?php
// We set the correct HTTP header so the browser knows it's a real 404 error
http_response_code(404);
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

        <!-- ========================================== -->
        <!-- 404 ERROR PAGE                              -->
        <!-- ========================================== -->
        <section class="relative min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920&q=80');">
            <!-- Dark overlay for readability -->
            <div class="absolute inset-0 bg-brand-deep/80"></div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col items-center justify-center min-h-screen px-6 text-center">
                <div class="max-w-4xl fade-up">
                    <!-- Glowing cyan sub-heading -->
                    <span class="inline-block text-brand-cyan text-sm font-bold tracking-[0.3em] uppercase mb-8 px-6 py-2 bg-white/10 backdrop-blur-sm rounded-full border border-brand-cyan/30 shadow-glow">
                        ERROR 404
                    </span>
                    
                    <!-- Massive serif main heading -->
                    <h1 class="text-6xl md:text-8xl lg:text-[10rem] font-serif text-white mb-8 leading-tight" style="text-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                        Looks like you've <br><i class="font-light text-brand-cyan">wandered off</i> the map.
                    </h1>
                    
                    <!-- Friendly paragraph -->
                    <p class="text-white/90 text-lg md:text-xl font-sans leading-relaxed mb-12 max-w-2xl">
                        The destination you are looking for has been moved, deleted, or simply doesn't exist. Let's get your journey back on track.
                    </p>
                    
                    <!-- Return to Home button -->
                    <a href="index.php" class="btn-primary !px-10 !py-4 text-lg">
                        Return to Home
                    </a>
                </div>
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>