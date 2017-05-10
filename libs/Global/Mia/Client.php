<?php 

/**
 * client class libary of Mia
 * @author wangjiaong@baidu.com
 */

class Global_Mia_Client{

	public $socket;
	public $_errno;
    public $_errstr;

	/**
	 * construct function
	 * @param
	 * @return
	 */
	public function __construct($ip, $port, $timeout){
		try{
			$this->sock = @fsockopen(
				$ip,
            	$port,
            	$this->_errno, 
            	$this->_errstr,
            	$timeout
            );
		}
		catch(exception $e){
    		Bingo_Log::warning("exception occured: ".$e->getMessage(),'dal');
    		return false;
    	}
    	if( !is_resource($this->sock) ){
        	Bingo_Log::warning($this->_errstr,"dal");
            return null;
        }
        return $this->sock;
	}

	/**
	 * pack the data according to the protocle
	 * @param
	 * @return
	 */
	public function pack($appName, $costomerKey, $costomerSecrete, $videoUrl, 
		$cmdno, $params = array(), $call_back = null){
		$data = pack(
			'N1A256A256A256A256A256A1024', 
    		$cmdno,
    		$appName,
    		$costomerKey,
    		$costomerSecrete,
    		$videoUrl,
    		$call_back,
    		json_encode($params)
    	);
		$data = $data.'^';
		return $data;
	}

	/**
	 * send sock data
	 * @param
	 * @return
	 */
	public function send($data){
 		$length = strlen($data);
 		if(empty($length)){
 			Bingo_Log::warning("illegal pack data!", $data);
 			return false;
 		}
 		try{
 			$num = fwrite($this->sock, $data, $length);
 		}
 		catch(exception $e){
 			Bingo_Log::warning("exception".$e->getMessage(), $data);
 		}
 		if($num != $length){
 			Bingo_Log::warning($this->_errstr, 'dal');
 		}
 		return $num;
	}

	/**
	 * read data from socket
	 * @param
	 * @return
	 */
	public function read($len = 2){
		try{
			$data = stream_get_contents($this->sock, $len);
		}
		catch(exception $e){
			Bingo_Log::warning($e->getMessage(), 'dal');
		}
		if($data != 'ok'){
			return false;
		}
		return true;
	}

	/**
	 * destruct function 
	 * @param
	 * @return
	 */
	public function __destruct(){
		if(is_resource($this->sock)){
			fclose($this->sock);
		}
	}

}