<?php
	$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
	$day = date('w', strtotime($date));
	$output = $days[$day];
	file_put_contents("cache/$date.html", $output);