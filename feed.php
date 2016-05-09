<html>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<body>

	    <h1>twitter</h1>

	   	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
	    	<input type="text" name="tweet";>
	    	<input type="submit" name="submit" value="tweet">
	    </form>

		<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
	    	#<input type="text" name="search";>
	    	<input type="submit" name="submit" value="search">
	    </form>


		<?php
			// pass in some info;
			require("common.php");

			if(empty($_SESSION['user'])) {
				// If they are not loggd in, we redirect them to the login page.
				$location = "http://" . $_SERVER['HTTP_HOST'] . "/login.php";
				echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
				//exit;
				die("Redirecting to login.php");
			}

			// To access $_SESSION['user'] values put in an array, show user his username
			$arr = array_values($_SESSION['user']);
			echo "Welcome " . $arr[1];
			echo "<br><br>";

			// open connection
			$connection = mysql_connect($host, $rootusername, $rootpassword) or die ("Unable to connect!");
			// select database
			mysql_select_db($dbname) or die ("Unable to select database!");

			// check for a search
			$search = mysql_escape_string($_POST['search']);

			// if the user used the form, go to the correct url
			if (!empty($_POST['search'])) {
				$location = "http://".$_SERVER['HTTP_HOST']."/twitter/feed.php?search=".$_POST['search'];
				echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
			}

			// get the search term from the url
			$search = $_GET["search"];

			// if there is a search term, search for it
			if (empty($_GET['search']) or isset($_POST['clear'])) {
				echo "Displaying all tweets <br> <br>";
				$query = "SELECT * FROM tweets ORDER BY id DESC";
			} else {
				// add a hashtag to the search
				if ($search[0] != "#") {
				    $search = "#$search";
				}
				echo "<form method='post' action=".$_SERVER['PHP_SELF'].">
					  Searching for ".$search." <input name='clear' type='submit' value='clear'/>
					  </form>";
				$query = "SELECT * FROM tweets WHERE contents LIKE '%$search%' ORDER BY id DESC";
			}

			// execute query
			$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

			// see if any rows were returned
			if (mysql_num_rows($result) > 0) {

	    		// print them one after another
	    		while ($row = mysql_fetch_row($result)) {
					$tweet = str_split($row[1]);
					$i = 0;
					$tags = [];
					foreach ($tweet as $letter) {
						if ($letter == "#") {
							$hashtag = "";
							foreach (array_slice($tweet, $i) as $hashtagletter) {
								if ($hashtagletter != " ") {
									$hashtag = $hashtag.$hashtagletter;
								} else {
									break;
								}
							}
							array_push($tags, $hashtag);
						}
						$i ++;
					}

					foreach ($tags as $tag) {
						$row[1] = str_replace($tag, "<a href='http://localhost:8888/twitter/feed.php?search=".trim($tag, "#")."'>".$tag."</a>", $row[1]);
					}

					echo "<div class='panel panel-default'>";
						echo "<div class='panel-heading'>".$row[2]."</div>";
						echo "<div class='panel-body'>";
			        		echo "<div>".$row[1]."</div>";
							echo "<div>".date('F j, g:i a', strtotime($row[3]))."</div>";
							// DISABLED DELETE FEATURE echo "<div><a href=".$_SERVER['PHP_SELF']."?id=".$row[0].">Delete</a></div>";
						echo "</div>";
					echo "</div>";
	    		}
			} else {
	    		echo "No rows found!";
			}

			// free result set memory
			mysql_free_result($result);
			// set variable values to HTML form inputs
			$tweet = mysql_escape_string($_POST['tweet']);

			// check to see if user has entered anything
			if ($tweet != "") {
		 		// build SQL query
				$query = "INSERT INTO tweets (contents, user) VALUES ('$tweet', '$arr[1]')";
				// run the query
	     		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());
				// refresh the page to show new update
		 		echo "<meta http-equiv='refresh' content='0'>";
			}

			// DISABLED FEATURE TO REMOVE TWEETS
			// // if DELETE pressed, set an id, if id is set then delete it from DB
			// if (isset($_GET['id'])) {
			//
			// 	// create query to delete record
			// 	echo $_SERVER['PHP_SELF'];
	    	// 	$query = "DELETE FROM symbols WHERE id = ".$_GET['id'];
			//
			// 	// run the query
		    // 		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());
			//
			// 	// reset the url to remove id $_GET variable
			// 	$location = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			// 	echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
			// 	exit;
			//
			// }

			// close connection
			mysql_close($connection);

		?>

		<br>
	    <form action="logout.php" method="post"><button>Log out</button></form>

	</body>
</html>
