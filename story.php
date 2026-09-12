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
                class="zoom-mode-button active"
                aria-pressed="true"
                title="Click to turn image click-to-fullscreen mode on or off"
            >
                <span class="zoom-icon" aria-hidden="true">🔍</span>
                <span id="zoom-mode-text">ON</span>
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
            <span>← → Stories</span>
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

        <?php if ($previousStory): ?>

    <a
        class="stage-arrow stage-prev"
        href="story.php?id=<?= (int)$previousStory['id'] ?>&lang=<?= e($lang) ?>"
        aria-label="Previous story"
        title="Previous Story"
    >
        <span class="arrow-icon" aria-hidden="true">‹</span>
    </a>

<?php else: ?>

    <a
        class="stage-arrow stage-prev disabled"
        href="stories.php?lang=<?= e($lang) ?>"
        aria-label="Story index"
        title="Story Index"
    >
        <span class="arrow-icon" aria-hidden="true">‹</span>
    </a>

<?php endif; ?>


<?php if ($nextStory): ?>

    <a
        class="stage-arrow stage-next"
        href="story.php?id=<?= (int)$nextStory['id'] ?>&lang=<?= e($lang) ?>"
        aria-label="Next story"
        title="Next Story"
    >
        <span class="arrow-icon" aria-hidden="true">›</span>
    </a>

<?php else: ?>

    <a
        class="stage-arrow stage-next disabled"
        href="stories.php?lang=<?= e($lang) ?>"
        aria-label="Story index"
        title="Story Index"
    >
        <span class="arrow-icon" aria-hidden="true">›</span>
    </a>

<?php endif; ?>

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

