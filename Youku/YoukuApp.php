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
        return $this->call('POST', 'oauth2/token', $params);
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
        return $this->call('GET', 'videos/by_me', $params);
    }
    
    /**
     * 统计当前账号下上传的视频数量
     * @return integer
     */
    public function getMyVideoCount($state=null) {
        $states = array();
        if ( null !== $state ) {
            $states[] = $state;
        }
        $totalCount = $this->getMyVideos(1,1,'published',$states);
        return $totalCount['total'];
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
        return $this->call('GET', 'videos/show', $params);
    }
    
    /**
     * @param string $method GET/POST
     * @param string $name the name of api
     * @param array $params params api
     * @throws YoukuException
     * @return array
     */
    public function call( $method, $name, $params=array() ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $url = 'https://api.youku.com/'.$name.'.json';
        switch ( strtoupper($method) ) {
        case 'GET' :
            if ( !empty($params) ) {
                $url = $url.'?'.http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
        case 'POST' ;
            curl_setopt($ch, CURLOPT_POST,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
        default : 
            throw new YoukuException('failed to call youku api with method `'.$method,'`');
        }
        
        $response = curl_exec($ch);
        if ( 0 !== curl_errno($ch) ) {
            throw new YoukuException('failed to call youku api : '.curl_error($ch));
        }
        curl_close($ch);
        
        $response = json_decode($response, true);
        if ( isset($response['error']) ) {
            $message = array(
                "failed to call youku api `{$name}` : {$response['error']['description']}",
                "URL : {$url}",
                "Method : {$method}",
                "Params : ".http_build_query($params),
            );
            throw new YoukuException(implode("\n", $message),$response['error']['code']);
        }
        return $response;
    }
}