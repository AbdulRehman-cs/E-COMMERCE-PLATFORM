<!DOCTYPE html>
<html lang="en">

<?php 
session_start();

include 'category/CategoryManager.php'; // Include your CategoryManager
$categoryManager = new CategoryManager();

if (!isset($_COOKIE['user_id'])) {
    echo "Error: User not logged in.";
    exit();
}
$message='';
$userId = $_COOKIE['user_id'];

// Fetch cart items for the user
$cartItems = $categoryManager->getCartItemsByUserId($userId);

// Initialize total variables
$subtotalAmount = 0;
$shippingCost = 10; // Example shipping cost
$totalDiscountCash = 0; // Total discount in cash overall

// Pre-fetch discounts per product to use later in the page
$productDiscountsMap = [];
foreach ($cartItems as $item) {
    $productDiscountsMap[$item['product_id']] = $categoryManager->getDiscountsByProductId($item['product_id']);
}
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
include "header.php"; 
?>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="#">Home</a>
                <a class="breadcrumb-item text-dark" href="#">Shop</a>
                <span class="breadcrumb-item active">Shopping Cart</span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Cart Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-lg-8 table-responsive mb-5" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-light table-borderless table-hover text-center mb-0">
                <thead class="thead-dark" style="position: sticky; top: 0; background-color: #343a40; color: white; z-index: 2;">
                    <tr>
                        <th>Select</th>
                        <th>Products</th>
                        <th>Price</th>
                        <th>Colors</th>
                        <th>Sizes</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Discount</th>
                        <th>Shipping</th>
                        <th>Commission</th>
                        <th>Total</th>
                        <th>Discount Level</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php 
                    foreach ($cartItems as $item): 
                        $discounts = $productDiscountsMap[$item['product_id']] ?? [];
                        $applicableDiscountPercent = 0;
                        // Determine applicable discount percent based on quantity
                        foreach ($discounts as $discount) {
                            if ($item['quantity'] >= $discount['quantity']) {
                                $applicableDiscountPercent = $discount['discount'];
                            }
                        }
                        $unitPrice = $item['price'];
                        $quantity = $item['quantity'];
                        $subtotal = $unitPrice * $quantity;
                        $discountAmountTotal = $subtotal * $applicableDiscountPercent / 100;
                        $shipping = 10; // fixed shipping per item for calculation
                        $commission = ($subtotal - $discountAmountTotal) * 0.02;
                        $totalPrice = $subtotal - $discountAmountTotal + $shipping + $commission;

                        $subtotalAmount += $subtotal - $discountAmountTotal;
                        $totalDiscountCash += $discountAmountTotal;
                    ?>
                    <tr>
                        <td class="align-middle">
                            <input type="checkbox" class="item-checkbox" id="checkbox-<?php echo $item['id']; ?>" data-id="<?php echo $item['id']; ?>" data-productid="<?php echo $item['product_id']; ?>" data-color="<?php echo htmlspecialchars($item['color']); ?>" data-size="<?php echo htmlspecialchars($item['size']); ?>" checked>
                        </td>
                        <td class="align-middle">
                            <img src="dash/<?php echo htmlspecialchars($item['image_paths'][0]); ?>" alt="" style="width: 50px;"> 
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </td>
                        <td class="align-middle">
  $<span class="item-price" id="item-price-<?php echo $item['id']; ?>"><?php echo number_format($unitPrice, 2); ?></span>
</td>
<td class="align-middle">
  <span class="item-color" id="color-<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['colors']); ?></span>
</td>
<td class="align-middle">
  <span class="item-size" id="size-<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['sizes']); ?></span>
