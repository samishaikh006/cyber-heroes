<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id) {
    header('Location: stories.php');
    exit;
}

$lang = (isset($_GET['lang']) && $_GET['lang'] === 'hindi')
    ? 'hindi'
    : 'english';

/* Get story */
$stmt = $pdo->prepare(
    'SELECT id, story_number, title_en, title_hi
     FROM stories
     WHERE id = ? AND is_active = 1
     LIMIT 1'
);
$stmt->execute([$id]);
$story = $stmt->fetch();

if (!$story) {
    header('Location: stories.php');
    exit;
}

/* Get this story's UNIQUE questions */
$qStmt = $pdo->prepare(
    'SELECT id,
            question_en, question_hi,
            option_a_en, option_a_hi,
            option_b_en, option_b_hi,
            option_c_en, option_c_hi,
            option_d_en, option_d_hi,
            correct_option, sort_order
     FROM quiz_questions
     WHERE story_id = ?
     ORDER BY sort_order ASC, id ASC'
);
$qStmt->execute([(int)$story['id']]);
$questions = $qStmt->fetchAll();

$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$score = 0;
$answers = [];

if ($submitted && $questions) {
    foreach ($questions as $question) {
        $qid = (int)$question['id'];
        $answer = isset($_POST['answer'][$qid])
            ? strtoupper(trim((string)$_POST['answer'][$qid]))
            : '';

        $answers[$qid] = $answer;

        if ($answer !== '' && $answer === $question['correct_option']) {
            $score++;
        }
    }
}

$total = count($questions);
$percentage = $total > 0 ? round(($score / $total) * 100) : 0;

if ($percentage >= 80) {
    $resultTitleEn = 'Cybersecurity Hero!';
    $resultTitleHi = 'साइबर सुरक्षा हीरो!';
    $resultTextEn = 'Excellent work. You understood the key safety lessons.';
    $resultTextHi = 'बहुत बढ़िया। आपने मुख्य सुरक्षा lessons को अच्छी तरह समझा।';
} elseif ($percentage >= 50) {
    $resultTitleEn = 'Good Job!';
    $resultTitleHi = 'अच्छा काम!';
    $resultTextEn = 'You have a good start. Review the story and try again for a higher score.';
    $resultTextHi = 'आपकी शुरुआत अच्छी है। Story को फिर से देखें और बेहतर score के लिए दोबारा प्रयास करें।';
} else {
    $resultTitleEn = 'Keep Learning!';
    $resultTitleHi = 'सीखते रहें!';
    $resultTextEn = 'Review the comic story and try the quiz again.';
    $resultTextHi = 'Comic story को फिर से पढ़ें और quiz दोबारा attempt करें।';
}

$pageTitle =
    ($lang === 'hindi' ? $story['title_hi'] : $story['title_en'])
    . ' Quiz | Cyber Heroes';

require __DIR__ . '/includes/header.php';
?>

