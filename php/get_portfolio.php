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
        project_id           INT           NOT NULL UNIQUE  COMMENT 'Links to projects.id in the CRM',
        display_category     VARCHAR(50)   DEFAULT NULL     COMMENT 'Portfolio filter: web, ecommerce, marketing, systems, it (overrides CRM category if set)',
        image_url            VARCHAR(500)  DEFAULT NULL     COMMENT 'Full URL or relative path to the project screenshot',
        tags                 VARCHAR(500)  DEFAULT NULL     COMMENT 'Comma-separated skills or tools used (e.g. WordPress, SEO, PHP)',
        badge_label          VARCHAR(100)  DEFAULT NULL     COMMENT 'Highlight label on the card (e.g. Featured, New, Award Winner)',
        badge_colour         VARCHAR(30)   DEFAULT 'gold'   COMMENT 'Badge colour: gold, green, blue, red, grey',
        case_study_title     VARCHAR(255)  DEFAULT NULL     COMMENT 'Heading shown when the case study popup opens',
        case_study_overview  TEXT          DEFAULT NULL     COMMENT 'Brief context: what the project was and who it was for',
        case_study_challenge TEXT          DEFAULT NULL     COMMENT 'The problem or pain point the client was facing',
        case_study_solution  TEXT          DEFAULT NULL     COMMENT 'What KConsulting built or implemented to solve it',
        case_study_results   TEXT          DEFAULT NULL     COMMENT 'Measurable outcomes: metrics, improvements, business impact',
        project_live_url     VARCHAR(500)  DEFAULT NULL     COMMENT 'Link to the live website (shown as a button in the popup)',
        show_in_portfolio    TINYINT(1)    DEFAULT 1        COMMENT '1 = visible on portfolio page, 0 = hidden without deleting',
        sort_order           INT           DEFAULT 0        COMMENT 'Display order: lower number appears first on the page',
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