</td>

                        <td class="align-middle">
                            <div class="input-group quantity mx-auto" style="width: 100px;">
                                <input type="number" class="form-control form-control-sm bg-secondary border-0 text-center" value="<?php echo $quantity; ?>" id="quantity-<?php echo $item['id']; ?>" min="1" onchange="updateQuantity(<?php echo $item['id']; ?>)">
                            </div>
                        </td>
                        <td class="align-middle">$<span class="item-subtotal" id="item-subtotal-<?php echo $item['id']; ?>"><?php echo number_format($subtotal, 2); ?></span></td>
                        <td class="align-middle">$<span class="item-discount" id="item-discount-<?php echo $item['id']; ?>"><?php echo number_format($discountAmountTotal, 2); ?></span></td>
                        <td class="align-middle">$<span class="item-shipping" id="item-shipping-<?php echo $item['id']; ?>"><?php echo number_format($shipping, 2); ?></span></td>
                        <td class="align-middle">$<span class="item-commission" id="item-commission-<?php echo $item['id']; ?>"><?php echo number_format($commission, 2); ?></span></td>
                        <td class="align-middle">$<span class="item-total" id="item-total-<?php echo $item['id']; ?>"><?php echo number_format($totalPrice, 2); ?></span></td>
                        <td class="align-middle">
                            <button class="btn btn-info btn-sm" onclick="showDiscountLevel(<?php echo $item['id']; ?>)">Show</button>
                            <div id="discount-level-<?php echo $item['id']; ?>" class="discount-level-details mt-2" style="display:none; text-align:left; font-size:0.9rem;">
                                <strong>Discount Levels:</strong><br>
                                <?php if (!empty($discounts)): ?>
                                    <ul style="padding-left: 15px; margin-top:5px;">
                                    <?php foreach ($discounts as $discountLevel): ?>
                                        <li>Qty &ge; <?php echo htmlspecialchars($discountLevel['quantity']); ?>: <?php echo htmlspecialchars($discountLevel['discount']); ?>% off</li>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    No discounts available
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="align-middle">
                            <button class="btn btn-sm btn-danger" onclick="removeFromCart(<?php echo $item['id']; ?>)"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table> 
     <?php if ($billingAddress): ?>
        
        <button class="btn btn-success my-3" id="proceed-checkout-btn" onclick="proceedToCheckout()">Proceed To Checkout</button>
    <?php else: ?>
        <p class="alert alert-warning alert-dismissible fade show">Please add or update your billing address before proceeding to checkout.</p>
        <h5  hidden class="modal-title" id="addressModalLabel">Add Address</h5>
        <!--  Your address form or modal trigger would go here -->
        <button class="btn btn-primary" data-toggle="modal" data-target="#addressModal">Add Address</button>
    <?php endif;  ?>
        </div>
        <div class="col-lg-4">
        <div class="container mt-5">

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
            <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Cart Summary of Selected Items</span></h5>
            <div class="bg-light p-30 mb-5">
                <div class="border-bottom pb-2">
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Subtotal</h6>
                        <h6>$<span id="summary-subtotal">0.00</span></h6>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Total Discount</h6>
                        <h6>-$<span id="summary-discount">0.00</span></h6>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Shipping</h6>
                        <h6>$<span id="summary-shipping">0.00</span></h6>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Commission</h6>
                        <h6>$<span id="summary-commission">0.00</span></h6>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="d-flex justify-content-between mt-2">
                        <h5>Total</h5>
                        <h5>$<span id="summary-total">0.00</span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Cart End -->

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Your Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmationBody" style="max-height:400px; overflow-y:auto;">
                <!-- Selected items will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmCheckoutBtn">Confirm Checkout</button>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script>
function showDiscountLevel(itemId) {
    const detailDiv = document.getElementById('discount-level-' + itemId);
    detailDiv.style.display = detailDiv.style.display === "none" ? "block" : "none";
}

function updateQuantity(itemId) {
    const quantityInput = document.getElementById('quantity-' + itemId);
    let quantity = parseInt(quantityInput.value);
    if (quantity < 1) quantity = 1;
    quantityInput.value = quantity;
    recalcItemTotals(itemId);
    updateDiscountAndTotalsAll();
    updateCashSummary();
}

