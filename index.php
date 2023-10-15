<!DOCTYPE html>
<html>
<head>
    <title>Search Form</title>
</head>
<body>
    <h1>Search Form</h1>
    <form method="post" action="">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" />
        <label for="email">Email:</label>
        <input type="text" name="email" id="email" />
        <input type="submit" value="Search" />
    </form>


    <?php
    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Connect to the MySQL database
        $servername = "localhost"; // Change this to your MySQL server address
        $username = "root"; // Change this to your MySQL username
        $password = ""; // Set the password to an empty string
        $database = "rhs"; // Change this to your MySQL database name

        $conn = new mysqli($servername, $username, $password, $database);

        // Check the database connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Process the form data
        $name = $_POST["name"];
        $email = $_POST["email"];

        // Log form data to the PHP error log
        error_log("Name: $name, Email: $email");

        // Create and execute a SQL query to search for results that match both fields
        $sql = "SELECT full_name, email, display_name, dob, address_first, city, state, zipcode, country, phone FROM users WHERE full_name LIKE '%$name%' AND email LIKE '%$email%'";

        $result = $conn->query($sql);

        // Display search results and count
        if ($result->num_rows > 0) {
            echo "<h2>Search Results:</h2>";
            echo "<h3>" . $result->num_rows . " results found</h3>"; // Display the count of results
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['full_name']} - {$row['email']} - {$row['display_name']} - {$row['dob']} - {$row['address_first']} - {$row['city']} - {$row['state']} - {$row['zipcode']} - {$row['country']} - {$row['phone']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<h2>No results found.</h2>";
        }

        // Close the database connection
        $conn->close();
    }
    ?>
</body>
</html>
