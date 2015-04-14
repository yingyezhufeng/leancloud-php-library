<?php
include_once 'AV.php';
$query = new leancloud\AVQuery('GameScore');
$query->where('name','dennis zhuang');
$return = $query->find();
print_r($return);
?>
