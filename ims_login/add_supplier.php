<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
// Page-specific code goes here
include("connect.php");
// Cache-Control headers to prevent form data caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$message = ""; // Message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyName = $_POST['company_name'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Check for duplicate phone number
    $checkQuery = "SELECT * FROM suppliers WHERE phone = '$phone'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        $message = "Data already exists in the database.";
        $_SESSION['message'] = $message; // Store message in session
    } else {
        // Insert new supplier if no duplicate is found
        $sql = "INSERT INTO suppliers (company_name, first_name, last_name, email, phone) 
                VALUES ('$companyName', '$firstName', '$lastName', '$email', '$phone')";

        if ($conn->query($sql) === TRUE) {
            $message = "Supplier added successfully!";
            $_SESSION['message'] = $message; // Store success message in session
            header("Location: add_supplier.php"); // Redirect after successful insert
            exit;
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
            $_SESSION['message'] = $message; // Store error message in session
        }
    }
    $conn->close();
}

// Retrieve message from session if it exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear message from session after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Supplier</title>
    <link rel="stylesheet" href="add_supplier.css">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/new_styles.css">
    <script>
        // Hide message after 2 seconds
        window.onload = function() {
            const messageBox = document.getElementById("messageBox");
            if (messageBox) {
                setTimeout(function() {
                    messageBox.style.display = "none";
                }, 2000);
            }
        };

        // Function to navigate back
        function goBack() {
            window.location.href = "suppliers.php";
        }
    </script>
</head>
<body>
    <button class="back-button" onclick="goBack()">&larr; Back</button>
    <div class="container">
        <h1 class="form-title">Add New Supplier</h1>
        
        <?php if (!empty($message)): ?>
            <div id="messageBox" class="message-box"><?= $message ?></div>
        <?php endif; ?>

        <form action="add_supplier.php" method="POST">
            <div class="input-group">
                <img src="assets/company.png" alt="company" class="input-icon">
                <input type="text" name="company_name" placeholder="Company Name" required>
            </div>

            <div class="input-group">
                <img src="assets/user.png" alt="user" class="input-icon">
                <input type="text" name="first_name" placeholder="First Name" required>
            </div>

            <div class="input-group">
                <img src="assets/user.png" alt="user" class="input-icon">
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>

            <div class="input-group">
                <img src="assets/email.png" alt="email" class="input-icon">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input-group">
                <img src="assets/phone.png" alt="phone" class="input-icon">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <button type="submit" class="btn">Add Supplier</button>
        </form>
    </div>
</body>
</html>
