<?php
// Include database connection file
include 'config.php'; // Database connection

// Initialize error variable
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"]);

    // Prepare query to check for user in both retailers and wholesalers
    $query = "
        SELECT id, password, status, 'retailer' AS user_type FROM retailers WHERE email = ?
        UNION
        SELECT id, password, status, 'wholesaler' AS user_type FROM wholesalers WHERE email = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check password
        if ($password == $row['password']) {
            // Check user status
            if ($row['status'] === 'active') {
                // Set cookies for id, email and user type with 30 days expiry
                setcookie("user_id", $row['id'], time() + (86400 * 30), "/"); // 30 days
                setcookie("user_email", $email, time() + (86400 * 30), "/"); // 30 days
                
                // Set user type to admin if email is raza@gmail.com
                if ($email === 'raza@gmail.com'|| $email === 'hashim@gmail.com'||$email === 'rehman@gmail.com' ) {
                    setcookie("user_type", 'admin', time() + (86400 * 30), "/"); // 30 days
                    header("Location: admin");
                } else {
                    setcookie("user_type", $row['user_type'], time() + (86400 * 30), "/"); // 30 days

                    // Redirect based on user type
                    if ($row['user_type'] === 'retailer') {
                        header("Location: index.php");
                    } else {
                        header("Location: dash/index.php");
                    }
                }
                exit();
            } else {
                // User is inactive
                $error = "Your account is inactive. Please contact support.";
                $support_email = "su94-adcsm-fd3@superior.edu.pk";
            }
        } else {
            $error = "Error: Invalid password.";
        }
    } else {
        $error = "Error: No user found with this email.";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Sign In - RazaBaba</title>
    <link rel="stylesheet" href="scss/auth.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
       
        .error-message {
            color: red;
            text-align: center;
            background-color: #ffe6e6;
            border: 1px solid red;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .support-icon {
            cursor: pointer;
            color: #007bff;
            font-size: 1.5rem;
            vertical-align: middle;
        }
       
        
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <div class="logo">
                    <a href="../index.html">
                        <div class="raza">RAZA</div>
                        <div class="baba">BABA</div>
                    </a>
                </div>
                <h1>Sign In</h1>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                    <?php if (isset($support_email)): ?>
                        <p>
                            If you need assistance, please contact us: 
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= htmlspecialchars($support_email) ?>&su=Account Activation&body=Hello, I need assistance with my account. My User ID is <?= htmlspecialchars($row['id']) ?> and my email is <?= htmlspecialchars($email) ?>.">
                                <i class="fa fa-envelope support-icon" aria-hidden="true"></i>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="signin.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required />
                </div>

                <button type="submit" class="auth-button " style="width: 125%;">Sign In</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </p>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
