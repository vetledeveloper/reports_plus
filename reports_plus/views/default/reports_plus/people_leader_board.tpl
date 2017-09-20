{title}{$leader_board_title}{/title}
{add_bread_crumb}Leader Board{/add_bread_crumb}
<div id="people_leader_board">
 <table class="common">
    <thead>
      <th class="lb_rank">{lang}Rank{/lang}</th>
      <th class="lb_user">{lang}Name{/lang}</th> 
      <th class="lb_score">{lang}Score{/lang}</th>
    </thead>
    <tbody>
      {if is_foreachable($leader_board_list)}
        {foreach $leader_board_list as $key => $row}
        {assign var=rank value=$key+1}
            <tr>
              <td class="lb_rank_value">{$rank}</td>
              {if $row.hide}
                <td class="lb_user_value"><div class="lb_user_name lb_user_hidden">{lang} -- hidden user --{/lang}</div></td>
              {else}
                <td class="lb_user_value"><img class="lb_user_avatar" src={$row.avatar}><div class="lb_user_name">{$row.label}</div></td>
              {/if}
              <td class="lb_score_value">{$row.score}</td>
            </tr>
        {/foreach}
      {else}
        <tr>
          <td class="lb_rank_value">{lang} --No Data-- {/lang}</td>
        </tr>
      {/if}
    </tbody>
  </table>
  
</div>

