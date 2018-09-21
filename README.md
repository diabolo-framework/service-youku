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
```
