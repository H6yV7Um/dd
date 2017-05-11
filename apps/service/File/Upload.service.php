<?php

/**
 * Class File_Upload_Service
 */
class File_Upload_Service extends Global_Service_Base {
    const FILE_STRING = '1234567890abcdefghijklmnopqrestuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    /**
     * @return File_Upload_Service
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * 上传文件
     * @param $fileArr
     * @return string
     * @throws Exception
     */
    public function uploadImg($fileArr) {
        if(!$fileArr['name']) {
            throw new \Exception('file name is empty', Global_ErrorCode_Common::FILE_UPLOAD_FILE_NAME_ERROR);
        }
        if($fileArr['error'] != 0) {
            throw new \Exception('receive file failed', Global_ErrorCode_Common::FILE_UPLOAD_RECEIVE_FILED_ERROR);
        }

        $fileName    = $fileArr['name'];
        $fileNameArr = explode('.', $fileName);
        $fileExt     = array_pop($fileNameArr);
        if(!in_array($fileExt, ['jpg', 'gif', 'png'])) {
            throw new \Exception('file is not an img', Global_ErrorCode_Common::FILE_UPLOAD_FILE_IS_NOT_IMG);
        }

        $newFileName = substr(str_shuffle(self::FILE_STRING), 0, 6) . substr(time(), 0, 8) . "." . $fileExt;
        $uploadPath  = FILE_PATH . "upload/";
        if(!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0777, true);
        }
        $res = move_uploaded_file($fileArr['tmp_name'] , $uploadPath . $newFileName);

        if(!$res) {
            throw new \Exception('move file failed', Global_ErrorCode_Common::FILE_UPLOAD_MOVE_FILE_FAILED);
        }

        return "upload/" . $newFileName;
    }
}