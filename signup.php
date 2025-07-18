<?php
include 'config.php'; // Database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure all POST values are set before using them
    $userType = isset($_POST["userType"]) ? $_POST["userType"] : '';
    $fullName = isset($_POST["fullName"]) ? $_POST["fullName"] : '';
    $email = isset($_POST["email"]) ? $_POST["email"] : '';
    $mobile = isset($_POST["mobile"]) ? $_POST["mobile"] : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    // Check if the email already exists in the database
    $emailCheckQuery = "SELECT email FROM retailers WHERE email = ? UNION SELECT email FROM wholesalers WHERE email = ?";
    $stmt = $conn->prepare($emailCheckQuery);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: This email is already registered. Please use a different email.";
    } else {
        // Prepare query for retailer or wholesaler based on user type
        if ($userType === "retailer") {
            $query = "INSERT INTO retailers (full_name, email, mobile, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $fullName, $email, $mobile, $password);
        } else {
            $wholesalerName = isset($_POST["wholesaler"]) ? $_POST["wholesaler"] : '';
            $address = isset($_POST["address"]) ? $_POST["address"] : '';
            $country = isset($_POST["country"]) ? $_POST["country"] : '';
            $registrationDate = isset($_POST["registrationDate"]) ? $_POST["registrationDate"] : '';

            // Upload files and check if successful
            $profilePicPath = uploadFile("profilePic");
            $companyDocPath = uploadFile("companyDoc");

            // Check if both files were uploaded successfully
            if ($profilePicPath === null || $companyDocPath === null) {
                echo "Error: File upload failed. Please try again.";
                exit();
            }

            $query = "INSERT INTO wholesalers (full_name, email, mobile, password, wholesaler_name, address, country, registration_date, profile_pic, company_doc) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssssss", $fullName, $email, $mobile, $password, $wholesalerName, $address, $country, $registrationDate, $profilePicPath, $companyDocPath);
        }

        if ($stmt->execute()) {
            echo "Registration successful!";
            header("Location: " . ($userType === "retailer" ? "signin.php" : "signin.php"));
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}

// File upload function
function uploadFile($inputName) {
    if (!empty($_FILES[$inputName]["name"])) {
        $targetDir = "pic/"; // Ensure this path is correct relative to your script
        $fileName = basename($_FILES[$inputName]["name"]);
        $targetFilePath = $targetDir . $fileName;

        // Check for upload errors
        if ($_FILES[$inputName]["error"] !== UPLOAD_ERR_OK) {
            return null; // Return null if there was an error
        }

        // Attempt to move the uploaded file
        if (move_uploaded_file($_FILES[$inputName]["tmp_name"], $targetFilePath)) {
            return $targetFilePath; // Return the path if successful
        } else {
            return null; // Return null if the upload failed
        }
    }
    return null; // Return null if no file was uploaded
}
?>


