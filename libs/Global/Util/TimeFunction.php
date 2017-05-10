<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @todo 用户等级升级相关
 * @file TimeFunction.php
 * @author wangjialong@baidu.com
 * @date 2015年7月17日 下午13:58:54
 * 许多模块需要复用的公共小方法，比如取自然周的时间起始时间
 *  
 **/
class Global_Util_TimeFunction{

	private static $weekDayMap = array(
        "Monday"    => 1,
        "Tuesday"   => 2,
        "Wednesday" => 3,
        "Thursday"  => 4,
        "Friday"    => 5,
        "Saturday"  => 6,
        "Sunday"    => 7,
    );

	 /**
     * 计算得到周榜前缀
     * @param
     * @return
     */
    public static function getWeekRankSuffix(){
        $nowTime  = Global_Env::timeAdjust(time());
        $weekDay  = date("l",$nowTime);    //当前时间为周几
        $weekDay  = self::$weekDayMap[$weekDay];
        //换算周一日期
        $monday   = date("Ymd",$nowTime-((int)$weekDay-1)*24*3600);
        //换算周天日期
        $sunday   = date("Ymd",$nowTime+(7-(int)$weekDay)*24*3600);
        $key = sprintf("%s_%s",$monday,$sunday);
        return $key;
    }

     /**
     * 格式化时间
     * @param
     * @return
     */
    public static  function formatDate($sTime) {
    
        $nTime      =   Global_Env::timeAdjust(time());//当前时间
        $dTime      =   $nTime - $sTime;
        if( $dTime < 60 ){
            $dTime =  $dTime."s ago";
        }elseif( $dTime < 3600 ){
            $dTime =  floor($dTime/60)."m ago";
        }elseif($dTime<3600*24){
            $dTime =  floor($dTime/(60*60))."h ago";
        }elseif($dTime< 60 * 60 * 24 * 3){
            $dTime = floor($dTime/(60*60*24))."d ago";
        }elseif($dTime>60*60*24*365){
            $dTime = floor($dTime/60*60*24*365)."y ago";
        }else{
            $dTime = date('M-d',$sTime);
        }
        return $dTime;
    }
	
}
/* End of file Score.php 
 */
