<?php
/**
 * PHP-FastDFS-Client (FOR FastDFS v4.0.6)
 *
 * 用PHP Socket实现的FastDFS客户端
 *
 * @author: $Author: QPWOEIRU96
 * @version: $Rev: 521 $
 * @date: $Date: 2014-01-17 09:54:45 +0800 (周五, 17 一月 2014) $
 */


class Global_Fastdfs_Tracker extends Global_Fastdfs_Base{

    /**
     * 根据GroupName申请Storage地址
     *
     * @command 104
     * @param string $group_name 组名称
     * @return array/boolean
     */
    public function applyStorage($group_name) {

        $req_header = self::buildHeader(104, Global_Fastdfs_Base::GROUP_NAME_MAX_LEN);

        $req_body   = self::padding($group_name, Global_Fastdfs_Base::GROUP_NAME_MAX_LEN);

        $this->send($req_header . $req_body);

        $res_header = $this->read(Global_Fastdfs_Base::HEADER_LENGTH);
        $res_info   = self::parseHeader($res_header);
        if($res_info['status'] !== 0) {
			$this->_errstr = "something wrong with get storage by group name.";
            $this->_errno = $res_info['status'];
            Bingo_Log::warning($this->_errstr,"dal");
            return FALSE;
        }

        $res_body = !!$res_info['length'] 
            ? $this->read($res_info['length'])
            : '';
        $group_name   = trim(substr($res_body, 0, Global_Fastdfs_Base::GROUP_NAME_MAX_LEN));
        $storage_addr = trim(substr($res_body, Global_Fastdfs_Base::GROUP_NAME_MAX_LEN, 
            Global_Fastdfs_Base::IP_ADDRESS_SIZE - 1));

        list(,,$storage_port)  = unpack('N2', substr($res_body, 
            Global_Fastdfs_Base::GROUP_NAME_MAX_LEN + Global_Fastdfs_Base::IP_ADDRESS_SIZE - 1, 
            Global_Fastdfs_Base::PROTO_PKG_LEN_SIZE));

        $storage_index  = ord(substr($res_body, -1));


        return array(
            'group_name'    => $group_name,
            'storage_addr'  => $storage_addr,
            'storage_port'  => $storage_port,
            'storage_index' => $storage_index
        );


    }

}



