<?php
// Database connection details
$servername = "localhost";
$username = "root";  //   username
$password = "";  //  password
$dbname = "BloodUnity";  // database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $post = mysqli_real_escape_string($conn, $_POST['post']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Form validation
    if (empty($name) || empty($address) || empty($contact) || empty($post) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required!";
    }

    // Validate password length and symbol
    if (empty($error_message) && (!preg_match('/^(?=.*\W).{8,}$/', $password))) {
        $error_message = "Password must be at least 8 characters long and include at least one symbol.";
    }

    // Check if password and confirm password match
    if (empty($error_message) && $password !== $confirm_password) {
        $error_message = "Passwords do not match. Please re-enter.";
    }

    // Check if email already exists
    if (empty($error_message)) {
        $emailCheck = "SELECT * FROM staff WHERE email = '$email'";
        $result = $conn->query($emailCheck);

        if ($result->num_rows > 0) {
            $error_message = "Email already exists. Please use a different email.";
        }
    }

    // Check if username already exists
    if (empty($error_message)) {
        $usernameCheck = "SELECT * FROM staff WHERE username = '$username'";
        $result = $conn->query($usernameCheck);

        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        }
    }

    // If no error, hash the password and insert into the database
    if (empty($error_message)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL query to insert data
        $sql = "INSERT INTO staff (name, address, contact, post, email, username, password) 
                VALUES ('$name', '$address', '$contact', '$post', '$email', '$username', '$hashedPassword')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to login page after successful registration
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="style4.css">
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="v1.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="register-container">
        <h1>Register</h1>

        <?php
        // Display error message if it exists
        if (!empty($error_message)) {
            echo "<div class='error-message'>$error_message</div>";
        }
        ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="2" placeholder="Enter your address" required><?php echo isset($address) ? $address : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number:</label>
                <input type="tel" id="contact" name="contact" value="<?php echo isset($contact) ? $contact : ''; ?>" placeholder="Enter your contact number" required>
            </div>
             <div class="form-group">
                <label for="post">Post:</label>
                <select id="post" name="post" required>
                    <option value="" disabled selected>Select your post</option>
                    <option value="Doctor" <?php echo isset($post) && $post == 'Doctor' ? 'selected' : ''; ?>>Doctor</option>
                    <option value="Nurse" <?php echo isset($post) && $post == 'Nurse' ? 'selected' : ''; ?>>Nurse</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>
</body>
</html>
