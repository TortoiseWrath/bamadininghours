<!DOCTYPE html>
<html lang="en-US">
<title>Bama Dining Hours - <?php
	date_default_timezone_set("America/Chicago");
	$date = date('F j, Y');
	echo $date;
 ?></title>
<meta charset="UTF-8">
<h1 style="text-align: center;"><?=$date?></h1>
<?php
	require_once('parsers.php');
	$date = date('Y-m-d');
	if(isset($_GET['date'])) {
		$date = date('Y-m-d', strtotime($_GET['date']));
	}
	if(!file_exists("cache/$date.html") || filesize("cache/$date.html") < 500) {
		$dining_html = @file_get_contents("http://bamadining.ua.edu/calendar/hours-of-operation$date/");
		if($dining_html === false) {
			parseCampusdish("https://ua.campusdish.com/LocationsAndMenus", $date);
		}
		else {
			parseCalendar($dining_html);
		}
	}
	echo file_get_contents("cache/$date.html");
