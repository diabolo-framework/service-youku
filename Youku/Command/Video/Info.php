<?php
namespace X\Service\Youku\Command\Video;
use X\Service\Youku\Service;
use X\Service\Youku\YoukuException;
class Info {
    /**
     * get video infomation.
     * @param $1 id of video file
     */
    public function run( $args=array() ) {
        if ( !isset($args['params'][0]) ){
            throw new \Exception('video id is required.' );
        }
        
        $videoId = $args['params'][0];
        
        $app = Service::getService()->getDefaultApp();
        try {
            $videoInfo = $app->getVideoInfo($videoId);
        } catch ( YoukuException $e ) {
            echo "Error : ".$e->getMessage()."\n";
            return;
        }
        
        echo "id : {$videoInfo['id']}\n";
        echo "title : {$videoInfo['title']}\n";
        echo "link : {$videoInfo['link']}\n";
        echo "bigThumbnail : {$videoInfo['duration']}\n";
        echo "category : {$videoInfo['category']}\n";
        echo "state : {$videoInfo['state']}\n";
        echo "created : {$videoInfo['created']}\n";
        echo "published : {$videoInfo['published']}\n";
        echo "description : {$videoInfo['description']}\n";
        echo "player : {$videoInfo['player']}\n";
        echo "public_type : {$videoInfo['public_type']}\n";
        echo "copyright_type : {$videoInfo['copyright_type']}\n";
        echo "user id : {$videoInfo['user']['id']}\n";
        echo "user name : {$videoInfo['user']['name']}\n";
        echo "user link : {$videoInfo['user']['link']}\n";
        echo "tags : {$videoInfo['tags']}\n";
        echo "view_count : {$videoInfo['view_count']}\n";
        
        $videoInfo['operation_limit'] = implode(',', $videoInfo['operation_limit']);
        echo "operation_limit : {$videoInfo['operation_limit']}\n";
        
        $videoInfo['streamtypes'] = implode(',', $videoInfo['streamtypes']);
        echo "streamtypes : {$videoInfo['streamtypes']}\n";
    }
}