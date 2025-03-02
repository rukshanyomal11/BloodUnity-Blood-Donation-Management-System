<?php 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for a donor ID in the URL to fetch data for editing
if (isset($_GET['id'])) {
    $donor_id = intval($_GET['id']);
    
    // Fetch donor data
    $stmt = $conn->prepare("SELECT id, name, email, contact, age, blood_type FROM Donor WHERE id = ?");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $donor = $result->fetch_assoc();
    } else {
        die("Donor not found!");
    }
}

// Handle form submission for updating donor details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $age = $_POST['age'];
    $blood_type = $_POST['blood_type'];

    // Update donor data
    $stmt = $conn->prepare("UPDATE Donor SET name = ?, email = ?, contact = ?, age = ?, blood_type = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $email, $contact, $age, $blood_type, $donor_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor updated successfully!";
        header("Location: view_donors.php");
        exit;
    } else {
        $_SESSION['error'] = "Error updating donor: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h2 class="text-center">Edit Donor</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($donor['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($donor['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="contact" class="form-label">Contact</label>
            <input type="text" class="form-control" id="contact" name="contact" value="<?= htmlspecialchars($donor['contact']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="age" class="form-label">Age</label>
            <input type="number" class="form-control" id="age" name="age" value="<?= htmlspecialchars($donor['age']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="blood_type" class="form-label">Blood Type</label>
            <input type="text" class="form-control" id="blood_type" name="blood_type" value="<?= htmlspecialchars($donor['blood_type']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Donor</button>
        <a href="view_donors.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>