function recalcItemTotals(itemId) {
    const quantity = parseInt(document.getElementById('quantity-' + itemId).value);
    const unitPrice = parseFloat(document.getElementById('item-price-' + itemId).innerText);
    
    let discounts = window.productDiscounts && window.productDiscounts[itemId] ? window.productDiscounts[itemId] : [];
    let applicableDiscountPercent = 0;
    discounts.forEach(discount => {
        if (quantity >= discount.quantity) {
            applicableDiscountPercent = discount.discount;
        }
    });

    const subtotal = unitPrice * quantity;
    const discountAmount = subtotal * (applicableDiscountPercent / 100);
    const shipping = 10; // fixed shipping cost
    const discountedSubtotal = subtotal - discountAmount;
    const commission = discountedSubtotal * 0.02;
    const totalPrice = discountedSubtotal + commission + shipping;

    document.getElementById('item-subtotal-' + itemId).innerText = subtotal.toFixed(2);
    document.getElementById('item-discount-' + itemId).innerText = discountAmount.toFixed(2);
    document.getElementById('item-shipping-' + itemId).innerText = shipping.toFixed(2);
    document.getElementById('item-commission-' + itemId).innerText = commission.toFixed(2);
    document.getElementById('item-total-' + itemId).innerText = totalPrice.toFixed(2);
}

function updateDiscountAndTotalsAll() {
    let subtotal = 0;
    let totalDiscountCash = 0;
    let shipping = 10; // fixed shipping cost per item (considered once per item here)
    let commissionTotal = 0;

    document.querySelectorAll('tbody tr').forEach(row => {
        const itemId = row.querySelector('input[id^="quantity-"]').id.split('-')[1];
        const quantity = parseInt(document.getElementById('quantity-' + itemId).value);
        const unitPrice = parseFloat(document.getElementById('item-price-' + itemId).innerText);

        let discounts = window.productDiscounts && window.productDiscounts[itemId] ? window.productDiscounts[itemId] : [];
        let applicableDiscountPercent = 0;
        discounts.forEach(discount => {
            if (quantity >= discount.quantity) {
                applicableDiscountPercent = discount.discount;
            }
        });

        const itemSubtotal = unitPrice * quantity;
        const discountAmount = itemSubtotal * (applicableDiscountPercent / 100);
        const discountedPriceTotal = itemSubtotal - discountAmount;
        const commission = discountedPriceTotal * 0.02;

        subtotal += discountedPriceTotal;
        totalDiscountCash += discountAmount;
        commissionTotal += commission;
    });

    // Update UI if needed (not used currently)
}

function updateCashSummary() {
    const shipping = 10; // fixed shipping per checkout
    let subtotal = 0;
    let discount = 0;
    let commission = 0;
    let totalShipping = 0;

    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const itemId = checkbox.getAttribute('data-id');
        const quantity = parseInt(document.getElementById('quantity-' + itemId).value);
        const unitPrice = parseFloat(document.getElementById('item-price-' + itemId).innerText);

        let discounts = window.productDiscounts && window.productDiscounts[itemId] ? window.productDiscounts[itemId] : [];
        let applicableDiscountPercent = 0;
        discounts.forEach(discountLevel => {
            if (quantity >= discountLevel.quantity) {
                applicableDiscountPercent = discountLevel.discount;
            }
        });

        const itemSubtotal = unitPrice * quantity;
        const itemDiscount = itemSubtotal * (applicableDiscountPercent / 100);
        const discountedSubtotal = itemSubtotal - itemDiscount;
        const itemCommission = discountedSubtotal * 0.02;
        const itemShipping = shipping;

        subtotal += discountedSubtotal;
        discount += itemDiscount;
        commission += itemCommission;
        totalShipping += itemShipping;
    });

    document.getElementById('summary-subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('summary-discount').innerText = discount.toFixed(2);
    document.getElementById('summary-shipping').innerText = totalShipping.toFixed(2);
    document.getElementById('summary-commission').innerText = commission.toFixed(2);
    document.getElementById('summary-total').innerText = (subtotal + commission + totalShipping).toFixed(2);
}

document.addEventListener('change', function(e) {
    if (e.target && (e.target.classList.contains('item-checkbox') || e.target.id.startsWith('quantity-'))) {
        updateCashSummary();
    }
});

