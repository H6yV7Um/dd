<?php
/**
 * Created by PhpStorm.
 * User: gedejin(com@baidu.com)
 * Date: 15/9/6
 * Time: 下午5:36
 */

class Global_Video_Upload {
    private static $_instance=null;
    private $_conf=null;
    private $_tracker=null;
    private $_storage=null;
    protected $storage_info=null;

    /**
     * @return Global_Video_Upload|null
     */
    public static function getInstance()
    {
        if(is_null(self::$_instance)){
            self::$_instance=new Global_Video_Upload();
        }
        return self::$_instance;
    }

    /**
     *
     */
    private  function __construct()
    {
        $this->_conf=include(CONFIG_PATH."Fastdfs.Conf.php");
        $this->_tracker=new Global_Fastdfs_Tracker($this->_conf["tracker"]["host"],$this->_conf["tracker"]["port"]);
        $this->storage_info = $this->_tracker->applyStorage($this->_conf["groupName"]);
        Bingo_Log::debug("storage_info :".json_encode($this->storage_info),"dal");
        $this->_storage=new Global_Fastdfs_Storage($this->storage_info['storage_addr'], $this->storage_info['storage_port']);
    }


    /**
     * @param $fileInfo
     * @return array|int
     */
    public function uploadFileByContent($fileInfo)
    {
        $ret=$this->_storage->uploadFileByContent($this->storage_info['storage_index'],$fileInfo['content'],$fileInfo['ext']);

        if($ret === false || empty($ret['path']))
        {
            return Global_ErrorCode_Video::$VIDEO_UPLOAD_TO_VIDEO_SERVICE_ERROR;
        }
        // $ret['url']=$this->_conf['httpHost'] ."/".$this->_conf['groupName']."/" .$ret['path'];
        if($this->_conf['useCdn']==1){
            $ret['url']=$this->_conf['cdnHost'] ."/".$this->_conf['groupName']."/" .$ret['path'];
        }else{
            $ret['url']=$this->_conf['httpHost'] ."/".$this->_conf['groupName']."/" .$ret['path'];
        }
        return $ret;
    }

    /**
     * @param $masterPath
     * @param $fileInfo
     * @return array|int
     */
    public function uploadSlaveFileByName($masterPath,$fileInfo)
    {
        $ret=$this->_storage->uploadSlaveFile($fileInfo['path'],$masterPath,$fileInfo['prefix'],$fileInfo['ext']);
        if($ret === false || empty($ret['path']))
        {
            return Global_ErrorCode_Video::$VIDEO_UPLOAD_TO_VIDEO_SERVICE_ERROR;
        }
        $ret['url']=$this->_conf['httpHost'] ."/".$this->_conf['groupName']."/".$ret['path'];
        return $ret;
    }
}