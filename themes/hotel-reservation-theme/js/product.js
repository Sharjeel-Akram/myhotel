/*
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
*/
//global variables
var serialScrollNbImagesDisplayed;
var selectedCombination = [];
var globalQuantity = 0;
var colors = [];
var original_url = window.location + '';
var first_url_check = true;
var firstTime = true;
/* Retro compat from product.tpl */
if (typeof customizationFields !== 'undefined' && customizationFields)
{
	var customizationFieldsBk = customizationFields;
	customizationFields = [];
	var j = 0;
	for (var i = 0; i < customizationFieldsBk.length; ++i)
	{
		var key = 'pictures_' + parseInt(id_product) + '_' + parseInt(customizationFieldsBk[i]['id_customization_field']);
		customizationFields[i] = [];
		customizationFields[i][0] = (parseInt(customizationFieldsBk[i]['type']) == 0) ? 'img' + i : 'textField' + j++;
		customizationFields[i][1] = (parseInt(customizationFieldsBk[i]['type']) == 0 && customizationFieldsBk[i][key]) ? 2 : parseInt(customizationFieldsBk[i]['required']);
	}
}

if (typeof combinationImages !== 'undefined' && combinationImages)
{
	combinationImagesJS = [];
	combinationImagesJS[0] = [];
	var k = 0;
	for (var i in combinationImages)
	{
		combinationImagesJS[i] = [];
		for (var j in combinationImages[i])
		{
			var id_image = parseInt(combinationImages[i][j]['id_image']);
			if (id_image)
			{
				combinationImagesJS[0][k++] = id_image;
				combinationImagesJS[i][j] = [];
				combinationImagesJS[i][j] = id_image;
			}
		}
	}

	if (typeof combinationImagesJS[0] !== 'undefined' && combinationImagesJS[0])
	{
	   var array_values = [];
	   for (var key in arrayUnique(combinationImagesJS[0]))
		   array_values.push(combinationImagesJS[0][key]);
	   combinationImagesJS[0] = array_values;
	}
	combinationImages = combinationImagesJS;
}

if (typeof combinations !== 'undefined' && combinations)
{
	combinationsJS = [];
	combinationsHashSet = {};
	var k = 0;
	for (var i in combinations)
	{
		globalQuantity += combinations[i]['quantity'];
		combinationsJS[k] = [];
		combinationsJS[k]['idCombination'] = parseInt(i);
		combinationsJS[k]['idsAttributes'] = combinations[i]['attributes'];
		combinationsJS[k]['quantity'] = combinations[i]['quantity'];
		combinationsJS[k]['price'] = combinations[i]['price'];
		combinationsJS[k]['ecotax'] = combinations[i]['ecotax'];
		combinationsJS[k]['image'] = parseInt(combinations[i]['id_image']);
		combinationsJS[k]['reference'] = combinations[i]['reference'];
		combinationsJS[k]['unit_price'] = combinations[i]['unit_impact'];
		combinationsJS[k]['minimal_quantity'] = parseInt(combinations[i]['minimal_quantity']);

		combinationsJS[k]['available_date'] = [];
			combinationsJS[k]['available_date']['date'] = combinations[i]['available_date'];
			combinationsJS[k]['available_date']['date_formatted'] = combinations[i]['date_formatted'];

		combinationsJS[k]['specific_price'] = [];
			combinationsJS[k]['specific_price']['reduction_percent'] = (combinations[i]['specific_price'] && combinations[i]['specific_price']['reduction'] && combinations[i]['specific_price']['reduction_type'] == 'percentage') ? combinations[i]['specific_price']['reduction'] * 100 : 0;
			combinationsJS[k]['specific_price']['reduction_price'] = (combinations[i]['specific_price'] && combinations[i]['specific_price']['reduction'] && combinations[i]['specific_price']['reduction_type'] == 'amount') ? combinations[i]['specific_price']['reduction'] : 0;
			combinationsJS[k]['price'] = (combinations[i]['specific_price'] && combinations[i]['specific_price']['price'] && parseInt(combinations[i]['specific_price']['price']) != -1) ? combinations[i]['specific_price']['price'] :  combinations[i]['price'];

		combinationsJS[k]['reduction_type'] = (combinations[i]['specific_price'] && combinations[i]['specific_price']['reduction_type']) ? combinations[i]['specific_price']['reduction_type'] : '';
		combinationsJS[k]['id_product_attribute'] = (combinations[i]['specific_price'] && combinations[i]['specific_price']['id_product_attribute']) ? combinations[i]['specific_price']['id_product_attribute'] : 0;

		var key = combinationsJS[k]['idsAttributes'].sort().join('-');
		combinationsHashSet[key] = combinationsJS[k];

		k++;
	}
	combinations = combinationsJS;
}

$(document).ready(function() {
    var url_found = checkUrl();
    //init the price in relation of the selected attributes
    if (!url_found) {
        if (typeof productHasAttributes != 'undefined' && productHasAttributes)
            findCombination();
        else
            refreshProductImages(0);
    }

    serialScrollSetNbImages();

    //init the serialScroll for thumbs
    if (!!$.prototype.serialScroll)
        $('#thumbs_list').serialScroll({
            items: 'li:visible',
            prev: '#view_scroll_left',
            next: '#view_scroll_right',
            axis: 'xy',
            offset: 0,
            start: 0,
            stop: true,
            onBefore: serialScrollFixLock,
            duration: 700,
            lazy: true,
            lock: false,
            force: false,
            cycle: false
        });

    $('#thumbs_list').trigger('goto', 0);

    //set jqZoom parameters if needed
    if (typeof(jqZoomEnabled) != 'undefined' && jqZoomEnabled) {
        if ($('#thumbs_list .shown img').length) {
            var new_src = $('#thumbs_list .shown img').attr('src').replace('cart_', 'large_');
            if ($('.jqzoom img').attr('src') != new_src)
                $('.jqzoom img').attr('src', new_src).parent().attr('href', new_src);
        }

        $('.jqzoom').jqzoom({
            zoomType: 'innerzoom', //innerzoom/standard/reverse/drag
            zoomWidth: 458, //zooming div default width(default width value is 200)
            zoomHeight: 458, //zooming div default width(default height value is 200)
            xOffset: 21, //zooming div default offset(default offset value is 10)
            yOffset: 0,
            title: false
        });

    }
    if (typeof(contentOnly) != 'undefined' && !contentOnly) {
        if (!!$.prototype.fancybox)
            $('li:visible .fancybox, .fancybox.shown').fancybox({
                'hideOnContentClick': true,
                'openEffect': 'elastic',
                'closeEffect': 'elastic',
            });
    } else if (typeof ajax_allowed != 'undefined' && !ajax_allowed)
        $('#buy_block').attr('target', '_top');

    if ($('#bxslider li').length && !!$.prototype.bxSlider)
        $('#bxslider').bxSlider({
            minSlides: 1,
            maxSlides: 6,
            slideWidth: 178,
            slideMargin: 20,
            pager: false,
            nextText: '',
            prevText: '',
            moveSlides: 1,
            infiniteLoop: false,
            hideControlOnEnd: true
        });

    if (!$('#bxslider li').length)
        $('.accessories-block').parent().remove();

    if (!!$.prototype.uniform) {
        if (typeof product_fileDefaultHtml !== 'undefined')
            $.uniform.defaults.fileDefaultHtml = product_fileDefaultHtml;
        if (typeof product_fileButtonHtml !== 'undefined')
            $.uniform.defaults.fileButtonHtml = product_fileButtonHtml;
    }

    if ($('#customizationForm').length) {
        var url = window.location + '';
        if (url.indexOf('#') != -1)
            getProductAttribute();
    }

    if ($('.room_info_hotel_images_wrap').length) {
        loadHotelImagesByPage(1);
    }
});

