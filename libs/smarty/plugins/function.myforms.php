<?php

function smarty_function_myforms($params, &$smarty2) {
	//md($params['item']->structure,1);
	$smarty= isset($params['clone']) ? clone($smarty2) : $smarty2;
	if (is_object($params['item']) && is_subclass_of ($params['item'],'gs_recordset')) {
		$rs=$params['item']; 
		$obj=$rs->first();
	} else {
		if (!is_string($params['item']) && ( !is_object($params['item']) || get_class($params['item'])!='gs_record' ) ) return;
		$rs=is_string($params['item']) ? new $params['item'] : $params['item']->get_recordset();
		$obj=is_object($params['item']) && get_class($params['item'])=='gs_record' ? $params['item'] : $rs->new_record();
	}

	if (isset($params['prepare_options']) && method_exists($rs,'prepare_myforms')) {
		$rs->prepare_myforms($params['prepare_options']);
	}

	$forms=explode(',',$params['forms']);
	$formname=array_shift($forms);

	$smarty->assign('_formname',$formname);
	$smarty->assign('_classname',get_class($rs));
	$smarty->assign('_id_field_name',$rs->id_field_name);
	$smarty->assign('_htmlforms',$rs->structure['htmlforms']);
	if (count($forms)) $smarty->assign('_add_forms',count($forms) ? $forms : false);

	$type=isset($params['type']) ? $params['type']: 'all';
	$smarty->assign('_formtype',$type);
	$type.='.';
	$fields=array();

	foreach($rs->structure['myforms'] as $k=>$v) if (strpos($k,$type)===0) $fields[str_replace($type,'',$k)]=$v['fields'];

	$smarty->assign('_fields',$fields);
	$smarty->assign('_titles',$rs->structure['myforms']['titles']);
	
	$smarty->assign('_item',$obj);
	$smarty->assign('_template',$params['template']);
	$smarty->assign('_prefix',$params['prefix']);
	$smarty->assign('_add_string',$params['add_string']);
	$smarty->assign('_nobuttons',$params['nobuttons'] && 1);

	switch ($params['template']) {
		default:
			$ret=$smarty->fetch('myforms/'.(string)$params['template'].'.html');
			break;
		case 'table':
			$ret=$smarty->fetch('myforms/table.html');
		break;
	}
	return $ret;
}

?>