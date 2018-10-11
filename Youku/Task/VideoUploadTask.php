<?php
namespace X\Service\Youku\Task;
use X\Service\Youku\YoukuApp;
use X\Service\Youku\YoukuException;
/**
 * @link https://cloud.youku.com/docs?id=110
 */
class VideoUploadTask {
    /** 电视剧 */
    const CAT_TV = "TV";
    /** 电影 */
    const CAT_MOVIES = "Movies";
    /** 综艺 */
    const CAT_VARIETY = "Variety";
    /** 动漫 */
    const CAT_ANIME = "Anime";
    /** 音乐 */
    const CAT_MUSIC = "Music";
    /** 教育 */
    const CAT_EDUCATION = "Education";
    /** 纪实 */
    const CAT_DOCUMENTARY = "Documentary";
    /** 资讯 */
    const CAT_NEWS = "News";
    /** 娱乐 */
    const CAT_ENTERTAINMENT = "Entertainment";
    /** 体育 */
    const CAT_SPORTS = "Sports";
    /** 汽车 */
    const CAT_AUTOS = "Autos";
    /** 科技 */
    const CAT_TECH = "Tech";
    /** 游戏 */
    const CAT_GAMES = "Games";
    /** 生活 */
    const CAT_LIFESTYLE = "LifeStyle";
    /** 时尚 */
    const CAT_FASHION = "Fashion";
    /** 旅游 */
    const CAT_TRAVEL = "Travel";
    /** 亲子 */
    const CAT_PARENTING = "Parenting";
    /** 搞笑 */
    const CAT_HUMOR = "Humor";
    /** 微电影 */
    const CAT_WDYG = "Wdyg";
    /** 网剧 */
    const CAT_WGJU = "Wgju";
    /** 拍客 */
    const CAT_PKER = "Pker";
    /** 创意视频 */
    const CAT_CHYI = "Chyi";
    /** 自拍 */
    const CAT_ZPAI = "Zpai";
    /** 广告 */
    const CAT_ADS = "Ads";
    /** 其他 */
    const CAT_OTHERS = "Others";
    
    /** 原创 */
    const COPYRIGHT_ORIGINAL = 'original';
    /** 转载 */
    const COPYRIGHT_REPRODUCED = 'reproduced';
    
    /** 公开 */
    const PUBLIC_ALL = 'all';
    /** 仅好友 */
    const PUBLIC_FRIEND = 'friend';
    /** 需要输入密码才能观看 */
    const PUBLIC_PASSWORD = 'password';
    /** 分片大小 */
    const SLICE_LENGTH = 1024;

    /**
     * 标题
     * @var string
     */
    public $title;
    /**
     * 类别
     * @var string
     */
    public $category;
    /**
     * 版权类型
     * @var string
     */
    public $copyrightType;
    /**
     * 公开度
     * @var string
     */
    public $publicType;
    /**
     * 播放密码
     * @var string
     */
    public $watchPassword;
    /**
     * 视频描述
     * @var string
     */
    public $description;
    /**
     * 是否使用web上传方式
     * @var boolean
     * */
    public $isweb;
    /**
     * 是否需要防抖处理
     * @var boolean
     * */
    public $deshake;
    /**
     * 视频标签
     * @var array
     * */
    public $tags = array();
    /**
     * 视频路径
     * @var string
     * */
    public $file;
    /** 
     * 进度回调
     * @var callback 
     * */
    public $progressCallback = null;
    
    /** YoukuApp */
    private $app = null;
    /**
     * 上传token
     * @var string
     */
    private $uploadToken;
    /**
     * 创建的视频id
     * @var string
     */
    private $videoId;
    /**
     * 上传服务器URI
     * @var string
     */
    private $uploadServerUri;
    /**
     * 上传服务器IP
     * @var string
     */
    private $uploadServerIp;
    
    /** @param YoukuApp $app */
    public function __construct( YoukuApp $app ) {
        $this->app = $app;
    }
    
    /**
     * @return string
     */
    public function start( ) {
        $this->createFile();
        $this->uploadStart();
        
        $fitstSlice = $this->createFirstSlice();
        $sliceId = $fitstSlice['slice_task_id'];
        $offset = $fitstSlice['offset'];
        $length = $fitstSlice['length'];
        
        do {
            if ( null !== $this->progressCallback ) {
                call_user_func_array($this->progressCallback, array($this,$offset));
            }
            $nextSlice = $this->uploadSlice($sliceId, $offset, $length);
            
            $uploadServerIp = null;
            if ( $this->isFinished($uploadServerIp) ) {
                $this->commit($uploadServerIp);
                break;
            }
            
            $sliceId = $nextSlice['slice_task_id'];
            $offset = $nextSlice['offset'];
            $length = $nextSlice['length'];
        } while( true );
        return $this->videoId;
    }
    
