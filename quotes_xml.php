<?php
$csv = array_map(function($v){return str_getcsv($v, "|");}, file('quotes.csv'));

$hdrs = array_shift($csv);

$count = 0;

$xml = '<?xml version="1.0" encoding="UTF-8"?>';

$xml .= '<quotes>';

foreach($csv as $k=>$v) {
    $xml .= '<record id="' . ++$count .'">';
    foreach($hdrs as $h=>$i) {
        $xml .= '<' . $i . '>' . htmlspecialchars($v[$h], ENT_XML1, 'UTF-8') . '</' . $i . '>';
    }
    $xml .= '</record>';
}

$xml .= '</quotes>';

header ("Content-Type:text/xml");
echo $xml;
?>