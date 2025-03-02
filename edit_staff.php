<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get staff ID from URL
if (!isset($_GET['id'])) {
    header('Location: staff_management.php');
    exit();
}

$staff_id = intval($_GET['id']);

// Fetch existing staff data
$fetch_sql = "SELECT * FROM staff WHERE id = ?";
$stmt = $conn->prepare($fetch_sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Staff member not found.</div>";
    exit();
}

$staff = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_staff'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $post = trim($_POST['post']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    
    // Check if email is changed and already exists
    if ($email !== $staff['email']) {
        $check_email = $conn->prepare("SELECT email FROM staff WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $staff_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            echo "<div class='alert alert-danger'>This email address is already in use.</div>";
            $check_email->close();
            exit();
        }
        $check_email->close();
    }

    // Prepare update query - with or without password
    if (!empty($_POST['password'])) {
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $sql = "UPDATE staff SET name=?, address=?, contact=?, post=?, email=?, username=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $name, $address, $contact, $post, $email, $username, $password, $staff_id);
    } else {
        $sql = "UPDATE staff SET name=?, address=?, contact=?, post=?, email=?, username=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $address, $contact, $post, $email, $username, $staff_id);
    }
    
    try {
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Staff member updated successfully! 
                  <a href='staff_management.php'>Return to staff list</a></div>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "<div class='alert alert-danger'>This username or email is already taken.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating staff member: " . $e->getMessage() . "</div>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h2>Edit Staff Member</h2>

    <form method="POST" onsubmit="return validateForm()">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?= htmlspecialchars($staff['name']) ?>" required maxlength="100">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" required rows="3"><?= htmlspecialchars($staff['address']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="contact" class="form-label">Contact Number</label>
            <input type="tel" class="form-control" id="contact" name="contact" 
                   value="<?= htmlspecialchars($staff['contact']) ?>" required pattern="[0-9+\-\s()]{8,20}">
        </div>
        <div class="mb-3">
            <label for="post" class="form-label">Post/Role</label>
            <input type="text" class="form-control" id="post" name="post" 
                   value="<?= htmlspecialchars($staff['post']) ?>" required maxlength="50">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= htmlspecialchars($staff['email']) ?>" required maxlength="100">
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?= htmlspecialchars($staff['username']) ?>" required maxlength="50">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" minlength="8">
            <div class="form-text">Leave blank to keep current password. New password must be at least 8 characters.</div>
        </div>
        <button type="submit" name="update_staff" class="btn btn-primary">Update Staff</button>
        <a href="staff_management.php" class="btn btn-secondary">Back to Staff List</a>
    </form>
</div>

<script>
function validateForm() {
    const email = document.getElementById('email').value;
    const contact = document.getElementById('contact').value;
    const password = document.getElementById('password').value;
    
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const contactPattern = /^[0-9+\-\s()]{8,20}$/;
    
    if (!emailPattern.test(email)) {
        alert('Please enter a valid email address');
        return false;
    }
    
    if (!contactPattern.test(contact)) {
        alert('Please enter a valid contact number');
        return false;
    }
    
    if (password && password.length < 8) {
        alert('New password must be at least 8 characters long');
        return false;
    }
    
    return true;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>