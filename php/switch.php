<?php

function _SwitchPage($strPage)
{
//	session_write_close();	// save before switch
	header($strPage);
	exit();
}

function SwitchToLink($strLink)
{
	$str = "location: ";
	$str .= $strLink;

	_SwitchPage($str);
}

function SwitchTo($strTitle)
{
	SwitchToLink($strTitle.UrlGetType());
}

function SwitchSetSess()
{
	$_SESSION['userurl'] = UrlGetCur();
}

function SwitchGetSess()
{
	return $_SESSION['userurl'];
}

function SwitchToSess()
{
    if (isset($_SESSION['userurl'])) 
    { 
//        $url = $_SESSION['userurl']; 
//        echo "<meta http-equiv=\"refresh\" content=\"0.5;url=$url\">";  //0.5s后跳转 
		$strLink = $_SESSION['userurl'];
		unset($_SESSION['userurl']);
		SwitchToLink($strLink);
    }    
/*	if (isset($_SERVER["HTTP_REFERER"]))
	{
		SwitchToLink($_SERVER["HTTP_REFERER"]);
	}
*/
}


?>
