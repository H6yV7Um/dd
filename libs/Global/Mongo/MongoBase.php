<?php

class Global_Mongo_MongoBase {
	
	public  $connected = false; 
	
	private $MyHost = null; 
	
	private $MyHosts = null; 
	
	private $MyUser = null; 
	private $MyPass = null; 
	private $MyDB = null; 
	private $MyTable = null; 
	private $replicaSet = null;
	
	public $m = null; 
	public $db = null; 
	public $lastErr = null;
	
	public function __construct($host, $user, $pass, $database, $replicaSet=null){
		$this->init($host, $user, $pass, $database, $replicaSet);
	}

	public function init($host, $user, $pass, $database, $replicaSet=null) {		
		$this->MyHost = $host;
		$this->MyHosts = explode(",", $this->MyHost);
		$this->MyUser = $user;
		$this->MyPass = $pass;
		$this->MyDB = $database;
		$this->replicaSet = $replicaSet;
		return $this->connect();
	}

	private function connect() {
				
		$flags = array("connectTimeoutMS" => 500);
		if(!empty($this->replicaSet)) {
			$flags['replicaSet'] = $this->replicaSet;
		}

		$success = $this->connectClient();
		
		if(!$success && !empty($this->replicaSet)) {
			$this->replicaSet = "";
			$success = $this->connectClient();
		}
		
		if(!$success && count($this->MyHosts)) { 
			$this->MyHost = array_shift($this->MyHosts);
			$success = $this->connectClient();
		}
		
		if(!$success && count($this->MyHosts)) {
			$this->MyHost = array_shift($this->MyHosts);
			$success = $this->connectClient();
		}

		if(empty($this->m)) {
			return false;
		}
		//try to select the db		
		try {
			$this->db = $this->m->selectDB($this->MyDB); // select a database
			$this->db->setReadPreference(MongoClient::RP_PRIMARY_PREFERRED); //default to primary prefered which is better than default of primary only
		} catch(Exception $e) {
			//It does not appear that Mongo can fail here since it doesn't hit server for database select.
			$this->log_db_error("SELECTDB",$this->MyDB,$e->getMessage(),$e->getCode());
			$this->db = null;
			$this->closeDatabase();
			$this->m = null;
			return false; //could not select the database
		}
		
		$this->connected = true;
		return true;
	}

	private function connectClient() {		
		if(empty($this->MyHost)){
			return false;
		} 
		$flags = array("connectTimeoutMS" => 500);
		if(!empty($this->replicaSet)) {
			$flags['replicaSet'] = $this->replicaSet;
		} 
		try {			
			$this->m = new MongoClient("mongodb://".$this->MyUser.":".$this->MyPass."@".$this->MyHost."/".$this->MyDB, $flags);	
		} catch(MongoConnectionException $e) {
			ini_set("mongo.ping_interval",1); //reduce ping interval in an attempt to jump start the discovery process
			$this->log_db_error("CONNECT",$this->MyDB,$e->getMessage(),$e->getCode());
			$this->closeDatabase();
			$this->m = null;
			return false;
		}
		return true;
	}
	

	public function reconnect($message) {		
		ini_set("mongo.ping_interval",1);
		if(!count($this->MyHosts)) {
			return false; 
		}
		/*$this->replicaSet = "";
		$bad = str_replace(": not master","",$message); 
		$this->MyHost = array_shift($this->MyHosts);
		if($bad==$this->MyHost) {
			if(count($this->MyHosts)) {
				$this->MyHost = array_shift($this->MyHosts);
			}else{
				return false;
			} 
		}*/
		return $this->connect(); 
	}
	
	public function disconnect() {
		$this->m = null;
		$this->db = null;
		$this->connected = false;
	}
	

	public function closeDatabase() {
		if($this->m) $this->m->close(true);
	}
	

	public function replicaRead($useReplica) {

		$success = false;
		if($this->db==null) { 
			return false; 
		}
		if(empty($this->replicaSet)){
			return false; 
		}

		if($useReplica) {
			$success = $this->db->setReadPreference(MongoClient::RP_SECONDARY_PREFERRED);
		} else {
			$success = $this->db->setReadPreference(MongoClient::RP_PRIMARY_PREFERRED);
		}

		return $success;
	}

