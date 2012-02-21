<?php
abstract class gs_base_module {
	static function add_subdir($data,$dir) {
		$subdir=trim(str_replace(cfg('lib_modules_dir'),'',clean_path($dir).'/'),'/');
		$d=array();
		foreach($data as $k=>$a) {
			foreach($a as $t=>$v) {
				if (strpos($t,'/')===0) {
					$d[$k][trim($t,'/')]=$v;
				} else {
					$d[$k][trim($subdir.'/'.$t,'/')]=$v;
				}
			}
		}
		return $d;
	}
	
	static function admin_auth($data,$params) {
		if (gs_var_storage::load('check_admin_auth')===FALSE) return true;
		gs_var_storage::save('check_admin_auth',FALSE);
		if (strpos($data['gspgid'],'admin')===0) {

			$admin_ip_access=cfg('admin_ip_access');
			if(is_array($admin_ip_access) && $admin_ip_access && !in_array($_SERVER['REMOTE_ADDR'],$admin_ip_access)) {
				$o=new admin_handler($data,array('name'=>'auth_error.html'));
				$o->show();
				return false;
			}
			$rec=gs_session::load('login_gs_admin');
			if (!$rec) {
				$o=new gs_base_handler($data,array('name'=>'admin_login.html'));
				$o->show(array());
				return false;
			}
		}
		gs_var_storage::save('check_admin_auth',TRUE);
		return true;
	}

}
