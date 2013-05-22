<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Test Tour Options</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<pre>
<?php # test_tour_options.php

	/*
	** varietals_box
	**		- create the html for a user to select a varietal 
	*/
	function varietals_box($wdb) {

		$varietals = array();
		$var_query = "SELECT var_id, type FROM Varietals";

		$var_res = mysqli_query($wdb, $var_query);
		if (($w_cnt = mysqli_num_rows($var_res)) >= 1) {
			for ($i=0; $i < $w_cnt; $i++) { 
				$r1 = mysqli_fetch_array($var_res);
				array_push($varietals, $r1);
			}
		}
	?>
	<br> Select grape variety: </br>
	<fieldset>
	<?php
		$i = 0;
		$j = 0;
		echo "</br>";
		foreach ($varietals as $varietal) {
			$j++;
			echo '<input type="checkbox" name="var'.$j.'" value="' . $varietal['var_id'] . '"/>' . $varietal['type'] . ' ';
			if ($i++ == 5) {
				$i = 0;
				echo "</br>";
			}
		}
	?>
	</fieldset>

	<?php
		return;

	}	// varietals_box()

	/*
	** check_varietals_form()
	**		- get an array of features to use for a SELECT query
	*/
	function check_varietals_form() {
		$var = array();
		// echo "Selected varietals".PHP_EOL;
		foreach ($_POST as $key => $value) {
			if (substr_compare($key, "var", 0, 3)==0) {
				// echo " $key = $value  ";
				array_push($var, $value);
			}
		}
		// echo PHP_EOL;
		return $var;
	}



	/*
	** features_box
	**		- create the html for a user to select a winery feature 
	*/
	function features_box($wdb) {

		$facilities = array();
		$fac_query = "SELECT fac_id, type FROM Facilities";

		$fac_res = mysqli_query($wdb, $fac_query);
		if (($w_cnt = mysqli_num_rows($fac_res)) >= 1) {
			for ($i=0; $i < $w_cnt; $i++) { 
				$r1 = mysqli_fetch_array($fac_res);
				array_push($facilities, $r1);
			}
		}
	?>
	<br> Select winery feature: </br>
	<fieldset>
	<?php
		$i = 0;
		$j = 0;
		echo "</br>";
		foreach ($facilities as $fac) {
			$j++;
			echo '<input type="checkbox" name="fac' .$j.'" value="' . $fac['fac_id'] . '"/>' . $fac['type'] . ' ';
			if ($i++ == 4) {
				$i = 0;
				echo "</br>";
			}
		}

	?>
	</fieldset>


<?php
		return $fac_ids;

	} // features_box()

	/*
	** check_features_form()
	**		- get an array of features to use for a SELECT query
	*/
	function check_features_form() {
		$fac = array();
		// echo "Selected features".PHP_EOL;
		foreach ($_POST as $key => $value) {
			if (substr_compare($key, "fac", 0, 3)==0) {
				// echo " $key = $value  ";
				array_push($fac, $value);
			}
		}
		// echo PHP_EOL;
		return $fac;
	}

	/*
	** build_varietals_features_rqst()
	*/
	function build_varietals_features_rqst($varietals, $features) {
		$winery_query = "SELECT name, full_addr FROM Winery" . 
							" INNER JOIN WineryVarieties USING (w_id)" .
							" INNER JOIN WineryFacilities USING (w_id)";

		// Build a WHERE clause in this format: 
		//    WHERE var_id IN (1, 2) AND fac_id IN (1, 2) 
		//           GROUP BY name 
		//           HAVING COUNT(DISTINCT var_id) = 2 AND COUNT(DISTINCT fac_id) = 2

		// process the varietal selections first
		$var_cnt = 0;
		$where = " WHERE private=FALSE ";

		if (count($varietals) > 0) {  // process varietals
			$where .= "AND var_id in (";

			foreach ($varietals as $var) {
				if ($var_cnt == 0) {
					$where .= $var;
				} else {
					$where .= ", " . $var ;
				}
				$var_cnt++;
			}
			$where .= ") ";
		}  // process varietals

		// process the winery features
		$feat_cnt = 0;
		if (count($features) > 0) {  // process features

			$where .= " AND fac_id IN (";

			foreach ($features as $feature) {
				if ($feat_cnt == 0) {
					$where .= $feature;
				} else {
					$where .= ", " . $feature;
				}
				$feat_cnt++;
			}

			$where .= ") ";
		}   // process features

		if ($feat_cnt > 0 && $var_cnt > 0) { // features and varietals present in request
			$where .= "GROUP BY name HAVING COUNT(DISTINCT var_id) = $var_cnt AND COUNT(DISTINCT fac_id) = $feat_cnt";
		}
		elseif ($feat_cnt > 0) { 			// features only present in request
			$where .= "GROUP BY name HAVING COUNT(DISTINCT fac_id) = $feat_cnt";
		}
		elseif ($var_cnt > 0) {				// varietals only present in request
			$where .= "GROUP BY name HAVING COUNT(DISTINCT var_id) = $var_cnt";
		}

		// build finalized query 
		$winery_query .= $where;
		return $winery_query;
	}  // build_varietals_features_rqst()


	/*
	** Mainline code
	*/

	$wdb = mysqli_connect('localhost', 'andrew', 'fredfred', 'Wineries') or die('error connecting to my sql server');

    /*
	** if the form has been submitted, process the form selections
	*/
	if (isset($_POST['submit'])) {

		$varietals = check_varietals_form();

		$features = check_features_form();

		$winery_query = build_varietals_features_rqst($varietals, $features);
		
		// echo "Winery query: $winery_query" . PHP_EOL;
		$winery_res = mysqli_query($wdb, $winery_query);
		if (($w_cnt = mysqli_num_rows($winery_res)) >= 1) {
			echo "# rows: $w_cnt" . PHP_EOL;
			for ($i=0; $i < $w_cnt; $i++) { 
				$r1 = mysqli_fetch_array($winery_res);
				// echo "Winery name: " . $r1['name'] . " address " . $r1['full_addr'] . PHP_EOL;
			}
		}
	}

	// form has not been submitted; we will need to show it
	else {
		$show_form = TRUE;
	}

	if ($show_form) {
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<?php	
	varietals_box($wdb);

	features_box($wdb);
	?>
	<input type="submit" name="submit" value="Submit" />
</form>
<?php
	}
?>

</pre>
</body>
</html>