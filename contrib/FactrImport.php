<?php
/*-------------------------------------------------------------------
*
*		FACTR Information Import
*
*------------------------------------------------------------------*/

Global $debug;

include '..\SysUtils\SysUtils.php';

session_start();
	
if (!isset($_GET['action']))
{
	/*
	 * Initialise System variables
	 */
	session_reset();		// Launching the system - Reset the Session variables.

	$debug = True;	//False;	//True;
	$_SESSION['debug'] = $debug;

	$db_name = 'Facts';
	$db_user = 'root';
	$db_password = '';

	date_default_timezone_set("Australia/Sydney");
	
	//$action = 'Parameters';
	$action = 'Process';	
}	
else
{
	/*
	* If this code is called as a web-page UR, then 'action' is passed.
	*/
	$action = $_GET['action'];
}

$debug = $_SESSION['debug'];


/******************************************
 * Load the Orders System Header/Menu HTML
 */
// sys_menu();

if ($action == "Parameters")
	sys_params();


/**************************
 * What are we processing?
 */
if ($action == "Process")
{
	// Username is gathered as post
	//$username = $_POST['username'];
	// $_SESSION['username'] = $username;
	// Password is also available as post
	// $password = $_POST['password'];
	// $_SESSION['password'] = $password;

	echo "<br><br><br><br><br>";


	//--------------------------------------------------
	// Connect to MySQL and Open the required database
	db_open($db_name, $db_user, $db_password);

	
	// Process the Category
	$categoryid = 0;

	proccategories();

	
	// Process the CSV file
	proccsvfile();

	
	// Close the MySql Database Connection
	db_close();
	
	return;
}


/*********************************************************************
 * Process Category information
 */
function proccategories()
{
	GLOBAL $debug;

	// CATEGORIES record values
	$categoryid = 0;
	GLOBAL $categoryid;
	$category = "Toilets";
	$catsing = "toilet";
	$catplural = "toilets";
	
	// Does the Category exist or must it be added
	if ($debug)
		echo "<br><br>Searching for Category: '$category'<br>";

	$result = mysql_query("SELECT * FROM categories WHERE name = '$category'") or die(db_error('Could not query table.'));
	$rowcount = mysql_num_rows($result);
	if ($debug)
		echo "Rows returned = $rowcount";
	echo "<br><br>";
	
	// Does Category exist?
	if ($rowcount == 0)
	{
		// Category doesn't exist so create it
		$sql = "INSERT INTO categories VALUES (0, '$category', '$catsing', '$catplural')" ;
		$result = mysql_query($sql) or die(db_error('Could not insert category.'));

		if ($debug)
			echo ">> Category '$category' Inserted<br>";
		
		// Extract the assigned Category ID
		$result = mysql_query("SELECT * FROM categories WHERE name = '$category'") or die(db_error('Could not query categories table.'));
	}

	// Extract the current category id
	$recdata = mysql_fetch_array($result);
	$categoryid = $recdata[0];

	if ($debug)
		echo "** Category ID = '$categoryid'<br><br><br>";


	//table_dump('categories');
	//html_table('categories');
}


/*********************************************************************
 * Process the CSV file
 */
