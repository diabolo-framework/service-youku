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
