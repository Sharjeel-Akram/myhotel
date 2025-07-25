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

<div id="edit_room_tab" class="tab-pane active">
    <input type="hidden" name="id_order" value="{$data.id_order}" />
    <input type="hidden" name="id_room" value="{$data.id_room}" />
    <input type="hidden" name="id_product" value="{$data.id_product}" />
    <input type="hidden" name="id_hotel" value="{$data.id_hotel}" />
    <input type="hidden" name="date_from" value="{$data.date_from}" />
    <input type="hidden" name="date_to" value="{$data.date_to}" />
    <input type="hidden" name="id_order_detail" value="{$data.id_order_detail}" />
    <input type="hidden" name="product_price_tax_excl" value="{Tools::ps_round($data.original_unit_price_tax_excl, _PS_PRICE_COMPUTE_PRECISION_)}" />
    <input type="hidden" name="product_price_tax_incl" value="{Tools::ps_round($data.original_unit_price_tax_incl, _PS_PRICE_COMPUTE_PRECISION_)}" />

    {if isset($data.id_status) && ($data.id_status != HotelBookingDetail::STATUS_ALLOTED)}
        <div class="alert alert-info">
            {if $data.id_status == HotelBookingDetail::STATUS_CHECKED_IN}
                {l s='The check-in date cannot be changed as the booking has already been checked in.'}
            {else if HotelBookingDetail::STATUS_CHECKED_OUT}
                {l s='The check-in and check-out date cannot be changed as the booking has already been checked out.'}
            {/if}
        </div>
    {/if}
    <div class="edit_room_fields">
        <div class="row form-group">
            <div class="col-sm-6 room_check_in_div">
                <label class="control-label">{l s='Check-In'}</label>
                <div class="input-group">
                    <input type="hidden" class="edit_product_date_from_actual" name="edit_product[date_from]"/>
                    {if isset($refundReqBookings) && $refundReqBookings && $data.id|in_array:$refundReqBookings && $data.is_refunded}
                        {dateFormat date=$data.date_from}
                    {else}
                        <input type="text" class="form-control edit_product_date_from" readonly {if isset($data.id_status) && ($data.id_status != HotelBookingDetail::STATUS_ALLOTED)}disabled{/if}/>
                        <div class="input-group-addon"><i class="icon-calendar"></i></div>
                    {/if}
                </div>
            </div>
            <div class="col-sm-6 room_check_out_div">
                <label class="control-label">{l s='Check-Out'}</label>
                <div class="input-group">
                    <input type="hidden" class="edit_product_date_to_actual" name="edit_product[date_to]"/>
                    {if isset($refundReqBookings) && $refundReqBookings && $data.id|in_array:$refundReqBookings && $data.is_refunded}
                        {dateFormat date=$data.date_to}
                    {else}
                    <input type="text" class="form-control edit_product_date_to" readonly {if isset($data.id_status) && ($data.id_status == HotelBookingDetail::STATUS_CHECKED_OUT)}disabled{/if}/>
                        <div class="input-group-addon"><i class="icon-calendar"></i></div>
                    {/if}
                </div>
            </div>
        </div>

        <div class="row form-group">
            <div class="col-sm-6">
                <label class="control-label">{l s='Price (tax excl.)'}</label>
                <div class="input-group">
                    {if $currency->format % 2}<div class="input-group-addon">{$currency->sign}</div>{/if}
                    <input class="form-control room_unit_price" type="text" name="room_unit_price" value=""/>
                    {if !($currency->format % 2)}<div class="input-group-addon">{$currency->sign}</div>{/if}
                </div>
            </div>
            <div class="col-sm-6">
                <label class="control-label">{l s='Occupancy'}</label>
                <div class="booking_occupancy_edit">
                    <div class="dropdown">
                        <button class="form-control booking_guest_occupancy btn btn-default btn-left btn-block input-occupancy" type="button">
                            <span>
                                {if $data['adults']}{$data['adults']}{/if} {if $data['adults'] > 1}{l s='Adults'}{else}{l s='Adult'}{/if}{if {$data['children']}}, {$data['children']} {if $data['children'] > 1}{l s='Children'}{else}{l s='Child'}{/if}{/if}
                            </span>
                        </button>
                        <div class="dropdown-menu booking_occupancy_wrapper fixed-width-xxl well well-sm">
                            <div class="booking_occupancy_inner">
                            <input type="hidden" class="max_adults" value="{if isset($data['room_type_info'])}{$data['room_type_info']['max_adults']|escape:'html':'UTF-8'}{/if}">
                            <input type="hidden" class="max_children" value="{if isset($data['room_type_info'])}{$data['room_type_info']['max_children']|escape:'html':'UTF-8'}{/if}">
                            <input type="hidden" class="max_guests" value="{if isset($data['room_type_info'])}{$data['room_type_info']['max_guests']|escape:'html':'UTF-8'}{/if}">
                                <div class="occupancy_info_block" occ_block_index="0">
                                    <div class="occupancy_info_head col-sm-12"><span class="room_num_wrapper">{l s='Room - 1'}</span></div>
                                    <div class="row">
                                        <div class="col-xs-6 occupancy_count_block">
                                            <div class="col-sm-12">
                                                <label>{l s='Adults'}</label>
                                                <input type="number" class="form-control num_occupancy num_adults" name="occupancy[0][adults]" value="{$data['adults']}" min="1"  max="{$data['room_type_info']['max_adults']|escape:'html':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="col-xs-6 occupancy_count_block">
                                            <div class="col-sm-12">
                                                <label>{l s='Child'} <span class="label-desc-txt"></span></label>
                                                <input type="number" class="form-control num_occupancy num_children" name="occupancy[0][children]" value="{$data['children']}" min="0" max="{$data['room_type_info']['max_children']|escape:'html':'UTF-8'}">
                                                ({l s='Below'}  {$max_child_age|escape:'htmlall':'UTF-8'} {l s='years'})
                                            </div>
                                        </div>
                                    </div>

                                    <p style="display:none;"><span class="text-danger occupancy-input-errors"></span></p>
                                    <div class="row children_age_info_block" {if !isset($data['child_ages']) || !$data['child_ages']}style="display:none"{/if}>
                                        <div class="col-sm-12">
                                            <label class="col-sm-12">{l s='All Children'}</label>
                                            <div class="col-sm-12">
                                                <div class="row children_ages">
                                                    {if isset($data['child_ages']) && $data['child_ages']}
                                                        {foreach $data['child_ages'] as $childAge}
                                                            <p class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                                <select class="guest_child_age room_occupancies" name="occupancy[0][child_ages][]">
                                                                    <option value="-1" {if $childAge == -1}selected{/if}>{l s='Select age'}</option>
                                                                    <option value="0" {if $childAge == 0}selected{/if}>{l s='Under 1'}</option>
                                                                    {for $age=1 to ($max_child_age-1)}
                                                                        <option value="{$age|escape:'htmlall':'UTF-8'}" {if $childAge == $age}selected{/if}>{$age|escape:'htmlall':'UTF-8'}</option>
                                                                    {/for}
                                                                </select>
                                                            </p>
                                                        {/foreach}
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product_invoice" style="display: none;">
            <select name="product_invoice" class="edit_product_invoice">
                {foreach from=$invoices_collection item=invoice}
                    <option value="{$invoice->id}" {if $invoice->id == $data.id_order_invoice}selected="selected"{/if}>
                        #{Configuration::get('PS_INVOICE_PREFIX', $current_id_lang, null, $order->id_shop)}{'%06d'|sprintf:$invoice->number}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" id="submitRoomChange" class="btn btn-primary"><i class="icon icon-bed"></i> &nbsp;{l s="Update Rooms"}</button>
    </div>
</div>