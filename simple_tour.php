<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Display a simple tour</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<pre>
<?php # simple_tour.php

	/* define simple_tour_rqst()
	**     - return a set of results using "simple" selection criteria
	** $wdb: 	winery database 
	** $wsel: 	wine type or varietal depending on $var
	** $wlimit:	limit on the number of wineries to return
	** $var:	boolean: true = varietla rqst, false = wine type rqst
	*/ 
	function simple_tour_rqst($wdb, $wsel, $wlimit, $var) {

		$winery_query = "SELECT DISTINCT name, full_addr FROM Winery" . 
							" INNER JOIN WineryVarieties USING (w_id)" .
							" INNER JOIN Varietals USING (var_id)";
		if (!$var) {
			// Select winery based on style
			$winery_query .= " WHERE color='" . $wsel . "'" . 
							 " LIMIT $wlimit";
		}
		else {
			// Select winery based on varietal
			$winery_query .= " WHERE type='" . $wsel . "'" . 
							 " LIMIT $wlimit";
		} 

		echo "Winery query: $winery_query" . PHP_EOL;
		$winery_res = mysqli_query($wdb, $winery_query);
		// print_r($var_res);
		if (($w_cnt = mysqli_num_rows($winery_res)) >= 1) {
			echo "# rows: $w_cnt" . PHP_EOL;
			for ($i=0; $i < $w_cnt; $i++) { 
				$r1 = mysqli_fetch_array($winery_res);
				echo "Winery name: " . $r1['name'] . " address " . $r1['full_addr'] . PHP_EOL;
			}
		}
	}

	$wdb = mysqli_connect('localhost', 'andrew', 'fredfred', 'Wineries') or die('error connecting to my sql server');

	// check to see if the form has been submitted yet
	if (isset($_POST['submit'])) {
		$tourn = $_POST['tourn'];
		$wtype = $_POST['wstyle'];
		$wvar = $_POST['wvar'];
		$wlimit = $_POST['wlimit'];

		if (isset($wtype)) {
			echo "Tour name: $tourn, Wine preference: $wtype @ $wlimit wineries." . PHP_EOL;
			simple_tour_rqst($wdb, $wtype, $wlimit, FALSE);
		}
		elseif (isset($wvar)) {
			echo "Tour name: $tourn, Wine preference: $wvar @ $wlimit wineries." . PHP_EOL;
			simple_tour_rqst($wdb, $wvar, $wlimit, TRUE);
		}
		else {
			echo "Please specify a wine variety or style" . PHP_EOL;
		}

	} 
	// form has not been submitted; we will need to show it
	else {
		$show_form = TRUE;
	}

	if ($show_form) {
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<label for="tourn">Tour name:</label><br />
	<input id="tourn" name="tourn" type="text" size="30" /><br />
	<label for="wstyle">Wine style:</label><br />
	<input type="radio" name="wstyle" value="Red">Red 
	<input type="radio" name="wstyle" value="White">White 
	<input type="radio" name="wstyle" value="Sparkling">Sparkling <br />
	<label for="wvar">Wine varietal:</label><br />
	<input type="radio" name="wvar" value="Cabernet Sauvignon">Cabernet Sauvignon  
	<input type="radio" name="wvar" value="Albarino">Albarino  
	<input type="radio" name="wvar" value="Viognier">Viognier <br />
	<label for="wlimit">How many wineries?:</label><br />
	<input id="wlimit" name="wlimit" type="text" size="2" /><br />
	<input type="submit" name="submit" value="Submit" />
</form>

<?php
	}
?>

</pre>
</body>
</html>