<?php
require_once('/php/email.php');
require_once('/php/account.php');
require_once('/php/stocklink.php');
require_once('_stock.php');
require_once('_editgroupform.php');

function _onAdjust($strSymbols)
{
    $ar = explode('&', $strSymbols);
    
    $ar0 = explode('=', $ar[0]);
    $strSymbol = $ar0[0];
    $fVal = floatval($ar0[1]);
    
    $ar1 = explode('=', $ar[1]);
    $strSymbol2 = $ar1[0];
    $fVal2 = floatval($ar1[1]);
    
    $iCount = count($ar);
    if ($iCount > 2)
    {
        $ar2 = explode('=', $ar[2]);
    }
    
    $fFactor = false;
    if (in_arrayLof($strSymbol) || in_arrayLofHk($strSymbol))
    {
        $fFactor = AdjustLofPriceFactor($strSymbol, $fVal, $fVal2, floatval($ar2[1]));
    }
    else if (in_arrayGoldEtf($strSymbol))
    {
        $fFactor = AdjustEtfPriceFactor($strSymbol, $fVal2, $fVal);
    }
    else if (in_arrayFuture($strSymbol))
    {
        $fFactor = AdjustEtfPriceFactor($strSymbol, $fVal, $fVal2);
        $strSymbol = FutureGetSinaSymbol($strSymbol);
    }
    else    // if (in_arrayPairTrading($strSymbol2))
    {
        $fFactor = AdjustEtfPriceFactor($strSymbol, $fVal, $fVal2);
    }
    
    if ($fFactor !== false)
    {
        SqlInsertStockCalibration(SqlGetStockId($strSymbol), ' ', $ar0[1], $ar1[1], $fFactor, DebugGetTimeDisplay());
    }
}

function _canModifyGroup($strGroupId, $strMemberId)
{
	if (AcctIsAdmin())    return true;
    if ($strMemberId == SqlGetStockGroupMemberId($strGroupId))  return true;    // I am the group onwer
    return false;
}

function _onDelete($strGroupId, $strMemberId)
{
    if (_canModifyGroup($strGroupId, $strMemberId))
    {
        SqlDeleteStockGroup($strGroupId);
    }
}

function _emailStockGroup($strMemberId, $strOperation, $strGroupName, $strSymbols)
{
    $bChinese = true;
    $strSubject = 'Stock Group: '.$strOperation;
	$str = AcctGetMemberLink($strMemberId, $bChinese);

//	$strMemberEmail = SqlGetEmailById($strMemberId);
    $strGroupId = SqlGetStockGroupId($strGroupName, $strMemberId);
    $strGroupLink = SelectGroupInternalLink($strGroupId, $bChinese);
    $str .= '<br />GroupName: '.$strGroupLink; 
    $str .= '<br />Symbols: '.$strSymbols; 
    
    EmailDebug($str, $strSubject); 
}

function _sqlEditStockGroup($strGroupId, $strGroupName, $strSymbols)
{
    if (SqlUpdateStockGroup($strGroupId, $strGroupName) == false)  return false;
    
	$arNew = StockGetIdSymbolArray($strSymbols);
    $arOld = SqlGetStockGroupArray($strGroupId);
    foreach ($arNew as $strStockId => $strSymbol)
	{
	    if (array_key_exists($strStockId, $arOld) == false)
	    {
	        SqlInsertStockGroupItem($strGroupId, $strStockId);
	    }
	}
	
    foreach ($arOld as $strStockId => $strSymbol)
	{
	    if (array_key_exists($strStockId, $arNew) == false)
	    {
            $strId = SqlGetStockGroupItemId($strGroupId, $strStockId);
	        SqlDeleteStockGroupItem($strId);
	    }
	}
	
    return true;
}

function _onEdit($strMemberId, $strGroupId, $strGroupName, $strSymbols)
{
    if (_canModifyGroup($strGroupId, $strMemberId))
    {
        $str = SqlGetStockGroupName($strGroupId);
        if (IsGroupNameReadOnly($str))  $strGroupName = $str;
        if (_sqlEditStockGroup($strGroupId, $strGroupName, $strSymbols))
        {
            _emailStockGroup($strMemberId, $_POST['submit'], $strGroupName, $strSymbols);
        }
    }
}

function _onNew($strMemberId, $strGroupName, $strSymbols)
{
	StockInsertGroup($strMemberId, $strGroupName, $strSymbols);
    _emailStockGroup($strMemberId, $_POST['submit'], $strGroupName, $strSymbols);
}

    $strMemberId = AcctAuth();

	if ($strGroupId = UrlGetQueryValue('delete'))
	{
	    _onDelete($strGroupId, $strMemberId);
	}
	else if (isset($_POST['submit']))
	{
		$strSymbols = FormatCleanString($_POST['symbols']);
		$strGroupName = FormatCleanString($_POST['groupname']);

		$strGroupId = UrlGetQueryValue('edit');
		if ($_POST['submit'] == STOCK_GROUP_EDIT || $_POST['submit'] == STOCK_GROUP_EDIT_CN)
		{	// edit group
		    _onEdit($strMemberId, $strGroupId, $strGroupName, $strSymbols);
		}
		else if ($_POST['submit'] == STOCK_GROUP_ADJUST_CN)
		{
		    _onAdjust($strSymbols); 
		}
		else if ($_POST['submit'] == STOCK_GROUP_NEW || $_POST['submit'] == STOCK_GROUP_NEW_CN)
		{
		    _onNew($strMemberId, $strGroupName, $strSymbols);
		}
		unset($_POST['submit']);
	}

	SwitchToSess();
?>