<!--
    Enlighten: logout.php
    Script to log the user out of Enlighten
    Created by Zack Nathan, Denis Khavin, Surya Pandiaraju, Michael McGovern, and Mark Hoel
    Created and last edited in May 2016
-->

<?php

    // First we execute our common code to connection to the database and start the session
    require("common.php");

    // We remove the user's data from the session
    unset($_SESSION['user']);

    // We redirect them to the login page
    $location = "http://".$_SERVER['HTTP_HOST']."/twitter/login.php";
    echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';

?>
