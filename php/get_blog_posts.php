<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

try {
    $conn = getDbConnection();

    $conn->query("CREATE TABLE IF NOT EXISTS blog_posts (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        title          VARCHAR(255) NOT NULL                    COMMENT 'Post title shown on cards and the post page',
        slug           VARCHAR(255) NOT NULL UNIQUE             COMMENT 'URL-friendly identifier used in the page link (e.g. how-to-grow-business)',
        excerpt        TEXT                                     COMMENT 'Short summary shown on listing cards and previews',
        content        LONGTEXT                                 COMMENT 'Full post content — paragraphs separated by a blank line',
        featured_image VARCHAR(500) DEFAULT NULL                COMMENT 'URL or relative path to the cover image',
        author         VARCHAR(100) DEFAULT 'KConsulting Team' COMMENT 'Author name displayed on the post',
        category       VARCHAR(100) DEFAULT NULL                COMMENT 'Main category: Marketing, IT, Growth, Systems',
        tags           VARCHAR(500) DEFAULT NULL                COMMENT 'Comma-separated tags (e.g. SEO, Leads, Strategy)',
        read_time      INT          DEFAULT 5                   COMMENT 'Estimated reading time in minutes',
        is_featured    TINYINT(1)   DEFAULT 0                   COMMENT '1 = shown as a featured or highlighted post',
        status         VARCHAR(20)  DEFAULT 'published'         COMMENT 'Post visibility: published or draft',
        views          INT          DEFAULT 0                   COMMENT 'Total view count',
        published_at   DATETIME     DEFAULT CURRENT_TIMESTAMP   COMMENT 'Date the post went live',
        created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ── Single post by slug ────────────────────────────────────────────────
    if (!empty($_GET['slug'])) {
        $slug = trim($_GET['slug']);
        $stmt = $conn->prepare(
            "SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1"
        );
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($post) {
            $post['tags_array'] = $post['tags']
                ? array_values(array_filter(array_map('trim', explode(',', $post['tags']))))
                : [];
            // Increment view count in DB and return the updated value
            $conn->query("UPDATE blog_posts SET views = views + 1 WHERE id = {$post['id']}");
            $post['views'] = (int)$post['views'] + 1;
        }

        $conn->close();
        echo json_encode(['success' => (bool)$post, 'post' => $post]);
        exit;
    }

    // ── Post listing ───────────────────────────────────────────────────────
    $limit    = min((int)($_GET['limit']    ?? 50), 50);
    $category = trim($_GET['category'] ?? '');
    $featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;

    $where  = ["status = 'published'"];
    $params = [];
    $types  = '';

    if ($category !== '' && strtolower($category) !== 'all') {
        $where[]  = 'LOWER(category) = LOWER(?)';
        $params[] = $category;
        $types   .= 's';
    }
    if ($featured !== null) {
        $where[]  = 'is_featured = ?';
        $params[] = $featured;
        $types   .= 'i';
    }

    $params[] = $limit;
    $types   .= 'i';

    $sql  = "SELECT id, title, slug, excerpt, featured_image, author,
                    category, tags, read_time, is_featured, views, published_at
             FROM blog_posts
             WHERE " . implode(' AND ', $where) . "
             ORDER BY is_featured DESC, published_at DESC
             LIMIT ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $row['tags_array'] = $row['tags']
            ? array_values(array_filter(array_map('trim', explode(',', $row['tags']))))
            : [];
        $posts[] = $row;
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'posts' => $posts, 'total' => count($posts)]);

} catch (Exception $e) {
    error_log('Blog posts error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'posts' => [], 'total' => 0]);
}
