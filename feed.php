<html>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link href="twitter.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<body>

		<div class="container">
		    <h1>twitter</h1>

			<div class="form-group has-success">
			   	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
					<div class="input-group input-group-lg">
						<span class="input-group-addon" id="sizing-addon1"><span class="glyphicon glyphicon-send" aria-hidden="true"></span></span>
						<input placeholder="What's on your mind?" type="text" name="tweet" class="form-control" aria-describedby="sizing-addon1">
					</div>
			    </form>

				<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
					<div class="input-group input-group-lg">
						<span class="input-group-addon" id="sizing-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
						<input placeholder="Search for a hashtag" type="text" name="search" class="form-control" aria-describedby="sizing-addon1">
					</div>
			    </form>
			</div>

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
				echo "<form action='logout.php' method='post'>Welcome, @".$arr[1]." <button class='btn btn-success' style='float: right;' >Log out</button></form>";

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

						echo "<div class='panel panel-success'>";
							if ($row[2] == $arr[1]) {
								echo "<div class='panel-heading'>
									      <form action='http://".$_SERVER['HTTP_HOST']."/twitter/feed.php?id=".$row[0]."' style='display: inline;' method='post'>
										      @".$row[2]."
											  <button class='btn btn-success btn-sm' data-toggle='tooltip' data-placement='top' title='Remove' style='float: right;'>
											      <span class='glyphicon glyphicon-remove' aria-hidden='true'></span>
											  </button>
										  </form>
									 </div>";
							} else {
								echo "<div class='panel-heading'>@".$row[2]."</div>";
							}
							echo "<div class='panel-body'>";
				        		echo "<div>".$row[1]."</div>";
								echo "<div>".date('F j, g:i a', strtotime($row[3]))."</div>";
							echo "</div>";
						echo "</div>";
		    		}
				} else {
		    		echo "No rows found!";
				}

				// free result set memory
				mysql_free_result($result);
				// set variable values to HTML form inputs
			
				
			$db_str = mysql_escape_string($_POST['tweet']);
			
			echo "<hr />";
			$chars = array(":)", "<3", ":(", "fuck", "shit", "bitch", "nigger", "cock", "cheesecake", "isaac", "Isaac", "ass", "McGovern");
			$icons = array("☻", "♡", "☹", "bleep", "bleep", "bleep", "bleep", "cheesecake", "dick", "Lodja", "Lodja", "donkey", "McGovich");
			$tweet = str_replace($chars,$icons,$db_str);
			echo $tweet;

				// if DELETE pressed, set an id, if id is set then delete it from DB
				if (isset($_GET['id'])) {

					// create query to delete record
					echo $_SERVER['PHP_SELF'];
		    		$query = "DELETE FROM tweets WHERE id = ".$_GET['id'];

					// run the query
		     		$result = mysql_query($query) or die ("Error in query: $query. ".mysql_error());

					// reset the url to remove id $_GET variable
					$location = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
					echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
					exit;

				}

				// check to see if user has entered anything
				if ($tweet != "") {
			 		// build SQL query
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
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});
		</script>

	</body>
</html>
