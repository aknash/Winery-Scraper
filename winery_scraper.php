<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>cUrl call to Google Maps Directions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<pre>
<?php # winery_scraper.php

	include('simple_html_dom.php');

	//die if curl is not installed
    if (!function_exists('curl_init')) {
                    return array('success'=>FALSE, 'error'=>"Error: Curl is not installed on this server, aborting", 'output' => $output);
    }
 	
	// Identify the winery scrape site
	// $scrape_url = 'http://www.napavintners.com/wineries/all_wineries.asp';
	$scrape_url = 'http://www.napavintners.com/wineries/wineries_by_name.asp?wineryname=A';
	
	// Fire up curl 
	$c = curl_init();

	curl_setopt($c, CURLOPT_FAILONERROR, true); 
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_TIMEOUT, 5);

	// print("\n Direction Rqst Url: $rqst_url".PHP_EOL);
	curl_setopt($c, CURLOPT_URL, $scrape_url);

	// Execute the transaction:
	$html_str = curl_exec($c);
	$r_error = curl_error($c);

	// Close the directions api connection:
	curl_close($c);

	//die if error returned from CURL
    if (!empty($r_error)) {
            $output .= "Scrape site returned error: " . $r_error . "<br/>";
            print($output);
    }

    // create a new dom object
    $html = new simple_html_dom();
	 
	// load the html into the object
	$html->load($html_str);

	// parse out the comment blocks
	// Find all comment (<!--...-->) blocks 
	// information we want is contained between 
	// <!-- begin winery list -->
	//			...
	// <!-- end winery list -->

	// print("parse out the comment fields");
	// $wl = $html->find('comment');

	// Find all <div> which attribute class="winerybox"

	// Find all article blocks
	foreach($html->find('div[class="winerybox"]') as $wb) {
		print('find link'.PHP_EOL);
		// Find all links 
		foreach($wb->find('a') as $lnk) {
			echo $lnk->href . '<br>';
			echo $lnk->plaintext . '<br>';
		}

		print("lookup a specific link");
		$lnk = $wb->find('a',1);
		echo $lnk->href . '<br>';
		echo $lnk->plaintext . '<br>';

	    //print_r($lnk);

	}

	// print_r($articles);

	// $wl = $html->find('div[class="winerybox"]');

	// print("display the results");

	// print_r($wl);


?>

</pre>
</body>
</html>