function proccsvfile()
{
	GLOBAL $debug, $categoryid;

	$dataurl = 'http://data.gov.au/dataset/4f875c86-2a8c-4daf-b40d-dca04aab49ea/resource/76d31bf1-a106-4c24-b37f-dae17fc691bd/download/ballaratpublictoilets.csv';

	
	// Processing variables
	$datatable = "L";		// "Locales" or "Temporal"

	$geofldname = "";		// MultiPolygon Processing

	$geofldno = 0;
	$latfldname = "Lat";
	$geofldno2 = 0;
	$lonfldname = "Long";

	// Database field values
	$location = "";

	$fld1name = "Site";
	$fld1no = 0;

	$attr1_name = "site";
	$attr1_isnum = 0;
	$attr1_val = "";


	$fld2name = "DoorsAuto";
	$fld2no = 0;

	$attr2_name = "auto door";
	$attr2_isnum = 0;
	$attr2_val = "";


	$fld3name = "";
	$fld3no = 0;

	$attr3_name = "";
	$attr3_isnum = 0;
	$attr3_val = "";

	if ($debug)
		echo "<br><br><br>Reading from '$dataurl' File ...<br><br><br>";

	
	// Remove previous records for the category
	db_query("DELETE FROM locales WHERE category = $categoryid");

	
	// Process the CSV file
	$line = 0;

	if (($csvhandle = fopen($dataurl, "r")) !== FALSE)
	{
		while (($data = fgetcsv($csvhandle, 2000, ",")) !== FALSE)
		{
			if ($line == 0)
			{
				$highfld = count($data);

				// Field headings - establish field numbers to speed up processing
				if (strlen($fld1name) > 0)
				{
					for ($c = 0; $c < $highfld; $c++)
					{
						if ($fld1name == $data[$c])
							$fld1no = $c;
					}
					echo "Field $fld1name = $fld1no<br>";					
				}
				if (strlen($fld2name) > 0)
				{
					for ($c = 0; $c < $highfld; $c++)
					{
						if ($fld2name == $data[$c])
							$fld2no = $c;
					}
					echo "Field $fld2name = $fld2no<br>";					
				}
				if (strlen($fld3name) > 0)
				{
					for ($c = 0; $c < $highfld; $c++)
					{
						if ($fld3name == $data[$c])
							$fld3no = $c;
					}
					echo "Field $fld3name = $fld3no<br>";					
				}

				// Fierlds for the GEO
				if (strlen($geofldname) > 0)
				{
					// Find the Geo Multipolygon field to break up					
					for ($c = 0; $c < $highfld; $c++)
					{
						if ($geofldname == $data[$c])
							$geofldno = $c;
					}
					echo "Field $geofldname = $geofldno<br>";
				}
				else
				{
					// Find the Lat & Long fields
					for ($c = 0; $c < $highfld; $c++)
					{
						if ($latfldname == $data[$c])
							$geofldno = $c;
					}
					echo "Field $latfldname = $geofldno<br>";					

					for ($c = 0; $c < $highfld; $c++)
					{
						if ($lonfldname == $data[$c])
							$geofldno2 = $c;
					}
					echo "Field $lonfldname = $geofldno2<br>";					
				}
				$colhead = $data;
			}
			else
			{
				// Create record in 'Locales' or 'Temporal'

				// Build location GEO Polygon
				if ($geofldno2 == 0)
				{
					//GEO field is in one MULTIPOLYGON field
					$geopolyval = $data[$geofldno];

					$location = "";
				}
				else
				{
					$geolatval = trim($data[$geofldno]);
					$geolonval = trim($data[$geofldno2]);

					$location = "MULTIPOLYGON( ((".$geolatval." ".$geolonval.",". $geolatval." ".$geolonval.",".$geolatval." ".$geolonval.",".$geolatval." ".$geolonval.",".$geolatval." ".$geolonval.") ))";
				}

				if ($fld1no > 0)
					$attr1_val = $data[$fld1no];

				if ($fld2no > 0)
					$attr2_val = $data[$fld2no];

				if ($fld3no > 0)
					$attr3_val = $data[$fld3no];

				$sql = "INSERT INTO locales
						  VALUES (0, '$categoryid', GeomFromText('$location'), 
								  '$attr1_name', $attr1_isnum, '$attr1_val',
								  '$attr2_name', $attr2_isnum, '$attr2_val',
								  '$attr3_name', $attr3_isnum, '$attr3_val')" ;
				
				db_query($sql);
			}

			$line++;
		}
		fclose($csvhandle);
		
		if ($debug)
			echo "<br><br>";
	}
}


/*********************************************************************
 * Create the Orders System Header/Menu HTML
 */
