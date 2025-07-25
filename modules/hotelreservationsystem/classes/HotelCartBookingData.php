<?php
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

class HotelCartBookingData extends ObjectModel
{
    public $id;
    public $id_cart;
    public $id_guest;
    public $id_order;
    public $id_customer;
    public $id_currency;
    public $id_product;
    public $id_room;
    public $id_hotel;
    public $quantity;
    public $booking_type;
    public $comment;
    public $is_refunded;
    public $is_back_order;
    public $date_from;
    public $date_to;
    public $adults;
    public $children;
    public $child_ages;
    public $extra_demands;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'htl_cart_booking_data',
        'primary' => 'id',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_room' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_hotel' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'booking_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'comment' => array('type' => self::TYPE_STRING),
            'is_refunded' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'is_back_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_from' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_to' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'adults' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'children' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'child_ages' => array('type' => self::TYPE_STRING),
            'extra_demands' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    protected $webserviceParameters = array(
        'objectsNodeName' => 'cart_bookings',
        'objectNodeName' => 'booking',
        'fields' => array(
            'id_hotel' => array(
                'xlink_resource' => array(
                    'resourceName' => 'hotels',
                )
            ),
            'id_product' => array(
                'xlink_resource' => array(
                    'resourceName' => 'products',
                )
            ),
            'id_room' => array(
                'xlink_resource' => array(
                    'resourceName' => 'hotel_rooms',
                )
            ),
        ),
        'associations' => array(
            'extra_demands' => array(
                'setter' => false,
                'resource' => 'extra_demand',
                'fields' => array(
                    'id_global_demand' => array(
                        'xlink_resource' => array(
                            'resourceName' => 'extra_demands',
                        )
                    ),
                    'id_option' => array(
                        'xlink_resource' => array(
                            'resourceName' => 'demand_advance_options',
                        )
                    )
                )
            ),
        ),
    );

    /**
     * [getCountRoomsInCart :: To get How much number of the rooms available in the cart of a customer or guest].
     *
     * @param [int] $id_cart [Id of the cart]
     * @param [int] $guest   [Guest Id of the customer]
     *
     * @return [int|boolean] [Returns number of the rooms available in the cart of a customer or guest if no rooms available returns 0]
     */
    public function getCountRoomsInCart($id_cart, $guest)
    {
        $sql = 'SELECT Count(`id`) FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart` = '.(int) $id_cart.' AND `id_guest` = '.(int) $guest.' AND `id_order` = 0';
        $count_rooms = Db::getInstance()->getValue($sql);

        if ($count_rooms) {
            return $count_rooms;
        } else {
            return 0;
        }
    }

