{**
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License version 3.0
* that is bundled with this package in the file LICENSE.md
* It is also available through the world-wide-web at this URL:
* https://opensource.org/license/osl-3-0-php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@qloapps.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to https://store.webkul.com/customisation-guidelines for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/license/osl-3-0-php Open Software License version 3.0
*}
<div class="panel">
	{if isset($header)}{$header}{/if}
	<div id="block_category_tree"{if !$is_category_filter} style="display:none"{/if}>
		{if isset($nodes)}
		<ul id="{$id|escape:'html':'UTF-8'}" class="cattree tree">
			{$nodes}
		</ul>
		{/if}
	</div>
</div>
<script type="text/javascript">
	var currentToken="{$token|@addslashes}";
	var treeClickFunc = function() {
		var newURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
		var queryString = window.location.search.replace(/&id_category=[0-9]*/, "") + "&id_category=" + $(this).val();
		location.href = newURL+queryString; // hash part is dropped: window.location.hash
	};
	{if isset($use_checkbox) && $use_checkbox == true}
		function checkAllAssociatedCategories($tree)
		{
			$tree.find(":input[type=checkbox]").each(
				function()
				{
					$(this).prop("checked", true);
					$(this).parent().addClass("tree-selected");
				}
			);
		}

		function uncheckAllAssociatedCategories($tree)
		{
			$tree.find(":input[type=checkbox]").each(
				function()
				{
					$(this).prop("checked", false);
					$(this).parent().removeClass("tree-selected");
				}
			);
		}
	{/if}
	{if isset($use_search) && $use_search == true}
		$("#{$id|escape:'html':'UTF-8'}-categories-search").bind("typeahead:selected", function(obj, datum) {
		    $("#{$id|escape:'html':'UTF-8'}").find(":input").each(
				function()
				{
					if ($(this).val() == datum.id_category)
					{
						$(this).prop("checked", true);
						$(this).parent().addClass("tree-selected");
						$(this).parents("ul.tree").each(
							function()
							{
								$(this).children().children().children(".icon-folder-close")
									.removeClass("icon-folder-close")
									.addClass("icon-folder-open");
								$(this).show();
							}
						);
					}
				}
			);
		});
	{/if}
	$(document).ready(function () {
		var tree = $("#{$id|escape:'html':'UTF-8'}").tree("collapseAll");

		tree.on('collapse', function() {
			$('#expand-all-{$id|escape:'html':'UTF-8'}').show();
		});

		tree.on('expand', function() {
			$('#collapse-all-{$id|escape:'html':'UTF-8'}').show();
		});

		$('#collapse-all-{$id|escape:'html':'UTF-8'}').hide();
		$("#{$id|escape:'html':'UTF-8'}").find(":input[type=radio]").click(treeClickFunc);

		{if isset($selected_categories)}
			{assign var=imploded_selected_categories value='","'|implode:$selected_categories}
			var selected_categories = new Array("{$imploded_selected_categories}");

			$("#{$id|escape:'html':'UTF-8'}").find(":input").each(
				function()
				{
					if ($.inArray($(this).val(), selected_categories) != -1)
					{
						$(this).prop("checked", true);
						$(this).parent().addClass("tree-selected");
						$(this).parents("ul.tree").each(
							function()
							{
								$(this).children().children().children(".icon-folder-close")
									.removeClass("icon-folder-close")
									.addClass("icon-folder-open");
								$(this).show();
							}
						);
					}
				}
			);
		{/if}
	});
</script>
