<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$lang = (isset($_GET['lang']) && $_GET['lang'] === 'hindi')
    ? 'hindi'
    : 'english';

$pageTitle = 'Cyber Heroes Quiz | 30 Story Quizzes';

$stmt = $pdo->query(
    'SELECT
        s.id,
        s.story_number,
        s.title_en,
        s.title_hi,
        s.description_en,
        s.description_hi,
        COUNT(q.id) AS question_count
     FROM stories s
     LEFT JOIN quiz_questions q ON q.story_id = s.id
     WHERE s.is_active = 1
     GROUP BY
        s.id,
        s.story_number,
        s.title_en,
        s.title_hi,
        s.description_en,
        s.description_hi
     ORDER BY s.story_number ASC'
);

$stories = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="quiz-index section-shell">

    <div class="quiz-index-hero">
        <span class="eyebrow">CYBER HEROES • QUIZ CENTER</span>

        <h1>
            <?= $lang === 'hindi' ? 'साइबर सुरक्षा क्विज़' : 'Cybersecurity Quiz' ?>
        </h1>

        <p>
            <?= $lang === 'hindi'
                ? 'हर कहानी के बाद अपना ज्ञान जांचें। अपनी कहानी चुनें और क्विज़ शुरू करें।'
                : 'Test your cybersecurity knowledge after each story. Choose a story and start its quiz.'
            ?>
        </p>

        <div class="quiz-language">
            <a
                class="<?= $lang === 'english' ? 'active' : '' ?>"
                href="quiz_index.php?lang=english"
            >
                English
            </a>

            <a
                class="<?= $lang === 'hindi' ? 'active' : '' ?>"
                href="quiz_index.php?lang=hindi"
            >
                हिंदी
            </a>
        </div>
    </div>


    <div class="quiz-index-grid">

        <?php foreach ($stories as $story): ?>

            <?php
                $number = (int)$story['story_number'];
                $title = $lang === 'hindi'
                    ? $story['title_hi']
                    : $story['title_en'];

                $description = $lang === 'hindi'
                    ? $story['description_hi']
                    : $story['description_en'];

                $questionCount = (int)$story['question_count'];

                $quizUrl = 'quiz.php?id=' . (int)$story['id']
                    . '&lang=' . e($lang);
            ?>

            <article class="quiz-index-card">

                <div class="quiz-card-number">
                    <?= str_pad((string)$number, 2, '0', STR_PAD_LEFT) ?>
                </div>

                <div class="quiz-card-content">

                    <div class="quiz-card-top">

                        <span class="quiz-card-label">
                            <?= $lang === 'hindi' ? 'कहानी' : 'STORY' ?>
                            <?= str_pad((string)$number, 2, '0', STR_PAD_LEFT) ?>
                        </span>

                        <span class="quiz-question-count">
                            <?= $questionCount ?> <?= $lang === 'hindi' ? 'प्रश्न' : 'Questions' ?>
                        </span>

                    </div>

                    <h2>
                        <?= e((string)$title) ?>
                    </h2>

                    <p>
                        <?= e((string)$description) ?>
                    </p>

                    <?php if ($questionCount > 0): ?>

                        <a class="quiz-start-button" href="<?= e($quizUrl) ?>">
                            <?= $lang === 'hindi'
                                ? 'क्विज़ शुरू करें →'
                                : 'Take Quiz →'
                            ?>
                        </a>

                    <?php else: ?>

                        <span class="quiz-coming-soon">
                            <?= $lang === 'hindi'
                                ? 'क्विज़ उपलब्ध नहीं है'
                                : 'Quiz not available'
                            ?>
                        </span>

                    <?php endif; ?>

                </div>

            </article>

        <?php endforeach; ?>

    </div>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
