document.addEventListener('DOMContentLoaded', function() {
    // Icons are static inline; no runtime initialization needed

    // Function to render suppliers table
    function renderSuppliersTable(suppliersList = suppliers) {
        const tableBody = document.getElementById('suppliers-table-body');
        tableBody.innerHTML = '';

        suppliersList.forEach(supplier => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${supplier.id}</td>
                <td>${supplier.company_name}</td>
                <td>${supplier.first_name}</td>
                <td>${supplier.last_name}</td>
                <td><a href="mailto:${supplier.email}">${supplier.email}</a></td>
                <td>${supplier.phone}</td>
            `;
            tableBody.appendChild(row);
        });

    // Icons are static inline; no runtime initialization needed

        // Update table footer
        const footerInfo = document.getElementById('table-footer-info');
        footerInfo.textContent = `Showing 1 to ${suppliersList.length} of ${suppliersList.length} rows`;
    }

    // Initial render with all suppliers
    renderSuppliersTable();

    // Handle search functionality
    const searchInput = document.getElementById('search-input');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filteredSuppliers = suppliers.filter(supplier => 
            supplier.company_name.toLowerCase().includes(searchTerm) ||
            supplier.first_name.toLowerCase().includes(searchTerm) ||
            supplier.last_name.toLowerCase().includes(searchTerm) ||
            supplier.email.toLowerCase().includes(searchTerm) ||
            supplier.phone.toLowerCase().includes(searchTerm)
        );
        renderSuppliersTable(filteredSuppliers);
    });

});
