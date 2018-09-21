<?php
namespace X\Service\Youku\Test\Service;
use PHPUnit\Framework\TestCase;
use X\Service\Youku\Service;
class YoukuAppTest extends TestCase {
    public function test_app() {
        $app = Service::getService()->getApp('youkutestapp');
        $videoInfo = $app->getVideoInfo('XMzE2ODg0MDAw');
        $this->assertEquals('第001话 你们这群混蛋!!就这样还能叫"银魂"吗! 前篇', $videoInfo['title']);
        
        $app->accessToken = '*** YOUR ACCESS TOKEN ***';
        $listInfo = $app->getMyVideos();
        $this->assertEquals(2, $listInfo['total']);
        
        $newToken = $app->refreshAccessToken('*** YOUR REFRESH TOKEN ***');
        $this->assertArrayHasKey('access_token', $newToken);
        $this->assertArrayHasKey('expires_in', $newToken);
        $this->assertArrayHasKey('refresh_token', $newToken);
        $this->assertArrayHasKey('token_type', $newToken);
    }
}