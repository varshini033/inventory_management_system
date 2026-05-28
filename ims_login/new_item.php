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
    <title>Add New Item</title>
    <link rel="stylesheet" href="new_item.css">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/new_styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        // Check if the item ID exists
        function checkItemId() {
            const itemId = document.getElementById("item_id").value;

            // Make an AJAX call to check if item ID exists in the database
            $.ajax({
                url: "new_item.php",
                method: "POST",
                data: { check_item_id: true, item_id: itemId },
                success: function(response) {
                    if (response === "exists") {
                        // Item ID exists - disable other fields except quantity
                        document.getElementById("name").disabled = true;
                        document.getElementById("category").disabled = true;
                        document.getElementById("wholesale_price").disabled = true;
                        document.getElementById("retail_price").disabled = true;

                        // Show a message to indicate the item already exists
                        alert("Item ID already exists. Only quantity is required.");
                    } else {
                        // Item ID does not exist - enable all fields
                        document.getElementById("name").disabled = false;
                        document.getElementById("category").disabled = false;
                        document.getElementById("wholesale_price").disabled = false;
                        document.getElementById("retail_price").disabled = false;
                    }
                }
            });
        }

        // Back button function to navigate back to the item listing page
        function goBack() {
            window.location.href = "items.php"; // Change to the appropriate page
        }
    </script>
</head>
<body>
    <button class="back-button" onclick="goBack()">&larr; Back</button>
    <div class="container">
        <h1 class="form-title">Add New Item</h1>

        <?php
        $message = ""; // Message variable

        // Check if AJAX request to verify item ID
        if (isset($_POST['check_item_id']) && isset($_POST['item_id'])) {
            $itemId = $_POST['item_id'];
            $query = "SELECT * FROM items WHERE item_id = '$itemId'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                echo "exists";
            } else {
                echo "not_exists";
            }
            exit; // End script execution for AJAX response
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['check_item_id'])) {
            $itemId = $_POST['item_id'];
            $name = $_POST['name'];
            $category = $_POST['category'];
            $wholesale_price = $_POST['wholesale_price'];
            $retail_price = $_POST['retail_price'];
            $quantity = $_POST['quantity'];

            // Check if item with the same item_id exists
            $checkQuery = "SELECT * FROM items WHERE item_id = '$itemId'";
            $result = $conn->query($checkQuery);

            if ($result->num_rows > 0) {
                // Fetch existing item details
                $row = $result->fetch_assoc();

                if ($row['name'] === $name) {
                    // Item exists with the same ID and name
                    if ($row['category'] !== $category || $row['wholesale_price'] != $wholesale_price || $row['retail_price'] != $retail_price) {
                        // Update category, wholesale price, and retail price if they differ
                        $updateQuery = "UPDATE items SET category = '$category', wholesale_price = '$wholesale_price', retail_price = '$retail_price', quantity = quantity + '$quantity' WHERE item_id = '$itemId'";
                    } else {
                        // Just update the quantity
                        $updateQuery = "UPDATE items SET quantity = quantity + '$quantity' WHERE item_id = '$itemId'";
                    }
                    if ($conn->query($updateQuery) === TRUE) {
                        $message = "Item updated successfully!";
                    } else {
                        $message = "Error updating item: " . $conn->error;
                    }
                } else {
                    // Item ID exists with a different name or details, find the next available ID
                    $newIdQuery = "SELECT MAX(CAST(item_id AS UNSIGNED)) + 1 AS next_id FROM items";
                    $newIdResult = $conn->query($newIdQuery);
                    $newItemId = $newIdResult->fetch_assoc()['next_id'] ?? 1;

                    $sql = "INSERT INTO items (item_id, name, category, wholesale_price, retail_price, quantity) 
                            VALUES ('$newItemId', '$name', '$category', '$wholesale_price', '$retail_price', '$quantity')";

                    if ($conn->query($sql) === TRUE) {
                        $message = "New item created with ID $newItemId!";
                    } else {
                        $message = "Error: " . $conn->error;
                    }
                }
            } else {
                // Item does not exist, insert with provided ID
                $sql = "INSERT INTO items (item_id, name, category, wholesale_price, retail_price, quantity) 
                        VALUES ('$itemId', '$name', '$category', '$wholesale_price', '$retail_price', '$quantity')";

                if ($conn->query($sql) === TRUE) {
                    $message = "Item added successfully!";
                } else {
                    $message = "Error: " . $conn->error;
                }
            }
            $conn->close();
        }
        ?>

        <?php if (!empty($message)): ?>
            <div id="messageBox" class="message-box"><?= $message ?></div>
        <?php endif; ?>

        <form action="new_item.php" method="POST">
            <div class="input-group">
                <img src="assets/barcode.png" alt="barcode" class="input-icon">
                <input type="text" name="item_id" id="item_id" placeholder="Item ID" required onblur="checkItemId()">
            </div>

            <div class="input-group">
                <img src="assets/tag.png" alt="tag" class="input-icon">
                <input type="text" name="name" id="name" placeholder="Item Name" required>
            </div>

            <div class="input-group">
                <img src="assets/category.png" alt="category" class="input-icon">
                <input type="text" name="category" id="category" placeholder="Category" required>
            </div>

            <div class="input-group">
                <img src="assets/dollar.png" alt="dollar" class="input-icon">
                <input type="number" name="wholesale_price" id="wholesale_price" placeholder="Wholesale Price" step="0.01" required>
            </div>

            <div class="input-group">
                <img src="assets/dollar.png" alt="dollar" class="input-icon">
                <input type="number" name="retail_price" id="retail_price" placeholder="Retail Price" step="0.01" required>
            </div>

            <div class="input-group">
                <img src="assets/quantity.png" alt="quantity" class="input-icon">
                <input type="number" name="quantity" id="quantity" placeholder="Quantity" required>
            </div>

            <button type="submit" class="btn">Add Item</button>
        </form>
    </div>
</body>
</html>
