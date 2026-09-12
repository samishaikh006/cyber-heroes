<?php
$pageTitle = 'Videos | Cyber Heroes';
require __DIR__ . '/includes/header.php';

/*
|--------------------------------------------------------------------------
| Cyber Heroes Videos
|--------------------------------------------------------------------------
| Add new videos at the bottom of this array.
| The "order" in this array is the sequence shown on the website.
*/
$videos = [
    [
        'number' => 1,
        'title' => 'OTP Scam',
        'description' => 'Learn how OTP scams work and how to stay safe from fraudulent requests.',
        'category' => 'OTP Scam',
        'youtube_id' => 'Xa1f2i7CsM4',
    ],
    [
        'number' => 2,
        'title' => 'What is Phishing?',
        'description' => 'Understand phishing attacks and how to identify suspicious messages and links.',
        'category' => 'Phishing',
        'youtube_id' => 'daV3thmt6Gg',
    ],
    [
        'number' => 3,
        'title' => 'Personal Information & Online Privacy',
        'description' => 'Learn why protecting personal information and online privacy is important.',
        'category' => 'Privacy',
        'youtube_id' => '-yV_VYfWtRM',
    ],
    [
        'number' => 4,
        'title' => 'Smshing',
        'description' => 'Learn about SMS-based phishing attacks and how to recognize dangerous messages.',
        'category' => 'Smshing',
        'youtube_id' => 'tMSrVpLPVlM',
    ],
    [
        'number' => 5,
        'title' => 'Password Security',
        'description' => 'Learn how strong passwords help protect your accounts from cyber attacks.',
        'category' => 'Password Security',
        'youtube_id' => 'kiBB0m_MY-Q',
    ],
];
?>

<section class="videos-page section-shell">

    <!-- HERO -->
    <div class="videos-hero">
        <span class="eyebrow">WATCH & LEARN</span>

        <h1>Cybersecurity Videos</h1>

        <p>
            Learn important cybersecurity concepts through short and useful
            videos. Watch them in sequence and become a Cyber Hero.
        </p>
    </div>

    <!-- VIDEO LIST -->
    <div class="video-list">

        <?php foreach ($videos as $video): ?>

            <article class="video-card">

                <div class="video-number">
                    <?= str_pad((string)$video['number'], 2, '0', STR_PAD_LEFT) ?>
                </div>

                <div class="video-player">
                    <iframe
                        src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>"
                        title="<?= e($video['title']) ?>"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>

                <div class="video-content">

                    <span class="video-category">
                        <?= e($video['category']) ?>
                    </span>

                    <h2><?= e($video['title']) ?></h2>

                    <p><?= e($video['description']) ?></p>

                    <a
                        class="watch-youtube"
                        href="https://www.youtube.com/watch?v=<?= e($video['youtube_id']) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Watch on YouTube ↗
                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    </div>

    <div class="videos-end">
        <span>MORE CYBERSECURITY VIDEOS</span>
        <p>More videos will be added here soon.</p>
    </div>

</section>

<style>
/* =========================================================
   CYBER HEROES VIDEO SECTION
   ========================================================= */

.videos-page {
    padding-top: 70px;
    padding-bottom: 80px;
}

.videos-hero {
    max-width: 820px;
    margin: 0 auto 45px;
    text-align: center;
}

.videos-hero h1 {
    margin: 10px 0 14px;
}

.videos-hero p {
    max-width: 680px;
    margin: 0 auto;
    color: var(--muted);
    line-height: 1.7;
}

/* VIDEO LIST */

.video-list {
    max-width: 1050px;
    margin: 0 auto;
    display: grid;
    gap: 28px;
}

/* CARD */

.video-card {
    position: relative;
    display: grid;
    grid-template-columns: minmax(360px, 1.25fr) 1fr;
    gap: 28px;
    padding: 20px;
    border: 1px solid var(--line);
    border-radius: 16px;
    background: rgba(13, 41, 66, .65);
    box-shadow: 0 18px 50px rgba(0, 0, 0, .18);
    overflow: hidden;
}

.video-card:hover {
    border-color: var(--cyan);
}

/* NUMBER */

.video-number {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 2;

    min-width: 42px;
    height: 30px;
    padding: 0 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 1px solid var(--cyan);
    border-radius: 7px;

    background: rgba(2, 11, 20, .92);
    color: var(--cyan);

    font-size: 12px;
    font-weight: 900;
    letter-spacing: 1px;
}

/* YOUTUBE */

.video-player {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    border-radius: 11px;
    background: #000;
}

.video-player iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}

/* CONTENT */

.video-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 10px 8px;
}

.video-category {
    width: fit-content;
    margin-bottom: 10px;
    padding: 5px 9px;
    border: 1px solid var(--line);
    border-radius: 6px;
    color: var(--cyan);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.video-content h2 {
    margin: 0 0 12px;
    font-size: clamp(22px, 3vw, 30px);
}

.video-content p {
    margin: 0 0 20px;
    color: var(--muted);
    line-height: 1.65;
}

.watch-youtube {
    width: fit-content;
    padding: 10px 14px;
    border: 1px solid var(--line);
    border-radius: 8px;
    color: var(--text);
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
}

.watch-youtube:hover {
    border-color: var(--cyan);
    color: var(--cyan);
}

/* END */

.videos-end {
    margin: 45px auto 0;
    text-align: center;
    color: var(--muted);
}

.videos-end span {
    color: var(--cyan);
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.5px;
}

.videos-end p {
    margin-top: 8px;
}

/* MOBILE */

@media (max-width: 850px) {
    .videos-page {
        padding-top: 50px;
    }

    .video-card {
        grid-template-columns: 1fr;
        gap: 18px;
        padding: 14px;
    }

    .video-content {
        padding: 4px 6px 8px;
    }
}

@media (max-width: 620px) {
    .videos-page {
        padding-top: 40px;
        padding-bottom: 55px;
    }

    .videos-hero {
        margin-bottom: 30px;
    }

    .videos-hero h1 {
        font-size: 30px;
    }

    .videos-hero p {
        font-size: 14px;
    }

    .video-list {
        gap: 20px;
    }

    .video-card {
        border-radius: 12px;
    }

    .video-content h2 {
        font-size: 22px;
    }

    .video-content p {
        font-size: 14px;
    }
}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