$(window).resize(function() {
    serialScrollSetNbImages();
    serialScrollResizeThumbContainer();
    $('#thumbs_list').trigger('goto', 0);
    serialScrollFixLock('', '', '', '', 0);
});

$(window).bind('hashchange', function() {
    checkUrl();
    findCombination();
});

//hover 'other views' images management
$(document).on('mouseover', '#views_block li a', function() {
    displayImage($(this));
});
//add a link on the span 'view full size' and on the big image
$(document).on('click', '#view_full_size, #image-block', function(e) {
    $('#views_block .shown').click();
});
//catch the click on the "more infos" button at the top of the page
$(document).on('click', '#short_description_block .button', function(e) {
    $('#more_info_tab_more_info').click();
    $.scrollTo('#more_info_tabs', 1200);
});
// Hide the customization submit button and display some message
$(document).on('click', '#customizedDatas input', function(e) {
    $('#customizedDatas input').hide();
    $('#ajax-loader').fadeIn();
    $('#customizedDatas').append(uploading_in_progress);
});

$(document).on('click', 'a[data-id=resetImages]', function(e) {
    e.preventDefault();
    refreshProductImages(0);
});

$(document).on('click', '.color_pick', function(e) {
    e.preventDefault();
    colorPickerClick($(this));
    getProductAttribute();
});

$(document).on('change', '.attribute_select', function(e) {
    e.preventDefault();
    findCombination();
    getProductAttribute();
});

$(document).on('click', '.attribute_radio', function(e) {
    e.preventDefault();
    findCombination();
    getProductAttribute();
});

$(document).on('click', 'button[name=saveCustomization]', function(e) {
    saveCustomization();
});

if (typeof ad !== 'undefined' && ad && typeof adtoken !== 'undefined' && adtoken) {
    $(document).on('click', 'a#publish_button', function(e) {
        e.preventDefault();
        submitPublishProduct(ad, 0, adtoken);
    });
    $(document).on('click', 'a#lnk_view', function(e) {
        e.preventDefault();
        submitPublishProduct(ad, 1, adtoken);
    });
}

if (typeof(contentOnly) != 'undefined' && contentOnly) {
    $(document).on('click', '.fancybox', function(e) {
        e.preventDefault();
    });

    $(document).on('click', '#image-block', function(e) {
        e.preventDefault();
        var productUrl = window.document.location.href + '';
        var data = productUrl.replace(/[\?|&]content_only=1/, '');

        if (window.parent.page_name == 'search')
            data += ((data.indexOf('?') < 0) ? '?' : '&') + 'HTTP_REFERER=' + encodeURIComponent(window.parent.document.location.href);

        window.parent.document.location.href = data;
        return;
    });
}

if (typeof minimalQuantity != 'undefined' && minimalQuantity) {
    checkMinimalQuantity();
    $(document).on('keyup', 'input[name=qty]', function(e) {
        checkMinimalQuantity(minimalQuantity);
    });
}

function arrayUnique(a) {
    return a.reduce(function(p, c) {
        if (p.indexOf(c) < 0)
            p.push(c);
        return p;
    }, []);
};

//check if a function exists
function function_exists(function_name) {
    if (typeof function_name === 'string')
        function_name = this.window[function_name];
    return typeof function_name === 'function';
}

//execute oosHook js code
function oosHookJsCode() {
    for (var i = 0; i < oosHookJsCodeFunctions.length; i++) {
        if (function_exists(oosHookJsCodeFunctions[i]))
            setTimeout(oosHookJsCodeFunctions[i] + '()', 0);
    }
}

//add a combination of attributes in the global JS sytem
function addCombination(idCombination, arrayOfIdAttributes, quantity, price, ecotax, id_image, reference, unit_price, minimal_quantity, available_date, combination_specific_price) {
    globalQuantity += quantity;

    var combination = [];
    combination['idCombination'] = idCombination;
    combination['quantity'] = quantity;
    combination['idsAttributes'] = arrayOfIdAttributes;
    combination['price'] = price;
    combination['ecotax'] = ecotax;
    combination['image'] = id_image;
    combination['reference'] = reference;
    combination['unit_price'] = unit_price;
    combination['minimal_quantity'] = minimal_quantity;
    combination['available_date'] = [];
    combination['available_date'] = available_date;
    combination['specific_price'] = [];
    combination['specific_price'] = combination_specific_price;
    combinations.push(combination);
}

// search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
function findCombination() {
    $('#minimal_quantity_wanted_p').fadeOut();
    if (typeof $('#minimal_quantity_label').text() === 'undefined' || $('#minimal_quantity_label').html() > 1)
        $('#quantity_wanted').val(1);

    //create a temporary 'choice' array containing the choices of the customer
    var choice = [];
    var radio_inputs = parseInt($('#attributes .checked > input[type=radio]').length);
    if (radio_inputs)
        radio_inputs = '#attributes .checked > input[type=radio]';
    else
        radio_inputs = '#attributes input[type=radio]:checked';

    $('#attributes select, #attributes input[type=hidden], ' + radio_inputs).each(function() {
        choice.push(parseInt($(this).val()));
    });

    if (typeof combinations == 'undefined' || !combinations)
        combinations = [];
    //testing every combination to find the conbination's attributes' case of the user
    for (var combination = 0; combination < combinations.length; ++combination) {
        //verify if this combinaison is the same that the user's choice
        var combinationMatchForm = true;
        $.each(combinations[combination]['idsAttributes'], function(key, value) {
            if (!in_array(parseInt(value), choice))
                combinationMatchForm = false;
        });

        if (combinationMatchForm) {
            if (combinations[combination]['minimal_quantity'] > 1) {
                $('#minimal_quantity_label').html(combinations[combination]['minimal_quantity']);
                $('#minimal_quantity_wanted_p').fadeIn();
                $('#quantity_wanted').val(combinations[combination]['minimal_quantity']);
                $('#quantity_wanted').bind('keyup', function() {
                    checkMinimalQuantity(combinations[combination]['minimal_quantity']);
                });
            }
            //combination of the user has been found in our specifications of combinations (created in back office)
            selectedCombination['unavailable'] = false;
            selectedCombination['reference'] = combinations[combination]['reference'];
            $('#idCombination').val(combinations[combination]['idCombination']);

            //get the data of product with these attributes
            quantityAvailable = combinations[combination]['quantity'];
            selectedCombination['price'] = combinations[combination]['price'];
            selectedCombination['unit_price'] = combinations[combination]['unit_price'];
            selectedCombination['specific_price'] = combinations[combination]['specific_price'];
            if (combinations[combination]['ecotax'])
                selectedCombination['ecotax'] = combinations[combination]['ecotax'];
            else
                selectedCombination['ecotax'] = default_eco_tax;

            //show the large image in relation to the selected combination
            if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
                displayImage($('#thumb_' + combinations[combination]['image']).parent());

            //show discounts values according to the selected combination
            if (combinations[combination]['idCombination'] && combinations[combination]['idCombination'] > 0)
                displayDiscounts(combinations[combination]['idCombination']);

            //get available_date for combination product
            selectedCombination['available_date'] = combinations[combination]['available_date'];

            //update the display
            updateDisplay();

            if (firstTime) {
                refreshProductImages(0);
                firstTime = false;
            } else
                refreshProductImages(combinations[combination]['idCombination']);
            //leave the function because combination has been found
            return;
        }
    }

    //this combination doesn't exist (not created in back office)
    selectedCombination['unavailable'] = true;
    if (typeof(selectedCombination['available_date']) != 'undefined')
        delete selectedCombination['available_date'];

    updateDisplay();
}

