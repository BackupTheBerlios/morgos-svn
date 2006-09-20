{include file="admin/header.tpl"}
	<div id="main">
		<h1>{$MorgOS_CurrentAdminPage->getName()}</h1>
		
		{include file="admin/messages.tpl"}

		<p>{$MorgOS_CurrentAdminPage->getContent()}</p>
	</div>
{include file="admin/footer.tpl"}
