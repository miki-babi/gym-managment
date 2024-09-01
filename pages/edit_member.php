<?php
include('../includes/db.php');
include('../includes/header.php');

// Fetch the member ID from the URL
$memberId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $package = $_POST['package'];
    $photo = $_FILES['photo'];
    
    // Get the existing member details
    $query = "SELECT * FROM members WHERE id = $memberId";
    $result = mysqli_query($conn, $query);
    $member = mysqli_fetch_assoc($result);

    // Generate the new photo filename if a new photo is uploaded
    $photoFilename = $member['photo'];
    if ($photo['tmp_name']) {
        $uniqueId = time() . '-' . preg_replace('/\s+/', '-', strtolower($name));
        $photoExtension = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $photoFilename = $uniqueId . '.' . $photoExtension;
        $photoPath = '../images/' . $photoFilename;
        
        if (move_uploaded_file($photo['tmp_name'], $photoPath)) {
            $photoUrl = '/images/' . $photoFilename;
        } else {
            $photoUrl = $member['photo'];
        }
    } else {
        $photoUrl = $member['photo'];
    }

    // Update member details
    $query = "UPDATE members 
              SET name = '$name', phone = '$phone', package = '$package', photo = '$photoUrl' 
              WHERE id = $memberId";
    
    if (mysqli_query($conn, $query)) {
        echo "<p>Member details updated successfully!</p>";
    } else {
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
}

// Fetch the member details for the form
$query = "SELECT * FROM members WHERE id = $memberId";
$result = mysqli_query($conn, $query);
$member = mysqli_fetch_assoc($result);
?>

<h2>Edit Member</h2>
<form method="post" enctype="multipart/form-data">
    <label>Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" required><br>
    <label>Phone:</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>" required><br>
    <label>Package:</label>
    <select name="package">
        <option value="monthly" <?php echo ($member['package'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
        <option value="quarterly" <?php echo ($member['package'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
        <option value="yearly" <?php echo ($member['package'] == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
    </select><br>
    <label>Photo:</label>
    <?php if ($member['photo']) { ?>
        <img src="<?php echo $member['photo']; ?>" alt="Current Photo" width="100"><br>
    <?php } ?>
    <input type="file" name="photo"><br>
    <input type="submit" value="Update">
</form>

<?php include('../includes/footer.php'); ?>
