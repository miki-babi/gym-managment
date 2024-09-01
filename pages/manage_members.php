<?php
include('../includes/db.php');
include('../includes/header.php');

// Join members and payments tables to get the next payment date for each member
$query = "SELECT m.*, p.next_payment_date 
          FROM members m 
          LEFT JOIN payments p ON m.id = p.member_id 
          ORDER BY m.id";
$result = mysqli_query($conn, $query);

function calculateDaysLeft($nextPaymentDate) {
    $currentDate = new DateTime();
    $nextPayment = new DateTime($nextPaymentDate);
    
    // Set time to 00:00:00 for accurate comparison
    $currentDate->setTime(0, 0, 0);
    $nextPayment->setTime(0, 0, 0);
    
    $interval = $currentDate->diff($nextPayment);
    
    // Return days left with a positive or negative sign
    return (int)$interval->format('%r%a'); // %r for +/- sign, %a for days
}
?>

<h2>Manage Members</h2>
<table>
    <tr>
        <th>Name</th>
        <th>Phone</th>
        <th>Package</th>
        <th>Photo</th>
        <th>Days Left</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { 
        $daysLeft = calculateDaysLeft($row['next_payment_date']);
    ?>
    <tr>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['phone']; ?></td>
        <td><?php echo $row['package']; ?></td>
        <td><img src="http://localhost/gym-managment/<?php echo $row['photo']; ?>" alt="Member Photo" width="50"></td>
        <td><?php echo ($daysLeft > 0) ? $daysLeft . ' days' : (($daysLeft == 0) ? 'Due today' : 'Overdue'); ?></td>
        <td>
            <a href="http://localhost/gym-managment/pages/edit_member.php?id=<?php echo $row['id']; ?>">Edit</a> | 
            <a href="delete_member.php?id=<?php echo $row['id']; ?>">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include('../includes/footer.php'); ?>
