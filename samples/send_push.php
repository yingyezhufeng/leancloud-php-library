<?php
include_once 'AV.php';
$push =new AVPush;
$push->alert = 'Hello from AVOS Cloud';
$push->channels = array('foo', 'bar');
$return = $push->send();
print_r($return);
?>