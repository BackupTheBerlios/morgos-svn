{if $MorgOS_CurUser}
{morgos_side_box EvalBoxTitle="t s='Welcome %1' 1=`$MorgOS_CurUser.Name`" BoxContentFile="user/boxuserform.tpl"}
{else}
{morgos_side_box EvalBoxTitle="t s='Login'" BoxContentFile="user/boxloginform.tpl"}
{/if}
