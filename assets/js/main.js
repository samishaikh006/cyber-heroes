(() => {
    const viewer = document.querySelector('[data-comic-viewer]');
    if (!viewer) return;

    const image = document.querySelector('[data-comic-image]');
    const canvas = document.querySelector('[data-comic-canvas]');
    const zoomValue = document.querySelector('[data-zoom-value]');
    const pageLabel = document.querySelector('[data-page-label]');
    const prevPage = document.querySelector('[data-page-prev]');
    const nextPage = document.querySelector('[data-page-next]');
    const fullscreenButton = document.querySelector('[data-fullscreen]');
    const pages = Array.isArray(window.CYBER_COMIC?.pages) ? window.CYBER_COMIC.pages : [];

    let zoom = 1;
    let currentPage = 0;

    const clampZoom = value => Math.max(0.5, Math.min(3, value));

    function renderZoom() {
        if (canvas) canvas.style.setProperty('--comic-scale', String(zoom));
        if (zoomValue) zoomValue.textContent = `${Math.round(zoom * 100)}%`;
    }

    function renderPage() {
        if (!pages.length || !image) {
            if (prevPage) prevPage.disabled = true;
            if (nextPage) nextPage.disabled = true;
            return;
        }

        const page = pages[currentPage];
        image.src = page.image;
        image.alt = `${window.CYBER_COMIC.title} - Page ${page.number}`;
        if (pageLabel) pageLabel.textContent = `Page ${currentPage + 1} of ${pages.length}`;

        if (prevPage) prevPage.disabled = currentPage === 0;
        if (nextPage) nextPage.disabled = currentPage === pages.length - 1;

        viewer.scrollTo({ left: 0, top: 0, behavior: 'smooth' });
        zoom = 1;
        renderZoom();
    }

    function changePage(direction) {
        if (!pages.length) return;
        const next = currentPage + direction;
        if (next < 0 || next >= pages.length) return;
        currentPage = next;
        renderPage();
    }

    document.querySelector('[data-zoom-in]')?.addEventListener('click', () => {
        zoom = clampZoom(zoom + 0.25);
        renderZoom();
    });

    document.querySelector('[data-zoom-out]')?.addEventListener('click', () => {
        zoom = clampZoom(zoom - 0.25);
        renderZoom();
    });

    document.querySelector('[data-zoom-reset]')?.addEventListener('click', () => {
        zoom = 1;
        renderZoom();
    });

    prevPage?.addEventListener('click', () => changePage(-1));
    nextPage?.addEventListener('click', () => changePage(1));

    fullscreenButton?.addEventListener('click', async () => {
        try {
            if (!document.fullscreenElement) {
                await viewer.requestFullscreen();
            } else {
                await document.exitFullscreen();
            }
        } catch (_) {
            // Fullscreen can be blocked by browser settings; the reader remains usable.
        }
    });

    image?.addEventListener('dblclick', () => {
        zoom = zoom === 1 ? 1.5 : 1;
        renderZoom();
    });

    // Wheel zoom only when Ctrl is held, preventing accidental zoom while scrolling.
    viewer.addEventListener('wheel', event => {
        if (!event.ctrlKey || !pages.length) return;
        event.preventDefault();
        zoom = clampZoom(zoom + (event.deltaY < 0 ? 0.1 : -0.1));
        renderZoom();
    }, { passive: false });

    document.addEventListener('keydown', event => {
        const active = document.activeElement;
        const typing = active && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName);
        if (typing) return;

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            changePage(-1);
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            changePage(1);
        }
        if (event.key === '+' || event.key === '=') {
            event.preventDefault();
            zoom = clampZoom(zoom + 0.25);
            renderZoom();
        }
        if (event.key === '-') {
            event.preventDefault();
            zoom = clampZoom(zoom - 0.25);
            renderZoom();
        }
        if (event.key === '0') {
            event.preventDefault();
            zoom = 1;
            renderZoom();
        }
        if (event.key.toLowerCase() === 'f') {
            event.preventDefault();
            fullscreenButton?.click();
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!fullscreenButton) return;
        const label = fullscreenButton.querySelector('span');
        if (label) label.textContent = document.fullscreenElement ? 'Exit Fullscreen' : 'Fullscreen';
    });

    renderZoom();
    renderPage();
})();
