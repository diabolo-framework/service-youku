<?php
namespace X\Service\Youku;
use X\Core\Service\XService;
/**
 *
 */
class Service extends XService {
    /** @var array */
    protected $apps = array();
    /** @var string */
    protected $defaultApp = null;
    
    /** @var YoukuApp[] */
    private $appInstances = array();
    
    /**
     * @param unknown $name
     * @return \X\Service\Youku\YoukuApp
     */
    public function getApp( $name ) {
        if ( isset($this->appInstances[$name]) ) {
            return $this->appInstances[$name];
        }
        if ( !isset($this->apps[$name]) ) {
            throw new YoukuException("youku app `{$name}` does not exists");
        }
        
        $app = new YoukuApp($this->apps[$name]);
        $this->appInstances[$name] = $app;
        return $this->appInstances[$name];
    }
    
    /**
     * @throws YoukuException
     * @return \X\Service\Youku\YoukuApp
     */
    public function getDefaultApp() {
        if ( empty($this->apps) ) {
            throw new YoukuException('no youku app configed.');
        }
        
        if ( null === $this->defaultApp ) {
            $appNames = array_keys($this->apps);
            $this->defaultApp = $appNames[0];
        }
        return $this->getApp($this->defaultApp);
    }
}