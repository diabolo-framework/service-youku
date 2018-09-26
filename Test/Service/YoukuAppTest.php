<?php
namespace X\Service\Youku\Test\Service;
use PHPUnit\Framework\TestCase;
use X\Service\Youku\Service;
use X\Service\Youku\YoukuException;
use X\Service\Youku\Task\VideoUploadTask;
class YoukuAppTest extends TestCase {
    public function test_upload() {
        $app = Service::getService()->getApp('youkutestapp');
        $app->accessToken = '********';
        
        $task = new VideoUploadTask($app);
        $task->title = '测试视频001';
        $task->category = VideoUploadTask::CAT_OTHERS;
        $task->copyrightType = VideoUploadTask::COPYRIGHT_ORIGINAL;
        $task->publicType = VideoUploadTask::PUBLIC_ALL;
        $task->description = '这是测试视频';
        $task->tags = array('测试');
        $task->file = __DIR__.'/../Resource/big_buck_bunny.mp4';
        $task->start();
    }
    
    public function test_app() {
        $app = Service::getService()->getApp('youkutestapp');
        $videoInfo = $app->getVideoInfo('XMzE2ODg0MDAw');
        $this->assertEquals('第001话 你们这群混蛋!!就这样还能叫"银魂"吗! 前篇', $videoInfo['title']);
        
        try {
            $videoInfo = $app->getVideoInfo('XXXXXXDFDFF');
            $this->fail("no exception throwed on getting non-exists video info.");
        } catch ( YoukuException $e ) {}
        
        $app->accessToken = '********';
        $listInfo = $app->getMyVideos();
        $this->assertEquals($listInfo['total'], count($listInfo['videos']));
        
        $newToken = $app->refreshAccessToken('********');
        $this->assertArrayHasKey('access_token', $newToken);
        $this->assertArrayHasKey('expires_in', $newToken);
        $this->assertArrayHasKey('refresh_token', $newToken);
        $this->assertArrayHasKey('token_type', $newToken);
    }
}