{if isset($list) && $list}
    <table class="table table-recap room-extra-demand-table">
        <thead>
            <tr>
                <th colspan="3" class="table-caption">{l s='Room Extra Demands Detail'}</th>
            </tr>
            <tr>
                <th>{l s='Room Type'}</th>
                <th>{l s='Name'}</th>
                <th>{l s='Total'} <br /> {l s='(Tax excl.)'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$list key=data_k item=data_v}
                {foreach from=$data_v['date_diff'] key=rm_k item=rm_v}
                    {if isset($rm_v['extra_demands']) && $rm_v['extra_demands']}
                        {assign var=roomCount value=1}
                        {foreach $rm_v['extra_demands'] as $roomDemand}
                            {foreach name=demandRow from=$roomDemand['extra_demands'] item=demand}
                                {if !isset($room_demand_exists)}
                                    {assign var=room_demand_exists value=1}
                                {/if}
                                <tr>
                                    {if $smarty.foreach.demandRow.first}
                                        <td rowspan="{$roomDemand['extra_demands']|count}">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                {$data_v['name']}<br>
                                                {$rm_v['data_form']|date_format:"%d-%m-%Y"} {l s='to'} {$rm_v['data_to']|date_format:"%d-%m-%Y"}<br>
                                                <strong>{l s='Room'} - {$roomCount}</strong>
                                            </font>
                                        </td>
                                    {/if}
                                    <td>
                                        <font size="2" face="Open-sans, sans-serif" color="#555454">
                                            {$demand['name']}
                                        </font>
                                    </td>
                                    <td>
                                        <font size="2" face="Open-sans, sans-serif" color="#555454">
                                            {convertPrice price=$demand['total_price_tax_excl']}
                                        </font>
                                    </td>
                                </tr>
                            {/foreach}
                            {assign var=roomCount value=$roomCount+1}
                        {/foreach}
                    {/if}
                {/foreach}
            {/foreach}

            {if !isset($room_demand_exists)}
                <tr>
                    <th colspan="3" class="table-caption">{l s='No room extra demands added.'}</th>
                </tr>
            {/if}
        </tbody>
    </table>

    <table class="table table-recap room-extra-service-table">
        <thead>
            <tr>
                <th colspan="4" class="table-caption">{l s='Rooms Services Detail'}</th>
            </tr>
            <tr>
                <th>{l s='Room Type'}</th>
                <th>{l s='Name'}</th>
                <th>{l s='Qty'}</th>
                <th>{l s='Total'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$list key=data_k item=data_v}
                {foreach from=$data_v['date_diff'] key=rm_k item=rm_v}
                    {if isset($rm_v['additional_services']) && $rm_v['additional_services']}
                        {assign var=roomCount value=1}
                        {foreach $rm_v['additional_services'] as $roomService}
                            {foreach name=serviceRow from=$roomService['additional_services'] item=service}
                                {if !isset($room_additinal_services_exists)}
                                    {assign var=room_additinal_services_exists value=1}
                                {/if}
                                <tr>
                                    {if $smarty.foreach.serviceRow.first}
                                        <td rowspan="{$roomService['additional_services']|count}">
                                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                                {$data_v['name']}<br>
                                                {$rm_v['data_form']|date_format:"%d-%m-%Y"} {l s='to'} {$rm_v['data_to']|date_format:"%d-%m-%Y"}<br>
                                                <strong>{l s='Room'} - {$roomCount}</strong>
                                            </font>
                                        </td>
                                    {/if}
                                    <td>
                                        <font size="2" face="Open-sans, sans-serif" color="#555454">
                                            {$service['name']}
                                        </font>
                                    </td>
                                    <td>
                                        <font size="2" face="Open-sans, sans-serif" color="#555454">
                                            {if $service['allow_multiple_quantity']}
                                                {$service['quantity']}
                                            {else}
                                                {l s='--'}
                                            {/if}
                                        </font>
                                    </td>
                                    <td>
                                        <font size="2" face="Open-sans, sans-serif" color="#555454">
                                            {convertPrice price=$service['total_price_tax_excl']}
                                        </font>
                                    </td>
                                </tr>
                            {/foreach}
                            {assign var=roomCount value=$roomCount+1}
                        {/foreach}
                    {/if}
                {/foreach}
            {/foreach}

            {if !isset($room_additinal_services_exists)}
                <tr>
                    <th colspan="4" class="table-caption">{l s='No room services added.'}</th>
                </tr>
            {/if}
        </tbody>
    </table>

	<style>
        {if !isset($room_demand_exists)}
            .room-extra-demand-table {
                display: none;
            }
        {/if}
        {if !isset($room_additinal_services_exists)}
            .room-extra-service-table {
                display: none;
            }
        {else}
            .room-extra-demand-table {
                margin-bottom: 20px;
            }
        {/if}
		.room-extra-demand-table, .room-extra-service-table {
		 	width:100%;
			border-collapse:collapse;
			padding:5px;
		}
		.room-extra-demand-table th, .room-extra-service-table th {
			border:1px solid #D6D4D4;
			background-color: #fbfbfb;
			color: #333;
			font-family: Arial;
			font-size: 13px;
            padding: 7px 7px 5px 10px;
            text-align:left;
		}
        .room-extra-demand-table th.table-caption, .room-extra-service-table th.table-caption {
			text-align: left;
            padding:10px;
		}
		.room-extra-demand-table td, .room-extra-service-table td {
			border:1px solid #D6D4D4;
			padding:5px;
            text-align:left;
            padding: 5px 5px 5px 10px;
		}
	</style>
{/if}
