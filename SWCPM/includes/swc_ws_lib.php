<?php

class ws
{
    //Populates RM table, updating out-of-date fields as needed
    public static function getMats() {
	$count = 0;
	$mat_uid = NULL;
	$mat_name = NULL;
	$data = simplexml_load_file("http://www.swcombine.com/ws/v1.0/types/materials/");
	foreach ($data->children() as $entities)
	{
	    foreach ($entities->children() as $mats)
	{
	    if ($mats->getName() == "type")
	    {
		foreach ($mats->attributes() as $attName => $att)
		{
                    if ($attName == "uid")
		    {
                        $mat_uid = $att;
                    }
		    elseif ($attName == "name")
		    {
			$mat_name = $att;
		    }
                }
	    }
	    if ($mat_uid != NULL && $mat_name != NULL)
	    {
		mysql_query("INSERT INTO " . $GLOBALS['DB_table_prefix'] . "mats (rm_uid, rm_name) VALUES ('$mat_uid', '$mat_name') ON DUPLICATE KEY UPDATE rm_name = '$mat_name'");
		$mat_uid = NULL;
		$mat_name = NULL;
		$count++;
	    }
	}
	}
	return $count;//Returns count of mat records located
    }
    
    //Populates planet table (names and IDs only), updating out-of-date fields as needed
    public static function getPlanets() {
	$start_index = 0;
	$max = 1;
	$planet_uid = NULL;
	$planet_name = NULL;
	while($start_index < $max)
	{
	    $data = simplexml_load_file("http://www.swcombine.com/ws/v1.0/galaxy/planets/?start_index=".$start_index);
	    foreach($data->attributes() as $attName => $att)
	    {
		if($attName == "total")
		{
		    $max = $att;
		}
	    }
	    foreach ($data->children() as $galaxy_planets)
	    {
		if ($galaxy_planets->getName() == "planet")
		{
		    foreach ($galaxy_planets->attributes() as $attName => $att)
		    {
			if ($attName == "uid")
			{
			    $planet_uid = $att;
			}
			elseif ($attName == "name")
			{
			    $planet_name = $att;
			}
		    }
		}
		if ($planet_uid != NULL && $planet_name != NULL)
		{
		    mysql_query("INSERT INTO " . $GLOBALS['DB_table_prefix'] . "planets (planet_uid, planet_name) VALUES ('$planet_uid', '$planet_name') ON DUPLICATE KEY UPDATE planet_name = '$planet_name'");
		    $planet_uid = NULL;
		    $planet_name = NULL;
		    $start_index++;
		}
	    }
	}
	return $start_index-1;
    }
    
    //Updates the location of all planets as well as the planet map
    public static function updatePlanets()
    {
	$galX = NULL;
	$galY = NULL;
	$sysX = NULL;
	$sysY = NULL;
	$planet_map = NULL;
	$planets = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets");
	for($i = 0; $i < mysql_num_rows($planets); $i++)
	{
	    $pid = mysql_result($planets, $i, 'planet_uid');
	    $data = simplexml_load_file("http://www.swcombine.com/ws/v1.0/galaxy/planets/".$pid);
	    foreach ($data->children() as $galaxy_planet)
	    {
		if($galaxy_planet->getName() == "coordinates")
		{
		    foreach($galaxy_planet->children() as $coords)
		    {
			if($coords->getName() == "galaxy")
			{
			    foreach ($coords->attributes() as $attName => $att)
			    {
				if ($attName == "x")
				{
				    $galX = $att;
				}
				elseif ($attName == "y")
				{
				    $galY = $att;
				}
			    }
			}
			if($coords->getName() == "system")
			{
			    foreach ($coords->attributes() as $attName => $att)
			    {
				if ($attName == "x")
				{
				    $sysX = $att;
				}
				elseif ($attName == "y")
				{
				    $sysY = $att;
				}
			    }
			}
		    }
		}
		if($galaxy_planet->getName() == "terrain-map")
		{
		    $planet_map = $galaxy_planet;
		}
	    }
	    if($galX != NULL && $galY != NULL && $sysX != NULL && $sysY != NULL && $planet_map != NULL)
	    {
		mysql_query("UPDATE ".$GLOBALS['DB_table_prefix']."planets SET galX = '$galX', galY = '$galY', sysX = '$sysX', sysY = '$sysY', plan_terr = '$planet_map' WHERE planet_uid = '$pid'");
		$galX = NULL;
		$galY = NULL;
		$sysX = NULL;
		$sysY = NULL;
		$planet_map = NULL;
	    }
	}
	return "Success";
    }
    
    //Deactivates all planets completely filled with volcanoes, black holes and suns.
    public static function deactivateUnprospectablePlanets()
    {
	$planets = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets");
	for($i = 0; $i < mysql_num_rows($planets); $i++)
	{
	    $unpassable = true;
	    $pid = mysql_result($planets, $i, 'planet_uid');
	    $terr_map_singular = mysql_result($planets, $i, 'plan_terr');
	    $terr_map_1D_array = str_split($terr_map_singular);
	    for($j = 0; $j < count($terr_map_1D_array); $j++)
	    {
		if($terr_map_1D_array[$j] != 'y' && $terr_map_1D_array[$j] != 'z' && $terr_map_1D_array[$j] != 'm')
		{
		    $unpassable = false;
		    break;
		}
	    }
	    if($unpassable)
	    {
		mysql_query("UPDATE ".$GLOBALS['DB_table_prefix']."planets SET unpassable = 1 WHERE planet_uid = '$pid'");
	    }
	}
	return "Completed";
    }
}