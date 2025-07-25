<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CartControllerCore extends FrontController
{
    public $php_self = 'cart';

    protected $id_product;
    protected $id_product_attribute;
    protected $id_address_delivery;
    protected $customization_id;
    protected $qty;
    public $ssl = true;

    protected $ajax_refresh = false;

    /**
     * This is not a public page, so the canonical redirection is disabled
     *
     * @param string $canonicalURL
     */
    public function canonicalRedirection($canonicalURL = '')
    {
    }

    /**
     * Initialize cart controller
     * @see FrontController::init()
     */
    public function init()
    {
        parent::init();

        // Send noindex to avoid ghost carts by bots
        header('X-Robots-Tag: noindex, nofollow', true);

        // Get page main parameters
        $this->id_product = (int)Tools::getValue('id_product', null);
        $this->id_product_attribute = (int)Tools::getValue('id_product_attribute', Tools::getValue('ipa'));
        $this->customization_id = (int)Tools::getValue('id_customization');
        $this->qty = abs((int) Tools::getValue('qty', 1));
        $this->id_hotel = abs((int) Tools::getValue('id_hotel', 0));
        $this->id_address_delivery = (int)Tools::getValue('id_address_delivery');
    }

    public function postProcess()
    {
        // Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
        if ($this->context->cookie->exists() && !$this->errors && !($this->context->customer->isLogged() && !$this->isTokenValid())) {
            if (Tools::getIsset('add') || Tools::getIsset('update')) {
                $this->processChangeProductInCart();
                CheckoutProcess::refreshCheckoutProcess();
            } elseif (Tools::getIsset('delete')) {
                $this->processDeleteProductInCart();
                CheckoutProcess::refreshCheckoutProcess();
            } elseif (Tools::getIsset('changeAddressDelivery')) {
                $this->processChangeProductAddressDelivery();
                CheckoutProcess::refreshCheckoutProcess();
            } elseif (Tools::getIsset('allowSeperatedPackage')) {
                $this->processAllowSeperatedPackage();
                CheckoutProcess::refreshCheckoutProcess();
            } elseif (Tools::getIsset('duplicate')) {
                $this->processDuplicateProduct();
                CheckoutProcess::refreshCheckoutProcess();
            } elseif (Tools::getIsset('updateServiceProduct')) {
                $this->processUpdateServiceProduct();
                CheckoutProcess::refreshCheckoutProcess();
            }
            // Make redirection
            if (!$this->errors && !$this->ajax) {
                $queryString = Tools::safeOutput(Tools::getValue('query', null));
                if ($queryString && !Configuration::get('PS_CART_REDIRECT')) {
                    Tools::redirect('index.php?controller=search&search='.$queryString);
                }

                // Redirect to previous page
                if (isset($_SERVER['HTTP_REFERER'])) {
                    preg_match('!http(s?)://(.*)/(.*)!', $_SERVER['HTTP_REFERER'], $regs);
                    if (isset($regs[3]) && !Configuration::get('PS_CART_REDIRECT')) {
                        $url = preg_replace('/(\?)+content_only=1/', '', $_SERVER['HTTP_REFERER']);
                        Tools::redirect($url);
                    }
                }

                Tools::redirect('index.php?controller=order&'.(isset($this->id_product) ? 'ipa='.$this->id_product : ''));
            }
        } elseif (!$this->isTokenValid()) {
            if (Tools::getValue('ajax')) {
                $this->ajaxDie(json_encode(array(
                    'hasError' => true,
                    'errors' => array(Tools::displayError('Impossible to add the product to the cart. Please refresh page.')),
                )));
            } else {
                Tools::redirect('index.php');
            }
        }
    }

    /**
     * This process delete a product from the cart
     */
    protected function processDeleteProductInCart()
    {
        $customization_product = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'customization`
		WHERE `id_cart` = '.(int)$this->context->cart->id.' AND `id_product` = '.(int)$this->id_product.' AND `id_customization` != '.(int)$this->customization_id);

        $product = new Product((int)$this->id_product);
        if (count($customization_product)) {
            if ($this->id_product_attribute > 0) {
                $minimal_quantity = (int)ProductAttribute::getAttributeMinimalQty($this->id_product_attribute);
            } else {
                $minimal_quantity = (int)$product->minimal_quantity;
            }

            $total_quantity = 0;
            foreach ($customization_product as $custom) {
                $total_quantity += $custom['quantity'];
            }

            if ($total_quantity < $minimal_quantity) {
                $this->ajaxDie(json_encode(array(
                        'hasError' => true,
                        'errors' => array(sprintf(Tools::displayError('You must add %d minimum quantity', !Tools::getValue('ajax')), $minimal_quantity)),
                )));
            }
        }

        if ($product->booking_product) {
            // delete booking data from hotel booking table(do not delete from ps cart here)
            $objCartBooking = new HotelCartBookingData();
            $objCartBooking->deleteCartBookingData($this->context->cart->id, $this->id_product, 0, 0, 0, 1);
            $result = $this->context->cart->deleteProduct($this->id_product, $this->id_product_attribute, $this->customization_id, $this->id_address_delivery);

            // now get updated available rooms
            $date_from = Tools::getValue('dateFrom');
            $date_to = Tools::getValue('dateTo');
            $date_from = date("Y-m-d H:i:s", strtotime($date_from));
            $date_to = date("Y-m-d H:i:s", strtotime($date_to));
            $objRoomType = new HotelRoomType();
            if ($roomTypeInfo = $objRoomType->getRoomTypeInfoByIdProduct($this->id_product)) {
                if ($id_hotel = $roomTypeInfo['id_hotel']) {
                    $objBookingDetail = new HotelBookingDetail();
                    $bookingParams = array(
                        'date_from' => $date_from,
                        'date_to' => $date_to,
                        'hotel_id' => $id_hotel,
                        'id_room_type' => $this->id_product,
                        'only_search_data' => 1,
                    );
                    if ($hotelRoomData = $objBookingDetail->dataForFrontSearch($bookingParams)) {
                        $total_available_rooms = $hotelRoomData['stats']['num_avail'];
                    }
                }
            }
            if ($result) {
                $this->context->cookie->avail_rooms = $total_available_rooms;
            }
        } else {
            $idProductOption = Tools::getValue('id_product_option', null);
            if ($product->selling_preference_type == Product::SELLING_PREFERENCE_STANDALONE) {
                $objServiceProductCartDetail = new ServiceProductCartDetail();
                $result = $objServiceProductCartDetail->removeCartServiceProduct(
                    $this->context->cart->id,
                    $this->id_product,
                    false,
                    false,
                    false,
                    $idProductOption ? $idProductOption : null
                );

            } elseif ($product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE
                || $product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE_AND_WITH_ROOM_TYPE
            ) {
                $objServiceProductCartDetail = new ServiceProductCartDetail();

                $result = $objServiceProductCartDetail->removeCartServiceProduct(
                    $this->context->cart->id,
                    $this->id_product,
                    false,
                    $this->id_hotel,
                    false,
                    $idProductOption ? $idProductOption : null
                );
            }
        }

        if ($result) {
            Hook::exec('actionAfterDeleteProductInCart', array(
                'id_cart' => (int)$this->context->cart->id,
                'id_product' => (int)$this->id_product,
                'id_product_attribute' => (int)$this->id_product_attribute,
                'customization_id' => (int)$this->customization_id,
                'id_address_delivery' => (int)$this->id_address_delivery
            ));

            if (!Cart::getNbProducts((int)$this->context->cart->id)) {
                $this->context->cart->setDeliveryOption(null);
                $this->context->cart->gift = 0;
                $this->context->cart->gift_message = '';
                $this->context->cart->update();
            }
        }

        $removed = CartRule::autoRemoveFromCart();
        CartRule::autoAddToCart();
        if (count($removed) && (int)Tools::getValue('allow_refresh')) {
            $this->ajax_refresh = true;
        }
    }

    protected function processChangeProductAddressDelivery()
    {
        if (!Configuration::get('PS_ALLOW_MULTISHIPPING')) {
            return;
        }

        $old_id_address_delivery = (int)Tools::getValue('old_id_address_delivery');
        $new_id_address_delivery = (int)Tools::getValue('new_id_address_delivery');

        if (!count(Carrier::getAvailableCarrierList(new Product($this->id_product), null, $new_id_address_delivery))) {
            $this->ajaxDie(json_encode(array(
                'hasErrors' => true,
                'error' => Tools::displayError('It is not possible to deliver this product to the selected address.', false),
            )));
        }

        $this->context->cart->setProductAddressDelivery(
            $this->id_product,
            $this->id_product_attribute,
            $old_id_address_delivery,
            $new_id_address_delivery
        );
    }

    protected function processAllowSeperatedPackage()
    {
        if (!Configuration::get('PS_SHIP_WHEN_AVAILABLE')) {
            return;
        }

        if (Tools::getValue('value') === false) {
            $this->ajaxDie('{"error":true, "error_message": "No value setted"}');
        }

        $this->context->cart->allow_seperated_package = (bool)Tools::getValue('value');
        $this->context->cart->update();
        $this->ajaxDie('{"error":false}');
    }

    protected function processDuplicateProduct()
    {
        if (!Configuration::get('PS_ALLOW_MULTISHIPPING')) {
            return;
        }

        if (!$this->context->cart->duplicateProduct(
            $this->id_product,
            $this->id_product_attribute,
            $this->id_address_delivery,
            (int)Tools::getValue('new_id_address_delivery')
        )) {
            //$error_message = $this->l('Error durring product duplication');
            // For the moment no translations
            $error_message = 'Error durring product duplication';
        }
    }

    /**
     * This process add or update a product in the cart
     */
    protected function processChangeProductInCart()
    {
        $mode = (Tools::getIsset('update') && $this->id_product) ? 'update' : 'add';
        $operator = Tools::getValue('op', 'up');
        $id_cart = $this->context->cart->id;
        $id_guest = $this->context->cart->id_guest;

        if (!$this->id_product) {
            $this->errors[] = Tools::displayError('Product not found', !Tools::getValue('ajax'));
        }

        $product = new Product($this->id_product, true, $this->context->language->id);
        if (!$product->id || !$product->active || !$product->checkAccess($this->context->cart->id_customer)) {
            $this->errors[] = Tools::displayError('This product is no longer available.', !Tools::getValue('ajax'));
            return;
        }

        if ($product->booking_product) {
            $occupancyRequiredForBooking = false;
            if (Configuration::get('PS_FRONT_ROOM_UNIT_SELECTION_TYPE') == HotelBookingDetail::PS_ROOM_UNIT_SELECTION_TYPE_OCCUPANCY) {
                $occupancyRequiredForBooking = true;
            }

            if ($occupancyRequiredForBooking && $operator == 'up') {
                if ($occupancy = json_decode(Tools::getValue('occupancy'), true)) {
                    $this->qty = count($occupancy);
                } else {
                    $this->errors[] = Tools::displayError('Invalid occupnacy.');
                }
            } else {
                $occupancy = Tools::getValue('qty');
                $this->qty = $occupancy;
            }
        }

        if ($this->qty == 0) {
            $this->errors[] = Tools::displayError('Null quantity.', !Tools::getValue('ajax'));
        }

        // check if the product is booking product, if booking product will check room availability
        if ($product->booking_product) {
            // By Webkul : This code is to check available quantity of Room before adding it to cart.
            // only check availability if qty is increasing
            if (!$this->errors) {
                $objRoomType = new HotelRoomType();
                if ($roomTypeInfo = $objRoomType->getRoomTypeInfoByIdProduct($this->id_product)) {
                    $date_from = Tools::getValue('dateFrom');
                    $date_to = Tools::getValue('dateTo');
                    $date_from = date("Y-m-d H:i:s", strtotime($date_from));
                    $date_to = date("Y-m-d H:i:s", strtotime($date_to));
                    $serviceProducts = json_decode(Tools::getValue('serviceProducts'),true);

                    // valdiate occupancy if providede
                    if ($operator == 'up' && $occupancyRequiredForBooking) {
                        foreach($occupancy as $key => $roomOccupancy) {
                            if (!isset($roomOccupancy['adults']) || !$roomOccupancy['adults'] || !Validate::isUnsignedInt($roomOccupancy['adults'])) {
                                $this->errors[] = sprintf(Tools::displayError('Invalid number of adults for Room %s.'), ($key + 1));
                            }
                            if (isset($roomOccupancy['children'])) {
                                if (!Validate::isUnsignedInt($roomOccupancy['children'])) {
                                    $this->errors[] = sprintf(Tools::displayError('Invalid number of children for Room %s.'), ($key + 1));
                                }
                                if ($roomOccupancy['children'] > 0) {
                                    if (!isset($roomOccupancy['child_ages']) || ($roomOccupancy['children'] != count($roomOccupancy['child_ages'])) || !is_array($roomOccupancy['child_ages'])) {
                                        $this->errors[] = sprintf(Tools::displayError('Please provide all children age for Room %s.'), ($key + 1));
                                    } else {
                                        if (is_array($roomOccupancy['child_ages'])) {
                                            foreach($roomOccupancy['child_ages'] as $childAge) {
                                                if (!Validate::isUnsignedInt($childAge)) {
                                                    $this->errors[] = sprintf(Tools::displayError('Invalid children age for Room %s.'), ($key + 1));
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (!count($this->errors)) {
                            foreach($occupancy as $key => $roomOccupancy) {
                                if ($roomOccupancy['adults'] > $roomTypeInfo['max_adults']) {
                                    $this->errors[] = sprintf(Tools::displayError('Room %s cannot have adults more than %s adults'), $key + 1, $roomTypeInfo['max_adults']);
                                }
                                if ($roomOccupancy['children'] > $roomTypeInfo['max_children']) {
                                    $this->errors[] = sprintf(Tools::displayError('Room %s cannot have children more than %s children'), $key + 1, $roomTypeInfo['max_children']);
                                }
                                if ($roomOccupancy['adults'] + $roomOccupancy['children'] > $roomTypeInfo['max_guests']) {
                                    $this->errors[] = sprintf(Tools::displayError('Room %s cannot have total guests more than %s'), $key + 1, $roomTypeInfo['max_guests']);
                                }
                            }
                        }
                    }

                    if ($id_hotel = $roomTypeInfo['id_hotel']) {
                        if (strtotime($date_from) < strtotime(date('Y-m-d'))) {
                            $this->errors[] = Tools::displayError('You can\'t book room before current date');
                        } elseif (strtotime($date_from) >= strtotime($date_to)) {
                            $this->errors[] = Tools::displayError('Check-out date must be after check-in date');
                        } elseif ($maxOrdDate = HotelOrderRestrictDate::getMaxOrderDate($id_hotel)) {
                            // Check Order restrict condition before adding in to cart
                            if (strtotime('-1 day', strtotime($maxOrdDate)) < strtotime($date_from)
                                || strtotime($maxOrdDate) < strtotime($date_to)
                            ) {
                                $maxOrdDate = date('d-m-Y', strtotime($maxOrdDate));
                                $this->errors[] = Tools::displayError('You can\'t book room after date '.$maxOrdDate);
                            }
                        }

                        if (!$this->errors) {
                            $objBookingDetail = new HotelBookingDetail();
                            $num_days = HotelHelper::getNumberOfDays($date_from, $date_to);
                            $req_rm = $this->qty;
                            $this->qty = $this->qty * (int) $num_days;
                            $objBookingDetail = new HotelBookingDetail();
                            $bookingParams = array(
                                'date_from' => $date_from,
                                'date_to' => $date_to,
                                'hotel_id' => $id_hotel,
                                'id_room_type' => $this->id_product,
                                'only_search_data' => 1,
                                'id_cart' => $id_cart,
                                'id_guest' => $id_guest,
                            );
                            if ($hotelRoomData = $objBookingDetail->dataForFrontSearch($bookingParams)) {
                                if (isset($hotelRoomData['stats']['num_avail'])) {
                                    $total_available_rooms = $hotelRoomData['stats']['num_avail'];
                                    if (Tools::getValue('op', 'up') == 'up') {
                                        if ($total_available_rooms < $req_rm) {
                                            die(json_encode(array('status' => 'unavailable_quantity', 'avail_rooms' => $total_available_rooms)));
                                        } else {
                                            // validate service products if available
                                            if ($serviceProducts) {
                                                $objRoomTypeServiceProduct = new RoomTypeServiceProduct();
                                                foreach ($serviceProducts as $key => $serviceProduct) {
                                                    if (!$objRoomTypeServiceProduct->isRoomTypeLinkedWithProduct($this->id_product, $serviceProduct['id_product'])) {
                                                        unset($serviceProducts[$key]);
                                                    } else {
                                                        if (Validate::isLoadedObject($objServiceProduct = new Product($serviceProduct['id_product']))) {
                                                            if (!$objServiceProduct->allow_multiple_quantity && $serviceProduct['quantity'] > 1) {
                                                                $serviceProducts[$key]['quantity'] = 1;
                                                            } else if ($objServiceProduct->max_quantity && $objServiceProduct->max_quantity < $serviceProduct['quantity'] ) {
                                                                $serviceProducts[$key]['quantity'] = $objServiceProduct->max_quantity;
                                                            }
                                                        } else {
                                                            $response['error'] = Tools::displayError('Service not Found');
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $this->errors[] = Tools::displayError('Rooms are unavailable. Please try with different dates');
                                }
                            } else {
                                $this->errors[] = Tools::displayError('Rooms are unavailable. Please try with different dates');
                            }
                        }
                    } else {
                        die(json_encode(array('status' => 'failed3')));
                    }
                } else {
                    die(json_encode(array('status' => 'failed4')));
                }
            }
        } else {
            $objHotelCartBookingData = new HotelCartBookingData();
            $idProductOption = Tools::getValue('id_product_option');
            if ($product->selling_preference_type == Product::SELLING_PREFERENCE_STANDALONE) {
                // if can be added without room type then we can directly add product in cart.
                if ($operator == 'up') {
                    if ($product->allow_multiple_quantity) {
                        if ($id_cart) {
                            $quantityInCart = Cart::getProductQtyInCart(
                                $id_cart,
                                $this->id_product
                            );
                        }
                        $finalQuantity = $this->qty;
                        if (isset($quantityInCart)) {
                            $finalQuantity += $quantityInCart;
                        }
                        if ($product->max_quantity && $finalQuantity > $product->max_quantity) {
                            $this->errors[] = Tools::displayError(sprintf('You cannot add more than %d quantity for this product in the cart.', $product->max_quantity));
                        }
                    } else {
                        if ($id_cart) {
                            if (cart::getProductQtyInCart($id_cart, $product->id)) {
                                $this->errors[] = Tools::displayError('You can only order one quantity for this product.');
                            }
                        }
                    }
                    if (ServiceProductOption::productHasOptions($this->id_product)) {
                        if (!$idProductOption) {
                            $this->errors[] = Tools::displayError('Cannot add service without a option.');
                        } else {
                            if (!ServiceProductOption::productHasOptions($this->id_product, $idProductOption)) {
                                $this->errors[] = Tools::displayError('The selected option is not available.');
                            }
                        }
                    }
                }
            } elseif ($product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE
                || $product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE_AND_WITH_ROOM_TYPE
            ) {
                $id_hotel = Tools::getValue('id_hotel');
                if ($operator == 'up') {
                    if ($id_hotel) {
                        $objServiceProductCartDetail = new ServiceProductCartDetail();
                        $productCartDetail = array();
                        if ($id_cart) {
                            if ($cartDetail = $objServiceProductCartDetail->getServiceProductsInCart(
                                (int) $id_cart,
                                [$product->selling_preference_type],
                                $id_hotel,
                                null,
                                null,
                                $this->id_product
                            )) {
                                $productCartDetail = array_shift($cartDetail);
                            }
                        }
                        if ($product->allow_multiple_quantity) {
                            $finalQuantity = $this->qty;
                            if (isset($productCartDetail) && $productCartDetail) {
                                $finalQuantity += $productCartDetail['quantity'];
                            }
                            if ($product->max_quantity && $finalQuantity > $product->max_quantity) {
                                $this->errors[] = Tools::displayError(sprintf('You cannot add more than %d quantity for this product in the cart.', $product->max_quantity));
                            }
                        } elseif ($productCartDetail) {
                            $this->errors[] = Tools::displayError('You can only order one quantity for this product.');
                        }
                        if (ServiceProductOption::productHasOptions($this->id_product)) {
                            if (!$idProductOption) {
                                $this->errors[] = Tools::displayError('Cannot add product without a option.');
                            } else {
                                if (!ServiceProductOption::productHasOptions($this->id_product, $idProductOption)) {
                                    $this->errors[] = Tools::displayError('The selected option is not available.');
                                }
                            }
                        }
                    } else {
                        $this->errors[] = Tools::displayError('Cannot add product without a hotel.');
                    }
                }
            } else {
                $this->errors[] = Tools::displayError('Can not add product without room in cart');
            }
        }

        $cart_products = $this->context->cart->getProducts();
        $qty_to_check = $this->qty;
        if (is_array($cart_products)) {
            foreach ($cart_products as $cart_product) {
                if ((!isset($this->id_product_attribute) || $cart_product['id_product_attribute'] == $this->id_product_attribute) &&
                    (isset($this->id_product) && $cart_product['id_product'] == $this->id_product)) {
                    $qty_to_check = $cart_product['cart_quantity'];

                    if (Tools::getValue('op', 'up') == 'down') {
                        $qty_to_check -= $this->qty;
                    } else {
                        $qty_to_check += $this->qty;
                    }

                    break;
                }
            }
        }

        // Check product quantity availability
        if ($this->id_product_attribute) {
            if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !ProductAttribute::checkAttributeQty($this->id_product_attribute, $qty_to_check)) {
                $this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
            }
        } elseif ($product->hasAttributes()) {
            $minimumQuantity = ($product->out_of_stock == 2) ? !Configuration::get('PS_ORDER_OUT_OF_STOCK') : !$product->out_of_stock;
            $this->id_product_attribute = Product::getDefaultAttribute($product->id, $minimumQuantity);
            // @todo do something better than a redirect admin !!
            if (!$this->id_product_attribute) {
                Tools::redirectAdmin($this->context->link->getProductLink($product));
            } elseif (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !ProductAttribute::checkAttributeQty($this->id_product_attribute, $qty_to_check)) {
                $this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
            }
        } elseif (!$product->checkQty($qty_to_check)) {
            $this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
        }

        // If no errors, process product addition
        if (!$this->errors && $mode == 'add') {
            // Add cart if no cart found
            if (!$this->context->cart->id) {
                if (Context::getContext()->cookie->id_guest) {
                    $guest = new Guest(Context::getContext()->cookie->id_guest);
                    $this->context->cart->mobile_theme = $guest->mobile_theme;
                }
                $this->context->cart->add();
                if ($this->context->cart->id) {
                    $this->context->cookie->id_cart = (int)$this->context->cart->id;
                }
            }

            // Check customizable fields
            if (!$product->hasAllRequiredCustomizableFields() && !$this->customization_id) {
                $this->errors[] = Tools::displayError('Please fill in all of the required fields, and then save your customizations.', !Tools::getValue('ajax'));
            }
            if (!$this->errors) {
                $cart_rules = $this->context->cart->getCartRules();
                $available_cart_rules = CartRule::getCustomerCartRules($this->context->language->id, (isset($this->context->customer->id) ? $this->context->customer->id : 0), true, true, true, $this->context->cart, false, true);

                /*------  BY Webkul ------*/
                /*
                * To add Rooms in hotel cart
                */
                if ($product->booking_product) {
                    $objHotelCartBookingData = new HotelCartBookingData();
                    $roomDemand = json_decode(Tools::getValue('roomDemands'), true);
                    $roomDemand = json_encode($roomDemand);
                    $availQty = $total_available_rooms;
                    $update_quantity = $objHotelCartBookingData->updateCartBooking(
                        $this->id_product,
                        $occupancy,
                        $operator,
                        $id_hotel,
                        0,
                        $date_from,
                        $date_to,
                        $roomDemand,
                        $serviceProducts,
                        $id_cart,
                        $id_guest
                    );
                    if ($operator == 'up') {
                        $availQty = $total_available_rooms - $req_rm;
                        $this->context->cookie->currentAddedProduct = json_encode(array(
                            'date_from' => $date_from,
                            'date_to' => $date_to,
                            'id_product' => $this->id_product,
                            'occupancy' => $occupancy,
                            'room_demand' => $roomDemand,
                            'req_rm' => $req_rm
                        ));
                    } else {
                        $availQty = $total_available_rooms + $req_rm;
                    }
                    $this->context->cookie->avail_rooms = $availQty;
                } elseif ($product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE
                    || $product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE_AND_WITH_ROOM_TYPE
                ) {
                    $objServiceProductCartDetail = new ServiceProductCartDetail();
                    $update_quantity = $objServiceProductCartDetail->updateCartServiceProduct(
                        $this->context->cart->id,
                        $this->id_product,
                        $operator,
                        $this->qty,
                        $id_hotel,
                        false,
                        isset($idProductOption) ? $idProductOption : null
                    );
                    if ($operator == 'up') {
                        $this->context->cookie->currentAddedProduct = json_encode(array(
                            'id_product' => $this->id_product,
                            'id_hotel' => $id_hotel,
                            'qty' => $this->qty,
                            'id_product_option' => isset($idProductOption) ? $idProductOption : null
                        ));
                    }
                } elseif ($product->selling_preference_type == Product::SELLING_PREFERENCE_STANDALONE) {
                    $objServiceProductCartDetail = new ServiceProductCartDetail();
                    $update_quantity = $objServiceProductCartDetail->updateCartServiceProduct(
                        $this->context->cart->id,
                        $this->id_product,
                        $operator,
                        $this->qty,
                        false,
                        false,
                        isset($idProductOption) ? $idProductOption : null
                    );
                    if ($operator == 'up') {
                        $this->context->cookie->currentAddedProduct = json_encode(array(
                            'id_product' => $this->id_product,
                            'qty' => $this->qty,
                            'id_product_option' => isset($idProductOption) ? $idProductOption : null
                        ));
                    }
                }

                /*------  BY Webkul ------*/

                if ($update_quantity < 0) {
                    // If product has attribute, minimal quantity is set with minimal quantity of attribute
                    $minimal_quantity = ($this->id_product_attribute) ? ProductAttribute::getAttributeMinimalQty($this->id_product_attribute) : $product->minimal_quantity;
                    $this->errors[] = sprintf(Tools::displayError('You must add %d minimum quantity', !Tools::getValue('ajax')), $minimal_quantity);
                } elseif (!$update_quantity) {
                    $this->errors[] = Tools::displayError('You already have the maximum quantity available for this product.', !Tools::getValue('ajax'));
                } elseif ((int)Tools::getValue('allow_refresh')) {
                    // If the cart rules has changed, we need to refresh the whole cart
                    $cart_rules2 = $this->context->cart->getCartRules();
                    if (count($cart_rules2) != count($cart_rules)) {
                        $this->ajax_refresh = true;
                    } elseif (count($cart_rules2)) {
                        $rule_list = array();
                        foreach ($cart_rules2 as $rule) {
                            $rule_list[] = $rule['id_cart_rule'];
                        }
                        foreach ($cart_rules as $rule) {
                            if (!in_array($rule['id_cart_rule'], $rule_list)) {
                                $this->ajax_refresh = true;
                                break;
                            }
                        }
                    } else {
                        $available_cart_rules2 = CartRule::getCustomerCartRules($this->context->language->id, (isset($this->context->customer->id) ? $this->context->customer->id : 0), true, true, true, $this->context->cart, false, true);
                        if (count($available_cart_rules2) != count($available_cart_rules)) {
                            $this->ajax_refresh = true;
                        } elseif (count($available_cart_rules2)) {
                            $rule_list = array();
                            foreach ($available_cart_rules2 as $rule) {
                                $rule_list[] = $rule['id_cart_rule'];
                            }
                            foreach ($cart_rules2 as $rule) {
                                if (!in_array($rule['id_cart_rule'], $rule_list)) {
                                    $this->ajax_refresh = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        $removed = CartRule::autoRemoveFromCart();
        CartRule::autoAddToCart();
        if (count($removed) && (int)Tools::getValue('allow_refresh')) {
            $this->ajax_refresh = true;
        }
    }

    protected function processUpdateServiceProduct()
    {
        $operator = Tools::getValue('operator', 'up');
        $idServiceProduct = Tools::getValue('id_product');
        $idCartBooking = Tools::getValue('id_cart_booking');
        $qty = Tools::getValue('qty');

        if (Validate::isLoadedObject($objHotelCartBookingData = new HotelCartBookingData($idCartBooking))) {

            if ($operator == 'up') {
                $objRoomTypeServiceProduct = new RoomTypeServiceProduct();
                if ($objRoomTypeServiceProduct->isRoomTypeLinkedWithProduct($objHotelCartBookingData->id_product, $idServiceProduct)) {
                    // validate quanitity
                    if (Validate::isLoadedObject($objProduct = new Product($idServiceProduct))) {
                        if ($objProduct->available_for_order) {
                            if ($objProduct->allow_multiple_quantity) {
                                if (!Validate::isUnsignedInt($qty)) {
                                    $this->errors[] = Tools::displayError('The quantity you\'ve entered is invalid.');
                                } elseif ($objProduct->max_quantity && $qty > $objProduct->max_quantity) {
                                    $this->errors[] = Tools::displayError(sprintf('cannot add more than %d quantity.', $objProduct->max_quantity));
                                }
                            } else {
                                $qty = 1;
                            }
                        } else {
                            $this->errors[] = Tools::displayError('This Service is not available.');
                        }
                    } else {
                        $this->errors[] = Tools::displayError('This Service is not available.');
                    }
                } else {
                    $this->errors[] = Tools::displayError('This Service is not available with selected room.');
                }
            }

            if (empty($this->errors)) {
                $objServiceProductCartDetail = new ServiceProductCartDetail();
                if ($objServiceProductCartDetail->updateCartServiceProduct(
                    $this->context->cart->id,
                    $idServiceProduct,
                    $operator,
                    $qty,
                    false,
                    $idCartBooking
                )) {
                    $this->ajaxDie(json_encode(array(
                        'hasError' => false
                    )));
                } else {
                    $this->errors[] = Tools::displayError('Unable to update services. Please try reloading the page.');
                }

            }
        } else {
            $this->errors[] = Tools::displayError('Room not found. Please try reloading the page.');
        }
        $this->ajaxDie(json_encode(array(
            'hasError' => true,
            'errors' => $this->errors
        )));
    }

    /**
     * Remove discounts on cart
     *
     * @deprecated 1.5.3.0
     */
    protected function processRemoveDiscounts()
    {
        Tools::displayAsDeprecated();
        $this->errors = array_merge($this->errors, CartRule::autoRemoveFromCart());
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->setTemplate(_PS_THEME_DIR_.'errors.tpl');
        if (!$this->ajax) {
            parent::initContent();
        }
    }

    /**
     * Display ajax content (this function is called instead of classic display, in ajax mode)
     */
    public function displayAjax()
    {
        if ($this->errors) {
            $this->ajaxDie(json_encode(array('hasError' => true, 'errors' => $this->errors)));
        }
        if ($this->ajax_refresh) {
            $this->ajaxDie(json_encode(array('refresh' => true)));
        }

        // write cookie if can't on destruct
        $this->context->cookie->write();

        if (Tools::getIsset('summary')) {
            $result = array();
            if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                $groups = (Validate::isLoadedObject($this->context->customer)) ? $this->context->customer->getGroups() : array(1);
                if ($this->context->cart->id_address_delivery) {
                    $deliveryAddress = new Address($this->context->cart->id_address_delivery);
                }
                $id_country = (isset($deliveryAddress) && $deliveryAddress->id) ? (int)$deliveryAddress->id_country : (int)Tools::getCountry();

                Cart::addExtraCarriers($result);
            }
            $result['summary'] = $this->context->cart->getSummaryDetails(null, true);
            $result['customizedDatas'] = Product::getAllCustomizedDatas($this->context->cart->id, null, true);
            $result['HOOK_SHOPPING_CART'] = Hook::exec('displayShoppingCartFooter', $result['summary']);
            $result['HOOK_SHOPPING_CART_EXTRA'] = Hook::exec('displayShoppingCart', $result['summary']);

            foreach ($result['summary']['products'] as $key => &$product) {
                $product['quantity_without_customization'] = $product['quantity'];
                if ($result['customizedDatas'] && isset($result['customizedDatas'][(int)$product['id_product']][(int)$product['id_product_attribute']])) {
                    foreach ($result['customizedDatas'][(int)$product['id_product']][(int)$product['id_product_attribute']] as $addresses) {
                        foreach ($addresses as $customization) {
                            $product['quantity_without_customization'] -= (int)$customization['quantity'];
                        }
                    }
                }
            }
            if ($result['customizedDatas']) {
                Product::addCustomizationPrice($result['summary']['products'], $result['customizedDatas']);
            }

            $json = '';
            Hook::exec('actionCartListOverride', array('summary' => $result, 'json' => &$json));
            $this->ajaxDie(json_encode(array_merge($result, (array)json_decode($json, true))));
        }
        // @todo create a hook
        elseif (file_exists(_PS_MODULE_DIR_.'/blockcart/blockcart-ajax.php')) {
            require_once(_PS_MODULE_DIR_.'/blockcart/blockcart-ajax.php');
        }
    }
}
