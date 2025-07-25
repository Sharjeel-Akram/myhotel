{*
* 2007-2017 PrestaShop
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
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{block name='my_account'}
    {capture name=path}{l s='My account'}{/capture}

    {block name='my_account_heading'}
        <h1 class="page-heading">{l s='My account'}</h1>
        {if isset($account_created)}
            <p class="alert alert-success">
                {l s='Your account has been created.'}
            </p>
        {/if}
        <p class="info-account">{l s='Welcome to your account. Here you can manage all of your personal information and orders.'}</p>
    {/block}
    <div class="row addresses-lists">
        <div class="col-xs-12 col-sm-6 col-lg-4">
            {block name='my_account_tabs'}
                <ul class="myaccount-link-list">
                    <li><a href="{$link->getPageLink('address', true)|escape:'html':'UTF-8'}" title="{l s='Address'}"><i class="icon-building"></i><span>{l s='Address'}</span></a></li>
                    <li><a href="{$link->getPageLink('history', true)|escape:'html':'UTF-8'}" title="{l s='Bookings'}"><i class="icon-list-ol"></i><span>{l s='Bookings'}</span></a></li>
                    {if $refundAllowed}
                        <li><a href="{$link->getPageLink('order-follow', true)|escape:'html':'UTF-8'}" title="{l s='Refund requests'}"><i class="icon-refresh"></i><span>{l s='Refund requests'}</span></a></li>
                    {/if}
                    <li><a href="{$link->getPageLink('order-slip', true)|escape:'html':'UTF-8'}" title="{l s='Credit slips'}"><i class="icon-file-o"></i><span>{l s='Credit slips'}</span></a></li>
                    <li><a href="{$link->getPageLink('identity', true)|escape:'html':'UTF-8'}" title="{l s='Personal information'}"><i class="icon-user"></i><span>{l s='Personal information'}</span></a></li>
                </ul>
            {/block}
        </div>
        {block name='displayCustomerAccount'}
            {if $voucherAllowed || isset($HOOK_CUSTOMER_ACCOUNT) && $HOOK_CUSTOMER_ACCOUNT !=''}
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <ul class="myaccount-link-list">
                        {if $voucherAllowed}
                            <li><a href="{$link->getPageLink('discount', true)|escape:'html':'UTF-8'}" title="{l s='Vouchers'}"><i class="icon-barcode"></i><span>{l s='Vouchers'}</span></a></li>
                        {/if}
                        {$HOOK_CUSTOMER_ACCOUNT}
                    </ul>
                </div>
            {/if}
        {/block}
    </div>

    {block name='displayCustomerAccountAfterTabs'}
        {hook h='displayCustomerAccountAfterTabs'}
    {/block}

    {block name='my_account_footer_links'}
        <ul class="footer_links clearfix">
        <li><a class="btn btn-default button button-small" href="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl}{else}{$base_dir}{/if}" title="{l s='Home'}"><span><i class="icon-chevron-left"></i> {l s='Home'}</span></a></li>
        </ul>
    {/block}

{/block}
