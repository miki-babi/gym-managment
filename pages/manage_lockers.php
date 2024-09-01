<?php
include('../includes/db.php');
include('../includes/header.php');

// Fetch lockers and their due dates
$query = "SELECT l.locker_number, l.due_date, m.name AS member_name 
          FROM lockers l 
          JOIN members m ON l.member_id = m.id 
          ORDER BY l.locker_number";
$result = mysqli_query($conn, $query);

function calculateDaysLeft($dueDate) {
    $currentDate = new DateTime();
    $dueDate = new DateTime($dueDate);

    // Set time to 00:00:00 for accurate comparison
    $currentDate->setTime(0, 0, 0);
    $dueDate->setTime(0, 0, 0);

    $interval = $currentDate->diff($dueDate);

    // Return days left with a positive or negative sign
    return (int)$interval->format('%r%a'); // %r for +/- sign, %a for days
}
?>

<h2>Manage Lockers</h2>
<table border="1">
    <tr>
        <th>Locker Number</th>
        <th>Member Name</th>
        <th>Due Date</th>
        <th>Days Left</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { 
        $daysLeft = calculateDaysLeft($row['due_date']);
    ?>
    <tr>
        <td><?php echo $row['locker_number']; ?></td>
        <td><?php echo htmlspecialchars($row['member_name']); ?></td>
        <td><?php echo $row['due_date']; ?></td>
        <td><?php echo ($daysLeft > 0) ? $daysLeft . ' days' : (($daysLeft == 0) ? 'Due today' : 'Overdue'); ?></td>
    </tr>
    <?php } ?>
</table>

<?php include('../includes/footer.php'); ?>
