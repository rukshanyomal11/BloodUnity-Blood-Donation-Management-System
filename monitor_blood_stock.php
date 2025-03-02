<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

// Fetch blood stock data
$sql = "SELECT * FROM blood_stock";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Blood Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h2 class="text-center">Monitor Blood Stock</h2>

    <!-- Blood Stock List Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Blood Type</th>
                <th>Units Available</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['blood_type'] ?></td>
                    <td><?= $row['units_available'] ?></td>
                    <td><?= $row['last_updated'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Button to Add/Update Blood Stock -->
    <div class="text-center mt-4">
        <a href="update_blood_stock.php" class="btn btn-primary">Add/Update Blood Stock</a>
    </div>

    <div class="text-center mt-4">
    <a href="admin_dashboard.html" class="btn btn-secondary">Back to Dashboard</a>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>