//update display of the availability of the product AND the prices of the product
function updateDisplay() {
    var productPriceDisplay = productPrice;
    var productPriceWithoutReductionDisplay = productPriceWithoutReduction;

    if (!selectedCombination['unavailable'] && quantityAvailable > 0 && productAvailableForOrder == 1) {
        //show the choice of quantities
        $('#quantity_wanted_p:hidden').show('slow');

        //show the "add to cart" button ONLY if it was hidden
        $('#add_to_cart:id_demands').fadeIn(600);

        //hide the hook out of stock
        $('#oosHook').hide();

        $('#availability_date').fadeOut();

        //availability value management
        if (stock_management && availableNowValue != '') {
            $('#availability_value').removeClass('label-warning').addClass('label-success').text(availableNowValue).show();
            $('#availability_statut:hidden').show('slow');
        } else
            $('#availability_statut:visible').hide('slow');

        //'last quantities' message management
        if (!allowBuyWhenOutOfStock) {
            if (quantityAvailable <= maxQuantityToAllowDisplayOfLastQuantityMessage)
                $('#last_quantities').show('slow');
            else
                $('#last_quantities').hide('slow');
        }

        if (quantitiesDisplayAllowed) {
            $('#pQuantityAvailable:hidden').show('slow');
            $('#quantityAvailable').text(quantityAvailable);

            if (quantityAvailable < 2) // we have 1 or less product in stock and need to show "item" instead of "items"
            {
                $('#quantityAvailableTxt').show();
                $('#quantityAvailableTxtMultiple').hide();
            } else {
                $('#quantityAvailableTxt').hide();
                $('#quantityAvailableTxtMultiple').show();
            }
        }
    } else {
        //show the hook out of stock
        if (productAvailableForOrder == 1) {
            $('#oosHook').show();
            if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
                oosHookJsCode();
        }

        //hide 'last quantities' message if it was previously visible
        $('#last_quantities:visible').hide('slow');

        //hide the quantity of pieces if it was previously visible
        $('#pQuantityAvailable:visible').hide('slow');

        //hide the choice of quantities
        if (!allowBuyWhenOutOfStock)
            $('#quantity_wanted_p:visible').hide('slow');

        //display that the product is unavailable with theses attributes
        if (!selectedCombination['unavailable']) {
            $('#availability_value').text(doesntExistNoMore + (globalQuantity > 0 ? ' ' + doesntExistNoMoreBut : ''));
            if (!allowBuyWhenOutOfStock)
                $('#availability_value').removeClass('label-success').addClass('label-warning');
        } else {
            $('#availability_value').text(doesntExist).removeClass('label-success').addClass('label-warning');
            $('#oosHook').hide();
        }

        if ((stock_management == 1 && !allowBuyWhenOutOfStock) || (!stock_management && selectedCombination['unavailable']))
            $('#availability_statut:hidden').show();

        if (typeof(selectedCombination['available_date']) != 'undefined' && typeof(selectedCombination['available_date']['date_formatted']) != 'undefined' && selectedCombination['available_date']['date'].length != 0) {
            var available_date = selectedCombination['available_date']['date'];
            var tab_date = available_date.split('-');
            var time_available = new Date(tab_date[0], tab_date[1], tab_date[2]);
            time_available.setMonth(time_available.getMonth() - 1);
            var now = new Date();
            if (now.getTime() < time_available.getTime() && $('#availability_date_value').text() != selectedCombination['available_date']['date_formatted']) {
                $('#availability_date').fadeOut('normal', function() {
                    $('#availability_date_value').text(selectedCombination['available_date']['date_formatted']);
                    $(this).fadeIn();
                });
            } else if (now.getTime() < time_available.getTime())
                $('#availability_date').fadeIn();
        } else
            $('#availability_date').fadeOut();

        //show the 'add to cart' button ONLY IF it's possible to buy when out of stock AND if it was previously invisible
        if (allowBuyWhenOutOfStock && !selectedCombination['unavailable'] && productAvailableForOrder) {
            $('#add_to_cart:hidden').fadeIn(600);

            if (stock_management && availableLaterValue != '') {
                $('#availability_value').addClass('label-warning').text(availableLaterValue).show('slow');
                $('#availability_statut:hidden').show('slow');
            } else
                $('#availability_statut:visible').hide('slow');
        } else {
            $('#add_to_cart:visible').fadeOut(600);
            if (stock_management == 1 && productAvailableForOrder)
                $('#availability_statut:hidden').show('slow');
        }

        if (productAvailableForOrder == 0)
            $('#availability_statut:visible').hide();
    }

    if (selectedCombination['reference'] || productReference) {
        if (selectedCombination['reference'])
            $('#product_reference span').text(selectedCombination['reference']);
        else if (productReference)
            $('#product_reference span').text(productReference);
        $('#product_reference:hidden').show('slow');
    } else
        $('#product_reference:visible').hide('slow');

    // If we have combinations, update price section: amounts, currency, discount amounts,...
    if (productHasAttributes)
        updatePrice();
}

