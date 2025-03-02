// Predefined usernames and passwords
const users = [
    { username: "T1", password: "123", redirectPage: "index5.html" },
    { username: "T2", password: "123", redirectPage: "index7.html" }
];

// Function to validate login credentials
function validateLogin() {
    // Get the entered username and password
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    // Find a user matching the entered credentials
    const validUser = users.find(
        (user) => user.username === username && user.password === password
    );

    if (validUser) {
        alert("Login successful! Welcome to BloodUnity.");
        // Redirect to the corresponding page
        window.location.href = validUser.redirectPage;
    } else {
        alert("Invalid username or password. Please try again.");
    }
}


