<?php
include('../includes/db.php');
include('../includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $package = $_POST['package'];
    $photo = $_FILES['photo'];

    // Check if a member with the same phone number already exists
    $checkQuery = "SELECT * FROM members WHERE phone = '$phone'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        echo "<p>Error: A member with this phone number already exists.</p>";
    } else {
        // Generate a unique filename for the photo
        $uniqueId = time() . '-' . preg_replace('/\s+/', '-', strtolower($name));
        $photoExtension = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $photoFilename = $uniqueId . '.' . $photoExtension;
        $photoPath = '../images/' . $photoFilename;

        // Handle photo upload
        if (move_uploaded_file($photo['tmp_name'], $photoPath)) {
            $photoUrl = '/images/' . $photoFilename;
        } else {
            $photoUrl = '';
        }

        // Calculate expiry date based on the package
        $currentDate = new DateTime();
        switch ($package) {
            case 'monthly':
                $expiryDate = $currentDate->modify('+1 month')->format('Y-m-d');
                break;
            case 'quarterly':
                $expiryDate = $currentDate->modify('+3 months')->format('Y-m-d');
                break;
            case 'yearly':
                $expiryDate = $currentDate->modify('+1 year')->format('Y-m-d');
                break;
            default:
                $expiryDate = $currentDate->format('Y-m-d');
        }

        // Save member details including the photo
        $query = "INSERT INTO members (name, phone, package, photo, expiry_date) VALUES ('$name', '$phone', '$package', '$photoUrl', '$expiryDate')";
        if (mysqli_query($conn, $query)) {
            // Get the member ID
            $memberId = mysqli_insert_id($conn);

            // Record the payment
            $amount = ($package == 'monthly') ? 50 : (($package == 'quarterly') ? 140 : 500); // Example amounts
            $paymentQuery = "INSERT INTO payments (member_id, amount, payment_date, next_payment_date) VALUES ('$memberId', '$amount', NOW(), '$expiryDate')";
            mysqli_query($conn, $paymentQuery);

            echo "<p>Member registered successfully!</p>";
        } else {
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<h2>Register New Member</h2>
<form method="post" enctype="multipart/form-data">
    <label>Name:</label><input type="text" name="name" required><br>
    <label>Phone:</label><input type="text" name="phone" required><br>
    <label>Package:</label>
    <select name="package">
        <option value="monthly">Monthly</option>
        <option value="quarterly">Quarterly</option>
        <option value="yearly">Yearly</option>
    </select><br>
    <label>Photo:</label><input type="file" name="photo" required><br>
    <input type="submit" value="Register">
</form>

<button>
<a href="http://localhost/gym-management/pages/register_locker.php" style="text-decoration: none;">Register Locker</a>
</button>

<?php include('../includes/footer.php'); ?>
