<?php
    //Include the standard header info on all pages
    include('includes/header.php');
    //Save the planet's ID, if any selected
    $pid = \planets::getParam('pid');
    //Display any error or success messages
    if(\planets::getParam('msg') != '')
    {
	echo \message::dispMsg(\planets::getParam('msg'));
	echo "<br/>";
    }
    //Processes submissions of new deposits
    if(\planets::getParam('mode') == 'submit' && $pid != '')
    {
	$who = addslashes(\planets::getParam('who'));
	$mat = \planets::getParam('mat');
	$size = \planets::getParam('size');
	$x = \planets::getParam('surfX');
	$y = \planets::getParam('surfY');
	//Verifies no blank parameters
	if($who == '' || $mat == '' || $size == '' || $x == '' || $y == '')
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=1");
	    exit();
	}
	//Verifies planet exists
	if(!\planets::planetExists($pid))
	{
	    header("Location: ".$GLOBALS['site_name']."?msg=2");
	    exit();
	}
	//Verifies RM exists
	if(!\planets::matExists($mat))
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=3");
	    exit();
	}
	//Removes formatting from numbers, verifies size is >= 0 and numeric
	$size = str_replace(",", '', $size);
	if($size < 0 || !is_numeric($size))
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=4");
	    exit();
	}
	//Verifies x-coordinate is valid given the current planet
	if(!($x >= 0 && $x < \planets::getMaxPlanetGroundCoord($pid)))
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=5");
	    exit();
	}
	//Verifies y-coordinate is valid given the current planet
	if(!($y >= 0 && $y < \planets::getMaxPlanetGroundCoord($pid)))
	{
	    header("Location: ".$GLOBALS['site_name']."?pid=".$pid."&msg=6");
	    exit();
	}
	//Adds/Removes/Updates the deposit and redirects with an appropriate success/error message
	\planets::addDeposit($pid , $size , $who , $mat, $x , $y);
    }
?>
<script type="text/javascript">
    function setPID() {
	var planetSel = document.getElementById("planet");
	var planetID = planetSel.options[planetSel.selectedIndex].value;
	form = document.createElement('form');
	form.setAttribute('method', 'POST');
	form.setAttribute('action', 'http://dot.swc-tf.com/ted_pm/');
	myvar = document.createElement('input');
	myvar.setAttribute('name', 'pid');
	myvar.setAttribute('type', 'hidden');
	myvar.setAttribute('value', planetID);
	form.appendChild(myvar);
	document.body.appendChild(form);
	form.submit(); 
    }
</script>
<!--Allows changing what planet is being viewed-->
<select name="planet" onchange="javascript: setPID()" id="planet" value="type">
    <?php echo \planets::generatePlanetOptions(); ?>
</select>

<?php
    if ($pid != '')
    {
	echo "<br/><br/>";
	echo \planets::dispTerrMapWithDeposits($pid);
	echo "<br/><br/>";
	echo \planets::dispHTMLMapWithDeposits($pid);
	echo "<br/><br/>";
	?>
	<!--Form for submitting new deposits-->
	<form method="POST" action="index.php?mode=submit">
	    <table>
		<tr>
		    <td>Name:</td><td><input type="hidden" name="pid" value="<? echo $pid ?>"/><input type="text" name="who" size="50"/></td>
		</tr>
		<tr>
		    <td>Choose mat type:</td>
		    <td>
			<select name="mat" id="planet" value="type">
			    <?php echo \planets::generateMatOptions(); ?>
			</select>
		    </td>
		</tr>
		<tr>
		    <td>Size of deposit:</td><td><input type="text" name="size" size="50"/></td>
		</tr>
		<tr>
		    <td>Surface X:</td><td><input type="text" name="surfX" size="50"/></td>
		</tr>
		<tr>
		    <td>Surface Y:</td><td><input type="text" name="surfY" size="50"/></td>
		</tr>
		<tr>
		    <td colspan="2" align="center"><input type="submit" size="50" name="Submit" value="Submit"/></td>
		</tr>
	    </table>
	</form>
	<?php
    }