function closeModal() {
    // Hide modal using Bootstrap's modal method or fallback
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        $(modal).modal('hide');
    }
}

function proceedToCheckout() {
    const selectedItems = [];

    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const itemId = checkbox.getAttribute('data-id');
        const color = document.getElementById('color-' + itemId)?.innerText ;
        const size = document.getElementById('size-' + itemId)?.innerText ;
        const quantity = parseInt(document.getElementById('quantity-' + itemId).value);
        const unitPrice = parseFloat(document.getElementById('item-price-' + itemId).innerText);
        const discountPercent = parseFloat(document.getElementById('item-discount-' + itemId).innerText);
        const shipping = parseFloat(document.getElementById('item-shipping-' + itemId).innerText);
        const commission = parseFloat(document.getElementById('item-commission-' + itemId).innerText);
        const totalPrice = parseFloat(document.getElementById('item-total-' + itemId).innerText);

        selectedItems.push({
            product_id: checkbox.getAttribute('data-productid'),
            quantity: quantity,
            color: color,
            size: size,
            unit_price: unitPrice,
            discount_percent: discountPercent,
            shipping: shipping,
            commission: commission,
            total_price: totalPrice
        });
    });

    if (selectedItems.length === 0) {
        alert('Please select at least one item to proceed to checkout.');
        return;
    }

    // Show confirmation modal with items listed
    const confirmationBody = document.getElementById('confirmationBody');
    confirmationBody.innerHTML = ''; // Clear previous content

    selectedItems.forEach((item, index) => {
        const discountCash = (item.discount_percent).toFixed(2);
        confirmationBody.innerHTML += `
            <div style="border-bottom:1px solid #ccc; padding:10px 0;">
                <p><strong>Item ${index + 1}</strong></p>
                <p><strong>Product ID:</strong> ${item.product_id}</p>
                <p><strong>Quantity:</strong> ${item.quantity}</p>
                <p><strong>Color:</strong> ${item.color}</p>
                <p><strong>Size:</strong> ${item.size}</p>
                <p><strong>Unit Price:</strong> $${item.unit_price.toFixed(2)}</p>
                <p><strong>Discount:</strong> $${item.discount_percent.toFixed(2)}</p>
                <p><strong>Shipping:</strong> $${item.shipping.toFixed(2)}</p>
                <p><strong>Commission:</strong> $${item.commission.toFixed(2)}</p>
                <p><strong>Total Price:</strong> $${item.total_price.toFixed(2)}</p>
            </div>
        `;
    });

    // Show Bootstrap modal (assuming jQuery is loaded and Bootstrap JS)
    $('#confirmationModal').modal('show');

    // Set confirm button handler
    const confirmBtn = document.getElementById('confirmCheckoutBtn');
    confirmBtn.onclick = function() {
        // Proceed to send selectedItems to server
        fetch('checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'selectedItems=' + encodeURIComponent(JSON.stringify(selectedItems))
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                $('#confirmationModal').modal('hide');
                if (data.status === 'success') {
                    window.location.href = 'o.php';
                } else {
               } 
            } catch (err) {
                console.error('Failed to parse JSON response:', err);
                console.error('Response text:', text);
                alert('Invalid server response: ' + text);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while processing your request: ' + error.message);
        });
    };
}

function removeFromCart(cartId) {
    alert('Remove function is not implemented in this demo.');
}

// Initialize all calculations on page load and set all checkboxes checked by default
window.onload = function() {
    document.querySelectorAll('input[id^="quantity-"]').forEach(input => {
        const itemId = input.id.split('-')[1];
        updateQuantity(itemId);
    });
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = true);
    updateCashSummary();
};

window.productDiscounts = {};
<?php
foreach ($cartItems as $item) {
    $discounts = $productDiscountsMap[$item['product_id']] ?? [];
    $jsDiscounts = json_encode($discounts);
    echo "window.productDiscounts[{$item['id']}] = {$jsDiscounts};";
}
?>
</script>

<!-- Bootstrap 4 and jQuery scripts for modal functionality (Ensure these are included in your footer or add here) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</html>

