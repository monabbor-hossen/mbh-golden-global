<?php
$current_page = basename($_SERVER['PHP_SELF']);
function get_nav_class($page, $current_page) {
    if ($page === $current_page) {
        return "flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-brand-cyan/20 to-transparent border border-brand-cyan/30 text-white shadow-[0_0_15px_rgba(0,130,202,0.2)] transition-all";
    }
    return "flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/10 hover:translate-x-2 transition-all";
}
function get_icon_class($page, $current_page) {
    if ($page === $current_page) {
        return "w-5 h-5 text-brand-cyan";
    }
    return "w-5 h-5";
}
?>
<div id="sidebar-overlay" class="fixed inset-0 bg-brand-bg/80 backdrop-blur-sm z-40 hidden transition-opacity md:hidden"></div>

<aside id="mobile-sidebar" class="fixed z-50 w-64 inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:top-6 md:left-6 md:bottom-6 md:rounded-3xl bg-white/10 backdrop-blur-2xl border border-white/20 shadow-[0_0_30px_rgba(0,130,202,0.15)] flex flex-col">
    <!-- Logo -->
    <div class="p-8 pb-6 border-b border-white/10 text-center">
        <img src="../assets/img/logo.png" alt="MBH" class="h-12 object-contain mx-auto brightness-0 invert drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] mb-2">
        <h1 class="text-xl font-serif text-white tracking-widest uppercase text-xs opacity-80">
            Admin
        </h1>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 flex flex-col gap-2">
        <a href="index.php" class="<?php echo get_nav_class('index.php', $current_page); ?>">
            <i data-lucide="layout-dashboard" class="<?php echo get_icon_class('index.php', $current_page); ?>"></i>
            <span class="font-medium text-sm">Dashboard</span>
        </a>
        <a href="packages.php" class="<?php echo get_nav_class('packages.php', $current_page); ?>">
            <i data-lucide="package" class="<?php echo get_icon_class('packages.php', $current_page); ?>"></i>
            <span class="font-medium text-sm">Packages</span>
        </a>
        <a href="stories.php" class="<?php echo get_nav_class('stories.php', $current_page); ?>">
            <i data-lucide="pen-tool" class="<?php echo get_icon_class('stories.php', $current_page); ?>"></i>
            <span class="font-medium text-sm">Stories</span>
        </a>
        <a href="inquiries.php" class="<?php echo get_nav_class('inquiries.php', $current_page); ?>">
            <i data-lucide="mail" class="<?php echo get_icon_class('inquiries.php', $current_page); ?>"></i>
            <span class="font-medium text-sm">Inquiries</span>
        </a>
        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin'): ?>
        <a href="admins.php" class="<?php echo get_nav_class('admins.php', $current_page); ?>">
            <i data-lucide="users" class="<?php echo get_icon_class('admins.php', $current_page); ?>"></i>
            <span class="font-medium text-sm">Admins</span>
        </a>
        <a href="settings.php" class="<?php echo get_nav_class('settings.php', $current_page); ?>">
            <i data-lucide="settings" class="<?php echo get_icon_class('settings.php', $current_page); ?>"></i>
            <span class="font-medium text-sm">Settings</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Pinned Logout Footer -->
    <div class="p-4 border-t border-white/10">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-white/70 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all group">
            <i data-lucide="log-out" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
            <span class="font-medium text-sm">Logout</span>
        </a>
    </div>
</aside>
