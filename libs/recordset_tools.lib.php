<?php
class field_interface {
	static function init($arr,$init_opts) {
		$structure =array('fields'=>array(),
				'recordsets'=>array(),
				'htmlforms'=>array(),
				'fkeys'=>array(),
				'indexes'=>array(),
				);
		$ret=array();
		$arr=string_to_params($arr);
		foreach ($arr as $k=>$r) {
			if(!isset($r['required'])) $r['required']='true';
			$r['func_name']=$r[0];
			if (in_array($r['func_name'],array('lMany2Many','lMany2One','lOne2One'))) {
				$r['linked_recordset']=$r[1];
				if (!isset($r['hidden'])) $r['hidden']=!(isset($r['verbose_name']) || isset($r[2])) ;
				if (!isset($r['verbose_name'])) $r['verbose_name']=isset($r[2]) ? $r[2] : $k;
				$r['counter'] = isset($r['counter']) && (!$r['counter'] || strtolower($r['counter'])=='false') ? false : true; // by default: on
				//$r['counter'] = (isset($r['counter']) && $r['counter'] && strtolower($r['counter'])!='false') ? true : false; // by default: off

			} else {
				if (!isset($r['hidden'])) $r['hidden']=!(isset($r['verbose_name']) || isset($r[1])) ;
				if (!isset($r['verbose_name'])) $r['verbose_name']=isset($r[1]) ? $r[1] : $k;
			}
			$ret[$k]=$r;
		}
		foreach ($ret as $k => $r) {
			if (!method_exists('field_interface',$r['func_name']))
				throw new gs_exception("field_interface: no method '".$r['func_name']."'");
			self::$r['func_name']($k,$r,$structure,$init_opts);
			if (isset($r['default']) && !isset($structure['fields'][$k]['default'])) $structure['fields'][$k]['default']=$r['default'];
		}
		return $structure;
	}

	static function fString($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'varchar','options'=>isset($opts['max_length']) ? $opts['max_length'] : 255);
		$structure['htmlforms'][$field]=array(
			'type'=>'input', 
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'], 
			);

