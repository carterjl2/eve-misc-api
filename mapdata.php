<?php
header('Access-Control-Allow-Origin: *');

require_once('db.inc.php');


$sql='select itemid,itemname,typename,mapDenormalize.typeid,solarsystemname,mapDenormalize.solarsystemid,mapDenormalize.constellationid,mapConstellations.constellationid,mapDenormalize.regionid,mapRegions.regionname,orbitid,mapDenormalize.x,mapDenormalize.y,mapDenormalize.z,concat_ws("|", itemid,itemname,typename,mapDenormalize.typeid,solarSystemName,mapDenormalize.solarsystemid,mapDenormalize.constellationid,mapConstellations.constellationid,mapDenormalize.regionID,mapRegions.regionname,orbitid,mapDenormalize.x,mapDenormalize.y,mapDenormalize.z) complete from mapDenormalize join invTypes on (mapDenormalize.typeid=invTypes.typeid) join mapSolarSystems on (mapSolarSystems.solarsystemid=mapDenormalize.solarsystemid) join mapRegions on (mapDenormalize.regionid=mapRegions.regionid) join  mapConstellations on (mapDenormalize.constellationid=mapConstellations.constellationid)';


$condition=array();
$binds=array();

if (array_key_exists('itemid', $_GET)) {
    $itemid=$_GET['itemid'];
    $condition[]="itemid=:itemid";
    $binds[":itemid"]=$itemid;
}

if (array_key_exists('groupid', $_GET)) {
    $groupid=$_GET['groupid'];
    $condition[]="mapDenormalize.groupid=:groupid";
    $binds[":groupid"]=$groupid;
}

if (array_key_exists('solarsystemid', $_GET)) {
    $solarsystemid=$_GET['solarsystemid'];
    $condition[]="mapDenormalize.solarsystemid=:solarsystemid";
    $binds[":solarsystemid"]=$solarsystemid;
}


if (array_key_exists('typeid', $_GET)) {
    $typeid=$_GET['typeid'];
    $condition[]="mapDenormalize.typeid=:typeid";
    $binds[":typeid"]=$typeid;
}



if (array_key_exists('regionid', $_GET)) {
    $regionid=$_GET['regionid'];
    $condition[]="mapDenormalize.regionid=:regionid";
    $binds[":regionid"]=$regionid;
}



if (array_key_exists('constellationid', $_GET)) {
    $constellationid=$_GET['constellationid'];
    $condition[]="mapDenormalize.constellationid=:constellationid";
    $binds[":constellationid"]=$constellationid;
}


$joincondition="";
if ($condition) {
    $joincondition=" where ";
    $joincondition.=join(" and ", $condition);
}
$limitcondition="limit 250";

$stmt = $dbh->prepare($sql." ".$joincondition." ".$limitcondition);
$stmt->execute($binds);

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$format='json';
$accept='';
if (isset($_SERVER['HTTP_ACCEPT'])) {
    $accept=$_SERVER['HTTP_ACCEPT'];
}
if (preg_match('#application/json#', $accept)) {
    $format="json";
}
if (preg_match('#text/xml#', $accept)|preg_match('#application/xml#', $accept)) {
    $format="xml";
}
if (preg_match('#text/csv#', $accept)) {
    $format="csv";
}

if (array_key_exists('format', $_GET) && $_GET['format'] == 'xml') {
    $format="xml";
}
if (array_key_exists('format', $_GET) && $_GET['format'] == 'json') {
    $format="json";
}
if (array_key_exists('format', $_GET) and $_GET['format'] == 'csv') {
    $format="csv";
}

if ($format=='xml') {
    header('Content-Type: text/xml; charset=UTF-8');
    $xml=new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><eveapi/>");
    array_to_xml($result, $xml);
    print $xml->asXML();
} elseif ($format=="csv") {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-disposition: inline;mapdata.csv');
    print array_to_csv($result);
} else {
    header('Content-Type: application/json; charset=UTF-8');
    print json_encode($result);
}






// function defination to convert array to xml
function array_to_xml($arrayin, &$xml_arrayout)
{
    foreach ($arrayin as $key => $value) {
        if (is_array($value)) {
            if (!is_numeric($key)) {
                $subnode = $xml_arrayout->addChild("$key");
                array_to_xml($value, $subnode);
            } else {
                $subnode = $xml_arrayout->addChild("row");
                array_to_xml($value, $subnode);
            }
        } else {
            $xml_arrayout->addChild("$key", "$value");
        }
    }
}

function array_to_csv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"')
{
    if (!is_array($array) or !is_array($array[0])) {
        return false;
    }
    
    //Header row.
    if ($header_row) {
        foreach ($array[0] as $key => $val) {
            //Escaping quotes.
            $key = str_replace($qut, "$qut$qut", $key);
            $output .= "$col_sep$qut$key$qut";
        }
        $output = substr($output, 1)."\n";
    }
    //Data rows.
    foreach ($array as $key => $val) {
        $tmp = '';
        foreach ($val as $cell_key => $cell_val) {
            //Escaping quotes.
            $cell_val = str_replace($qut, "$qut$qut", $cell_val);
            $tmp .= "$col_sep$qut$cell_val$qut";
        }
        $output .= substr($tmp, 1).$row_sep;
    }
    
    return $output;
}
