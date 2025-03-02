<?php
// Start the session
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Database connection details
    $servername = "localhost";
    $username = "root"; // 
    $password = ""; // 
    $dbname = "BloodUnity";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initialize error message variable
    $error_message = "";

    // Validate and sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $address = htmlspecialchars(trim($_POST['address']));
    $contact = htmlspecialchars(trim($_POST['contact']));
    $age = intval($_POST['age']);
    $blood_type = htmlspecialchars(trim($_POST['blood-type']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Password validation
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$/', $password)) {
        $error_message = "Password must be at least 8 characters long and include at least one symbol.";
    } else {
        // Hash password
        $password = password_hash($password, PASSWORD_BCRYPT);

        // Check if username already exists
        $sql_check_username = "SELECT COUNT(*) FROM Donor WHERE username = ?";
        $stmt_check = $conn->prepare($sql_check_username);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $error_message = "This username is already taken. Please choose another one.";
        } else {
            // Insert data into Donor table
            $sql = "INSERT INTO Donor (name, address, contact, age, blood_type, email, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sssissss", $name, $address, $contact, $age, $blood_type, $email, $username, $password);

                if ($stmt->execute()) {
                    // Redirect to login page after successful registration
                    echo "<script>window.location.href = 'login.php';</script>";
                    exit;
                } else {
                    $error_message = "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="style3.css">
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="v1.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="register-container">
        <h1>Register to BloodUnity</h1>
        <?php
        // Show error message if any
        if (!empty($error_message)) {
            echo "<p style='color:red;'>$error_message</p>";
        }
        ?>
        <form id="registration-form" method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" placeholder="Enter your address" required><?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number:</label>
                <input type="tel" id="contact" name="contact" value="<?php echo isset($_POST['contact']) ? $_POST['contact'] : ''; ?>" placeholder="Enter your contact number" required>
            </div>
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>" min="18" placeholder="Enter your age" required>
            </div>
            <div class="form-group">
                <label for="blood-type">Blood Type:</label>
                <select id="blood-type" name="blood-type" required>
                    <option value="" disabled selected>Select your blood type</option>
                    <option value="A+" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "A+" ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "A-" ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "B+" ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "B-" ? 'selected' : ''; ?>>B-</option>
                    <option value="O+" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "O+" ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "O-" ? 'selected' : ''; ?>>O-</option>
                    <option value="AB+" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "AB+" ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?php echo isset($_POST['blood-type']) && $_POST['blood-type'] === "AB-" ? 'selected' : ''; ?>>AB-</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>
</body>
</html>
