(function () {
    function layoutEventGrid(grid) {
        var rowHeight = parseFloat(window.getComputedStyle(grid).getPropertyValue('grid-auto-rows'));
        var rowGap = parseFloat(window.getComputedStyle(grid).getPropertyValue('row-gap'));

        if (!rowHeight) {
            return;
        }

        Array.prototype.forEach.call(grid.querySelectorAll('.bh-event-card'), function (card) {
            card.style.gridRowEnd = '';
            var height = card.getBoundingClientRect().height;
            var span = Math.ceil((height + rowGap) / (rowHeight + rowGap));
            card.style.gridRowEnd = 'span ' + span;
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
