<?php
    include_once 'AV.php';
    $obj = new AVObject('GameScore');
    $geo = new AVGeoPoint(30.0, -20.0);
    $obj->location = $geo->location;
    $return = $obj->save();
	$query = new AVQuery('GameScore');
    $query->whereNear('location', (new AVGeoPoint(30.0, -20.0))->location);
	$return = $query->find();
    print_r($return);
?>