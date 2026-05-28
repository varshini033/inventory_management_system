<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: customer.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register & Login</title>
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
  <div class="container" id="signup" style="display:none;">
    <h1 class="form-title">Register</h1>
    <form method="post" action="register.php">
      <div class="input-group">
        <img src="assets/user.png" alt="user" class="input-icon">
        <input type="text" name="fName" id="fName" placeholder="First Name" required>
      </div>
      <div class="input-group">
        <img src="assets/user.png" alt="user" class="input-icon">
        <input type="text" name="lName" id="lName" placeholder="Last Name" required>
      </div>
      <div class="input-group">
        <img src="assets/email.png" alt="email" class="input-icon">
        <input type="email" name="email" id="email" placeholder="Email" required>
      </div>
      <div class="input-group">
        <img src="assets/password.png" alt="password" class="input-icon">
        <input type="password" name="password" id="password" placeholder="Password" required>
      </div>
      <input type="submit" class="btn" value="Sign Up" name="signUp">
    </form>
    <div class="links">
      <p>Already Have Account ?</p>
      <button id="signInButton">Sign In</button>
    </div>
  </div>

  <div class="container" id="signIn">
    <h1 class="form-title">Sign In</h1>
    <form method="post" action="register.php">
      <div class="input-group">
        <img src="assets/email.png" alt="email" class="input-icon">
        <input type="email" name="email" id="email" placeholder="Email" required>
      </div>
      <div class="input-group">
        <img src="assets/password.png" alt="password" class="input-icon">
        <input type="password" name="password" id="password" placeholder="Password" required>
      </div>
      <input type="submit" class="btn" value="Sign In" name="signIn">
    </form>
    <div class="links">
      <p>Don't have account yet?</p>
      <button id="signUpButton">Sign Up</button>
    </div>
  </div>
  <script src="index.js"></script>
</body>

</html>