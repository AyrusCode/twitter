<!--
    Enlighten: feed.php
    This page allows the user to create and delete tweets, as well as
		view other users' tweets and search for hashtags and users
	Created by Zack Nathan, Denis Khavin, Surya Pandiaraju, Michael McGovern, and Mark Hoel
    Created and last edited in May 2016
-->

<html>
	<head>
		<title>Enlighten!</title>

		<script src="js/jquery.js"></script>
		<script src="js/jquery.emotions.js"> </script>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		<link href="enlighten.css" rel="stylesheet">
		<link rel="icon" type="image/png" href="http://static.php.net/www.php.net/favicon.ico" />

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

		<meta charset="UTF-8">
        <meta name="description" content="Enlighten Home Page">
        <meta name="author" content="Zack, Denis, Surya, and Michael">
	</head>

	<body background="resources/sky.png">
		<div class="container" style="width:500px">
			<img width=250px height=150px src="resources/phoenix.png">
			<div>
				<h1><font color="white">Enlighten!</font></h1>
			</div>
			<br>

			<div class="form-group has-danger">

				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style='display:inline;' method="post">

					<div class="input-group input-group-lg">
						<span class="input-group-addon" id="sizing-addon1"><span class="glyphicon glyphicon-fire" aria-hidden="true"></span></span>
						<input placeholder="What's on your mind?" type="text" name="tweet" class="form-control" aria-describedby="sizing-addon1">
						<span class="input-group-btn"><button class="btn btn-danger" type="submit">Go</button></span>
					</div>
					<br/>

				</form>

				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">

					<div class="input-group input-group-lg">
						<span class="input-group-addon" id="sizing-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
						<input placeholder="Search for a hashtag" type="text" name="search" class="form-control" aria-describedby="sizing-addon1">
						<span class="input-group-btn"><button class="btn btn-danger" type="submit">Go</button></span>
					</div>

					<br/>

					<div class="input-group input-group-lg">
						<span class="input-group-addon" id="sizing-addon1"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></span>
						<input placeholder="Search for a user" type="text" name="usersearch" class="form-control" aria-describedby="sizing-addon1">
						<span class="input-group-btn"><button class="btn btn-danger" type="submit">Go</button></span>
					</div>

					<input type="submit" style="visibility: hidden;">
				</form>
			</div>

			<?php

				// pass in some info;
				require("common.php");

				// check if the user is logged in
				if (empty($_SESSION['user'])) {
					// If they are not logged in, we redirect them to the login page.
					$location = "http://" . $_SERVER['HTTP_HOST'] . "/twitter/login.php";
					echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
					// exit
					die("Redirecting to login.php");
				}

				// To access $_SESSION['user'] values put in an array, show user his username
				$arr = array_values($_SESSION['user']);

				// Welcome message and log out button
				echo "<div style='color: #FFFFFF; font-size:20pt' ><strong><center><form style='display:inline;' action='logout.php' method='post'>Welcome, @".$arr[1]." <button class='btn btn-danger' style='float: right;' >Log out</button></form></div>";

				// open connection
				$connection = mysql_connect($host, $rootusername, $rootpassword) or die ("Unable to connect!");

				// select database
				mysql_select_db($dbname) or die ("Unable to select database!");

				// check the form for a hashtag search
				$search = mysql_escape_string($_POST['search']);

				// check the form for a user search
				$usersearch = mysql_escape_string($_POST['usersearch']);

				// if the user enterd a search into the form, go to the correct url
				// the search system is based on url parameters that are created
				// in this section and accessed below
				if (!empty($_POST['search'])) {
					if (!empty($_POST['usersearch'])) {
						// if the user is searching for a hashtag and a user
						$location = "http://".$_SERVER['HTTP_HOST']."/twitter/feed.php?search=".ltrim($_POST['search'], "#")."&usersearch=".ltrim($_POST['usersearch'], "@");
					} else {
						// if the user is searching for a hashtag
						$location = "http://".$_SERVER['HTTP_HOST']."/twitter/feed.php?search=".ltrim($_POST['search'], "#");
					}
					echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
				} else {
					if (!empty($_POST['usersearch'])) {
						// if the user is searching for a user
						$location = "http://".$_SERVER['HTTP_HOST']."/twitter/feed.php?usersearch=".ltrim($_POST['usersearch'], "@");
						echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
					}
				}

				// get the search terms from the url parameters
				$search = $_GET["search"];
				$usersearch = $_GET["usersearch"];

				// format the search terms
				if ($search[0] != "#") {
					$search = "#$search";
				}
				if ($usersearch[0] == "@") {
					$usersearch = ltrim($usersearch, "@");
				}

				// depending on what the user is searching for,
				// create a query to find the correct results
				// all of the queries sort the tweets in order from newest to oldest
				if (!empty($_GET['search'])) {

					// hashtag search and user search
					if (!empty($_GET['usersearch'])) {
						// print the search message and create the clear button
						echo "<form method='post' action=".$_SERVER['PHP_SELF'].">
							  <span style='color: #FFFFFF;'>Searching for ".$search." and @".$usersearch." </span><input class='btn btn-danger btn-sm' name='clear' type='submit' value='clear'/>
							  </form>";
						// SQL query filters for the tweets with the hashtag search in their text
						// and the user search in their author's username
						$query = "SELECT * FROM tweets WHERE contents LIKE '%$search%' AND user LIKE '%$usersearch%' ORDER BY id DESC";

					// hashtag search only
					} else {
						// print the search message and create the clear button
						echo "<form method='post' action=".$_SERVER['PHP_SELF'].">
							  <span style='color: #FFFFFF;'>Searching for ".$search." </span><input class='btn btn-danger btn-sm' name='clear' type='submit' value='clear'/>
							  </form>";
						// SQL query filters for the tweets with the hashtag search in their text
						$query = "SELECT * FROM tweets WHERE contents LIKE '%$search%' ORDER BY id DESC";
					}

				} else {

					// user search only
					if (!empty($_GET['usersearch'])) {
						// print the search message and create the clear button
						echo "<form method='post' action=".$_SERVER['PHP_SELF'].">
							  <span style='color: #FFFFFF;'>Searching for @".$usersearch." </span><input class='btn btn-danger btn-sm' name='clear' type='submit' value='clear'/>
							  </form>";
						// SQL query filters for the tweets with the user search in their author's username
					    $query = "SELECT * FROM tweets WHERE user LIKE '%$usersearch%' ORDER BY id DESC";

					// no search
					} else {
						// tell the user that they are not searching for anything
						echo "<span style='color: #FFFFFF;'>Displaying all Messages!</span>";
						// SQL query selects all tweets
						$query = "SELECT * FROM tweets ORDER BY id DESC";
					}
				}

				echo "<br><br>";

				// execute query
				$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

				// see if any tweets were returned matching the searches
				if (mysql_num_rows($result) > 0) {

		    		// print the returned tweets one after another
		    		while ($row = mysql_fetch_row($result)) {

						// this algorithm goes through the text of the tweet
						// and turns any hashtags into links to the searche
						// page of that hashtag

						// the text of the tweet
						$tweet = str_split($row[1]);

						// counter for keeping track of the position in the text
						$i = 0;

						// empty list will be populated by hashtags
						$tags = [];

						// iterate through the characters
						foreach ($tweet as $letter) {

							// if the character is a hashtag...
							if ($letter == "#") {
								// create a string that will hold the hashtag
								$hashtag = "";
								// go through the characters after the hashtag
								foreach (array_slice($tweet, $i) as $hashtagletter) {
									// and add them to the hashtag string
									if ($hashtagletter != " ") {
										$hashtag = $hashtag.$hashtagletter;
									// until a space is found, meaning the hashtag is over
									} else {
										break;
									}
								}

								// add the hashtag to the list of hashtags
								array_push($tags, $hashtag);
							}

							// increment the counter variable
							$i ++;
						}

						// go through each of the hashtags
						foreach ($tags as $tag) {
							// and replace the text of the hashtag with a link to the corresponding search page
							$row[1] = str_replace($tag, "<a href='http://localhost:8888/twitter/feed.php?search=".trim($tag, "#")."'>".$tag."</a>", $row[1]);
							// remember $row[1] holds the text of the tweet
						}

						// each tweet is in a nice bootstrap panel
						echo "<div class='panel panel-danger'>";

							// checks if the author of the tweet is the user
							// who is currently signed in
							if ($row[2] == $arr[1]) {
								// if it is their own tweet, give them the option to delete it
								// the tweet is deleted by passing the tweet id through as a url parameter
								// this is extremely insecure and easy to abuse
								echo "<div class='panel-heading'>
									      <form action='http://".$_SERVER['HTTP_HOST']."/twitter/feed.php?delete=".$row[0]."' style='display: inline;' method='post'>
										      <b>"."@".$row[2]."
											  <button class='btn btn-danger btn-sm' data-toggle='tooltip' data-placement='top' title='Delete' style='float: right;'>
											      <span class='glyphicon glyphicon-remove' aria-hidden='true'></span>
											  </button>
										  </form>
									 </div>";
							} else {
								// if it is someone elses tweet, don't let them delete it
								echo "<div class='panel-heading'><b>@".$row[2]."</div>";
							}
							echo "<div class='panel-body'>";
								// text of the tweet
				        		echo "<div>".$row[1]."</div>";
								// format the timestamp and echo it as a readable date and time
								echo "<div style='padding-top:4px;'><span style='font-weight:normal; font-size:12px;'>".date('F j, g:i a', strtotime($row[3]))."</span></div>";
							echo "</div>";
						echo "</div>";
		    		}
				} else {
					// no tweets found
		    		echo "<span style='color: #FFFFFF;'>No tweets found!</span>";
				}

				// free result set memory
				mysql_free_result($result);


				// create a variable to hold the delete parameter
				$delete = $_GET["delete"];

				// check if a tweet is being deleted
				if (!empty($_GET['delete'])) {

					// create query to delete the tweet
		    		$query = "DELETE FROM tweets WHERE id = ".$delete;

					// run the query
		     		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

					// reload the page to remove the url parameter
					$location = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
					echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
				}

				// make a variable from the create tweet form
				$tweet = mysql_escape_string($_POST['tweet']);

				// check to see if user has entered anything
				if ($tweet != "") {

			 		// build SQL query to create a new tweet
					$query = "INSERT INTO tweets (contents, user) VALUES ('$tweet', '$arr[1]')";

					// run the query
		     		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

					// refresh the page to show new update
			 		echo "<meta http-equiv='refresh' content='0'>";
				}

				// close connection
				mysql_close($connection);

			?>

		</div>

		<script>
			// enables the fancy tooltip on the delete button
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});
		</script>
	</body>

</html>
