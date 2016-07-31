<?php

$BASEURL = "http://planr.ballarathackerspace.org.au/";
$CLIENTURL = $BASEURL."factr/web/";
$APIURL = $BASEURL."factr/api/";

// Kickstart the framework
$f3=require('lib/base.php');
//$f3->set('CACHE','memcache=localhost');
$f3->set('CACHE',FALSE);

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

$db=new \DB\SQL('mysql:host=localhost;port=3306;dbname=facts','facts','passw0rd');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$CATEGORIES = $db->exec('SELECT DISTINCT id FROM categories');
$NUMSERVICES = count($CATEGORIES);

// Load configuration
$f3->config('config.ini');

$f3->route('GET /',
	function($f3) {
		$classes=array(
			'Base'=>
				array(
					'hash',
					'json',
					'session'
				),
			'Cache'=>
				array(
					'apc',
					'memcache',
					'wincache',
					'xcache'
				),
			'DB\SQL'=>
				array(
					'pdo',
					'pdo_dblib',
					'pdo_mssql',
					'pdo_mysql',
					'pdo_odbc',
					'pdo_pgsql',
					'pdo_sqlite',
					'pdo_sqlsrv'
				),
			'DB\Jig'=>
				array('json'),
			'DB\Mongo'=>
				array(
					'json',
					'mongo'
				),
			'Auth'=>
				array('ldap','pdo'),
			'Bcrypt'=>
				array(
					'mcrypt',
					'openssl'
				),
			'Image'=>
				array('gd'),
			'Lexicon'=>
				array('iconv'),
			'SMTP'=>
				array('openssl'),
			'Web'=>
				array('curl','openssl','simplexml'),
			'Web\Geo'=>
				array('geoip','json'),
			'Web\OpenID'=>
				array('json','simplexml'),
			'Web\Pingback'=>
				array('dom','xmlrpc')
		);
		$f3->set('classes',$classes);
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET /userref',
	function($f3) {
		$f3->set('content','userref.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET /services',
    function()
    {
        global $db, $f3;
        $name = $f3->get('POST.name');
        $rows=$db->exec('SELECT DISTINCT category FROM points');
        echo json_encode($rows);
    }
);


$f3->route('GET /services/@service',
    function() {
        global $db, $f3;
        $service = $f3->get('PARAMS.service');	
        $sql = "SELECT * FROM points WHERE category='$service'";
        $rows=$db->exec($sql);
        echo json_encode($rows);
    }
);

//Retrieve a list of the nearest services from a lat/lon 
//latlonran should be in the form of: latitude,longitude,range (in km)
$f3->route('GET /services/within10/walking/@latlon',
    function() {
        global $db, $f3, $WALKDISTANCE;
        $R=6371000; //Radius of the earth in m
        $ran = $WALKDISTANCE;

        list($lat, $lon) = explode(",",$f3->get('PARAMS.latlon'));
        // first-cut bounding box (in degrees)
        $maxLat = $lat + rad2deg($ran/$R);
        $minLat = $lat - rad2deg($ran/$R);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLon = $lon + rad2deg($ran/$R/cos(deg2rad($lat)));
        $minLon = $lon - rad2deg($ran/$R/cos(deg2rad($lat)));
        
        $sql = "Select DISTINCT category
                From points
                Where lat Between $minLat And $maxLat
                And lon Between $minLon And $maxLon";
        $rows=$db->exec($sql,NULL,86400);
        echo json_encode($rows);
    }
);

$f3->route('GET /services/within10/riding/@latlon',
    function() {
        global $db, $f3, $RIDEDISTANCE;
        $R=6371000; //Radius of the earth in m
        $ran = $RIDEDISTANCE;

        list($lat, $lon) = explode(",",$f3->get('PARAMS.latlon'));
        // first-cut bounding box (in degrees)
        $maxLat = $lat + rad2deg($ran/$R);
        $minLat = $lat - rad2deg($ran/$R);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLon = $lon + rad2deg($ran/$R/cos(deg2rad($lat)));
        $minLon = $lon - rad2deg($ran/$R/cos(deg2rad($lat)));
        $sql = "Select DISTINCT category
                From points
                Where lat Between $minLat And $maxLat
                And lon Between $minLon And $maxLon";
        $rows=$db->exec($sql,NULL,86400);
        echo json_encode($rows);
    }
);

$f3->route('GET /services/within10/driving/@latlon',
    function() {
        global $db, $f3, $DRIVEDISTANCE;
        $R=6371000; //Radius of the earth in m
        $ran = $DRIVEDISTANCE;

        list($lat, $lon) = explode(",",$f3->get('PARAMS.latlon'));
        // first-cut bounding box (in degrees)
        $maxLat = $lat + rad2deg($ran/$R);
        $minLat = $lat - rad2deg($ran/$R);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLon = $lon + rad2deg($ran/$R/cos(deg2rad($lat)));
        $minLon = $lon - rad2deg($ran/$R/cos(deg2rad($lat)));
        $sql = "Select DISTINCT category
                From points
                Where lat Between $minLat And $maxLat
                And lon Between $minLon And $maxLon";
        $rows=$db->exec($sql,NULL,86400);
        echo json_encode($rows);
    }
);

$f3->route('GET /services/categories/@rad/@latlon',
    function() {
        global $db, $f3;

        $rad = $f3->get('PARAMS.rad');
        list($lat, $lon) = explode(",",$f3->get('PARAMS.latlon'));
        $location = sprintf("POINT(%F %F)", $lat, $lon);
        $sql = "SELECT DISTINCT category
                FROM locales
                WHERE (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad;";
   
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);                     
        $stmt->execute();   
        
        echo json_encode($stmt->fetchAll());
    }
);

$f3->route('GET /services/categories',
    function() {
        global $db, $f3;

        $sql = "SELECT id, name, singular, plural
                FROM categories";
   
        $stmt = $db->prepare($sql);
        $stmt->execute();   
        
        echo json_encode($stmt->fetchAll());
    }
);

$f3->route('GET /services/random/string/@rad/@latlon',
    function() {
        global $db, $f3;

        $rad = $f3->get('PARAMS.rad');
        list($lat, $lon) = explode(",",$f3->get('PARAMS.latlon'));
        $location = sprintf("POINT(%F %F)", $lat, $lon);
        $category = "SELECT category
                    FROM locales
                    WHERE (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad
                    ORDER BY RAND() LIMIT 1";
        $sql = "SELECT *
                FROM locales
                WHERE category = 6
                AND (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad";
        $sql = "SELECT *
                FROM temporal
                WHERE category = 6
                AND (ST_Distance(temporal.location, GeomFromText(:location) )*111195) < $rad";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);                     
        $stmt->execute();  
        //print_r($stmt->fetchAll()); 
        
        $categoryID = $stmt->fetch()[1];
        $categorySQL = "SELECT plural
                        FROM categories
                        WHERE id = $categoryID;";
        $categoryName = $db->query($categorySQL)->fetch()[0];
        
        echo "There are ".$stmt->rowCount()." $categoryName within $rad metres of you";
        //print_r($stmt->fetchAll()); 
        
        //echo json_encode($stmt->fetchAll());
    }
);

function randomThing($db, $lat, $lon, $rad, $categories, $time_period)
{
        //Randomise the user defined categories
        if(sizeof($categories) > 0) { shuffle($categories); }
        
        $location = sprintf("POINT(%F %F)", $lat, $lon); 
        
        //If the categories array is empty, we do a search of all categories in the area
        $targetDate = new DateTime('NOW');
        $time_period = rand(50,500);
        $targetDate->sub(new DateInterval("P".$time_period."D"));
        
        
        $categories = [];
        while(sizeof($categories) == 0)
        {
            //Randomise which table we go for
            if(rand(0,1))
            {
                $category = "SELECT DISTINCT category
                            FROM locales
                            WHERE (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad
                            ORDER BY RAND()";
                $useTime = false;
            }
            else
            {
                $category = "SELECT DISTINCT category
                        FROM temporal
                        WHERE (ST_Distance(temporal.location, GeomFromText(:location) )*111195) < $rad
                        AND date > '".$targetDate->format('Y/m/d')."'
                        ORDER BY RAND()";
                $useTime = true;
            }
            $stmt = $db->prepare($category);
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);                     
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $categoryIDs = join("','",$categories);

        //Set the output variables
        $category = "";
        $distance = 0;
        $type = "count";
        $quip = "";
        $value = 0;
        $value_name = "";
        
        $sql = "";
        $category = $categories[0];

        if($useTime)
        {   
            $isNumSQL = "SELECT MAX(attr1_isnum) AS attr1_isnum, attr1_name
                        FROM temporal
                        WHERE category = $category
                        GROUP BY attr1_name
                        LIMIT 1";
        }
        else
        {   
            $isNumSQL = "SELECT MAX(attr1_isnum) AS attr1_isnum, attr1_name
                        FROM locales
                        WHERE category = $category
                        GROUP BY attr1_name
                        LIMIT 1";
        }
        $isNum = false;
        $isNumResult = $db->query($isNumSQL);
        if($isNumResult) 
        {
            $array = $isNumResult->fetch();
            $isNum = $array['attr1_isnum']; 
            $value_name = $array['attr1_name']; 
        }
        if(!$isNum)
        {
            $categoryNameSQL = "SELECT plural
                                FROM categories
                                WHERE id = $category
                                LIMIT 1";
            $value_name = $db->query($categoryNameSQL)->fetch()[0];
        }   
                        
        //Choose the output type
        switch(rand(0,2))
        //switch(0)
        {
            case 0: //average
                if($isNum)
                { 
                    //Average
                    if($useTime)
                    {
                        $sql = "SELECT AVG(attr1_val) AS value, MIN(ST_Distance(temporal.location, GeomFromText(:location) )*111195) AS distance
                                FROM temporal
                                WHERE category = $category
                                AND (ST_Distance(temporal.location, GeomFromText(:location) )*111195) < $rad
                                AND date > '".$targetDate->format('Y/m/d')."'";
                        $type = "average";
                    }
                    else
                    {
                        $sql = "SELECT AVG(attr1_val) AS value, MIN(ST_Distance(locales.location, GeomFromText(:location) )*111195) AS distance
                                FROM locales
                                WHERE category = $category
                                AND (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad";
                        $type = "average";
                    }
                
                }
                else
                {
                    if($useTime)
                    {
                        $sql = "SELECT COUNT(attr1_val) AS value, MIN(ST_Distance(temporal.location, GeomFromText(:location) )*111195) AS distance
                                FROM temporal
                                WHERE category = $category
                                AND (ST_Distance(temporal.location, GeomFromText(:location) )*111195) < $rad
                                AND date > '".$targetDate->format('Y/m/d')."'";
                        $type = "count";
                    }
                    else
                    {
                        $sql = "SELECT COUNT(attr1_val) AS value, MIN(ST_Distance(locales.location, GeomFromText(:location) )*111195) AS distance
                                FROM locales
                                WHERE category = $category
                                AND (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad";
                        $type = "count";
                    }
                }
                break;
                
            case 1: //Sum
                if($isNum)
                { 
                    //Sum
                    if($useTime)
                    {
                        $sql = "SELECT SUM(attr1_val) AS value, MIN(ST_Distance(temporal.location, GeomFromText(:location) )*111195) AS distance
                                FROM temporal
                                WHERE category = $category
                                AND (ST_Distance(temporal.location, GeomFromText(:location) )*111195) < $rad
                                AND date > '".$targetDate->format('Y/m/d')."'";
                        $type = "sum";
                    }
                    else
                    {
                        $sql = "SELECT SUM(attr1_val) AS value, MIN(ST_Distance(locales.location, GeomFromText(:location) )*111195) AS distance
                                FROM locales
                                WHERE category = $category
                                AND (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad";
                        $type = "sum";
                    }
                
                }
                else
                {
                    $sql = "SELECT COUNT(attr1_val) AS value, MIN(ST_Distance(locales.location, GeomFromText(:location) )*111195) AS distance
                                FROM locales
                                WHERE category = $category
                                AND (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad";
                    $type = "count";
                }
                break;
                
            case 2: //Most common 
                if($useTime)
                    {
                        $sql = "COUNT(attr1_val) AS value_count, attr1_name,  attr1_val as value, MIN(ST_Distance(temporal.location, GeomFromText(:location) )*111195) AS distance
                                FROM temporal
                                WHERE category = $category
                                AND (ST_Distance(temporal.location, GeomFromText(:location) )*111195) < $rad
                                AND date > '".$targetDate->format('Y/m/d')."'
                                GROUP BY value
                                ORDER BY value_count DESC
                                LIMIT 1";
                        $type = "common";
                    }
                    else
                    {
                        $sql = "SELECT COUNT(attr1_val) AS value_count, attr1_name, attr1_val as value, MIN(ST_Distance(locales.location, GeomFromText(:location) )*111195) AS distance
                                FROM locales
                                WHERE category = $category
                                AND (ST_Distance(locales.location, GeomFromText(:location) )*111195) < $rad
                                GROUP BY value
                                ORDER BY value_count DESC
                                LIMIT 1";
                        $type = "common";
                    }
                    break;
        }
        //echo $sql;
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);                     
        $stmt->execute(); 
        $final_result = $stmt->fetch();
        
        if($type == "common") { $value_name = $final_result['attr1_name']; }
        
        //print_r($final_result);
        
        $value = $final_result['value'];
        //$value_name = $data[0][attr1_name];
        $distance = $final_result['distance'];
        
        if($value == "") { return array(); }
        
        $categorySQL = "SELECT quip
                        FROM quips
                        WHERE category IN ('$category',0);
                        ORDER BY RAND()
                        LIMIT 100";
        $categorySQL = "SELECT quip
                        FROM quips
                        WHERE category IN ('$category',0)";
        $quipResult = $db->query($categorySQL);
        $quips = $quipResult->fetchAll();
        shuffle($quips);
        if(sizeof($quips) > 0) { $quip = $quips[0][0]; }
        
        return array("category" => $category, "distance" => $distance, "type" => $type, "quip" => $quip, "value" => $value, "value_name" => $value_name, "time_period" => $time_period, "is_number" => $isNum);
}

$f3->route('POST /json/random',
    function() {
        global $db, $f3;

        //echo $f3->get('POST.blah');
        $json = json_decode($f3->get('POST.request'), true);
        
        if($json == "") { return; }
        
        //var_dump($json);
        $lat = $json['lat'];
        $lon = $json['lon'];
        $rad = $json['radius'];
        $categories = $json['categories'];
        $max_results = $json['max_results'];
        $time_period = $json['time_period'];
        
        $json = array();
        for ($x=0; $x < $max_results; $x++) 
        {  
            $json[$x] = randomThing($db, $lat, $lon, $rad, $categories, $time_period);
        }
        echo json_encode($json);

    }
);

$f3->route('POST /json/sentence',
    function() {
        global $db, $f3;

        $json = $f3->get('POST.request');
        //$json = json_decode($f3->get('POST.request'), true);

        //echo $json;
        //if($json == "") { return; }
        $json = str_replace("'","'\''",$json);
        $cmd = "curl -H \"Content-Type: application/json\" -X POST -d '$json' 127.0.0.1:5000/create_sentence";
        //echo $cmd;
        echo exec($cmd);

    }
);

//Returns a list of lat/lon coordinates in a grid based on a boudning box and the number of points required
//input is in the form lat1,lon1,lat2,lon2,x,y where
// lat1, lon1 are the latitude and longitude of the top left point of the bounding box
// lat2, lon2 are the latitude and longitude of the bottom right point of the boudning box
// x, y are the number of desired grid points along each axis
$f3->route('GET /utils/getGrid/@mode/@input',
    function() {
        global $f3;

        list($lat1, $lon1, $lat2, $lon2, $xSteps, $ySteps) = explode(",",$f3->get('PARAMS.input'));

        $latStep = abs(($lat2-$lat1)/$xSteps);
        $lonStep = (($lon2-$lon1)/$ySteps);

        echo "[";
        for ($x=0; $x <= $xSteps; $x++) {
            $curX = $lat1 - ($x * $latStep);
            if ($x > 0) { echo ","; }
            for ($y=0; $y <= $ySteps; $y++) {
                if ($y > 0) { echo ","; }
                $curY = $lon1 + ($y * $lonStep);
                //echo "{\"lat\":\""$curX."\",\"lon\":\"".$curY."\"},";
                //$heat = heat("walking", $curX, $curY);
                $heat = heat($f3->get('PARAMS.mode'), $curX, $curY);
                echo '{"lat":"'.$curX.'","lon":"'.$curY.'","heat":"'.$heat.'"}';
            }
        }
        echo "]";
    }
);


$f3->run();
