<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

// Fetch donor data with prepared statement
$sql = "SELECT id, name, email, contact, age, blood_type FROM Donor ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching donor data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Donors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h2 class="text-center">Donor List</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Add New Donor Button -->
    <div class="text-end mb-3">
        <a href="add_donor.php" class="btn btn-success">Add New Donor</a>
    </div>

    <!-- Donor List Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Blood Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td><?= htmlspecialchars($row['contact']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['blood_type']) ?></td>
                        <td>
                            <a href="edit_donor.php?id=<?= htmlspecialchars($row['id']) ?>" 
                               class="btn btn-warning btn-sm">Edit</a>
                            <button class="btn btn-danger btn-sm" 
                                    onclick="deleteDonor(<?= htmlspecialchars($row['id']) ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Back to Dashboard -->
    <div class="text-center mt-4">
        <a href="admin_dashboard.html" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<script>
function deleteDonor(id) {
    if (confirm('Are you sure you want to delete this donor?')) {
        window.location.href = `delete_donor.php?id=${id}`;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php 
$stmt->close();
$conn->close(); 
?>