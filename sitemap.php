<?php
require_once 'includes/db.php';

header("Content-Type: application/xml; charset=utf-8");

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . '/mbh-golden-global';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
echo "\n";

// Static Pages
$staticPages = ['index.php', 'about.php', 'destinations.php', 'stories.php', 'contact.php'];
foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . '/' . ($page === 'index.php' ? '' : $page)) . "</loc>\n";
    echo "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    echo "    <changefreq>" . ($page === 'index.php' ? 'daily' : 'weekly') . "</changefreq>\n";
    echo "    <priority>" . ($page === 'index.php' ? '1.0' : '0.8') . "</priority>\n";
    echo "  </url>\n";
}

// Packages
try {
    $pkgStmt = $pdo->prepare("SELECT slug, created_at FROM packages WHERE is_active = 1");
    $pkgStmt->execute();
    while ($pkg = $pkgStmt->fetch()) {
        $lastmod = date('Y-m-d', strtotime($pkg['created_at']));
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($baseUrl . '/tour/' . $pkg['slug']) . "</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";
    }
} catch (PDOException $e) {
    error_log('Sitemap packages error: ' . $e->getMessage());
}

// Stories
try {
    $storyStmt = $pdo->prepare("SELECT slug, published_date FROM stories WHERE is_published = 1");
    $storyStmt->execute();
    while ($story = $storyStmt->fetch()) {
        $lastmod = date('Y-m-d', strtotime($story['published_date']));
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($baseUrl . '/story/' . $story['slug']) . "</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.7</priority>\n";
        echo "  </url>\n";
    }
} catch (PDOException $e) {
    error_log('Sitemap stories error: ' . $e->getMessage());
}

echo "</urlset>\n";
?>
