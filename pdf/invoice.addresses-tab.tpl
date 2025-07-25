{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<table id="addresses-tab" cellspacing="0" cellpadding="0">
	<tr>
		<!-- <td width="33%"><span class="bold"> </span><br/><br/>
			{if isset($order_invoice)}{$order_invoice->shop_address}{/if}
		</td> -->
		<!-- <td width="33%">{if $delivery_address}<span class="bold">{l s='Delivery Address' pdf='true'}</span><br/><br/>
				{$delivery_address}
			{/if}
		</td> -->
		<td width="33%">
			{if !empty($hotel_address)}
				<span class="bold">{l s='Hotel Detail' pdf='true'}</span><br/><br/>
				{$hotel_address}
			{/if}
		</td>
		<td  width="33%"></td>
		<td width="33%">
			<span class="bold">{l s='Customer Detail' pdf='true'}</span><br/><br/>
			{if $invoice_address}
				{$invoice_address}
			{else}
				{$customer->firstname} {$customer->lastname}
				<br>
				{$customer->phone}
			{/if}
		</td>
	</tr>
</table>