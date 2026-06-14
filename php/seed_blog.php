<?php
/**
 * Blog Seed Script,inserts dummy blog posts for testing.
 *
 * HOW TO RUN:
 *   Open your browser and go to:
 *   http://localhost/kconsulting/php/seed_blog.php
 *
 * HOW TO UNDO:
 *   DELETE FROM blog_posts WHERE slug IN (... slugs printed below ...);
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    $conn = getDbConnection();
    echo "Connected to database.\n\n";

    $conn->query("CREATE TABLE IF NOT EXISTS blog_posts (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        title          VARCHAR(255) NOT NULL,
        slug           VARCHAR(255) NOT NULL UNIQUE,
        excerpt        TEXT,
        content        LONGTEXT,
        featured_image VARCHAR(500) DEFAULT NULL,
        author         VARCHAR(100) DEFAULT 'KConsulting Team',
        category       VARCHAR(100) DEFAULT NULL,
        tags           VARCHAR(500) DEFAULT NULL,
        read_time      INT          DEFAULT 5,
        is_featured    TINYINT(1)   DEFAULT 0,
        status         VARCHAR(20)  DEFAULT 'published',
        views          INT          DEFAULT 0,
        published_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "blog_posts table ready.\n\n";

    $posts = [
        [
            'title'      => '5 Signs Your Website Is Losing You Leads',
            'slug'       => 'signs-your-website-is-losing-leads',
            'excerpt'    => 'Most business websites look professional but quietly turn away potential clients every day. Here are five warning signs,and what to do about each one.',
            'content'    => "Your website might be your biggest untapped asset,or your biggest liability. Most business owners assume that having a website means it is working for them. The truth is that the average business website converts less than 2% of its visitors into enquiries. That means 98 out of every 100 people who find you online leave without getting in touch.\n\nHere are five clear signs that your website is costing you leads, and what you can do about each one.\n\n**1. Your contact form is buried or complicated**\n\nIf someone has to click through three pages to find your contact form, you have already lost them. Visitors make decisions in seconds. Every extra step reduces the chance they will reach out. Your primary call-to-action should be visible above the fold on every key page, and your contact form should ask for the minimum required information,name, email, and message.\n\n**2. Your site loads slowly on mobile**\n\nOver 65% of web traffic in South Africa now comes from mobile devices. If your site takes more than three seconds to load on a phone, studies show that more than half of visitors will leave before it finishes loading. Google also ranks slower sites lower in search results, meaning fewer people find you in the first place. Use Google PageSpeed Insights to check your current score.\n\n**3. Your messaging is unclear within five seconds**\n\nWhen someone lands on your homepage, they are asking three questions: What do you do? Who do you do it for? Why should I choose you? If your homepage does not answer all three within the first few seconds, visitors leave confused. Clear, specific messaging outperforms clever every time.\n\n**4. You have no social proof above the fold**\n\nTestimonials, client logos, and results are some of the most powerful conversion tools on any website. If a visitor has to scroll to the bottom of your page to find evidence that you are credible and capable, most of them will not get that far. Place your strongest proof,a short quote, a result, or a client name,high on the page.\n\n**5. Your website has no follow-up mechanism**\n\nMost visitors are not ready to enquire on their first visit. If your only option is a contact form, you are losing everyone who is still in research mode. Adding a lead magnet, a free audit offer, or an email capture gives you a way to stay in touch with visitors who are interested but not ready yet.\n\nFixing even one of these issues can meaningfully improve the number of enquiries your website generates. If you would like a clear picture of how your site is performing, our free website audit covers all of these areas and more.",
            'category'   => 'Marketing',
            'tags'       => 'Leads, Website, CRO, Conversion, UX',
            'read_time'  => 5,
            'is_featured'=> 1,
            'published_at' => '2025-11-01 08:00:00',
        ],
        [
            'title'      => 'What Is Conversion Rate Optimisation and Why Does It Matter?',
            'slug'       => 'what-is-conversion-rate-optimisation',
            'excerpt'    => 'CRO is the science of turning more of your existing website visitors into customers,without spending more on advertising. Here is what you need to know.',
            'content'    => "Conversion Rate Optimisation, or CRO, is the process of improving your website so that a higher percentage of visitors take the action you want,whether that is submitting an enquiry form, booking a call, making a purchase, or downloading a resource.\n\nIf your website currently converts 1% of visitors and you improve that to 2%, you have doubled your leads without spending a single extra rand on advertising. That is the power of CRO.\n\n**Why most businesses ignore CRO**\n\nMost marketing budgets are spent on getting more traffic,through paid ads, SEO, or social media. Very little attention goes to what happens after the traffic arrives. The result is that businesses spend more and more to acquire visitors, while quietly losing the majority of them.\n\n**What CRO actually involves**\n\nGood CRO starts with understanding why visitors are not converting. This means reviewing heatmaps to see where people click and scroll, watching session recordings to understand user behaviour, analysing your funnel to see where people drop off, and testing changes to see which versions perform better.\n\nCommon improvements include simplifying contact forms, rewriting headlines to be more specific, adding trust signals like testimonials and certifications, improving page speed, and making call-to-action buttons more prominent and compelling.\n\n**What results can you expect?**\n\nCRO results vary depending on your starting point and the changes made, but it is common to see conversion rate improvements of 30% to 100% from a structured optimisation process. For a business with steady traffic, this can translate directly into a significant increase in monthly revenue without any increase in advertising spend.\n\n**CRO is not a one-time project**\n\nThe best results come from treating CRO as an ongoing process rather than a single project. Consumer behaviour changes, new competitors enter the market, and your own offerings evolve. Regular testing and refinement keeps your website performing at its best over time.",
            'category'   => 'Marketing',
            'tags'       => 'CRO, Conversion, Growth, Website, Optimisation',
            'read_time'  => 6,
            'is_featured'=> 0,
            'published_at' => '2025-11-08 09:00:00',
        ],
        [
            'title'      => 'How to Connect Your Business Tools with System Integration',
            'slug'       => 'how-to-connect-business-tools-system-integration',
            'excerpt'    => 'If your CRM, website, and communication tools do not talk to each other, you are losing time and leads every day. System integration is how you fix that.',
            'content'    => "Most growing businesses reach a point where their tools stop working together. Leads come in through a website form, but someone has to manually copy them into the CRM. Orders are placed in the online store, but the inventory system needs to be updated by hand. Customer service queries arrive in one inbox while the project team works in another.\n\nThis kind of manual data transfer is not just time-consuming,it is a source of errors, delays, and lost business. System integration solves this.\n\n**What is system integration?**\n\nSystem integration connects two or more software applications so they share data automatically. When a new lead submits your contact form, it appears in your CRM instantly, triggers a WhatsApp notification to your sales team, and sends an automated acknowledgement email to the client,all without anyone lifting a finger.\n\n**Common integrations for South African businesses**\n\nWebsite to CRM: Every enquiry or contact form submission flows directly into your client management system, tagged and assigned automatically.\n\nPayment gateway to accounting software: Payments reconcile automatically, removing hours of manual bookkeeping each week.\n\nWhatsApp to CRM: Customer conversations are logged against the correct client record so your team always has context.\n\nEcommerce to inventory: When a product sells, stock levels update across all channels immediately.\n\n**Tools we use for integration**\n\nMost integrations are built using APIs,the standardised communication layer that modern software applications expose. For businesses without technical resources, platforms like Zapier and n8n allow many integrations to be configured without writing code. For more complex requirements, we build custom integration layers.\n\n**The business impact**\n\nOur clients typically see a 30% to 60% reduction in manual admin time within the first month of implementing structured integrations. More importantly, no leads fall through the cracks, and the team can focus on work that actually grows the business.",
            'category'   => 'Systems',
            'tags'       => 'API, Automation, CRM, Integration, Zapier, n8n',
            'read_time'  => 7,
            'is_featured'=> 0,
            'published_at' => '2025-11-15 10:00:00',
        ],
        [
            'title'      => 'Cloud vs On-Premise: What Is Right for Your South African Business?',
            'slug'       => 'cloud-vs-on-premise-south-africa',
            'excerpt'    => 'Choosing between cloud and on-premise infrastructure is one of the most important IT decisions a growing business will make. Here is how to think about it.',
            'content'    => "The question of cloud versus on-premise infrastructure comes up for almost every business that starts to take its IT seriously. Both have legitimate advantages, and the right answer depends on your business size, budget, risk tolerance, and growth plans.\n\n**What is on-premise infrastructure?**\n\nOn-premise means your servers, storage, and networking equipment physically reside in your office or a dedicated server room. You own the hardware, manage the maintenance, and are responsible for uptime, backups, and security.\n\nThe advantage is control,you know exactly where your data is and who can access it. The disadvantage is cost. Hardware is expensive to buy and replace, maintenance requires either in-house expertise or an IT support contract, and scaling up means buying more equipment.\n\n**What is cloud infrastructure?**\n\nCloud infrastructure means your computing resources are hosted by a third-party provider,typically AWS, Microsoft Azure, or Google Cloud,and accessed over the internet. You pay for what you use, and scaling up or down can be done in minutes.\n\nFor most South African SMEs, cloud offers a compelling combination of lower upfront cost, built-in redundancy, and professional-grade security that would be prohibitively expensive to replicate on-premise.\n\n**Where South African businesses need to be careful**\n\nInternet reliability is a real consideration. If your business depends on cloud-hosted tools and your connectivity goes down, so does your operation. A hybrid approach,critical tools on cloud, with offline capabilities for essential functions,often works well here.\n\nData sovereignty is another factor. Certain industries have regulatory requirements about where data is stored. South Africa's POPIA legislation has implications for businesses storing personal information offshore.\n\n**Our recommendation for most SMEs**\n\nFor businesses with fewer than 50 staff, cloud is almost always the right choice. The cost savings, reliability, built-in backups, and scalability far outweigh the benefits of on-premise. For larger businesses or those with specific compliance requirements, a hybrid approach often makes the most sense.\n\nThe most important thing is not to let the decision be made by default,migrating away from an under-powered on-premise setup later is significantly more expensive and disruptive than starting right.",
            'category'   => 'IT',
            'tags'       => 'Cloud, AWS, Infrastructure, South Africa, POPIA, On-Premise',
            'read_time'  => 8,
            'is_featured'=> 0,
            'published_at' => '2025-11-22 08:30:00',
        ],
        [
            'title'      => 'The Complete Guide to Lead Generation for South African SMEs',
            'slug'       => 'lead-generation-guide-south-african-smes',
            'excerpt'    => 'Getting consistent, qualified leads is the number one challenge for most small and medium businesses. This guide covers everything you need to build a reliable lead generation system.',
            'content'    => "Lead generation is the process of attracting people who are likely to become customers and moving them toward making contact with your business. For most South African SMEs, this is the most pressing growth challenge they face.\n\nThe problem is not usually a lack of marketing activity. Most business owners are posting on social media, running occasional ads, and relying on word-of-mouth. The problem is the absence of a system,a reliable, repeatable process that generates enquiries consistently, regardless of who is doing what on a given week.\n\n**The three stages of lead generation**\n\nEvery effective lead generation system has three stages: attract, capture, and convert.\n\nAttracting means getting the right people to find you,through organic search, paid advertising, social media, referrals, or partnerships. The channel matters less than the targeting. You want people who have the problem your business solves.\n\nCapturing means giving those visitors a reason to share their contact details. This might be a contact form, a free audit offer, a downloadable guide, or a WhatsApp chat widget. Without a capture mechanism, most visitors will leave and never return.\n\nConverting means following up in a way that builds trust and moves people toward a buying decision. This is where most businesses fail. They capture a lead, send one email, and give up. A structured follow-up sequence,whether by email, WhatsApp, or phone,dramatically increases the percentage of leads that become clients.\n\n**Why most lead gen fails for South African SMEs**\n\nThree reasons account for most lead generation failures. First, the website is not optimised to convert visitors,unclear messaging, slow load times, or no visible call-to-action. Second, there is no follow-up system, so leads go cold. Third, there is no tracking, so the business does not know which channels or messages are actually working.\n\n**Building your lead generation system**\n\nStart with your website. Ensure that every key page has a clear, specific call-to-action. Reduce the friction in your contact form. Add at least one lead magnet,something valuable enough that a potential client would trade their email address for it.\n\nNext, set up a simple CRM. Even a basic system ensures that no lead is forgotten and that every person who expresses interest receives consistent follow-up.\n\nFinally, track everything. Know where your leads come from, which ones convert, and how long the average sales cycle takes. This data tells you where to invest more and where to stop wasting money.\n\nBuilding a working lead generation system takes time, but the compounding effect is significant. Businesses that do it well stop worrying about where their next client is coming from.",
            'category'   => 'Growth',
            'tags'       => 'Leads, SME, Growth, Strategy, Funnel, CRM',
            'read_time'  => 10,
            'is_featured'=> 1,
            'published_at' => '2025-11-29 09:00:00',
        ],
        [
            'title'      => 'Why Your Ecommerce Store Is Not Converting (And How to Fix It)',
            'slug'       => 'why-ecommerce-store-not-converting',
            'excerpt'    => 'Traffic is not your problem. Most ecommerce stores have enough visitors,they just fail to convert them into buyers. Here are the most common reasons, and the fixes.',
            'content'    => "Getting traffic to an ecommerce store is easier than it has ever been. Paid ads, social media, and SEO can all drive consistent visitors to your products. The problem that most store owners eventually run into is not traffic,it is conversion.\n\nThe average ecommerce conversion rate globally sits around 2% to 3%. South African stores often see even lower numbers, partly due to trust barriers around online payments. If your store is converting at below 1%, you are leaving significant revenue on the table every single month.\n\n**The checkout is killing your sales**\n\nThe single biggest cause of lost ecommerce revenue is a complicated checkout. Every extra field, every unexpected cost, every required account creation is a reason for a buyer to abandon their cart. Studies consistently show that cart abandonment rates drop significantly when checkout is simplified to the minimum required steps.\n\nFix: Audit your checkout. Remove every field that is not essential. Enable guest checkout. Show the full cost,including shipping,before the final step. Add trust badges near the payment button.\n\n**Your product pages are not doing enough work**\n\nA buyer who cannot quickly understand exactly what they are getting, why it is worth the price, and why they should trust you will not buy. Product pages need clear images from multiple angles, specific descriptions that address the buyer's question, visible pricing, and social proof,reviews, ratings, or buyer counts.\n\n**Mobile experience is an afterthought**\n\nIn South Africa, mobile commerce is dominant. If your product images are slow to load, your add-to-cart button is hard to tap, or your checkout is difficult to complete on a phone, you are losing the majority of your potential buyers.\n\n**Payment options are limited**\n\nLocal buyers want to pay the way they are comfortable paying. If you only offer one payment method and a buyer does not use it, the sale is lost. Offering PayFast, Ozow, credit card, and EFT covers most South African buyers.\n\n**There is no urgency or reason to buy now**\n\nMost visitors who do not buy immediately never return. Ethical urgency,limited stock indicators, limited-time offers, or shipping cutoff times,gives buyers who are on the fence a reason to act now rather than later.\n\nFixing these issues does not require a full rebuild of your store. Targeted, methodical changes,tested and measured,can double your conversion rate without increasing your advertising spend.",
            'category'   => 'Marketing',
            'tags'       => 'eCommerce, CRO, Checkout, Revenue, WooCommerce, Conversion',
            'read_time'  => 7,
            'is_featured'=> 0,
            'published_at' => '2025-12-06 10:00:00',
        ],
        [
            'title'      => 'GEO: How to Get Your Business Recommended by AI Tools Like ChatGPT',
            'slug'       => 'geo-get-business-recommended-by-ai',
            'excerpt'    => 'Traditional SEO gets you found on Google. Generative Engine Optimisation (GEO) gets your business recommended by ChatGPT, Google Gemini, and Microsoft Copilot.',
            'content'    => "Search behaviour is changing fast. A growing number of people now start their buying research not on Google, but by asking an AI assistant,ChatGPT, Google Gemini, or Microsoft Copilot. They type questions like \"what is the best web design company in Cape Town\" or \"which South African IT firm should I use for cloud migration\" and expect the AI to recommend specific businesses.\n\nIf your business is not set up to be found and recommended by these AI platforms, you are invisible to a growing segment of potential clients.\n\n**What is Generative Engine Optimisation?**\n\nGenerative Engine Optimisation (GEO) is the practice of structuring your online presence, content, and business information so that AI-powered platforms can understand what you do, who you serve, and why you are credible,and recommend you in response to relevant queries.\n\nIt is not the same as traditional SEO. SEO optimises for keyword rankings in search results. GEO optimises for the way large language models synthesise and recommend information.\n\n**How AI tools decide what to recommend**\n\nAI assistants draw on multiple sources: the web content they were trained on, real-time search results, structured business data, reviews, and authoritative third-party mentions. Businesses that appear consistently across credible, specific sources are more likely to be surfaced in AI responses.\n\n**What GEO involves in practice**\n\nClear, specific website content that directly answers questions your target clients are likely to ask. Generic marketing copy does not help AI tools understand what you do,specific, substantive answers do.\n\nStructured data markup on your website tells search engines and AI platforms exactly what your business is, who it serves, and what services it provides.\n\nConsistent NAP data (Name, Address, Phone) across Google Business Profile, directories, and your website ensures AI tools can verify and reference your business reliably.\n\nAuthority signals,being mentioned in industry publications, having genuine reviews, and earning links from credible sources,all contribute to the AI's confidence in recommending your business.\n\n**Is GEO replacing SEO?**\n\nNot yet, and possibly not entirely. Traditional search still drives significant traffic, and many buyers still use Google in the conventional way. The smart approach is to build a strategy that works for both,which, fortunately, has a great deal of overlap.",
            'category'   => 'Marketing',
            'tags'       => 'GEO, AI, SEO, ChatGPT, Google Gemini, Digital Marketing',
            'read_time'  => 6,
            'is_featured'=> 0,
            'published_at' => '2025-12-13 08:00:00',
        ],
        [
            'title'      => '5 Business Metrics Every South African SME Should Track Weekly',
            'slug'       => 'business-metrics-south-african-sme-track-weekly',
            'excerpt'    => 'You cannot grow what you do not measure. These five metrics give you a clear, weekly picture of the health and direction of your business.',
            'content'    => "Most small business owners are busy. Tracking business metrics feels like an admin task that competes with serving clients, managing staff, and keeping operations running. But the businesses that grow consistently are not the ones working the hardest,they are the ones making the best decisions. And good decisions require reliable data.\n\nYou do not need a complex dashboard or an expensive analytics platform to track the metrics that matter most. These five numbers, reviewed weekly, will give you a better picture of your business health than most formal management reports.\n\n**1. Number of new leads this week**\n\nThis tells you whether your marketing is working. If you are not counting leads, you have no way to know if a change in marketing spend, a new campaign, or a website update is making a difference. Set a target, track weekly, and review the trend monthly.\n\n**2. Lead-to-client conversion rate**\n\nOf the leads that came in this month, what percentage became paying clients? This tells you whether your sales process is working, whether your pricing is positioned correctly, and whether the quality of your leads is improving or declining.\n\n**3. Revenue this week versus the same week last year**\n\nYear-on-year comparison removes seasonal noise and gives you a true picture of growth. Knowing that this week was 20% ahead of the same week twelve months ago is more meaningful than knowing it was better than last week.\n\n**4. Outstanding invoices and cash flow position**\n\nFor South African SMEs, cash flow is frequently the difference between survival and closure. Tracking outstanding invoices weekly,and following up consistently,has a direct impact on your ability to pay staff, suppliers, and yourself.\n\n**5. Website enquiries and conversions**\n\nIf your website is one of your primary lead generation channels, track weekly how many people visited, how many submitted an enquiry, and what your conversion rate was. A sudden drop in conversions is an early warning sign of a technical problem, a Google ranking change, or a competitor move.\n\n**Making it sustainable**\n\nThe goal is not a perfect dashboard,it is a sustainable habit. Even tracking these five numbers in a simple spreadsheet, reviewed every Monday morning, will give you more clarity and control over your business than most owners have.",
            'category'   => 'Growth',
            'tags'       => 'Analytics, KPIs, Dashboard, Growth, Business Metrics, SME',
            'read_time'  => 6,
            'is_featured'=> 0,
            'published_at' => '2025-12-20 09:30:00',
        ],
    ];

    $stmt = $conn->prepare(
        "INSERT INTO blog_posts
             (title, slug, excerpt, content, author, category, tags,
              read_time, is_featured, status, views, published_at)
         VALUES (?, ?, ?, ?, 'KConsulting Team', ?, ?, ?, ?, 'published', ?, ?)
         ON DUPLICATE KEY UPDATE
             title       = VALUES(title),
             excerpt     = VALUES(excerpt),
             content     = VALUES(content),
             category    = VALUES(category),
             tags        = VALUES(tags),
             read_time   = VALUES(read_time),
             is_featured = VALUES(is_featured),
             views       = VALUES(views),
             published_at= VALUES(published_at)"
    );

    $slugs = [];
    foreach ($posts as $p) {
        $title       = $p['title'];
        $slug        = $p['slug'];
        $excerpt     = $p['excerpt'];
        $content     = $p['content'];
        $category    = $p['category'];
        $tags        = $p['tags'];
        $read_time   = (int)$p['read_time'];
        $is_featured = (int)$p['is_featured'];
        $views       = (int)($p['views'] ?? rand(80, 420));
        $published   = $p['published_at'];

        $stmt->bind_param(
            'ssssssiiis',
            $title, $slug, $excerpt, $content,
            $category, $tags, $read_time,
            $is_featured, $views, $published
        );
        $stmt->execute();
        $slugs[] = $slug;
        echo "Upserted: {$title}\n";
    }

    $stmt->close();
    $conn->close();

    echo "\n";
    echo "==========================================================\n";
    echo "Done! Visit:\n";
    echo "  http://localhost/kconsulting/blog.html\n";
    echo "  http://localhost/kconsulting/index.html  (scroll to blog preview)\n";
    echo "==========================================================\n\n";
    echo "TO CLEAN UP (remove test data):\n";
    echo "  DELETE FROM blog_posts WHERE slug IN ('" . implode("','", $slugs) . "');\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
