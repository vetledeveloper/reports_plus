var reports_plus_logged_user_rank ;

var render_user_score = function(update){
    if($('.profile_menu_popup_avatar_wrapper').get(0)){
      
        var score_wrapper = $('.profile_menu_popup_avatar_wrapper');
        var score_values = update || (reports_plus_logged_user_rank || null);
        var score_html = '<div class="rank_information"><span class="profile_score_error_msg">' + App.lang('No score updates <br> for you') + '  </span></div>';
        var can_access_leader_board = App.Config.get('can_access_leader_board');
        if(can_access_leader_board){
          $('.profile_menu_popup').addClass('reports_profile_menu_popup');
          if(score_values && score_values.score !== null ){
            //var score_html = '<div class="rank_information"><span class="profile_score_value">' + score_values.rank + '</span><span class="profile_score_label">' + App.lang("Rank") + '</span><span class="profile_score_label_subs"> (' + App.lang('out of') + ' ' + score_values.total_users + ')</span></div><div class="score_information"><span class="profile_score_value">' + score_values.score + '</span><span class="profile_score_label">' + App.lang("Score") + '</span></div>';
            var score_html = '<a href="' + App.Config.get('leader_board_url') + '"><div class="rank_information">' +
            					'<span class="profile_score_label">' + App.lang("You're Ranked") + '</span><br />' +
            				   '<span class="profile_score_value"><b>' + score_values.rank + '</b></span><br />' + 
            				   '</div>' +
            				   '<div class="score_information">' +
            				   '<span class="profile_score_label">' + App.lang("Score") + ': </span>' +
            				   '<span class="profile_score_value">' + score_values.score + '</span>' +
            				   '</div></a>';
          }
        }
        if(!$(score_wrapper).find('.profile_score_container').get(0)){
            $(score_wrapper).append('<div class="profile_score_container">' + score_html + '</div>');
        }else{
            $(score_wrapper).find('.profile_score_container').html(score_html);
        }
    }
}

var check_for_menu_popup = function(){
    var reports_enable_leader_board = App.Config.get('reports_enable_leader_board');
    if(reports_enable_leader_board){
      if($('.profile_menu_popup_avatar_wrapper').get(0)){
        $('.profile_menu_popup').addClass('reports_profile_menu_popup');
      	get_score_updates();
      }else{
        setTimeout(check_for_menu_popup, 100);
      }
    }
}

var get_score_updates = function () {
  var can_access_leader_board = App.Config.get('can_access_leader_board');
  if(can_access_leader_board){
    //var score_update_url = App.Config.data.url_base + '?path_info=reports_plus/score-update';
    var score_update_url = App.Config.get('user_score_update_url');
    $.ajax({
            'url' : score_update_url,
            'type' : 'get', 
            'success' : function(response) {
                  reports_plus_logged_user_rank = response;
                  render_user_score(response);
            }, 
            'error' : function() {
              reports_plus_logged_user_rank = null;
              // App.Wireframe.Flash.error('No Data Found');
            }
    });
  }else{
    render_user_score(null);
  }
}

$(document).ajaxStop(function() {
	// Remove all previously added click event handlers
	$('#menu_item_profile a').off('click.reports_plus');
	$('#menu_item_profile a').on('click.reports_plus', check_for_menu_popup);
});


$(document).ajaxComplete(function(event, XMLHttpRequest, ajaxOptions) {
	var ajax_url = ajaxOptions.url;
  var reports_enable_leader_board = App.Config.get('reports_enable_leader_board');

	if(ajax_url.toLowerCase().indexOf("refresh-menu") >= 0 && reports_enable_leader_board) {
		render_user_score();
  }
});

App.Wireframe.Events.bind('content_updated single_content_updated', function(){
  var can_access_leader_board = App.Config.get('can_access_leader_board');
  var reports_enable_leader_board = App.Config.get('reports_enable_leader_board');
  if(reports_enable_leader_board && can_access_leader_board){
    if($('#menu_item_people').hasClass('current')){
          if( (!($("#page_action_leader_board").get(0) || $('.quick_view_wrapper').get(0)) )) {
            var leader_board_url = App.Config.data.url_base + '?path_info=reports_plus/leader-board';
            App.Wireframe.PageTitle.addAction('leader_board', {
                  'url' : leader_board_url,
                  'text' : ' ' + App.lang('Leader Board'),
                  'icon' : App.Wireframe.Utils.assetUrl('icon/leader_board_12.png', 'reports_plus', 'images', 'default')
            }, 'leader_board');
          }
    }
  }

});
