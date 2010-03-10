<?php
DEFINE ('GS_DATA_STDIN','stdin');
DEFINE ('GS_DATA_POST','post'); // operator
DEFINE ('GS_DATA_GET','get'); // operator
DEFINE ('GS_DATA_SEF','sef'); // Search Engines Frendly URI
DEFINE ('GS_DATA_SESSION','session');
DEFINE ('GS_DATA_COOKIE','cookie');
DEFINE ('GS_NULL_XML',"<null></null>");

class gs_null extends SimpleXMLElement implements arrayaccess {
	public function __get($name) {
		return $this;
	}
	public function offsetGet($offset) {
		return $this;
	}
	    public function offsetSet($offset, $value) {
	    }
	    public function offsetExists($offset) {
	    }
	    public function offsetUnset($offset) {
	    }
}
class gs_data {
	
	static private $data;
	private $data_drivers;
	
	public function __construct()
	{
		$this->data_drivers=array(
			GS_DATA_COOKIE,
			GS_DATA_SESSION,
			GS_DATA_GET,
			GS_DATA_SEF,
			GS_DATA_POST,
			GS_DATA_STDIN,
		);
		$this->data=array('gspgid'=>'','gspgtype'=>'');
		$config=gs_config::get_instance();
		foreach ($this->data_drivers as $key => $class_name)
		{
			load_file($config->lib_data_drivers_dir.$class_name.'.lib.php');
			$s_name='gs_data_driver_'.$class_name;
			$c=new $s_name;
			if ($c->test_type())
			{
				$this->data=array_merge($this->data,$c->import());
			}
		}
		//md($this->data);
	}
	

	public function get_data()
	{
		return $this->data;
	}
}

interface gs_data_driver {

	function test_type();
	
	function import();
}

interface gs_module {
	function install();
	static function get_handlers();
	//static function register();
}


class gs_iterator implements Iterator, arrayaccess {
    public $array = array();  


    function add_element(&$element) {
	    return $this->array[]=$element;
    }

    function add($elements) {
	    if (!is_array($elements)) {
		    $elements=array($elements);
	    }
	$this->array=array_merge($this->array,$elements);
    }
    function replace($elements) {
	    $this->reset();
	    $this->array=(array)$elements;
    }
    function reset() {
	    $this->array=array();
	    $this->rewind();
    }

    function rewind() {
	    reset($this->array);
    }
    function first() {
	    $this->rewind();
	    return $this->current();
    }

    function current() {
	    return current($this->array);
    }

    function key() {
        return key($this->array);
    }

    function next() {
	    return next($this->array);
    }

    function count() {
	    return count($this->array);
    }

    function valid() {
        return current($this->array);
    }
    public function offsetSet($offset, $value) {
        $this->array[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : new gs_null(GS_NULL_XML);
    }
}


class gs_cacher {
	static function save($data,$subdir='.',$id=NULL) {
		$dirname=cfg('cache_dir').'/'.$subdir.'/';
		check_and_create_dir($dirname);
		if (!$id) {
			$fn=tempnam($dirname,'');
			$id=basename($fn);
			//$id.=substr(base64_encode(md5(rand())),0,8);
		} else {
			//$id=substr($id,0,-8);
			$fn=$dirname.$id;
		}
		file_put_contents($fn,serialize($data));
		return $id;
	}
	static function load($id,$subdir='.') {
		//$id=substr($id,0,-8);
		$dirname=cfg('cache_dir').'/'.$subdir.'/'.$id;
		if (!file_exists($dirname)) return NULL;
		$ret=unserialize(file_get_contents($dirname));
		//unlink($dirname);
		return $ret;
	}
	static function clear($id,$subdir='.') {
		//$id=substr($id,0,-8);
		$dirname=cfg('cache_dir').'/'.$subdir.'/'.$id;
		return file_exists($dirname) && unlink($dirname);
	}
	static function cleardir($subdir=false) {
		if (!$subdir) return false;
		$dirname=cfg('cache_dir').'/'.$subdir;
		foreach (glob("$dirname/*") as $filename) {
			unlink($filename);
		}
		return file_exists($dirname) && is_dir($dirname) && rmdir($dirname);
	}

}

class gs_session {
	static function save($obj,$name='') {
		$data=array();
		if (isset($_COOKIE['gs_session'])) {
			$data=gs_cacher::load($_COOKIE['gs_session'],'gs_session');
		}
		$data[$name]=$obj;
		$id=gs_cacher::save($data,'gs_session',isset($_COOKIE['gs_session']) ? $_COOKIE['gs_session'] : NULL);
		$t=strtotime("now +".cfg('session_lifetime'));
		return setcookie('gs_session',$id,$t,cfg('www_dir'));
	}

	static function load($name=NULL) {
		if (!isset($_COOKIE['gs_session'])) return FALSE;
		$ret=gs_cacher::load($_COOKIE['gs_session'],'gs_session');
		return $name!==NULL  ? $ret[$name] : $ret;
		//return isset($ret[$name]) ? $ret[$name] : $ret;
	}

	static function clear($name=NULL) {
		if (!isset($_COOKIE['gs_session'])) return FALSE;
		return gs_cacher::clear($_COOKIE['gs_session'],'gs_session');
		//return isset($ret[$name]) ? $ret[$name] : $ret;
	}

}






?>
