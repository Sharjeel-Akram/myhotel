/**
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
 */

var dashavailability_data;
var dashavailability_chart;

function line_chart_availability(widget_name, chart_details) {
    nv.addGraph(function() {
        var chart = nv.models.lineChart()
            .useInteractiveGuideline(true)
            .x(function(d) { return (d !== undefined ? d[0] : 0); })
            .y(function(d) { return (d !== undefined ? parseInt(d[1]) : 0); })
            .forceY(0)
            .margin({
                left: 30,
                right: 30,
            }
        );

        chart.xAxis
        .tickFormat(function(d) {
            date = new Date(d * 1000);
            return date.format(chart_details['date_format']);
        })
        // through this function we are also fixing the x axis date and values alignment issue
        .tickValues(function(values) {
            var indexInterval = Math.floor(values[0].values.length / 10);

            var dates = [];
            for (var i = 0; i < 10; i++) {
                if (values[0].values[(indexInterval+1) * i]) {
                    dates.push(values[0].values[(indexInterval+1) * i]);
                }
            }
            var dates =  dates.map(function(v) {
                return v[0]
            });

            return dates;
        });

        chart.yAxis.tickFormat(function(d) {
            return Number.isInteger(d) ? d : '';
        });

        // create content for the tooltip of chart
        chart.interactiveLayer.tooltip.contentGenerator((obj, element) => {
            if (typeof obj.series[0] !== 'undefined') {
                var date = new Date(obj.value * 1000);
                date = date.format(chart_details['date_format']);

                tooltipContent = '';
                tooltipContent += '<p>' + date_txt + ': <b>' + date + '</b></p>';
                tooltipContent += '<p>' + avail_rooms_txt + ': <b>' + obj.series[0].value + '</b></p>';
            }

            return getTooltipContent(obj.series[0].key, tooltipContent, obj.series[0].color);
        });

        chart.legend.updateState(false);

        dashavailability_data = chart_details.data;
        dashavailability_chart = chart;
        d3.select('#availability_line_chart1 svg')
            .datum(dashavailability_data)
            .call(chart);
        nv.utils.windowResize(chart.update);

        return chart;
    });
}

function refreshAvailabilityBarData() {
    var days = parseInt($('#dashavailability').find('.avail-bar-btn.bar-btn-active').attr('data-days'));
    fetchAvailablityBarData(days, $("#bardate").val());
}

// select fetch bar chart data
function fetchAvailablityBarData(days, date_from = false) {
    if (!date_from) {
        date_from = $("#bardate").val();
    }

    var extra = JSON.stringify({
        date_from: date_from,
        days: days,
    });

    refreshDashboard('dashavailability', true, extra);
}

function availDatePicker() {
    $('#bardate').datepicker('show');
}

$(document).on('click', '.avail-bar-btn', function() {
    $('.avail-bar-btn').removeClass('bar-btn-active');
    $(this).addClass('bar-btn-active');
    refreshAvailabilityBarData();
});

$(document).ready(function() {
    $("#bardate").val($("#date-start").val());
    $(".bar-date").find("strong").text($("#date-start").val());

    $("#bardate").datepicker({
        dateFormat: 'yy-mm-dd',
        beforeShow: function(input, inst) {
            setTimeout(function() {
                inst.dpDiv.css({
                    top: $(".datepicker").offset().top + 35,
                    left: $(".datepicker").offset().left
                });
            }, 0);
        },
        onSelect: function(date) {
            $(".bar-date").find("strong").text(date);
            refreshAvailabilityBarData();
        }
    });
});
