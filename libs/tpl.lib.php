<?php

final class gs_tpl {
	
	protected function init()
	{
		$config=gs_config::get_instance();
		load_file($config->lib_tpl_dir.'Smarty.class.php');
		$tpl=new Smarty;
		$tpl->template_dir=$config->tpl_data_dir;
		$tpl->compile_dir=$config->tpl_var_dir;
		$tpl->assign('base_dir',$config->www_dir);
		$tpl->assign('http_host',$config->host);
		return $tpl;
	}
	
	function &get_instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$loader=new gs_tpl();
			$instance = $loader->init();
		}
		return $instance;
	}
}

?>
