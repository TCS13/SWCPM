<?php
class message {

    public static function dispMsg($msgNumber) {
        $msgNumber = stripslashes($msgNumber);
        $msg = null;
        $err = 0;
        if ($msgNumber == '1') {
            $msg = "A required parameter was not supplied.";
            $err = 1;
        } elseif ($msgNumber == '2') {
            $msg = "The specified planet does not exist.";
            $err = 1;
        } elseif ($msgNumber == '3') {
            $msg = "The specified raw material does not exist.";
            $err = 1;
        } elseif ($msgNumber == '4') {
            $msg = "The deposit size was invalid.";
            $err = 1;
        } elseif ($msgNumber == '5') {
            $msg = "The x-coordinate was invalid.";
            $err = 1;
        } elseif ($msgNumber == '6') {
            $msg = "The y-coordinate was invalid.";
            $err = 1;
        } elseif ($msgNumber == '7') {
            $msg = "You have added a new deposit to this planet.";
        } elseif ($msgNumber == '8') {
            $msg = "Adding the deposit failed for an unspecified reason.";
	    $err = 1;
        } elseif ($msgNumber == '9') {
            $msg = "You have removed a deposit from this planet.";
        } else
	{
	    $msg = "The error you received was unidentifiable.<br/>Please contact a site administrator with how you received this error.";
	    $err = 1;
	}
        if ($err == 1) {
            return '<table><tr><td><font color="red">' . $msg . '</font></td></tr></table>';
        } else {
            return '<table><tr><td><font color="green">' . $msg . '</font></td></tr></table>';
        }
    }

}

?>