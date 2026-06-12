<?php
// sitemap.php - Dynamic sitemap generator
header('Content-Type: application/xml; charset=utf-8');

require_once __DIR__ . '/php/db.php';

// Base URL
$base_url = 'https://www.thekconsult.co.za';

// Static pages
$pages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/marketing.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/it.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/system-integration.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/ecommerce-optimization.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/conversion-optimization.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/lead-generation.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/free-website-audit.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/portfolio.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/blog.html', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/about.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/consultation.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/contact.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/privacy-policy.html', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/cookie.html', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($pages as $page): ?>
    <url>
        <loc><?php echo $base_url . $page['loc']; ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq><?php echo $page['changefreq']; ?></changefreq>
        <priority><?php echo $page['priority']; ?></priority>
    </url>
    <?php endforeach; ?>
    <?php
    // Published blog posts
    try {
        $conn = getDbConnection();
        $result = $conn->query("SELECT slug, published_at FROM blog_posts WHERE status = 'published' ORDER BY id DESC");
        while ($result && $row = $result->fetch_assoc()) {
            ?>
    <url>
        <loc><?php echo $base_url . '/blog-post.html?slug=' . urlencode($row['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($row['published_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
            <?php
        }
        $conn->close();
    } catch (Exception $e) {
        // Silently skip blog entries if the database is unavailable
    }
    ?>
</urlset>
