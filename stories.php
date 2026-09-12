<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Stories | Cyber Heroes';
$stories = $pdo->query('SELECT id, story_number, title_en, title_hi, description_en, description_hi FROM stories WHERE is_active = 1 ORDER BY story_number')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero section-shell">
    <span class="eyebrow">INDEX • 30 STORIES</span>
    <h1>Cybersecurity Stories</h1>
    <p>Choose a story to open the comic reader. Your 30-story index can be edited from the database later.</p>
</section>
<section class="section-shell story-index">
<?php if (!$stories): ?>
    <div class="empty-state"><strong>No stories added yet.</strong><p>Import <code>sql/schema.sql</code> and then add the story records.</p></div>
<?php else: ?>
    <div class="story-grid">
    <?php foreach ($stories as $story): ?>
        <a class="story-card" href="story.php?id=<?= (int)$story['id'] ?>&lang=<?= e($lang) ?>">
            <span class="story-number"><?= str_pad((string)$story['story_number'], 2, '0', STR_PAD_LEFT) ?></span>
            <div>
                <h2><?= e($lang === 'hindi' ? $story['title_hi'] : $story['title_en']) ?></h2>
                <p><?= e($lang === 'hindi' ? $story['description_hi'] : $story['description_en']) ?></p>
            </div>
            <span class="story-arrow">→</span>
        </a>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
