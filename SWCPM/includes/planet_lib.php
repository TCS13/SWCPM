<?php
class planets
{
    public static function dispTerrMapWithDeposits($pid)
    {
	$planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	$plan_terr = mysql_result($planet, 0, 'plan_terr');
	$terr_map_1D_array = str_split($plan_terr);
	$maxGridsPerDimension = sqrt(count($terr_map_1D_array));
    }
    
    //Assumes all parameters have been sufficiently sterilized
    public static function addDeposit($pid, $size, $who, $type, $x, $y)
    {
	$alreadyExists = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."grids WHERE planet_uid = '$pid' AND planX = '$x' AND planY = '$y'");
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
	    header("Location: ".$GLOBALS['this_site']."?pid=".$pid."&msg=7");
	    exit();
	}
	else
	{
	    header("Location: ".$GLOBALS['this_site']."?pid=".$pid."&msg=8");
	    exit();
	}
    }
    
    public static function dispHTMLMapWithDeposits($pid)
    {
	$planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	$plan_terr = mysql_result($planet, 0, 'plan_terr');
	$terr_map_1D_array = str_split($plan_terr);
	$maxGridsPerDimension = sqrt(count($terr_map_1D_array));
    }
    
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
    
    public static function planetExists($pid)
    {
	$planet = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."planets WHERE planet_uid = '$pid'");
	if(mysql_num_rows($planet) == 1)
	{
	    return true;
	}
	return false;
    }
    
    public static function matExists($mid)
    {
	$mat = mysql_query("SELECT * FROM ".$GLOBALS['DB_table_prefix']."mats WHERE rm_uid = '$mid'");
	if(mysql_num_rows($mat) == 1)
	{
	    return true;
	}
	return false;
    }
    
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
}