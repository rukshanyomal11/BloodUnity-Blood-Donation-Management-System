<?php
// Start the session at the very beginning
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['donor_id'])) {
    header("Location: ddashboard.php");
    exit();
} elseif (isset($_SESSION['staff_id'])) {
    header("Location: admin_dashboard.html");
    exit();
}

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

$error_message = '';

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare and sanitize input data
    $login_type = htmlspecialchars($_POST['login-type'], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password']; // Passwords shouldn't be sanitized as they might contain special characters
    
    // Form validation
    if (empty($login_type) || empty($username) || empty($password)) {
        $error_message = "All fields are required!";
    } else {
        // Prepare statement based on login type
        if ($login_type === "donor") {
            $stmt = $conn->prepare("SELECT id, name, password FROM Donor WHERE username = ?");
        } elseif ($login_type === "staff") {
            $stmt = $conn->prepare("SELECT id, name, password FROM staff WHERE username = ?");
        } else {
            $error_message = "Invalid login type selected.";
        }

        if (isset($stmt)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    if ($login_type === "donor") {
                        $_SESSION['donor_id'] = $user['id'];
                        $_SESSION['donor_name'] = $user['name'];
                        $_SESSION['user_type'] = 'donor';
                        header("Location: ddashboard.php");
                    } else {
                        $_SESSION['staff_id'] = $user['id'];
                        $_SESSION['staff_name'] = $user['name'];
                        $_SESSION['user_type'] = 'staff';
                        header("Location: admin_dashboard.html");
                    }
                    exit();
                } else {
                    $error_message = "Invalid password. Please try again.";
                }
            } else {
                $error_message = "Username not found. Please check your credentials.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BloodUnity</title>
    <link rel="stylesheet" href="style3.css">
</head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop id="background-video">
        <source src="v1.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <!-- Login Container -->
    <div class="register-container">
        <h1>Login to BloodUnity</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
    <?php echo htmlspecialchars($error_message); ?>
</div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
            <!-- Login Type Dropdown -->
            <div class="form-group">
                <label for="login-type">Login Type:</label>
                <select id="login-type" name="login-type" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="donor" <?php echo (isset($_POST['login-type']) && $_POST['login-type'] === 'donor') ? 'selected' : ''; ?>>Donor</option>
                    <option value="staff" <?php echo (isset($_POST['login-type']) && $_POST['login-type'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>

            <!-- Username Field -->
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" 
                       placeholder="Enter your username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" 
                       placeholder="Enter your password" 
                       required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>
