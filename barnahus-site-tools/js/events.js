(function () {
    function clampEventDescription(card) {
        var content = card.querySelector('.bh-event-content');
        var description = card.querySelector('.bh-event-description');
        var link = card.querySelector('.bh-event-link');

        if (!content || !description || !link) {
            return;
        }

        description.style.display = '';
        description.style.maxHeight = '';
        description.style.webkitLineClamp = '';

        var contentRect = content.getBoundingClientRect();
        var descriptionRect = description.getBoundingClientRect();
        var lineHeight = parseFloat(window.getComputedStyle(description).lineHeight);
        var maxLines = card.classList.contains('is-featured') ? 12 : 3;
        var linkHeight = link.getBoundingClientRect().height;
        var linkGap = 12;
        var availableHeight = contentRect.height - (descriptionRect.top - contentRect.top) - linkHeight - linkGap;
        var visibleLines = Math.max(0, Math.min(maxLines, Math.floor(availableHeight / lineHeight)));

        if (!visibleLines) {
            description.style.display = 'none';
            return;
        }

        description.style.maxHeight = (visibleLines * lineHeight) + 'px';
        description.style.webkitLineClamp = String(visibleLines);
    }

    function layoutEventGrid(grid) {
        Array.prototype.forEach.call(grid.querySelectorAll('.bh-event-card'), function (card) {
            card.style.gridRowEnd = card.classList.contains('is-featured') ? 'span 2' : 'span 1';
            clampEventDescription(card);
        });
    }

    function layoutEventGrids() {
        Array.prototype.forEach.call(document.querySelectorAll('.bh-events-grid'), layoutEventGrid);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', layoutEventGrids);
    } else {
        layoutEventGrids();
    }

    window.addEventListener('load', layoutEventGrids);
    window.addEventListener('resize', function () {
        window.clearTimeout(window.barnahusEventGridResizeTimer);
        window.barnahusEventGridResizeTimer = window.setTimeout(layoutEventGrids, 120);
    });
}());
