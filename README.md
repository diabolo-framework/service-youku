# service-youku
a diabolo service to get youku video information.

# configuration

```
'services' => array(
    'Youku' => array(
        'class' => \X\Service\Youku\Service::class,
        'enable' => true,
        'delay' => true,
        'params' => array(
            'apps' => array(
                'youkutestapp' => array(
                    'appid' => '*** YOUR APP ID ***',
                ),
            )
        ),
    ),
),
```

# basic usage
```
use X\Service\Youku\Service;

$app = Service::getService()->getApp('youkutestapp');

# get video information by video id
$videoInfo = $app->getVideoInfo('XMzE2ODg0MDAw');
$this->assertEquals('第001话 你们这群混蛋!!就这样还能叫"银魂"吗! 前篇', $videoInfo['title']);
        
$app->accessToken = '*** YOUR ACCESS TOKEN ***';
# get uploaded video list
$listInfo = $app->getMyVideos();

# refresh token
$newToken = $app->refreshAccessToken('*** YOUR REFRESH TOKEN ***');    

# upload video
$task = new VideoUploadTask($app);
$task->title = '测试视频001';
$task->category = VideoUploadTask::CAT_OTHERS;
$task->copyrightType = VideoUploadTask::COPYRIGHT_ORIGINAL;
$task->publicType = VideoUploadTask::PUBLIC_ALL;
$task->description = '这是测试视频';
$task->tags = array('测试');
$task->file = __DIR__.'/../Resource/big_buck_bunny.mp4';
$task->start();
```

# command usage
```
# get video information 
$ php index.php service/youku/video/info XMzgzNzYwMDk5Ng==
id : XMzgzNzYwMDk5Ng==
title : 杨洋 上海杜莎夫人蜡像馆 蜡像量身
link : http://v.youku.com/v_show/id_XMzgzNzYwMDk5Ng==.html
bigThumbnail : 87.00
category : 旅游
state : normal
created : 2018-09-25 22:15:29
published : 2018-09-26 03:00:37
description : 杨洋 上海杜莎夫人蜡像馆 蜡像量身 Yang Yang Madame Tussauds Body Measurements
player : http://player.youku.com/player.php/sid/XMzgzNzYwMDk5Ng==/partnerid/d7965f53d2a3e9ea/v.swf
public_type : all
copyright_type : reproduced
user id : 818042065
user name : 酸核桃_com
user link : http://i.youku.com/i/UMzI3MjE2ODI2MA==
tags : 上海杜莎夫人蜡像馆
view_count : 34
operation_limit : 
streamtypes : 3gphd,flvhd,hd,hd2

```
