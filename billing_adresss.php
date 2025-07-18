<?php 
session_start();

include 'category/CategoryManager.php'; // Include your CategoryManager
$categoryManager = new CategoryManager();

if (!isset($_COOKIE['user_id'])) {
    echo "Error: User not logged in.";
    exit();
}

$userId = $_COOKIE['user_id'];

// Fetch billing address for the user
$billingAddress = $categoryManager->getBillingAddressByUserId($userId);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
?>
<?php include "header.php"; ?>
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
            <?php if ($billingAddress): ?>
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

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
