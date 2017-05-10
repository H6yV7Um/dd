<?php

/**
 * Created by PhpStorm.
 * User: gedejin(com@baidu.com)
 * Date: 15/9/6
 * Time: 下午5:36
 */

class Global_Fastdfs_FastDFSClient
{
    // Tracker实例
    protected $_tracker;
    // Storage列表
    protected $_groupList;
    //storage实例
    protected $_storage;
    // 配置变量
    protected $_config;

    /**
     * 构造：初始化配置，连接Tracker
     * @param $config
     */
    public function __construct($config)
    {
        $this->_config = $config;
        $this->_tracker = fastdfs_connect_server($this->_config["tracker"]["host"],$this->_config["tracker"]["port"]);
        $this->_groupList = fastdfs_tracker_list_groups(null,$this->_tracker);
        $this->_storage = $this->chooseGroupByMaxFreeSpace();
    }

    /**
     * 析构：释放连接
     */
    public function __destruct()
    {
        fastdfs_disconnect_server($this->_tracker);
        fastdfs_disconnect_server($this->_storage);
    }

    /**
     * @return bool
     */
    public function checkTrackerStatus(){
        if(!fastdfs_active_test($this->_tracker)){
            //fastdfs测试连接失败
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function chooseGroupByMaxFreeSpace(){
        if(is_array($this->_groupList)){
            foreach($this->_groupList as $groupName => $groupInfo){
                $gFreeSpace[$groupInfo["free_space"]] = $groupName;
            }
            krsort($gFreeSpace);
            reset($gFreeSpace);
            $group = current($gFreeSpace);
            return fastdfs_tracker_query_storage_store($group,$this->_tracker);
        }
        return false;
    }

    /**
     * @param $filePath
     * @param $ext
     * @return mixed
     */
    public function uploadFile($filePath,$ext){
        $uploadFileInfo = @fastdfs_storage_upload_by_filename($filePath,$ext,array(),null,$this->_tracker,$this->_storage);
        return $uploadFileInfo;
    }

}
