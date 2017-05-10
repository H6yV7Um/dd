<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 15/5/6
 * Time: 下午1:44
 */



class Global_Image_CommonImageService {

    const APPID   = 1;
    const APPKEY  = "86f5mn/oz7kTvR4ACg3ncVVVEwcd6SrTCYUh6IpnX7xCKwiOB80";
    const TIMEOUT = 10;


    /**
     * $imgName 图片名称
     * $imgContent 图片内容
     * $env 国家
     * $slStr 宽高列表  w_h_.self_prefix
     * $quality 裁剪质量
     * $fit 剪切位置
     *  0：压缩时从中间剪切
     *  1：压缩时从上方剪切
     *  2：压缩时从下方剪切
     *  3：压缩时从左方剪切
     *  4：压缩时从右方剪切
     *  5：压缩时从左上剪切
     *  6：压缩时从右上剪切
     *  7：压缩时从坐下剪切
     *  8：压缩时从右下剪切
     *  9：压缩时改变图片宽高比例
     *  10：压缩时不剪切，考虑兼容完整图片
     *  11：压缩时不剪切，用单一颜色填充空白
     * $saveori 是否保留原图 true 保留 false 不保留
     * return 剪切后图片地址
     */
    /**
     * @param $imgName
     * @param $imgContent
     * @param $env
     * @param $slStr
     * @param $quality
     * @param int $fit
     * @param bool $saveori
     * @return array
     */
    public static function uploadPostImage($imgName,$imgContent,$env, $slStr, $quality, $fit=0, $saveori=false) {
        $start = time();
        $imageServer = Image_Config::$image_server['up'][$env];
        $arrParam = array(
            "appid"  => self::APPID,
            "appkey" => self::APPKEY,
            "filename" => $imgName,
            "rename" => $imgName,
            "resize"  => $slStr,
            "content"=> base64_encode($imgContent),
            "totype"=> "jpg",
            "usecnd"=>0,
        );


        $ch = curl_init();
        $options = array(
            CURLOPT_URL           => $imageServer,
            CURLOPT_HEADER        => false,
            CURLOPT_TIMEOUT       => self::TIMEOUT,
            CURLOPT_FOLLOWLOCATION=> true,
            CURLOPT_RETURNTRANSFER=> true,
            CURLOPT_POST          => 1,
            CURLOPT_POSTFIELDS    => $arrParam,
        );

        if(defined("DEBUG") && DEBUG === true){
            $host = array("HOST:imgoff.hiclub.jp");
            $options[CURLOPT_HTTPHEADER] = $host;
        }
        curl_setopt_array($ch, $options);
        $curlInfo = curl_getinfo($ch);
        $data = curl_exec($ch);
        curl_close($ch);
        $dataArr = json_decode($data, true);
        $end = time();
        $uploadLog = "use :".($end - $start)."  service return:".$data;

        if ($dataArr['status']=="ok") {
            Bingo_Log::notice("[uploadPostImage]$uploadLog","img_dal");
            $picUrl = !empty($dataArr['url'])?$dataArr['url']:"";
            if(!empty($picUrl)){
                return array(
                    "result"=>1,
                    "picUrl"=>$picUrl["original"],
                    "picUrl_middle"=>$picUrl["middle"],
                    "picUrl_small"=>$picUrl["small"],
                    "picUrl_post_ori"=>$picUrl["post_ori"],
                    "picUrl_all"=>$picUrl,
                );
            }
            Bingo_Log::warning("[uploadPostImage]$options","img_dal");
            return array(
                "result"=>0,
                "picUrl"=>$dataArr,
            );
        }
        else {
            Bingo_Log::warning("[uploadPostImage]THE image curl error {imgurl}, error:".$imageServer.$data.var_export($curlInfo,true),"img_dal");
            return array(
                "result"=>0,
                "picUrl"=>$dataArr,
            );
        }
    }


    /**
     * @param $imgUrl
     * @param $width
     * @param $height
     * @param $env
     * @param $quality
     * @param int $fit
     * @param bool $saveori
     * @return array
     */
    public static function uploadPostImageWithCrop($imgUrl,$width,$height,$env, $quality, $fit=0, $saveori=false) {
        $start = time();
        $imageServer = Image_Config::$image_server['crop'][$env];
        $arrParam = array(
            "appid"  => self::APPID,
            "appkey" => self::APPKEY,
            "imgurl" => $imgUrl,
            "width" => $width,
            "height"  => $height,
            "saveori"  => $saveori,
            "fit"  => $fit,
            "quality"  => $quality,
            "imgcont" => base64_encode(file_get_contents($imgUrl)),
        );


        $ch = curl_init();
        $options = array(
            CURLOPT_URL           => $imageServer,
            CURLOPT_HEADER        => false,
            CURLOPT_TIMEOUT       => self::TIMEOUT,
            CURLOPT_FOLLOWLOCATION=> true,
            CURLOPT_RETURNTRANSFER=> true,
            CURLOPT_POST          => 1,
            CURLOPT_POSTFIELDS    => $arrParam,
        );

        if(DEBUG){
            $host = array("HOST:imgoff.hiclub.jp");
            $options[CURLOPT_HTTPHEADER] = $host;
        }
        curl_setopt_array($ch, $options);
//        $curlInfo = curl_getinfo($ch);
        $data = curl_exec($ch);
        curl_close($ch);
        $dataArr = json_decode($data, true);
        $end = time();
        $uploadLog = "use :".($end - $start)."  service return:".$data;

        if ($dataArr['status']==="ok") {
            Bingo_Log::notice("[uploadPostImageWithCrop]$uploadLog","img_dal");
            $picUrl = !empty($dataArr['url'])?$dataArr['url']:"";
            if(!empty($picUrl)){
                return array(
                    "result"=>1,
                    "picUrl"=>$picUrl,
                );
            }
            return array(
                "result"=>0,
                "content"=>$dataArr,
            );
        }
        else {
//            Bingo_Log::notice("[uploadPostImageWithCrop]THE image curl error params:".json_encode($arrParam),"img_dal");
            Bingo_Log::notice("[uploadPostImageWithCrop]THE image curl ({$imageServer}) error result:".json_encode($dataArr),"img_dal");
            return array(
                "result"=>0,
                "content"=>$dataArr,
            );
        }
    }

}