	public function changeTable($newTable) {

		$this->MyTable = $newTable;	
	}
	
	public function insert($object, $safe=true, $timeout=500) {
				
		if($this->db==null) {
			return null; 
		}
		$collection = $this->db->selectCollection($this->MyTable);
		$flags = array("timeout" => intval($timeout));
		if($safe) { 
			$flags['w'] = 1;
		}else{
			$flags['w'] = 0;
		} 
		
		try {
			$result = $collection->insert($object, $flags);
		} catch(MongoException $e) {
			$this->log_db_error("INSERT1",$this->MyTable,$e->getMessage(),$e->getCode(),$this->serializeQuery($object));
			$success = $this->reconnect($e->getMessage());
			if(!$success) {
				return null;
			}
			return $this->insert($object,$safe,$timeout);
			
		}
		if($safe && $result['ok']!=1) {
			$this->log_db_error("INSERT2",$this->MyTable,$result['err']." ".$result['errmsg'],$result['code'],$this->serializeQuery($object));
			return null;
		}
		return $result;
	}
	
	public function remove($criteria,$safe=true,$timeout=500) {
				
		if($this->db==null) return null; 
		$collection = $this->db->selectCollection($this->MyTable);
		$flags = array("timeout" => intval($timeout));
		if($safe) {
			$flags['w'] = 1;
		}else{
			$flags['w'] = 0;
		}
		try {
			$result = $collection->remove($criteria, $flags); 
		} catch(MongoCursorException $e) {
			$this->log_db_error("REMOVE1",$this->MyTable,$e->getMessage(),$e->getCode(),json_encode($criteria));
			$success = $this->reconnect($e->getMessage());
			if(!$success) { 
				return null;
			}
			return $this->remove($criteria,$safe,$timeout);
		} catch(MongoCursorTimeoutException $e) {
			$this->log_db_error("REMOVE2",$this->MyTable,$e->getMessage(),$e->getCode(),json_encode($criteria));
			return null;
		}
		
		if($safe && $result['ok']!=1) {
			$this->log_db_error("REMOVE3",$this->MyTable,$result['err']." ".$result['errmsg'],$result['code'],json_encode($criteria));
			return null;
		}
				
		if($safe && $result['ok']) return $result['n'];
		return $result;
	}

	public function update($criteria,$newdata,$safe=true,$upsert=false,$timeout=500) {
				
		if($this->db==null) {
			return null; 
		}
		$collection = $this->db->selectCollection($this->MyTable);
		$flags = array("multiple" => true, "timeout" => intval($timeout), "upsert" => $upsert);
		if($safe) $flags['w'] = 1; else $flags['w'] = 0;
		try {
			$result = $collection->update($criteria, $newdata, $flags);
		} catch(MongoException $e) {
			$this->log_db_error("UPDATE1",$this->MyTable,$e->getMessage(),$e->getCode(),$this->serializeQuery(array($criteria,$newdata)));
			$success = $this->reconnect($e->getMessage());
			if(!$success) {
				return null;
			}
			return $this->update($criteria,$newdata,$safe,$upsert,$timeout);
		}
		if($safe && $result['ok']!=1) {
			$this->log_db_error("UPDATE2",$this->MyTable,$result['err']." ".$result['errmsg'],$result['code'],$this->serializeQuery(array($criteria,$newdata)));
			return null;
		}
		if($safe && $result['ok']) return $result['n'];
		return $result;
	}
		

	public function find($query,$fields = "",$sort="",$limit=0,$timeout=500) {
				
		if($this->db==null) return null; 
		$collection = $this->db->selectCollection($this->MyTable);
		$fields = $this->fieldStrToArray($fields);
		try {
			$cursor = $collection->find($query,$fields)->timeout($timeout);
			if(!empty($sort)) $cursor->sort($sort);
			if(!empty($limit)) $cursor->limit($limit);
		} catch(MongoException $e) {
			$this->log_db_error("FIND",$this->MyTable,$e->getMessage(),$e->getCode(),$this->serializeQuery($query));
			return null;
		}
		return $cursor;
	}

