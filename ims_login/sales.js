document.addEventListener('DOMContentLoaded', function() {
    // Icons are static inline; no runtime initialization needed

    // Handle search functionality
    const searchInput = document.getElementById('search-input');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#cart-items tr');

        // Loop through each row to filter based on search term
        tableRows.forEach(row => {
            const itemId = row.cells[0].textContent.toLowerCase();
            const itemName = row.cells[1].textContent.toLowerCase();
            const quantity = row.cells[3].textContent.toLowerCase();

            if (
                itemId.includes(searchTerm) ||
                itemName.includes(searchTerm) ||
                quantity.includes(searchTerm)
            ) {
                row.style.display = ''; // Show row if it matches
            } else {
                row.style.display = 'none'; // Hide row if it doesn't match
            }
        });

        // Update footer info to reflect visible rows
        updateFooterInfo();
    });

    // Function to update footer info
    function updateFooterInfo() {
        const tableBody = document.getElementById("cart-items");
        const visibleRows = Array.from(tableBody.getElementsByTagName("tr"))
            .filter(row => row.style.display !== 'none').length;
        const rowCount = tableBody.getElementsByTagName("tr").length;

        // Optional: create footer text element if not already present
        let footerInfo = document.getElementById("table-footer-info");
        if (!footerInfo) {
            footerInfo = document.createElement("span");
            footerInfo.id = "table-footer-info";
            tableBody.parentElement.appendChild(footerInfo);
        }

        footerInfo.textContent = `Showing ${visibleRows} of ${rowCount} rows`;
    }

    // Initial footer update
    updateFooterInfo();
});
