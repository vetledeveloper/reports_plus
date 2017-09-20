<?php

/**
 * reports_plus module on_after_init event handler
 *
 * @package activeCollab.modules.hide_people
 * @subpackage handlers
 */

/**
 * Handle on after init
 *
 * @param void
 * @return null
 */
function reports_plus_handle_on_shutdown() {

	$path_info = AngieApplication::getRequestPathInfo ();

			$smarty = SmartyForAngie::getInstance();
		    $var = $smarty->getVariable('request');
		    /**@var $request Request*/
		    $request = $var->value;
		    if (!($request instanceof Request)){
		        return;
		    }
		    // $allowed_routers_types = array('people', 'people_company', 'people_company_user');
		    if (!$request->isAsyncCall() && Authentication::getLoggedUser() != null) {
		    	$reports = new ReportsPlus();
		      	$result = $reports->getLeaderBoardData(Authentication::getLoggedUser());
		      	echo '<script type="text/javascript">';
	            echo '   var reports_plus_logged_user_rank = ' . JSON::encode($result['logged_user_rank']) . ';';
				echo '</script>';
		    }
}

// reports_plus_handle_on_after_shutdown
?>