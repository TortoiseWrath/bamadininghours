<?php
	function daysContain($days, $day) { // if a range such as "Sat - Mon, Thu" contains the integral day $day (0=Sun)
		$dayIndices = array('sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6);
		$dayRanges = explode(',', $days);
		foreach($dayRanges as &$dayRange) {
			$dayRange = explode('-',trim($dayRange));
			foreach($dayRange as &$dayBound) {
				$dayBound = $dayIndices[strtolower(trim($dayBound))];
			}
			if(count($dayRange) == 1) { // only one day in range
				if($dayRange[0] == $day) {
					return true;
				}
				else {
					continue; // ignore this range
				}
			}
			$i = $dayRange[0];
			while(true) {
				if($i == $day) {
					return true;
				}
				if($i == $dayRange[1]) {
					break;
				}
				$i = ($i + 1) % 7;
			}
		}
		return false;
	}
	// for($i = 0; $i < 7; $i++) { // debug code
	// 	var_dump(daysContain("Sat - Mon, Thu", $i));
	// }

	function hoursList($url, $day, $date) {
		$doc = new DOMDocument();
		@$doc->loadHTML(file_get_contents($url));
		$xp = new DOMXpath($doc);
		$hourGroups = $xp->query("//div[@class='location__hours']/ul[1]/li");
		// var_dump($url, $hourGroups);

		$output = '<ul>';
		foreach($hourGroups as $group) {
			$groupName = $xp->query("div[@class='mealPeriod']", $group)->item(0)->textContent;
			$specialHours = $xp->query("../../../../../..", $group);
			$specialHours = $specialHours && $specialHours->length && preg_match("/^hoursModal[0-9]+$/", $specialHours->item(0)->getAttribute("id")) ? 
				$xp->query("//span[@class='modal-title']", $specialHours->item(0))->item(0)->textContent
				: false;
			if($specialHours && !preg_match("`[^1]$date`", $specialHours)) {
				continue; // not today's special hours
			}
			// var_dump($specialRoot);
			$groupHours = $xp->query("ul/li", $group);
			$hours = "CLOSED";
			foreach($groupHours as $gh) {
				$days = $xp->query("span[@class='location__day']", $gh)->item(0)->textContent;
				if(daysContain($days, $day)) {
					$hours = $xp->query("span[@class='location__times']", $gh)->item(0)->textContent;
					break;
				}
			}
			if(strtolower($hours) !== 'closed') {
				$output .= "<li>";
				if($specialHours) {
					$output .= "<h4>" . trim(preg_replace("`\($date\)`", "", $specialHours)) . "</h4>";
				}
				if($group->previousSibling !== NULL || $group->nextSibling !== NULL) {
					$output .= "<h4>$groupName</h4>";
				}
				$output .= $hours;
			}
		}
		if($output === '<ul>') {
			$output .= "CLOSED";
		}
		$output .= '</ul>';


		return $output;
	}

	$day = date('w', strtotime($date));
	$dateFormatted = date('n/j', strtotime($date));

	$doc = new DOMDocument();
	$locationPrefix = 'LocationsAndMenus';
	@$doc->loadHTML(file_get_contents("$url/$locationPrefix"));
	$xp = new DOMXpath($doc);
	$locations = $xp->query("//*/div[@id='locationList']/div/ul/li");
	$links = [];
	$names = [];
	foreach($locations as $location) {
		$element = $xp->query("div/a", $location)->item(0);
		$name = $element->getAttribute('aria-label');
		$link = preg_replace("/^\/$locationPrefix\//", '', $element->getAttribute('href'));
		$path = explode('/', $link);
		if(count($path) > 1) {
			if(!isset($links[$path[0]]) || !is_array($links[$path[0]])) {
				$links[$path[0]] = array();
			}
			$links[$path[0]][$path[1]] = $link;
			$names[$path[1]] = $name;
		}
		else {
			$links[$path[0]] = $link;
			$names[$path[0]] = $name;
		}
	}
	$output = '<ul>';
	// $links = array("LakesideDiningHall" => $links["LakesideDiningHall"]); // for debug
	foreach($links as $key=>$link) {
		$output .= "<li id=\"$key\"><h2>".$names[$key]."</h2>";
		if(is_array($link)) {
			$output .= "<ul>";
			foreach($link as $key=>$link) {
				$output .= "<li id=\"$key\"><h3>".$names[$key]."</h3>";
				$output .= hoursList("$url/$locationPrefix/$link", $day, $dateFormatted);
			}
			$output .= '</ul>';
		}
		else {
			$output .= hoursList("$url/$locationPrefix/$link", $day, $dateFormatted);
		}
	}
	$output .= '</ul>';

	file_put_contents("cache/$date.html", $output);