<?php 
require('php/_lof.php');

function EchoLofRelated($ref)
{
	$strGroup = GetLofLinks($ref);
	$strQqq = GetQqqSoftwareLinks();
	$strCompany = GetEFundSoftwareLinks();
	
	$strOfficial = GetEFundOfficialLink($ref->GetDigitA());
	
	echo <<< END
	<p><b>注意XLV和SZ161126跟踪的指数可能不同, 此处估算结果仅供参考.</b></p>
    <p>
    	$strOfficial
    </p>
	<p> $strGroup
		$strQqq
		$strCompany
	</p>
END;
}

require('/php/ui/_dispcn.php');
?>
