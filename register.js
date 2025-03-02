// Function to handle form submission
function submitRegistration() {
    // Collect form data (for demonstration; typically, you'd send this to a server)
    const name = document.getElementById("name").value;
    const address = document.getElementById("address").value;
    const contact = document.getElementById("contact").value;
    const age = document.getElementById("age").value;
    const bloodType = document.getElementById("blood-type").value;
    const email = document.getElementById("email").value;
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    // Simulate form submission (replace with actual server-side call if needed)
    console.log("Registration Data:", {
        name,
        address,
        contact,
        age,
        bloodType,
        email,
        username,
        password
    });

    // Show success message
    alert("Registration successful!");

    // Redirect to login page
    window.location.href = "index6login.html";
}