	public function count($query, $limit=0) {
		$limit = intval($limit);
		if($this->db==null) return null; 
		$collection = $this->db->selectCollection($this->MyTable);
		try {
			$count = $collection->count($query,$limit);
		} catch(MongoException $e) {
			$this->log_db_error("COUNT",$this->MyTable,$e->getMessage(),$e->getCode(),$this->serializeQuery($query));
			return null;
		}
		return $count;
	}
		

	public function getNext($cursor) {
		if(empty($cursor)) return -1;
		try {
			$row = $cursor->getNext();
		} catch(MongoCursorException $e) {
			$info = $this->serializeQuery($cursor->info());		
			$this->log_db_error("GETNEXT1",$this->MyTable,$e->getMessage(),$e->getCode(),$info);
			return -1;
		} catch(MongoException $e) {
			$info = $this->serializeQuery($cursor->info());
			$this->log_db_error("GETNEXT2",$this->MyTable,$e->getMessage(),$e->getCode(),$info);
			return -1;
		}
		return $row;
	}
	

	public function findOne($query,$fields = "") {
				
		if($this->db==null) return null; 
		
		$collection = $this->db->selectCollection($this->MyTable);
		
		$fields = $this->fieldStrToArray($fields);
		
		try {
			$result = $collection->findOne($query,$fields);
		} catch(MongoException $e) {
			$this->log_db_error("FINDONE",$this->MyTable,$e->getMessage(),$e->getCode(),json_encode($query));
			return null;
		}
				
		return $result;
	}
	

	public function distinct($query) {
				
		if($this->db==null) return null; 
		$fields = $this->fieldStrToArray($fields);
		try {
			$cursor = $this->db->command(array("distinct" => $this->MyTable, "key" => $query));
		
		} catch(MongoException $e) {
			$this->log_db_error("DISTINCT",$this->MyTable,$e->getMessage(),$e->getCode());
			return null;
		}
				
		return $cursor;
	}
 
	public function aggregate($ops) {
				
		if($this->db==null) return null; 
		try {
			$collection = $this->db->selectCollection($this->MyTable);
			$result = $collection->aggregate($ops);
		} catch(MongoException $e) {
			$this->log_db_error("AGGREGATE",$this->MyTable,$e->getMessage(),$e->getCode(),json_encode($ops));
			return null;
		}
				
		return $result;
	}

	public function mapreduce($map,$reduce,$query='') {
				
		if($this->db==null) return null; 
		try {
			$map = new MongoCode($map);
			$reduce = new MongoCode($reduce);
			$command = array(
				"mapreduce" => $this->MyTable, 
				"map" => $map,
				"reduce" => $reduce,
				"out" => array("inline" => 1)
				);
			if(!empty($query)) $command['query'] = $query;
						
			$result = $this->db->command($command); 
	
		} catch(MongoException $e) {
			$this->log_db_error("MAPREDUCE",$this->MyTable,$e->getMessage(),$e->getCode());
			return null;
		}
				
		return $result;
	}

	public function ensureIndex($index,$unique=false) {
				
		if($this->db==null) return null; 
		$collection = $this->db->selectCollection($this->MyTable);
		try {
			$success = $collection->ensureIndex($index, array("background"=>true,"safe"=>true,"unique" => $unique));
		
		} catch(MongoException $e) {
			$this->log_db_error("ensureIndex",$this->MyTable,$e->getMessage(),$e->getCode());
			return false;
		} 
				
		return true;
	}

	private function fieldStrToArray($fields) {
		$out = array();
		
		if(!empty($fields)) {
			$fields = explode(",",$fields);
			foreach($fields as $f=>$k) {
				$k = trim($k);
				if(!empty($k)) $out[$k] = true;
			}
		}
		return $out;
	}
	

	public function serializeQuery($object) {
		try { 
			$serialized = json_encode($object); 
		} catch(Exception $e) { 
			$serialized = serialize($object); 
		} 
		return $serialized;
	}

	private function log_db_error($type,$db,$error,$code,$info='') {
		
		if(stristr($error,"Operation now in progress")) $error = "Timeout ($error)"; 
		$this->lastErr = $code;
		$message = date('r')." MONGO ".Mongo::VERSION." ".$type." ERROR ".$code." \"".$error."\" AT ".$db." ".$info."\n";
		Bingo_Log::warning($message,"dal");
	}
	
}
?>

