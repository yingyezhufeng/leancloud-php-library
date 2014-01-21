<?php
	include_once 'AV.php';
	$obj = new AVObject('GameScore');
	$obj->score = 1000;
	$obj->name = 'dennis zhuang';
    $save = $obj->save();
	print_r($save);
	$updateObject = new AVObject('GameScore');
	$updateObject->score = 2000;
	$return = $updateObject->update($save->objectId);
	$deleteObject = new AVObject('GameScore');
    //取消注释来删除对象。
    //$return = $deleteObject->delete($save->objectId);
?>