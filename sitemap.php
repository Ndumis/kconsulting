<?php
// sitemap.php - Dynamic sitemap generator
header('Content-Type: application/xml; charset=utf-8');

// Database connection for dynamic content (if you add blog later)
$db_host = 'localhost';
$db_name = 'kconsulting';
$db_user = 'root';
$db_pass = '';

// Base URL
$base_url = 'https://www.thekconsult.co.za';

// Static pages
$pages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/about.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/marketing.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/it.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/consultation.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/contact.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/privacy-policy.html', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/cookie.html', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/system-integration.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/ecommerce-optimization.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/conversion-optimization.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/lead-generation.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
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
    // If you have blog posts or case studies later, query them here
    /*
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if (!$conn->connect_error) {
            $result = $conn->query("SELECT id, title, updated_at FROM blog_posts WHERE status='published'");
            while ($row = $result->fetch_assoc()) {
                $slug = strtolower(str_replace(' ', '-', $row['title']));
                ?>
                <url>
                    <loc><?php echo $base_url; ?>/blog/<?php echo $slug; ?>.html</loc>
                    <lastmod><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></lastmod>
                    <changefreq>monthly</changefreq>
                    <priority>0.6</priority>
                </url>
                <?php
            }
            $conn->close();
        }
    } catch (Exception $e) {
        // Silently fail - just don't include dynamic content
    }
    */
    ?>
</urlset>