(function () {
    function layoutEventGrid(grid) {
        Array.prototype.forEach.call(grid.querySelectorAll('.bh-event-card'), function (card) {
            card.style.gridRowEnd = card.classList.contains('is-featured') ? 'span 2' : 'span 1';
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
