<?php
function smarty_function_block($params, &$smarty)
{
                    //$smarty->trigger_error("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
		    $blocks=$smarty->get_template_vars('_blocks');
		    $block=$blocks[$params['id']];
		    return $block->show();
}
?>