<section class="quiz-page section-shell">

    <div class="quiz-topbar">
        <a href="story.php?id=<?= (int)$story['id'] ?>&lang=<?= e($lang) ?>">
            ← <?= $lang === 'hindi' ? 'कहानी पर वापस' : 'Back to Story' ?>
        </a>

        <span>
            STORY <?= str_pad((string)$story['story_number'], 2, '0', STR_PAD_LEFT) ?>
        </span>

        <a href="stories.php?lang=<?= e($lang) ?>">
            <?= $lang === 'hindi' ? 'इंडेक्स' : 'Index' ?> →
        </a>
    </div>

    <div class="quiz-heading">
        <span class="quiz-eyebrow">CYBER HEROES • STORY QUIZ</span>

        <h1>
            <?= e($lang === 'hindi' ? $story['title_hi'] : $story['title_en']) ?>
        </h1>

        <p>
            <?= $lang === 'hindi'
                ? 'इस कहानी से जुड़े 5 unique सवालों का जवाब दें।'
                : 'Answer 5 unique questions based on this story.' ?>
        </p>

        <div class="quiz-language">
            <a
                class="<?= $lang === 'english' ? 'active' : '' ?>"
                href="quiz.php?id=<?= (int)$story['id'] ?>&lang=english"
            >English</a>

            <a
                class="<?= $lang === 'hindi' ? 'active' : '' ?>"
                href="quiz.php?id=<?= (int)$story['id'] ?>&lang=hindi"
            >हिंदी</a>
        </div>
    </div>

    <?php if (!$questions): ?>

        <div class="quiz-empty">
            <h2>
                <?= $lang === 'hindi'
                    ? 'इस कहानी का quiz अभी उपलब्ध नहीं है।'
                    : 'Quiz not available yet.' ?>
            </h2>

            <p>
                <?= $lang === 'hindi'
                    ? 'quiz_questions table में इस story के questions add करें।'
                    : 'Add questions for this story to the quiz_questions table.' ?>
            </p>
        </div>

    <?php elseif (!$submitted): ?>

        <form method="post" class="quiz-form">

            <?php foreach ($questions as $index => $question): ?>
                <?php
                    $prefix = $lang === 'hindi' ? 'question_hi' : 'question_en';
                    $optionPrefix = $lang === 'hindi' ? 'option_' : 'option_';
                    $qid = (int)$question['id'];
                ?>

                <article class="quiz-card">
                    <div class="quiz-number">
                        <?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?>
                    </div>

                    <h2>
                        <?= e($question[$prefix]) ?>
                    </h2>

                    <div class="quiz-options">

                        <?php foreach (['A', 'B', 'C', 'D'] as $letter): ?>
                            <?php $field = 'option_' . strtolower($letter) . '_' . ($lang === 'hindi' ? 'hi' : 'en'); ?>

                            <label class="quiz-option">
                                <input
                                    type="radio"
                                    name="answer[<?= $qid ?>]"
                                    value="<?= $letter ?>"
                                    required
                                >
                                <span class="option-letter"><?= $letter ?></span>
                                <span><?= e($question[$field]) ?></span>
                            </label>
                        <?php endforeach; ?>

                    </div>
                </article>
            <?php endforeach; ?>

            <button type="submit" class="quiz-submit">
                <?= $lang === 'hindi' ? 'क्विज़ सबमिट करें' : 'Submit Quiz' ?>
            </button>

        </form>

    <?php else: ?>

        <div class="quiz-result">
            <div class="quiz-score">
                <?= $score ?><small>/<?= $total ?></small>
            </div>

            <div class="quiz-result-content">
                <span class="quiz-eyebrow">
                    <?= $lang === 'hindi' ? 'RESULT' : 'RESULT' ?>
                </span>

                <h2>
                    <?= e($lang === 'hindi' ? $resultTitleHi : $resultTitleEn) ?>
                </h2>

                <p>
                    <?= e($lang === 'hindi' ? $resultTextHi : $resultTextEn) ?>
                </p>

                <strong><?= $percentage ?>%</strong>
            </div>
        </div>

        <div class="quiz-review">
            <h2>
                <?= $lang === 'hindi' ? 'उत्तर समीक्षा' : 'Answer Review' ?>
            </h2>

            <?php foreach ($questions as $index => $question): ?>
                <?php
                    $qid = (int)$question['id'];
                    $given = $answers[$qid] ?? '';
                    $correct = $question['correct_option'];
                    $isCorrect = $given === $correct;

                    $qField = $lang === 'hindi' ? 'question_hi' : 'question_en';
                    $correctField =
                        'option_' . strtolower($correct) . '_' .
                        ($lang === 'hindi' ? 'hi' : 'en');
                ?>

                <div class="review-card <?= $isCorrect ? 'correct' : 'wrong' ?>">
                    <div class="review-status">
                        <?= $isCorrect ? '✓' : '✕' ?>
                    </div>

                    <div>
                        <h3>
                            <?= ($index + 1) . '. ' . e($question[$qField]) ?>
                        </h3>

                        <p>
                            <?php if ($isCorrect): ?>
                                <?= $lang === 'hindi' ? 'सही उत्तर' : 'Correct answer' ?>
                            <?php else: ?>
                                <?= $lang === 'hindi' ? 'सही उत्तर' : 'Correct answer' ?>
                            <?php endif; ?>:
                            <strong><?= e($correct) ?> —
                                <?= e($question[$correctField]) ?>
                            </strong>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="quiz-actions">
            <a
                class="quiz-btn secondary"
                href="quiz.php?id=<?= (int)$story['id'] ?>&lang=<?= e($lang) ?>"
            >
                <?= $lang === 'hindi' ? 'फिर से प्रयास करें' : 'Try Again' ?>
            </a>

            <a
                class="quiz-btn primary"
                href="story.php?id=<?= (int)$story['id'] ?>&lang=<?= e($lang) ?>"
            >
                <?= $lang === 'hindi' ? 'कहानी पर वापस' : 'Back to Story' ?>
            </a>
        </div>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
