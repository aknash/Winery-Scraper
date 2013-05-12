<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Scrape Winery Information</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<pre>
<?php # winery_scraper_local.php

	include('simple_html_dom.php');


	function scrape_winery($url, $wdb) {
	
		// Load HTML from a HTML file 
		$html = file_get_html($url);
		// $html = file_get_html("V. Sattui Winery.html");

		// Winery Features
		$e = $html->find('div[id=features]');
		// Find all <li> in <ul> 
		$ul = $e[0]->find('ul', 0);
		echo PHP_EOL . "Winery features:" .PHP_EOL;
	    foreach($ul->find('li') as $li) 
	    {
	        $csize = 0;
	    	$feature_selector = strip_tags($li->plaintext);
	    	// echo "Feature: " . $feature_selector . PHP_EOL; 

	    	switch ($feature_selector) {
	    		case 'Open for wine tastings':
	    			break;
	    			case 'Open to the public':
	    			break;
	    			case 'Offers regular daily tours':
	    			break;
	    			case 'Certified Napa Green Land':
	    			break;
	    			case 'Tasting at tasting bar':
	    			# code...
	    			break;
	    			case 'Seated tasting available':
	    			# code...
	    			break;
	    			case 'Tasting available in private area':
	    			# code...
	    			break;
	    			case 'Couches':
	    			# code...
	    			break;
	    			case 'Tasting fee':
	    			# code...
	    			break;
	    			case 'Winery has gardens':
	    			# code...
	    			break;
	    			case 'Winery practices sustainable agriculture/production':
	    			# code...
	    			break;
	    			case 'Winery has unique architecture':
	    			# code...
	    			break;
	    			case 'Winery has art on display':
	    			# code...
	    			break;
	    			case 'Winery can host corporate functions up to 300 people':
	    			# code...
	    			break;
	    			case 'Indoor tasting area':
	    			# code...
	    			break;
	    			case 'Outdoor tasting area':
	    			# code...
	    			break;
	    			case 'Barrel tasting available':
	    			# code...
	    			break;
	    			case 'Club member lounge':
	    			# code...
	    			break;
	    			case 'Romantic':
	    			# code...
	    			break;
	    			case 'Open by appointment - call/email':
	    			# code...
	    			break;
	    			case 'Tasting fee waived with wine purchase':
	    			# code...
	    			break;
	    			case 'Winemaker or owner usually available':
	    			# code...
	    			break;
	    			case 'Fireplace':
	    			# code...
	    			break;
	    			case 'Family run':
	    			# code...
	    			break;
	    			case 'Offers tours by appointment - call/email':
	    			# code...
	    			break;
	    			case 'Winery property is historical landmark':
	    			# code...
	    			break;
	    			case 'Complimentary Tasting':
	    			# code...
	    			break;
	    			case 'Winery has gardens':
	    			# code...
	    			break;
	    			case 'Winery has picnic area':
	    			# code...
	    			break;
	    			case 'Certified Napa Green Winery':
	    			# code...
	    			break;
	    			case 'Certified Napa Green Land':
	    			# code...
	    			break;
	    			case "Dog friendly - It's ok to bring your dog!":
	    			# code...
	    			break;
	    			case 'Winery includes wine caves':
	    			# code...
	    			break;
	    			case 'Winery is family friendly':
	    			# code...
	    			break;
	    			/*
	    			case 'value':
	    			# code...
	    			break;
	    			case 'value':
	    			# code...
	    			break;
	    			case 'value':
	    			# code...
	    			break;
	    			*/

	    		default:
	    			echo "Unknown faciity: " . $feature_selector .PHP_EOL;
	    			break;
	    	}

	    }
	    echo PHP_EOL;

	} // end scrape_winery()

	/*
	** Main line code execution
	** process the outer list of wineries
	** and pass the url for eachto the scrape winery rtn
	*/

	$scrape_url = 'http://www.napavintners.com/wineries/wineries_by_name.asp?wineryname=B';

	$ahtml = file_get_html($scrape_url);

	// Find all winery boxes
	foreach($ahtml->find('div[class="winerybox"]') as $wb) {
		// Find links to winery description pages
			$lnk = $wb->find('a',1);
			echo $lnk->plaintext . '<br>';
			//echo "lnk: " . $lnk->href . '<br>';
			$wurl = "http://www.napavintners.com" . $lnk->href;
			echo $wurl . PHP_EOL;
			scrape_winery($wurl, $wdb);
	}
?>

</pre>
</body>
</html>