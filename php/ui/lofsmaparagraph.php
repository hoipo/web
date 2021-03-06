<?php
require_once('smaparagraph.php');

function _callbackLofSma($lof_ref, $strEst = false)
{
	return $strEst ? strval($lof_ref->GetLofValue($strEst, $lof_ref->strCNY)) : $lof_ref->stock_ref;
}

function EchoLofSmaParagraph($lof_ref, $callback2 = false)
{
	$ref = $lof_ref->GetEstRef();
    if (RefHasData($ref) == false) 	return;
    
    EchoSmaParagraph($ref, false, $lof_ref, '_callbackLofSma', $callback2);
}

?>
