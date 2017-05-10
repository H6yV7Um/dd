<?php
/**
 * Created by PhpStorm.
 * User: gedejin(com@baidu.com)
 * Date: 15/9/6
 * Time: 下午5:36
 */

class Global_Video_UploadNew {

    private static $_instance=null;

    private $fastDFSClient;
    private $_config;

    /**
     * @return Global_Video_Upload|null
     */
    public static function getInstance()
    {
        if(is_null(self::$_instance)){
            self::$_instance=new Global_Video_UploadNew();
        }
        return self::$_instance;
    }

    public function __construct(){
        $this->_config = include(CONFIG_PATH."Fastdfs.Conf.php");
        $this->fastDFSClient = new Global_Fastdfs_FastDFSClient($this->_config);
    }

    /**
     * @param $fileInfo
     * @return array
     */
    public function uploadFileByContent($fileInfo){
        $filePath = $fileInfo["filePath"];
        $ext = $fileInfo["ext"];
        $res = $this->fastDFSClient->uploadFile($filePath,$ext);
        if($this->_config['useCdn']==1){
            $url=$this->_config['cdnHost'] ."/".$res['group_name']."/" .$res['filename'];
        }else{
            $url=$this->_config['httpHost'] ."/".$res['group_name']."/" .$res['filename'];
        }
        // $url=$this->_config['httpHost'] ."/".$res['group_name']."/" .$res['filename'];
        if(empty($res['filename'])){
            return Global_ErrorCode_Video::$VIDEO_UPLOAD_TO_VIDEO_SERVICE_ERROR;
        }
        return array("url"=>$url);
    }
}