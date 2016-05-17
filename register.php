<!--
    Enlighten: register.php
    Use this page to register a new account with Enlighten
    Created by Zack Nathan, Denis Khavin, Surya Pandiaraju, Michael McGovern, and Mark Hoel
    Created and last edited in May 2016
-->

<?php

    // First we execute our common code to connection to the database and start the session
    require("common.php");

    // This if statement checks to determine whether the registration form has been submitted
    // If it has, then the registration code is run, otherwise the form is displayed
    if(!empty($_POST)) {

        // This is an empty string which will be used to track errors with the user's input
        $errors = "";

        // Check for a valid username
        if (empty($_POST['username']) or strlen($_POST['username']) <= 3 or !ctype_alnum($_POST['username'])) {
            // If the username is invalid, add it to the error string
            $errors = "$errors-badusername";
        }

        // Check for a valid password
        if (empty($_POST['password']) or strlen($_POST['password']) <= 3) {
            // If the password is invalid, add it to the error string
            $errors = "$errors-badpassword";
        }

        // Check for a valid email address using PHP's email filter
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            // If the email is invalid, add it to the error string
            $errors = "$errors-bademail";
        }

        // We will use this SQL query to see whether the username entered by the
        // user is already in use.  A SELECT query is used to retrieve data from the database.
        // :username is a special token, we will substitute a real value in its place when
        // we execute the query.
        $query = "
            SELECT
                1
            FROM users
            WHERE
                username = :username
        ";

        // This contains the definitions for any special tokens that we place in
        // our SQL query.  In this case, we are defining a value for the token
        // :username.  It is possible to insert $_POST['username'] directly into
        // your $query string; however doing so is very insecure and opens your
        // code up to SQL injection exploits.  Using tokens prevents this.
        // For more information on SQL injections, see Wikipedia:
        // http://en.wikipedia.org/wiki/SQL_Injection
        $query_params = array(
            ':username' => $_POST['username']
        );

        try {
            // These two statements run the query against your database table.
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch (PDOException $ex) {
            // Note: On a production website, you should not output $ex->getMessage().
            // It may provide an attacker with helpful information about your code.
            die("Failed to run query: " . $ex->getMessage());
        }

        // The fetch() method returns an array representing the "next" row from
        // the selected results, or false if there are no more rows to fetch.
        $row = $stmt->fetch();

        // If a row was returned, then we know a matching username was found in
        // the database already and we should not allow the user to continue.
        if ($row) {
            // If the username is already in use, add that to the error string
            $errors = "$errors-usernameinuse";
        }

        // Now we perform the same type of check for the email address, in order
        // to ensure that it is unique.
        $query = "
            SELECT
                1
            FROM users
            WHERE
                email = :email
        ";

        // Method is the same as above, now with email instead of username
        $query_params = array(
            ':email' => $_POST['email']
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        $row = $stmt->fetch();

        if ($row) {
            // If the email is in use, add that to the error string
            $errors = "$errors-emailinuse";
        }

        // Format the error string
        $errors = ltrim($errors, "-");

        // If the error string is longer than one, that means there are errors
        if (strlen($errors) > 1) {
            // If there are errors, reload the page with the error string in the URL
            // The error string allows the page to display which errors exist in the input
            $location = "http://".$_SERVER['HTTP_HOST']."/twitter/register.php?errors=".$errors;
            echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
        } else {
            //If there are no errors in the input, create the user

            // An INSERT query is used to add new rows to a database table.
            // Again, we are using special tokens (technically called parameters) to
            // protect against SQL injection attacks.
            $query = "
                INSERT INTO users (
                    username,
                    password,
                    salt,
                    email
                ) VALUES (
                    :username,
                    :password,
                    :salt,
                    :email
                )
            ";

            // A salt is randomly generated here to protect again brute force attacks
            // and rainbow table attacks.  The following statement generates a hex
            // representation of an 8 byte salt.  Representing this in hex provides
            // no additional security, but makes it easier for humans to read.
            // For more information:
            // http://en.wikipedia.org/wiki/Salt_%28cryptography%29
            // http://en.wikipedia.org/wiki/Brute-force_attack
            // http://en.wikipedia.org/wiki/Rainbow_table
            $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));

            // This hashes the password with the salt so that it can be stored securely
            // in your database.  The output of this next statement is a 64 byte hex
            // string representing the 32 byte sha256 hash of the password.  The original
            // password cannot be recovered from the hash.  For more information:
            // http://en.wikipedia.org/wiki/Cryptographic_hash_function
            $password = hash('sha256', $_POST['password'] . $salt);

            // Next we hash the hash value 65536 more times.  The purpose of this is to
            // protect against brute force attacks.  Now an attacker must compute the hash 65537
            // times for each guess they make against a password, whereas if the password
            // were hashed only once the attacker would have been able to make 65537 different
            // guesses in the same amount of time instead of only one.
            for ($round = 0; $round < 65536; $round++) {
                $password = hash('sha256', $password . $salt);
            }

            // Here we prepare our tokens for insertion into the SQL query.  We do not
            // store the original password; only the hashed version of it.  We do store
            // the salt (in its plaintext form; this is not a security risk).
            $query_params = array(
                ':username' => $_POST['username'],
                ':password' => $password,
                ':salt' => $salt,
                ':email' => $_POST['email']
            );

            try {
                // Execute the query to create the user
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);

                // After the user is created, send them to the login page
                $location = "http://".$_SERVER['HTTP_HOST']."/twitter/login.php";
                echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';

            } catch(PDOException $ex) {
                // Note: On a production website, you should not output $ex->getMessage().
                // It may provide an attacker with helpful information about your code.
                die("Failed to run query: " . $ex->getMessage());
            }
        }
    }

