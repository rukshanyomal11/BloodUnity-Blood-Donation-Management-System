<?php 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodunity";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if a donor ID is provided in the URL
if (isset($_GET['id'])) {
    $donor_id = intval($_GET['id']);
    
    // Delete the donor from the database
    $stmt = $conn->prepare("DELETE FROM Donor WHERE id = ?");
    $stmt->bind_param("i", $donor_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting donor: " . $stmt->error;
    }

    header("Location: view_donors.php");
    exit;
} else {
    $_SESSION['error'] = "No donor ID provided!";
    header("Location: view_donors.php");
    exit;
}

$conn->close();
?>
