<?php
include('../includes/db.php');
include('../includes/header.php');

// Fetch total members grouped by package
$query = "SELECT package, COUNT(id) as total_members FROM members GROUP BY package";
$result = mysqli_query($conn, $query);
$total_income = 0;

$package_prices = [
    'monthly' => 100,
    'quarterly' => 270,
    'yearly' => 1000
];

// Arrays to hold data for Chart.js
$packages = [];
$incomes = [];
$memberCounts = [];

while ($row = mysqli_fetch_assoc($result)) { 
    $income = $row['total_members'] * $package_prices[$row['package']];
    $total_income += $income;

    // Prepare data for the chart
    $packages[] = ucfirst($row['package']);
    $incomes[] = $income;
    $memberCounts[] = $row['total_members'];
}

// Fetch the number of active users (users whose membership is still valid)
$query_active_users = "SELECT COUNT(*) as active_users FROM members WHERE expiry_date >= CURDATE()";
$result_active_users = mysqli_query($conn, $query_active_users);
$row_active_users = mysqli_fetch_assoc($result_active_users);
$active_users = $row_active_users['active_users'];

// Calculate Average Revenue Per User (ARPU)
$average_revenue_per_user = $active_users > 0 ? round($total_income / $active_users, 2) : 0;

// Fetch total number of lockers and occupied lockers
$query_lockers = "SELECT COUNT(*) as total_lockers, 
                         SUM(CASE WHEN due_date >= CURDATE() THEN 1 ELSE 0 END) as occupied_lockers 
                  FROM lockers";
$result_lockers = mysqli_query($conn, $query_lockers);
$row_lockers = mysqli_fetch_assoc($result_lockers);
$total_lockers = $row_lockers['total_lockers'];
$occupied_lockers = $row_lockers['occupied_lockers'];
$locker_occupancy_rate = $total_lockers > 0 ? round(($occupied_lockers / $total_lockers) * 100, 2) : 0;

// Income breakdown by package (percentage)
$income_percentages = [];
foreach ($incomes as $income) {
    $income_percentages[] = $total_income > 0 ? round(($income / $total_income) * 100, 2) : 0;
}

?>

<h2>Financial Report</h2>
<table>
    <tr>
        <th>Package</th>
        <th>Total Members</th>
        <th>Total Income</th>
        <th>Income Percentage</th>
    </tr>
    <?php 
    // Output the data into the table
    foreach ($packages as $index => $package) { 
    ?>
    <tr>
        <td><?php echo $package; ?></td>
        <td><?php echo $memberCounts[$index]; ?></td>
        <td><?php echo $incomes[$index]; ?> ETB</td>
        <td><?php echo $income_percentages[$index]; ?>%</td>
    </tr>
    <?php } ?>
</table>
<p>Total Income: <?php echo $total_income; ?> ETB</p>

<h3>Active Users: <?php echo $active_users; ?></h3>
<h3>Average Revenue Per User (ARPU): <?php echo $average_revenue_per_user; ?> ETB</h3>
<h3>Locker Occupancy Rate: <?php echo $locker_occupancy_rate; ?>%</h3>

<!-- Create a canvas for the chart -->
<canvas id="incomeChart" width="400" height="200"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('incomeChart').getContext('2d');
    var incomeChart = new Chart(ctx, {
        type: 'bar', // Bar chart
        data: {
            labels: <?php echo json_encode($packages); ?>, // Package names
            datasets: [{
                label: 'Total Income (ETB)',
                data: <?php echo json_encode($incomes); ?>, // Income data
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include('../includes/footer.php'); ?>
