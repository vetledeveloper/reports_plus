<?php

/**
   * Select multiple roles helper implementation
   *
   * @package custom.modules.quick_links
   * @subpackage helpers
   */


  function smarty_function_select_company_multiple($params, &$smarty) {
    $params['multiple'] = true;
    
    $value = array_var($params, 'value', null, true);
    $optional = array_var($params, 'optional', false, true);

    if($optional) {
    	if(is_array($value) && in_array(-1, $value)){
        	$option_attributes = array('selected' => true);
      	} else {
        	$option_attributes = null;
      	}
      	$options = array(
        option_tag(lang('All'), -1, $option_attributes));
    } else {
      $options = array();
    } 
    // $roles = Users::getAvailableUserClasses(); // This will return array() of roles 
    $companies = Companies::getIdNameMap(); // This will return array() of Companies 
    if(is_foreachable($companies)) {
      foreach($companies as $id => $name) {
            if(is_array($value) && in_array($id, $value)){
              $option_attributes = array('selected' => true);
            } else {
              $option_attributes = null;
            }
            $options[] = option_tag($name, $id, $option_attributes);
      } 
    } // if
    
    return select_box($options, $params);
  } // smarty_function_select_role_multiple