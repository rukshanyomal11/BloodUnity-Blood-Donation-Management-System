<?php
// Start the session first
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['donor_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get donor ID from session
$donor_id = $_SESSION['donor_id'];

// Fetch donor details from the database
$donor_query = "SELECT * FROM Donor WHERE id = ?";
$stmt = $conn->prepare($donor_query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$donor_result = $stmt->get_result();
$donor_data = $donor_result->fetch_assoc();
$stmt->close();

// Fetch donation history for the donor
$donation_query = "SELECT * FROM donations WHERE donor_id = ? ORDER BY donation_date DESC";
$stmt = $conn->prepare($donation_query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$donations_result = $stmt->get_result();
$stmt->close();

// Handle form submission for appointment scheduling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = $_POST['donation-location'];
    $appointment_date = $_POST['appointment-date'];
    $appointment_time = $_POST['appointment-time'];

    // Insert the new appointment into the database
    $appointment_query = "INSERT INTO appointments (donor_id, location, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($appointment_query);
    $stmt->bind_param("isss", $donor_id, $location, $appointment_date, $appointment_time);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Appointment scheduled successfully!";
    } else {
        $_SESSION['error'] = "Error scheduling appointment: " . $stmt->error;
    }
    $stmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch the most recent appointment
$appointment_query = "SELECT * FROM appointments WHERE donor_id = ? ORDER BY appointment_date DESC LIMIT 1";
$stmt = $conn->prepare($appointment_query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$appointment_result = $stmt->get_result();
$appointment_data = $appointment_result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodUnity Donor Dashboard</title>
    <link rel="stylesheet" href="style5.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h1>BloodUnity Donor Dashboard</h1>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($donor_data['name']); ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <!-- Dashboard Layout -->
    <div class="dashboard">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="#donor-profile">My Profile</a></li>
                <li><a href="#donation-history">Donation History</a></li>
                <li><a href="#appointment-scheduling">Appointment Scheduling</a></li>
                <li><a href="#my-appointment">My Appointment</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="content">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="success-message">
                    <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- My Profile Section -->
            <section id="donor-profile">
                <h2>My Profile</h2>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($donor_data['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($donor_data['email']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($donor_data['contact']); ?></p>
                    <p><strong>Blood Type:</strong> <?php echo htmlspecialchars($donor_data['blood_type']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($donor_data['address']); ?></p>
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($donor_data['age']); ?></p>
                </div>
            </section>

            <!-- Donation History Section -->
            <section id="donation-history">
                <h2>Donation History</h2>
                <?php if ($donations_result->num_rows > 0): ?>
                    <table class="donation-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Units Donated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($donation = $donations_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donation['donation_date']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['donation_units']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No donation history available.</p>
                <?php endif; ?>
            </section>

            <!-- Appointment Scheduling Section -->
            <section id="appointment-scheduling">
                <h2>Schedule New Appointment</h2>
                <form action="" method="POST" class="appointment-form">
                    <div class="form-group">
                        <label for="donation-location">Select Donation Location:</label>
                        <select id="donation-location" name="donation-location" required>
                            <option value="">Select a location</option>
                            <option value="Hospital Kuala Lumpur">Hospital 1</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appointment-date">Choose Donation Date:</label>
                        <input type="date" id="appointment-date" name="appointment-date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="appointment-time">Choose Time Slot:</label>
                        <select id="appointment-time" name="appointment-time" required>
                            <option value="">Select a time slot</option>
                            <option value="9:00 AM - 12:00 PM">9:00 AM - 12:00 PM</option>
                            <option value="12:00 PM - 3:00 PM">12:00 PM - 3:00 PM</option>
                            <option value="3:00 PM - 6:00 PM">3:00 PM - 6:00 PM</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn">Schedule Appointment</button>
                </form>
            </section>

            <!-- My Appointment Section -->
            <section id="my-appointment">
                <h2>My Upcoming Appointment</h2>
                <?php if ($appointment_data): ?>
                    <div class="appointment-info">
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($appointment_data['location']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($appointment_data['appointment_date']); ?></p>
                        <p><strong>Time Slot:</strong> <?php echo htmlspecialchars($appointment_data['appointment_time']); ?></p>
                    </div>
                <?php else: ?>
                    <p>No upcoming appointments.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
<?php
// Close the connection at the very end
$conn->close();
?>