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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            navy: '#003355',
                            cyan: '#0082CA',
                            sand: '#F4F7F9',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-sand text-brand-navy">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-8">
                <h1 class="text-2xl font-serif font-bold">
                    <span class="text-brand-cyan">MBH</span> Admin
                </h1>
                <div class="hidden md:flex gap-6">
                    <a href="index.php" class="text-brand-cyan font-semibold border-b-2 border-brand-cyan pb-1">Dashboard</a>
                    <a href="packages.php" class="hover:text-brand-cyan transition">Packages</a>
                    <a href="stories.php" class="hover:text-brand-cyan transition">Stories</a>
                    <a href="inquiries.php" class="hover:text-brand-cyan transition">Inquiries</a>
                    <a href="settings.php" class="hover:text-brand-cyan transition">Settings</a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($adminName); ?></span>
                <a href="logout.php" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition text-sm font-medium">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-12">
        <!-- Header -->
        <div class="mb-12">
            <h2 class="text-4xl font-serif font-bold mb-2">Welcome Back, <?php echo htmlspecialchars($adminName); ?></h2>
            <p class="text-gray-600">Here's an overview of your website's performance.</p>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Active Packages -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Active Packages</p>
                        <h3 class="text-3xl font-bold text-brand-navy"><?php echo $stats['active_packages']; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
                <a href="packages.php" class="text-brand-cyan text-sm font-medium mt-4 inline-block hover:underline">Manage →</a>
            </div>

            <!-- Total Stories -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Published Stories</p>
                        <h3 class="text-3xl font-bold text-brand-navy"><?php echo $stats['total_stories']; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i data-lucide="pen-tool" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
                <a href="stories.php" class="text-brand-cyan text-sm font-medium mt-4 inline-block hover:underline">Manage →</a>
            </div>

            <!-- Unread Inquiries -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Unread Inquiries</p>
                        <h3 class="text-3xl font-bold text-brand-navy"><?php echo $stats['unread_inquiries']; ?></h3>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i data-lucide="mail" class="w-6 h-6 text-orange-600"></i>
                    </div>
                </div>
                <a href="inquiries.php" class="text-brand-cyan text-sm font-medium mt-4 inline-block hover:underline">View →</a>
            </div>

            <!-- Total Inquiries -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Total Inquiries</p>
                        <h3 class="text-3xl font-bold text-brand-navy"><?php echo $stats['total_inquiries']; ?></h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i data-lucide="inbox" class="w-6 h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Recent Inquiries</h3>
                <a href="inquiries.php" class="text-brand-cyan text-sm font-medium hover:underline">View All →</a>
            </div>

            <?php if (!empty($recentInquiries)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Name</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Subject</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInquiries as $inquiry): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-4 px-4 text-sm"><?php echo htmlspecialchars($inquiry['full_name']); ?></td>
                                    <td class="py-4 px-4 text-sm"><?php echo htmlspecialchars(substr($inquiry['subject'], 0, 40)); ?></td>
                                    <td class="py-4 px-4 text-sm text-gray-600">
                                        <?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?>
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            <?php echo $inquiry['status'] === 'unread' ? 'bg-red-100 text-red-700' : ($inquiry['status'] === 'read' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'); ?>
                                        ">
                                            <?php echo ucfirst($inquiry['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        <a href="inquiries.php?id=<?php echo $inquiry['id']; ?>" class="text-brand-cyan hover:underline">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No inquiries yet</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