function sys_menu()
{
	GLOBAL $debug, $action;

	// if ($debug)
		// echo "Orders System Menu HTML Creation<br><br>";
	
	echo '<!DOCTYPE html><html>';
	echo ' <head>';
	echo '  <link type="text/css" rel="stylesheet" href="System_Menu.css"/>';
	echo '  <title>Factr Import !!!</title>';
	echo ' </head>';
	
	echo ' <body style="margin:0px">';
	echo '  <div id="header">';
	echo '   <div id="headertext">';
	echo '    <ul><li>Order</li><li>Processing</li><li>System</li></ul>';
	echo '   </div>';

	echo '   <div id="headermenu">';
	echo sys_menu_opt("Home", $action);
	echo sys_menu_opt("Orders", $action);
	echo sys_menu_opt("Lists", $action);
	echo sys_menu_opt("Reports", $action);
	echo sys_menu_opt("Admin", $action);
	echo '   </div>';
	echo '  </div>';
	echo ' </body>';
	echo '</html>';
}


function sys_menu_opt($smo_name, $smo_sel)
{
	// Generates menu options based on which menu selection is current
	$smo_str = '<div><a href="FactrImport.php?action='.$smo_name.'">';
	$smo_str .= '<p id="menuopt'. (($smo_name == $smo_sel)?'sel':''). '">'. $smo_name. '</p></a></div>';
		
	return $smo_str;
}


/*********************************************************************
 * Get the Processing Parameters
 */
function sys_params()
{
	echo '<!DOCTYPE html><html>';
	echo ' <head>';
	echo '  <link type="text/css" rel="stylesheet" href="System_Params.css"/>';
	echo '  <title>Information Import !!!</title>';
	echo ' </head>';

	echo ' <body style="margin:0px">';
	echo '  <div id="processform">';
	echo '   <form action="FactrImport.php?action=Process" method="post">';
	
	echo '    <h2>Parameter Screen</h2>';
	
//	echo '    <div>';
	echo '     <h4>Database:</h4>';
	echo '     <input type="text" name="username" value="Fred Nerk">';
//	echo '    </div>';

//	echo '    <div>';
	echo '     <h4>Password:</h4>';
	echo '     <input type="text" name="password" value="asdf">';
//	echo '    </div>';
	echo '    <br><br>';

	echo '    <input id="Process" type="Submit" value="Process">';

	echo '   </form>';
	echo '  </div>';
	echo ' </body>';
	echo '</html>';

	return;
}


function html_table($ht_name)
{
	Global $debug, $fldlist;
	$pad_string = "&nbsp;";

	// Query the SQL table
	if ($debug)
		echo "HTML Table of '$ht_name' Table<br><br>";
	
	$result = mysql_query("SELECT * FROM $ht_name") or die(db_error('Could not query table.'));
	if ($debug)
		echo "Records selected from '$ht_name' table.<br />";

	// Place the fetched result into an 
	$rowcount = mysql_num_rows($result);
	$colcount = mysql_num_fields($result);
	if ($debug)
		echo "Rows returned = $rowcount,  Column Count = $colcount<br /><br />";

	// Table HTML setup
	echo '<html>';
	echo ' <head>';
	echo '  <link type="text/css" rel="stylesheet" href="html_table.css"/>';
	echo '  <title></title>';
	echo ' </head>';
	
	echo ' <body>';
	echo '  <form action="../test/welcome.php" method="post">';
	echo '  <table>';
	echo '   <thead>';
//	echo '    <tr id="heading"><th colspan="11"><h1>Rod\'s Fine Table</h1></tr>';
	echo '	  <tr>';
	
	// Table Column headings
	for ($c=0; $c < $colcount; $c++)
	{
		$fld = trim(mysql_field_name($result, $c));
		$fldlist[] = $fld;
		echo '<th>'. $fld. '</th>';
	}
		
	echo '	  </tr>';
	echo '   </thead>';
	echo '   <tbody>';
	
	$r = 0;
	while ($recdata = mysql_fetch_array($result))
	{
		echo '<tr>';
		$r++;
		for ($c=0; $c < $colcount; $c++)
		{
			$fld = $fldlist[$c];	// mysql_field_name($result, $c));
			echo '<td><input type="text" name="'.$fld.$r.'" value="'.$recdata[$c].'"></td>';
		}
		echo '</tr>';
	}
	
	// Table HTML finish
	echo '</tbody></table></form></html>';
	//echo '$fldlist = '. print_r($fldlist, True). "<br><br>";

	// Discard the fetched table result
	mysql_free_result($result);
	if ($debug)
		echo "<br>Result released.<br><br>";
}

?>