<?php
/*

test file

*/
require_once('badWordsFilter.php');

$string = "YOUR STRING";

$res = badWordsFilter::filterBadWords($string, false, true, 5);
echo $res;

?>