function updatePrice() {
    // Get combination prices
    var combID = $('#idCombination').val();
    var combination = combinationsFromController[combID];
    if (typeof combination == 'undefined')
        return;

    // Set product (not the combination) base price
    var basePriceWithoutTax = +productPriceTaxExcluded;
    var basePriceWithTax = +productPriceTaxIncluded;
    var priceWithGroupReductionWithoutTax = 0;

    priceWithGroupReductionWithoutTax = basePriceWithoutTax * (1 - groupReduction);

    // Apply combination price impact (only if there is no specific price)
    // 0 by default, +x if price is inscreased, -x if price is decreased
    basePriceWithoutTax = basePriceWithoutTax + +combination.price;
    basePriceWithTax = basePriceWithTax + +combination.price * (taxRate / 100 + 1);

    // If a specific price redefine the combination base price
    if (combination.specific_price && combination.specific_price.price > 0) {
        basePriceWithoutTax = +combination.specific_price.price;
        basePriceWithTax = +combination.specific_price.price * (taxRate / 100 + 1);
    }

    var priceWithDiscountsWithoutTax = basePriceWithoutTax;
    var priceWithDiscountsWithTax = basePriceWithTax;

    if (default_eco_tax) {
        // combination.ecotax doesn't modify the price but only the display
        priceWithDiscountsWithoutTax = priceWithDiscountsWithoutTax + default_eco_tax * (1 + ecotaxTax_rate / 100);
        priceWithDiscountsWithTax = priceWithDiscountsWithTax + default_eco_tax * (1 + ecotaxTax_rate / 100);
        basePriceWithTax = basePriceWithTax + default_eco_tax * (1 + ecotaxTax_rate / 100);
        basePriceWithoutTax = basePriceWithoutTax + default_eco_tax * (1 + ecotaxTax_rate / 100);
    }

    // Apply specific price (discount)
    // We only apply percentage discount and discount amount given before tax
    // Specific price give after tax will be handled after taxes are added
    if (combination.specific_price && combination.specific_price.reduction > 0) {
        if (combination.specific_price.reduction_type == 'amount') {
            if (typeof combination.specific_price.reduction_tax !== 'undefined' && combination.specific_price.reduction_tax === "0") {
                var reduction = combination.specific_price.reduction;
                if (combination.specific_price.id_currency == 0)
                    reduction = reduction * currencyRate * (1 - groupReduction);
                priceWithDiscountsWithoutTax -= reduction;
                priceWithDiscountsWithTax -= reduction * (taxRate / 100 + 1);
            }
        } else if (combination.specific_price.reduction_type == 'percentage') {
            priceWithDiscountsWithoutTax = priceWithDiscountsWithoutTax * (1 - +combination.specific_price.reduction);
            priceWithDiscountsWithTax = priceWithDiscountsWithTax * (1 - +combination.specific_price.reduction);
        }
    }


    // Apply Tax if necessary
    if (noTaxForThisProduct || customerGroupWithoutTax) {
        basePriceDisplay = basePriceWithoutTax;
        priceWithDiscountsDisplay = priceWithDiscountsWithoutTax;
    } else {
        basePriceDisplay = basePriceWithTax;
        priceWithDiscountsDisplay = priceWithDiscountsWithTax;
    }

    // If the specific price was given after tax, we apply it now
    if (combination.specific_price && combination.specific_price.reduction > 0) {
        if (combination.specific_price.reduction_type == 'amount') {
            if (typeof combination.specific_price.reduction_tax === 'undefined' || (typeof combination.specific_price.reduction_tax !== 'undefined' && combination.specific_price.reduction_tax === '1')) {
                var reduction = combination.specific_price.reduction;

                if (typeof specific_currency !== 'undefined' && specific_currency && parseInt(combination.specific_price.id_currency) && combination.specific_price.id_currency != currency.id)
                    reduction = reduction / currencyRate;
                else if (!specific_currency)
                    reduction = reduction * currencyRate;

                if (typeof groupReduction !== 'undefined' && groupReduction > 0)
                    reduction *= 1 - parseFloat(groupReduction);

                priceWithDiscountsDisplay -= reduction;
                // We recalculate the price without tax in order to keep the data consistency
                priceWithDiscountsWithoutTax = priceWithDiscountsDisplay - reduction * (1 / (1 + taxRate / 100));
            }
        }
    }

    // Compute discount value and percentage
    // Done just before display update so we have final prices
    if (basePriceDisplay != priceWithDiscountsDisplay) {
        var discountValue = basePriceDisplay - priceWithDiscountsDisplay;
        var discountPercentage = (1 - (priceWithDiscountsDisplay / basePriceDisplay)) * 100;
    }

    var unit_impact = +combination.unit_impact;
    if (productUnitPriceRatio > 0 || unit_impact) {
        if (unit_impact) {
            baseUnitPrice = productBasePriceTaxExcl / productUnitPriceRatio;
            unit_price = baseUnitPrice + unit_impact;

            if (!noTaxForThisProduct || !customerGroupWithoutTax)
                unit_price = unit_price * (taxRate / 100 + 1);
        } else
            unit_price = priceWithDiscountsDisplay / productUnitPriceRatio;
    }

    /*  Update the page content, no price calculation happens after */

    // Hide everything then show what needs to be shown
    $('#reduction_percent').hide();
    $('#reduction_amount').hide();
    $('#old_price, #old_price_display, #old_price_display_taxes').hide();
    $('.price-ecotax').hide();
    $('.unit-price').hide();

    $('#our_price_display').text(formatCurrency(priceWithDiscountsDisplay, currencyFormat, currencySign, currencyBlank)).trigger('change');

    // If the calculated price (after all discounts) is different than the base price
    // we show the old price striked through

    if (priceWithDiscountsDisplay.toFixed(2) != basePriceDisplay.toFixed(2)) {
        $('#old_price_display span.price').text(formatCurrency(basePriceDisplay, currencyFormat, currencySign, currencyBlank));
        $('#old_price, #old_price_display, #old_price_display_taxes').removeClass('hidden').show();

        // Then if it's not only a group reduction we display the discount in red box
        if (priceWithDiscountsWithoutTax != priceWithGroupReductionWithoutTax) {
            if (combination.specific_price.reduction_type == 'amount') {
                $('#reduction_amount_display').html('-' + formatCurrency(discountValue, currencyFormat, currencySign, currencyBlank));
                $('#reduction_amount').show();
            } else {
                var toFix = 2;
                if ((parseFloat(discountPercentage).toFixed(2) - parseFloat(discountPercentage).toFixed(0)) == 0)
                    toFix = 0;
                $('#reduction_percent_display').html('-' + parseFloat(discountPercentage).toFixed(toFix) + '%');
                $('#reduction_percent').show();
            }
        }
    }

    // Green Tax (Eco tax)
    // Update display of Green Tax
    if (default_eco_tax) {
        ecotax = default_eco_tax;

        // If the default product ecotax is overridden by the combination
        if (combination.ecotax)
            ecotax = +combination.ecotax;

        if (!noTaxForThisProduct)
            ecotax = ecotax * (1 + ecotaxTax_rate / 100)

        $('#ecotax_price_display').text(formatCurrency(ecotax * currencyRate, currencyFormat, currencySign, currencyBlank));
        $('.price-ecotax').show();
    }

    // Unit price are the price per piece, per Kg, per m²
    // It doesn't modify the price, it's only for display
    if (productUnitPriceRatio > 0) {
        $('#unit_price_display').text(formatCurrency(unit_price * currencyRate, currencyFormat, currencySign, currencyBlank));
        $('.unit-price').show();
    }

    if (noTaxForThisProduct || customerGroupWithoutTax)
        updateDiscountTable(productBasePriceTaxExcl);
    else
        updateDiscountTable(productBasePriceTaxIncl);
}

//update display of the large image
function displayImage(domAAroundImgThumb, no_animation) {
    if (typeof(no_animation) == 'undefined')
        no_animation = false;
    if (domAAroundImgThumb.attr('href')) {
        var new_src = domAAroundImgThumb.attr('href').replace('thickbox', 'large');
        var new_title = domAAroundImgThumb.attr('title');
        var new_href = domAAroundImgThumb.attr('href');
        if ($('#bigpic').attr('src') != new_src) {
            $('#bigpic').attr({
                'src': new_src,
                'alt': new_title,
                'title': new_title
            }).load(function() {
                if (typeof(jqZoomEnabled) != 'undefined' && jqZoomEnabled)
                    $(this).attr('rel', new_href);
            });
        }
        $('#views_block li a').removeClass('shown');
        $(domAAroundImgThumb).addClass('shown');
    }
}

/**
 * Update display of the discounts table.
 * @param combination Combination ID.
 */
function displayDiscounts(combination) {
    // Tables & rows selection
    var quantityDiscountTable = $('#quantityDiscount');
    var combinationsSpecificQuantityDiscount = $('.quantityDiscount_'+combination, quantityDiscountTable);
	var allQuantityDiscount = $('.quantityDiscount_0', quantityDiscountTable);

    // If there is some combinations specific quantity discount, show them, else, if there are some
    // products quantity discount: show them. In case of result, show the category.
    if (combinationsSpecificQuantityDiscount.length != 0) {
        combinationsSpecificQuantityDiscount.show();
        allQuantityDiscount.hide();
        quantityDiscountTable.show();
    } else if (allQuantityDiscount.length != 0) {
        allQuantityDiscount.show();
        $('tbody tr', quantityDiscountTable).not('.quantityDiscount_0').hide();
        quantityDiscountTable.show();
    } else {
        quantityDiscountTable.hide();
    }
}

