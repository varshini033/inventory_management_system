document.addEventListener('DOMContentLoaded', function() {
    // Icons are static inline; no runtime initialization needed

    // Function to update table footer after rendering the customer table
    function updateTableFooter() {
        const tableBody = document.getElementById('customer-table-body');
        const allRows = Array.from(tableBody.getElementsByTagName('tr'));
        const visibleRows = allRows.filter(row => row.style.display !== 'none');
        const visibleCount = visibleRows.length;
        const totalCount = allRows.length;
        const footerInfo = document.getElementById('table-footer-info');
        if (!footerInfo) return;
        if (totalCount === 0) footerInfo.textContent = 'No rows';
        else if (visibleCount === 0) footerInfo.textContent = `Showing 0 of ${totalCount} rows`;
        else footerInfo.textContent = `Showing 1 to ${visibleCount} of ${totalCount} rows`;
    }

    // Initial footer update
    updateTableFooter();

    // Handle search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#customer-table-body tr');

            tableRows.forEach(row => {
                // Safely gather the text content of every cell in the row
                const rowText = Array.from(row.querySelectorAll('td'))
                    .map(td => (td && td.textContent) ? td.textContent.toLowerCase() : '')
                    .join(' ');

                const rowMatches = rowText.includes(searchTerm);
                row.style.display = rowMatches ? '' : 'none';
            });

            // After filtering, update the footer with new row count
            updateTableFooter();
        });
    }


    // Handle new customer button (redirect to form)
    const newCustomerButton = document.querySelector('.btn-primary');
    newCustomerButton.addEventListener('click', function() {
        window.location.href = 'new_customer.php'; // Redirect to the new customer form page
    });

});
