<?php

require_once('db.inc.php');

if (array_key_exists('typename',$_GET))
{
$bpid=$_GET['typename'];
$sql='select typename,typeid from invTypes where lower(typename)=lower(?)';
$stmt = $dbh->prepare($sql);
$stmt->execute(array($bpid));

if ($row = $stmt->fetchObject())
{
$itemname=$row->typename;
$itemid=$row->typeid;
}
else
{
$itemname="bad item";
$itemid=0;
}

$format='json';

if (array_key_exists('format',$_GET))
{
    if ($_GET['format'] == 'xml')
    {
        $format='xml';
    }
}


if (is_numeric($itemid))
{

if ($format == 'xml')
{
echo "<?xml version='1.0' encoding='UTF-8'?>
<eveapi version=\"2\">
<result>
<rowset name=\"typeids\" key=\"typeID\" columns=\"typeName,TypeID\">
<row typeName=\"".$itemname."\" typeID=\"".$itemid."\" />
</rowset>
</result>
</eveapi>";
}

if ($format == 'json')
{
echo "{\"typeID\": ".$itemid.",\"typeName\": \"".$itemname."\"}";
}


}


}
?>