function updateDiscountTable(newPrice) {
    $('#quantityDiscount tbody tr').each(function() {
        var type = $(this).data("discount-type");
        var discount = $(this).data("discount");
        var quantity = $(this).data("discount-quantity");

        if (type == 'percentage') {
            var discountedPrice = newPrice * (1 - discount / 100);
            var discountUpTo = newPrice * (discount / 100) * quantity;
        } else if (type == 'amount') {
            var discountedPrice = newPrice - discount;
            var discountUpTo = discount * quantity;
        }

        if (displayDiscountPrice != 0)
            $(this).children('td').eq(1).text(formatCurrency(discountedPrice * currencyRate, currencyFormat, currencySign, currencyBlank));
        $(this).children('td').eq(2).text(upToTxt + ' ' + formatCurrency(discountUpTo * currencyRate, currencyFormat, currencySign, currencyBlank));
    });
}

function serialScrollFixLock(event, targeted, scrolled, items, position) {
    var serialScrollNbImages = $('#thumbs_list li:visible').length;
    var leftArrow = position == 0 ? true : false;
    var rightArrow = position + serialScrollNbImagesDisplayed >= serialScrollNbImages ? true : false;

    $('#view_scroll_left').css('cursor', leftArrow ? 'default' : 'pointer').css('display', leftArrow ? 'none' : 'block').fadeTo(0, leftArrow ? 0 : 1);
    $('#view_scroll_right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');
    return true;
}

function serialScrollSetNbImages() {
    serialScrollNbImagesDisplayed = 6;
    if ($(window).outerWidth(true) < 768) {
        var frame_width = $('#thumbs_list').width();
        var thumbWidth = $('#thumbs_list_frame li:first').outerWidth(true);
        serialScrollNbImagesDisplayed = Math.floor(frame_width/ thumbWidth);
    }
    else if ($(window).outerWidth(true) < 992)
        serialScrollNbImagesDisplayed = 3;
    else if ($(window).outerWidth(true) < 1200)
        serialScrollNbImagesDisplayed = 5;
}

function serialScrollResizeThumbContainer() {
    if ($(window).outerWidth(true) < 768) {
        var vp_width = 0;
        $('#thumbs_list_frame >li').each(function(index, val) {
            vp_width += $(this).outerWidth(true);
        });
        $('#thumbs_list_frame').width(parseInt(vp_width) + 'px');
    } else {
        $('#thumbs_list_frame').width('');
    }
}

// Change the current product images regarding the combination selected
function refreshProductImages(id_product_attribute) {
    id_product_attribute = parseInt(id_product_attribute);

    if (id_product_attribute > 0 && typeof(combinationImages) != 'undefined' && typeof(combinationImages[id_product_attribute]) != 'undefined') {
        $('#thumbs_list li').hide();
        for (var i = 0; i < combinationImages[id_product_attribute].length; i++)
            if (typeof(jqZoomEnabled) != 'undefined' && jqZoomEnabled)
                $('#thumbnail_' + parseInt(combinationImages[id_product_attribute][i])).show().children('a.shown').trigger('click');
            else
                $('#thumbnail_' + parseInt(combinationImages[id_product_attribute][i])).show();
    } else {
        $('#thumbs_list li').show();

        var choice = [];
        var radio_inputs = parseInt($('#attributes .checked > input[type=radio]').length);
        if (radio_inputs)
            radio_inputs = '#attributes .checked > input[type=radio]';
        else
            radio_inputs = '#attributes input[type=radio]:checked';

        $('#attributes select, #attributes input[type=hidden], ' + radio_inputs).each(function() {
            choice.push(parseInt($(this).val()));
        });

        if (typeof combinations == 'undefined' || !combinations)
            combinations = [];

        //testing every combination to find the conbination's attributes' case of the user
        for (var combination = 0; combination < combinations.length; ++combination) {
            //verify if this combinaison is the same that the user's choice
            var combinationMatchForm = true;

            $.each(combinations[combination]['idsAttributes'], function(key, value) {
                if (!in_array(parseInt(value), choice))
                    combinationMatchForm = false;
            });

            if (combinationMatchForm) {
                //show the large image in relation to the selected combination
                if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
                    displayImage($('#thumb_' + combinations[combination]['image']).parent());
            }
        }
    }

    if (parseInt($('#thumbs_list_frame >li:visible').length) != parseInt($('#thumbs_list_frame >li').length))
        $('#wrapResetImages').stop(true, true).show();
    else
        $('#wrapResetImages').stop(true, true).hide();

    serialScrollResizeThumbContainer();
    $('#thumbs_list').trigger('goto', 0);
    serialScrollFixLock('', '', '', '', 0);
}

function saveCustomization() {
    $('#quantityBackup').val($('#quantity_wanted').val());
    $('#customizationForm').submit();
}

function submitPublishProduct(url, redirect, token) {
    var id_product = $('#admin-action-product-id').val();

    $.ajaxSetup({
        async: false
    });
    $.post(url + '/index.php', {
            action: 'publishProduct',
            id_product: id_product,
            status: 1,
            redirect: redirect,
            ajax: 1,
            tab: 'AdminProducts',
            token: token
        },
        function(data) {
            if (data.indexOf('error') === -1)
                document.location.href = data;
        }
    );
    return true;
}

function checkMinimalQuantity(minimal_quantity) {
    if ($('#quantity_wanted').val() < minimal_quantity) {
        $('#quantity_wanted').css('border', '1px solid red');
        $('#minimal_quantity_wanted_p').css('color', 'red');
    } else {
        $('#quantity_wanted').css('border', '1px solid #BDC2C9');
        $('#minimal_quantity_wanted_p').css('color', '#374853');
    }
}

function colorPickerClick(elt) {
    id_attribute = $(elt).attr('id').replace('color_', '');
    $(elt).parent().parent().children().removeClass('selected');
    $(elt).fadeTo('fast', 1, function() {
        $(this).fadeTo('fast', 0, function() {
            $(this).fadeTo('fast', 1, function() {
                $(this).parent().addClass('selected');
            });
        });
    });
    $(elt).parent().parent().parent().children('.color_pick_hidden').val(id_attribute);
}

function getProductAttribute() {
    // get every attributes values
    request = '';
    //create a temporary 'tab_attributes' array containing the choices of the customer
    var tab_attributes = [];
    var radio_inputs = parseInt($('#attributes .checked > input[type=radio]').length);
    if (radio_inputs)
        radio_inputs = '#attributes .checked > input[type=radio]';
    else
        radio_inputs = '#attributes input[type=radio]:checked';

    $('#attributes select, #attributes input[type=hidden], ' + radio_inputs).each(function() {
        tab_attributes.push($(this).val());
    });

    // build new request
    for (var i in attributesCombinations)
        for (var a in tab_attributes)
            if (attributesCombinations[i]['id_attribute'] === tab_attributes[a])
                request += '/' + attributesCombinations[i]['id_attribute'] + '-' + attributesCombinations[i]['group'] + attribute_anchor_separator + attributesCombinations[i]['attribute'];
    request = request.replace(request.substring(0, 1), '#/');
    var url = window.location + '';

    // redirection
    if (url.indexOf('#') != -1)
        url = url.substring(0, url.indexOf('#'));

    if ($('#customizationForm').length) {
        // set ipa to the customization form
        customAction = $('#customizationForm').attr('action');
        if (customAction.indexOf('#') != -1)
            customAction = customAction.substring(0, customAction.indexOf('#'));
        $('#customizationForm').attr('action', customAction + request);
    }

    window.location.replace(url + request);
}

