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
    <title>Add New Sale Item</title>
    <link rel="stylesheet" href="add_sale_item.css">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/new_styles.css">
    <script>
        // Hide the message box after 2 seconds
        window.onload = function() {
            const messageBox = document.getElementById("messageBox");
            if (messageBox) {
                setTimeout(function() {
                    messageBox.style.display = "none";
                }, 2000);
            }
        };

        // Back button function to navigate back to previous page
        function goBack() {
            window.location.href = "sales.php";
        }
    </script>
</head>
<body>
    <button class="back-button" onclick="goBack()">&larr; Back</button>
    <div class="container">
        <h1 class="form-title">Add New Sale Item</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = $_POST['item_id'];
            $item_name = $_POST['item_name'];
            $price = $_POST['price'];
            $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
            $discount = isset($_POST['discount']) ? $_POST['discount'] : null;

            // Check if item_id and item_name combination already exists
            $checkQuery = "SELECT * FROM sales WHERE item_id = '$item_id' AND item_name = '$item_name'";
            $result = $conn->query($checkQuery);

            if ($result->num_rows > 0) {
                // Item exists with matching name, so update quantity and price
                $row = $result->fetch_assoc();
                $newQuantity = $row['quantity'] + $quantity; // Add new quantity to existing

                // Update the price and quantity in the database
                $updateQuery = "UPDATE sales SET quantity = '$newQuantity', price = '$price' 
                                WHERE item_id = '$item_id' AND item_name = '$item_name'";

                if ($conn->query($updateQuery) === TRUE) {
                    echo "<div id='messageBox' class='message-box'>Quantity and price updated successfully!</div>";
                } else {
                    echo "<div id='messageBox' class='message-box'>Error: " . $conn->error . "</div>";
                }
            } else {
                // item_id exists but item_name does not match, so find the next available item_id
                $getMaxIdQuery = "SELECT MAX(item_id) AS max_id FROM sales";
                $maxIdResult = $conn->query($getMaxIdQuery);
                $newItemId = $item_id;

                if ($maxIdResult && $maxIdResult->num_rows > 0) {
                    $maxRow = $maxIdResult->fetch_assoc();
                    $newItemId = $maxRow['max_id'] + 1; // Increment max item_id for the new entry
                }

                // Insert the new entry with the new item_id
                $insertQuery = "INSERT INTO sales (item_id, item_name, price, quantity, discount) 
                                VALUES ('$newItemId', '$item_name', '$price', '$quantity', " . ($discount !== null ? "'$discount'" : "NULL") . ")";

                if ($conn->query($insertQuery) === TRUE) {
                    echo "<div id='messageBox' class='message-box'>New item entry added with item ID $newItemId!</div>";
                } else {
                    echo "<div id='messageBox' class='message-box'>Error: " . $conn->error . "</div>";
                }
            }

            $conn->close();
        }
        ?>

        <form action="add_sale_item.php" method="POST">
            <div class="input-group">
                <img src="assets/barcode.png" alt="barcode" class="input-icon">
                <input type="text" name="item_id" placeholder="Item ID" required>
            </div>

            <div class="input-group">
                <img src="assets/tag.png" alt="tag" class="input-icon">
                <input type="text" name="item_name" placeholder="Item Name" required>
            </div>

            <div class="input-group">
                <img src="assets/dollar.png" alt="dollar" class="input-icon">
                <input type="number" name="price" placeholder="Price" required step="0.01">
            </div>

            <div class="input-group">
                <img src="assets/quantity.png" alt="quantity" class="input-icon">
                <input type="number" name="quantity" placeholder="Quantity">
            </div>

            <div class="input-group">
                <img src="assets/discount.png" alt="discount" class="input-icon">
                <input type="number" name="discount" placeholder="Discount (%)" step="0.01" min="0" max="100">
            </div>

            <button type="submit" class="btn">Add Sale Item</button>
        </form>
    </div>
</body>
</html>
