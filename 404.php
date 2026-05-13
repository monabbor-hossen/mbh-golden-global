<?php
http_response_code(404);
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<main class="relative min-h-screen flex items-center justify-center bg-brand-navy overflow-hidden">
    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="orb-3d w-96 h-96 top-[20%] -left-20 animate-float" style="background: radial-gradient(circle at 30% 30%, rgba(0,130,202,0.4), transparent); opacity: 0.6;"></div>
        <div class="orb-3d w-[40rem] h-[40rem] bottom-[-10%] -right-20 animate-float-delayed" style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.1), transparent); opacity: 0.3;"></div>
        
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920&q=80')] bg-cover bg-center opacity-20 mix-blend-overlay"></div>
    </div>

    <div class="relative z-10 w-full max-w-4xl px-6 fade-up perspective-[1000px]">
        <div class="card-premium !bg-white/10 !backdrop-blur-2xl rounded-[3rem] p-10 md:p-20 text-center border border-white/20 shadow-[0_20px_50px_rgba(0,0,0,0.3)]">
            <div class="inner-3d">
                
                <h1 class="text-[8rem] md:text-[12rem] font-serif font-black leading-none text-transparent bg-clip-text bg-gradient-to-b from-white to-brand-cyan/40 drop-shadow-[0_0_30px_rgba(0,130,202,0.4)] mb-4">
                    404
                </h1>
                
                <span class="inline-block px-5 py-2 rounded-full border border-white/30 bg-white/5 text-white text-[9px] font-bold tracking-[0.3em] uppercase mb-8 shadow-sm">
                    Destination Unknown
                </span>

                <h2 class="text-3xl md:text-5xl font-serif text-white mb-6 leading-tight text-3d-dark">
                    Looks like you've wandered <br><i class="font-light text-brand-cyan">off the itinerary.</i>
                </h2>

                <p class="text-gray-300 text-lg md:text-xl font-medium mb-12 max-w-2xl mx-auto leading-relaxed">
                    The flight to this page has been canceled. Let's get your journey back on track and discover some real destinations.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-8">
                    <a href="index.php" class="btn-primary !px-10 !py-4 shadow-[0_0_20px_rgba(0,130,202,0.4)] hover:shadow-[0_0_30px_rgba(0,130,202,0.6)]">
                        <i data-lucide="home" class="w-4 h-4 mr-2"></i> Return to Home
                    </a>
                    
                    <a href="destinations.php" class="text-white hover:text-brand-cyan font-bold text-xs tracking-[0.15em] uppercase transition-colors flex items-center gap-2 drop-shadow-md">
                        Explore Packages <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>