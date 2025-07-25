{*
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

{foreach from=$languages item=language}
	{if $languages|count > 1}
		<div class="translatable-field row lang-{$language.id_lang}">
			<div class="col-lg-9">
	{/if}
	{if isset($maxchar) && $maxchar}
				<div class="input-group">
					<span id="{if isset($input_id)}{$input_id}_{$language.id_lang}{else}{$input_name}_{$language.id_lang}{/if}_counter" class="input-group-addon">
						<span class="text-count-down">{$maxchar|intval}</span>
					</span>
	{/if}
					<textarea id="{$input_name}_{$language.id_lang}" name="{$input_name}_{$language.id_lang}" class="{if isset($class)}{$class}{else}textarea-autosize{/if}"{if isset($maxlength) && $maxlength} maxlength="{$maxlength|intval}"{/if}{if isset($maxchar) && $maxchar} data-maxchar="{$maxchar|intval}"{/if}>{if isset($input_value[$language.id_lang])}{$input_value[$language.id_lang]|htmlentitiesUTF8}{/if}</textarea>
					<span class="counter" data-max="{if isset($max)}{$max|intval}{/if}{if isset($maxlength)}{$maxlength|intval}{/if}{if !isset($max) && !isset($maxlength)}none{/if}"></span>
			{if isset($maxchar) && $maxchar}
				</div>
			{/if}
	{if $languages|count > 1}
			</div>
			<div class="col-lg-2">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					{$language.iso_code}
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					{foreach from=$languages item=language}
					<li><a href="javascript:tabs_manager.allow_hide_other_languages = false;hideOtherLanguage({$language.id_lang});">{$language.name}</a></li>
					{/foreach}
				</ul>
			</div>
		</div>
	{/if}
{/foreach}

<script type="text/javascript">
	{if isset($maxchar) && $maxchar}
		$(document).ready(function(){
		{foreach from=$languages item=language}
			countDown($("#{$input_name}_{$language.id_lang}"), $("#{$input_name}_{$language.id_lang}_counter"));
		{/foreach}
		});
	{/if}
	$(".textarea-autosize").autosize();
</script>

