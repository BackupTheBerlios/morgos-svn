<div class="linkbar">
  <ul class="menu">
    <li class="greenbegin"></li>
    {foreach from=$MorgOS_Admin_RootMenu item='menuItem' name='menu'}
      <li class="green"></li>
      <li class="link"> 
        <a href="{$menuItem.Link|xhtml}">{$menuItem.Title}</a>
        {if $menuItem.Childs}
          <ul>
            {foreach from=$menuItem.Childs item='childItem'}
              <li>
                <a href="{$childItem.Link|xhtml}">{$childItem.Title}</a>
              </li>
            {/foreach}
          </ul>
        {/if}
      </li>
    {/foreach}
  </ul>
</div>