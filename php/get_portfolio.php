<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

try {
    $conn = getDbConnection();

    // portfolio_extras holds all display/marketing data for the portfolio page.
    // It links to the existing CRM `projects` table via project_id.
    // The CRM owns the core project record; this table owns the website display settings.
    $conn->query("CREATE TABLE IF NOT EXISTS portfolio_extras (
        id                   INT AUTO_INCREMENT PRIMARY KEY,
        project_id           INT           NOT NULL UNIQUE,
        display_category     VARCHAR(50)   DEFAULT NULL,
        image_url            VARCHAR(500)  DEFAULT NULL,
        tags                 VARCHAR(500)  DEFAULT NULL,
        badge_label          VARCHAR(100)  DEFAULT NULL,
        badge_colour         VARCHAR(30)   DEFAULT 'gold',
        case_study_title     VARCHAR(255)  DEFAULT NULL,
        case_study_overview  TEXT          DEFAULT NULL,
        case_study_challenge TEXT          DEFAULT NULL,
        case_study_solution  TEXT          DEFAULT NULL,
        case_study_results   TEXT          DEFAULT NULL,
        project_live_url     VARCHAR(500)  DEFAULT NULL,
        show_in_portfolio    TINYINT(1)    DEFAULT 1,
        sort_order           INT           DEFAULT 0,
        created_at           DATETIME      DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // JOIN the CRM projects table with the portfolio display settings.
    // COALESCE uses display_category if set, otherwise falls back to the CRM category.
    $stmt = $conn->prepare(
        "SELECT
             p.id,
             p.name                                         AS project_name,
             p.description                                  AS short_description,
             COALESCE(pe.display_category, p.category)      AS category,
             pe.image_url,
             pe.tags,
             pe.badge_label,
             pe.badge_colour,
             pe.case_study_title,
             pe.case_study_overview,
             pe.case_study_challenge,
             pe.case_study_solution,
             pe.case_study_results,
             pe.project_live_url,
             pe.sort_order
         FROM projects p
         INNER JOIN portfolio_extras pe ON pe.project_id = p.id
         WHERE pe.show_in_portfolio = 1
           AND p.status = 'completed'
         ORDER BY pe.sort_order ASC, p.id DESC"
    );

    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $row['tags_array'] = $row['tags']
            ? array_values(array_filter(array_map('trim', explode(',', $row['tags']))))
            : [];
        $projects[] = $row;
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'projects' => $projects, 'total' => count($projects)]);

} catch (Exception $e) {
    error_log('Portfolio fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'projects' => [], 'total' => 0, 'error' => $e->getMessage()]);
}