    /**
     * [getCartBookingDetailsByIdCartIdGuest :: To get Booking data from table by id cart and id guest of the customer].
     *
     * @param [int] $id_cart  [Id of the cart which booking information you want to get]
     * @param [int] $id_guest [Id guest of the customer]
     *
     * @return [array|false] [If required information found then returns the array of the information else returns false]
     */
    public function getCartBookingDetailsByIdCartIdGuest($id_cart, $id_guest, $id_lang)
    {
        $sql = 'SELECT cbd.id AS id_cart_book_data, cbd.id_cart, cbd.id_guest, cbd.id_product, cbd.id_room, cbd.id_hotel, cbd.quantity, cbd.date_from, cbd.date_to, ri.room_num, pl.name AS room_type
                FROM `'._DB_PREFIX_.'htl_cart_booking_data` AS cbd
                INNER JOIN `'._DB_PREFIX_.'htl_room_information` AS ri ON (cbd.id_room = ri.id)
                INNER JOIN `'._DB_PREFIX_.'product_lang` AS pl ON (cbd.id_product = pl.id_product AND pl.`id_lang`='.(int) $id_lang.')
                WHERE cbd.id_cart = '.(int) $id_cart.' AND cbd.id_guest = '.(int) $id_guest;
        $cart_book_data = Db::getInstance()->executeS($sql);

        if ($cart_book_data) {
            foreach ($cart_book_data as $key => $value) {
                // By webkul New way to calculate product prices with feature Prices
                $roomTypeDateRangePrice = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                    $value['id_product'],
                    $value['date_from'],
                    $value['date_to'],
                    0,
                    (int)Group::getCurrent()->id,
                    $id_cart,
                    $id_guest,
                    $value['id_room']
                );
                $cart_book_data[$key]['amt_with_qty'] = $roomTypeDateRangePrice['total_price_tax_excl'];
            }

            return $cart_book_data;
        } else {
            return false;
        }
    }

    /**
     * [getOnlyCartBookingData description].
     *
     * @param [type] $id_cart     [description]
     * @param [type] $id_guest    [description]
     * @param [type] $id_product  [description]
     * @param int    $id_customer [description]
     *
     * @return [type] [description]
     */
    public function getOnlyCartBookingData($id_cart, $id_guest, $id_product, $id_customer = 0)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart` = '.(int) $id_cart.' AND `id_product` = '.(int) $id_product;

        if ($id_customer) {
            $sql .=  ' AND `id_customer` = '.(int) $id_customer;
        }

        $cart_book_data = Db::getInstance()->executeS($sql);

        if ($cart_book_data) {
            return $cart_book_data;
        } else {
            return false;
        }
    }

    /**
     * [getCountRoomsByIdCartIdProduct :: To get Number of rooms for a date range(From $date_from TO $date_to) in a cart(which cat cart id is $id_cart) belongs to the same room type(product)].
     *
     * @param [int]  $id_cart    [Id of the cart]
     * @param [int]  $id_product [Id of the product]
     * @param [date] $date_from  [Start date of the booking]
     * @param [date] $date_to    [End date of the booking]
     *
     * @return [int|false] [If data found Returns the number for a date range(From $date_from TO $date_to) in a cart(which cat 			cart id is $id_cart) belongs to a room type(product) else returns false]
     */
    public function getCountRoomsByIdCartIdProduct($id_cart, $id_product, $date_from, $date_to)
    {
        $sql = 'SELECT Count(`id`) FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart` = '.(int) $id_cart.' AND `id_product` = '.(int) $id_product." AND `date_from` <= '".pSql($date_from)."' AND `date_to` >= '".pSQL($date_to)."'";

        $count_rooms = Db::getInstance()->getValue($sql);

        if ($count_rooms) {
            return $count_rooms;
        } else {
            return false;
        }
    }

    /**
     * Deprecated
     * [deleteRowById :: To delete Row from the table by its id(primary key)].
     *
     * @param [int] $id [Id(primary key) of the table which row has to be deleted]
     *
     * @return [type] [Returns true if deleted successfully else returns false]
     */
    public function deleteRowById($id)
    {
        $objHotelCartBookingData = new self($id);
        if (Validate::isLoadedObject($objHotelCartBookingData)) {
            $objHotelCartBookingData->delete();
            return true;
        }

        return false;
    }

    /**
     * [getCartCurrentDataByCartId :: To get booking information of the cart by cart id].
     *
     * @param [int] $cart_id [Id of the cart]
     *
     * @return [array|false] [If data found Returns the array containing the information of the cart of the passed cart id else returns false]
     */
    public function getCartCurrentDataByCartId($cart_id)
    {
        $result = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart`='.(int)$cart_id);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * [getLastAddedData :: To get booking information of the cart by cart id].
     *
     * @param [int] $cart_id [Id of the cart]
     *
     * @return [array|false] [If data found Returns the array containing the information of the cart of the passed cart id else returns false]
     */
    public function getLastAddedData($cart_id)
    {
        if ($cart_id) {
            return Db::getInstance()->getRow('
                SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data`
                WHERE `id_cart`='.(int) $cart_id.' ORDER BY `date_add` DESC
            ');
        }

        return false;
    }

    /**
     * [getCartCurrentDataByCartId :: To get booking information of the cart by Order id].
     *
     * @param [int] $id_order [Id of the order]
     *
     * @return [array|false] [If data found Returns the array containing the information of the cart of the passed order id else returns false]
     */
    public function getCartCurrentDataByOrderId($id_order)
    {
        $result = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_order`='.(int) $id_order);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /*public function deleteRowHotelCustomerCartDetail($id)
    {
        $deleted = Db::getInstance()->delete('htl_cart_booking_data','id='.$id);
        if ($deleted)
            return true;
        return false;
    }

    public function deleteCartDataById($id)
    {
        $deleted = Db::getInstance()->delete('htl_cart_booking_data','id='.$id);
        if ($deleted)
            return true;
        return false;
    }*/

    /**
     * [changeProductDataByRoomId :: To delete the room from the cart when added from the admin side to the cart and update the 								quantity of the product in prestashop cart table].
     *
     * @param [int] $roomid     [Id of the room]
     * @param [int] $id_product [Id of the room type(product)]
     * @param [int] $days_diff  [Number of days between start date and end date of the booking]
     * @param [int] $cart_id    [Id of the cart which products information has to be changed]
     *
     * @return [boolean] [If cart updated with quantity successfully returns true else returns false]
     */
    public function changeProductDataByRoomId($idRoom, $idProduct, $daysDiff, $idCart)
    {
        $cartProductQuantity = Db::getInstance()->getValue('SELECT `quantity` FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart`='.(int) $idCart.' AND `id_product`='.(int) $idProduct);
        $newQuantity = $cartProductQuantity - $daysDiff;

        if ($newQuantity > 0) {
            return Db::getInstance()->update('cart_product', array('quantity' => $newQuantity), '`id_cart`='.(int) $idCart.' AND `id_product`='.(int) $idProduct);
        } else {
            return Db::getInstance()->delete('cart_product', '`id_cart`='.(int) $idCart.' AND `id_product`='.(int) $idProduct);
        }
    }

    /**
     * delete rooms from the cart
     * @param integer $idCart
     * @param integer $idProduct
     * @param integer $idRoom
     * @param integer $dateFrom
     * @param integer $dateTo
     * @param integer $updPsCart
     * @return [number of rooms deleted]
     */
    public function deleteCartBookingData(
        $idCart = 0,
        $idProduct = 0,
        $idRoom = 0,
        $dateFrom = 0,
        $dateTo = 0,
        $updPsCart = 1,
        $idOrder = 0
    ) {
        $where = '1';
        if ($idCart) {
            $where .= ' AND `id_cart`='.(int) $idCart;
        }
        if ($idProduct) {
            $where .= ' AND `id_product`='.(int) $idProduct;
        }
        if ($idRoom) {
            $where .= ' AND `id_room`='.(int) $idRoom;
        }
        if ($dateFrom) {
            $where .= ' AND `date_from`=\''.pSQL($dateFrom).'\'';
        }
        if ($dateTo) {
            $where .= ' AND `date_to`=\''.pSQL($dateTo).'\'';
        }

        $where .= ' AND `id_order`='.(int) $idOrder;
        // If rooms is deleting from cart the we need to delete the ps_cart quantity of the product from table
        // if product will delete the prestashop will handle
        $numRooms = 0;
        if ($cartBookingInfo = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE '.$where
        )) {
            $numRooms = count($cartBookingInfo);
            if ($updPsCart) {
                if (isset(Context::getContext()->controller->controller_type)) {
                    $controllerType = Context::getContext()->controller->controller_type;
                } else {
                    $controllerType = 'front';
                }
                $objServiceProductCartDetail = new ServiceProductCartDetail();
                foreach ($cartBookingInfo as $bookingRow) {
                    $idPsCart = $bookingRow['id_cart'];
                    $idPsProduct = $bookingRow['id_product'];
                    $objCart = new Cart($idPsCart);
                    $updQty = HotelHelper::getNumberOfDays($bookingRow['date_from'], $bookingRow['date_to']);
                    // remove service product for this room
                    $objServiceProductCartDetail->removeServiceProductByIdHtlCartBooking($bookingRow['id']);
                    // if room type is deleting from admin then reduce product cart quantity by updating directly table
                    if ($controllerType == 'admin' || $controllerType == 'moduleadmin') {
                        if ($cartQty = Cart::getProductQtyInCart($idPsCart, $idPsProduct)) {
                            //if room type has qty remaining in cart the then update ($cartQty - $updQty)
                            if ($updQty < $cartQty) {
                                Db::getInstance()->update(
                                    'cart_product',
                                    array('quantity' => (int)($cartQty - $updQty)),
                                    '`id_product` = '.(int)$idPsProduct.' AND `id_cart` = '.(int)$idPsCart
                                );
                            } else {
                                //if room type has no qty remaining in cart then delete row
                                Db::getInstance()->delete(
                                    'cart_product',
                                    '`id_product` = '.(int)$idPsProduct.' AND `id_cart` = '.(int)$idPsCart
                                );
                            }
                        }
                    } else {
                        // if room type is deleting from front then reduce product cart qty by $objCart->updateQty()
                        $objCart->updateQty(
                            $updQty,
                            $idPsProduct,
                            null,
                            false,
                            'down',
                            0,
                            null,
                            true
                        );
                    }
                }
            }

            // delete rows from table
            foreach ($cartBookingInfo as $row) {
                $objHotelCartBookingData = new HotelCartBookingData($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    $objHotelCartBookingData->delete();
                }
            }
        }
        // after room is removed from cart, check if any of the appled cart rules are not being used
        Cache::clear();
        CartRule::autoRemoveFromCart(Context::getContext());
        CartRule::autoAddToCart(Context::getContext());

        // return number of rooms deleted
        return true;
    }

    public function addCartBookingData(
        $id_product,
        $occupancy,
        $id_hotel,
        $date_from,
        $date_to,
        $roomDemand,
        $serviceProducts,
        $roomsAvailableList,
        $id_cart,
        $id_room = 0,
        $booking_type = HotelBookingDetail::ALLOTMENT_AUTO,
        $comment = ''
    ) {
        $chkQty = 0;
        $num_days = HotelHelper::getNumberOfDays($date_from, $date_to);
        $objServiceProductCartDetail = new ServiceProductCartDetail();
        if (defined('_PS_ADMIN_DIR_')) {
            $PS_ROOM_UNIT_SELECTION_TYPE = Configuration::get('PS_BACKOFFICE_ROOM_BOOKING_TYPE');
        } else {
            $PS_ROOM_UNIT_SELECTION_TYPE = Configuration::get('PS_FRONT_ROOM_UNIT_SELECTION_TYPE');
        }
        if ($PS_ROOM_UNIT_SELECTION_TYPE == HotelBookingDetail::PS_ROOM_UNIT_SELECTION_TYPE_OCCUPANCY) {
            $roomsRequired = count($occupancy);
        } else {
            $objRoomType = new HotelRoomType();
            $roomTypeInfo = $objRoomType->getRoomTypeInfoByIdProduct($id_product);
            $roomsRequired = $occupancy;
        }
        if (Validate::isLoadedObject($objCart = new Cart($id_cart))) {
            $res = true;
            foreach ($roomsAvailableList as $hotelRoomInfo) {
                if ($chkQty < $roomsRequired) {

                    $obj_htl_cart_booking_data = new HotelCartBookingData();
                    $obj_htl_cart_booking_data->id_cart = $objCart->id;
                    $obj_htl_cart_booking_data->id_guest = $objCart->id_guest;
                    $obj_htl_cart_booking_data->id_customer = $objCart->id_customer;
                    $obj_htl_cart_booking_data->id_currency = $objCart->id_currency;
                    $obj_htl_cart_booking_data->id_product = $id_product;
                    $obj_htl_cart_booking_data->id_room = $hotelRoomInfo['id_room'];
                    $obj_htl_cart_booking_data->id_hotel = $id_hotel;
                    $obj_htl_cart_booking_data->quantity = $num_days;
                    $obj_htl_cart_booking_data->extra_demands = $roomDemand;
                    $obj_htl_cart_booking_data->date_from = $date_from;
                    $obj_htl_cart_booking_data->date_to = $date_to;
                    $obj_htl_cart_booking_data->booking_type = $booking_type;
                    $obj_htl_cart_booking_data->comment = $comment;
                    if ($PS_ROOM_UNIT_SELECTION_TYPE == HotelBookingDetail::PS_ROOM_UNIT_SELECTION_TYPE_OCCUPANCY) {
                        $room_occupancy = array_shift($occupancy);
                        $obj_htl_cart_booking_data->adults = $room_occupancy['adults'];
                        $obj_htl_cart_booking_data->children = $room_occupancy['children'];
                        $obj_htl_cart_booking_data->child_ages = $room_occupancy['children'] ? json_encode($room_occupancy['child_ages']) : json_encode(array());
                    } else {
                        // if room is being booked without occupancy selection the for adults set base occupancy and children will be 0
                        $obj_htl_cart_booking_data->adults = $roomTypeInfo['adults'];
                        $obj_htl_cart_booking_data->children = 0;
                        $obj_htl_cart_booking_data->child_ages = json_encode(array());
                    }
                    if ($res &= $obj_htl_cart_booking_data->save()) {
                        // get auto add service product
                        if ($services = RoomTypeServiceProduct::getAutoAddServices($id_product)) {
                            foreach($services as $service) {
                                $objServiceProductCartDetail->addServiceProductInCart(
                                    $id_cart,
                                    $service['id_product'],
                                    1,
                                    false,
                                    $obj_htl_cart_booking_data->id
                                );
                            }
                        }
                        if (count($serviceProducts)) {
                            foreach($serviceProducts as $product) {
                                $objServiceProductCartDetail->addServiceProductInCart(
                                    $id_cart,
                                    $product['id_product'],
                                    $product['quantity'],
                                    false,
                                    $obj_htl_cart_booking_data->id
                                );
                            }
                        }

                    }
                    ++$chkQty;
                } else {
                    break;
                }
            }
            if ($res && $objCart->updateQty((int)($roomsRequired * $num_days), $id_product)) {
                Cache::clear();
                if ($roomsRequired == 1) {
                    return $obj_htl_cart_booking_data->id;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function updateCartBooking(
        $id_product,
        $occupancy,
        $operator,
        $id_hotel = 0,
        $id_room = 0,
        $date_from = '',
        $date_to = '',
        $roomDemand = array(),
        $serviceProducts = array(),
        $id_cart = 0,
        $id_guest = 0,
        $booking_type = HotelBookingDetail::ALLOTMENT_AUTO,
        $comment = ''
    ) {
        $context = Context::getContext();
        if (!$id_cart) {
            $id_cart = $context->cart->id;
        }
        if (!$id_guest) {
            $id_guest = $context->cart->id_guest;
        }

        if ($operator == 'up') {
            if (!$date_from && !$date_to) {
                return false;
            }

            if (!$id_hotel) {
                $objRoomType = new HotelRoomType();
                if ($roomTypeInfo = $objRoomType->getRoomTypeInfoByIdProduct($id_product)) {
                    if (!$id_hotel = $roomTypeInfo['id_hotel']) {
                        return false;
                    }
                }
            }
            $objBookingDtl = new HotelBookingDetail();
            $bookingParams = array(
                'date_from' => $date_from,
                'date_to' => $date_to,
                'hotel_id' => $id_hotel,
                'id_room_type' => $id_product,
                'only_search_data' => 1,
                'search_booked' => 0,
                'search_unavai' => 0,
                'search_partial' => 0,
                'id_cart' => $id_cart,
                'id_guest' => $id_guest,
            );
            if (defined('_PS_ADMIN_DIR_')) {
                $PS_ROOM_UNIT_SELECTION_TYPE = Configuration::get('PS_BACKOFFICE_ROOM_BOOKING_TYPE');
            } else {
                $PS_ROOM_UNIT_SELECTION_TYPE = Configuration::get('PS_FRONT_ROOM_UNIT_SELECTION_TYPE');
            }
            if ($PS_ROOM_UNIT_SELECTION_TYPE == HotelBookingDetail::PS_ROOM_UNIT_SELECTION_TYPE_OCCUPANCY) {
                $roomsRequired = count($occupancy);
            } else {
                $roomsRequired = $occupancy;
            }

            if ($hotelRoomData = $objBookingDtl->getBookingData($bookingParams)) {
                if ($id_room) {
                    if (in_array($id_room, array_column($hotelRoomData['rm_data'][$id_product]['data']['available'], 'id_room'))) {
                        $hotelRoomData['rm_data'][$id_product]['data']['available'] = array_filter(
                            $hotelRoomData['rm_data'][$id_product]['data']['available'],
                            function($room) use ($id_room) {
                                return $room['id_room'] == $id_room ? true : false;
                            }
                        );
                        $hotelRoomData['stats']['num_avail'] = count($hotelRoomData['rm_data'][$id_product]['data']['available']);
                    } else {
                        return false;
                    }
                }
                if (isset($hotelRoomData['stats']['num_avail'])) {
                    $totalAvailableRooms = $hotelRoomData['stats']['num_avail'];
                    if ($operator == 'up') {
                        if ($totalAvailableRooms >= $roomsRequired) {
                            // add rooms to cart
                            $roomsAvailableList = $hotelRoomData['rm_data'][$id_product]['data']['available'];
                            if (is_array($roomDemand)) {
                                $roomDemand = json_encode($roomDemand);
                            }
                            return $this->addCartBookingData(
                                $id_product,
                                $occupancy,
                                $id_hotel,
                                $date_from,
                                $date_to,
                                $roomDemand,
                                $serviceProducts,
                                $roomsAvailableList,
                                $id_cart,
                                $id_room,
                                $booking_type,
                                $comment
                            );
                        } else {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return $this->deleteCartBookingData(
                $id_cart,
                $id_product,
                0,
                $date_from,
                $date_to
            );
        }
    }
    /**
     * [checkExistanceOfRoomInCurrentCart :: To check Whether a room for a date range(which start date is $date_from and End date 									is $date_to) in current cart is already exists for a customer whose guest id is $id_guest].
     *
     * @param [int]  $id_room   [Id of the room]
     * @param [date] $date_from [Start date of the booking]
     * @param [date] $date_to   [End date of the booking]
     * @param [int]  $id_cart   [Id of the cart]
     * @param [int]  $id_guest  [Customer's guest Id]
     *
     * @return [int|false] [If room already exists in the cart then returns id of the row in the table where entry for this *								room is located else returns false]
     */
    public function checkExistanceOfRoomInCurrentCart($id_room, $date_from, $date_to, $id_cart, $id_guest)
    {
        $result = Db::getInstance()->getValue('SELECT id FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_room`='.(int) $id_room." AND `date_from`='".pSQL($date_from)."' AND `date_to`='".pSQL($date_to)."' AND `id_cart`=".(int) $id_cart.' AND `id_guest`='.(int) $id_guest);

        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     * [deleteCartDataByIdProductIdCart ::  To delete room from the cart(which cart id is passed) which belong to the room type(									product)(which product id is passed) and booked for the date range(hich start date is 										$date_from and end date is $date_to)].
     *
     * @param [int]  $id_cart    [Id of the cart]
     * @param [int]  $id_product [Id of the product]
     * @param [date] $date_from  [Start date of the booking]
     * @param [date] $date_to    [End date og=f the booking]
     *
     * @return [boolean] [Returns true if deleted successfully else returns false]
     */
    public function deleteCartDataByIdProductIdCart($idCart, $idProduct, $dateFrom, $dateTo)
    {
        $result = $this->getHotelCartRoomsInfoByRoomType($idCart, $idProduct, $dateFrom, $dateTo);
        if (is_array($result) && count($result)) {
            $return = true;
            foreach ($result as $row) {
                $objHotelCartBookingData = new self($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    $return &= $objHotelCartBookingData->delete();
                }
            }

            return $return;
        }

        return false;
    }

    /**
     * [deleteRoomDataFromOrderLine :: To delete room from the order line when customer deletes the room from the order line for a 										date range (which start date is $date_from and End date is $date_to) in current cart].
     *
     * @param [int]  $id_cart    [Id of the cart]
     * @param [int]  $id_guest   [Customer's guest Id]
     * @param [int]  $id_product [Id of the product]
     * @param [date] $date_from  [Start date of the booking]
     * @param [date] $date_to    [End date of the booking]
     *
     * @return [boolean] [Returns true if deleted successfully else returns false]
     */
    public function deleteRoomDataFromOrderLine($id_cart, $id_guest, $id_product, $date_from, $date_to)
    {
        // To get the num_rm
        Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart`='.(int) $id_cart.' AND `id_guest`='.(int) $id_guest.' AND `id_product`='.(int) $id_product." AND `date_from`= '".pSQL($date_from)."' AND `date_to`= '".pSQL($date_to)."'");
        $num_rm = Db::getInstance()->NumRows();

        $num_days = HotelHelper::getNumberOfDays($date_from, $date_to);

        $qty = (int) $num_rm * (int) $num_days;
        if ($qty) {
            return $this->deleteCartBookingData($id_cart, $id_product, 0, $date_from, $date_to);
        }

        return false;
    }

    /**
     * [deleteBookingCartDataNotOrderedByProductId :: To delete the data of the rooms booking from the table Which rooms were 														added to the cart but Not Ordered By there room type(product id)].
     *
     * @param [int] $id_product [Id of the product(room type)]
     *
     * @return [boolean] [If deleted successfully returns true else returns false]
     */
    public function deleteBookingCartDataNotOrderedByProductId($id_product)
    {
        $result = Db::getInstance()->executeS(
            'SELECT `id`
            FROM `'._DB_PREFIX_.'htl_cart_booking_data`
            WHERE `id_product`='.(int) $id_product.' AND `id_order`= 0'
        );

        if (is_array($result) && count($result)) {
            $return = true;
            foreach ($result as $row) {
                $objHotelCartBookingData = new self($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    $return &= $objHotelCartBookingData->delete();
                }
            }

            return $return;
        }

        return false;
    }

    /**
     * [removeBackdateRoomsFromCart :: To delete the rooms from cart whose booking date is before the current date
     *
     * @param [int] $idCart [Id of the cart]
     *
     * @return [boolean] [If removed successfully returns true else returns false]
     */
    public function removeBackdateRoomsFromCart($idCart)
    {
        if ($cartBookingData = $this->getCartCurrentDataByCartId($idCart)) {
            $res = true;
            foreach ($cartBookingData as $cartRoom) {
                if (strtotime($cartRoom['date_from']) < strtotime(date('Y-m-d'))) {
                    $res = $res && $this->deleteRoomDataFromOrderLine(
                        $cartRoom['id_cart'],
                        $cartRoom['id_guest'],
                        $cartRoom['id_product'],
                        $cartRoom['date_from'],
                        $cartRoom['date_to']
                    );
                }
            }
            return $res;
        }

        return true;
    }

    /**
     * [getCustomerIdRoomsByIdCartIdProduct :: To get array of rooms ids in the cart booked by a customer for a date range].
     *
     * @param [int]  $id_cart    [Id of the cart]
     * @param [int]  $id_product [Id of the product]
     * @param [date] $date_from  [Start date of the booking]
     * @param [date] $date_to    [End date of the booking]
     *
     * @return [array|false] [If rooms found returns array containing rooms ids else returns false]
     */
    public function getCustomerIdRoomsByIdCartIdProduct($id_cart, $id_product, $date_from, $date_to)
    {
        $rooms_ids = Db::getInstance()->executeS('SELECT `id_room` FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart`='.(int) $id_cart.' AND `id_product`='.(int) $id_product." AND `date_from`='".pSQL($date_from)."' AND `date_to`='".pSQL($date_to)."'");
        if ($rooms_ids) {
            return $rooms_ids;
        }

        return false;
    }

    /**
     * [deleteRowByCartBookingData : To delete data from the table by given conditions(conditions array in argument)].
     *
     * @param [Array] $cartData [array of the conditions on which row is to be deleted]
     *
     * @return [boolean] [Returns true if successfully updated else returns false]
     */
    public function deleteRowByCartBookingData($cartData)
    {
        if (!is_array($cartData)) {
            return false;
        }

        $dltdata = '';

        $stringArr = array('date_from', 'date_to');

        foreach ($cartData as $c_key => $c_val) {
            if ($dltdata) {
                $dltdata .= ' AND ';
            }

            if (in_array($c_key, $stringArr)) {
                $dltdata .= $c_key."= '$c_val'";
            } else {
                $dltdata .= $c_key.' = '.$c_val;
            }
        }

        $result = Db::getInstance()->executeS(
            'SELECT `id`
            FROM `'._DB_PREFIX_.'htl_cart_booking_data`
            WHERE '.$dltdata
        );

        if (is_array($result) && count($result)) {
            $return = true;
            foreach ($result as $row) {
                $objHotelCartBookingData = new self($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    $return &= $objHotelCartBookingData->delete();
                }
            }

            return $return;
        }

        return false;
    }

    /**
     * [updateCartBookingData : To update data in the table with given data and conditions].
     *
     * @param [Array] $updData [array of the data to be updated]
     * @param [Array] $updBy   [Conditions array will be used in where condition]
     *
     * @return [boolean] [Returns true if successfully updated else returns false]
     */
    public function updateCartBookingData($updData, $updBy)
    {
        if (!is_array($updData) && !is_array($updBy)) {
            return false;
        }

        $where = '';
        $stringArr = array('date_from', 'date_to');
        foreach ($updBy as $u_key => $u_val) {
            if ($where) {
                $where .= ' AND ';
            }

            if (in_array($u_key, $stringArr)) {
                $where .= $u_key."= '$u_val'";
            } else {
                $where .= $u_key.' = '.$u_val;
            }
        }
        // $update = Db::getInstance()->update('htl_cart_booking_data', $updData, $where);
        $result = Db::getInstance()->executeS(
            'SELECT `id`
            FROM `'._DB_PREFIX_.'htl_cart_booking_data`
            WHERE '.$where
        );

        if (is_array($result) && count($result)) {
            $return = true;
            foreach ($result as $row) {
                $objHotelCartBookingData = new self($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    foreach ($updData as $key => $value) {
                        $objHotelCartBookingData->$key = $value;
                    }

                    $return &= $objHotelCartBookingData->save();
                }
            }

            return $return;
        }

        return true;
    }

    // validate cart data if not available then remove from cart
    public static function validateCartBookings($checkServiceRoomLink = true)
    {
        $context = Context::getContext();
        $errors = array();
        $objModule = Module::getInstanceByName('hotelreservationsystem');

        // For admin cart validations use this variable
        $forAdminCart = 0;
        if (isset($context->employee->id)) {
            $forAdminCart = 1;
        }

        // validate room types if bookable from front office
        if ($cartProducts = $context->cart->getProducts()) {
            if (!$forAdminCart) {
                $objHotelCartBookingData = new HotelCartBookingData();
                foreach ($cartProducts as $product) {
                    if ($product['booking_product'] && !$product['show_at_front']) {
                        $objHotelCartBookingData->deleteCartBookingData($context->cart->id, $product['id_product']);
                    }
                }
            }
        } else {
            $errors[] = $objModule->l('No booking found in the cart.', 'HotelOrderRestrictDate');
        }

        if (Validate::isLoadedObject($context->cart)) {
            // validate service products if not active, deleted or not associated to a specific room type/hotels then remove from cart
            $objServiceProductCartDetail = new ServiceProductCartDetail();
            $objRoomTypeServiceProduct = new RoomTypeServiceProduct();
            if ($serviceProducts = $objServiceProductCartDetail->getServiceProductsInCart($context->cart->id)) {
                foreach ($serviceProducts as $service) {
                    $toRemoveService = 0;
                    if (!Validate::isLoadedObject($product = new Product($service['id_product']))) {
                        $toRemoveService = 1;
                    } else {
                        // check if product is active and (available for order for front office)
                        if (!$product->active || (!$forAdminCart && !$product->available_for_order)) {
                            $toRemoveService = 1;
                        } else if ($checkServiceRoomLink) {
                            if ($product->selling_preference_type == Product::SELLING_PREFERENCE_WITH_ROOM_TYPE) {
                                // service with room type must have association with valid hotel cart booking
                                if (Validate::isLoadedObject($objHotelCartBooking = new HotelCartBookingData($service['id_hotel_cart_booking']))) {
                                    // check if added room type is associated with valid service product
                                    $serviceAssociations = $objRoomTypeServiceProduct->getAssociatedHotelsAndRoomType(
                                        $service['id_product'],
                                        RoomTypeServiceProduct::WK_ELEMENT_TYPE_ROOM_TYPE,
                                        $objHotelCartBooking->id_product
                                    );
                                    if (!isset($serviceAssociations['room_type'])
                                        || !in_array($objHotelCartBooking->id_product, $serviceAssociations['room_type'])
                                    ) {
                                        $toRemoveService = 1;
                                    }
                                } else {
                                    $toRemoveService = 1;
                                }
                            } else {
                                if (ServiceProductOption::productHasOptions($service['id_product'])) {
                                    if ($product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE_AND_WITH_ROOM_TYPE
                                        && $service['id_hotel_cart_booking']
                                    ) {
                                        // do nothing if service of type SELLING_PREFERENCE_HOTEL_STANDALONE_AND_WITH_ROOM_TYPE is added with toom type
                                        // then we will not check for options
                                    } elseif (!Validate::isLoadedObject(new ServiceProductOption($service['id_product_option']))) {
                                        $toRemoveService = 1;
                                    }
                                } elseif ($service['id_product_option']) {
                                    $toRemoveService = 1;
                                }

                                if (!$toRemoveService) {
                                    if ($product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE) {
                                        // service with hotel must have association with valid hotel
                                        if (Validate::isLoadedObject($objHotelBranch = new HotelBranchInformation($service['id_hotel']))) {
                                            $serviceAssociations = $objRoomTypeServiceProduct->getAssociatedHotelsAndRoomType(
                                                $service['id_product'],
                                                RoomTypeServiceProduct::WK_ELEMENT_TYPE_HOTEL,
                                                $service['id_hotel']
                                            );
                                            if (!isset($serviceAssociations['hotel'])
                                                || !in_array($service['id_hotel'], $serviceAssociations['hotel'])
                                            ) {
                                                $toRemoveService = 1;
                                            }
                                        } else {
                                            $toRemoveService = 1;
                                        }
                                    } elseif ($product->selling_preference_type == Product::SELLING_PREFERENCE_STANDALONE) {
                                        // Standalone product must not have any association with hotel or hotel cart booking
                                        if ($service['id_hotel'] || $service['id_hotel_cart_booking']) {
                                            $toRemoveService = 1;
                                        }
                                    } elseif ($product->selling_preference_type == Product::SELLING_PREFERENCE_HOTEL_STANDALONE_AND_WITH_ROOM_TYPE) {
                                        // service with hotel or room type must have association with hotel or hotel cart booking
                                        if (!$service['id_hotel'] && !$service['id_hotel_cart_booking']) {
                                            $toRemoveService = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($toRemoveService) {
                        $objServiceProductCartDetail->removeCartServiceProduct(
                            $context->cart->id,
                            null,
                            false,
                            null,
                            null,
                            null,
                            $service['id_service_product_cart_detail']
                        );
                    }
                }
            }

            // for admin/employee remove bookings of the back dates from cart if not allowed
            if ($forAdminCart) {
                if ($context->employee->isSuperAdmin()) {
                    $backOrderConfigKey = 'PS_BACKDATE_ORDER_SUPERADMIN';
                } else {
                    $backOrderConfigKey = 'PS_BACKDATE_ORDER_EMPLOYEES';
                }
                if (!Configuration::get($backOrderConfigKey)) {
                    $objHotelCartBookingData = new HotelCartBookingData();
                    $objHotelCartBookingData->removeBackdateRoomsFromCart($context->cart->id);
                }
            }

            // validate room types for restriction date
            if ($cartProducts = $context->cart->getProducts()) {
                $objHotelCartBookingData = new HotelCartBookingData();
                $objHotelBookingDetail = new HotelBookingDetail();

                foreach ($cartProducts as $product) {
                    if ($product['booking_product']) {
                        if ($product['active']) {
                            if ($cartBookingData = $objHotelCartBookingData->getOnlyCartBookingData(
                                $context->cart->id,
                                $context->cart->id_guest,
                                $product['id_product']
                            )) {
                                $cartData = array();
                                foreach ($cartBookingData as $bookingData) {
                                    $dateJoin = strtotime($bookingData['date_from']).strtotime($bookingData['date_to']);
                                    $cartData[$dateJoin]['date_from'] = $bookingData['date_from'];
                                    $cartData[$dateJoin]['date_to'] = $bookingData['date_to'];
                                    $cartData[$dateJoin]['id_hotel'] = $bookingData['id_hotel'];
                                    $cartData[$dateJoin]['id_rms'][] = $bookingData['id_room'];
                                }

                                foreach ($cartData as $roomData) {
                                    if (!$forAdminCart) {
                                        if ($maxOrderDate = HotelOrderRestrictDate::getMaxOrderDate($roomData['id_hotel'])) {
                                            if (strtotime('-1 day', strtotime($maxOrderDate)) < strtotime($roomData['date_from'])
                                                || strtotime($maxOrderDate) < strtotime($roomData['date_to'])
                                            ) {
                                                $objHotelBranchInformation = new HotelBranchInformation(
                                                    $roomData['id_hotel'],
                                                    $context->language->id
                                                );
                                                $errors[] = sprintf(
                                                    $objModule->l('You can not book rooms for hotel "%s" after date %s. Please remove rooms from %s - %s to proceed.', 'HotelOrderRestrictDate'),
                                                    $objHotelBranchInformation->hotel_name,
                                                    Tools::displayDate($maxOrderDate),
                                                    Tools::displayDate($roomData['date_from']),
                                                    Tools::displayDate($roomData['date_to'])
                                                );
                                            }
                                        }

                                        $minBookingOffset = HotelOrderRestrictDate::getMinimumBookingOffset($roomData['id_hotel']);
                                        if ($minBookingOffset !== false) {
                                            $minOrderDate = date('Y-m-d', strtotime('+'. ($minBookingOffset) .' days'));
                                            if (strtotime($minOrderDate) > strtotime($roomData['date_from'])) {
                                                $objHotelBranchInformation = new HotelBranchInformation(
                                                    $roomData['id_hotel'],
                                                    $context->language->id
                                                );
                                                $errors[] = sprintf(
                                                    $objModule->l('You can not book rooms for hotel "%s" before date %s. Please remove rooms from %s - %s to proceed.', 'HotelOrderRestrictDate'),
                                                    $objHotelBranchInformation->hotel_name,
                                                    Tools::displayDate($minOrderDate),
                                                    Tools::displayDate($roomData['date_from']),
                                                    Tools::displayDate($roomData['date_to'])
                                                );
                                            }
                                        }
                                    }

                                    $bookingParams = array(
                                        'date_from' => $roomData['date_from'],
                                        'date_to' => $roomData['date_to'],
                                        'hotel_id' => $roomData['id_hotel'],
                                        'id_room_type' => $product['id_product'],
                                        'only_search_data' => 1,
                                    );
                                    $bookingSearchData = $objHotelBookingDetail->dataForFrontSearch($bookingParams);
                                    $isRoomBooked = 0;
                                    if (count($bookingSearchData['rm_data'][$product['id_product']]['data']['available']) < count($roomData['id_rms'])) {
                                        foreach ($roomData['id_rms'] as $searchRoomData) {
                                            if($isRoomBooked = $objHotelBookingDetail->chechRoomBooked($searchRoomData, $roomData['date_from'], $roomData['date_to'])){
                                                break;
                                            }
                                        }
                                        if ($isRoomBooked) {
                                            $errors[] = sprintf($objModule->l('The Room "%s" has been booked by another customer from "%s" to "%s" Please remove rooms from cart to proceed', 'HotelOrderRestrictDate'), $product['name'], date('d-m-Y', strtotime($roomData['date_from'])), date('d-m-Y', strtotime($roomData['date_to'])));
                                        } else {
                                            $errors[] = sprintf($objModule->l('The Room "%s" is no longer available from "%s" to "%s" Please remove rooms from cart to proceed', 'HotelOrderRestrictDate'), $product['name'], date('d-m-Y', strtotime($roomData['date_from'])), date('d-m-Y', strtotime($roomData['date_to'])));
                                        }
                                    }
                                }
                            }
                        } else {
                            $errors[] = $objModule->l('You can not book rooms from "', 'HotelOrderRestrictDate'). $product['name'] .$objModule->l('". Please remove rooms from "', 'HotelOrderRestrictDate'). $product['name'] . $objModule->l('" from cart to proceed.', 'HotelOrderRestrictDate');
                        }
                    }
                }
            }
        }

        Hook::exec('actionCartBookingsErrorsModifier', array('errors' => &$errors));

        return $errors;
    }

    /**
     * [getCartFormatedBookinInfoByIdCart : To get cart booking information with some additional information in a custom famated way].
     * @param [Int] $id_cart [Id of the cart]
     * @return [Array|false] [If data found returns cart booking information with some additional information else returns false]
     */
    public function getCartFormatedBookinInfoByIdCart($id_cart)
    {
        $context = Context::getContext();
        $cart_detail_data = $this->getCartCurrentDataByCartId((int) $id_cart);
        if ($cart_detail_data) {
            $objRoomDemands = new HotelRoomTypeDemand();
            $objServiceProductCartDetail = new ServiceProductCartDetail();
            $objRoomTypeServiceProduct = new RoomTypeServiceProduct();

            foreach ($cart_detail_data as $key => $value) {
                $product_image_id = Product::getCover($value['id_product']);
                $productObj = new Product((int) $value['id_product'], false, (int) Configuration::get('PS_LANG_DEFAULT'));
                $link_rewrite = $productObj->link_rewrite;
                if ($product_image_id) {
                    $cart_detail_data[$key]['image_link'] = $context->link->getImageLink($link_rewrite, $product_image_id['id_image'], 'small_default');
                } else {
                    $cart_detail_data[$key]['image_link'] = $context->link->getImageLink($link_rewrite, $context->language->iso_code.'-default', 'small_default');
                }

                $cart_detail_data[$key]['room_type'] = $productObj->name;
                $obj_htl_room_info = new HotelRoomInformation((int) $value['id_room']);
                $cart_detail_data[$key]['room_num'] = $obj_htl_room_info->room_num;
                $cart_detail_data[$key]['date_from'] = $value['date_from'];
                $cart_detail_data[$key]['date_to'] = $value['date_to'];

                $cart_detail_data[$key]['child_ages'] = json_decode($value['child_ages']);
                $occupancy = array(
                    array(
                        'adults' => $value['adults'],
                        'children' => $value['children'],
                        'child_ages' => json_decode($value['child_ages'])
                    )
                );
                $unit_price = Product::getPriceStatic($value['id_product'], true);
                $unit_price_tax_excl = Product::getPriceStatic($value['id_product'], false);
                $productPriceWithoutReduction = $productObj->getPriceWithoutReduct(false);
                $feature_price = HotelRoomTypeFeaturePricing::getRoomTypeFeaturePricesPerDay(
                    $value['id_product'],
                    $value['date_from'],
                    $value['date_to'],
                    true,
                    0,
                    $id_cart,
                    $value['id_guest'],
                    $value['id_room'],
                    0,
                    1,
                    $occupancy
                );
                $feature_price_tax_excl = HotelRoomTypeFeaturePricing::getRoomTypeFeaturePricesPerDay(
                    $value['id_product'],
                    $value['date_from'],
                    $value['date_to'],
                    false,
                    0,
                    $id_cart,
                    $value['id_guest'],
                    $value['id_room'],
                    0,
                    1,
                    $occupancy
                );
                $feature_price_diff = (float)($productPriceWithoutReduction - $feature_price);
                $cart_detail_data[$key]['product_price'] = $unit_price;
                $cart_detail_data[$key]['product_price_tax_excl'] = $unit_price_tax_excl;
                $cart_detail_data[$key]['feature_price'] = $feature_price;
                $cart_detail_data[$key]['feature_price_tax_excl'] = $feature_price_tax_excl;
                $cart_detail_data[$key]['feature_price_diff'] = $feature_price_diff;
                // add extra demands
                $cart_detail_data[$key]['extra_demands'] = $objRoomDemands->getRoomTypeDemands(
                    $value['id_product']
                );
                $cart_detail_data[$key]['selected_demands'] = $this->getCartExtraDemands(
                    $id_cart,
                    $value['id_product'],
                    $value['id_room'],
                    $value['date_from'],
                    $value['date_to'],
                    0,
                    1
                );
                $cart_detail_data[$key]['demand_price'] = $this->getCartExtraDemands(
                    $id_cart,
                    $value['id_product'],
                    $value['id_room'],
                    $value['date_from'],
                    $value['date_to'],
                    1,
                    0,
                    false
                );
                $cart_detail_data[$key]['additional_service'] = $objRoomTypeServiceProduct->getServiceProductsData(
                    $value['id_product'],
                    1,
                    0,
                    false,
                    2,
                    null
                );
                $cart_detail_data[$key]['selected_services'] = $objServiceProductCartDetail->getServiceProductsInCart(
                    $id_cart,
                    [],
                    null,
                    $value['id'],
                    null,
                    null,
                    null,
                    null,
                    0,
                    null,
                    null,
                    1
                );
                $cart_detail_data[$key]['additional_service_price'] = $objServiceProductCartDetail->getServiceProductsInCart(
                    $id_cart,
                    [],
                    null,
                    $value['id'],
                    $value['id_product'],
                    null,
                    $idProductOption = null,
                    false,
                    1,
                    0
                );
                $cart_detail_data[$key]['additional_services_auto_add_price'] = $objServiceProductCartDetail->getServiceProductsInCart(
                    $id_cart,
                    [],
                    null,
                    $value['id'],
                    $value['id_product'],
                    null,
                    null,
                    false,
                    1,
                    1
                );
                $cart_detail_data[$key]['additional_services_auto_add_with_room_price'] = $objServiceProductCartDetail->getServiceProductsInCart(
                    $id_cart,
                    [],
                    null,
                    $value['id'],
                    $value['id_product'],
                    null,
                    null,
                    false,
                    1,
                    1,
                    Product::PRICE_ADDITION_TYPE_WITH_ROOM
                );
                $cart_detail_data[$key]['additional_services_auto_add_independent_price'] = $objServiceProductCartDetail->getServiceProductsInCart(
                    $id_cart,
                    [],
                    null,
                    $value['id'],
                    $value['id_product'],
                    null,
                    null,
                    false,
                    1,
                    1,
                    Product::PRICE_ADDITION_TYPE_INDEPENDENT
                );
                // By webkul New way to calculate product prices with feature Prices
                $roomTypeDateRangePrice = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                    $value['id_product'],
                    $value['date_from'],
                    $value['date_to'],
                    $occupancy,
                    0,
                    $id_cart,
                    $value['id_guest'],
                    $value['id_room'],
                    0
                );

                $cart_detail_data[$key]['amt_with_qty'] = $roomTypeDateRangePrice['total_price_tax_excl'];
            }
        }
        if ($cart_detail_data) {
            return $cart_detail_data;
        }

        return false;
    }

    /**
     * [updateIdCurrencyByIdCart : To update id_currency in the table By id_cart].
     *
     * @param [Int] $id_cart     [Id of the cart]
     * @param [Int] $id_currency [Id of the currency]
     *
     * @return [Boolean] [Returns true if successfully updated else returns false]
     */
    public function updateIdCurrencyByIdCart($id_cart, $id_currency)
    {
        $result = Db::getInstance()->executeS(
            'SELECT `id`
            FROM `'._DB_PREFIX_.'htl_cart_booking_data`
            WHERE `id_cart` = '.(int) $id_cart
        );

        if (is_array($result) && count($result)) {
            $return = true;
            foreach ($result as $row) {
                $objHotelCartBookingData = new HotelCartBookingData($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    $objHotelCartBookingData->id_currency = $id_currency;
                    $return &= $objHotelCartBookingData->save();
                }
            }

            return $return;
        }

        return true;
    }

    /**
     * [deleteRoomFromOrder : Deletes a row from the table with the supplied conditions].
     *
     * @param [int]  $id_order  [Id of the order]
     * @param [int]  $id_room   [id_of the room]
     * @param [date] $date_from [Start date of the booking]
     * @param [date] $date_to   [End date of the booking]
     *
     * @return [Boolean] [True if deleted else false]
     */
    public function deleteOrderedRoomFromCart($id_order, $id_hotel, $id_room, $date_from, $date_to)
    {
        $result = Db::getInstance()->executeS(
            'SELECT `id`
            FROM `'._DB_PREFIX_.'htl_cart_booking_data`
            WHERE '.'`id_order`='.(int) $id_order.' AND `id_hotel`='.(int) $id_hotel.' AND `id_room`='.(int) $id_room." AND `date_from`='$date_from' AND `date_to`='$date_to'"
        );

        if (is_array($result) && count($result)) {
            $return = true;
            foreach ($result as $row) {
                $objHotelCartBookingData = new HotelCartBookingData($row['id']);
                if (Validate::isLoadedObject($objHotelCartBookingData)) {
                    $return &= $objHotelCartBookingData->delete();
                }
            }

            return $return;
        }

        return true;
    }

    /**
     * [getCartInfoIdCartIdProduct :: Returns Cart Info by id_product].
     *
     * @param [int] $id_cart    [cart id]
     * @param [int] $id_product [product id]
     *
     * @return [array/false] [returns all entries if data found else return false]
     */
    public function getCartInfoIdCartIdProduct($id_cart, $id_product, $date_from = false, $date_to = false)
    {
        $cache_key = 'HotelCartBookingData::getCartInfoIdCartIdProduct_'.(int)$id_cart.'_'.(int)$id_product.'_'.($date_from ? strtotime($date_from) : 'null').'_'.($date_to ? strtotime($date_to) : 'null');
        if (!Cache::isStored($cache_key)) {
            $sql = 'SELECT *
                FROM `'._DB_PREFIX_.'htl_cart_booking_data`
                WHERE `id_cart`='.(int) $id_cart.' AND `id_product`='.(int) $id_product;
            if ($date_from && $date_to) {
                $sql .= ' AND `date_from` = \''.pSQL($date_from).'\' AND `date_to` = \''.pSQL($date_to).'\'';
            }

            $res = Db::getInstance()->executeS($sql);

            Cache::store($cache_key, $res);
        } else {
            $res = Cache::retrieve($cache_key);
        }

        return $res;
    }

    /**
     * [getProductFeaturePricePlanByDateByPriority returns priority wise feature price plan on a perticular date].
     *
     * @param [int]  $id_product [id of the product]
     * @param [date] $date       [date for which feature price plan to be returned]
     * @param [int] $id_group    [id_group for which price is need (if available for the passed group)]
     * @return [array|false] [returns array containg info of the feature plan if foung otherwise returns false]
     */
    public static function getProductFeaturePricePlanByDateByPriority(
        $id_product,
        $date,
        $id_group,
        $id_cart = 0,
        $id_guest = 0,
        $id_room = 0
    ) {
        if ($id_cart && $id_room) {
            if ($featurePrice = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing` fp
                LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_restriction` fpr
                ON (fpr.`id_feature_price` = fp.`id_feature_price`)
                WHERE fp.`id_product` = '.(int) $id_product.' AND fp.`id_cart` = '.(int) $id_cart.'
                AND fp.`id_guest` = '.(int) $id_guest.' AND fp.`id_room` = '.(int) $id_room.'
                AND fp.`active` = 1 AND fpr.`date_from` <= \''.pSQL($date).'\'
                AND fpr.`date_to` >= \''.pSQL($date).'\'')
            ) {
                return $featurePrice;
            }
        }

        //Get priority
        $featurePricePriority = Configuration::get('HTL_FEATURE_PRICING_PRIORITY');
        $featurePricePriority = explode(';', $featurePricePriority);
        if ($featurePricePriority) {
            foreach ($featurePricePriority as $priority) {
                if ($priority == 'specific_date') {
                    if ($featurePrice = Db::getInstance()->getRow(
                        'SELECT * FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing` fp'.
                        (Group::isFeatureActive() ? ' INNER JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_group` fpg
                        ON (fp.`id_feature_price` = fpg.`id_feature_price` AND fpg.`id_group` = '.(int) $id_group.')' : '').'
                        LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_restriction` fpr
                        ON (fpr.`id_feature_price` = fp.`id_feature_price`)
                        WHERE fp.`id_cart` = 0 AND fp.`id_product`='.(int) $id_product.' AND fp.`active`=1
                        AND fpr.`date_selection_type` = '.(int) HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC.' AND fpr.`date_from` = \''.pSQL($date).'\''
                    )) {
                        return $featurePrice;
                    }
                } elseif ($priority == 'special_day') {
                    if ($featurePrice = Db::getInstance()->executeS(
                        'SELECT * FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing` fp'.
                        (Group::isFeatureActive() ? ' INNER JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_group` fpg
                        ON (fp.`id_feature_price` = fpg.`id_feature_price` AND fpg.`id_group` = '.(int) $id_group.')' : '').'
                        LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_restriction` fpr
                        ON (fpr.`id_feature_price` = fp.`id_feature_price`)
                        WHERE fp.`id_cart` = 0 AND fp.`id_product`='.(int) $id_product.'
                        AND fpr.`is_special_days_exists`=1 AND fp.`active`=1 AND fpr.`date_from` <= \''.pSQL($date).'\'
                        AND fpr.`date_to` >= \''.pSQL($date).'\''
                    )) {
                        foreach ($featurePrice as $fRow) {
                            $specialDays = json_decode($fRow['special_days']);
                            if (in_array(strtolower(date('D', strtotime($date))), $specialDays)) {
                                return $fRow;
                            }
                        }
                    }
                } elseif ($priority == 'date_range') {
                    if ($featurePrice = Db::getInstance()->getRow(
                        'SELECT * FROM `'._DB_PREFIX_.'htl_room_type_feature_pricing` fp'.
                        (Group::isFeatureActive() ? ' INNER JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_group` fpg
                        ON (fp.`id_feature_price` = fpg.`id_feature_price` AND fpg.`id_group` = '.(int) $id_group.')' : '').'
                        LEFT JOIN `'._DB_PREFIX_.'htl_room_type_feature_pricing_restriction` fpr
                        ON (fpr.`id_feature_price` = fp.`id_feature_price`)
                        WHERE fp.`id_cart` = 0 AND fp.`id_product`='.(int) $id_product.' AND fpr.`date_selection_type` = '.(int) HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE.'
                        AND `is_special_days_exists`=0 AND `active`=1
                        AND fpr.`date_from` <= \''.pSQL($date).'\' AND fpr.`date_to` >= \''.pSQL($date).'\''
                    )) {
                        return $featurePrice;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns booking info of the current cart
     * @param integer $detailed : send 1 for detailedd info and 0 for normal info
     * @return array of booking info of the current cart
    */
    public static function getHotelCartBookingData($detailed = 1)
    {
        $cartHotelData = array();
        $context = Context::getContext();
        if ($cartRoomTypes = $context->cart->getProducts()) {
            $idLang = $context->language->id;
            $price_tax = HotelBookingDetail::useTax();
            // create needed objects
            $objCartBooking = new self();
            $objRoomType = new HotelRoomType();
            $objHotelBranch = new HotelBranchInformation();
            $objHtlFeatures = new HotelFeatures();
            $objCartBookingData = new HotelCartBookingData();
            $objRoomDemands = new HotelRoomTypeDemand();
            $objRoomTypeServiceProduct = new RoomTypeServiceProduct();
            $objServiceProductCartDetail = new ServiceProductCartDetail();

            foreach ($cartRoomTypes as $prodKey => $product) {
                if (Validate::isLoadedObject(
                    $objProduct = new Product($product['id_product'], false, $idLang)
                )) {
                    // check if room type mapped with hotel
                    if ($roomDetail = $objRoomType->getRoomTypeInfoByIdProduct($product['id_product'])) {
                        $unitPrice = Product::getPriceStatic(
                            $product['id_product'],
                            $price_tax,
                            null,
                            6,
                            null,
                            false,
                            true,
                            1
                        );

                        $unitPriceWithoutReduction = $objProduct->getPriceWithoutReduct(!$price_tax);
                        $cartHotelData[$prodKey]['adults'] = $roomDetail['adults'];
                        $cartHotelData[$prodKey]['children'] = $roomDetail['children'];
                        $cartHotelData[$prodKey]['total_num_rooms'] = 0;
                        $cartHotelData[$prodKey]['id_product'] = $product['id_product'];
                        $cartHotelData[$prodKey]['name'] = $objProduct->name;
                        $cartHotelData[$prodKey]['unit_price'] = $unitPrice;
                        $cartHotelData[$prodKey]['unit_price_without_reduction'] = $unitPriceWithoutReduction;
                        $cartHotelData[$prodKey]['total_room_type_amount'] = 0;

                        // get cover image link
                        $coverImageArr = $objProduct->getCover($product['id_product']);
                        if (!empty($coverImageArr)) {
                            $coverImg = $context->link->getImageLink(
                                $objProduct->link_rewrite,
                                $objProduct->id.'-'.$coverImageArr['id_image'],
                                'small_default'
                            );
                        } else {
                            $coverImg = $context->link->getImageLink(
                                $objProduct->link_rewrite,
                                $context->language->iso_code.'-default',
                                'small_default'
                            );
                        }
                        $cartHotelData[$prodKey]['cover_img'] = $coverImg;

                        if ($detailed) {
                            // extra demands
                            $cartHotelData[$prodKey]['extra_demands'] = $objRoomDemands->getRoomTypeDemands($product['id_product']);
                            $cartHotelData[$prodKey]['service_products'] = $objRoomTypeServiceProduct->getServiceProductsData(
                                $product['id_product'],
                                1,
                                0,
                                true,
                                isset($context->employee->id) ? 2 : 1
                            );
                            // add hotel info of the room
                            if ($hotelInfo = $objHotelBranch->hotelBranchesInfo(
                                false,
                                2,
                                1,
                                $roomDetail['id_hotel']
                            )) {
                                $addressInfo = HotelBranchInformation::getAddress($roomDetail['id_hotel']);

                                $hotelInfo['location'] = $hotelInfo['hotel_name'].', '.$addressInfo['city'].
                                ($addressInfo['id_state']?', '.$addressInfo['state']:'').', '.
                                $addressInfo['country'].', '.$addressInfo['postcode'];


                                // append hotel features
                                if ($hotelFeaureIds = $objHotelBranch->getFeaturesOfHotelByHotelId($roomDetail['id_hotel'])) {
                                    $hotelFeatures = array();
                                    foreach ($hotelFeaureIds as $value) {
                                        $htlFeatureInfo = $objHtlFeatures->getFeatureInfoById($value['feature_id']);
                                        if ($htlFeatureInfo = $objHtlFeatures->getFeatureInfoById($value['feature_id'])) {
                                            $hotelFeatures[] = $htlFeatureInfo['name'];
                                        }
                                    }
                                    if ($hotelFeatures) {
                                        $hotelInfo['htl_features'] = $hotelFeatures;
                                    }
                                }
                                // append roomtype features
                                $hotelInfo['room_features'] = $objProduct->getFrontFeatures($idLang);

                                $cartHotelData[$prodKey]['hotel_info'] = $hotelInfo;
                            }
                        }
                        if (isset($context->customer->id)) {
                            $cartBookingDetails = $objCartBooking->getOnlyCartBookingData(
                                $context->cart->id,
                                $context->cart->id_guest,
                                $product['id_product']
                            );
                        } else {
                            $cartBookingDetails = $objCartBooking->getOnlyCartBookingData(
                                $context->cart->id,
                                $context->cart->id_guest,
                                $product['id_product']
                            );
                        }

                        if (isset($cartBookingDetails) && $cartBookingDetails) {
                            foreach ($cartBookingDetails as $data_k => $data_v) {
                                $dateJoin = strtotime($data_v['date_from']).strtotime($data_v['date_to']);
                                $demandPrice = $objCartBookingData->getCartExtraDemands(
                                    $context->cart->id,
                                    $data_v['id_product'],
                                    $data_v['id_room'],
                                    $data_v['date_from'],
                                    $data_v['date_to'],
                                    1
                                );
                                $serviceProductPrice = $objServiceProductCartDetail->getServiceProductsInCart(
                                    $context->cart->id,
                                    [],
                                    null,
                                    $data_v['id'],
                                    null,
                                    null,
                                    null,
                                    null,
                                    1,
                                    0
                                );

                                $totalAdditionalServicePrice = $demandPrice + $serviceProductPrice;
                                $occupancy = array(
                                    array(
                                        'adults' => $data_v['adults'],
                                        'children' => $data_v['children'],
                                        'child_ages' => json_decode($data_v['child_ages'])
                                    )
                                );
                                if (isset($cartHotelData[$prodKey]['date_diff'][$dateJoin])) {
                                    $numDays = HotelHelper::getNumberOfDays($data_v['date_from'], $data_v['date_to']);
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['demand_price'] += $totalAdditionalServicePrice;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['num_rm'] += 1;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['num_days'] = $numDays;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['adults'] += $data_v['adults'];
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['children'] += $data_v['children'];
                                    if (is_array($data_v['child_ages'])) {
                                        $cartHotelData[$prodKey]['date_diff'][$dateJoin]['child_ages'] = array_merge($cartHotelData[$prodKey]['date_diff'][$dateJoin]['child_ages'], json_decode($data_v['child_ages']));
                                    }
                                    $varQty = (int) $cartHotelData[$prodKey]['date_diff'][$dateJoin]['num_rm'];
                                    $roomTypeDateRangePrice = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        $occupancy,
                                        0,
                                        $context->cart->id,
                                        $context->cart->id_guest,
                                        $data_v['id_room']
                                    );
                                    $roomTypeDateRangePriceWithoutAutoAdd = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        $occupancy,
                                        0,
                                        $context->cart->id,
                                        $context->cart->id_guest,
                                        $data_v['id_room'],
                                        0
                                    );
                                    $priceWithoutDiscount = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        $occupancy,
                                        0,
                                        $context->cart->id,
                                        $context->cart->id_guest,
                                        $data_v['id_room'],
                                        1,
                                        0
                                    );
                                    if (!$price_tax) {
                                        $amount = $roomTypeDateRangePrice['total_price_tax_excl'];
                                        $amountWithoutAutoAdd = $roomTypeDateRangePriceWithoutAutoAdd['total_price_tax_excl'];
                                        $totalPriceWithoutDiscount = $priceWithoutDiscount['total_price_tax_excl'];
                                    } else {
                                        $amount = $roomTypeDateRangePrice['total_price_tax_incl'];
                                        $amountWithoutAutoAdd = $roomTypeDateRangePriceWithoutAutoAdd['total_price_tax_incl'];
                                        $totalPriceWithoutDiscount = $priceWithoutDiscount['total_price_tax_incl'];
                                    }
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['amount'] += $amount;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['total_price_without_discount'] += $totalPriceWithoutDiscount;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['amount_without_auto_add'] += $amountWithoutAutoAdd;
                                } else {
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['demand_price'] = $totalAdditionalServicePrice;
                                    $numDays = HotelHelper::getNumberOfDays($data_v['date_from'], $data_v['date_to']);
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['num_rm'] = 1;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['data_form'] = date(
                                        'Y-m-d H:i:s',
                                        strtotime($data_v['date_from'])
                                    );
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['data_to'] = date(
                                        'Y-m-d H:i:s',
                                        strtotime($data_v['date_to'])
                                    );
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['num_days'] = $numDays;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['adults'] = $data_v['adults'];
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['children'] = $data_v['children'];
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['child_ages'] = json_decode($data_v['child_ages']);

                                    $roomTypeDateRangePrice = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        $occupancy,
                                        0,
                                        $context->cart->id,
                                        $context->cart->id_guest,
                                        $data_v['id_room']
                                    );
                                    $roomTypeDateRangePriceWithoutAutoAdd = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        $occupancy,
                                        0,
                                        $context->cart->id,
                                        $context->cart->id_guest,
                                        $data_v['id_room'],
                                        0
                                    );
                                    $priceWithoutDiscount = HotelRoomTypeFeaturePricing::getRoomTypeTotalPrice(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        $occupancy,
                                        0,
                                        $context->cart->id,
                                        $context->cart->id_guest,
                                        $data_v['id_room'],
                                        1,
                                        0
                                    );
                                    if (!$price_tax) {
                                        $amount = $roomTypeDateRangePrice['total_price_tax_excl'];
                                        $amountWithoutAutoAdd = $roomTypeDateRangePriceWithoutAutoAdd['total_price_tax_excl'];
                                        $totalPriceWithoutDiscount = $priceWithoutDiscount['total_price_tax_excl'];
                                    } else {
                                        $amount = $roomTypeDateRangePrice['total_price_tax_incl'];
                                        $amountWithoutAutoAdd = $roomTypeDateRangePriceWithoutAutoAdd['total_price_tax_incl'];
                                        $totalPriceWithoutDiscount = $priceWithoutDiscount['total_price_tax_incl'];
                                    }
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['amount'] = $amount;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['amount_without_auto_add'] = $amountWithoutAutoAdd;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['total_price_without_discount'] = $totalPriceWithoutDiscount;
                                    $cartHotelData[$prodKey]['date_diff'][$dateJoin]['link'] = $context->link->getPageLink(
                                        'order-opc',
                                        null,
                                        $idLang,
                                        "id_product=".$product['id_product']."&deleteFromOrderLine=1&date_from=".
                                        $data_v['date_from']."&date_to=".$data_v['date_to']
                                    );
                                }
                                if ($price_tax) {
                                    $feature_price = HotelRoomTypeFeaturePricing::getRoomTypeFeaturePricesPerDay(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        true
                                    );
                                } else {
                                    $feature_price = HotelRoomTypeFeaturePricing::getRoomTypeFeaturePricesPerDay(
                                        $product['id_product'],
                                        $data_v['date_from'],
                                        $data_v['date_to'],
                                        false
                                    );
                                }
                                $feature_price_diff = (float)($unitPriceWithoutReduction - $feature_price);
                                $cartHotelData[$prodKey]['date_diff'][$dateJoin]['feature_price'] = $feature_price;
                                $cartHotelData[$prodKey]['date_diff'][$dateJoin]['feature_price_diff'] = $feature_price_diff;
                            }

                            $cartHotelData[$prodKey]['total_room_type_amount'] = array_sum(array_column($cartHotelData[$prodKey]['date_diff'], 'amount'));
                            $cartHotelData[$prodKey]['total_num_rooms'] = array_sum(array_column($cartHotelData[$prodKey]['date_diff'], 'num_rm'));
                        }
                    }
                }
            }
        }

        return $cartHotelData;
    }

    public function getHotelCartDistinctDateRangesByRoomType($id_cart, $id_product)
    {
        return Db::getInstance()->executeS('SELECT DISTINCT `date_from`, `date_to` FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart`='.(int) $id_cart.' AND `id_product`='.(int)$id_product);
    }

    public function getHotelCartRoomsInfoByRoomType($id_cart, $id_product, $date_from, $date_to)
    {
        return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE `id_cart`='.(int) $id_cart.' AND `id_product`='.(int)$id_product.' AND `date_from` = \''.pSQL($date_from).'\' AND `date_to` = \''.pSQL($date_to).'\'');
    }

    public function updateCartProductQuantityInPsCart($id_cart, $id_product, $quantity, $direction='up')
    {
        $cart = new Cart($id_cart);
        $shop = Context::getContext()->shop;
        $containsProduct = $cart->containsProduct($id_product, 0, 0, (int)$cart->id_address_delivery);
        $oldQty = $containsProduct['quantity'];
        /* Update quantity if product already exist */
        if ($containsProduct) {
            if ($direction == 'up') {
                $new_qty = (int)$containsProduct['quantity'] + (int)$quantity;
                $qty = '+ '.(int)$quantity;
            } elseif ($direction == 'down') {
                $new_qty = (int)$containsProduct['quantity'] - (int)$quantity;
                $qty = '- '.(int)$quantity;
            } elseif ($direction == 'fix') {
                $new_qty = (int)$quantity;
            } else {
                return false;
            }
            /* Delete product from cart */
            if ($new_qty <= 0) {
                return $cart->deleteProduct((int)$id_product, 0, (int)0, 0, 0);
            } else {
                Db::getInstance()->execute('
                    UPDATE `'._DB_PREFIX_.'cart_product`
                    SET `quantity` = '.(int) $new_qty.', `date_add` = NOW()
                    WHERE `id_product` = '.(int)$id_product.'
                    AND `id_cart` = '.(int)$id_cart.' AND `id_address_delivery` = '.(int)$cart->id_address_delivery
                );
            }
        } else { /* Add product to the cart */
            $result_add = Db::getInstance()->insert('cart_product', array(
                'id_product' => (int)$id_product,
                'id_product_attribute' => 0,
                'id_cart' => (int)$id_cart,
                'id_address_delivery' => (int)$cart->id_address_delivery,
                'id_shop' => $shop->id,
                'quantity' => (int)$quantity,
                'date_add' => date('Y-m-d H:i:s')
            ));
            if (!$result_add) {
                return false;
            }
        }
        return true;
    }

    /**
     * [getHotelCartInfoIdOrderIdProduct :: Returns Cart Info by id_product]
     * @param  [int] $id_cart    [cart id]
     * @param  [int] $id_product [product id]
     * @return [array/false]     [returns all entries if data found else return false]
     */
    public static function getHotelCartInfoIdOrderIdProduct($id_cart, $id_product)
    {
        return Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."htl_cart_booking_data` WHERE `id_cart`=".(int) $id_cart." AND `id_product`=".(int) $id_product);
    }

    public function getRoomRowByIdProductIdRoomInDateRange($id_cart, $id_product, $date_from, $date_to, $id_room)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data`
            WHERE `id_cart`='.(int)$id_cart.' AND `id_product`='.(int)$id_product.
            ' AND `date_from`=\''.pSQL($date_from).'\' AND `date_to`= \''.pSQL($date_to).'\''.' AND `id_room`='.(int)$id_room
        );
    }

    public function getCartExtraDemands(
        $idCart = 0,
        $idProduct = 0,
        $idRoom = 0,
        $dateFrom = 0,
        $dateTo = 0,
        $getTotalPrice = 0,
        $onlyRoomDemands = 0,
        $useTax = null
    ) {
        if ($useTax === null) {
            $useTax = HotelBookingDetail::useTax();
        }
        $context = Context::getContext();
        if (isset($context->currency->id)
            && Validate::isLoadedObject($context->currency)
        ) {
            $idCurrency = (int)$context->currency->id;
        } else {
            $idCurrency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        }

        if ($getTotalPrice) {
            $totalDemandsPrice = 0;
        }
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'htl_cart_booking_data` WHERE 1';
        if ($idCart) {
            $sql .= ' AND `id_cart`='.(int) $idCart;
        }
        if ($idProduct) {
            $sql .= ' AND `id_product`='.(int) $idProduct;
        }
        if ($idRoom) {
            $sql .= ' AND `id_room`='.(int) $idRoom;
        }
        if ($dateFrom && $dateTo) {
            $sql .= ' AND `date_from` = \''.pSQL($dateFrom).'\' AND `date_to` = \''.pSQL($dateTo).'\'';
        }
        //if ($idCart && ($idRoom || ($idProduct && $dateFrom && $dateTo))) {
        if ($onlyRoomDemands) {
            if ($roomTypeDemands = Db::getInstance()->getRow($sql)) {
                $objRoomDemandPrice = new HotelRoomTypeDemandPrice();

                if ($getTotalPrice) {
                    if ($roomTypeDemands['extra_demands']
                        && ($extraDemand = json_decode($roomTypeDemands['extra_demands'], true))
                    ) {
                        $totalDemandsPrice += $objRoomDemandPrice->getRoomTypeDemandsTotalPrice(
                            $roomTypeDemands['id_product'],
                            $extraDemand,
                            $useTax,
                            $roomTypeDemands['date_from'],
                            $roomTypeDemands['date_to']
                        );
                    }
                } else {
                    $roomTypeDemands['extra_demands'] = json_decode(
                        $roomTypeDemands['extra_demands'],
                        true
                    );
                    if (isset($roomTypeDemands['extra_demands']) && $roomTypeDemands['extra_demands']) {
                        foreach ($roomTypeDemands['extra_demands'] as &$selDemand) {
                            if ($selDemand['id_option']) {
                                $objOption = new HotelRoomTypeGlobalDemandAdvanceOption(
                                    $selDemand['id_option'],
                                    $context->language->id
                                );
                                $selDemand['name'] = $objOption->name;
                            } else {
                                $objGlobalDemand = new HotelRoomTypeGlobalDemand(
                                    $selDemand['id_global_demand'],
                                    $context->language->id
                                );
                                $selDemand['name'] = $objGlobalDemand->name;
                            }
                        }
                    }
                    $roomTypeDemands = $roomTypeDemands['extra_demands'];
                }
            }
        } else {
            if ($roomTypeDemands = Db::getInstance()->executeS($sql)) {
                $objRoomDemandPrice = new HotelRoomTypeDemandPrice();
                foreach ($roomTypeDemands as $key => &$demand) {
                    if ($getTotalPrice) {
                        if ($demand['extra_demands']
                            && ($extraDemand = json_decode($demand['extra_demands'], true))
                        ) {
                            $totalDemandsPrice += $objRoomDemandPrice->getRoomTypeDemandsTotalPrice(
                                $demand['id_product'],
                                $extraDemand,
                                $useTax,
                                $demand['date_from'],
                                $demand['date_to']
                            );
                        }
                    } else {
                        $demand['extra_demands'] = json_decode(
                            $demand['extra_demands'],
                            true
                        );
                        if (isset($demand['extra_demands']) && $demand['extra_demands']) {
                            foreach ($demand['extra_demands'] as &$selDemand) {
                                if ($selDemand['id_option']) {
                                    $objOption = new HotelRoomTypeGlobalDemandAdvanceOption(
                                        $selDemand['id_option'],
                                        $context->language->id
                                    );
                                    $selDemand['name'] = $objOption->name;
                                } else {
                                    $objGlobalDemand = new HotelRoomTypeGlobalDemand(
                                        $selDemand['id_global_demand'],
                                        $context->language->id
                                    );
                                    $selDemand['name'] = $objGlobalDemand->name;

                                }
                            }
                        }
                    }
                }
            }
        }
        if ($getTotalPrice) {
            return $totalDemandsPrice;
        } else {
            return $roomTypeDemands;
        }
    }

    public function save($null_values = false, $auto_date = true)
    {
        $return = parent::save($null_values = false, $auto_date = true);
        // after updating cart data, check if any of the appled cart rules are not being used
        CartRule::autoRemoveFromCart(Context::getContext());
        CartRule::autoAddToCart(Context::getContext());
        return $return;
    }

    public function update($null_values = false)
    {
        if (!$this->extra_demands) {
            $this->extra_demands = json_encode(array());
        }

        return parent::update($null_values);
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (!$this->extra_demands) {
            $this->extra_demands = json_encode(array());
        }

        return parent::add($auto_date, $null_values);
    }

    // Webservice :: get extra demands for the cart booking
    public function getWsExtraDemands()
    {
        $extraDemands = json_decode($this->extra_demands, true);
        if ($extraDemands) {
            foreach ($extraDemands as &$demand) {
                $demand['id'] = $demand['id_global_demand'];
            }
            return $extraDemands;
        }
    }
}
