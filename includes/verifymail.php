<?php
/************************************************************************ 
  * This function checks the format of an email address. There are five levels of 
  * checking: 
  * 
  * 1 - Basic format checking. Ensures that: 
  *     There is an @ sign with something on the left and something on the right 
  *     To the right of the @ sign, there's at least one dot, with something to the left and right. 
  *     To the right of the last dot is either 2 or 3 letters, or the special case "arpa" 
  * 2 - The above, plus the letters to the right of the last dot are: 
  *     com, net, org, edu, mil, gov, int, arpa or one of the two-letter country codes 
  * 3 - The above, plus attempts to check if there is an MX (Mail eXchange) record for the 
  *     domain name. 
  * 4 - The above, plus attempt to connect to the mail server 
  * 5 - The above, plus check to see if there is a response from the mail server. The third 
  *     argument to this function is optional, and sets the number of times to loop while 
  *     waiting for a response from the mail server. The default is 15000. The actual waiting 
  *     time, of course, depends on such things as the speed of your server. 
  * 
  * Level 1 is bulletproof: if the address fails this level, it's bad. Level 2 is still 
  * pretty solid, but less certain: there could be valid TLDs overlooked when writing 
  * this function, or new ones could be added. Level 3 is even less certain: there are 
  * a number of things that could prevent finding an MX record for a valid address 
  * at any given time. 4 and 5 are even less certain still. Ultimately, the only absolutely 
  * positive way to test an email address is to send something to it. 
  * 
  * The function returns 0 for a valid address, or the level at which it failed, for an 
  * invalid address. 
  * 
  ************************************************************************/ 

  function MailVal($Addr, $Level, $Timeout = 15000) { 

//  Valid Top-Level Domains 
    $gTLDs = "com:net:org:edu:gov:mil:int:arpa:"; 
    $CCs   = "ad:ae:af:ag:ai:al:am:an:ao:aq:ar:as:at:au:aw:az:ba:bb:bd:be:bf:". 
             "bg:bh:bi:bj:bm:bn:bo:br:bs:bt:bv:bw:by:bz:ca:cc:cf:cd:cg:ch:ci:". 
             "ck:cl:cm:cn:co:cr:cs:cu:cv:cx:cy:cz:de:dj:dk:dm:do:dz:ec:ee:eg:". 
             "eh:er:eu:es:et:fi:fj:fk:fm:fo:fr:fx:ga:gb:gd:ge:gf:gh:gi:gl:gm:". 
             "gn:gp:gq:gr:gs:gt:gu:gw:gy:hk:hm:hn:hr:ht:hu:id:ie:il:in:io:iq:". 
             "ir:is:it:jm:jo:jp:ke:kg:kh:ki:km:kn:kp:kr:kw:ky:kz:la:lb:lc:li:". 
             "lk:lr:ls:lt:lu:lv:ly:ma:mc:md:mg:mh:mk:ml:mm:mn:mo:mp:mq:mr:ms:". 
             "mt:mu:mv:mw:mx:my:mz:na:nc:ne:nf:ng:ni:nl:no:np:nr:nt:nu:nz:om:". 
             "pa:pe:pf:pg:ph:pk:pl:pm:pn:pr:pt:pw:py:qa:re:ro:ru:rw:sa:sb:sc:". 
             "sd:se:sg:sh:si:sj:sk:sl:sm:sn:so:sr:st:su:sv:sy:sz:tc:td:tf:tg:". 
             "th:tj:tk:tm:tn:to:tp:tr:tt:tv:tw:tz:ua:ug:uk:um:us:uy:uz:va:vc:". 
             "ve:vg:vi:vn:vu:wf:ws:ye:yt:yu:za:zm:zr:zw:"; 

//  The countries can have their own 'TLDs', e.g. mydomain.com.au 
    $cTLDs = "com:net:org:edu:gov:mil:co:ne:or:ed:go:mi:"; 

    $fail = 0; 

//  Shift the address to lowercase to simplify checking 
    $Addr = strtolower($Addr); 

//  Split the Address into user and domain parts 
    $UD = explode("@", $Addr); 
    if (sizeof($UD) != 2 || !$UD[0]) $fail = 1; 

//  Split the domain part into its Levels 
    $Levels = explode(".", $UD[1]); $sLevels = sizeof($Levels); 
    if ($sLevels < 2) $fail = 1; 

//  Get the TLD, strip off trailing ] } ) > and check the length 
    $tld = $Levels[$sLevels-1]; 
    $tld = ereg_replace("[>)}]$|]$", "", $tld); 
    if (strlen($tld) < 2 || strlen($tld) > 3 && $tld != "arpa") $fail = 1; 

    $Level--; 

//  If the string after the last dot isn't in the generic TLDs or country codes, it's invalid. 
    if ($Level && !$fail) { 
    $Level--; 
    if (!ereg($tld.":", $gTLDs) && !ereg($tld.":", $CCs)) $fail = 2; 
    } 

//  If it's a country code, check for a country TLD; add on the domain name. 
    if ($Level && !$fail) { 
    $cd = $sLevels - 2; $domain = $Levels[$cd].".".$tld; 
    if (ereg($Levels[$cd].":", $cTLDs)) { $cd--; $domain = $Levels[$cd].".".$domain; } 
    } 

//  See if there's an MX record for the domain 
    if ($Level && !$fail) { 
    $Level--; 
    if (!getmxrr($domain, $mxhosts, $weight)) $fail = 3; 
    } 

//  Attempt to connect to port 25 on an MX host 
    if ($Level && !$fail) { 
    $Level--; 
    while (!$sh && list($nul, $mxhost) = each($mxhosts)) 
      $sh = fsockopen($mxhost, 25); 
    if (!$sh) $fail = 4; 
    } 

//  See if anyone answers 
    if ($Level && !$fail) { 
    $Level--; 
    set_socket_blocking($sh, false); 
    $out = ""; $t = 0; 
    while ($t++ < $Timeout && !$out) 
      $out = fgets($sh, 256); 
    if (!ereg("^220", $out)) $fail = 5; 
    } 

    if (isset($sh)) fclose($sh); 

    return $fail; 
  } //MailVal
?> 