<style>
@media (max-width: 620px) {
    .comic-viewer {
        touch-action: pan-y;
        overflow: hidden;
    }
    .comic-viewer.is-panning {
        cursor: grabbing;
    }
    .comic-page-image {
        touch-action: none;
        user-select: none;
        -webkit-user-drag: none;
    }
}
</style>


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

    const viewer = document.querySelector('.comic-viewer');
    const canvas = document.querySelector('.comic-canvas');
    const image = document.querySelector('.comic-page-image');

    const zoomModeButton = document.getElementById('zoom-mode-button');
    const zoomModeText = document.getElementById('zoom-mode-text');
    const zoomOutButton = document.getElementById('zoom-out-button');
    const zoomInButton = document.getElementById('zoom-in-button');
    const zoomResetButton = document.getElementById('zoom-reset-button');
    const zoomValue = document.getElementById('zoom-value');
    const fullscreenButton = document.getElementById('fullscreen-button');

    const comicData = window.CYBER_COMIC || {};
    const pages = Array.isArray(comicData.pages) ? comicData.pages : [];

    if (!viewer) return;

    let pageIndex = 0;
    let zoom = 1;
    let clickToFullscreen = true;
    let baseWidth = 0;
    let baseHeight = 0;

    // Mobile reader state.
    let panX = 0;
    let panY = 0;
    let touchMode = null;
    let lastTouchX = 0;
    let lastTouchY = 0;
    let pinchStartDistance = 0;
    let pinchStartZoom = 1;
    let pinchStartCenterX = 0;
    let pinchStartCenterY = 0;
    let pinchStartPanX = 0;
    let pinchStartPanY = 0;
    let movedDuringTouch = false;
    let suppressClickUntil = 0;

    const MIN_ZOOM = 0.5;
    const MAX_ZOOM = 3;
    const STEP = 0.25;

    function isMobile() {
        return window.matchMedia('(max-width: 620px)').matches;
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function getPanBounds() {
        if (!viewer || !baseWidth || !baseHeight) {
            return { x: 0, y: 0 };
        }

        const scaledWidth = baseWidth * zoom;
        const scaledHeight = baseHeight * zoom;

        // Image is centered inside the viewer. Allow exactly the amount
        // that can move before an empty area appears.
        return {
            x: Math.max(0, (scaledWidth - viewer.clientWidth) / 2),
            y: Math.max(0, (scaledHeight - viewer.clientHeight) / 2)
        };
    }

    function clampPan() {
        const bounds = getPanBounds();

        // At 100% there is nothing useful to pan.
        if (zoom <= 1) {
            panX = 0;
            panY = 0;
            return;
        }

        panX = clamp(panX, -bounds.x, bounds.x);
        panY = clamp(panY, -bounds.y, bounds.y);
    }

    function updateZoomButtons() {
        zoomOutButton.disabled = zoom <= MIN_ZOOM;
        zoomInButton.disabled = zoom >= MAX_ZOOM;
    }

    function updateCanvasSize() {
        if (!canvas || !image || !image.naturalWidth || !image.naturalHeight) return;

        const padding = 40;
        const availableWidth = Math.max(320, viewer.clientWidth - padding);
        const availableHeight = Math.max(240, viewer.clientHeight - padding);

        const naturalRatio = image.naturalWidth / image.naturalHeight;

        let width = Math.min(image.naturalWidth, availableWidth);
        let height = width / naturalRatio;

        if (height > availableHeight) {
            height = availableHeight;
            width = height * naturalRatio;
        }

        baseWidth = Math.max(1, Math.round(width));
        baseHeight = Math.max(1, Math.round(height));

        clampPan();
        applyZoom();
    }

    function applyZoom() {
        if (!canvas || !image || !baseWidth || !baseHeight) return;

        image.style.width = Math.round(baseWidth) + 'px';
        image.style.height = Math.round(baseHeight) + 'px';
        image.style.maxWidth = 'none';
        image.style.maxHeight = 'none';

        if (isMobile()) {
            /*
             * MOBILE MODE
             * The canvas stays centered.
             * The IMAGE itself is translated, so both horizontal and
             * vertical dragging work reliably on touch screens.
             */
            canvas.style.width = Math.round(baseWidth) + 'px';
            canvas.style.height = Math.round(baseHeight) + 'px';
            canvas.style.transform = 'none';

            image.style.transform =
                'translate3d(' + Math.round(panX) + 'px,' +
                Math.round(panY) + 'px,0) scale(' + zoom + ')';
            image.style.transformOrigin = 'center center';
        } else {
            // Desktop keeps the existing scroll/zoom style.
            canvas.style.width = Math.round(baseWidth * zoom) + 'px';
            canvas.style.height = Math.round(baseHeight * zoom) + 'px';
            canvas.style.transform = 'none';

            image.style.transform = 'scale(' + zoom + ')';
            image.style.transformOrigin = 'top left';
        }

        zoomValue.textContent = Math.round(zoom * 100) + '%';
        updateZoomButtons();
    }

    function setZoom(value, keepCenter = false, centerX = null, centerY = null) {
        const oldZoom = zoom;
        zoom = clamp(value, MIN_ZOOM, MAX_ZOOM);

        if (!keepCenter || oldZoom === zoom) {
            if (zoom <= 1) {
                panX = 0;
                panY = 0;
            }
            clampPan();
            applyZoom();
            return;
        }

        // Keep the area under the pinch center visually stable.
        if (centerX !== null && centerY !== null && oldZoom > 0) {
            const ratio = zoom / oldZoom;
            const viewerRect = viewer.getBoundingClientRect();
            const localX = centerX - viewerRect.left - viewer.clientWidth / 2;
            const localY = centerY - viewerRect.top - viewer.clientHeight / 2;

            panX = localX - (localX - panX) * ratio;
            panY = localY - (localY - panY) * ratio;
        }

        if (zoom <= 1) {
            panX = 0;
            panY = 0;
        }

        clampPan();
        applyZoom();
    }

    function showPage(index) {
        if (!image || !pages.length) return;

        pageIndex = Math.max(0, Math.min(pages.length - 1, index));
        panX = 0;
        panY = 0;
        zoom = 1;

        image.onload = function () {
            zoom = 1;
            panX = 0;
            panY = 0;
            updateCanvasSize();
        };

        image.src = pages[pageIndex].image;
        image.alt = (comicData.title || 'Cyber Heroes') +
            ' - Page ' + pages[pageIndex].number;

        if (image.complete) {
            updateCanvasSize();
        }

        const pageLabel = document.querySelector('[data-page-label]');
        if (pageLabel) {
            pageLabel.textContent =
                'Page ' + pages[pageIndex].number +
                ' of ' + pages.length;
        }

        viewer.scrollTop = 0;
        viewer.scrollLeft = 0;
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

        requestAnimationFrame(updateCanvasSize);
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
        requestAnimationFrame(updateCanvasSize);
    }

    async function toggleFullscreen() {
        if (document.fullscreenElement === viewer) {
            await exitFullscreen();
        } else {
            await enterFullscreen();
        }
    }

    zoomModeButton.addEventListener('click', function () {
        clickToFullscreen = !clickToFullscreen;

        zoomModeButton.classList.toggle('active', clickToFullscreen);
        zoomModeButton.setAttribute(
            'aria-pressed',
            clickToFullscreen ? 'true' : 'false'
        );
        zoomModeText.textContent = clickToFullscreen ? 'ON' : 'OFF';
    });

    zoomInButton.addEventListener('click', function () {
        setZoom(zoom + STEP);
    });

    zoomOutButton.addEventListener('click', function () {
        setZoom(zoom - STEP);
    });

    zoomResetButton.addEventListener('click', function () {
        zoom = 1;
        panX = 0;
        panY = 0;
        viewer.scrollTop = 0;
        viewer.scrollLeft = 0;
        applyZoom();
    });

    fullscreenButton.addEventListener('click', toggleFullscreen);

    /*
     * ================================================================
     * MOBILE IMAGE READER
     * ================================================================
     *
     * One finger  -> pan the image when zoomed
     * Two fingers -> pinch zoom
     * No swipe changes the story.
     */
    if (image) {
        let activePointers = new Map();
        let pointerStartDistance = 0;
        let pointerStartZoom = 1;
        let pointerStartPanX = 0;
        let pointerStartPanY = 0;
        let pointerStartCenterX = 0;
        let pointerStartCenterY = 0;
        let pointerLastX = 0;
        let pointerLastY = 0;

        function pointerDistance() {
            const points = Array.from(activePointers.values());
            if (points.length < 2) return 0;

            return Math.hypot(
                points[1].x - points[0].x,
                points[1].y - points[0].y
            );
        }

        function pointerCenter() {
            const points = Array.from(activePointers.values());
            if (points.length < 2) return null;

            return {
                x: (points[0].x + points[1].x) / 2,
                y: (points[0].y + points[1].y) / 2
            };
        }

        image.addEventListener('pointerdown', function (event) {
            if (!isMobile()) return;

            image.setPointerCapture?.(event.pointerId);

            activePointers.set(event.pointerId, {
                x: event.clientX,
                y: event.clientY
            });

            movedDuringTouch = false;

            if (activePointers.size === 1) {
                pointerLastX = event.clientX;
                pointerLastY = event.clientY;

                if (zoom > 1) {
                    touchMode = 'pan';
                } else {
                    touchMode = null;
                }
            }

            if (activePointers.size === 2) {
                touchMode = 'pinch';

                pointerStartDistance = pointerDistance();
                pointerStartZoom = zoom;
                pointerStartPanX = panX;
                pointerStartPanY = panY;

                const center = pointerCenter();
                if (center) {
                    pointerStartCenterX = center.x;
                    pointerStartCenterY = center.y;
                }

                event.preventDefault();
            }
        }, { passive: false });

        image.addEventListener('pointermove', function (event) {
            if (!isMobile() || !activePointers.has(event.pointerId)) return;

            activePointers.set(event.pointerId, {
                x: event.clientX,
                y: event.clientY
            });

            if (activePointers.size >= 2) {
                touchMode = 'pinch';

                const distance = pointerDistance();
                const center = pointerCenter();

                if (distance > 0 && center && pointerStartDistance > 0) {
                    const targetZoom = clamp(
                        pointerStartZoom *
                        (distance / pointerStartDistance),
                        MIN_ZOOM,
                        MAX_ZOOM
                    );

                    zoom = targetZoom;

                    // Keep the point under the fingers approximately fixed.
                    const ratio = zoom / pointerStartZoom;
                    const rect = viewer.getBoundingClientRect();

                    const localX =
                        center.x - rect.left - viewer.clientWidth / 2;
                    const localY =
                        center.y - rect.top - viewer.clientHeight / 2;

                    panX =
                        localX -
                        (localX - pointerStartPanX) * ratio;

                    panY =
                        localY -
                        (localY - pointerStartPanY) * ratio;

                    if (zoom <= 1) {
                        panX = 0;
                        panY = 0;
                    }

                    clampPan();
                    applyZoom();
                    movedDuringTouch = true;
                }

                event.preventDefault();
                return;
            }

            if (
                activePointers.size === 1 &&
                touchMode === 'pan' &&
                zoom > 1
            ) {
                const dx = event.clientX - pointerLastX;
                const dy = event.clientY - pointerLastY;

                if (Math.abs(dx) > 0 || Math.abs(dy) > 0) {
                    panX += dx;
                    panY += dy;

                    clampPan();
                    applyZoom();

                    pointerLastX = event.clientX;
                    pointerLastY = event.clientY;

                    movedDuringTouch = true;
                }

                event.preventDefault();
            }
        }, { passive: false });

        function endPointer(event) {
            if (!isMobile()) return;

            activePointers.delete(event.pointerId);

            if (movedDuringTouch) {
                suppressClickUntil = Date.now() + 500;
            }

            if (activePointers.size === 0) {
                touchMode = null;
                pointerStartDistance = 0;
            } else if (activePointers.size === 1 && zoom > 1) {
                const remaining = Array.from(activePointers.values())[0];

                touchMode = 'pan';
                pointerLastX = remaining.x;
                pointerLastY = remaining.y;
            }
        }

        image.addEventListener('pointerup', endPointer, { passive: true });
        image.addEventListener('pointercancel', endPointer, { passive: true });

        image.addEventListener('click', function () {
            if (Date.now() < suppressClickUntil) return;

            if (clickToFullscreen) {
                toggleFullscreen();
            }
        });

        image.addEventListener('dblclick', function (event) {
            event.preventDefault();
        });
    }

    document.addEventListener('fullscreenchange', function () {
        const active = document.fullscreenElement === viewer;
        viewer.classList.toggle('is-fullscreen', active);

        if (fullscreenButton) {
            fullscreenButton.querySelector('span:last-child').textContent =
                active ? 'Exit Fullscreen' : 'Fullscreen';
        }

        requestAnimationFrame(updateCanvasSize);
    });

    document.addEventListener('keydown', function (event) {
        const tag = document.activeElement
            ? document.activeElement.tagName
            : '';

        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;

        if (event.key === 'ArrowLeft') {
            const previousLink =
                document.querySelector('.stage-prev:not(.disabled)');

            if (previousLink) {
                event.preventDefault();
                window.location.href = previousLink.href;
            }
        }

        if (event.key === 'ArrowRight') {
            const nextLink =
                document.querySelector('.stage-next:not(.disabled)');

            if (nextLink) {
                event.preventDefault();
                window.location.href = nextLink.href;
            }
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

    if (pages.length) {
        showPage(0);
    }
})();
</script>


<style>
/* ================================================================
   MOBILE COMIC READER
   Separate mobile interaction: pinch-to-zoom + one-finger pan
   ================================================================ */
@media (max-width: 620px) {
    .comic-viewer {
        position: relative;
        overflow: hidden !important;
        touch-action: none !important;
        overscroll-behavior: contain;
    }

    .comic-canvas {
        position: relative;
        flex: 0 0 auto;
        margin: 0 auto;
        will-change: transform;
    }

    .comic-page-image {
        display: block;
        margin: 0;
        touch-action: none !important;
        user-select: none;
        -webkit-user-select: none;
        -webkit-user-drag: none;
        will-change: transform;
        cursor: grab;
    }

    .comic-page-image:active {
        cursor: grabbing;
    }

    /* Keep the story arrows as story navigation only. */
    .stage-arrow {
        touch-action: manipulation !important;
        z-index: 20;
    }
}
    .comic-viewer,
    .comic-canvas,
    .comic-page-image {
        touch-action: none !important;
    }

    .comic-page-image {
        pointer-events: auto;
        -webkit-user-drag: none;
        user-select: none;
        -webkit-user-select: none;
    }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>