<?php
//For all functions relating to planets.
class planets
{
    //Displays a terrain map like what would be shown through the mining display in SWC
    //On mouseover, you receive a tooltip with size of deposit, type of deposit and name of who found it
    public static function dispTerrMapWithDeposits($pid)
    {
	$result = '';
	if(\planets::planetExists($pid))
	{
	    $planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	    $plan_terr = mysql_result($planet, 0, 'plan_terr');
	    $terr_map_1D_array = str_split($plan_terr);
	    $maxGridsPerDimension = sqrt(count($terr_map_1D_array));
	    $result = "<table cellpadding=0 border=1>";
	    for($y = 0; $y < $maxGridsPerDimension; $y++)
	    {
		$result .= "<tr>";
		for($x = 0; $x < $maxGridsPerDimension; $x++)
		{
		    $terr_char = $terr_map_1D_array[($y * $maxGridsPerDimension) + $x];
		    $result .= '<td align="center" title="'.\planets::getDepositTextLong($pid , $x , $y).'" valign="middle" style="background-image: url('.\planets::getTerrainImg($terr_char).')" height=35 width=35>';
		    $result .= \planets::getDepositImg($pid, $x, $y);
		    $result .= "</td>";
		}
		$result .= "</tr>";
	    }
	    $result .= "</table>";
	}
	return $result;
    }
    
    //Assumes all parameters have been sufficiently sterilized
    //Adds all deposits to the DB
    //Updates deposits if they already existed, overwriting previous information
    //Removes a deposit record if size == 0
    public static function addDeposit($pid, $size, $who, $type, $x, $y)
    {
	$alreadyExists = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."grids WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
	if($size == 0)
	{
	    $del = mysql_query("DELETE FROM ".$GLOBALS['DB_table_prefix']."grids WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
	    if ($del)
	    {
		header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=9");
		exit();
	    }
	}
	if(mysql_num_rows($alreadyExists) == 1)
	{
	    $result = mysql_query("UPDATE ".$GLOBALS['DB_table_prefix']."grids SET mat_quant = '$size', mat_type = '$type', prospector = '$who' WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
	}
	else
	{
	    $result = mysql_query("INSERT INTO ".$GLOBALS['DB_table_prefix']."grids (planet_uid, planX, planY, mat_quant, mat_type, prospector) VALUES ('$pid', '$x', '$y', '$size', '$type', '$who')");
	}
	if($result)
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=7");
	    exit();
	}
	else
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=8");
	    exit();
	}
    }
    
    //Displays a table with no images, but text representations of the planet's deposits
    public static function dispHTMLMapWithDeposits($pid)
    {
	$result = '';
	if(\planets::planetExists($pid))
	{
	    $planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	    $plan_terr = mysql_result($planet, 0, 'plan_terr');
	    $terr_map_1D_array = str_split($plan_terr);
	    $maxGridsPerDimension = sqrt(count($terr_map_1D_array));
	    $result = "<table cellpadding=10 border=1>";
	    for($y = 0; $y < $maxGridsPerDimension; $y++)
	    {
		$result .= "<tr>";
		for($x = 0; $x < $maxGridsPerDimension; $x++)
		{
		    $result .= "<td align='center'>";
		    $terr_char = $terr_map_1D_array[($y * $maxGridsPerDimension) + $x];
		    $result .= \planets::getTerrainName($terr_char);
		    $depText = \planets::getDepositText($pid , $x , $y);
		    if ($depText != '')
		    {
			$result .= "<br/>".$depText;
		    }
		    $result .= "</td>";
		}
		$result .= "</tr>";
	    }
	    $result .= "</table>";
	}
	return $result;
    }
    
    //Generates the options for all planets that have the possibility of holding mats
    public static function generatePlanetOptions()
    {
	$curr_plan = \planets::getParam('pid');
	$planets = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE unpassable = 0 ORDER BY planet_name ASC");
	$options = '<option value="-1" name="none"';
	if($curr_plan == '' || !\planets::planetExists($curr_plan))
	{
	    $options .= " SELECTED";
	    $curr_plan = '';
	}
	$options .= ' DISABLED>None</option>';
	for($i = 0; $i < mysql_num_rows($planets); $i++)
	{
	    $pid = mysql_result($planets, $i, "planet_uid");
	    $planet_name = mysql_result($planets, $i, "planet_name");
	    $options .= '<option value="'.$pid.'" name="'.$planet_name.'"';
	    if($curr_plan == $pid)
	    {
		$options .= " SELECTED";
	    }
	    $options .= '>'.$planet_name.'</option>';
	    
	}
	return $options;
    }
    
