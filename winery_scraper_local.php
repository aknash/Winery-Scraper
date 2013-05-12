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

	// set the default time zone to PST
	date_default_timezone_set('America/Los_Angeles');

	function scrape_winery($url, $wdb) {
	
		// Load HTML from a HTML file 
		$html = file_get_html($url);
		// $html = file_get_html("V. Sattui Winery.html");
	 
	 	// Locate the winery url in the winerylabel <div>
		foreach ($html->find('.winerylabel') as $wl) {
			print('find next winery label'.PHP_EOL);
			$lnk = $wl->find('a',1);
			echo "winery url: " . $lnk->href . '<br>' .PHP_EOL;
			$w_url = $lnk->href;
		}
		
		// Locate the winery description 
		$e = $html->find('td[align=left]');

		// Note: add support for the "winery booking urls" in winery specified paragraph if they exist

		// First paragraph is 'a free form' description provided by the winery
		// Capture for potential later use
		$wp1 = $e[0]->children(1)->plaintext;

		// Second paragraph is "structured content" conistent across all wineries
		// Extract winery name, address, ph # etc
		
		$para = $e[0]->children(2);
		$wp2 = strip_tags($e[0]->children(2)->plaintext);
		$wp2 = str_replace('&nbsp;',' ',$wp2);

		// winery name
		$w_name = trim(strip_tags(strtok($wp2, "\n")));
		echo "Extracted winery name: " . $w_name .PHP_EOL;
		$w_st = trim(strip_tags(strtok("\n")));
		echo "Extracted winery St: " . $w_st .PHP_EOL;
		$w_city = trim(strip_tags(strtok("\n")));
		echo "Extracted winery City: " . $w_city .PHP_EOL;
		
		if (($i = strcmp(strip_tags(trim($w_st)), "Address Not Available")) == 0) {
			// echo "Address Not Available" . PHP_EOL;
			$w_state = "";
			$w_zip = "";
			$w_null = strtok("\n");
		} else {
			$w_state = trim(strip_tags(strtok(" ")));
			echo "Extracted winery State: " . $w_state .PHP_EOL;
			$w_zip = trim(strip_tags(strtok("\n")));
			echo "Extracted zip code: " . $w_zip . PHP_EOL;
		}

		// produce a concatented full address
		$w_addr = $w_st . " " . $w_city . " " . $w_state . " " . $w_zip;
		echo "Full address string" . $w_addr . PHP_EOL;

		$w_ph = trim(strip_tags(strtok("\n")));
		echo "Extracted winery Phone: " . $w_ph .PHP_EOL;

		// find where the winery hours start
		if (($cpos = strpos($wp2, "Hours:") ) === false) {
			echo "No match for hours found" .PHP_EOL;
			$w_hrs="";
			$w_ot="";
			$w_ct="";
		} else {
			// Skip to the hours segment
			$wp2 = substr($wp2, $cpos);
			strtok($wp2, "\n");
			$w_hrs = trim(strip_tags(strtok("\n")));
			echo "Hours: " . $w_hrs .PHP_EOL;
			if ($w_hrs == "Not open to the public.") {
				$w_ot="";
				$w_ct="";
			} else {
				// Find the first integer in the string
				if (preg_match('/\d/', $w_hrs, $m, PREG_OFFSET_CAPTURE)) {
					$spos = $m[0][1];
				} else {
					$spos = 0;
				}
				$dpos = strpos($w_hrs, "-");
				$cpos = strlen($w_hrs);
				echo "spos: " .$spos . "dpos: " . $dpos . "cpos" . $cpos .PHP_EOL;
				$w_ot = trim(strip_tags(substr($w_hrs, $spos, $dpos-$spos-1)));
				$w_ct = trim(strip_tags(substr($w_hrs, $dpos+2, ($cpos-$dpos-2))));
				echo "Open: " . $w_ot . "Close: " . $w_ct . PHP_EOL;
			}
		}

		// find where the tasting room contact info starts
		if (($cpos = strpos($wp2, "Tastings") ) === false) {
			echo "No match for tasting room found" .PHP_EOL;
			$w_tasting_ph = "";
		} else {
			// Skip to the tasting room ph # segment
			$wp2 = substr($wp2, $cpos);
			strtok($wp2, "\n");
			$w_tasting_ph = trim(strip_tags(strtok("\n")));
			echo "Tasting room ph#: " . $w_tasting_ph .PHP_EOL;
		}

		// set the creation and last update dates for new records
		$c_date = date("Y-m-d");

		// write the winery information to the database
		
		$query = "INSERT INTO winery (name, full_addr, street, city, state, zip, country, w_ph, t_ph, hours, open, close, web_site, c_date, u_date) " .
				"VALUES ('".$w_name."','".$w_addr."','".$w_st."','".$w_city."','".$w_state."','".$w_zip."','".
						 "US','".$w_ph."','".$w_tasting_ph."','".$w_hrs."','".$w_ot."','".$w_ct."','".$w_url."','".$c_date."','".$c_date."')";
		echo "query string: " . $query . PHP_EOL;
		$r = mysqli_query($wdb, $query) or die("Error on Winery INSERT query");

		// get the winery identifier (w_id) created for the newly inserted record
		if (($w_id = mysqli_insert_id ( $wdb )) == 0) {
			echo "Last inserted winery record did not return a valid winery id";
			die("invalid w_id");
		}

		// Winery Features
		$e = $html->find('div[id=features]');
		// Find all <li> in <ul> 
		$ul = $e[0]->find('ul', 0);
		echo PHP_EOL . "Winery features:" .PHP_EOL;
	    foreach($ul->find('li') as $li) 
	    {
	        $csize = 0;
	        // echo $li->plaintext .PHP_EOL;
	        if (($cpos = strpos($li->plaintext, "corporate functions")) === false ) {
		    	$feature_selector = strip_tags($li->plaintext);
		    } else {
		    	$clen = strlen("corporate functions up to");
		    	$feature_selector = strip_tags(substr($li->plaintext, 0, ($cpos+$clen)));
		    	$num = substr($li->plaintext, ($cpos+$clen));
		    	$csize = intval(strtok($num, " "));
		    }

		    // set up the intial SQL query string based on the winery feature
		    // other fields may be appended

		    $wf_query = "INSERT INTO WineryFacilities(w_id, fac_id, u_date";

		    $fs = addslashes($feature_selector);
	    	$f_query = "SELECT fac_id FROM Facilities WHERE type = '$fs'";
	    	// echo "feature query : " . $f_query . PHP_EOL;
	    	if ($res = mysqli_query($wdb, $f_query)) {
		    	$row = mysqli_fetch_row($res);
		    	$fac_id = $row[0];
		    	// echo "facility identifier: " . $fac_id .PHP_EOL;
		    } else {
		    	die("error querying facility table");
		    }

		    // echo "Winery ID: " . $w_id . PHP_EOL;

		    if ($csize == 0) {
		    	$wf_query .= ") VALUES ('$w_id' , '$fac_id' , '$c_date')";
		    } else {
		    	$wf_query .= ", size ) VALUES ('$w_id' , '$fac_id' , '$c_date' , '$csize')";
		    }
		    // echo "Winery Facility INSERT query: " . $wf_query . PHP_EOL;

		    $r = mysqli_query($wdb, $wf_query) or die("Error on WineryFacilities INSERT query");

	    }
	    echo PHP_EOL;

		// Varietal types

		$e = $html->find('div[id=varietals]');
		if (isset($e)) {
			// Find all <li> in <ul> 
			$ul = $e[0]->find('ul', 0);
			if (isset($ul)) {
				echo "Varietals available:" .PHP_EOL;
			    foreach($ul->find('li') as $li) 
			    {
			        $varietal_selector = strip_tags($li->plaintext);
				    echo "varietal selector: " . $varietal_selector . PHP_EOL;

				    $var_query = "SELECT var_id FROM Varietals WHERE type = '$varietal_selector'";
				    echo "Varietal Query: " . $var_query . PHP_EOL;
				    if ($res = mysqli_query($wdb, $var_query)) {
				    	$row = mysqli_fetch_row($res);
				    	$var_id = $row[0];
				    	// echo "facility identifier: " . $fac_id .PHP_EOL;
				    } else {
				    	die("error querying varietal table");
				    }

				    $wv_query = "INSERT INTO WineryVarieties(w_id, var_id, u_date)"." VALUES ('$w_id' , '$var_id' , '$c_date')";
				    echo "WV Query: " . $wv_query . PHP_EOL;
				    $r = mysqli_query($wdb, $wv_query) or die("Error on WineryVarieties INSERT query");
				    
			    } // end-foreach
			    echo PHP_EOL;
			}
		}
		
		// Wine club details
		$e = $html->find('div[id=wineclub]');
		$w_club = addslashes($e[0]->plaintext);
		// echo "Wine Club: " . $w_club . PHP_EOL;
		$wc_query = "INSERT INTO Wineclub(description) VALUES ('$w_club')";
		echo "Wine Club query: " . $wc_query . PHP_EOL;
		$r = mysqli_query($wdb, $wc_query) or die("Error on WineryVarieties INSERT query");

		// Tasting room details 	
		$e = $html->find('div[id=tastingroom]');
		$tr = $e[0]->innertext;

		// squeeze out all the extra whitespace and line break indicators
		$tr = preg_replace('/\s+/', ' ', $tr);
		$tr = str_replace('&nbsp;',' ',$tr);
		// echo "tr: " . $tr . PHP_EOL;


		// find where the music information starts
		if (($cpos = strpos($tr, "Music in Tasting Room:") ) === false) {
			echo "No match for music found" .PHP_EOL;
			
		} else {
			
			// echo "Music found" .PHP_EOL;
			$tr = substr($tr, $cpos);
			$cpos = stripos($tr, ": </strong> ");
			if ($cpos === false) {
			    echo "strong emphasis not located" . PHP_EOL;	
			} 
			// look for <br>
			$epos = stripos($tr, '<br>');
			if ($epos === false) {
			    echo "no <br> located" . PHP_EOL;	
			} else {
			    // echo "<br> loccated at: $epos" . "start pos $cpos" . PHP_EOL;
			}
			$music = strip_tags(substr($tr, $cpos+2, ($epos-$cpos-2)));
			echo "Music: $music" . PHP_EOL;

		}

		// find where the view information starts
		if (($cpos = strpos($tr, "View from Tasting Room:") ) === false) {
			echo "No match for view found" .PHP_EOL;
			
		} else {
			// echo "View found" .PHP_EOL;
			$tr = substr($tr, $cpos);
			$cpos = stripos($tr, ": </strong> ");
			if ($cpos === false) {
			    echo "strong emphasis not located" . PHP_EOL;	
			} 
			// look for <br>
			$epos = stripos($tr, '<br>');
			if ($epos === false) {
			    echo "no <br> located" . PHP_EOL;	
			} else {
			    // echo "<br> loccated at: $epos" . "start pos $cpos" . PHP_EOL;
			}
			$view = strip_tags(substr($tr, $cpos+2, ($epos-$cpos-2)));
			echo "View: $view" . PHP_EOL;
		}

		// find where the food information starts
		if (($cpos = strpos($tr, "Food Available:") ) === false) {
			echo "No match for food found" .PHP_EOL;
				
		} else {	
			// echo "Food found" .PHP_EOL;
			$tr = substr($tr, $cpos);
			$cpos = stripos($tr, ": </strong> ");
			if ($cpos === false) {
			    echo "strong emphasis not located" . PHP_EOL;	
			} 
			// look for <br>
			$epos = stripos($tr, '<br>');
			if ($epos === false) {
			    echo "no <br> located" . PHP_EOL;	
			} else {
			    // echo "break loccated at: $epos" . "start pos $cpos" . PHP_EOL;
			}
			$food = strip_tags(substr($tr, $cpos+2, ($epos-$cpos-2)));
			echo "Food: $food" . PHP_EOL;
		}


		$img = $e[0]->find('img[alt]');

		echo "img src: " . $img[0]->src . PHP_EOL;

		$ls = $img[0]->src;
		$needle = "taste_";
		$lp = stripos($ls, $needle);
		if ($lp === false) {
			    echo "no line taste string located" . PHP_EOL;
			    $val = null;
			} else {
			    // echo "line taste string located at: $lp" . PHP_EOL;
			    $dot = strpos($ls, ".", $lp);
			    $st = strlen($needle);
			    $val = substr($ls, ($lp+$st), ($dot-$lp-$st));
			    // echo "lp: " . $lp . "dot: " . $dot . " st: " . $st . " val: " . $val . PHP_EOL;
			}
		$cont_oldworld = $val;
		echo "contemporary vs old world: " . $cont_oldworld .PHP_EOL;

		echo "img src: " . $img[2]->src . PHP_EOL;

		$ls = $img[2]->src;
		$needle = "taste_";
		$lp = stripos($ls, $needle);
		if ($lp === false) {
			    echo "no line taste string located" . PHP_EOL;
			    $val = null;
		} else {
		    // echo "line taste string located at: $lp" . PHP_EOL;
		    $dot = strpos($ls, ".", $lp);
		    $st = strlen($needle);
		    $val = substr($ls, ($lp+$st), ($dot-$lp-$st));
		    // echo "lp: " . $lp . "dot: " . $dot . " st: " . $st . " val: " . $val . PHP_EOL;
		}
		$form_relax = $val;
		echo "formal vs relaxed: " . $form_relax .PHP_EOL;

		echo "img src: " . $img[4]->src . PHP_EOL;

		$ls = $img[4]->src;
		$needle = "taste_";
		$lp = stripos($ls, $needle);
		if ($lp === false) {
		    echo "no line taste string located" . PHP_EOL;
		    $val = null;
		} else {
		    // echo "line taste string located at: $lp" . PHP_EOL;
		    $dot = strpos($ls, ".", $lp);
		    $st = strlen($needle);
		    $val = substr($ls, ($lp+$st), ($dot-$lp-$st));
		    // echo "lp: " . $lp . "dot: " . $dot . " st: " . $st . " val: " . $val . PHP_EOL;
		}

		$lively_quiet = $val;
		echo "lively vs quiet: " . $lively_quiet .PHP_EOL;


		// echo "cn 17: " . $e[0]->childNodes(17) . PHP_EOL;


		$w_tasting_rm = strip_tags( addslashes($e[0]->childNodes(17)));
		echo "Tasting Room: " . $w_tasting_rm . PHP_EOL;
		$tr_query = "INSERT INTO TastingRoom(music_type, view, food_type, cont_oldworld, form_relax, lively_quiet, descr)" .
		" VALUES ('$music' , '$view' , '$food' , '$cont_oldworld' , '$form_relax' , '$lively_quiet' , '$w_tasting_rm')";
		echo "Tasting Room query: " . $tr_query . PHP_EOL;
		$r = mysqli_query($wdb, $tr_query) or die("Error on WineryVarieties INSERT query");

	} // end scrape_winery()

	/*
	** Main line code execution
	** process the outer list of wineries
	** and pass the url for eachto the scrape winery rtn
	*/

	$wdb = mysqli_connect('localhost', 'andrew', 'fredfred', 'Wineries') or die('error connecting to my sql server');

	$scrape_url = 'http://www.napavintners.com/wineries/wineries_by_name.asp?wineryname=V';

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
	mysqli_close($wdb);
?>

</pre>
</body>
</html>