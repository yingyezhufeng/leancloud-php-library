## PHP AVOS Cloud Library
===========================
Forked from

## SETUP
=========================

**Instructions** after cloning this repository you have to create a file in the root of it called **parseConfig.php**

## sample of AVConfig.php ###

Below is what you want parseConfig.php to look like, just fill in your IDs and KEYs to get started.

```
<?php

class parseConfig{

	const APPID = '';
	const MASTERKEY = '';
	const RESTKEY = '';
	const PARSEURL = 'https://api.parse.com/1/';
}

?>

```



EXAMPLE
=========================

### sample of upload.php ###

```
<?php
    //This example is a sample video upload stored in parse

    $parse = new parseObject('Videos');
    $parse->title = $data['upload_data']['title'];
    $parse->description = $data['upload_data']['description'];
    $parse->tags = $data['upload_data']['tags'];

    //create new geo
    $geo = new parseGeoPoint($data['upload_data']['lat'],$data['upload_data']['lng']);
    $parse->location = $geo->location;

    //use pointer to other class
    $parse->userid = array("__type" => "Pointer", "className" => "_User", "objectId" => $data['upload_data']['userid']);

    //create acl
    $parse->ACL = array("*" => array("write" => true, "read" => true));
    $r = $parse->save();
    ?>
```
