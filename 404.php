<?php
http_response_code(404);
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

        <section class="relative min-h-screen w-full flex items-center justify-center overflow-hidden bg-brand-navy perspective-[1000px] pt-20">
            
            <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
                <div class="orb-3d w-96 h-96 top-[20%] -left-10 animate-float"></div>
                <div class="orb-3d w-[40rem] h-[40rem] top-[50%] -right-20 animate-float-delayed" style="background: radial-gradient(circle at 30% 30%, rgba(0,130,202,0.4), rgba(0,51,85,0.1), transparent);"></div>
            </div>

            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920&q=80" alt="Lost in the clouds" class="w-full h-full object-cover opacity-30 transform scale-105">
                <div class="absolute inset-0 bg-gradient-to-b from-brand-navy/95 via-brand-navy/60 to-brand-navy/95 mix-blend-multiply"></div>
            </div>

            <div class="relative z-10 w-full max-w-4xl px-6 fade-up my-12">
                <div class="card-premium !bg-white/10 !backdrop-blur-2xl border border-white/20 p-10 md:p-16 rounded-[3rem] text-center overflow-hidden relative shadow-glass-3d">
                    
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[12rem] md:text-[22rem] font-black text-white/5 select-none pointer-events-none font-sans tracking-tighter">
                        404
                    </div>

                    <div class="relative z-10 inner-3d">
                        <span class="inline-flex items-center gap-2 text-brand-cyan text-[10px] md:text-xs font-bold tracking-[0.3em] uppercase mb-8 px-5 py-2.5 bg-brand-navy/50 backdrop-blur-md rounded-full border border-brand-cyan/30 shadow-glow">
                            <i data-lucide="map-pin-off" class="w-4 h-4"></i> Destination Unknown
                        </span>
                        
                        <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 leading-[1.1] text-3d-dark tracking-tight">
                            You've wandered <br><i class="font-light text-brand-cyan">off the map.</i>
                        </h1>
                        
                        <p class="text-white/80 text-base md:text-lg font-medium leading-relaxed mb-10 max-w-xl mx-auto drop-shadow-sm">
                            The journey you're looking for doesn't exist, but a world of extraordinary destinations still awaits. Let's get you back on track.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 perspective-[500px]">
                            <a href="index.php" class="btn-primary !px-10 !py-4 w-full sm:w-auto" style="transform: translateZ(10px);">
                                Return to Base
                            </a>
                            <a href="destinations.php" class="btn-outline !bg-transparent !border-white/30 !text-white hover:!bg-white/10 !px-10 !py-4 w-full sm:w-auto" style="transform: translateZ(10px);">
                                Explore Packages
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        </section>

<?php
require_once 'includes/footer.php';
?>