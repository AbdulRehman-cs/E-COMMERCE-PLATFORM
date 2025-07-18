<?php
// Start the session to manage user login state
session_start();

include 'category/CategoryManager.php';
$categoryManager = new CategoryManager();

// Check if user is logged in
if (!isset($_COOKIE['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}if (isset($_POST['item_id'])) {

$message="welcome ";
 $itemId = $_POST['item_id'];
    $userId = $_POST['user_id'];
    $itemPrice = $_POST['item_price']; // Access the original item price
    $itemPrice20Percent = $_POST['item_price_20_percent'];
    $_SESSION['itemId'] = $itemId;
    $_SESSION['itemPrice'] = $itemPrice;
}
$user_id = $_COOKIE['user_id'];
$userId=$user_id;
echo $user_id;
$billingAddress = $categoryManager->getBillingAddressByUserId($userId);
  $addressId=$billingAddress['id'];
$adrsid=$billingAddress['id'];
 $itemId=$_SESSION['itemId'];
   $itemPrice= $_SESSION['itemPrice'];
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])){
    try {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $mobileNo = $_POST['mobile'];
        $addressLine1 = $_POST['address_line1'];
        $addressLine2 = $_POST['address_line2'];
        $country = $_POST['country'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zipCode = $_POST['zip_code'];

        if ($billingAddress) {
         
            // Update existing address
            $categoryManager->updateBillingAddress($billingAddress['id'], $userId, $firstName, $lastName, $email, $mobileNo, $addressLine1, $addressLine2, $country, $city, $state, $zipCode);
            $message = "Billing address updated successfully.";
        } else {
            // Add new address
            $categoryManager->addBillingAddress($userId, $firstName, $lastName, $email, $mobileNo, $addressLine1, $addressLine2, $country, $city, $state, $zipCode);
            $message = "Billing address added successfully.";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $method = $_POST['paymentMethod'];
    $amount = $_POST['amount'];

    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        echo "<script>alert('Invalid amount. Please enter a valid payment amount.');</script>";
        exit;
    }

    $accountOrCard = '';
    $cardNumber = null;
    $cvv = null;
    $expiryDate = null;

    if ($method === 'easypaisa' || $method === 'jazzcash') {
        $accountOrCard = $_POST['accountNumber'];
    } elseif ($method === 'card') {
        $accountOrCard = $_POST['cardNumber'];
        $cardNumber = $_POST['cardNumber'];
        $cvv = $_POST['cvv'];
        $expiryDate = $_POST['expiryDate'];
    }

    // Debugging output

  $status='piad';
    if ($categoryManager->insertPayment($method, $accountOrCard, $amount, $cardNumber, $cvv, $expiryDate, $itemId, $userId, $addressId, $itemPrice)) {
            $categoryManager->upItemFromCart($user_id, $itemId,$status); // Call the method to remove the item

        header("location:whole/");
    } else {
        echo "<script>alert('Failed to process payment.');</script>";
    }
}
include "header.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Form with PHP and MySQL</title>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title mb-4 text-center">Make a Payment</h3>
                        <form id="paymentForm" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Select Payment Method</label>
                                <select class="form-select" id="paymentMethod" name="paymentMethod" required>
                                    <option value="" selected disabled>-- Choose a method --</option>
                                    <option value="easypaisa">EasyPaisa</option>
                                    <option value="jazzcash">JazzCash</option>
                                    <option value="card">Card</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a payment method.
                                </div>
                            </div>

                            <div id="accountNumberGroup" class="mb-3">
                                <label for="accountNumber" class="form-label">Account Number</label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="accountNumber" 
                                    name="accountNumber" 
                                    placeholder="Enter Account Number" 
                                    pattern="\d{10,15}"
                                    title="Enter a valid account number (10 to 15 digits)"
                                />
                                <div class="invalid-feedback">
                                    Please enter a valid account number (10 to 15 digits).
                                </div>
                            </div>

                            <div id="cardDetailsGroup" class="d-none">
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">Card Number</label>
                                    <input 
                                        type="tel" 
                                        class="form-control" 
                                        id="cardNumber" 
                                        name="cardNumber" 
                                        placeholder="1234 5678 9012 3456" 
                                        pattern="\d{13,19}" 
                                        maxlength="19" 
                                        inputmode="numeric" 
                                        title="Enter a valid card number (13 to 19 digits)"
                                    />
                                    <div class="invalid-feedback">
                                        Please enter a valid card number (13 to 19 digits).
                                    </div>
                                </div>
                                <div class="row gx-3">
                                    <div class="col-6 mb-3">
                                        <label for="expiryDate" class="form-label">Expiry Date</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            id="expiryDate" 
                                            name="expiryDate" 
                                            placeholder="MM/YY" 
                                            pattern="^(0[1-9]|1[0-2])\/\d{2}$" 
                                            title="Enter expiry date in MM/YY format"
                                        />
                                        <div class="invalid-feedback">
                                            Please enter expiry date in MM/YY format.
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="cvv" 
                                            name="cvv" 
                                            placeholder="123" 
                                            pattern="\d{3,4}" 
                                            maxlength="4" 
                                            inputmode="numeric" 
                                            title="Enter 3 or 4 digit CVV"
                                        />
                                        <div class="invalid-feedback">
                                            Please enter a 3 or 4 digit CVV code.
                                        </div>
                                    </div>
                                </div>
                            </div>
                              <div class="mb-3">
                                <label for="amount" class="form-g" value=" 45<?php echo $itemPrice ?>">Total Amount (PKR)</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    value="<?php echo $itemPrice ?>"
                                    id="tamount" 
                                    name="tamount" 
                                    placeholder="Enter amount" 
                                    min="1" 
                                    step="0.01" 
                                    required
                                />
                                <div class="invalid-feedback">
                                    Please enter a valid payment amount.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payment Amount (PKR)</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="amount" 
                                    value="<?php echo $itemPrice20Percent ?>"
                                    name="amount" 
                                    placeholder="Enter amount" 
                                    min="1" 
                                    step="0.01" 
                                    required
                                />
                                <div class="invalid-feedback">
                                    Please enter a valid payment amount.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Pay Now</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow mt-5">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Payment Records</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle" id="paymentsTable" aria-label="Payment Records Table">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Method</th>
                                        <th scope="col">Account/Card Number</th>
                                        <th scope="col">Amount (PKR)</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Payment records will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-4">
        <div class="container mt-5">
    <h1 class="mb-4">Billing Address Management</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addressModal">Add/Update Address</button>

    <!-- Billing Address Card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Billing Address</h5>
            <?php if ($billingAddress):
                $addressId =$billingAddress['id'] ?>
            
                <p class="card-text">
                    <?php echo htmlspecialchars($billingAddress['first_name'] . ' ' . $billingAddress['last_name']); ?><br>
                    <?php echo htmlspecialchars($billingAddress['email']); ?><br>
                    <?php echo htmlspecialchars($billingAddress['mobile_no']); ?><br>
                    <?php echo htmlspecialchars($billingAddress['address_line1'] . ', ' . $billingAddress['city'] . ', ' . $billingAddress['state'] . ', ' . $billingAddress['country'] . ' - ' . $billingAddress['zip_code']); ?>
                </p>
            <?php else: ?>
                <p class="card-text">No billing address found. Please add one.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Adding/Updating Address -->
    <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Add/Update Address</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="col-md-6 form-group">
                                <label>First Name</label>
                                <input class="form-control" type="text" name="first_name" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['first_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Last Name</label>
                                <input class="form-control" type="text" name="last_name" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['last_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail</label>
                                <input class="form-control" type="email" name="email" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['email']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Mobile No</label>
                                <input class="form-control" type="text" name="mobile" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['mobile_no']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Address Line 1</label>
                                <input class="form-control" type="text" name="address_line1" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['address_line1']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Address Line 2</label>
                                <input class="form-control" type="text" name="address_line2" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['address_line2']) : ''; ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Country</label>
                                <select class="custom-select" name="country" required>
                                    <option value="United States" <?php if ($billingAddress && $billingAddress['country'] == 'United States') echo 'selected'; ?>>United States</option>
                                    <option value="Afghanistan" <?php if ($billingAddress && $billingAddress['country'] == 'Afghanistan') echo 'selected'; ?>>Afghanistan</option>
                                    <option value="Albania" <?php if ($billingAddress && $billingAddress['country'] == 'Albania') echo 'selected'; ?>>Albania</option>
                                    <option value="Algeria" <?php if ($billingAddress && $billingAddress['country'] == 'Algeria') echo 'selected'; ?>>Algeria</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>City</label>
                                <input class="form-control" type="text" name="city" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['city']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>State</label>
                                <input class="form-control" type="text" name="state" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['state']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>ZIP Code</label>
                                <input class="form-control" type="text" name="zip_code" value="<?php echo $billingAddress ? htmlspecialchars($billingAddress['zip_code']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
            
        </div>
        </div>
    </div><script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to toggle card details visibility
        document.getElementById('paymentMethod').addEventListener('change', function() {
            var method = this.value;
            var accountNumberGroup = document.getElementById('accountNumberGroup');
            var cardDetailsGroup = document.getElementById('cardDetailsGroup');

            if (method === 'card') {
                accountNumberGroup.classList.add('d-none');
                cardDetailsGroup.classList.remove('d-none');
            } else {
                accountNumberGroup.classList.remove('d-none');
                cardDetailsGroup.classList.add('d-none');
            }
        });
    </script>
</body>
</html>
