<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_donor'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $age = intval($_POST['age']);
    $blood_type = trim($_POST['blood_type']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    
    // Check if email already exists
    $check_email = $conn->prepare("SELECT email FROM Donor WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    
    if ($result->num_rows > 0) {
        echo "<div class='alert alert-danger' role='alert'>
            This email address is already registered. Please use a different email.
        </div>";
    } else {
        $sql = "INSERT INTO Donor (name, address, contact, age, blood_type, email, username, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssisiss", $name, $address, $contact, $age, $blood_type, $email, $username, $password);
            
            try {
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success' role='alert'>
                        Donor registration successful! You can now <a href='view_donors.php'>return to donor list</a>
                    </div>";
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    echo "<div class='alert alert-danger' role='alert'>
                        This username or email is already taken. Please choose different credentials.
                    </div>";
                } else {
                    echo "<div class='alert alert-danger' role='alert'>
                        Error registering donor: " . $e->getMessage() . "
                    </div>";
                }
            }
            $stmt->close();
        }
    }
    $check_email->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Donor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-4">
    <h2>Register New Donor</h2>

    <form method="POST" onsubmit="return validateForm()">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required maxlength="100">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" required rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="contact" class="form-label">Contact Number</label>
            <input type="tel" class="form-control" id="contact" name="contact" required pattern="[0-9+\-\s()]{8,20}">
        </div>
        <div class="mb-3">
            <label for="age" class="form-label">Age</label>
            <input type="number" class="form-control" id="age" name="age" required min="18" max="65">
            <div class="form-text">Donors must be between 18 and 65 years old</div>
        </div>
        <div class="mb-3">
            <label for="blood_type" class="form-label">Blood Type</label>
            <select class="form-select" id="blood_type" name="blood_type" required>
                <option value="">Select Blood Type</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required maxlength="100">
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required maxlength="50">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="form-text">Password must be at least 8 characters long</div>
        </div>
        <button type="submit" name="add_donor" class="btn btn-primary">Register Donor</button>
        <a href="view_donors.php" class="btn btn-secondary">Back to Donor List</a>
    </form>
</div>

<script>
function validateForm() {
    const email = document.getElementById('email').value;
    const contact = document.getElementById('contact').value;
    const password = document.getElementById('password').value;
    const age = parseInt(document.getElementById('age').value);
    
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
    
    if (password.length < 8) {
        alert('Password must be at least 8 characters long');
        return false;
    }
    
    if (age < 18 || age > 65) {
        alert('Donor age must be between 18 and 65 years');
        return false;
    }
    
    return true;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>