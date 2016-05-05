<html>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<body>

	    <h1>twitter</h1>

	   	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
	    	<input type="text" name="tweet";>
	    	<input type="submit" name="submit" value="tweet">
	    </form>


		<?php
			// pass in some info;
			require("common.php");

			if(empty($_SESSION['user'])) {

				// If they are not, we redirect them to the login page.
				$location = "http://" . $_SERVER['HTTP_HOST'] . "/login.php";
				echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
				//exit;

				// Remember that this die statement is absolutely critical.  Without it,
				// people can view your members-only content without logging in.
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

			// create query
			$query = "SELECT * FROM tweets";

			// execute query
			$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

			// see if any rows were returned
			if (mysql_num_rows($result) > 0) {

	    		// print them one after another
			    $query5 = "SELECT * FROM tweets ORDER BY id DESC";
	            $result = mysql_query($query5) or die ("Error in query: $query. ".mysql_error());

	    		while($row = mysql_fetch_row($result)) {

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

	    		// print status message
	    		echo "No rows found!";
			}

			// free result set memory
			mysql_free_result($result);

			// set variable values to HTML form inputs
			$tweet = mysql_escape_string($_POST['tweet']);

			// check to see if user has entered anything
			if ($tweet != "") {

		 		// build SQL query
				$query = "INSERT INTO tweets (text, user) VALUES ('$tweet', '$arr[1]')";

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
