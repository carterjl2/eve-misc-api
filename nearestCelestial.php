<?php

require_once('db.inc.php');

$sql="select (pow(:x-x,2)+pow(:y-y,2)+pow(:z-z,2)) distance,itemName,itemID,typeID 
      from mapDenormalize 
      where solarsystemid=:solarsystemid
      order by distance asc 
      limit 1";


if (isset($_GET['x']) and is_numeric($_GET['x'])
    and isset($_GET['y']) and is_numeric($_GET['y'])
    and isset($_GET['z']) and is_numeric($_GET['z'])
    and isset($_GET['solarsystemid']) and is_numeric($_GET['solarsystemid'])) {
    $x=$_GET['x'];
    $y=$_GET['y'];
    $z=$_GET['z'];
    $solarsystemid=$_GET['solarsystemid'];
} else {
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}



$stmt = $dbh->prepare($sql);
$stmt->execute(array(":x"=>$x,":y"=>$y,":z"=>$z,":solarsystemid"=>$solarsystemid));


if ($row = $stmt->fetchObject()) {
    $itemname=$row->itemName;
    $typeid=(int)$row->typeID;
    $itemid=(int)$row->itemID;
    $distance=sqrt($row->distance);
} else {
    $itemname="bad item";
    $itemid=0;
}

$data=array("itemName"=>$itemname,
            "typeid"=>$typeid,
            "itemid"=>$itemid,
            "distance"=>$distance);
echo json_encode($data);
