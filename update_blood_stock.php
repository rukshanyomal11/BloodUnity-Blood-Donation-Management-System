<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

// Add or Update Blood Stock
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $blood_type = $_POST['blood_type'];
    $units_change = $_POST['units_change']; // Positive for add, negative for remove

    // Check if the blood type already exists
    $sql = "SELECT * FROM blood_stock WHERE blood_type = '$blood_type'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update the existing record (Add or Remove stock)
        $row = $result->fetch_assoc();
        $current_units = $row['units_available'];
        
        // Make sure that units don't go below zero when removing
        $new_units = $current_units + $units_change;
        if ($new_units < 0) {
            $message = "Error: Insufficient stock to remove.";
        } else {
            $sql = "UPDATE blood_stock SET units_available = $new_units, last_updated = NOW() WHERE blood_type = '$blood_type'";
            if ($conn->query($sql) === TRUE) {
                $message = "Blood stock updated successfully!";
            } else {
                $message = "Error updating record: " . $conn->error;
            }
        }
    } else {
        // Add new blood type record if it doesn't exist
        if ($units_change > 0) {
            $sql = "INSERT INTO blood_stock (blood_type, units_available) VALUES ('$blood_type', '$units_change')";
            if ($conn->query($sql) === TRUE) {
                $message = "New blood type added successfully!";
            } else {
                $message = "Error adding record: " . $conn->error;
            }
        } else {
            $message = "Error: You cannot add a negative number of units for a new blood type.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Blood Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h2 class="text-center">Add/Update Blood Stock</h2>

    <!-- Display success/error message -->
    <?php if (isset($message)) { ?>
        <div class="alert alert-info"><?= $message; ?></div>
    <?php } ?>

    <!-- Blood Stock Form -->
    <form method="POST">
        <div class="mb-3">
            <label for="blood_type" class="form-label">Blood Type</label>
            <select class="form-select" id="blood_type" name="blood_type" required>
                <option value="" disabled selected>Choose a blood type</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="units_change" class="form-label">Units to Add/Remove</label>
            <input type="number" class="form-control" id="units_change" name="units_change" required>
            <small class="form-text text-muted">Enter a positive number to add or a negative number to remove units.</small>
        </div>
        <button type="submit" class="btn btn-primary">Add/Update Stock</button>
    </form>

    <hr>
    <a href="monitor_blood_stock.php" class="btn btn-secondary">Back to Blood Stock Monitoring</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>

