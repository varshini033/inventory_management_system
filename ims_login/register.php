<?php 
include 'connect.php';  // Include your database connection file

// Handle Sign Up
if (isset($_POST['signUp'])) {
    // Get data from form
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);  // Hash password before saving

    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "Email Address Already Exists!";
    } else {
        // Insert new user into the database
        $insertQuery = "INSERT INTO users (firstName, lastName, email, password)
                        VALUES ('$firstName', '$lastName', '$email', '$password')";
        if ($conn->query($insertQuery) === TRUE) {
            // Redirect to index.php after successful sign up
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Handle Sign In
if (isset($_POST['signIn'])) {
    // Get form data
    $email = $_POST['email'];
    $password = md5($_POST['password']);  // Hash the entered password

    // Check if email and password match
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Start session and save user data in session variables
        session_start();
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];
        $_SESSION['firstName'] = $row['firstName'];
        $_SESSION['lastName'] = $row['lastName'];

        // Redirect to customer.php after successful login
        header("Location: customer.php");
        exit();
    } else {
        echo "Not Found, Incorrect Email or Password";
    }
}
?>

