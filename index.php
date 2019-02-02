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
	$date = date('Y-m-d');
	if(!file_exists("cache/$date.html")) {
		$dining_html = file_get_contents("http://bamadining.ua.edu/calendar/hours-of-operation$date/");
		$doc = new DOMDocument();
		@$doc->loadHTML($dining_html);
		$paragraphs = $doc->getElementsByTagName('p');
		$output = '';
		foreach($paragraphs as $p)
			if(strpos($p->getAttribute('class'), 'copyright') === false && strpos($p->getAttribute('class'), 'back') === false)
				$output .= $p->ownerDocument->saveHTML($p);
		file_put_contents("cache/$date.html", $output);
	}
	echo file_get_contents("cache/$date.html");
