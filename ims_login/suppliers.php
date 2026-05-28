<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
include("connect.php");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS</title>
    <link rel="stylesheet" href="suppliers.css">
    <link rel="stylesheet" href="assets/styles.css">
    <!-- Inline SVG icons used instead of Lucide CDN -->
</head>

<body>
    <header>
        <div class="container">
            <h1 class="brand">
                <img src="assets/logo.png" alt="IMS logo" class="logo">
                <div class="brand-lines">
                    <span>INVENTORY</span>
                    <span>MANAGEMENT</span>
                    <span>SYSTEM</span>
                </div>
            </h1>
            <div class="user-info">
                <span>
                    <?php
                    echo isset($_SESSION['firstName']) && isset($_SESSION['lastName'])
                        ? $_SESSION['firstName'] . ' ' . $_SESSION['lastName']
                        : 'Guest';
                    ?>
                </span>
                <a href="logout.php" class="logout" title="Logout" aria-label="Logout"><img src="assets/logout.png" alt="Logout" class="nav-icon logout-icon"></a>
            </div>

        </div>
    </header>
    <nav>
        <div class="container">
            <a href="customer.php" class="nav-item"><img src="assets/customer.png" alt="Customers" class="nav-icon">Customers</a>
            <a href="items.php" class="nav-item"><img src="assets/items.png" alt="Items" class="nav-icon">Items</a>
            <a href="suppliers.php" class="nav-item active"><img src="assets/suppliers.png" alt="Suppliers" class="nav-icon">Suppliers</a>
            <a href="sales.php" class="nav-item"><img src="assets/sales.png" alt="Sales" class="nav-icon">Sales</a>
        </div>
    </nav>
    <main class="container">
        <div class="table-container">
            <div class="search-container">
                <input type="text" placeholder="Search" id="search-input">
                <div class="actions">
                    <a href="add_supplier.php"><button class="btn btn-primary"><img src="assets/plus.png" alt="plus" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;">New Supplier</button></a>
                </div>
            </div>
            <table id="suppliers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                    </tr>
                </thead>
                <tbody id="suppliers-table-body"></tbody>
                <tfoot>
                        <tr>
                            <td colspan="6">
                                <span id="table-footer-info"></span> <!-- Footer element for dynamic updates -->
                                <span class="tfoot-arrow left" style="float:left; margin-top:4px;"> <img src="assets/left-arrow.png" alt="left" > </span>
                                <span class="tfoot-arrow right" style="float:right; margin-top:4px;"> <img src="assets/right-arrow.png" alt="right" > </span>
                            </td>
                        </tr>
                    </tfoot>
            </table>
            <div class="table-actions" style="margin-top:12px;">
                <button class="btn btn-secondary edit-btn" data-table="suppliers-table" title="Edit" aria-label="Edit">
                    <img src="assets/pen.png" alt="edit" />
                </button>
            </div>
        </div>
    </main>

    <?php
    // Fetch data from the database
    $suppliers = [];
    $sql = "SELECT id, company_name, first_name, last_name, email, phone FROM suppliers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
    }
    $conn->close();
    ?>

    <!-- Pass suppliers data to JavaScript -->
    <script>
        const suppliers = <?php echo json_encode($suppliers); ?>;
    </script>
    <script src="suppliers.js"></script>
    <script src="table-pagination.js"></script>
    <script src="table-edit.js"></script>
    <!-- Icons are static inline; no runtime icon library needed -->
</body>

</html>