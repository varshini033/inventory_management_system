document.addEventListener('DOMContentLoaded', function() {
    // Icons are static inline; no runtime initialization needed

    // Attach click handlers to arrow buttons rendered server-side
    document.querySelectorAll('.row-arrow-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tr = e.target.closest('tr');
            // ID cell is second TD when corner column is present
            const idCell = tr ? tr.querySelector('td:nth-child(2)') : null;
            const id = idCell ? idCell.textContent.trim() : null;
            const action = btn.getAttribute('data-action');
            // Default behavior: log and highlight row briefly
            console.log('Arrow clicked:', action, 'row id=', id);
            tr.classList.add('row-arrow-active');
            setTimeout(() => tr.classList.remove('row-arrow-active'), 300);
        });
    });
});