function checkUrl() {
    if (original_url != window.location || first_url_check) {
        first_url_check = false;
        var url = window.location + '';
        // if we need to load a specific combination
        if (url.indexOf('#/') != -1) {
            // get the params to fill from a "normal" url
            params = url.substring(url.indexOf('#') + 1, url.length);
            tabParams = params.split('/');
            tabValues = [];
            if (tabParams[0] == '')
                tabParams.shift();

            var len = tabParams.length;
            for (var i = 0; i < len; i++)
                tabValues.push(tabParams[i].split(attribute_anchor_separator));

            // fill html with values
            $('.color_pick').removeClass('selected').parent().parent().children().removeClass('selected');

            count = 0;
            for (var z in tabValues)
                for (var a in attributesCombinations)
                    if (attributesCombinations[a]['group'] === decodeURIComponent(tabValues[z][1]) && attributesCombinations[a]['id_attribute'] === decodeURIComponent(tabValues[z][0])) {
                        count++;

                        // add class 'selected' to the selected color
                        $('#color_' + attributesCombinations[a]['id_attribute']).addClass('selected').parent().addClass('selected');
                        $('input:radio[value=' + attributesCombinations[a]['id_attribute'] + ']').prop('checked', true);
                        $('input[type=hidden][name=group_' + attributesCombinations[a]['id_attribute_group'] + ']').val(attributesCombinations[a]['id_attribute']);
                        $('select[name=group_' + attributesCombinations[a]['id_attribute_group'] + ']').val(attributesCombinations[a]['id_attribute']);
                        if (!!$.prototype.uniform)
                            $.uniform.update('input[name=group_' + attributesCombinations[a]['id_attribute_group'] + '], select[name=group_' + attributesCombinations[a]['id_attribute_group'] + ']');

                    }
                    // find combination and select corresponding thumbs
            if (count) {
                if (firstTime) {
                    firstTime = false;
                    findCombination();
                }
                original_url = url;
                return true;
            }
            // no combination found = removing attributes from url
            else
                window.location.replace(url.substring(0, url.indexOf('#')));
        }
    }
    return false;
}

/*#####################################################################*/
/*java script code by webkul on produt page.*/
/*#####################################################################*/

