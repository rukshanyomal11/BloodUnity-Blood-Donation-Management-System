<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);


// Fetch all donors for dropdown using prepared statement
$sql = "SELECT id, name, blood_type FROM Donor ORDER BY name ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing donor statement: " . $conn->error);
}
$stmt->execute();
$donors = $stmt->get_result();

// Fetch donation history for selected donor
$selected_donor = null;
$donation_history = null;

if (isset($_POST['donor_id'])) {
    $selected_donor_id = intval($_POST['donor_id']);

    // Fetch selected donor details with prepared statement
    $donor_sql = "SELECT id, name, blood_type FROM Donor WHERE id = ?";
    $stmt = $conn->prepare($donor_sql);
    if ($stmt) {
        $stmt->bind_param("i", $selected_donor_id);
        $stmt->execute();
        $selected_donor = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Fetch donation history with prepared statement
        if ($selected_donor) {
            $history_sql = "SELECT * FROM donations WHERE donor_id = ? ORDER BY donation_date DESC";
            $stmt = $conn->prepare($history_sql);
            if ($stmt) {
                $stmt->bind_param("i", $selected_donor_id);
                $stmt->execute();
                $donation_history = $stmt->get_result();
                $stmt->close();
            }
        }
    }
}

// Add new donation
if (isset($_POST['add_donation'])) {
    $donor_id = intval($_POST['donor_id']);
    $donation_units = floatval($_POST['donation_units']);
    $donation_date = $_POST['donation_date'];

    $insert_sql = "INSERT INTO donations (donor_id, donation_units, donation_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    
    if ($stmt) {
        $stmt->bind_param("ids", $donor_id, $donation_units, $donation_date);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Donation added successfully!";
            header("Location: donation_management.php");
            exit();
        } else {
            $_SESSION['error'] = "Error adding donation: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">Donation Management</h2>

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

    <!-- Select Donor -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="donor_id" class="form-label">Select Donor</label>
            <select class="form-select" id="donor_id" name="donor_id" required>
                <option value="">-- Select a Donor --</option>
                <?php while ($row = $donors->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['id']) ?>" 
                            <?= (isset($selected_donor) && $selected_donor['id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['blood_type']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">View History</button>
    </form>

    <?php if ($selected_donor): ?>
        <h3>Donation History for <?= htmlspecialchars($selected_donor['name']) ?> 
            (<?= htmlspecialchars($selected_donor['blood_type']) ?>)</h3>

        <!-- Donation History Table -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Quantity (Units)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($donation_history && $donation_history->num_rows > 0): ?>
                        <?php while ($row = $donation_history->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['donation_date']) ?></td>
                                <td><?= htmlspecialchars($row['donation_units']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">No donations found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Donation Form -->
        <h3>Add Donation</h3>
        <form method="POST" class="mb-4">
            <input type="hidden" name="donor_id" value="<?= htmlspecialchars($selected_donor['id']) ?>">
            <div class="mb-3">
                <label for="donation_units" class="form-label">Donation Units</label>
                <input type="number" class="form-control" id="donation_units" 
                       name="donation_units" step="0.1" min="0.1" max="10" required>
            </div>
            <div class="mb-3">
                <label for="donation_date" class="form-label">Donation Date</label>
                <input type="date" class="form-control" id="donation_date" 
                       name="donation_date" max="<?= date('Y-m-d') ?>" required>
            </div>
            <button type="submit" name="add_donation" class="btn btn-success">Add Donation</button>
        </form>
    <?php endif; ?>

    <!-- Back to Dashboard -->
    <div class="text-center mt-4">
        <a href="admin_dashboard.html" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close all results and connection
if (isset($donors)) $donors->close();
if (isset($donation_history)) $donation_history->close();
$conn->close();
?>