(function() {
    const PAGE_SIZE = 10;

    function setupPaginationForTable(table, tbody) {
        if (!table || !tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr'));
        if (rows.length <= PAGE_SIZE) {
            const infoSpan = table.querySelector('#table-footer-info');
            if (infoSpan) infoSpan.textContent = `Showing 1 to ${rows.length} of ${rows.length} rows`;
            return;
        }

        const footer = table.querySelector('tfoot');
        const leftArrow = footer ? footer.querySelector('.tfoot-arrow.left') : null;
        const rightArrow = footer ? footer.querySelector('.tfoot-arrow.right') : null;
        const infoSpan = footer ? footer.querySelector('#table-footer-info') : null;

        const pageCount = Math.ceil(rows.length / PAGE_SIZE);
        let currentPage = 0;

        function renderPage(pageIndex) {
            currentPage = Math.max(0, Math.min(pageIndex, pageCount - 1));
            const start = currentPage * PAGE_SIZE;
            const end = Math.min(start + PAGE_SIZE, rows.length);
            rows.forEach((r, i) => {
                r.style.display = (i >= start && i < end) ? '' : 'none';
            });
            if (infoSpan) infoSpan.textContent = `Showing ${start + 1} to ${end} of ${rows.length} rows`;
            if (leftArrow) leftArrow.classList.toggle('disabled', currentPage === 0);
            if (rightArrow) rightArrow.classList.toggle('disabled', currentPage >= pageCount - 1);
        }

        // Attach handlers (replace nodes to avoid duplicate listeners)
        if (leftArrow) {
            const newLeft = leftArrow.cloneNode(true);
            leftArrow.parentNode.replaceChild(newLeft, leftArrow);
            newLeft.addEventListener('click', () => renderPage(currentPage - 1));
        }
        if (rightArrow) {
            const newRight = rightArrow.cloneNode(true);
            rightArrow.parentNode.replaceChild(newRight, rightArrow);
            newRight.addEventListener('click', () => renderPage(currentPage + 1));
        }

        renderPage(0);
    }

    function initTablePagination(tableId, tbodySelector = null) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const tbody = tbodySelector ? table.querySelector(tbodySelector) : table.tBodies[0];
        if (!tbody) return;

        if (tbody.querySelectorAll('tr').length > 0) {
            setupPaginationForTable(table, tbody);
            return;
        }

        const observer = new MutationObserver((mutations, obs) => {
            if (tbody.querySelectorAll('tr').length > 0) {
                obs.disconnect();
                setupPaginationForTable(table, tbody);
            }
        });
        observer.observe(tbody, { childList: true });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initTablePagination('items-table', '#items-table-body');
        initTablePagination('customer-table', '#customer-table-body');
        initTablePagination('sales-table', '#cart-items');
        initTablePagination('suppliers-table', '#suppliers-table-body');
    });
})();