		if (strtolower($opts['required'])=='false') {
			$structure['htmlforms'][$field]['validate']='dummyValid';
		} else {
			$structure['htmlforms'][$field]['validate'][]='isLength';
			$structure['htmlforms'][$field]['validate_params']['min']=isset($opts['min_length']) ? (int)($opts['min_length']) : 1;
			$structure['htmlforms'][$field]['validate_params']['max']=isset($opts['max_length']) ? (int)($opts['max_length']) : $structure['fields'][$field]['options'];
			if (isset($opts['validate_regexp'])) {
				$structure['htmlforms'][$field]['validate'][]='isRegexp';
				$structure['htmlforms'][$field]['validate_params']['validate_regexp']=$opts['validate_regexp'];
			}
		}
		if (isset($opts['unique']) && strtolower($opts['unique'])=='true') {
			$structure['htmlforms'][$field]['validate'][]='checkField';
			$structure['htmlforms'][$field]['validate_params']['class']=get_class($this);
			$structure['htmlforms'][$field]['validate_params']['field']=$field;
			//'validate_params'=>array('class'=>'users','field'=>'userLogin','message'=>'Login invalid or occupied'
		}
		if (isset($opts['default'])) {
			$structure['htmlforms'][$field]['default']=$opts['default'];
		}
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	static function fPassword($field,$opts,&$structure,$init_opts) {
		return self::fString($field,$opts,$structure,$init_opts);
	}
	static function fCheckbox($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'int');
		$structure['htmlforms'][$field]=array(
			'type'=>'checkbox',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'isNumber'
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	static function fInt($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'int');
		$structure['htmlforms'][$field]=array(
			'type'=>'input',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'isNumber'
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	
	static function fFloat($field,$opts,&$structure,$init_opts) {
		self::fInt($field,$opts,$structure,$init_opts);
		$structure['fields'][$field]=array('type'=>'float');
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	
	static function fEmail($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'varchar','options'=>isset($opts['max_length']) ? $opts['max_length'] : 255);
		$structure['htmlforms'][$field]=array(
			'type'=>'email',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
		);
		$structure['htmlforms'][$field]['validate'][]=strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty';
		
		if (isset($opts['unique']) && strtolower($opts['unique'])=='true') {
			$structure['htmlforms'][$field]['validate'][]='checkField';
			$structure['htmlforms'][$field]['validate_params']['class']=get_class();
			$structure['htmlforms'][$field]['validate_params']['field']=$field;
			//'validate_params'=>array('class'=>'users','field'=>'userLogin','message'=>'Login invalid or occupied'
		}
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	static function fDateTime($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'date');
		$structure['htmlforms'][$field]=array(
			'type'=>'datetime',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'isDate'
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	static function fText($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'text');
		$structure['htmlforms'][$field]=array(
			'type'=>'text',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty'
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	
	static function fFile($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field.'_filename']=array('type'=>'varchar','options'=>255);
		$structure['fields'][$field.'_data']=array('type'=>'longblob');
		$structure['fields'][$field.'_mimetype']=array('type'=>'varchar','options'=>'16');
		$structure['fields'][$field.'_size']=array('type'=>'bigint');
		$structure['htmlforms'][$field]=array(
			'type'=>'file',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty'
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	
	static function fSelect($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'varchar','options'=>isset($opts['max_length']) ? $opts['max_length'] : 255);
		$structure['htmlforms'][$field]=array(
			'type'=>'Select',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty',
			'options'=>array_combine(explode(',',$opts['values']),explode(',',$opts['values'])),
		);
		$structure['indexes'][$field]=$field;
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	static function f___dummy($field,$opts,&$structure,$init_opts) {
		$structure['fields'][$field]=array('type'=>'varchar','options'=>255);
		$structure['htmlforms'][$field]=array(
			'type'=>'input',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty'
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
	}
	static function lOne2One($field,$opts,&$structure,$init_opts) {
		$fname=$field.'_id';
		$structure['fields'][$fname]=array('type'=>'int');
		if (isset($opts['mode']) && $opts['mode']=='link') {
			$structure['fields'][$fname.'_hash']=array('type'=>'varchar','options'=>16);
			$structure['htmlforms'][$fname.'_hash']=array(
			'type'=>'hidden',
			'validate'=>'dummyValid'
		);
		}
		$structure['htmlforms'][$fname]=array(
			'type'=>'lOne2One',
			'linkname'=>$field,
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty',
			'nulloption'=>(isset($opts['nulloption']) && $opts['nulloption'] && strtolower($opts['nulloption'])!='false') ? true : false ,
		);
		$structure['indexes'][$fname]=$fname;
		$structure['recordsets'][$field]=array(
			'recordset'=>$opts['linked_recordset'],
			'local_field_name'=>$fname,
			'foreign_field_name'=>'id',
			'update_recordset'=>$opts['linked_recordset'],
			);
		$structure['htmlforms'][$fname]['options']=$structure['recordsets'][$field];
		$structure['fkeys'][]=array('link'=>$field,'on_delete'=>'RESTRICT','on_update'=>'CASCADE');
		if (isset($opts['widget'])) $structure['htmlforms'][$fname]['widget']=$opts['widget'];


	}
	static function lMany2One($field,$opts,&$structure,$init_opts) {
		if(isset($init_opts['skip_many2many'])) return;
		list($rname,$linkname)=explode(':',$opts['linked_recordset']);
		$obj=new $rname(array('skip_many2many'=>true));
		$obj_rs=$obj->structure['recordsets'][$linkname];
		$structure['recordsets'][$field]=array(
			'recordset'=>$rname,
			'local_field_name'=>'id',
			'foreign_field_name'=>$obj_rs['local_field_name'],
			'type'=>'many',
			);
		if($opts['counter']) {
			$counter_fieldname='_'.$field.'_count';
			$structure['recordsets'][$field]['counter_fieldname']=$counter_fieldname;
			$structure['recordsets'][$field]['counter_linkname']=$linkname;
			$structure['fields'][$counter_fieldname]=array('type'=>'int','default'=>0);
			$structure['htmlforms'][$counter_fieldname]=array( 'type'=>'fInt', 'hidden'=>'true',);
		}
		$structure['htmlforms'][$field]=array(
			'type'=>'lMany2One',
			'linkname'=>$field,
			'hidden'=>$opts['hidden'],
			'widget'=>isset($opts['widget']) ? $opts['widget'] : '',
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty',
			'nulloption'=>(isset($opts['nulloption']) && $opts['nulloption'] && strtolower($opts['nulloption'])!='false') ? true : false ,
			'options'=>$structure['recordsets'][$field],
		);
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];
		if (isset($opts['widget_params'])) $structure['htmlforms'][$field]['widget_params']=$opts['widget_params'];
	}
	static function lMany2Many($field,$opts,&$structure,$init_opts) {
		@list($rname,$table_name,$foreign_field_name)=explode(':',$opts['linked_recordset']);
		/*
		new gs_rs_links($init_opts['recordset'],$rname,$table_name);	
		нужно переопределить lazy_load в _short чтобы он для rs_links вызывал хитрый конструктор.

		*/
		$structure['htmlforms'][$field]=array(
			'type'=>'lMany2Many',
			'hidden'=>$opts['hidden'],
			'verbose_name'=>$opts['verbose_name'],
			'validate'=>strtolower($opts['required'])=='false' ? 'dummyValid' : 'notEmpty',
		);

		$structure['recordsets'][$field]=array(
			'recordset'=>$table_name,
			'rs1_name'=>$init_opts['recordset'],
			'rs2_name'=>$rname,
			'rs_link'=>false,
			'local_field_name'=>'id',
			'foreign_field_name'=>$foreign_field_name ? $foreign_field_name : $init_opts['recordset'].'_id',
			'type'=>'many',
			);
		$structure['recordsets']['_'.$field]=$structure['recordsets'][$field];
		$structure['recordsets']['_'.$field]['rs_link']=true;

		if ($opts['counter']) {
			$counter_fieldname='_'.$field.'_count';
			$structure['fields'][$counter_fieldname]=array('type'=>'int','default'=>0);
			$structure['htmlforms'][$counter_fieldname]=array( 'type'=>'fInt', 'hidden'=>'true',);
			$structure['recordsets'][$field]['counter_fieldname']=$counter_fieldname;
		}
		$structure['htmlforms'][$field]['options']=$structure['recordsets'][$field];
		if (isset($opts['widget'])) $structure['htmlforms'][$field]['widget']=$opts['widget'];

		//$structure['fkeys'][]=array('link'=>$field,'on_delete'=>'CASCADE','on_update'=>'CASCADE');
	}
	function install()  {

	}
}

class gs_rs_links extends gs_recordset{
        public $id_field_name='id';
		private $links=array();
        public $structure=array(
                'fields'=>array(
                        'id'=>array('type'=>'serial'),
			),
		);

	function __construct($rs1,$rs2,$table_name,$rs_link=false,$link_name='') { 
		$this->table_name=$table_name;
		$this->link_name=$link_name;
		$this->rs1_name=$rs1;
		$this->rs2_name=$rs2;
		$this->rs_link=$rs_link;
		$this->gs_connector_id=$conn_id=key(cfg('gs_connectors'));

		$f1=$rs1.'_id';
		$f2=$rs1!=$rs2 ? $rs2.'_id': 'id2';
		$this->structure['fields'][$f1]=array('type'=>'int');
		$this->structure['fields'][$f2]=array('type'=>'int');

		$this->structure['indexes'][$f1]=$f1;
		$this->structure['indexes'][$f2]=$f2;

		$this->structure['recordsets']['parents']=array('recordset'=>$rs1,'local_field_name'=>$f1,'foreign_field_name'=>'id','update_recordset'=>$rs2,'update_link'=>$link_name);
		$this->structure['recordsets']['childs']=array('recordset'=>$rs2,'local_field_name'=>$f2,'foreign_field_name'=>'id','update_recordset'=>$rs1,'update_link'=>$link_name);
		/*
		*/
		$this->structure['fkeys'][]=array('link'=>'parents','on_delete'=>'CASCADE','on_update'=>'CASCADE');
		$this->structure['fkeys'][]=array('link'=>'childs','on_delete'=>'CASCADE','on_update'=>'CASCADE');
                return parent::__construct($conn_id,$table_name);

	}
	public function find($opts,$linkname=null) {
		return $this->first()->get_recordset()->find($opts,$linkname);
	}
	public function find_records($options=null,$fields=null,$index_field_name=null) {
		parent::find_records($options,$fields,$index_field_name);
		if ($this->rs_link) {
			$this->links=$this->array;
			return $this;
		}
		if (isset($this->parent_record)) {
			$idname=$this->structure['recordsets']['childs']['local_field_name'];
			$ids=array();
			foreach ($this as $t) $ids[]=$t->$idname;
			$rsname=$this->structure['recordsets']['childs']['recordset'];
			$rs=new $rsname();
			$rs=$rs->find_records(array('id'=>$ids));
			$rs->parent_recordset=$this;
			$links=array();
			foreach ($this->array as $l) {
				$links[$l->$idname]=$l;
			}
			$this->links=$links;
			$this->array=$rs->array;
		}
		return $this;
	}
	function implode($d=':') {
		return implode($d,$this->array_keys());
	}
	function array_keys() {
		return array_keys($this->array);
	}
	public function new_record($data=null,$id=NULL) {
		if ($data) {$arr=array($this->structure['recordsets']['parents']['local_field_name']=>$this->parent_record->get_id(),
				$this->structure['recordsets']['childs']['local_field_name']=>$data);
			$nr=parent::new_record($arr);
		} else {
			$nr=parent::new_record($data);
		}
		//throw new gs_exception("field_interface: no method '".$r['func_name']."'");
		$this->links[]=$nr;
		//$this->links[]=4;
		return $nr;
	}
	public function flush($data) {
		$fname=$this->structure['recordsets']['childs']['local_field_name'];
		if (isset($this->links)) foreach ($this->links as $k=>$l) {
			if (!array_key_exists($l->$fname,$data))  {
				$l->delete();
			}
		}
	}
	public function commit() {
		/*
		md('rs_links::commit',1);
		md($this->links,1);
		*/
		foreach ($this->structure['recordsets'] as $l=>$st) {
			$prec=new $st['recordset'];
			$update_link=$st['update_link'];
			if (isset($prec->structure['recordsets'][$update_link])) {
				$counter_fieldname=$prec->structure['recordsets'][$update_link]['counter_fieldname'];
			} else {
				foreach ($prec->structure['recordsets'] as $rs) {
					if ($rs['recordset']==$this->table_name) {
						$counter_fieldname=$rs['counter_fieldname'];
						break;
					}
				}
			}
			$ids=array();
			foreach ($this->links as $a) {
				$ids[]=$a->{$st['local_field_name']};
			}
			$prec->find_records(array($st['foreign_field_name']=>array_unique($ids)));
			$counter_arr=array();
			if (isset($this->links)) foreach ($this->links as $link) {
				$id=$link->{$st['local_field_name']};
				if ($link->recordstate & RECORD_NEW) $counter_arr[$id]=$counter_arr[$id]+1;
				if ($link->recordstate & RECORD_DELETED) $counter_arr[$id]=$counter_arr[$id]-1;
			}
			foreach ($counter_arr as $id=>$cnt) {
				$prec[$id]->$counter_fieldname+=$cnt;
			}
			$prec->commit();
		}
		$ret=parent::commit();
		if (isset($this->links)) foreach ($this->links as $l) $l->commit();

		return $ret;
	}
}
class gs_recordset_short extends gs_recordset {
	function __construct($s=false,$init_opts=false) {
		$this->init_opts=$init_opts;
		$this->init_opts['recordset']=get_class($this);
		if (!$s || !is_array($s)) throw new gs_exception('gs_recordset_short :: empty init values');
		if (!$this->table_name) $this->table_name=get_class($this);
		if (!$this->id_field_name) $this->id_field_name='id';
		if (!$this->gs_connector_id) $this->gs_connector_id=key(cfg('gs_connectors'));
		$this->structure['fields'][$this->id_field_name]=array('type'=>'serial');
		$this->selfinit($s);
		$this->structure['fields']['_ctime']=array('type'=>'date');
		$this->structure['fields']['_mtime']=array('type'=>'date');
		parent::__construct($this->gs_connector_id,$this->table_name);
	}

	function selfinit($arr) {
		$struct=field_interface::init($arr,$this->init_opts);
		foreach ($struct as $k=>$s)
			$this->structure[$k]=isset($this->structure[$k]) ? array_merge($this->structure[$k],$struct[$k]) : $struct[$k];
	}
	function commit() {
		foreach ($this->structure['recordsets'] as $l=>$st) {
			if(isset($st['update_recordset'])) {
				$prec=new $st['update_recordset'];
				foreach ($prec->structure['recordsets'] as $pl=>$pst) {
					if ($pst['counter_linkname']==$l) {
						foreach ($this as $rlink) {
							$old_id=$rlink->get_old_value($st['local_field_name']);
							$new_id=$rlink->{$st['local_field_name']};

							if ($rlink->recordstate & RECORD_NEW) {
									$plink=$prec->get_by_id($new_id);
									$plink->{$pst['counter_fieldname']}++;
									$plink->commit(1);
							} else if ($rlink->recordstate & RECORD_DELETED) {
									$plink=$prec->get_by_id($old_id);
									$plink->{$pst['counter_fieldname']}--;
									$plink->commit(1);
							} else if ($old_id!=$new_id) {
									$plink=$prec->get_by_id($new_id);
									$plink->{$pst['counter_fieldname']}++;
									$plink->commit(1);
									$plink=$prec->get_by_id($old_id);
									$plink->{$pst['counter_fieldname']}--;
									$plink->commit(1);
							}
						}

					}
				}
			}
		}
		$ret=parent::commit();
		return $ret;
	}
	function html_list() {
		return trim($this);
	}
}

?>
