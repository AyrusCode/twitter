<html>
	<body>


    <h1>Twitter</h1>    
    
    <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <!-- This is the HTML form -->

   	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
    	Tweet: <input type="text" name="country";>
    	<input type="submit" name="submit">
    </form>
    
        
	<?php
	require("../../../../Users/Surya.Pandiaraju/Downloads/twitapp/common.php"); ///This should be your directory, the one here is mine
		$arr = array_values($_SESSION['user']);
		echo "Welcome " . $arr[2];
		// set database server access variables:
		$host = "localhost";
		$user = "root";
		$pass = "root";
		$db = "testdb";

		// open connection
		$connection = mysql_connect($host, $user, $pass) or die ("Unable to connect!");

		// select database
		mysql_select_db($db) or die ("Unable to select database!");

		// create query
		$query = "SELECT * FROM symbols";

		// execute query
		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

		// see if any rows were returned
		if (mysql_num_rows($result) > 0) {

    		// print them one after another
    		echo "<table cellpadding=10 class='table' border=1>";
			
		   $query5 = "SELECT * FROM symbols ORDER BY id DESC";
            $result = mysql_query($query5) or die ("Error in query: $query. ".mysql_error());
    		while($row = mysql_fetch_row($result)) {
				
        		echo "<tr>";
				echo "<td>".$row[0]."</td>";
        		echo "<td>" . $row[1]."</td>";
        		echo "<td>".$row[2]."</td>";
				echo "<td><a href=".$_SERVER['PHP_SELF']."?id=".$row[0].">Delete</a></td>";
        		echo "</tr>";
    		}
		    echo "</table>";

		} else {
			
    		// print status message
    		echo "No rows found!";
		}

		// free result set memory
		mysql_free_result($result);

		// set variable values to HTML form inputs
		$country = mysql_escape_string($_POST['country']);
		//$username = ;
		
  
		
		// check to see if user has entered anything
		if ($country != "") {
			
			
	 		// build SQL query
			$query = "INSERT INTO symbols (tweet, Username) VALUES ('$country', '$arr[2]')";
		
			// run the query
     		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());
	
			// refresh the page to show new update
	 		echo "<meta http-equiv='refresh' content='0'>";
		}
		
		// if DELETE pressed, set an id, if id is set then delete it from DB
		if (isset($_GET['id'])) {

			// create query to delete record
			echo $_SERVER['PHP_SELF'];
    		$query = "DELETE FROM symbols WHERE id = ".$_GET['id'];

			// run the query
     		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());
			
			// reset the url to remove id $_GET variable
			$location = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
			exit;
			
		}
		
		// close connection
		mysql_close($connection);

	?>
    <a type="submit" class="button"> <button type="button" class="btn btn-success">Success</button> <?php
	$db->query("INSERT INTO table VALUES(8,'two','three')");
	
	?></a>
     <form action="logout.php" method="post"><button>Log out</button></form>
    
	</body>
</html>