    //Returns the maximum ground coordinate for the specified planet.
    public static function getMaxPlanetGroundCoord($pid)
    {
	$planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	$plan_terr = mysql_result($planet, 0, 'plan_terr');
	$terr_map_1D_array = str_split($plan_terr);
	$total_grids = count($terr_map_1D_array);
	$root_total_grids = sqrt($total_grids);
	return $root_total_grids;
    }
    
    //Gets any parameter passed through post or get
    public static function getParam($param, $default = '') {
        $response = $default;
        if (isset($_REQUEST[$param])) {
            $response = trim($_REQUEST[$param]);
            $response = nl2br($response);
            $response = str_replace("\r\n", '', $response);
            $response = strip_tags($response, '<b><i><u><br><table><tr><th><td>');
            $response = mysql_real_escape_string($response);
            $response = htmlspecialchars($response, ENT_QUOTES, "UTF-8");
        }
        return $response;
    }
    
    //Returns true if the planet exists, false otherwise.
    public static function planetExists($pid)
    {
	$planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	if(mysql_num_rows($planet) == 1)
	{
	    return true;
	}
	return false;
    }
    
    //Returns true if the material exists, false otherwise.
    public static function matExists($mid)
    {
	$mat = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."mats WHERE rm_uid = '$mid'");
	if(mysql_num_rows($mat) == 1)
	{
	    return true;
	}
	return false;
    }
    
    //Generates the options for the RM selector
    public static function generateMatOptions()
    {
	$curr_plan = \planets::getParam('pid');
	$mats = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."mats ORDER BY rm_name ASC");
	$options = '<option value="-1" name="none" SELECTED DISABLED>None</option>';
	for($i = 0; $i < mysql_num_rows($mats); $i++)
	{
	    $mid = mysql_result($mats, $i, "rm_uid");
	    $rm_name = mysql_result($mats, $i, "rm_name");
	    $options .= '<option value="'.$mid.'" name="'.$rm_name.'">'.$rm_name.'</option>';
	    
	}
	return $options;
    }
    
    //Returns the name of the specified terrain
    public static function getTerrainName($tid)
    {
	$terrain = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."terr WHERE terr_char = '$tid'");
	return mysql_result($terrain, 0, 'terr_img');
    }
    
    //Returns the URL of the image for the terrain
    public static function getTerrainImg($tid)
    {
	$terrain = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."terr WHERE terr_char = '$tid'");
	return $GLOBALS['site_name'].'images/terrains/'.mysql_result($terrain, 0, 'terr_img').'.gif';
    }
    
    //Returns the name of the specified material
    public static function getMatName($mid)
    {
	$mat = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."mats WHERE rm_uid = '$mid'");
	return mysql_result($mat, 0, 'rm_name');
    }
    
    //Returns an HTML image of the specified material.
    public static function getMatImg($mid)
    {
	$mat_img = explode(':',$mid)[1];
	return '<img height=20 width=20 src="'.$GLOBALS['site_name'].'images/mats/'.$mat_img.'.gif"/>';
    }
    
    //Returns an HTML image of the specified deposit.
    public static function getDepositImg($pid, $x, $y)
    {
	$deposit = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."grids WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
	if (mysql_num_rows($deposit) == 1)
	{
	    return \planets::getMatImg(mysql_result($deposit, 0, 'mat_type'));
	}
	else
	{
	    return '';
	}
    }
    
    //Returns the text of the deposit for use in the HTML table
    //In form: X unit(s) of [MAT_TYPE].
    public static function getDepositText($pid, $x, $y)
    {
	$deposit = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."grids WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
	if (mysql_num_rows($deposit) == 1)
	{
	    $result = mysql_result($deposit, 0, 'mat_quant');
	    $result .= " unit(s) of ";
	    $result .= \planets::getMatName(mysql_result($deposit, 0, 'mat_type'));
	    $result .= ".";
	    return $result;
	}
	else
	{
	    return '';
	}
    }
    
    //Returns the text of the deposit for use in tooltips
    //In form: X unit(s) of [MAT_TYPE] discovered by [WHO].
    public static function getDepositTextLong($pid, $x, $y)
    {
	$deposit = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."grids WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
	if (mysql_num_rows($deposit) == 1)
	{
	    $result = mysql_result($deposit, 0, 'mat_quant');
	    $result .= " unit(s) of ";
	    $result .= \planets::getMatName(mysql_result($deposit, 0, 'mat_type'));
	    $result .= " discovered by ";
	    $result .= stripslashes(mysql_result($deposit, 0, 'prospector'));
	    $result .= ".";
	    return $result;
	}
	else
	{
	    return 'No deposit here.';
	}
    }
}