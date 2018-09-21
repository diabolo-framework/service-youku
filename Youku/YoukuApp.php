<?php
namespace X\Service\Youku;
class YoukuApp {
    /** 发布时间 */
    const ORDER_BY_PUBLISHED = 'published';
    /** 总播放数 */
    const ORDER_BY_VIEW_COUNT = 'view-count';
    /** 总评论数 */
    const ORDER_BY_COMMENT_COUNT = 'comment-count';
    /** 总收藏数 */
    const ORDER_BY_FAVORITE_COUNT = 'favorite-count';
    /** 已发布 */
    const STATE_PUBLISHED = 'published';
    /** 上传中 */
    const STATE_UPLOADING = 'uploading';
    /** 转码中 */
    const STATE_ENCODING = 'encoding';
    /** 转码失败 */
    const STATE_FAIL = 'fail';
    /** 审核中 */
    const STATE_CHECKING = 'checking';
    /** 已屏蔽 */
    const STATE_BLOCKED = 'blocked';
    
    /** @var string */
    protected $appid = null;
    /** @var string  */
    public $accessToken = null;
    
    /**
     * @param string $option
     */
    public function __construct( $option ) {
        $this->appid = $option['appid'];
    }
    
    /** @return string */
    public function getAppId() {
        return $this->appid;
    }
    
    /**
     * @param unknown $refreshToken
     * @link http://cloud.youku.com/docs?id=104
     */
    public function refreshAccessToken( $refreshToken ) {
        $params = array(
            'client_id' => $this->appid,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        );
        $url = 'https://api.youku.com/oauth2/token.json';
        $response = $this->httpRequestJson('post', $url, $params);
        if ( isset($response['error']) ) {
            throw new YoukuException('failed to list videos : '.$response['error']['description']);
        }
        return $response;
    }
    
    /**
     * @param int $page
     * @param int $size
     * @param string $order
     * @param array $state
     * @link http://cloud.youku.com/docs?id=48
     */
    public function getMyVideos( $page=1, $size=20, $order='published', $state=array() ) {
        $params = array(
            'client_id' => $this->appid,
            'access_token' => $this->accessToken,
            'orderby' => $order,
            'page' => $page,
            'count' => $size,
            'state' => implode(',', $state),
        );
        $url = 'https://api.youku.com/videos/by_me.json?'.http_build_query($params);
        $response = $this->httpRequestJson('GET', $url);
        if ( isset($response['error']) ) {
            throw new YoukuException('failed to list videos : '.$response['error']['description']);
        }
        return $response;
    }
    
    /**
     * @param string $id
     * @param array $attrs
     * @link http://cloud.youku.com/docs?id=46
     */
    public function getVideoInfo( $id, $attrs=null ) {
        $params = array(
            'client_id' => $this->appid,
            'video_id' => $id,
            'ext' => (null===$attrs) ? null : implode(',', $attrs),
        );
        $url = 'https://api.youku.com/videos/show.json?'.http_build_query($params);
        $response = $this->httpRequestJson('GET', $url);
        if ( isset($response['error']) ) {
            throw new YoukuException('failed to get youku video info : '.$response['error']['description']);
        }
        return $response;
    }
    
    /**
     * @param unknown $method
     * @param unknown $url
     * @param array $params
     * @return array
     */
    private function httpRequestJson( $method, $url, $params=array() ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ( 'post' === $method ) {
            curl_setopt($ch, CURLOPT_POST,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $response = curl_exec($ch);
        if ( 0 !== curl_errno($ch) ) {
            throw new YoukuException('failed to call youku api : '.curl_error($ch));
        }
        
        curl_close($ch);
        $response = json_decode($response, true);
        return $response;
    }
}