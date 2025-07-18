
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>404 - Account Type Not Valid</title>
    <link rel="stylesheet" href="scss/auth.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0e7ff 0%, #f0f9ff 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .error-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 3rem 2.5rem;
            border-radius: 24px;
            box-shadow:
                0 8px 32px rgb(99 102 241 / 0.15),
                inset 0 0 40px rgb(99 102 241 / 0.05);
            max-width: 420px;
            width: 100%;
            text-align: center;
            position: relative;
        }

        .error-container::before {
            content: "404";
            position: absolute;
            top: 12px;
            right: 20px;
            font-size: 8rem;
            font-weight: 900;
            color: rgba(99, 102, 241, 0.1);
            user-select: none;
            pointer-events: none;
        }

        .error-header h1 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 900;
            color: #4338ca;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1rem;
            color: #6b7280;
            background-color: #fce7f3;
            border: 1px solid #db2777;
            padding: 12px 20px;
            border-radius: 18px;
            margin-bottom: 2rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .support-link {
            font-weight: 600;
            color: #6366f1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            transition: color 0.3s ease;
        }

        .support-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .support-link i {
            font-size: 1.4rem;
            transition: transform 0.3s ease;
        }

        .support-link:hover i {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            font-weight: 700;
            padding: 14px 40px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            display: inline-block;
            user-select: none;
        }
        .btn-primary:hover {
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="error-container" role="main" aria-labelledby="errorTitle" aria-describedby="errorDesc">
        <div class="error-header">
            <h1 id="errorTitle">404 - Account Type Not Valid</h1>
        </div>

        <div id="errorDesc" class="error-message" aria-live="assertive" aria-atomic="true">
            Sorry, your account type is not valid for access.<br />
            Please consider creating a retailer account to enjoy our services.
        </div>

        <p>
                If you need assistance, please contact us: 
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=support@example.com&su=Account Type Inquiry&body=Hello, I need assistance with my account type.">
                    <i class="fa fa-envelope support-icon" aria-hidden="true"></i>
                </a>
            </p>

        <br />
        <a href="logout.php" class="btn-primary" role="button" aria-label="logout ">
            logout
        </a>
    </div>
</body>
</html>

<?php
// Optional: Include footer ?>

