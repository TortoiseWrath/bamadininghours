<!DOCTYPE html>
<html lang="en-US">
<title>Bama Dining Hours - <?php
	set_time_limit(300);
	date_default_timezone_set("America/Chicago");
	$date_long = date('F j, Y');
	$date = date('Y-m-d');
	if(isset($_GET['date'])) {
		$date = date('Y-m-d', strtotime($_GET['date']));
		$date_long = date('F j, Y', strtotime($_GET['date']));
	}
	echo $date_long;
?></title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=320, initial-scale=1">
<meta charset="UTF-8">
<h1 style="text-align: center;"><?=$date_long?></h1>
<?php
	if(!file_exists("cache/$date.html") || filesize("cache/$date.html") < 500 || isset($_GET["debug"])) {
		require_once('parsers.php');
		$dining_html = @file_get_contents("http://bamadining.ua.edu/calendar/hours-of-operation$date/");
		if($dining_html === false) {
			parseCampusdish("https://ua.campusdish.com");
		}
		else {
			parseCalendar($dining_html, $date);
		}
	}
	echo file_get_contents("cache/$date.html");
