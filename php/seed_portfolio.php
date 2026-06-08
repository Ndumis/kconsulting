<?php
/**
 * Portfolio Seed Script — testing only.
 *
 * HOW TO RUN:
 *   Open your browser and go to:
 *   http://localhost/kconsulting/php/seed_portfolio.php
 *
 * WHAT IT DOES:
 *   1. Creates portfolio_extras table if it doesn't exist
 *   2. Temporarily marks all 8 existing projects as 'completed' so they show on the portfolio
 *   3. Inserts portfolio display data (tags, badge, case study) for each project
 *
 * HOW TO UNDO:
 *   Run the cleanup SQL at the bottom of this output, then delete this file.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    $conn = getDbConnection();
    echo "Connected to database.\n\n";

    // ── 1. Ensure portfolio_extras table exists ────────────────────────────

    $conn->query("CREATE TABLE IF NOT EXISTS portfolio_extras (
        id                   INT AUTO_INCREMENT PRIMARY KEY,
        project_id           INT           NOT NULL UNIQUE  COMMENT 'Links to projects.id in the CRM',
        display_category     VARCHAR(50)   DEFAULT NULL     COMMENT 'Portfolio filter: web, ecommerce, marketing, systems, it',
        image_url            VARCHAR(500)  DEFAULT NULL     COMMENT 'Full URL or relative path to the project screenshot',
        tags                 VARCHAR(500)  DEFAULT NULL     COMMENT 'Comma-separated skills or tools used (e.g. WordPress, SEO, PHP)',
        badge_label          VARCHAR(100)  DEFAULT NULL     COMMENT 'Highlight label on the card (e.g. Featured, New, Award Winner)',
        badge_colour         VARCHAR(30)   DEFAULT 'gold'   COMMENT 'Badge colour: gold, green, blue, red, grey',
        case_study_title     VARCHAR(255)  DEFAULT NULL     COMMENT 'Heading shown when the case study popup opens',
        case_study_overview  TEXT          DEFAULT NULL     COMMENT 'Context: what the project was and who it was for',
        case_study_challenge TEXT          DEFAULT NULL     COMMENT 'The problem the client was facing',
        case_study_solution  TEXT          DEFAULT NULL     COMMENT 'What KConsulting built or implemented',
        case_study_results   TEXT          DEFAULT NULL     COMMENT 'Measurable outcomes and results achieved',
        project_live_url     VARCHAR(500)  DEFAULT NULL     COMMENT 'Link to the live website (optional)',
        show_in_portfolio    TINYINT(1)    DEFAULT 1        COMMENT '1 = visible on portfolio page, 0 = hidden',
        sort_order           INT           DEFAULT 0        COMMENT 'Display order: lower number appears first',
        created_at           DATETIME      DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "portfolio_extras table ready.\n\n";

    // ── 2. Mark existing projects 1-8 as completed (for testing) ──────────

    $conn->query("UPDATE projects SET status = 'completed' WHERE id BETWEEN 1 AND 8");
    echo "Updated projects 1-8 to status = 'completed' for testing.\n\n";

    // ── 3. Portfolio display data for each existing project ────────────────
    //
    // display_category maps your CRM categories to portfolio filter tabs:
    //   web | ecommerce | marketing | systems | it
    //
    // Project IDs match your existing rows: 1=E-Commerce, 2=Mobile App,
    // 3=Database Migration, 4=API Integration, 5=Security Audit,
    // 6=Cloud Infrastructure, 7=Internal Dashboard, 8=Legacy System

    $extras = [
        [
            'project_id'           => 1,
            'display_category'     => 'ecommerce',
            'image_url'            => null,
            'tags'                 => 'WooCommerce, UX, Payment Gateway, CRO, Mobile',
            'badge_label'          => 'Featured',
            'badge_colour'         => 'gold',
            'case_study_title'     => 'E-Commerce Platform Redesign',
            'case_study_overview'  => 'A complete redesign and rebuild of a client e-commerce platform to improve conversions, modernise the shopping experience, and support higher traffic volumes.',
            'case_study_challenge' => 'The existing platform was outdated, had a high cart abandonment rate, and performed poorly on mobile devices. Clients were losing sales to competitors with more modern stores.',
            'case_study_solution'  => 'We redesigned the product pages, simplified the checkout to three steps, integrated a reliable payment gateway, and rebuilt the mobile experience from the ground up with performance as a priority.',
            'case_study_results'   => "Cart abandonment dropped from 72% to 44%.\nMobile conversion rate increased by 85%.\nRevenue grew 40% month-on-month in the first quarter post-launch.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 1,
        ],
        [
            'project_id'           => 2,
            'display_category'     => 'web',
            'image_url'            => null,
            'tags'                 => 'iOS, Android, React Native, API, CRM Integration',
            'badge_label'          => 'New',
            'badge_colour'         => 'green',
            'case_study_title'     => 'Mobile App for Business Management',
            'case_study_overview'  => 'A native iOS and Android app built to give a client\'s team real-time access to their business data, client records, and task management from any device.',
            'case_study_challenge' => 'The client\'s team was working from spreadsheets and desktop-only tools, making it impossible to access information or update records while out of the office.',
            'case_study_solution'  => 'We designed and built a cross-platform mobile app with secure login, real-time data sync, push notifications, and full CRM integration so the team could manage everything on the go.',
            'case_study_results'   => "Team productivity improved by 30% in the first 60 days.\nField staff response times reduced by 50%.\nApp store rating of 4.8 from internal users.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 2,
        ],
        [
            'project_id'           => 3,
            'display_category'     => 'it',
            'image_url'            => null,
            'tags'                 => 'MySQL, Database, Migration, Data Integrity, SQL',
            'badge_label'          => null,
            'badge_colour'         => 'gold',
            'case_study_title'     => 'Legacy Database Migration to MySQL',
            'case_study_overview'  => 'Migrated a client\'s decade-old legacy database to a modern, optimised MySQL infrastructure with zero data loss and minimal downtime.',
            'case_study_challenge' => 'The legacy system was slow, prone to errors, and no longer supported. Data was spread across inconsistent formats, making querying unreliable and reporting impossible.',
            'case_study_solution'  => 'We audited the existing data, designed a clean relational schema, wrote custom migration scripts to transform and validate all records, and ran parallel testing before the final cutover.',
            'case_study_results'   => "100% data integrity confirmed post-migration.\nQuery performance improved by 300% on key reports.\nSystem downtime during migration was under 2 hours.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 3,
        ],
        [
            'project_id'           => 4,
            'display_category'     => 'systems',
            'image_url'            => null,
            'tags'                 => 'API, REST, Payment Gateway, Shipping, Webhooks, PHP',
            'badge_label'          => 'Featured',
            'badge_colour'         => 'blue',
            'case_study_title'     => 'Third-Party API Integration System',
            'case_study_overview'  => 'Built a unified integration layer connecting payment, shipping, and inventory APIs for a retail client, replacing a fragmented manual process with a single automated system.',
            'case_study_challenge' => 'The client was using three separate tools with no integration between them. Staff were manually copying order data between systems, leading to delays, errors, and customer complaints.',
            'case_study_solution'  => 'We designed and built a REST API integration hub using PHP and webhooks, connecting the payment gateway, shipping provider, and inventory system so data flows automatically with full error handling and logging.',
            'case_study_results'   => "Order processing time reduced from 45 minutes to under 2 minutes.\nData entry errors eliminated entirely.\nStaff saved approximately 20 hours per week in manual admin.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 4,
        ],
        [
            'project_id'           => 5,
            'display_category'     => 'it',
            'image_url'            => null,
            'tags'                 => 'Security, GDPR, Compliance, Penetration Testing, Audit',
            'badge_label'          => null,
            'badge_colour'         => 'gold',
            'case_study_title'     => 'Security Audit and GDPR Compliance',
            'case_study_overview'  => 'Conducted a full security review and GDPR compliance implementation for a client handling sensitive customer data, identifying vulnerabilities and delivering a compliant, secure setup.',
            'case_study_challenge' => 'The client was collecting and storing customer data without proper GDPR controls in place, creating legal risk. Their systems had not been audited and had several unpatched vulnerabilities.',
            'case_study_solution'  => 'We performed a penetration test, reviewed all data handling processes, implemented encryption at rest and in transit, updated cookie and privacy policies, and introduced role-based access controls.',
            'case_study_results'   => "12 security vulnerabilities identified and resolved.\nFull GDPR compliance achieved before regulatory deadline.\nClient passed third-party compliance audit with no findings.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 5,
        ],
        [
            'project_id'           => 6,
            'display_category'     => 'it',
            'image_url'            => null,
            'tags'                 => 'AWS, DevOps, Cloud, Auto-scaling, Docker, Linux',
            'badge_label'          => null,
            'badge_colour'         => 'gold',
            'case_study_title'     => 'Cloud Infrastructure Setup on AWS',
            'case_study_overview'  => 'Designed and deployed a fully managed AWS cloud infrastructure with auto-scaling, load balancing, and automated backups for a growing SaaS business.',
            'case_study_challenge' => 'The client\'s on-premise setup could not handle traffic spikes, causing outages during peak periods. They needed a scalable, fault-tolerant architecture that grew with their user base.',
            'case_study_solution'  => 'We architected a multi-AZ AWS setup with EC2 auto-scaling groups, an Application Load Balancer, RDS with automated backups, CloudWatch monitoring, and Docker containerisation for consistent deployments.',
            'case_study_results'   => "Zero downtime during a 3x traffic spike post-launch.\nInfrastructure costs reduced by 32% versus on-premise.\nDeployment time cut from 2 hours to 8 minutes with CI/CD pipeline.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 6,
        ],
        [
            'project_id'           => 7,
            'display_category'     => 'web',
            'image_url'            => null,
            'tags'                 => 'Dashboard, Analytics, PHP, MySQL, Chart.js, UX',
            'badge_label'          => null,
            'badge_colour'         => 'gold',
            'case_study_title'     => 'Custom Business Intelligence Dashboard',
            'case_study_overview'  => 'Built a custom internal analytics dashboard giving leadership real-time visibility into sales, project progress, team performance, and revenue KPIs from a single screen.',
            'case_study_challenge' => 'Management was pulling data from five separate tools to compile weekly reports, taking 3-4 hours each time. Decisions were being made on data that was days old.',
            'case_study_solution'  => 'We built a custom PHP and MySQL dashboard with live data feeds, interactive Chart.js visualisations, role-based access, and automated daily email summaries so leadership always had current numbers.',
            'case_study_results'   => "Weekly reporting time reduced from 4 hours to zero (fully automated).\nData freshness improved from 48-hour lag to real-time.\nLeadership reported faster, more confident decision-making within 30 days.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 7,
        ],
        [
            'project_id'           => 8,
            'display_category'     => 'systems',
            'image_url'            => null,
            'tags'                 => 'PHP, Laravel, Refactoring, API, MySQL, Modernisation',
            'badge_label'          => null,
            'badge_colour'         => 'gold',
            'case_study_title'     => 'Legacy PHP System Modernisation',
            'case_study_overview'  => 'Modernised a decade-old PHP system into a maintainable, secure, and scalable Laravel application without disrupting day-to-day business operations.',
            'case_study_challenge' => 'The existing system was built on unsupported PHP versions, had no unit tests, and was impossible to extend or maintain safely. Adding any new feature risked breaking existing functionality.',
            'case_study_solution'  => 'We broke the migration into phases, rewriting modules incrementally into Laravel with full test coverage, a clean API layer, and a modern database schema while keeping the old system running in parallel during transition.',
            'case_study_results'   => "New features now delivered in days instead of weeks.\nBug rate dropped by 80% within two months of go-live.\nSystem now fully supported and extensible for future growth.",
            'project_live_url'     => null,
            'show_in_portfolio'    => 1,
            'sort_order'           => 8,
        ],
    ];

    $stmt = $conn->prepare(
        "INSERT INTO portfolio_extras
             (project_id, display_category, image_url, tags, badge_label, badge_colour,
              case_study_title, case_study_overview, case_study_challenge,
              case_study_solution, case_study_results, project_live_url,
              show_in_portfolio, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
             display_category    = VALUES(display_category),
             tags                = VALUES(tags),
             badge_label         = VALUES(badge_label),
             badge_colour        = VALUES(badge_colour),
             case_study_title    = VALUES(case_study_title),
             case_study_overview = VALUES(case_study_overview),
             case_study_challenge= VALUES(case_study_challenge),
             case_study_solution = VALUES(case_study_solution),
             case_study_results  = VALUES(case_study_results),
             show_in_portfolio   = VALUES(show_in_portfolio),
             sort_order          = VALUES(sort_order)"
    );

    foreach ($extras as $e) {
        $stmt->bind_param(
            'isssssssssssii',
            $e['project_id'], $e['display_category'], $e['image_url'],
            $e['tags'], $e['badge_label'], $e['badge_colour'],
            $e['case_study_title'], $e['case_study_overview'], $e['case_study_challenge'],
            $e['case_study_solution'], $e['case_study_results'], $e['project_live_url'],
            $e['show_in_portfolio'], $e['sort_order']
        );
        $stmt->execute();
        echo "Upserted portfolio_extras for project ID: {$e['project_id']}\n";
    }

    $stmt->close();
    $conn->close();

    echo "\n";
    echo "==========================================================\n";
    echo "Done! Visit http://localhost/kconsulting/portfolio.html\n";
    echo "==========================================================\n\n";
    echo "TO CLEAN UP (remove test data):\n";
    echo "  DELETE FROM portfolio_extras WHERE project_id BETWEEN 1 AND 8;\n";
    echo "  UPDATE projects SET status = 'in-progress' WHERE id BETWEEN 1 AND 8;\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
