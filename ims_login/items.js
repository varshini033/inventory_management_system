document.addEventListener('DOMContentLoaded', function() {
    // Icons are static inline; no runtime initialization needed

    // Handle search functionality
    const searchInput = document.getElementById('search-input');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#items-table-body tr');
        
        // Loop through each row to filter based on search term
        tableRows.forEach(row => {
            const itemId = row.cells[0].textContent.toLowerCase();
            const itemName = row.cells[1].textContent.toLowerCase();
            const category = row.cells[2].textContent.toLowerCase();
            const quantity = row.cells[5].textContent.toLowerCase();

            if (itemId.includes(searchTerm) || itemName.includes(searchTerm) || category.includes(searchTerm) || quantity.includes(searchTerm)) {
                row.style.display = ''; // Show row if it matches
            } else {
                row.style.display = 'none'; // Hide row if it doesn't match
            }
        });
        
        // Update the footer info to reflect visible rows
        updateFooterInfo();
    });

    // Function to update footer info
    function updateFooterInfo() {
        const tableBody = document.getElementById("items-table-body");
        const footerInfo = document.getElementById("table-footer-info");
        const visibleRows = Array.from(tableBody.getElementsByTagName("tr"))
            .filter(row => row.style.display !== 'none').length;
        
        footerInfo.textContent = `Showing ${visibleRows} of ${tableBody.getElementsByTagName("tr").length} rows`;
    }
    
    // Initial footer update
    updateFooterInfo();
});
