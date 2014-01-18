## PHP AVOS Cloud Library
===========================
Forked from https://github.com/apotropaic/parse.com-php-library

AVOS Cloud PHP SDK。

## SETUP
=========================

checkout这个项目后，你需要在项目根路径创建一个文件名为`AVConfig.php`作为配置文件。

```
git clone https://github.com/killme2008/avoscloud-php-library.git
cd avoscloud-php-library ; touch AVConfig.php
```

## AVConfig.php示范

填写AVConfig.php配置示范如下：

```
<?php

class AVConfig{

    const APPID = '';
    const MASTERKEY = '';
    const APPKEY = '';
    const AVOSCLOUDURL = 'https://cn.avoscloud.com/1/';
}

?>
```

其中APPID就是应用Id，MasterKey为应用的Master Key，APPKEY为应用Key。这些信息都可以在应用设置的应用key菜单里找到。

你可以通过`php test.php`命令运行单元测试。


## 简单例子
=========================

更多例子参考tests目录下的测试用例，更多文档等待补充。

### sample of upload.php ###

```
<?php
    //上传视频到AVOS Cloud的简单例子。

    $obj = new AVObject('Videos');
    $obj->title = $data['upload_data']['title'];
    $obj->description = $data['upload_data']['description'];
    $obj->tags = $data['upload_data']['tags'];

    //create new geo
    $geo = new AVGeoPoint($data['upload_data']['lat'],$data['upload_data']['lng']);
    $obj->location = $geo->location;

    //use pointer to other class
    $obj->userid = array("__type" => "Pointer", "className" => "_User", "objectId" => $data['upload_data']['userid']);

    //create acl
    $obj->ACL = array("*" => array("write" => true, "read" => true));
    $r = $obj->save();
    ?>
```
