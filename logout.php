<?php
// Start the session
session_start();

// Clear the cookies
setcookie("user_id", "", time() - 3600, "/"); // Expire the cookie
setcookie("user_email", "", time() - 3600, "/"); // Expire the cookie
setcookie("user_type", "", time() - 3600, "/"); // Expire the cookie

// Optionally, destroy the session if you are using sessions
session_destroy();

// Redirect to the sign-in page
header("Location: signin.php");
exit();
?>
