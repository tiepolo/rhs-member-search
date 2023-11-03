<!DOCTYPE html>
<html>
<head>
    <title>Member Search</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/fontawesome.css">
    <link rel="stylesheet" href="assets/fontawesome/css/solid.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>
</head>
<body>
    <h1>Member Search</h1>
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
            <label for "state" class="form-label">State:</label>
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

        // Create and execute a SQL query to search for results that match the fields and join with the legacy_ids table
        $sql = "SELECT u.full_name, u.email, u.display_name, u.dob, u.address_first, u.city, u.state, u.zipcode, u.country, u.phone, l.legacy_id, MAX(m.membership_end_at) AS latest_membership_end_at, GROUP_CONCAT(DISTINCT CONCAT(cm.role, ':', c.chapter_name) SEPARATOR ',') AS chapter_data
                FROM users u
                INNER JOIN legacy_ids l ON u.mtt_id = l.mtt_id
                LEFT JOIN chapter_member cm ON u.mtt_id = cm.author_id
                LEFT JOIN chapters c ON cm.chapter_id = c.mtt_chapter_id
                LEFT JOIN membership m ON u.mtt_id = m.mtt_id
                WHERE u.full_name LIKE '%$name'
                AND u.email LIKE '%$email'
                AND u.city LIKE '%$city'
                AND u.state LIKE '%$state'
                AND u.country LIKE '%$country'
                GROUP BY u.mtt_id";

        // Execute the query
        $result = $conn->query($sql);

        // Check if the query failed
        if (!$result) {
            die("Query failed: " . $conn->error . " with SQL: " . $sql);
        }

            $result = $conn->query($sql);

            // Display search results and count
            if ($result->num_rows > 0) {
                echo '<h2><strong>Search Results:</strong></h2>';
                echo '<h3><strong class="text-danger">' . $result->num_rows . '</strong> result' . ($result->num_rows === 1 ? '' : 's') . ' found';
                if (!empty($name)) {
                    echo ' for <strong>Name:</strong> "' . $name . '"';
                }
                if (!empty($email)) {
                    echo ' for <strong>Email:</strong> "' . $email . '"';
                }
                if (!empty($city)) {
                    echo ' for <strong>City:</strong> "' . $city . '"';
                }
                if (!empty($state)) {
                    echo ' for <strong>State:</strong> "' . $state . '"';
                }
                if (!empty($country)) {
                    echo ' for <strong>Country:</strong> "' . $country . '"';
                }
                echo '</h3>';
                    while ($row = $result->fetch_assoc()) {
                    echo '<div class="card">';
                    echo '<div class="card-header custom-purple">' . '<span class="fs-5"><strong>' . $row['full_name'] . '</strong></span><br />' . $row['legacy_id'] . ' - Membership End Date: ' . $row['latest_membership_end_at'] . '</div>';
                    echo '<div class="card-body">';
                    echo '<p class="card-text"><strong>Email: </strong>' . $row['email'] . '</p>';
                    echo '<p class="card-text"><strong>Display Name: </strong>' . $row['display_name'] . '</p>';
                    echo '<p class="card-text"><strong>Date of Birth: </strong>' . $row['dob'] . '</p>';
                    echo '<p class="card-text"><strong>Address: </strong>' . $row['address_first'] . ', ' . $row['city'] . ', ' . $row['state'] . ', ' . $row['zipcode'] . ', ' . $row['country'] . '</p>';
                    echo '<p class="card-text"><strong>Phone: </strong>' . $row['phone'] . '</p>';
                    if (!empty($row['chapter_data'])) {
                        $chapterData = explode(',', $row['chapter_data']);
                        $adminChapters = [];
                        $memberChapters = [];
                    
                        // Separate admin and member chapters
                        foreach ($chapterData as $data) {
                            list($role, $chapterName) = explode(':', $data);
                            if ($role === 'admin') {
                                $adminChapters[] = $chapterName;
                            } else {
                                $memberChapters[] = $chapterName;
                            }
                        }
                    
                        echo '<p class="card-text"><strong>Chapters:</strong><br />';
                    
                        // Display admin chapters first
                        if (!empty($adminChapters)) {
                            foreach ($adminChapters as $chapter) {
                                echo '<i class="fa-solid fa-crown"></i> ' . $chapter . '<br />';
                            }
                        }
                    
                        // Display member chapters next
                        if (!empty($memberChapters)) {
                            foreach ($memberChapters as $chapter) {
                                echo $chapter . '<br />';
                            }
                        }
                    
                        echo '</p>';
                    }                    
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
