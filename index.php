<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Search</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/fontawesome.css">
    <link rel="stylesheet" href="assets/fontawesome/css/solid.css">
    <link href="https://cdn.jsdelivr.net/npm/fastbootstrap@2.0.0/dist/css/fastbootstrap.min.css" rel="stylesheet" integrity="sha256-EkS1lBVeD1Dv7HGBICgtEPKeIz4ffoKbo5gRiPvD6/8=" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>
</head>
<body class="bg-light-subtle">
    <div class="container border border-light-subtle border-2 rounded-4 bg-white p-10 shadow-lg">
    <h1 class="mb-10">Member Search</h1>
    <form method="get" action="" class="row gy-2 gx-3 align-items-center">
        <div class="row my-5">
            <div class="col-auto">
                <label for="name" class="form-label">Name:</label>
                <input type="text" name="name" id="name" class="form-control"  />
            </div>
            <div class="col-auto">
                <label for="email" class="form-label">Email:</label>
                <input type="text" name="email" id="email" class="form-control" />
            </div>
        </div>
        <div class="row mb-5">
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
        </div>        
        <div class="row submit-btn">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-search"></i> Search</button>
            </div>
        </div>
    </form>

    <?php
    // Enable exception handling for mysqli
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Check if the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            // Connect to the MySQL database
            $servername = "localhost"; // Change this to your MySQL server address
            $username = "root"; // Change this to your MySQL username
            $password = ""; // Set the password to an empty string
            $database = "rhs"; // Change this to your MySQL database name
            $conn = new mysqli($servername, $username, $password, $database);

            // Process the form data
            $name = $_GET["name"] ?? "";
            $email = $_GET["email"] ?? "";
            $city = $_GET["city"] ?? "";
            $state = $_GET["state"] ?? "";
            $country = $_GET["country"] ?? "";

            // Pagination variables
            $itemsPerPage = 10; // Define how many items per page
            $page = $_GET["page"] ?? 1; // Get the current page number from the URL
            $offset = ($page - 1) * $itemsPerPage; // Calculate the offset

            // Calculate total number of pages
            $totalSql = "SELECT COUNT(DISTINCT u.mtt_id) AS total
                    FROM users u
                    WHERE u.full_name LIKE '%$name%'
                    AND u.email LIKE '%$email%'
                    AND u.city LIKE '%$city%'
                    AND u.state LIKE '%$state%'
                    AND u.country LIKE '%$country%'";

            $totalResult = $conn->query($totalSql);
            $totalRow = $totalResult->fetch_assoc();
            $totalItems = $totalRow["total"];
            $totalPages = ceil($totalItems / $itemsPerPage);

            // Calculate the starting and ending item numbers
            $startItemNumber = ($page - 1) * $itemsPerPage + 1;
            $endItemNumber = min($page * $itemsPerPage, $totalItems);

            // Log form data to the PHP error log
            error_log(
                "Name: $name, Email: $email, City: $city, State: $state, Country: $country"
            );

            // Create and execute a SQL query to search for results that match the fields and join with the legacy_ids table
            $sql = "SELECT u.full_name, u.email, u.display_name, u.dob, u.address_first, u.city, u.state, u.zipcode, u.country, u.phone, l.legacy_id, u.membership_type, MAX(m.membership_end_at) AS latest_membership_end_at, GROUP_CONCAT(DISTINCT CONCAT(cm.role, ':', c.chapter_name) SEPARATOR ',') 
                AS chapter_data 
                FROM users u 
                INNER JOIN legacy_ids l ON u.mtt_id = l.mtt_id 
                LEFT JOIN chapter_member cm ON u.mtt_id = cm.author_id 
                LEFT JOIN chapters c ON cm.chapter_id = c.mtt_chapter_id 
                LEFT JOIN membership m ON u.mtt_id = m.mtt_id 
                WHERE u.full_name LIKE '%$name%' AND u.email LIKE '%$email%' AND u.city LIKE '%$city%' AND u.state LIKE '%$state%' AND u.country LIKE '%$country%' 
                GROUP BY u.mtt_id LIMIT $itemsPerPage OFFSET $offset";

            // Execute the query
            $result = $conn->query($sql);

            // Check if the query failed
            if (!$result) {
                die("Query failed: " . $conn->error . " with SQL: " . $sql);
            }

            // Display search results and count
            if ($result->num_rows > 0) {
                echo "<h2><strong>Search Results:</strong></h2>";
                echo '<h3><strong class="text-danger">' .
                    $totalItems .
                    "</strong> result" .
                    ($totalItems === 1 ? "" : "s") .
                    " found";
                if (!empty($name)) {
                    echo ' for <strong>Name:</strong> "' .
                        htmlspecialchars($name) .
                        '"';
                }
                if (!empty($email)) {
                    echo ' for <strong>Email:</strong> "' .
                        htmlspecialchars($email) .
                        '"';
                }
                if (!empty($city)) {
                    echo ' for <strong>City:</strong> "' .
                        htmlspecialchars($city) .
                        '"';
                }
                if (!empty($state)) {
                    echo ' for <strong>State:</strong> "' .
                        htmlspecialchars($state) .
                        '"';
                }
                if (!empty($country)) {
                    echo ' for <strong>Country:</strong> "' .
                        htmlspecialchars($country) .
                        '"';
                }
                echo "</h3>";
                echo "<h3>Showing items " .
                    $startItemNumber .
                    "-" .
                    $endItemNumber .
                    "</h3>";

                // Start of user info
                while ($row = $result->fetch_assoc()) {
                    $userId = htmlspecialchars($row["mtt_id"]); // Ensure this has a unique value for each row
                    $crownIcon =
                        $row["membership_type"] === "Queen"
                            ? '<i class="fa-solid fa-crown"></i> '
                            : "";

                    // User's name as a link to trigger the modal
                    echo '<div class="mb-10">';
                    echo '<span class="fs-5">' . $crownIcon;
                    echo '<a href="#" data-bs-toggle="modal" data-bs-target="#userModal' .
                        htmlspecialchars($row["legacy_id"]) .
                        '">' .
                        htmlspecialchars($row["full_name"]) .
                        "</a>";
                    echo "</span><br />";
                    echo '<span class="fs-sm text-secondary">' .
                        htmlspecialchars($row["legacy_id"]);
                    echo ' | <span class="fw-medium"><strong class="text-primary ls-wider">Exp:</strong> ' .
                        date(
                            "F d, Y",
                            strtotime($row["latest_membership_end_at"])
                        ) .
                        "</span>";
                    echo "</span><br /><br />";
                    echo "</div>";

                    // The Modal
                    echo '<div class="modal fade" id="userModal' .
                        htmlspecialchars($row["legacy_id"]) .
                        '" tabindex="-1" aria-labelledby="userModalLabel' .
                        htmlspecialchars($row["legacy_id"]) .
                        '" aria-hidden="true">';
                    echo '<div class="modal-dialog">';
                    echo '<div class="modal-content">';
                    echo '<div class="modal-header">';
                    echo '<h5 class="modal-title" id="userModalLabel' .
                        htmlspecialchars($row["legacy_id"]) .
                        '">' .
                        htmlspecialchars($row["full_name"]) .
                        "</h5>";
                    echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                    echo "</div>";
                    echo '<div class="modal-body">';

                    // Include only if not null or empty
                    if (!empty($row["email"])) {
                        echo "<p><strong>Email: </strong>" .
                            htmlspecialchars($row["email"]) .
                            "</p>";
                    }
                    if (!empty($row["display_name"])) {
                        echo "<p><strong>Display Name: </strong>" .
                            htmlspecialchars($row["display_name"]) .
                            "</p>";
                    }
                    if (!empty($row["dob"])) {
                        echo "<p><strong>Date of Birth: </strong>" .
                            date("F d, Y", strtotime($row["dob"])) .
                            "</p>";
                    }
                    if (
                        !empty($row["address_first"]) ||
                        !empty($row["city"]) ||
                        !empty($row["state"]) ||
                        !empty($row["zipcode"]) ||
                        !empty($row["country"])
                    ) {
                        echo "<p><strong>Address:</strong><br />";
                        if (!empty($row["address_first"])) {
                            echo htmlspecialchars($row["address_first"]) .
                                "<br />";
                        }
                        if (
                            !empty($row["city"]) ||
                            !empty($row["state"]) ||
                            !empty($row["zipcode"])
                        ) {
                            $cityStateZip = array_filter([
                                $row["city"],
                                $row["state"],
                                $row["zipcode"],
                            ]);
                            echo htmlspecialchars(
                                implode(", ", $cityStateZip)
                            ) . "<br />";
                        }
                        if (!empty($row["country"])) {
                            echo htmlspecialchars($row["country"]);
                        }
                        echo "</p>";
                    }
                    if (!empty($row["phone"])) {
                        echo "<p><strong>Phone: </strong>" .
                            htmlspecialchars($row["phone"]) .
                            "</p>";
                    }
                    if (!empty($row["chapter_data"])) {
                        echo "<p><strong>Chapters:</strong><br />";
                        $chapterData = explode(",", $row["chapter_data"]);
                        foreach ($chapterData as $chapter) {
                            list($role, $chapterName) = explode(":", $chapter);
                            if ($role === "admin") {
                                echo '<i class="fa-solid fa-crown animate-pulse"></i> ';
                            }
                            echo htmlspecialchars($chapterName) . "<br />";
                        }
                        echo "</p>";
                    }

                    echo "</div>"; // End of modal-body
                    echo "</div>"; // End of modal-content
                    echo "</div>"; // End of modal-dialog
                    echo "</div>"; // End of modal
                }
                // End of user info
            } else {
                echo "<h2>No results found.</h2>";
            }

            // Close the database connection
            $conn->close();
        }
    } catch (mysqli_sql_exception $exception) {
        die("Connection failed: " . $exception->getMessage());
    }

    // Collect search parameters
    $searchParams = [
        "name" => $name,
        "email" => $email,
        "city" => $city,
        "state" => $state,
        "country" => $country,
        // include any other parameters here
    ];

    // Start the pagination
    echo '<nav aria-label="Page navigation example">';
    echo '<ul class="pagination justify-content-center">';

    // Previous button
    $disabledClass = $page <= 1 ? " disabled" : "";
    $searchParams["page"] = max(1, $page - 1); // Update page number for the Previous button
    $queryString = http_build_query($searchParams);
    echo "<li class='page-item{$disabledClass}'><a class='page-link' href='?$queryString'>Previous</a></li>";

    // Page number buttons
    $startPage = max($page - 1, 1);
    $endPage = min($page + 1, $totalPages);

    // Show first page and ellipses if needed
    if ($startPage > 1) {
        echo "<li class='page-item'><a class='page-link' href='?" .
            http_build_query(["page" => 1] + $searchParams) .
            "'>1</a></li>";
        if ($startPage > 2) {
            echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = $page == $i ? " active" : "";
        $searchParams["page"] = $i; // Update page number for each page button
        $queryString = http_build_query($searchParams);
        echo "<li class='page-item{$activeClass}'><a class='page-link' href='?$queryString'>{$i}</a></li>";
    }

    // Show ellipses and last page if needed
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        echo "<li class='page-item'><a class='page-link' href='?" .
            http_build_query(["page" => $totalPages] + $searchParams) .
            "'>$totalPages</a></li>";
    }

    // Next button
    $disabledClass = $page >= $totalPages ? " disabled" : "";
    $searchParams["page"] = min($totalPages, $page + 1); // Update page number for the Next button
    $queryString = http_build_query($searchParams);
    echo "<li class='page-item{$disabledClass}'><a class='page-link' href='?$queryString'>Next</a></li>";

    // End the pagination
    echo "</ul>";
    echo "</nav>";
    ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/fastbootstrap@2.0.0/dist/js/fastbootstrap.min.js" integrity="sha256-o0tNXN7ia0O9G0qNbrzBkEEiQTv+GeW5EO4LjnfDkZk=" crossorigin="anonymous"></script>
</body>
</html>
