<?php
/**
 * Admin Dashboard
 * 
 * Main dashboard showing website statistics and recent activity
 */

require_once '../includes/db.php';
require_once 'includes/auth.php';

// Fetch statistics
$stats = [
    'active_packages' => 0,
    'total_stories' => 0,
    'unread_inquiries' => 0,
    'total_inquiries' => 0,
];

$recentInquiries = [];

try {
    // Active packages
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM packages WHERE is_active = TRUE");
    $stmt->execute();
    $stats['active_packages'] = $stmt->fetch()['count'];

    // Total stories
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM stories WHERE is_published = TRUE");
    $stmt->execute();
    $stats['total_stories'] = $stmt->fetch()['count'];

    // Unread inquiries
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM inquiries WHERE status = 'unread'");
    $stmt->execute();
    $stats['unread_inquiries'] = $stmt->fetch()['count'];

    // Total inquiries
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM inquiries");
    $stmt->execute();
    $stats['total_inquiries'] = $stmt->fetch()['count'];

    // Recent inquiries (last 5)
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, subject, status, created_at 
        FROM inquiries 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentInquiries = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MBH Golden Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: {
                            navy: '#003355',
                            cyan: '#0082CA',
                            sand: '#F4F7F9',
                        }
                    },
                    animation: {
                        'float': 'floatOrb 12s infinite ease-in-out alternate',
                        'float-delayed': 'floatOrb 15s infinite ease-in-out alternate-reverse',
                    },
                    keyframes: {
                        floatOrb: {
                            '0%': { transform: 'translateY(0) scale(1)' },
                            '100%': { transform: 'translateY(-20px) scale(1.05)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#003355] text-white overflow-x-hidden min-h-screen relative">

    <!-- Ambient Background Orbs -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute rounded-full w-[30rem] h-[30rem] top-[-10%] -left-20 animate-float mix-blend-screen opacity-20" style="background: radial-gradient(circle at center, rgba(0,130,202,0.5), transparent 70%);"></div>
        <div class="absolute rounded-full w-[45rem] h-[45rem] bottom-[-20%] -right-20 animate-float-delayed mix-blend-screen opacity-20" style="background: radial-gradient(circle at center, rgba(255,255,255,0.4), transparent 70%);"></div>
        <div class="absolute inset-0 bg-[url('../assets/img/bg1.avif')] bg-cover bg-center opacity-[0.15] mix-blend-overlay"></div>
    </div>

    <div class="flex flex-col min-h-screen relative z-10">
        <?php require_once 'includes/sidebar.php'; ?>

        
        <!-- Mobile Top Bar -->
        <div class="md:hidden flex items-center justify-between p-6 bg-white/5 backdrop-blur-xl border-b border-white/10 sticky top-0 z-30 w-full">
            <h1 class="text-xl font-serif font-bold text-white"><span class="text-brand-cyan">MBH</span> Admin</h1>
            <button id="hamburger-btn" class="text-white hover:text-brand-cyan transition-colors">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Main Content -->
        <main class="w-full flex-1 md:pl-80 p-6 md:py-8 md:pr-8 min-h-screen flex flex-col">
            <!-- Top Header -->
            <header class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-4 md:p-6 flex flex-wrap gap-4 justify-between items-center mb-6 md:mb-8 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
                <div>
                    <h2 class="text-2xl md:text-3xl lg:text-4xl font-serif text-white">Welcome Back, <span class="font-light text-brand-cyan"><?php echo htmlspecialchars($adminName ?? 'Admin'); ?></span></h2>
                    <p class="text-white/60 mt-1 text-sm tracking-wide">Here's an overview of your website's performance.</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="logout.php" class="flex items-center gap-2 px-5 py-2.5 bg-white/5 hover:bg-red-500/20 border border-white/10 hover:border-red-500/50 text-white/80 hover:text-red-400 hover:shadow-[0_0_15px_rgba(239,68,68,0.2)] rounded-xl transition-all font-medium text-sm">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </a>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                <!-- Active Packages -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:-translate-y-1 hover:border-white/20 transition-all shadow-[0_4px_30px_rgba(0,0,0,0.1)] group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-cyan-500/20 flex items-center justify-center border border-cyan-500/30 group-hover:shadow-[0_0_15px_rgba(0,130,202,0.3)] transition-all">
                            <i data-lucide="package" class="w-6 h-6 text-cyan-400"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white mb-1"><?php echo $stats['active_packages'] ?? 0; ?></h3>
                        <p class="text-white/50 text-sm">Active Packages</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <a href="packages.php" class="text-xs font-bold uppercase tracking-wider text-white/50 hover:text-brand-cyan transition-colors flex items-center gap-1 group-hover:translate-x-1">Manage <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                    </div>
                </div>

                <!-- Total Stories -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:-translate-y-1 hover:border-white/20 transition-all shadow-[0_4px_30px_rgba(0,0,0,0.1)] group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center border border-green-500/30 group-hover:shadow-[0_0_15px_rgba(34,197,94,0.3)] transition-all">
                            <i data-lucide="pen-tool" class="w-6 h-6 text-green-400"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white mb-1"><?php echo $stats['total_stories'] ?? 0; ?></h3>
                        <p class="text-white/50 text-sm">Published Stories</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <a href="stories.php" class="text-xs font-bold uppercase tracking-wider text-white/50 hover:text-green-400 transition-colors flex items-center gap-1 group-hover:translate-x-1">Manage <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                    </div>
                </div>

                <!-- Unread Inquiries -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:-translate-y-1 hover:border-white/20 transition-all shadow-[0_4px_30px_rgba(0,0,0,0.1)] group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-orange-500/20 flex items-center justify-center border border-orange-500/30 group-hover:shadow-[0_0_15px_rgba(249,115,22,0.3)] transition-all">
                            <i data-lucide="mail" class="w-6 h-6 text-orange-400"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white mb-1"><?php echo $stats['unread_inquiries'] ?? 0; ?></h3>
                        <p class="text-white/50 text-sm">Unread Inquiries</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <a href="inquiries.php" class="text-xs font-bold uppercase tracking-wider text-white/50 hover:text-orange-400 transition-colors flex items-center gap-1 group-hover:translate-x-1">View <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                    </div>
                </div>

                <!-- Total Inquiries -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:-translate-y-1 hover:border-white/20 transition-all shadow-[0_4px_30px_rgba(0,0,0,0.1)] group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center border border-purple-500/30 group-hover:shadow-[0_0_15px_rgba(168,85,247,0.3)] transition-all">
                            <i data-lucide="inbox" class="w-6 h-6 text-purple-400"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white mb-1"><?php echo $stats['total_inquiries'] ?? 0; ?></h3>
                        <p class="text-white/50 text-sm">Total Inquiries</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <a href="inquiries.php" class="text-xs font-bold uppercase tracking-wider text-white/50 hover:text-purple-400 transition-colors flex items-center gap-1 group-hover:translate-x-1">View All <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                    </div>
                </div>
            </div>

            <!-- Recent Inquiries -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-[0_4px_30px_rgba(0,0,0,0.1)] flex-1">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-serif font-bold text-white flex items-center gap-2">
                        <i data-lucide="clock" class="w-5 h-5 text-brand-cyan"></i>
                        Recent Inquiries
                    </h3>
                    <a href="inquiries.php" class="text-xs font-bold uppercase tracking-wider text-brand-cyan hover:text-white transition-colors flex items-center gap-1">View All <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
                </div>

                <?php if (!empty($recentInquiries)): ?>
                    <div class="overflow-x-auto rounded-xl">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr>
                                    <th class="border-b border-white/10 py-4 px-4 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Name</th>
                                    <th class="border-b border-white/10 py-4 px-4 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Subject</th>
                                    <th class="border-b border-white/10 py-4 px-4 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Date</th>
                                    <th class="border-b border-white/10 py-4 px-4 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Status</th>
                                    <th class="border-b border-white/10 py-4 px-4 text-white/50 text-xs tracking-wider uppercase font-semibold whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInquiries as $inquiry): ?>
                                    <tr class="hover:bg-white/5 transition-colors group">
                                        <td class="py-4 px-4 text-sm text-white/90 border-b border-white/5 group-last:border-none whitespace-nowrap"><?php echo htmlspecialchars($inquiry['full_name']); ?></td>
                                        <td class="py-4 px-4 text-sm text-white/70 border-b border-white/5 group-last:border-none whitespace-nowrap"><?php echo htmlspecialchars(substr($inquiry['subject'], 0, 40)); ?></td>
                                        <td class="py-4 px-4 text-sm text-white/50 border-b border-white/5 group-last:border-none whitespace-nowrap">
                                            <?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?>
                                        </td>
                                        <td class="py-4 px-4 text-sm border-b border-white/5 group-last:border-none whitespace-nowrap">
                                            <?php if ($inquiry['status'] === 'unread'): ?>
                                                <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-red-500/20 border border-red-500/50 text-red-300 shadow-[0_0_10px_rgba(239,68,68,0.2)]">Unread</span>
                                            <?php elseif ($inquiry['status'] === 'read'): ?>
                                                <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 shadow-[0_0_10px_rgba(234,179,8,0.2)]">Read</span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold bg-green-500/20 border border-green-500/50 text-green-300 shadow-[0_0_10px_rgba(34,197,94,0.2)]">Replied</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 text-sm border-b border-white/5 group-last:border-none whitespace-nowrap">
                                            <a href="inquiries.php?id=<?php echo $inquiry['id']; ?>" class="text-brand-cyan hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg inline-block">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 px-4 border border-white/5 rounded-xl bg-white/5">
                        <i data-lucide="inbox" class="w-12 h-12 text-white/20 mx-auto mb-3"></i>
                        <p class="text-white/50 text-sm">No inquiries found</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