?>
<html>
    <head>
        <title>Register</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <link href="dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="enlighten.css">

        <meta charset="UTF-8">
        <meta name="description" content="Register with Enlighten">
        <meta name="author" content="Zack, Denis, Surya, and Michael">
    </head>

    <body background="resources/sunset.jpg">
        <div class="container">
            <img width=250px height=150px src="resources/phoenix.png">

            <br>
            <br>
            <br>

            <div class="redbox">

                <font color ="white"><h2>Register</h2></font>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">

                    <br />
                    <input type="text" name="username" class="form-control" placeholder="Username" aria-describedby="sizing-addon1" required/>
                    <?php
                    // Display the message if the url contains the usernameinuse error
                    if (strpos($_GET["errors"], "usernameinuse") !== false) {
                        echo "<span style='color: white;'>Username is already taken</span><br>";
                    }
                    // Display the message if the url contains the baspassword error
                    if (strpos($_GET["errors"], "badusername") !== false) {
                        echo "<span style='color: white;'>Username must be alphanumeric and at least 4 characters long</span><br>";
                    }
                    ?>
                    <br />

                    <input type="text" name="email" class="form-control" placeholder="Email" aria-describedby="sizing-addon1" required/>
                    <?php
                    // Display the message if the url contains the emailinuse error
                    if (strpos($_GET["errors"], "emailinuse") !== false) {
                        echo "<span style='color: white;'>Email address is already registered</span><br>";
                    }
                    // Display the message if the url contains the bademail error
                    if (strpos($_GET["errors"], "bademail") !== false) {
                        echo "<span style='color: white;'>Please enter a valid email address</span><br>";
                    }
                    ?>
                    <br />

                    <input type="password" name="password" class="form-control" placeholder="Password" aria-describedby="sizing-addon1" required/>
                    <?php
                    // Display the message if the url contains the badpassword error
                    if (strpos($_GET["errors"], "badpassword") !== false) {
                        echo "<span style='color: white;'>Password must be at least 4 characters long</span><br>";
                    }
                    ?>
                    <br />

                   <center> <button class="button" style="vertical-align:middle"><span>Join the Bonfire</span> </button></center>

                </form>

                <a href="login.php"><strong><span style="color:white">I already have an account, log in!</span><br /></a>
                <a href="about.php"><strong><span style="color:white">About Enlighten and FAQ</span></a>
            </div>
        </div>
    </body>
</html>
