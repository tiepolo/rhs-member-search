<!DOCTYPE html>
<html>
<head>
    <title>Search Form</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
</head>
<body>
    <h1>Search Form</h1>
    <form method="post" action="" class="row gy-2 gx-3 align-items-center">
        <div class="col-auto">
            <label for="name" class="form-label">Name:</label>
            <input type="text" name="name" id="name" class="form-control"  />
        </div>
        <div class="col-auto">
            <label for="email" class="form-label">Email:</label>
            <input type="text" name="email" id="email" class="form-control" />
        </div>
        <div class="col-auto">
            <label for="city" class="form-label">City:</label>
            <input type="text" name="city" id="city" class="form-control" />
        </div>
        <div class="col-auto">
            <label for="state" class="form-label">State:</label>
            <input type="text" name="state" id="state" class="form-control" />
        </div>
        <div class="col-auto">
            <label for="country" class="form-label">Country:</label>
            <input type="text" name="country" id="country" class="form-control" />
        </div>        
        <div class="row submit-btn">
            <div class="col-auto">
                <input type="submit" value="Search" class="btn btn-primary" />
            </div>
        </div>
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
        $city = $_POST["city"];
        $state = $_POST["state"];
        $country = $_POST["country"];

        // Log form data to the PHP error log
        error_log("Name: $name, Email: $email, City: $city, State: $state, Country: $country");

        // Create and execute a SQL query to search for results that match both fields
        $sql = "SELECT full_name, email, display_name, dob, address_first, city, state, zipcode, country, phone FROM users 
        WHERE full_name LIKE '%$name%' 
        AND email LIKE '%$email%'
        AND city LIKE '%$city%'
        AND state LIKE '%$state%'
        AND country LIKE '%$country'";

        $result = $conn->query($sql);

        // Display search results and count
        if ($result->num_rows > 0) {
            echo '<h2><strong>Search Results:</strong></h2>';
            echo '<h3><strong class="text-danger">' . $result->num_rows . "</strong> results found for <strong>Name:</strong> \"$name\", <strong>Email:</strong> \"$email\", <strong>City:</strong> \"$city\", <strong>State:</strong> \"$state\", <strong>Country:</strong> \"$country\" </h3>"; // Display the count of results
            while ($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                echo '<div class="card-header custom-purple">' . $row['full_name'] . '</div>';
                echo '<div class="card-body">';
                echo '<p class="card-text"><strong>Email: </strong>' . $row['email'] . '</p>';
                echo '<p class="card-text"><strong>Display Name: </strong>' . $row['display_name'] . '</p>';
                echo '<p class="card-text"><strong>Date of Birth: </strong>' . $row['dob'] . '</p>';
                echo '<p class="card-text"><strong>Address: </strong>' . $row['address_first'] . '</p>';
                echo '<p class="card-text"><strong>City: </strong>' . $row['city'] . '</p>';
                echo '<p class="card-text"><strong>State: </strong>' . $row['state'] . '</p>';
                echo '<p class="card-text"><strong>Zipcode: </strong>' . $row['zipcode'] . '</p>';
                echo '<p class="card-text"><strong>Country: </strong>' . $row['country'] . '</p>';
                echo '<p class="card-text"><strong>Phone: </strong>' . $row['phone'] . '</p>';
                echo '</div>'; // Close card-body
                echo '</div>'; // Close card
            }
        } else {
            echo "<h2>No results found.</h2>";
        }

        // Close the database connection
        $conn->close();
    }
    ?>
</body>
</html>
