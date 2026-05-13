<?php
http_response_code(404);
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

        <section class="relative min-h-screen flex items-center justify-center bg-brand-navy overflow-hidden pt-32 pb-20 px-6 perspective-[1000px]">
            
            <div class="absolute inset-0 pointer-events-none z-0">
                <div class="orb-3d w-96 h-96 top-[10%] -left-20 animate-float" style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.05), rgba(0, 130, 202, 0.15), transparent);"></div>
                <div class="orb-3d w-[45rem] h-[45rem] bottom-[-10%] -right-20 animate-float-delayed" style="background: radial-gradient(circle at 30% 30%, rgba(0,130,202,0.1), rgba(0,51,85,0.3), transparent);"></div>
            </div>

            <div class="relative z-10 max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-0 items-center card-premium !bg-white/5 !border-white/10 backdrop-blur-2xl rounded-[3rem] p-3 shadow-2xl fade-up">
                
                <div class="p-10 md:p-16 inner-3d">
                    <span class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-brand-cyan/30 bg-brand-cyan/10 text-brand-cyan text-[10px] font-bold tracking-[0.3em] uppercase mb-8">
                        <span class="w-2 h-2 rounded-full bg-brand-cyan animate-pulse shadow-glow"></span>
                        Flight 404
                    </span>
                    
                    <div class="relative mb-8">
                        <h1 class="text-[8rem] md:text-[12rem] font-serif font-black text-white/5 leading-none absolute -top-16 md:-top-24 -left-4 select-none pointer-events-none">404</h1>
                        
                        <h2 class="relative text-5xl md:text-6xl font-serif text-white leading-[1.1]">
                            Lost in the <br><i class="font-light text-brand-cyan">clouds?</i>
                        </h2>
                    </div>
                    
                    <p class="text-gray-300 text-lg font-medium leading-relaxed mb-10 max-w-md relative z-10">
                        The destination you are looking for seems to have drifted off our radar. It might have been moved, deleted, or perhaps it never existed.
                    </p>
                    
                    <div class="flex flex-wrap gap-4 relative z-10">
                        <a href="index.php" class="btn-primary !px-8 !py-4">
                            <i data-lucide="compass" class="w-4 h-4 mr-2"></i> Return Home
                        </a>
                        <a href="destinations.php" class="inline-flex items-center justify-center px-8 py-4 rounded-full border border-white/20 bg-transparent text-white text-xs font-bold tracking-[0.15em] uppercase transition-all duration-300 hover:bg-white/10 hover:border-white/40">
                            View Destinations
                        </a>
                    </div>
                </div>

                <div class="hidden lg:block relative h-full min-h-[600px] rounded-[2.5rem] overflow-hidden shadow-2xl inner-3d border border-white/10 group">
                    <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1000&q=80" alt="Clouds out the window" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-[3s] ease-apple">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-navy/90 via-brand-navy/20 to-transparent"></div>
                    <div class="absolute inset-0 shadow-[inset_0_0_50px_rgba(0,0,0,0.5)]"></div>
                    
                    <div class="absolute bottom-10 left-10">
                        <p class="text-brand-cyan text-[10px] tracking-[0.2em] uppercase font-bold mb-2 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-3 h-3"></i> Current Location
                        </p>
                        <p class="text-white font-serif text-3xl">Unknown Territory</p>
                    </div>
                </div>
                
            </div>
        </section>

<?php
require_once 'includes/footer.php';
?>