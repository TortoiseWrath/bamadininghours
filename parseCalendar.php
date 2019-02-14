<?php
	$doc = new DOMDocument();
	@$doc->loadHTML($html);
	$paragraphs = $doc->getElementsByTagName('p');
	$output = '';
	foreach($paragraphs as $p) {
		if(strpos($p->getAttribute('class'), 'copyright') === false && strpos($p->getAttribute('class'), 'back') === false)
		$output .= $p->ownerDocument->saveHTML($p);
	}
	file_put_contents("cache/$date.html", $output);