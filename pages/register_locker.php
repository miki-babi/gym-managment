<?php
include('../includes/db.php');
include('../includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $locker_number = $_POST['locker_number'];
    $member_id = $_POST['member_id'];

    // Check if the locker is already assigned to a member
    $checkLockerQuery = "SELECT id FROM lockers WHERE locker_number = '$locker_number'";
    $lockerResult = mysqli_query($conn, $checkLockerQuery);

    // Check if the member already has a locker
    $checkMemberQuery = "SELECT id FROM lockers WHERE member_id = '$member_id'";
    $memberResult = mysqli_query($conn, $checkMemberQuery);

    if (mysqli_num_rows($lockerResult) > 0) {
        echo "<p style='color:red;'>Error: This locker is already assigned to another member.</p>";
    } elseif (mysqli_num_rows($memberResult) > 0) {
        echo "<p style='color:red;'>Error: This member already has a locker assigned.</p>";
    } else {
        // Calculate the due date (1 month from now)
        $due_date = date('Y-m-d', strtotime('+1 month'));

        // Insert the new locker assignment
        $query = "INSERT INTO lockers (locker_number, member_id, due_date) VALUES ('$locker_number', '$member_id', '$due_date')";
        if (mysqli_query($conn, $query)) {
            echo "<p style='color:green;'>Locker registered successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}

// Get the list of members
$query = "SELECT * FROM members";
$members = mysqli_query($conn, $query);
?>

<h2>Register Locker</h2>
<form method="post">
    <label>Locker Number:</label><input type="text" name="locker_number" required><br>
    <label>Assign to Member:</label>
    <select name="member_id">
        <?php while ($row = mysqli_fetch_assoc($members)) { ?>
        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
        <?php } ?>
    </select><br>
    <input type="submit" value="Register Locker">
</form>

<?php include('../includes/footer.php'); ?>