<!DOCTYPE html>
<html lang="en">
    <?php include "header.php" ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RazaBaba</title>
      
    <link rel="stylesheet" href="scss/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="auth-container ">
        <div class="auth-box">
            <div class="auth-header">
                <div class="logo">
                    <a href="../index.html">
                        <div class="raza">RAZA</div>
                        <div class="baba">BABA</div>
                    </a>
                </div>
                <h1>Create Account</h1>
            </div>

            <!-- User Type Selection -->
            <div class="user-type-selector">
                <button class="user-type active" data-type="retailer">Retailer</button>
                <button class="user-type" data-type="wholesaler">Wholesaler</button>
            </div>

            <!-- Signup Form -->
            <form class="auth-form" id="signupForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="userType" name="userType" value="retailer">

                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required oninput="validateEmail()">
                    <p id="emailMessage" style="color: red; display: none;">❌ Invalid email format.</p>
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" placeholder="03XXXXXXXXX" maxlength="11" required oninput="validateNumber()">                 
                    <p id="message"></p>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required oninput="validatePassword()">
                    <p id="passwordRequirements" style="color: red;"></p>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" required oninput="validatePasswordMatch()">
                    <p id="passwordMessage" style="color: red;"></p>
                </div>

                <!-- Wholesaler Fields (Hidden by Default) -->
                <div class="wholesaler-fields" style="display: none;">
                    <div class="form-group">
                        <label for="wholesaler">Wholesaler Name</label>
                        <input type="text" id="wholesaler" name="wholesaler" placeholder="Enter wholesaler name">
                    </div><BR>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" placeholder="Enter your address">
                    </div><BR>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country">
                            <option value="">Select Country</option>
                            <option value="Pakistan">Pakistan</option>
                            <option value="USA">USA</option>
                            <option value="India">India</option>
                            <option value="UK">UK</option>
                        </select>
                    </div>
                    <BR>
                    <!-- New: Company Registration Date -->
                    <div class="form-group">
                        <label for="registrationDate">Company Registration Date</label>
                        <input type="date" id="registrationDate" name="registrationDate">
                    </div>
                    <BR>
                    <div class="form-group">
                        <label for="profilePic">Profile Picture</label>
                        <input type="file" id="profilePic" name="profilePic" accept="image/*">
                    </div>
                    <BR>
                    <div class="form-group">
                        <label for="companyDoc">Upload Company Document</label>
                        <input type="file" id="companyDoc" name="companyDoc" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="form-group" style="margin-bottom: 15px; display: flex; align-items: center; padding: 10px;">
                    <label for="termsCheckbox" style="font-size: 14px; color: #333; display: flex; align-items: center;">
                        <input type="checkbox" id="termsCheckbox" style="margin-right: 8px; width: 16px; height: 16px;">
                        I agree to the 
                        <a href="terms.html" target="_blank" style="color: #007bff; text-decoration: none;">Terms and Conditions</a>
                    </label>
                </div>

                <button type="submit" class="auth-button" id="submitButton" disabled>Create Account</button>
            </form>

            <p class="auth-footer">
                Already have an account? <a href="signin.php">Sign In</a>
            </p>
        </div>
    </div>

    <script>
        // Mobile Number Validation
        function validateNumber() {
            let mobileInput = document.getElementById("mobile").value;
            let message = document.getElementById("message");
            let regex = /^03\d{9}$/;

            if (mobileInput.length !== 11) {
                message.innerHTML = "❌ Number must be exactly 11 digits.";
                message.style.color = "red";
            } else if (!regex.test(mobileInput)) {
                message.innerHTML = "❌ Invalid Pakistani mobile number.";
                message.style.color = "red";
            } else {
                message.innerHTML = "✅ Valid number!";
                message.style.color = "green";
            }
        }

        // Password Validation: Check if the password meets the required criteria
        function validatePassword() {
            const password = document.getElementById("password").value;
            const passwordRequirements = document.getElementById("passwordRequirements");

            // Regular Expressions for password validation
            const minLength = /.{8,}/;  // At least 8 characters
            const upperCase = /[A-Z]/;  // At least one uppercase letter
            const specialChar = /[!@#$%^&*(),.?":{}|<>]/;  // At least one special character

            let message = "";

            if (!minLength.test(password)) {
                message += "Password must be at least 8 characters long. ";
            }
            if (!upperCase.test(password)) {
                message += "Password must contain at least one uppercase letter. ";
            }
            if (!specialChar.test(password)) {
                message += "Password must contain at least one special character. ";
            }

            // Update the password requirements message
            if (message) {
                passwordRequirements.textContent = message;
            } else {
                passwordRequirements.textContent = "✅ Password is strong!";
                passwordRequirements.style.color = "green";
            }
        }

        // Confirm Password Validation: Check if the passwords match
        function validatePasswordMatch() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const passwordMessage = document.getElementById("passwordMessage");

            if (password !== confirmPassword) {
                passwordMessage.textContent = "❌ Passwords do not match.";
                passwordMessage.style.color = "red";
            } else {
                passwordMessage.textContent = "✅ Passwords match!";
                passwordMessage.style.color = "green";
            }
        }

        // Toggle Wholesaler Fields
        document.querySelectorAll(".user-type").forEach(button => {
            button.addEventListener("click", function() {
                document.querySelectorAll(".user-type").forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");

                let userType = this.getAttribute("data-type");
                document.getElementById("userType").value = userType; // Update hidden input

                let wholesalerFields = document.querySelector(".wholesaler-fields");
                wholesalerFields.style.display = (userType === "wholesaler") ? "block" : "none";
            });
        });

        // Enable Submit Button Only When Terms Are Accepted
        document.getElementById("termsCheckbox").addEventListener("change", function() {
            document.getElementById("submitButton").disabled = !this.checked;
        });

        // Email Validation
        function validateEmail() {
            let emailInput = document.getElementById("email").value;
            let emailMessage = document.getElementById("emailMessage");
            let emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

            if (!emailRegex.test(emailInput)) {
                emailMessage.style.display = "block";
            } else {
                emailMessage.style.display = "none";
            }
        }
    </script>
    <?php include "footer.php"?>
</body>
</html>
