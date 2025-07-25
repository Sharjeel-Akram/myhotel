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

<section id="dashoccupancy" class="panel widget allow_push">
	<header class="panel-heading">
		<i class="icon-bar-chart"></i>
		<span>{l s='Occupancy' mod='dashoccupancy'}&nbsp;<small class='text-muted' id='dashoccupancy_date_range'></small></span>
        &nbsp;<i class="icon-info-circle label-tooltip" data-toggle="tooltip" data-original-title="{l s='Occupancy information will be displayed by considering the selected date range as Check-in and Check-out dates.' mod='dashoccupancy'}"></i>
		<span class="panel-heading-action">
			<a class="list-toolbar-btn" href="javascript:void(0);" title="Refresh" onclick="refreshDashboard('dashoccupancy'); return false;">
				<i class="process-icon-refresh"></i>
			</a>
		</span>
	</header>
	<div class="row text-center avil-chart-head">
		<div class="col-md-4 col-xs-4">
			<div class="row">
				<div class="col-md-11 label-tooltip col-lg-11 avail-pie-label-container" style="background: #A569DF;"  data-toggle="tooltip" data-original-title="{l s='The total number of rooms booked for date range out of total number of rooms.' mod='dashoccupancy'}">
					<div class="">
						<p class="avail-pie-text">
							{l s='Occupied' mod='dashoccupancy'}
						</p>
						<div class="avail-pie-value-container">
							<p class="avail-pie-value">
								<span id="do_count_occupied"></span>/<span id="do_count_total"></span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-xs-4">
			<div class="row">
				<div class="col-md-11 col-lg-11 avail-pie-label-container label-tooltip" style="background: #56CE56;" data-toggle="tooltip" data-original-title="{l s='The total number of rooms available for booking for date range.' mod='dashoccupancy'}">
					<div class="">
						<p class="avail-pie-text">
							{l s='Available' mod='dashoccupancy'}
						</p>
						<div class="avail-pie-value-container">
							<p class="avail-pie-value" id="pie_avail_text">
								<span id="do_count_available"></span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4 col-xs-4">
			<div class="row">
				<div class="col-md-11 col-lg-11 avail-pie-label-container label-tooltip" style="background: #FF655C;" data-toggle="tooltip" data-original-title="{l s='The total number of rooms unavailable for booking for date range.' mod='dashoccupancy'}">
					<div class="">
						<p class="avail-pie-text">
							{l s='Unavailable' mod='dashoccupancy'}
						</p>
						<div class="avail-pie-value-container">
							<p class="avail-pie-value" id="pie_inactive_text">
								<span id="do_count_unavailable"></span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="avil-chart-svg" id="availablePieChart">
		<svg></svg>
	</div>
</section>
