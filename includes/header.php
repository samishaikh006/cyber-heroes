<?php
require_once __DIR__ . '/../config/app.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$lang = (isset($_GET['lang']) && $_GET['lang'] === 'hindi')
    ? 'hindi'
    : 'english';
?>

<!doctype html>
<html lang="<?= $lang === 'hindi' ? 'hi' : 'en' ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="<?= e(APP_NAME) ?> — learn cybersecurity through interactive comics, quizzes and videos."
    >

    <title><?= e($pageTitle ?? APP_NAME) ?></title>


    <!-- =========================================================
         MAIN WEBSITE CSS
    ========================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- =========================================================
         QUIZ CSS
    ========================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/quiz.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/quiz_index.css"
    >

    <link rel="stylesheet" href="assets/css/cyber_mobile_reader_fix.css">

</head>


<body>


<header class="site-header">

    <div class="nav-shell">


        <!-- =====================================================
             LOGO
        ====================================================== -->

        <a
            class="brand"
            href="index.php"
            aria-label="Cyber Security Comic Book Home"
        >

            <span class="brand-mark">⌁</span>

            <span>
                CYBER <strong>HEROES</strong>
            </span>

        </a>


        <!-- =====================================================
             NAVIGATION
        ====================================================== -->

        <nav
            class="site-nav"
            aria-label="Main navigation"
        >

            <!-- HOME -->

            <a
                class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"
                href="index.php?lang=<?= e($lang) ?>"
            >
                Home
            </a>


            <!-- STORIES -->

            <a
                class="<?= (
                    $currentPage === 'stories.php' ||
                    $currentPage === 'story.php'
                ) ? 'active' : '' ?>"
                href="stories.php?lang=<?= e($lang) ?>"
            >
                Stories
            </a>


            <!-- QUIZ -->

            <a
                class="<?= (
                    $currentPage === 'quiz_index.php' ||
                    $currentPage === 'quiz.php'
                ) ? 'active' : '' ?>"
                href="quiz_index.php?lang=<?= e($lang) ?>"
            >
                Quiz
            </a>


            <!-- VIDEOS -->

            <a
                class="<?= $currentPage === 'videos.php' ? 'active' : '' ?>"
                href="videos.php?lang=<?= e($lang) ?>"
            >
                Videos
            </a>

        </nav>


        <!-- =====================================================
             LANGUAGE SWITCH
        ====================================================== -->

        <div
            class="language-switch"
            aria-label="Language selection"
        >

            <a
                class="<?= $lang === 'english' ? 'active' : '' ?>"
                href="<?= e($currentPage) ?>?lang=english"
            >
                EN
            </a>


            <a
                class="<?= $lang === 'hindi' ? 'active' : '' ?>"
                href="<?= e($currentPage) ?>?lang=hindi"
            >
                हिं
            </a>

        </div>


    </div>

</header>


<main>