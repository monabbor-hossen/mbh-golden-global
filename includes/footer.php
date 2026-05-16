<?php
// Fetch all settings
$settings = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (PDOException $e) {
    // Silently handle error
}

$social_links = [];
if (!empty($settings['social_links'])) {
    $decoded = json_decode($settings['social_links'], true);
    if (is_array($decoded)) {
        $social_links = $decoded;
    }
}
?>
</main>

<!-- Light Theme Footer (Low-Profile 3D) -->
<footer class="bg-brand-sand pt-24 pb-12 text-brand-navy border-t-[4px] border-brand-cyan relative overflow-hidden">
    <img src="./assets/img/logo.png"
        class="absolute -left-20 -bottom-10 w-[500px] opacity-[0.03] pointer-events-none z-0" alt="">

    <div class="max-w-[90rem] mx-auto px-6 sm:px-8 lg:px-12 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 mb-16 perspective-[1000px]">

            <!-- Brand Info -->
            <div class="lg:col-span-5 pr-8 card-premium !bg-transparent !border-none !shadow-none">
                <div class="mb-8 inner-3d">
                    <img src="./assets/img/logo.png" alt="MBH Golden Global" class="h-24 md:h-32 object-contain"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="hidden font-serif font-bold text-3xl tracking-wide text-brand-navy text-3d-light">
                        <span class="text-brand-cyan">MBH</span> GLOBAL
                    </div>
                </div>
                <p class="text-gray-500 mb-8 max-w-md font-medium leading-relaxed text-base inner-3d">
                    Our goal is to make travel accessible and stress-free for everyone. Travel with confidence and ease
                    with us.
                </p>
                <div class="flex space-x-4 inner-3d">
                    <?php foreach ($social_links as $link): ?>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"
                            class="btn-outline !w-12 !h-12 !p-0 !rounded-xl flex items-center justify-center text-brand-cyan hover:text-white"
                            title="<?php echo htmlspecialchars($link['platform']); ?>">
                            <i class="<?php echo htmlspecialchars($link['icon']); ?> text-xl hover:text-brand-cyan transition-colors"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="lg:col-span-3 lg:col-start-7 fade-up delay-100">
                <h4 class="text-brand-cyan text-[10px] font-black tracking-[0.2em] uppercase mb-8 text-3d-light">
                    Discover</h4>
                <ul class="space-y-4 font-bold text-sm">
                    <li><a href="index" class="hover:text-brand-cyan hover:translate-x-1 transition-all">Home</a>
                    </li>
                    <li><a href="about" class="hover:text-brand-cyan hover:translate-x-1 transition-all">About Us</a></li>
                    <li><a href="destinations"
                            class="hover:text-brand-cyan hover:translate-x-1 transition-all">Destinations</a></li>
                    <li><a href="stories" class="hover:text-brand-cyan hover:translate-x-1 transition-all">Travel
                            Stories</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="lg:col-span-3 fade-up delay-200">
                <h4 class="text-brand-cyan text-[10px] font-black tracking-[0.2em] uppercase mb-8 text-3d-light">Contact
                </h4>
                <ul class="space-y-6 font-bold text-sm">
                    <li class="flex items-center gap-4">
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100"><i class="fas fa-phone w-4 h-4 text-brand-cyan"></i></div>
                        <?php echo htmlspecialchars($settings['phone_1'] ?? '+966 536 785 506'); ?>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100"><i class="fas fa-envelope w-4 h-4 text-brand-cyan"></i></div>
                        <?php echo htmlspecialchars($settings['email_1'] ?? 'mbhgoldenglobal@gmail.com'); ?>
                    </li>
                    <li class="pt-2 text-gray-500 flex items-start gap-4">
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100"><i class="fas fa-map-marker-alt w-4 h-4 text-brand-cyan"></i></div> <span
                            class="text-brand-navy"><?php echo nl2br(htmlspecialchars($settings['address'] ?? 'Buraydah, Al-Qassim, Saudi Arabia')); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-gray-200 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold tracking-[0.15em] uppercase text-gray-400">
            <p>&copy;
                <?php echo date('Y'); ?> MBH Golden Global.
            </p>
            <div class="flex space-x-8">
                <a href="#" class="hover:text-brand-cyan transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-brand-cyan transition-colors">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="assets/js/main.js"></script>
</body>

</html>