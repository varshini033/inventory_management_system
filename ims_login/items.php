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
    <link rel="stylesheet" href="items.css">
    <link rel="stylesheet" href="assets/styles.css">
    <!-- Using local inline SVGs instead of Lucide CDN for faster load -->
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
            <a href="items.php" class="nav-item active"><img src="assets/items.png" alt="Items" class="nav-icon">Items</a>
            <a href="suppliers.php" class="nav-item"><img src="assets/suppliers.png" alt="Suppliers" class="nav-icon">Suppliers</a>
            <a href="sales.php" class="nav-item"><img src="assets/sales.png" alt="Sales" class="nav-icon">Sales</a>
        </div>
    </nav>
    <main class="container">
        <div class="actions">
            <div class="left-actions"></div>

        </div>
        <div class="table-container">
            <div class="search-container">
                <input type="text" placeholder="Search" id="search-input">

                <div class="right-actions">
                    <a href="new_item.php"><button class="btn btn-primary"><img src="assets/plus.png" alt="plus" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;">New Item</button>
                    </a>
                </div>
            </div>
            <table id="items-table">
                <thead>
                    <tr>
                        <th style="width: 1%;">ID</th>
                        <th style="width: 5%;">Item Name</th>
                        <th style="width: 5%;">Category</th>
                        <th style="width: 7%;">Wholesale Price</th>
                        <th style="width: 7%;">Retail Price</th>
                        <th style="width: 5%;">Quantity</th>
                    </tr>
                </thead>
                <tbody id="items-table-body">
                    <?php
                    // Fetch data from the database
                    $sql = "SELECT item_id, name, category, wholesale_price, retail_price, quantity FROM items";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["item_id"] . "</td>";
                            echo "<td>" . $row["name"] . "</td>";
                            echo "<td>" . $row["category"] . "</td>";
                            echo "<td>" . $row["wholesale_price"] . "</td>";
                            echo "<td>" . $row["retail_price"] . "</td>";
                            echo "<td>" . $row["quantity"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No records found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
                <tfoot>
                        <tr>
                            <td colspan="7">
                                <span id="table-footer-info"></span> <!-- Footer element for dynamic updates -->
                                <span class="tfoot-arrow left" style="float:left; margin-top:4px;"> <img src="assets/left-arrow.png" alt="left" > </span>
                                <span class="tfoot-arrow right" style="float:right; margin-top:4px;"> <img src="assets/right-arrow.png" alt="right" > </span>
                            </td>
                        </tr>
                    </tfoot>
            </table>
            <div class="table-actions" style="margin-top:12px;">
                <button class="btn btn-secondary edit-btn" data-table="items-table" title="Edit" aria-label="Edit">
                    <img src="assets/pen.png" alt="edit"  />
                </button>
            </div>
        </div>
    </main>
    <script src="items.js"></script>
    <script src="table-pagination.js"></script>
    <script src="table-edit.js"></script>

    <script>
        // Update the table footer dynamically
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.getElementById("items-table-body");
            const footerInfo = document.getElementById("table-footer-info");

            const rowCount = tableBody.getElementsByTagName("tr").length;
            footerInfo.textContent = `Showing 1 to ${rowCount} of ${rowCount} rows`;
        });
    </script>
    <!-- No lucide icon library required; using character arrows for pagination -->
</body>

</html>