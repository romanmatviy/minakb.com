<?php
 
/**
 * redis test
 */
class redis_test extends Controller
{
	
	function index()
	{
		$redis = new Redis();
		$redis->connect("/home/leomark/.system/redis.sock");
		if (!$redis->get('int') && !$redis->get('string') && !$redis->get('another_string')) {
		    $redis->set('string', 'variables were set at ' . date('F'));
		    $redis->set('int', date('d'));
		    $redis->set('another_string', date('G') . ":" . date('i'));
		}
		echo $redis->get('string') . " " . $redis->get('int') . ", " . $redis->get('another_string') . "<br>";
		var_dump($redis->get('int'));
		var_dump($redis->get('string'));
		var_dump($redis->get('another_string'));
	}

	public function test()
	{
		var_dump($this->db->redis_test());
	}

	function vps()
	{
		ini_set('max_execution_time', 1800);
		ini_set('max_input_time', 1800);
		
		// $redis = new Redis();
		// $redis->connect('185.65.245.27', '6379');
		// $redis->auth('D7sW2B9f4JaT3');
		 
		$this->toRedis(CACHE_PATH);
		// $this->toRedis(CACHE_PATH . 'shop' . DIRSEP . 'optionsToGroup');
	}

	public function del_ru()
	{
		$this->db->redis_delByKey('shop_ru/optionsToGroup');
		$this->db->redis_delByKey('shop_ru/products');
	}

	public function toRedis($dir)
    {
    	$len = strlen(CACHE_PATH) + 1;
    	if(is_dir($dir))
    	{
    		if ($objs = glob($dir.DIRSEP."*"))
    		{
	           	foreach($objs as $obj) {
	             	if(is_dir($obj))
	             		$this->toRedis($obj);
	             	else
	             	{
	             		$key = substr($obj, $len);
	             		$this->db->redis_set($key, file_get_contents($obj));
	             		echo "$key <br>";
	             	}
	           	}
	        }
    	}
    }
}
 
?>