    /***
     * 创建文件
     */
    private function createFile() {
        $params = array();
        $params['title'] = $this->title;
        $params['tags'] = implode(',', $this->tags);
        $params['category'] = $this->category;
        $params['copyright_type'] = $this->copyrightType;
        $params['public_type'] = $this->publicType;
        $params['watch_password'] = $this->watchPassword;
        $params['description'] = $this->description;
        $params['file_name'] = basename($this->file);
        $params['file_md5'] = md5_file($this->file);
        $params['file_size'] = filesize($this->file);
        $params['isweb'] = $this->isweb;
        $params['deshake'] = $this->deshake;
        $params['client_id'] = $this->app->getAppId();
        $params['access_token'] = $this->app->accessToken;
        $response = $this->app->call('GET', 'uploads/create', $params);
        
        $this->videoId = $response['video_id'];
        $this->uploadToken = $response['upload_token'];
        $this->uploadServerUri = $response['upload_server_uri'];
        $this->uploadServerIp = gethostbyname($this->uploadServerUri);
    }
    
    /**
     * 初始上传信息
     * @throws YoukuException
     */
    private function uploadStart() {
        $params = array();
        $params['upload_token'] = $this->uploadToken;
        $params['file_size'] = filesize($this->file);
        $params['ext'] = pathinfo($this->file, PATHINFO_EXTENSION);
        $params['slice_length'] = self::SLICE_LENGTH;
        $response = $this->call('POST', 'create_file', $params);
    }
    
    /**
     * 创建视频第一个文件块
     * @throws YoukuException
     */
    private function createFirstSlice() {
        $params = array();
        $params['upload_token'] = $this->uploadToken;
        $response = $this->call('GET', 'new_slice', $params);
        return $response;
    }
    
    /**
     * 上传文件块
     * @param unknown $sliceId
     * @param unknown $offset
     * @param unknown $length
     * @throws YoukuException
     */
    private function uploadSlice( $sliceId, $offset, $length ) {
        $videoData = $this->getVideoData($length, $offset);
        
        $params = array();
        $params['upload_token'] = $this->uploadToken;
        $params['slice_task_id'] = $sliceId;
        $params['offset'] = $offset;
        $params['length'] = $length;
        $params['crc'] = dechex(crc32($videoData));
        $params['hash'] = bin2hex(md5($videoData, true));
        
        $url = "http://{$this->uploadServerIp}/gupload/upload_slice?".http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $videoData);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        if ( 0 !== curl_errno($ch) ) {
            throw new YoukuException('failed to call youku api : '.curl_error($ch));
        }
        curl_close($ch);
        
        $response = json_decode($response, true);
        if ( isset($response['error']) ) {
            throw new YoukuException(
                "failed to call youku api `gupload/upload_slice` : {$response['error']['description']}",
                $response['error']['code']
            );
        }
        return $response;
    }
    
    /**
     * 检查文件是否上传完成
     * @param unknown $uploadServerIp
     * @throws YoukuException
     * @return boolean
     */
    private function isFinished( &$uploadServerIp ) {
        $params = array();
        $params['upload_token'] = $this->uploadToken;
        $response = $this->call('GET', 'check', $params);
        $uploadServerIp = $response['upload_server_ip'];
        return $response['finished'];
    }
    
    /**
     * 提交上传文件
     * @param unknown $uploadServerIp
     * @return string
     */
    private function commit( $uploadServerIp ) {
        $params = array();
        $params['access_token'] = $this->app->accessToken;
        $params['client_id'] = $this->app->getAppId();
        $params['upload_token'] = $this->uploadToken;
        $params['upload_server_ip'] = $uploadServerIp;
        $response = $this->app->call('POST', 'uploads/commit', $params);
        return $response['video_id'];
    }
    
    /**
     * @param string $method GET/POST
     * @param string $name the name of api
     * @param array $params params api
     * @throws YoukuException
     * @return array
     */
    private function call( $method, $name, $params=array() ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $url = "http://{$this->uploadServerIp}/gupload/".$name;
        switch ( strtoupper($method) ) {
        case 'GET' :
            if ( !empty($params) ) {
                $url = $url.'?'.http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            break;
        case 'POST' ;
            curl_setopt($ch, CURLOPT_POST,true);
            $params = http_build_query($params);
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
            throw new YoukuException(
                "failed to call youku api `{$name}` : {$response['error']['description']}",
                $response['error']['code']
            );
        }
        return $response;
    }
    
    /**
     * @param unknown $length
     * @param unknown $offset
     * @return string
     */
    private function getVideoData($length, $offset) {
        $file = fopen($this->file, "rb");
        $data = stream_get_contents($file, $length, $offset);
        fclose($file);
        return $data;
    }
}