$(document).ready(function() {
    if (!PS_CATALOG_MODE) {
        BookingForm.init();
    }

    $(document).on('QloApps:updateRoomOccupancy', function(e) {
        BookingForm.refresh();
    });

    $(document).on('QloApps:updateRoomQuantity', function(e) {
        BookingForm.refresh();
    });

    $('.id_room_type_demand').on('click', function() {
        BookingForm.refresh();
    });

    $(document).on('click', '.remove_roomtype_demand', function(e) {
        e.preventDefault();
        let idGlobalDemand = $(this).data('id_global_demand');
        $('.id_room_type_demand[data-id_global_demand="' + idGlobalDemand + '"]:checked').prop('checked', false).uniform();
        BookingForm.refresh();
    });

    $(document).on('change', '.room_demand_block .id_option', function(e) {
        var optionSelected = $(this).find('option:selected');
        var extraDemandPrice = optionSelected.attr("optionPrice")
        extraDemandPrice = parseFloat(extraDemandPrice);
        extraDemandPrice = formatCurrency(extraDemandPrice, currency_format, currency_sign, currency_blank);
        $(this).closest('.room_demand_block').find('.extra_demand_option_price').text(extraDemandPrice);
        if ($(this).closest('.room_demand_block').find('.id_room_type_demand:checked').val()) {
            BookingForm.refresh();
        }
    });


    // Accordian for extra demand
    function close_accordion_section() {
        $('.accordion .accordion-section-title').removeClass('active');
        $('.accordion .accordion-section-content').slideUp(300).removeClass('open');
    }

    $(document).on('click', '.accordion-section-title', function(e) {
        // Grab current anchor value
        var currentAttrValue = $(this).attr('href');

        if ($(e.target).is('.active')) {
            close_accordion_section();
            $(this).find('i').removeClass('icon-angle-down');
            $(this).find('i').addClass('icon-angle-left');
        } else {
            close_accordion_section();
            // Add active class to section title
            $(this).addClass('active');
            $(this).find('i').removeClass('icon-angle-left');
            $(this).find('i').addClass('icon-angle-down');
            // Open up the hidden content panel
            $('.accordion ' + currentAttrValue).slideDown(300).addClass('open');
        }
        e.preventDefault();
	});
    // normal product
    $(document).on('click', '.add_roomtype_product', function(e){
        e.preventDefault();
        addProductToRoomType(this);
    });

    $(document).on('click', '.remove_roomtype_product', function(e){
        e.preventDefault();
        removeRoomtypeProduct(this);
    });

    $(document).on('click', '.room_service_product_qty_up', function(e) {
        e.preventDefault();
        qtyfield = $(this).closest('.qty_container').find('input.room_service_product_qty');
        var newQuantity = parseInt(qtyfield.val()) + 1;
        if (qtyfield.data('max_quantity') && qtyfield.data('max_quantity') < newQuantity) {
            newQuantity = qtyfield.data('max_quantity');
        }
        $(this).closest('.qty_container').find('.qty_count span').text(newQuantity);
        qtyfield.val(newQuantity);
        updateRoomServiceQuantity(this);
    });

    // The button to decrement the product value
    $(document).on('click', '.room_service_product_qty_down', function(e) {
        e.preventDefault();
        qtyfield = $(this).closest('.qty_container').find('input.room_service_product_qty');
        var currentVal = parseInt(qtyfield.val());
        if (!isNaN(currentVal) && currentVal > 1) {
            $(this).closest('.qty_container').find('.qty_count span').text(currentVal - 1);
            qtyfield.val(currentVal - 1);
        } else {
            $(this).closest('.qty_container').find('.qty_count span').text(1);
            qtyfield.val(1);
        }
        updateRoomServiceQuantity(this);
    });

    var ajax_check_var = '';

    $('#service_products_cont .get-service-products').on('click', function(e) {
        var triggerElement = $(this);
        var p = $(this).data('page');
        var id_category = $(this).data('id_category');
        abortRunningAjax();
        ajax_check_var = $.ajax({
            type: 'POST',
            headers: {
                "cache-control": "no-cache"
            },
            url: product_controller_url,
            dataType: 'JSON',
            cache: false,
            data: {
                id_category: id_category,
                p: p,
                id_product: $('#product_page_product_id').val(),
                action: 'getServiceProducts',
                ajax: true,
                token: static_token
            },
            beforeSend: function() {
                triggerElement.attr('disabled', 'disabled');
            },
            success: function(result) {
                if (result.status) {
                    if (result.service_products) {
                        if (id_category) {
                            $('#category_'+id_category+' .product-list').append(result.service_products);
                            triggerElement.data('page', parseInt(triggerElement.data('page')) + 1);
                            totalResults = triggerElement.data('num_total');
                            if (totalResults <= $('#category_'+id_category+' .product-list .service-product-element').length) {
                                triggerElement.hide();
                            }
                        } else {
                            $('#service_products_cont .product-list').append(result.service_products);
                            triggerElement.data('page', parseInt(triggerElement.data('page')) + 1);
                            totalResults = triggerElement.data('num_total');
                            if (totalResults <= $('#service_products_cont .product-list .service-product-element').length) {
                                triggerElement.hide();
                            }
                        }
                    } else {
                        triggerElement.hide();
                    }
                } else {
                    if (result.error) {
                        showErrorMessage(result.error);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                if ((textStatus != 'error' || errorThrown != '') && textStatus != 'abort')
                    showErrorMessage(textStatus + ': ' + errorThrown);
            },
            complete: function() {
                triggerElement.attr('disabled', false);
            }
        });
    });

    var timer = '';
    // service Products
    $(document).on('click', '.service_product_qty_up', function(e) {
        e.preventDefault();
        qtyfield = $(this).closest('.qty_container').find('input.service_product_qty');
        var newQuantity = parseInt(qtyfield.val()) + 1;
        let hasStockError = false;
        if (parseInt($('#product_page_booking_product').val()) == 0) {
            let stockQty = parseInt($(this).closest('.qty_container').find('.stock_qty').data('stock_quantity'));
            let allow_oosp = parseInt($(this).closest('.qty_container').find('.stock_qty').data('allow_oosp'));
            if (!allow_oosp && (stockQty < newQuantity)) {
                showErrorMessage(out_of_stock_text);
                newQuantity = stockQty;
                hasStockError = true;
            }
        }
        if (!hasStockError) {
            if (qtyfield.data('max_quantity') && qtyfield.data('max_quantity') < newQuantity) {
                // if max quantity is reached
                newQuantity = qtyfield.data('max_quantity');
                let cartQty = parseInt(qtyfield.data('cart_quantity'));
                if (isNaN(cartQty)) {
                    cartQty = 0;
                }

                showErrorMessage(max_service_product_qty_txt+' '+ (parseInt(qtyfield.data('max_quantity')) + cartQty));
            }
            $(this).closest('.qty_container').find('.qty_count span').text(newQuantity);
        }
        qtyfield.val(newQuantity);
        clearTimeout(timer);
        timer = setTimeout(function() {
            BookingForm.refresh()
        }, 500);
    });

    // The button to decrement the product value
    $(document).on('click', '.service_product_qty_down', function(e) {
        e.preventDefault();
        qtyfield = $(this).closest('.qty_container').find('input.service_product_qty');
        var currentVal = parseInt(qtyfield.val());
        if (!isNaN(currentVal) && currentVal > 1) {
            $(this).closest('.qty_container').find('.qty_count span').text(currentVal - 1);
            qtyfield.val(currentVal - 1);
        } else {
            $(this).closest('.qty_container').find('.qty_count span').text(1);
            qtyfield.val(1);
        }
        clearTimeout(timer);
        timer = setTimeout(function() {
            BookingForm.refresh()
        }, 500);
    });

    $(document).on('change', 'input[name="id_product_option"]', function(e) {
        BookingForm.refresh()
    });


    function abortRunningAjax() {
        if (ajax_check_var) {
            ajax_check_var.abort();
        }
    }
});
function addProductToRoomType(that) {
    var id_product = $(that).data('id-product');
    var qty = $('input#room_service_product_qty_'+id_product).val();
    if (typeof(qty) == 'undefined') {
        qty = 1;
    }
    $(that).prop('disabled', true);

    if ($('#additional_products input#service_product_'+ id_product).length) {
        $('input#service_product_'+ id_product).val(qty);
    } else {
        $('<input type="hidden">').attr({
            id: 'service_product_'+ id_product,
            name: 'service_product['+ id_product +'][]',
            class: 'service_product',
            'data-id_product': id_product,
            value: qty
        }).appendTo('#additional_products');
    }
    BookingForm.refresh().then(function (res) {
        $(that).text(unselect_txt).removeClass('btn-success').removeClass('add_roomtype_product').addClass('btn-danger').addClass('remove_roomtype_product').prop('disabled', false);
        showSuccessMessage(service_added_txt);
    });
}

function removeRoomtypeProduct(that) {
    var id_product = $(that).data('id-product');
    $(document).find('input#service_product_'+ id_product).remove();
    $('.select_room_service_'+ id_product).closest('.service_product_action_block').find('input.room_service_product_qty').val(1);
    $('.select_room_service_'+ id_product).closest('.service_product_action_block').find('.qty_count span').text(1);
    $(that).prop('disabled', true);
    BookingForm.refresh().then(function (res) {
        $('.select_room_service_'+ id_product).text(select_txt).removeClass('btn-danger').removeClass('remove_roomtype_product').addClass('btn-success').addClass('add_roomtype_product').prop('disabled', false);
        showSuccessMessage(service_removed_txt);
    });
}

function updateRoomServiceQuantity(that) {
    var buttonElement = $(that).closest('.service_product_action_block').find('.btn-service-product');
    if ($(buttonElement).hasClass('remove_roomtype_product')) {
        var id_product = $(buttonElement).data('id-product');
        var newQuantity = parseInt($('#room_service_product_qty_'+id_product).val());
        var oldQuantity = parseInt($('#service_product_'+id_product).val());
        if (newQuantity != oldQuantity) {
            $('#service_product_'+id_product).val(newQuantity);
            BookingForm.refresh().then(function (res) {
                showSuccessMessage(service_updated_txt);
            });
        }
    }
}


function initMap() {
    const map = new google.maps.Map($('#room_type_map_tab .map-wrap').get(0), {
        zoom: 10,
        streetViewControl: false,
        mapId: PS_MAP_ID
    });

    const hotelLatLng = {
        lat: Number(hotel_location.latitude),
        lng: Number(hotel_location.longitude),
    };

    map.setCenter(hotelLatLng);
    let icon = document.createElement('img');
    icon.src = PS_STORES_ICON;
    icon.style.width = '24px';
    icon.style.height = '24px';
    let marker = new google.maps.marker.AdvancedMarkerElement({
        position: hotelLatLng,
        map: map,
        content: icon,
    });

    const uiContent = $('#room-info-map-ui-content .hotel-info-wrap').get(0);
    map.controls[google.maps.ControlPosition.LEFT_TOP].push(uiContent);
}

var BookingForm = {
    currentRequest: null,
    init: function() {
        $('form#booking-form input[type="radio"]').uniform();
        $('select.input-hotel').chosen({
            disable_search: true,
            width: '100%',
        });
        this.currentRequest = null;
        if (parseInt($('#product_page_booking_product').val())) {
            BookingForm.initDatepicker(max_order_date, min_booking_offset, $('#room_check_in').val(), $('#room_check_out').val());
        }
        // initialize tootltip for extra service
        if ($('.price_desc_block .services-info').length) {
            $('.price_desc_block .services-info img').tooltip({
                content: $('.price_desc_block .services-info-container').html(),
                items: "div",
                trigger : 'hover',
                position: { my: "left-15 top", at: "left bottom" },
                tooltipClass: "services-tootip",
                open: function(event, ui)
                    {
                        if (typeof(event.originalEvent) === 'undefined')
                        {
                            return false;
                        }

                        var $id = $(ui.tooltip).attr('id');

                        // close any lingering tooltips
                        if ($('div.ui-tooltip').not('#' + $id).length) {
                            return false;
                        }

                        // ajax function to pull in data and add it to the tooltip goes here
                    },
                    close: function(event, ui)
                    {
                        ui.tooltip.hover(function()
                        {
                            $(this).stop(true).fadeTo(400, 1);
                        },
                        function()
                        {
                            $(this).fadeOut('400', function()
                            {
                                $(this).remove();
                            });
                        });
                    }

            });
        }
        if (!$('.max_avail_type_qty').length || $('.max_avail_type_qty').val() < 1) {
            disableRoomTypeDemands(1);
            disableRoomTypeServices(1);
        } else {
            disableRoomTypeDemands(0);
            disableRoomTypeServices(0);
        }
        $(document).trigger("QloApps:afterBookingFormInit");
    },
    initDatepicker: function(max_order_date, min_booking_offset, dateFrom, dateTo) {
        let start_date = new Date();
        if (min_booking_offset) {
            start_date.setDate(start_date.getDate() + parseInt(min_booking_offset));
            start_date.setHours(0, 0, 0, 0);
            if (dateFrom && new Date(dateFrom + ' 00:00:00') < start_date) {
                dateFrom = $.datepicker.formatDate('yy-mm-dd', start_date);
                if (new Date(dateTo + ' 00:00:00') <= start_date) {
                    dateTo = new Date(start_date);
                    dateTo.setDate(start_date.getDate() + 1);
                    dateTo = $.datepicker.formatDate('yy-mm-dd', dateTo);
                }
            }
        }

        if (max_order_date) {
            max_order_date = $.datepicker.parseDate('yy-mm-dd', max_order_date );
        } else {
            max_order_date = false;
        }
        if (typeof $('#room_date_range').data('dateRangePicker') != 'undefined') {
            if (max_order_date) {
                if ($.datepicker.parseDate('yy-mm-dd', $('#room_check_out').val()) < max_order_date) {
                    dateFrom = $('#room_check_in').val();
                    dateTo = $('#room_check_out').val();
                }
            }
            $('#room_date_range').data('dateRangePicker').clear();
            $('#room_date_range').data('dateRangePicker').destroy();
            $("#room_date_range").off("datepicker-change");
        }

        $('#room_date_range').dateRangePicker({
            endDate: max_order_date,
            startDate: start_date,
        }).on('datepicker-change', function(event,obj){
            $('#room_check_in').val($.datepicker.formatDate('yy-mm-dd', obj.date1));
            $('#room_check_out').val($.datepicker.formatDate('yy-mm-dd', obj.date2));
            BookingForm.refresh();
        });

        $('body button').on('click', '', function() {
            if (!$(this).closest('.date-picker-wrapper').length) {
                $('#room_date_range').data('dateRangePicker').close();
            }
        });
        if (dateFrom && dateTo) {
            $('#room_date_range').data('dateRangePicker').setDateRange(
                $.datepicker.formatDate('dd-mm-yy', $.datepicker.parseDate('yy-mm-dd', dateFrom)),
                $.datepicker.formatDate('dd-mm-yy', $.datepicker.parseDate('yy-mm-dd', dateTo))
            );
        }
    },
    getFormData: function () {
        let formData = new FormData($('form#booking-form').get(0));
        formData.append('room_type_demands', JSON.stringify(getRoomsExtraDemands()));
        formData.append('room_service_products', JSON.stringify(getRoomsServiceProducts()));
        return formData;
    },
    refresh: function(resetOccupancy = false) {
        return new Promise((resolve, reject) => {
            let formData = BookingForm.getFormData();
            formData.append('ajax', true);
            formData.append('action', 'refreshBookingForm');
            BookingForm.currentRequest = $.ajax({
                url: product_controller_url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    if(BookingForm.currentRequest != null) {
                        BookingForm.currentRequest.abort();
                    }
                    $('.book_now_submit').prop('disabled', true).find('span:not(#booking_action_loader)').hide();
                    $('#booking_action_block').find('#booking_action_loader').addClass('active');
                },
                success: function(response) {
                    if (response.status == true) {
                        $('.booking-form').replaceWith(response.html_booking_form);
                        BookingForm.init();

                        if (resetOccupancy) {
                            BookingForm.resetOccupancy();
                        }
                        resolve(response);
                    }
                },
                error: function() {
                    if (!onlineFlag) {
                        showErrorMessage(no_internet_txt);
                    }
                },
                complete: function() {
                    $('.book_now_submit').prop('disabled', false).find('span:not(#booking_action_loader)').show();
                    $('#booking_action_block').find('#booking_action_loader').removeClass('active');
                }
            });
        });
    },
    resetOccupancy: function () {
        resetOccupancyField($('.booking-form .booking_occupancy_wrapper'));
    }
}

function loadHotelImagesByPage(page = 1) {
    page = parseInt(page);

    $.ajax({
        url: product_controller_url,
        type: 'POST',
        dataType: 'JSON',
        data: {
            ajax: true,
            action: 'getHotelImages',
            id_product: id_product,
            page: page,
        },
        beforeSend: function() {
            $('.room_info_hotel_images_wrap .skeleton-loading-wrap').show();
        },
        success: function(response) {
            if (response.status == true && response.message == 'HTML_OK') {
                $('.room_info_hotel_images_wrap .images-wrap').append(response.html);
                $('.room_info_hotel_images_wrap .hotel-images-fancybox').fancybox();
                $('.room_info_hotel_images_wrap .btn-show-more-images').removeClass('hide');
            }

            if (response.has_next_page) {
                $('.room_info_hotel_images_wrap .btn-show-more-images').show(200);
                $('.room_info_hotel_images_wrap .btn-show-more-images').attr('data-next-page', page + 1);
            } else {
                $('.room_info_hotel_images_wrap .btn-show-more-images').hide(200);
            }
        },
        complete: function() {
            $('.room_info_hotel_images_wrap .skeleton-loading-wrap').hide();
        }
    });
}

$(document).on('click', '.room_info_hotel_images_wrap .btn-show-more-images', function () {
    const page = parseInt($(this).attr('data-next-page'));
    loadHotelImagesByPage(page);
});

// get guest occupancies of the booking
function getBookingOccupancy()
{
    let bookingform = $("#booking-form");
    let occupancy;
    if (occupancy_required_for_booking) {
        let selected_occupancy = $(bookingform).find(".occupancy_info_block.selected")
        if (selected_occupancy.length) {
            occupancy = [];
            $(selected_occupancy).each(function(ind, element) {
                if (parseInt($(element).find('.num_adults').val())) {
                    let child_ages = [];
                    $(element).find('.guest_child_age').each(function(index) {
                        if ($(this).val() > -1) {
                            child_ages.push($(this).val());
                        }
                    });
                    if ($(element).find('.num_children').val()) {
                        if (child_ages.length != $(element).find('.num_children').val()) {
                            $(bookingform).find('.booking_occupancy_wrapper').parent().addClass('open')
                            occupancy = false;
                            return false;
                        }
                    }
                    occupancy.push({
                        'adults': $(element).find('.num_adults').val(),
                        'children': $(element).find('.num_children').val(),
                        'child_ages': child_ages
                    });
                } else {
                    // $(bookingform).find('.booking_occupancy_wrapper').parent().addClass('open')
                    occupancy = false;
                    return false;
                }
            });
        } else {
            // $(bookingform).find('.booking_occupancy_wrapper').parent().addClass('open')
            occupancy = false;
        }
    } else {
        occupancy = parseInt($(bookingform).find(".quantity_wanted").val());
    }

    return occupancy;
}