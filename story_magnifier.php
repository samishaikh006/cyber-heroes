<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$lang = (isset($_GET['lang']) && $_GET['lang'] === 'hindi')
    ? 'hindi'
    : 'english';

if (!$id) {
    header('Location: stories.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Story
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare(
    'SELECT * FROM stories
     WHERE id = ? AND is_active = 1
     LIMIT 1'
);

$stmt->execute([$id]);
$story = $stmt->fetch();

if (!$story) {
    header('Location: stories.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Previous Story
|--------------------------------------------------------------------------
*/
$prevStmt = $pdo->prepare(
    'SELECT id, story_number, title_en, title_hi
     FROM stories
     WHERE is_active = 1
       AND story_number < ?
     ORDER BY story_number DESC
     LIMIT 1'
);

$prevStmt->execute([(int)$story['story_number']]);
$previousStory = $prevStmt->fetch();

/*
|--------------------------------------------------------------------------
| Next Story
|--------------------------------------------------------------------------
*/
$nextStmt = $pdo->prepare(
    'SELECT id, story_number, title_en, title_hi
     FROM stories
     WHERE is_active = 1
       AND story_number > ?
     ORDER BY story_number ASC
     LIMIT 1'
);

$nextStmt->execute([(int)$story['story_number']]);
$nextStory = $nextStmt->fetch();

/*
|--------------------------------------------------------------------------
| Comic Pages
|--------------------------------------------------------------------------
*/
$pageStmt = $pdo->prepare(
    'SELECT page_number, image_en, image_hi
     FROM story_pages
     WHERE story_id = ?
     ORDER BY page_number ASC'
);

$pageStmt->execute([(int)$story['id']]);
$pages = $pageStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Build Correct Image URLs
|--------------------------------------------------------------------------
|
| Database stores:
|
| uploads/stories/story-01/en/1.png
|
| Browser needs:
|
| /cyber_security_comic_v1/uploads/stories/story-01/en/1.png
|
|--------------------------------------------------------------------------
*/

/**
 * Convert a database image path into a browser-safe URL.
 */
function comicImageUrl(string $path): string
{
    $path = trim($path);

    if ($path === '') {
        return '';
    }

    /*
     * If it is already a complete URL, keep it.
     */
    if (
        str_starts_with($path, 'http://') ||
        str_starts_with($path, 'https://') ||
        str_starts_with($path, 'data:')
    ) {
        return $path;
    }

    /*
     * Project folder.
     *
     * story.php is located directly inside:
     *
     * /cyber_security_comic_v1/
     */
    $projectPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

    /*
     * Remove accidental leading slash.
     */
    $path = ltrim($path, '/');

    return $projectPath . '/' . $path;
}

$comicPages = [];

foreach ($pages as $page) {

    /*
     * English selected
     */
    if ($lang === 'english') {

        $image = !empty($page['image_en'])
            ? $page['image_en']
            : $page['image_hi'];

    /*
     * Hindi selected
     */
    } else {

        $image = !empty($page['image_hi'])
            ? $page['image_hi']
            : $page['image_en'];
    }

    if (!empty($image)) {

        $comicPages[] = [
            'number' => (int)$page['page_number'],
            'image'  => comicImageUrl((string)$image)
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Page Title
|--------------------------------------------------------------------------
*/
$pageTitle =
    ($lang === 'hindi'
        ? $story['title_hi']
        : $story['title_en'])
    . ' | Cyber Heroes';

require __DIR__ . '/includes/header.php';
?>

<section class="story-reader section-shell">

    <!-- =========================================================
         TOP BAR
    ========================================================== -->

    <div class="reader-topbar">

        <a
            class="reader-back"
            href="stories.php?lang=<?= e($lang) ?>"
        >
            ← Stories
        </a>

        <span class="reader-story-label">
            STORY
            <?= str_pad(
                (string)$story['story_number'],
                2,
                '0',
                STR_PAD_LEFT
            ) ?>
        </span>

        <a
            class="reader-quiz-link"
            href="quiz.php?id=<?= (int)$story['id'] ?>&lang=<?= e($lang) ?>"
        >
            Quiz →
        </a>

    </div>


    <!-- =========================================================
         STORY HEADING
    ========================================================== -->

    <div class="reader-heading">

        <div class="reader-title-block">

            <span class="eyebrow">
                CYBER HEROES • COMIC READER
            </span>

            <h1>
                <?= e(
                    $lang === 'hindi'
                        ? $story['title_hi']
                        : $story['title_en']
                ) ?>
            </h1>

            <p>
                <?= e(
                    $lang === 'hindi'
                        ? $story['description_hi']
                        : $story['description_en']
                ) ?>
            </p>

        </div>


        <!-- =====================================================
             LANGUAGE SWITCH
        ====================================================== -->

        <div
            class="reader-language"
            aria-label="Language selection"
        >

            <a
                class="<?= $lang === 'english' ? 'active' : '' ?>"
                href="story.php?id=<?= (int)$story['id'] ?>&lang=english"
            >
                English
            </a>

            <a
                class="<?= $lang === 'hindi' ? 'active' : '' ?>"
                href="story.php?id=<?= (int)$story['id'] ?>&lang=hindi"
            >
                हिंदी
            </a>

        </div>

    </div>


    <!-- =========================================================
         COMIC TOOLBAR
    ========================================================== -->

    <div class="reader-toolbar" aria-label="Comic controls">

        <div class="reader-tool-group">

            <button
                type="button"
                id="zoom-mode-button"
                class="zoom-mode-button"
                aria-pressed="false"
                title="Turn magnifier on"
            >
                <span class="zoom-icon" aria-hidden="true">🔍</span>
                <span id="zoom-mode-text">Magnify</span>
            </button>

            <button
                type="button"
                id="zoom-out-button"
                title="Zoom out"
            >−</button>

            <span class="zoom-value" id="zoom-value">100%</span>

            <button
                type="button"
                id="zoom-in-button"
                title="Zoom in"
            >+</button>

            <button
                type="button"
                id="zoom-reset-button"
                title="Reset zoom"
            >Reset</button>

        </div>

        <div class="reader-tool-group reader-shortcuts">
            <span>← → Pages</span>
            <span>+ − Zoom</span>
            <span>F Fullscreen</span>
        </div>

        <button
            class="fullscreen-button"
            id="fullscreen-button"
            type="button"
            title="Open comic in fullscreen (F)"
        >
            <span aria-hidden="true">⛶</span>
            <span>Fullscreen</span>
        </button>

    </div>


    <!-- =========================================================
         COMIC VIEWER
    ========================================================== -->

    <div
        class="comic-stage"
        data-comic-stage
    >

        <div
            class="comic-viewer"
            tabindex="0"
            aria-label="Comic reader"
        >

            <div class="comic-magnifier" id="comic-magnifier" aria-hidden="true"></div>

            <?php if (!empty($comicPages)): ?>

                <div
                    class="comic-canvas"
                >

                    <img
                        class="comic-page-image"
                        src="<?= e($comicPages[0]['image']) ?>"
                        alt="<?= e(
                            ($lang === 'hindi'
                                ? $story['title_hi']
                                : $story['title_en'])
                            . ' - Page '
                            . $comicPages[0]['number']
                        ) ?>"
                        draggable="false"
                    >

                </div>

            <?php else: ?>

                <!-- =================================================
                     EMPTY STATE
                ================================================== -->

                <div class="comic-empty">

                    <div class="comic-empty-icon">
                        ▧
                    </div>

                    <span>
                        COMIC PAGES NOT ADDED YET
                    </span>

                    <strong>
                        Story
                        <?= str_pad(
                            (string)$story['story_number'],
                            2,
                            '0',
                            STR_PAD_LEFT
                        ) ?>
                    </strong>

                    <p>
                        Add comic pages to the
                        <code>story_pages</code>
                        table and they will appear here automatically.
                    </p>

                </div>

            <?php endif; ?>

        </div>


        <!-- =====================================================
             PAGE ARROWS
        ====================================================== -->

        <button
            class="stage-arrow stage-prev"
            type="button"
            aria-label="Previous page"
        >
            ‹
        </button>

        <button
            class="stage-arrow stage-next"
            type="button"
            aria-label="Next page"
        >
            ›
        </button>

    </div>


    <!-- =========================================================
         PAGE STATUS
    ========================================================== -->

    <div class="reader-status">

        <span data-page-label>

            <?php if (!empty($comicPages)): ?>

                Page 1 of <?= count($comicPages) ?>

            <?php else: ?>

                No comic pages

            <?php endif; ?>

        </span>

        <span class="reader-dot">
            •
        </span>

        <span data-story-label>

            Story
            <?= str_pad(
                (string)$story['story_number'],
                2,
                '0',
                STR_PAD_LEFT
            ) ?>

        </span>

    </div>


    <!-- =========================================================
         STORY NAVIGATION
    ========================================================== -->

    <div class="reader-navigation">

        <?php if ($previousStory): ?>

            <a
                class="btn btn-secondary"
                href="story.php?id=<?= (int)$previousStory['id'] ?>&lang=<?= e($lang) ?>"
            >
                ← Previous Story
            </a>

        <?php else: ?>

            <a
                class="btn btn-secondary"
                href="stories.php?lang=<?= e($lang) ?>"
            >
                ← Story Index
            </a>

        <?php endif; ?>


        <a
            class="btn btn-primary"
            href="quiz.php?id=<?= (int)$story['id'] ?>&lang=<?= e($lang) ?>"
        >
            Take Story Quiz →
        </a>


        <?php if ($nextStory): ?>

            <a
                class="btn btn-secondary"
                href="story.php?id=<?= (int)$nextStory['id'] ?>&lang=<?= e($lang) ?>"
            >
                Next Story →
            </a>

        <?php else: ?>

            <a
                class="btn btn-secondary"
                href="stories.php?lang=<?= e($lang) ?>"
            >
                Back to Index →
            </a>

        <?php endif; ?>

    </div>

</section>


<!-- =============================================================
     COMIC DATA FOR JAVASCRIPT
============================================================== -->

<script>
window.CYBER_COMIC = <?= json_encode(
    [
        'pages' => $comicPages,
        'title' => $lang === 'hindi'
            ? $story['title_hi']
            : $story['title_en'],
        'language' => $lang,
        'storyId' => (int)$story['id'],
        'storyNumber' => (int)$story['story_number']
    ],
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
) ?>;
</script>



<script>
(function () {
    'use strict';

    // This reader owns its controls. It does not depend on footer JavaScript.
    const viewer = document.querySelector('.comic-viewer');
    const canvas = document.querySelector('.comic-canvas');
    const image = document.querySelector('.comic-page-image');

    const zoomModeButton = document.getElementById('zoom-mode-button');
    const zoomModeText = document.getElementById('zoom-mode-text');
    const magnifier = document.getElementById('comic-magnifier');
    const zoomOutButton = document.getElementById('zoom-out-button');
    const zoomInButton = document.getElementById('zoom-in-button');
    const zoomResetButton = document.getElementById('zoom-reset-button');
    const zoomValue = document.getElementById('zoom-value');
    const fullscreenButton = document.getElementById('fullscreen-button');

    const prevButton = document.querySelector('.stage-prev');
    const nextButton = document.querySelector('.stage-next');
    const pageLabel = document.querySelector('[data-page-label]');

    const comicData = window.CYBER_COMIC || {};
    const pages = Array.isArray(comicData.pages) ? comicData.pages : [];

    if (!viewer) return;

    let pageIndex = 0;
    let zoom = 1;
    let magnifierEnabled = false;
    let baseWidth = 0;
    let baseHeight = 0;
    let lastPointerEvent = null;
    const MAGNIFICATION = 2.5;
    const LENS_SIZE = 190;

    const MIN_ZOOM = 0.5;
    const MAX_ZOOM = 3;
    const STEP = 0.25;

    function updateZoomButtons() {
        zoomOutButton.disabled = zoom <= MIN_ZOOM;
        zoomInButton.disabled = zoom >= MAX_ZOOM;
    }

    function updateCanvasSize() {
        if (!canvas || !image || !image.naturalWidth || !image.naturalHeight) return;

        const padding = 40;
        const availableWidth = Math.max(320, viewer.clientWidth - padding);
        const availableHeight = Math.max(240, window.innerHeight * 0.72);
        const naturalRatio = image.naturalWidth / image.naturalHeight;

        // 100% = fit the comic inside the reader frame.
        let width = Math.min(image.naturalWidth, availableWidth);
        let height = width / naturalRatio;

        if (height > availableHeight) {
            height = availableHeight;
            width = height * naturalRatio;
        }

        baseWidth = Math.max(1, Math.round(width));
        baseHeight = Math.max(1, Math.round(height));

        applyZoom();
    }

    function applyZoom() {
        if (!canvas || !image || !baseWidth || !baseHeight) return;

        const scaledWidth = Math.round(baseWidth * zoom);
        const scaledHeight = Math.round(baseHeight * zoom);
        const framePadding = 40;

        image.style.width = scaledWidth + 'px';
        image.style.height = scaledHeight + 'px';
        image.style.maxWidth = 'none';
        image.style.maxHeight = 'none';
        image.style.transform = 'none';

        // The reader frame follows the zoomed comic size.
        // No internal scrollbar is used. The normal browser page can scroll
        // when the zoomed comic becomes larger than the screen.
        canvas.style.width = scaledWidth + 'px';
        canvas.style.height = scaledHeight + 'px';
        canvas.style.margin = '0 auto';

        viewer.style.minHeight = Math.max(620, scaledHeight + framePadding) + 'px';
        viewer.style.height = Math.max(620, scaledHeight + framePadding) + 'px';
        viewer.style.overflow = 'hidden';

        // Keep the frame wide enough for the zoomed comic without forcing
        // a horizontal scrollbar inside the reader.
        if (zoom > 1) {
            viewer.classList.add('zoomed-frame');
        } else {
            viewer.classList.remove('zoomed-frame');
        }

        zoomValue.textContent = Math.round(zoom * 100) + '%';
        updateZoomButtons();

        // Re-position the magnifier if it is currently visible.
        if (magnifier && magnifierEnabled && lastPointerEvent) {
            moveMagnifier(lastPointerEvent);
        }
    }

    function setZoom(value) {
        zoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, value));
        applyZoom();
    }

    function updatePageButtons() {
        if (!prevButton || !nextButton) return;

        const disabled = pages.length <= 1;
        prevButton.disabled = disabled || pageIndex <= 0;
        nextButton.disabled = disabled || pageIndex >= pages.length - 1;
    }

    function showPage(index) {
        if (!image || !pages.length) return;

        pageIndex = Math.max(0, Math.min(pages.length - 1, index));

        image.onload = function () {
            zoom = 1;
            updateCanvasSize();
        };

        image.src = pages[pageIndex].image;
        image.alt = (comicData.title || 'Cyber Heroes') + ' - Page ' + pages[pageIndex].number;

        if (image.complete) {
            zoom = 1;
            updateCanvasSize();
        }

        if (pageLabel) {
            pageLabel.textContent = 'Page ' + pages[pageIndex].number + ' of ' + pages.length;
        }

        updatePageButtons();
        if (magnifier) magnifier.classList.remove('visible');
    }

    async function enterFullscreen() {
        try {
            if (!document.fullscreenElement) {
                viewer.classList.add('is-fullscreen');
                await viewer.requestFullscreen();
            }
        } catch (error) {
            viewer.classList.add('is-fullscreen');
            console.error('Fullscreen could not be opened:', error);
        }

        requestAnimationFrame(function () {
            updateCanvasSize();
        });
    }

    async function exitFullscreen() {
        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
            }
        } catch (error) {
            console.error('Fullscreen could not be closed:', error);
        }

        viewer.classList.remove('is-fullscreen');

        requestAnimationFrame(function () {
            updateCanvasSize();
        });
    }

    async function toggleFullscreen() {
        if (document.fullscreenElement === viewer) {
            await exitFullscreen();
        } else {
            await enterFullscreen();
        }
    }

    // Magnifier: turn it on, then move the pointer over any part of the comic.
    // Only the area under the pointer is enlarged; the whole comic does not resize.
    function setMagnifierEnabled(enabled) {
        magnifierEnabled = enabled;

        zoomModeButton.classList.toggle('active', enabled);
        zoomModeButton.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        zoomModeText.textContent = enabled ? 'Magnify ON' : 'Magnify';
        zoomModeButton.title = enabled
            ? 'Magnifier is ON — move over the comic'
            : 'Turn magnifier on';

        if (!enabled && magnifier) {
            magnifier.classList.remove('visible');
        }
    }

    function moveMagnifier(event) {
        lastPointerEvent = event;
        if (!magnifierEnabled || !magnifier || !image) return;

        const rect = image.getBoundingClientRect();
        if (!rect.width || !rect.height) return;

        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
            magnifier.classList.remove('visible');
            return;
        }

        const viewerRect = viewer.getBoundingClientRect();
        const lensLeft = event.clientX - viewerRect.left - LENS_SIZE / 2;
        const lensTop = event.clientY - viewerRect.top - LENS_SIZE / 2;

        magnifier.style.left = lensLeft + 'px';
        magnifier.style.top = lensTop + 'px';

        // The lens uses the same image as a background, scaled 2.5×,
        // so only the selected area is enlarged.
        const scaledWidth = rect.width * MAGNIFICATION;
        const scaledHeight = rect.height * MAGNIFICATION;
        const bgX = -(x * MAGNIFICATION - LENS_SIZE / 2);
        const bgY = -(y * MAGNIFICATION - LENS_SIZE / 2);

        magnifier.style.backgroundImage = 'url("' + image.currentSrc.replace(/"/g, '\\"') + '")';
        magnifier.style.backgroundSize = scaledWidth + 'px ' + scaledHeight + 'px';
        magnifier.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
        magnifier.classList.add('visible');
    }

    zoomModeButton.addEventListener('click', function () {
        setMagnifierEnabled(!magnifierEnabled);
    });

    if (image) {
        image.addEventListener('mouseenter', moveMagnifier);
        image.addEventListener('mousemove', moveMagnifier);
        image.addEventListener('mouseleave', function () {
            if (magnifier) magnifier.classList.remove('visible');
        });
    }

    zoomInButton.addEventListener('click', function () {
        setZoom(zoom + STEP);
    });

    zoomOutButton.addEventListener('click', function () {
        setZoom(zoom - STEP);
    });

    zoomResetButton.addEventListener('click', function () {
        setZoom(1);
    });

    fullscreenButton.addEventListener('click', toggleFullscreen);

    if (image) {
        image.addEventListener('dblclick', function (event) {
            event.preventDefault();
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            if (pageIndex > 0) showPage(pageIndex - 1);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            if (pageIndex < pages.length - 1) showPage(pageIndex + 1);
        });
    }

    document.addEventListener('fullscreenchange', function () {
        const active = document.fullscreenElement === viewer;
        viewer.classList.toggle('is-fullscreen', active);

        if (fullscreenButton) {
            fullscreenButton.querySelector('span:last-child').textContent =
                active ? 'Exit Fullscreen' : 'Fullscreen';
            fullscreenButton.title =
                active ? 'Exit fullscreen (Esc)' : 'Open comic in fullscreen (F)';
        }

        requestAnimationFrame(function () {
            updateCanvasSize();
        });
    });

    document.addEventListener('keydown', function (event) {
        const tag = document.activeElement ? document.activeElement.tagName : '';
        const typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

        if (typing) return;

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            if (pageIndex > 0) showPage(pageIndex - 1);
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            if (pageIndex < pages.length - 1) showPage(pageIndex + 1);
        }

        if (event.key === '+' || event.key === '=') {
            event.preventDefault();
            setZoom(zoom + STEP);
        }

        if (event.key === '-' || event.key === '_') {
            event.preventDefault();
            setZoom(zoom - STEP);
        }

        if (event.key.toLowerCase() === 'f') {
            event.preventDefault();
            toggleFullscreen();
        }
    });

    window.addEventListener('resize', function () {
        updateCanvasSize();
    });

    // Initial page.
    if (pages.length) {
        showPage(0);
    } else {
        updatePageButtons();
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>