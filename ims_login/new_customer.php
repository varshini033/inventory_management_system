<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// include DB connection
include("connect.php");

// Page-specific code goes here

// Cache-Control headers to prevent form data caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$message = ""; // Message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $totalSpent = $_POST['totalSpent'];

    // Check for duplicate phone number
    $checkQuery = "SELECT * FROM customers WHERE phone = '$phone'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        $message = "Data already exists in the database.";
    } else {
        // Insert data if no duplicate
        $sql = "INSERT INTO customers (firstName, lastName, email, phone, total_spent) VALUES ('$firstName', '$lastName', '$email', '$phone', '$totalSpent')";
        
        if ($conn->query($sql) === TRUE) {
            // Stay on this page and show a success message instead of redirecting.
            $message = "Successfully entered data.";
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="new_customer.css">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/new_styles.css">
    <script>
        // Prevent browser cache for the form page
        window.onload = function() {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            // Disable the form data cache (this ensures the browser doesn’t remember the data)
            window.onbeforeunload = function() {
                window.history.replaceState(null, null, window.location.href);
            };

            // Hide the message box after 2 seconds and redirect after success
            const messageBox = document.getElementById("messageBox");
            if (messageBox) {
                setTimeout(function() {
                    messageBox.style.display = "none";
                    <?php if (isset($_SESSION['show_message'])): ?>
                        <?php unset($_SESSION['show_message']); // Clear session variable ?>
                        location.href = "customer.php"; // Redirect to customer.php after showing message
                    <?php endif; ?>
                }, 2000);
            }
        };

        // Back button function to navigate back to customer.php
        function goBack() {
            window.location.href = "customer.php";
        }
    </script>
</head>
<body>
    <button class="back-button" onclick="goBack()">&larr; Back</button>
    <div class="container">
        <h1 class="form-title">Add New Customer</h1>
        
        <?php if (!empty($message)): ?>
            <div id="messageBox" class="message-box"><?= $message ?></div>
        <?php endif; ?>
        
        <form action="new_customer.php" method="POST">
            <div class="input-group">
                <img src="assets/user.png" alt="user" class="input-icon">
                <input type="text" name="firstName" placeholder="First Name" required>
            </div>

            <div class="input-group">
                <img src="assets/user.png" alt="user" class="input-icon">
                <input type="text" name="lastName" placeholder="Last Name" required>
            </div>

            <div class="input-group">
                <img src="assets/email.png" alt="email" class="input-icon">
                <input type="email" name="email" placeholder="Email">
            </div>

            <div class="input-group">
                <img src="assets/phone.png" alt="phone" class="input-icon">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <div class="input-group">
                <img src="assets/dollar.png" alt="dollar" class="input-icon">
                <input type="number" step="0.01" name="totalSpent" value="0" placeholder="Total Spent">
            </div>

            <button type="submit" class="btn">Add Customer</button>
        </form>
    </div>
</body>
</html>
