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

class HotelBranchInformation extends ObjectModel
{
    public $id_category;
    public $hotel_name;
    public $email;
    public $check_in;
    public $check_out;
    public $description;
    public $short_description;
    public $rating;
    public $policies;
    public $active;
    public $latitude;
    public $longitude;
    public $map_formated_address;
    public $map_input_text;
    public $active_refund;
    public $fax;
    public $date_add;
    public $date_upd;

    public $moduleInstance;

    public static $definition = array(
        'table' => 'htl_branch_info',
        'primary' => 'id',
        'multilang' => true,
        'fields' => array(
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'email' => array('type' => self::TYPE_STRING,'validate' => 'isEmail', 'size' => 255, 'required' => true),
            'rating' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'check_in' => array('type' => self::TYPE_STRING, 'required' => true),
            'check_out' => array('type' => self::TYPE_STRING, 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'latitude' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'longitude' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'map_formated_address' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'map_input_text' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active_refund' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'fax' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),

            //lang fields
            'policies' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true),
            'hotel_name' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isGenericName'),
            'description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true),
            'short_description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true),
    ), );

    // Webservice fields
    public $id_country;
    public $id_state;
    public $city;
    public $zipcode;
    public $address;
    public $phone;
    public $use_global_max_checkout_offset = 1;
    public $max_checkout_offset = '1000';
    public $use_global_min_booking_offset = 1;
    public $min_booking_offset = '0';

    protected $webserviceParameters = array(
        'objectsNodeName' => 'hotels',
        'objectNodeName' => 'hotel',
        'objectMethods' => array(
            'add' => 'addWs',
            'delete' => 'deleteWs',
            'update' => 'updateWs',
        ),
        'fields' => array(
            'phone' => array('required' => true),
            'address' => array('required' => true),
            'id_country' => array(
                'required' => true,
                'xlink_resource'=> 'countries',
            ),
            'id_state' => array(
                'xlink_resource'=> 'states',
            ),
            'city' => array(
                'required' => true,
            ),
            'zipcode' => array(),
            'id_default_image' => array(
                'getter' => 'getCoverWs',
                'setter' => 'setCoverWs',
                'xlink_resource' => array(
                    'resourceName' => 'images',
                    'subResourceName' => 'hotels'
                )
            ),
            'use_global_max_checkout_offset' => array(),
            'max_checkout_offset' => array(),
            'use_global_min_booking_offset' => array(),
            'min_booking_offset' => array(),
        ),
        'associations' => array(
            'room_types' => array(
                'setter' => false,
                'resource' => 'room_types',
                'fields' => array('id' => array())
            ),
            'images' => array(
                'resource' => 'image',
                'fields' => array('id' => array())
            ),
            'hotel_features' => array(
                'resource' => 'hotel_feature',
                'fields' => array('id' => array('required' => true))
            ),
            'hotel_refund_rules' => array(
                'resource' => 'hotel_refund_rule',
                'fields' => array('id' => array('required' => true))
            ),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->moduleInstance = Module::getInstanceByName('hotelreservationsystem');
        parent::__construct($id, $id_lang, $id_shop);

        $this->id_country = $this->id_state = $this->city = $this->zipcode = $this->address = $this->phone = null;
        if ($id) {
            if ($hotelAddress = self::getAddress($id)) {
                $this->id_country = $hotelAddress['id_country'];
                $this->id_state = $hotelAddress['id_state'];
                $this->city = $hotelAddress['city'];
                $this->zipcode = $hotelAddress['postcode'];
                $this->address = $hotelAddress['address1'];
                $this->phone = $hotelAddress['phone'];
            }

            if ($hotelRestrictions = HotelOrderRestrictDate::getDataByHotelId($id)) {
                $this->use_global_max_checkout_offset = $hotelRestrictions['use_global_max_checkout_offset'];
                $this->max_checkout_offset = $hotelRestrictions['max_checkout_offset'];
                $this->use_global_min_booking_offset = $hotelRestrictions['use_global_min_booking_offset'];
                $this->min_booking_offset = $hotelRestrictions['min_booking_offset'];
            }
        }
    }

    public function add($autodate = true, $null_values = false)
    {
        // Add tab
        if (parent::add($autodate, $null_values)) {
            // insert accesses of the hotel
            return self::initAccess($this->id);
        }
        return false;
    }

    /** When creating a new hotel $idHotel, this add default rights to the table access
     * @param int $idHotel
     * @param Context $context
     * @return bool true if succeed
     */
    protected function initAccess($idHotel, ?Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        /* Query definition */
        $query = 'REPLACE INTO `'._DB_PREFIX_.'htl_access` (`id_profile`, `id_hotel`, `access`)';
        $query .= ' VALUES '.'('.(int) _PS_ADMIN_PROFILE_.', '.(int)$idHotel.', 1)';
        /* Profile selection */
        $profiles = Db::getInstance()->executeS('SELECT `id_profile` FROM '._DB_PREFIX_.'profile WHERE `id_profile` != '.(int) _PS_ADMIN_PROFILE_);
        if ($profiles) {
            foreach ($profiles as $profile) {
                $access = 0;
                if (isset($context->employee->id_profile)) {
                    $access = ($profile['id_profile'] == $context->employee->id_profile) ? 1 : 0;
                }

                $query .= ', ('.(int)$profile['id_profile'].', '.(int)$idHotel.', '.(int)$access.')';
            }
        }
        return Db::getInstance()->execute($query);
    }

    // Add profile hotel access while profile is added
    public function addHotelsAccessToProfile($idProfile)
    {
        return Db::getInstance()->execute(
            'INSERT INTO '._DB_PREFIX_.'htl_access (SELECT '.(int)$idProfile.', id, 0 FROM '
            ._DB_PREFIX_.'htl_branch_info)'
        );
    }

    // Add profile hotel access while profile is deleted
    public function deleteProfileHotelsAccess($idProfile)
    {
        return Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'htl_access` WHERE `id_profile` = '.(int)$idProfile
        );
    }

    /**
     * get profile accessed hotels
     * @param [type] $idProfile
     * @param integer $access send 1 for allowed, 0 for not allowed and 2 form all
     * @param integer $onlyhotelIds  send 1 if only hotel ids needed
     * @return [hotelaccessInfo or id_hotel array]
     */
    public static function getProfileAccessedHotels($idProfile, $access = 2, $onlyhotelIds = 0, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $sql = 'SELECT ha.`id_hotel`';
        if (!$onlyhotelIds) {
            $sql .= ', hbl.`hotel_name`, hbi.`id_category`, hbi.`active`';
        }
        $sql .= ' FROM `'._DB_PREFIX_.'htl_access` ha';
        if (!$onlyhotelIds) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'htl_branch_info` hbi ON (hbi.`id` = ha.`id_hotel`)';
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
                ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')';
        }
        $sql .= ' WHERE `id_profile` = '.(int)$idProfile;
        if ($access != 2) {
            $sql .= ' AND access = 1';
        }

        if ($hotelAccessInfo =  Db::getInstance()->executeS($sql)) {
            if ($onlyhotelIds) {
                $hotels = array();
                foreach ($hotelAccessInfo as $hotel) {
                    $hotels[] = $hotel['id_hotel'];
                }
                return $hotels;
            }
        }
        return $hotelAccessInfo;
    }

    // Add profile hotel access while profile is deleted
    public function getHotelAccess($idHotel)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'htl_access` WHERE `id_hotel` = '.(int)$idHotel
        );
    }

    /**
     * Filter associative array containing id_hotel or id prodcut as per the hotels access
     * @param [array] $data : associative array containing id_hotel or id prodcut in its elements
     * @param [int] $idProfile : id profile as pe which we have to filter data of hotel access
     * @param [int] $idAsIdHotel : consider id key in the array as id_hotel
     * @param [int] $idAsIdProduct : consider id key in the array as id_product
     * @param [int] $addAccessKey : Dont unset but set a key which define the access by hotels
     * @return [array or false]
     */
    public static function filterDataByHotelAccess(
        $dataArray,
        $idProfile,
        $idHotelAlias = false,
        $idProductAlias = false,
        $addAccessKey = 0
    ) {
        if ($hotelAccessInfo =  Db::getInstance()->executeS(
            'SELECT `id_hotel` FROM `'._DB_PREFIX_.'htl_access` WHERE  access = 1 AND `id_profile` = '.(int)$idProfile
        )) {
            $hotels = array();
            foreach ($hotelAccessInfo as $hotel) {
                $hotels[] = $hotel['id_hotel'];
            }
            if ($hotels) {
                foreach ($dataArray as $key => $row) {
                    if (isset($row['id_hotel'])) {
                        if ($addAccessKey) {
                            $dataArray[$key]['htl_access'] = 0;
                            if (in_array($row['id_hotel'], $hotels)) {
                                $dataArray[$key]['htl_access'] = 1;
                            }
                        } else {
                            if (!in_array($row['id_hotel'], $hotels)) {
                                unset($dataArray[$key]);
                            }
                        }
                    } elseif ($idHotelAlias && isset($row[$idHotelAlias])) {
                        if ($addAccessKey) {
                            $dataArray[$key]['htl_access'] = 0;
                            if (in_array($row[$idHotelAlias], $hotels)) {
                                $dataArray[$key]['htl_access'] = 1;
                            }
                        } else {
                            if (!in_array($row[$idHotelAlias], $hotels)) {
                                unset($dataArray[$key]);
                            }
                        }
                    } elseif ($idProductAlias && isset($row[$idProductAlias])) {
                        $objRoomType = new HotelRoomType();
                        if ($roomTypeInfo = $objRoomType->getRoomTypeInfoByIdProduct($row[$idProductAlias])) {
                            if ($addAccessKey) {
                                $dataArray[$key]['htl_access'] = 0;
                                if (in_array($roomTypeInfo['id_hotel'], $hotels)) {
                                    $dataArray[$key]['htl_access'] = 1;
                                }
                            } else {
                                if (!in_array($roomTypeInfo['id_hotel'], $hotels)) {
                                    unset($dataArray[$key]);
                                }
                            }
                        } else {
                            if ($addAccessKey) {
                                $dataArray[$key]['htl_access'] = 0;
                            } else {
                                unset($dataArray[$key]);
                            }
                        }
                    } elseif (isset($row['id_product'])) {
                        $objRoomType = new HotelRoomType();
                        if ($roomTypeInfo = $objRoomType->getRoomTypeInfoByIdProduct($row['id_product'])) {
                            if ($addAccessKey) {
                                $dataArray[$key]['htl_access'] = 0;
                                if (in_array($roomTypeInfo['id_hotel'], $hotels)) {
                                    $dataArray[$key]['htl_access'] = 1;
                                }
                            } else {
                                if (!in_array($roomTypeInfo['id_hotel'], $hotels)) {
                                    unset($dataArray[$key]);
                                }
                            }
                        } else {
                            if ($addAccessKey) {
                                $dataArray[$key]['htl_access'] = 0;
                            } else {
                                unset($dataArray[$key]);
                            }
                        }
                    }
                }
                return $dataArray;
            }
        }
        return array();
    }

    public function hotelBranchesInfo($idLang = false, $active = 2, $detailedInfo = 0, $idHotel = 0)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $cache_id = 'hotelBranch::hotelBranchesInfo'.(int)$idLang.'-'.(int)$active.'-'.(int)$detailedInfo.'-'.(int)$idHotel;
        if (!Cache::isStored($cache_id)) {
            $sql = 'SELECT hbi.*, hbl.`policies`, hbl.`hotel_name`, hbl.`description`, hbl.`short_description`';
            if ($detailedInfo) {
                $sql .= ', hi.id as id_cover_img, a.`address1` as address, a.`postcode`, a.`phone`,  a.`city`,
                    s.`name` as `state_name`, cl.`name` as country_name';
            }
            $sql .= ' FROM `'._DB_PREFIX_.'htl_branch_info` hbi';
            $sql .= ' LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')';
            if ($detailedInfo) {
                $sql .= ' LEFT JOIN `'._DB_PREFIX_.'htl_image` hi ON (hi.`id_hotel` = hbi.`id` AND hi.`cover` = 1)';
                $sql .= ' LEFT JOIN `'._DB_PREFIX_.'address` a ON (a.`id_hotel` = hbi.`id`)';
                $sql .= ' LEFT JOIN `'._DB_PREFIX_.'state` s ON (s.`id_state` = a.`id_state`)';
                $sql .= ' LEFT JOIN `'._DB_PREFIX_.
                'country_lang` cl ON (cl.`id_country` = a.`id_country` AND cl.`id_lang` = '.(int)$idLang.')';
            }
            $sql .= ' WHERE 1';
            if ($active == 1 || $active == 0) {
                $sql .= ' AND hbi.`active` = '.(int)$active;
            }
            if ($idHotel) {
                $sql .= ' AND hbi.`id` = '.(int)$idHotel;
                $result =  Db::getInstance()->getRow($sql);
            } else {
                $result =  Db::getInstance()->executeS($sql);
            }
            Cache::store($cache_id, $result);
            return $result;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * [getActiveHotelBranchesInfo : To get all the activated hotels information created by the admin].
     * @return [array | false] [If no hotel is created or activated then returns false otherwise returns all activated hotels information array]
     */
    public function getActiveHotelBranchesInfo()
    {
        $idLang = Context::getContext()->language->id;
        $sql = 'SELECT hbi.*, hbl.`policies`, hbl.`hotel_name`, hbl.`description`, hbl.`short_description`
            FROM `'._DB_PREFIX_.'htl_branch_info` hbi
            LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')  WHERE hbi.`active` = 1';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * [getHotelIdAddress: get id_address for current hotel]
     *
     * @return int
     */
    public function getHotelIdAddress()
    {
        return Db::getInstance()->getValue(
            'SELECT `id_address` from `'._DB_PREFIX_.'address` WHERE `id_hotel` = '.(int)$this->id.' AND `deleted` = 0
        ');
    }

    /**
     * [getAddress: get complete address for hotel by id_hotel]
     *
     * @param [int] $id_hotel
     * @param int $id_lang
     * @return array
     */
    public static function getAddress($id_hotel, $id_lang = false)
    {
        if (!$id_lang)
            $id_lang = Context::getContext()->language->id;

        if (!$id_hotel) {
            return false;
        }

        $cache_id = 'hotelBranch::getAddress'.(int)$id_hotel.'-'.(int)$id_lang;
        if (!Cache::isStored($cache_id)) {
            $sql = 'SELECT a.*, cl.`name` AS country, s.`name` AS state, s.`iso_code` AS state_iso
					FROM `'._DB_PREFIX_.'address` a
					LEFT JOIN `'._DB_PREFIX_.'country` c ON (a.`id_country` = c.`id_country`)
					LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country`)
					LEFT JOIN `'._DB_PREFIX_.'state` s
                    ON (s.`id_state` = a.`id_state` AND`id_lang` = '.(int)$id_lang.')
					WHERE `id_hotel` = '.(int)$id_hotel.' AND a.`deleted` = 0';

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            Cache::store($cache_id, $result);
            return $result;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Deprecated
     * [hotelBranchInfoById : Hotel's information By its id].
     *
     * @param [int] $id [id of the hotel which information is wanted]
     *
     * @return [array | false]     [If no hotel found with id sent then returns false otherwise returns all hotel's information array]
     */
    public function hotelBranchInfoById($id)
    {
        $idLang = Context::getContext()->language->id;
        $sql = 'SELECT hbi.*, hbl.`policies`, hbl.`hotel_name`, hbl.`description`, hbl.`short_description`
            FROM `'._DB_PREFIX_.'htl_branch_info` hbi
            LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')  WHERE hbi.`id` = '.(int)$id;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Deprecated
     * [hotelsNameAndId : To get array of All created Hotels name and id].
     *
     * @return [array | false] [If no hotel found then returns false otherwise returns array of all hotels name and id]
     */
    public function hotelsNameAndId()
    {
        $idLang = Context::getContext()->language->id;
        $sql = 'SELECT hbi.`id`, hbl.`hotel_name` FROM `'._DB_PREFIX_.'htl_branch_info` hbi
            LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * [getUnassignedFeaturesHotelIds : To get array of hotels id and name To which no hotel features are assigned].
     *
     * @return [array | false] [If no hotel found then returns false otherwise returns array of all hotels name and id to which no features are assigned]
     */
    public function getUnassignedFeaturesHotelIds()
    {
        $idLang = Context::getContext()->language->id;
        $sql = 'SELECT hbi.`id`, hbl.`hotel_name` FROM `'._DB_PREFIX_.'htl_branch_info` hbi
            LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')
            WHERE hbi.`id` NOT IN (SELECT DISTINCT id_hotel FROM `'._DB_PREFIX_.'htl_branch_features`)';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * [getFeaturesOfHotelByHotelId : To get assigned Features of a hotel by its id].
     *
     * @param [type] $id_hotel [id of the hotel]
     *
     * @return [array | false] [If no feature found then returns false otherwise returns array of all features assigned to the hotel]
     */
    public function getFeaturesOfHotelByHotelId($id_hotel)
    {
        return Db::getInstance()->executeS(
            'SELECT feature_id FROM `'._DB_PREFIX_.'htl_branch_features` WHERE id_hotel='.(int)$id_hotel
        );
    }

    /**
     * [getFeaturesOfHotelByHotelId : To get Hotel's id by its category id].
     *
     * @param [int] $id_category [id_category of the hotel]
     *
     * @return [array | int] [If no hotel found then returns false otherwise returns id of the hotel]
     */
    public static function getHotelIdByIdCategory($id_category)
    {
        $cache_key = 'HotelBranchInformation::getHotelIdByIdCategory'.(int)$id_category;
        if (!Cache::isStored($cache_key)) {
            $res = Db::getInstance()->getValue(
                'SELECT `id` FROM `'._DB_PREFIX_.'htl_branch_info` WHERE id_category = '.(int)$id_category
            );
            Cache::store($cache_key, $res);
        } else {
            $res = Cache::retrieve($cache_key);
        }

        return $res;
    }

    /**
     * [getFeaturesOfHotelByHotelId : To get Category Information by its id_category].
     *
     * @param [int] $id_category [id of the category , Which innformation is wanted]
     *
     * @return [array | false] [If no Category found then returns false otherwise returns array of information of that category]
     */
    public function getCategoryDataByIdCategory($id_category)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'category_lang` WHERE id_category = '.(int)$id_category
        );
    }

    /**
     * [hotelBranchInfoByCategoryId : To get active Hotels which belongs to the passed category Id]
     * @param  [int] $cat_id [id of the category]
     * @return [array | false]         [retuns array of the information of hotels belongs to the category if found otherwise false]
     */
    public function hotelBranchInfoByCategoryId($cat_id)
    {
        $idLang = Context::getContext()->language->id;
        $sql = 'SELECT hbi.`id`, hbi.`id_category`, hbl.`hotel_name` FROM `'._DB_PREFIX_.'htl_branch_info` hbi
            LEFT JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')
            WHERE hbi.`id_category` = '.(int)$cat_id.' AND `active` = 1';

        return Db::getInstance()->getRow($sql);
    }

    /**
     * [getHotelCategoryTree : Select category info by name of the category]
     * @param  [string] $searchData [description]
     * @return [type]              [description]
     */
    public function getHotelCategoryTree($searchData)
    {
        $context = Context::getContext();

        $locationCategory = new Category(Configuration::get('PS_LOCATIONS_CATEGORY'));

        return Db::getInstance()->executeS(
            'SELECT cl.`id_category` , cl.`name`
            FROM `'._DB_PREFIX_.'category_lang` AS cl
            INNER JOIN `'._DB_PREFIX_.'category` AS c ON (cl.`id_category` = c.`id_category`)
            WHERE cl.`name` LIKE \'%'.pSQL($searchData).'%\'
            AND c.`level_depth` NOT IN (0, 1, 6) and cl.`id_lang`='.(int)$context->language->id.'
            AND c.`nleft` > '.(int)$locationCategory->nleft.' AND c.`nright` < '.(int)$locationCategory->nright.'
            GROUP BY cl.`name`'
        );
    }

    /**
     * [getMapFormatHotelsInfo To get address ofthe hotel in the format to get the latitude and longitude of the place by using address]
     * @return [array] [description]
     */
    public function getMapFormatHotelsInfo($active = false, $idLang = false)
    {
        if (!$idLang) {
            $context = Context::getContext();
            $idLang = $context->language->id;
        }

        $sql = 'SELECT hbl.`hotel_name`, a.`phone`, hbi.`email`, a.`city`, cl.`name` AS country, a.`postcode`,
            a.`address1` as `address`, hbi.`latitude`, hbi.`longitude`, hbi.`map_formated_address`, hbi.`map_input_text`
            FROM `'._DB_PREFIX_.'htl_branch_info` AS hbi
            INNER JOIN `'._DB_PREFIX_.'htl_branch_info_lang` hbl
            ON (hbl.`id` = hbi.`id` AND hbl.`id_lang` = '.(int)$idLang.')
            LEFT JOIN `'._DB_PREFIX_.'address` a ON (a.`id_hotel` = hbi.`id`)
            INNER JOIN `'._DB_PREFIX_.'country_lang` AS cl
            ON (cl.`id_country` = a.`id_country` AND cl.`id_lang` = '.(int)$idLang.")
            WHERE hbi.`latitude` != 0 AND hbi.`longitude` != 0";

        if ($active !== false) {
            if ($active) {
                $sql .= " AND hbi.`active` = ".(int)$active;
            }
        }

        return Db::getInstance()->executeS($sql);
    }
    public function getAllHotels()
    {
        return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_branch_info`');
    }

    //Overrided ObjectModet::update() to update all the dependencies of the hotel
    public function update($null_values = false)
    {
        $objHotelInfo = new HotelBranchInformation($this->id);
        $oldStatus = $objHotelInfo->active;

        if ($return = parent::update()) {
            if ($oldStatus && !$this->active) {
                $objHotelRoomType = new HotelRoomType();
                $idsProduct = $objHotelRoomType->getIdProductByHotelId($this->id);
                if (isset($idsProduct) && $idsProduct) {
                    foreach ($idsProduct as $key_prod => $value_prod) {
                        $objProduct = new Product($value_prod['id_product']);
                        if ($objProduct->active) {
                            $objProduct->toggleStatus();
                        }
                    }
                }
            }
            if ($hotelsInfo = $this->hotelBranchesInfo(false, 1)) {
                // If more than one hotel then
                if (count($hotelsInfo) > 1) {
                    Configuration::updateValue('WK_HOTEL_NAME_ENABLE', 1);
                }
            }
        } else {
            return false;
        }

        return $return;
    }

    public function getCategoryParams($params)
    {
        if (!isset($params['parent_category'])) {
            $params['parent_category'] = false;
        }

        if (!isset($params['is_hotel'])) {
            $params['is_hotel'] = false;
        }

        if (!isset($params['id_hotel'])) {
            $params['id_hotel'] = 0;
        }

        if (!isset($params['link_rewrite'])) {
            $params['link_rewrite'] = false;
        }

        if (!isset($params['meta_title'])) {
            $params['meta_title'] = false;
        }

        if (!isset($params['meta_description'])) {
            $params['meta_description'] = false;
        }

        if (!isset($params['meta_keywords'])) {
            $params['meta_keywords'] = false;
        }

        return $params;
    }

    /**
     * Send parameters in array form
     * @param array $params
     *  $params['name']: [name of the category]
     *  $params['group_ids']: [group_ids of the category]
     *  $params['parent_category']: [parent_category of the category]
     *  $params['is_hotel']: [is_hotel = 1 if category is for hotel]
     *  $params['id_hotel']: [id_hotel of the category, if category is for hotel]
     *  $params['link_rewrite']: [link_rewrite of the category]
     *  $params['meta_title']: [meta_title of the category]
     *  $params['meta_description']: [meta_description of the category]
     *  $params['meta_keywords']: [meta_keywords of the category]
     * @return int  returns ID of the category added.
     */
    public function addCategory(array $params)
    {
        extract($this->getCategoryParams($params));
        $context = Context::getContext();
        if (!$parent_category) {
        $parent_category = Configuration::get('PS_LOCATIONS_CATEGORY');
        }

        if (is_array($name) && isset($name[Configuration::get('PS_LANG_DEFAULT')])) {
            $catName = $name[Configuration::get('PS_LANG_DEFAULT')];
        } else {
            $catName = $name;
        }
        if ($categoryExists = Category::searchByNameAndParentCategoryId(
            Configuration::get('PS_LANG_DEFAULT'),
            $catName,
            $parent_category
        )) {
            if (is_array($link_rewrite)) {
                $objCategory = new Category($categoryExists['id_category']);

                foreach (Language::getLanguages() as $lang) {
                    $objCategory->link_rewrite[$lang['id_lang']] = $link_rewrite[$lang['id_lang']];
                }

                $objCategory->save();
            }
            return $categoryExists['id_category'];
        } else {
            $category = new Category();
            foreach (Language::getLanguages(true) as $lang) {
                if (is_array($name) && isset($name[$lang['id_lang']])) {
                    $catName = $name[$lang['id_lang']];
                } else {
                    $catName = $name;
                }
                $category->name[$lang['id_lang']] = $catName;
                $category->description[$lang['id_lang']] = $this->moduleInstance->l('Hotel Branch Category', 'HotelBranchInformation');

                if (is_array($link_rewrite)) {
                    $category->link_rewrite[$lang['id_lang']] = $link_rewrite[$lang['id_lang']];
                } else {
                    $category->link_rewrite[$lang['id_lang']] = Tools::link_rewrite($catName);
                }
            }

            if ($meta_title) {
                $category->meta_title = $meta_title;
            }
            if ($meta_description) {
                $category->meta_description = $meta_description;
            }
            if ($meta_keywords) {
                $category->meta_keywords = $meta_keywords;
            }

            $category->id_parent = $parent_category;
            $category->groupBox = $group_ids;
            $category->add();

            return $category->id;
        }
    }

    //Overrided ObjectModet::delete() to delete all the dependencies of the hotel
    public function delete()
    {
        $contextController = Context::getContext()->controller;
        if ($idHotel = $this->id) {
            // room types of this hotel
            $objHotelRoomType = new HotelRoomType();
            $idsProduct = $objHotelRoomType->getIdProductByHotelId($idHotel);

            if (isset($idsProduct) && $idsProduct) {
                foreach ($idsProduct as $key_prod => $value_prod) {
                    $objProduct = new Product($value_prod['id_product']);
                    if (!$objProduct->delete()) {
                        $contextController->errors[] = $this->moduleInstance->l(
                            'Some error has occurred while deleting products of this hotel.',
                            'HotelBranchInformation'
                        );
                    }
                }
            }
            $objHotelfeatures = new HotelBranchFeatures();
            $objHotelImage = new HotelImage();
            if (!$objHotelfeatures->deleteBranchFeaturesByHotelId($idHotel)) {
                $contextController->errors[] = $this->moduleInstance->l(
                    'Some error has occurred while deleting hotel feature data.',
                    'HotelBranchInformation'
                );
            }
            $hotelAllImages = $objHotelImage->getImagesByHotelId($idHotel);
            if ($hotelAllImages) {
                foreach ($hotelAllImages as $key_img => $value_img) {
                    if (Validate::isLoadedObject($objHotelImage = new HotelImage((int) $value_img['id']))) {
                        $objHotelImage->deleteImage();
                    }
                }
            }
            if (!$objHotelImage->deleteByHotelId($idHotel)) {
                $contextController->errors[] = $this->moduleInstance->l(
                    'Some error has occurred while deleting images of hotel.',
                    'HotelBranchInformation'
                );
            }
            // delete hotel unused categories of this hotel
            if (!$this->deleteUnusedHotelCategories($idHotel)) {
                $contextController->errors[] = $this->moduleInstance->l(
                    'Some error has occurred while deleting unused hotel categories.',
                    'HotelBranchInformation'
                );
            }
        }
        if (!count($contextController->errors)) {
            // delete accesses of the hotel
            if (Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'htl_access WHERE `id_hotel` = '.(int)$this->id)
                && parent::delete()
            ) {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * returns all the categories used by all the hotels
     * @param array $excludeIdHotels  [array of the hotels which categories you dont want]
     * @return [array] returns array of all the categories used by all the hotels
     */
    public function getAllHotelCategories($excludeIdHotels = array(), $idHotel = false)
    {
        $hotelrelatedCategs = array();
        $sql = 'SELECT `id_category` FROM `'._DB_PREFIX_.'htl_branch_info` WHERE 1';
        if ($excludeIdHotels && count($excludeIdHotels)) {
            $sql .= ' AND `id` NOT IN ('.implode(',', $excludeIdHotels).')';
        }

        if ($idHotel) {
            $sql .= ' AND `id`='.(int) $idHotel;
        }

        // get all the hotel name categories
        if ($hotelCategs = Db::getInstance()->executeS($sql)) {
            foreach ($hotelCategs as $rowCateg) {
                if (Validate::isLoadedObject($objCategory = new Category($rowCateg['id_category']))) {
                    if ($parentCategs = $objCategory->getParentsCategories(Configuration::get('PS_LANG_DEFAULT'))) {
                        foreach ($parentCategs as $categInfo) {
                            // enter only unique categories in the array
                            if (!in_array($categInfo['id_category'], $hotelrelatedCategs)) {
                                $hotelrelatedCategs[] = $categInfo['id_category'];
                            }
                        }
                    }
                }
            }
        }
        return $hotelrelatedCategs;
    }

    /**
     * Deletes all the unused categories created by hotel creation of a hotel
     * @param [int] $idHotel which categories you want to delete
     * @return true if all the categories will be deleted
     */
    public function deleteUnusedHotelCategories($idHotel)
    {
        if (Validate::isLoadedObject($objHotel = new HotelBranchInformation($idHotel))) {
            $idCategory = $objHotel->id_category;
            if (Validate::isLoadedObject($objCategory = new Category($idCategory))) {
                $hotelCategories = $this->getAllHotelCategories(array($idHotel));
                // check if category is not root or home category and not used by other hotels
                while ($idCategory
                    && !in_array($idCategory, $hotelCategories)
                    && $idCategory != Configuration::get('PS_HOME_CATEGORY')
                    && $idCategory != Configuration::get('PS_LOCATIONS_CATEGORY')
                ) {
                    if ($objCategory->delete()) {
                        // continue deleting the unused parent hotel categories
                        $idCategory = $objCategory->id_parent;
                        $objCategory = new Category($idCategory);
                    }
                }
            }
        }
        return true;
    }

    public function updateRoomTypeCategories()
    {
        if (!Validate::isLoadedObject($this)) {
            return false;
        }

        $hotelCategories = $this->getAllHotelCategories(array(), $this->id);
        $objHotelRoomType = new HotelRoomType();
        if ($roomTypes = $objHotelRoomType->getRoomTypeByHotelId($this->id, Context::getContext()->language->id)) {
            foreach ($roomTypes as $roomType) {
                $objProduct = new Product($roomType['id_product']);
                $objProduct->updateCategories($hotelCategories);
            }
        }
    }

    public function isRefundable()
    {
        return (Configuration::get('WK_ORDER_REFUND_ALLOWED') && $this->active_refund);
    }

    public static function addHotelRestriction($idsHotel, $alias = null, $identifier = 'id_hotel', $idProfile = null)
    {
        if (!$idProfile) {
            $idProfile = Context::getContext()->employee->id_profile;
        }

        $accessedIds = self::getProfileAccessedHotels($idProfile, 1, 1);
        if (!$idsHotel) {
            $idsHotel = $accessedIds;
        } else {
            if (!is_array($idsHotel)) {
                $idsHotel = array($idsHotel);
            }
            // check if passed hotel id's are available for current employee
            $idsHotel = array_filter($idsHotel, function ($idHotel) use($accessedIds)  {
                return in_array($idHotel, $accessedIds);
            });
        }

        $restriction = ' AND ';
        if (count($idsHotel)) {
            if ($alias) {
                $alias .= '.';
            }

            $identifier = "`$identifier`";
            $restriction .= $alias.$identifier.' IN ('.implode(', ', $idsHotel).') ';
        } else {
            $restriction .= 0;
        }

        return $restriction;
    }

    // Webservice getter : get virtual field id_default_image
    public function getCoverWs()
    {
        if ($result = HotelImage::getCover($this->id)) {
            // we are sending id_hotel/id_image as per the url set for the hotel images
            return $result['id'];
        }
        return false;
    }

    /**
    * Webservice setter : set virtual field id_default_image in hotel images
    * @return bool
    */
    public function setCoverWs($id_image)
    {
        if ($id_image) {
            // first unset the cover
            Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'htl_image` SET `cover` = NULL
                WHERE `id_hotel` = '.(int)$this->id);

            // set the sent id of the image to the cover of the hotel
            Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'htl_image` SET `cover` = 1 WHERE `id` = '.(int)$id_image.' AND `id_hotel` = '.(int)$this->id);
        }

        return true;
    }

    // Webservice:: function to prepare id parameter for hotel images in a hotel api
    public function getWsImages()
    {
        $ids = array();
        if ($hotelImages =  Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'htl_image` WHERE `id_hotel` = '.(int)$this->id)) {
            foreach ($hotelImages as $key => $image) {
                // we are sending id_hotel/id_image as per the url set for the hotel images
                $ids[$key]['id'] = $image['id'];
            }
        }
        return $ids;
    }

    // Webservice:: function to prepare id parameter for hotel features in a hotel api
    public function getWsHotelFeatures()
    {
        return Db::getInstance()->executeS(
            'SELECT `feature_id` as `id` FROM `'._DB_PREFIX_.'htl_branch_features` WHERE `id_hotel` = '.(int)$this->id.
            ' ORDER BY `feature_id` ASC'
        );
    }

    // Webservice:: function to prepare id parameter for hotel features in a hotel api
    public function setWsHotelFeatures($branchFeatures)
    {
        Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'htl_branch_features`
			WHERE `id_hotel` = '.(int)$this->id
        );

        foreach ($branchFeatures as $feature) {
            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'htl_branch_features` (`id_hotel`, `feature_id`, `date_add`, `date_upd`) VALUES ('.(int)$this->id.', '.(int)$feature['id'].', NOW(), NOW())');
        }

        return true;
    }

    // Webservice:: function to get hotel refund rules in a hotel api
    public function getWsHotelRefundRules()
    {
        return Db::getInstance()->executeS(
            'SELECT `id_refund_rule` as `id` FROM `'._DB_PREFIX_.'htl_branch_refund_rules` WHERE `id_hotel` = '.(int)$this->id.' ORDER BY `id_refund_rule` ASC'
        );
    }

    // Webservice:: function to set hotel refund rules in a hotel api
    public function setWsHotelRefundRules($refundRules)
    {
        Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'htl_branch_refund_rules`
            WHERE `id_hotel` = '.(int)$this->id
        );

        foreach ($refundRules as $rule) {
            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'htl_branch_refund_rules` (`id_hotel`, `id_refund_rule`, `date_add`, `date_upd`) VALUES ('.(int)$this->id.', '.(int)$rule['id'].', NOW(), NOW())');
        }

        return true;
    }

    // Webservice :: get room types of the hotel
    public function getWsRoomTypes()
    {
        return Db::getInstance()->executeS(
            'SELECT `id_product` as `id` FROM `'._DB_PREFIX_.'htl_room_type` WHERE `id_hotel` = '.(int)$this->id.' ORDER BY `id` ASC'
        );
    }

    // Webservice :: function will run when hotel added from API
    public function addWs($autodate = true, $null_values = false)
    {
        if ($this->add($autodate, $null_values)) {
            // Set hotel address
            $this->setWsHotelAddress();

            // set hotel restrictions
            $this->setWsHotelRestrictions();

            // set categories of the hotel
            $this->setWsHotelCategories();

            return true;
        }

        return false;
    }

    // Webservice :: function will run when hotel updated from API
    public function updateWs($null_values = false)
    {
        if ($this->update($null_values)) {
            // Set hotel address
            $this->setWsHotelAddress();

            // set hotel restrictions
            $this->setWsHotelRestrictions();

            // set categories of the hotel
            $this->setWsHotelCategories();

            // update room categories after the hotel category is updated.
            $this->updateRoomTypeCategories();

            return true;
        }

        return false;
    }

    public function setWsHotelAddress()
    {
        if ($idHotelAddress = $this->getHotelIdAddress()) {
            $objAddress = new Address($idHotelAddress);
        } else {
            $objAddress = new Address();
        }

        $hotelName = $this->hotel_name[Configuration::get('PS_LANG_DEFAULT')];
        $hotelName = trim(preg_replace('/[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $hotelName));
        $objAddress->id_hotel = $this->id;
        $objAddress->id_country = $this->id_country;
        $objAddress->id_state = $this->id_state;
        $objAddress->city = $this->city;
        $objAddress->postcode = $this->zipcode;
        $objAddress->address1 = $this->address;
        $objAddress->phone = $this->phone;
        $objAddress->alias = substr($hotelName, 0, 32);
        $addressFirstName = $hotelName;
        $addressLastName = $hotelName;
        if (Tools::strlen($hotelName) > 32) {
            $addressFirstName = trim(substr($hotelName, 0, 32));
            if (!$addressLastName = trim(substr($hotelName, 32, 32))) {
                $addressLastName = $addressFirstName;
            }
        }

        $objAddress->firstname = $addressFirstName;
        $objAddress->lastname = $addressLastName;

        return $objAddress->save();
    }

    public function setWsHotelRestrictions()
    {
        if ($this->id) {
            // save Maximum checkout offset and min booking offset
            if ($restrictDateInfo = HotelOrderRestrictDate::getDataByHotelId($this->id)) {
                $objHotelRestrictDate = new HotelOrderRestrictDate($restrictDateInfo['id']);
            } else {
                $objHotelRestrictDate = new HotelOrderRestrictDate();
            }

            $objHotelRestrictDate->id_hotel = $this->id;
            $objHotelRestrictDate->use_global_max_checkout_offset = $this->use_global_max_checkout_offset;
            if (!$this->use_global_max_checkout_offset) {
                $objHotelRestrictDate->max_checkout_offset = $this->max_checkout_offset;
            }
            $objHotelRestrictDate->use_global_min_booking_offset = $this->use_global_min_booking_offset;
            if (!$this->use_global_min_booking_offset) {
                $objHotelRestrictDate->min_booking_offset = $this->min_booking_offset;
            }

            return $objHotelRestrictDate->save();
        }
    }

    public function validateFields($die = true, $error_return = false)
    {
        // set additional hotel address and restriction fields validations here
        if (isset($this->webservice_validation) && $this->webservice_validation) {
            if ($this->rating < 1 || $this->rating > 5) {
                $message = Tools::displayError('Rating must be between 1 and 5.');
            } elseif (!strtotime($this->check_in)) {
                $message = Tools::displayError('Check In time is invalid.');
            } elseif (!strtotime($this->check_out)) {
                $message = Tools::displayError('Check out time is invalid.');
            } elseif ($this->check_in && $this->check_out && strtotime($this->check_out) >= strtotime($this->check_in)) {
                $message = Tools::displayError('Check Out time must be before Check In time.');
            } elseif (!trim($this->phone)) {
                $message = Tools::displayError('Phone number is required field.');
            } elseif (!Validate::isPhoneNumber($this->phone)) {
                $message = Tools::displayError('Please enter a valid phone number.');
            } elseif (trim($this->address) == '') {
                $message = Tools::displayError('Address is required field.');
            } elseif (!Validate::isAddress($this->address)) {
                $message = Tools::displayError('Address is invalid.');
            } elseif (!$this->id_country) {
                $message = Tools::displayError('Country is required field.');
            } elseif ($this->city == '') {
                $message = Tools::displayError('City is required field.');
            } elseif (!Validate::isCityName($this->city)) {
                $message = Tools::displayError('Enter a Valid City Name.');
            } elseif (!Validate::isBool($this->use_global_max_checkout_offset)) {
                $message = Tools::displayError('invalid value for use global max checkout offset.');
            } elseif (!Validate::isBool($this->use_global_min_booking_offset)) {
                $message = Tools::displayError('invalid value for use global min booking offset.');
            } elseif (!$this->use_global_max_checkout_offset && $this->max_checkout_offset == '') {
                $message = Tools::displayError('Maximum checkout offset is required.');
            } elseif (!$this->use_global_max_checkout_offset && (!$this->max_checkout_offset || !Validate::isUnsignedInt($this->max_checkout_offset))) {
                $message = Tools::displayError('Maximum checkout offset is invalid.');
            } elseif (!$this->use_global_min_booking_offset && $this->min_booking_offset === '') {
                $message = Tools::displayError('Minimum booking offset is a required.');
            } elseif (!$this->use_global_min_booking_offset && !Validate::isUnsignedInt($this->min_booking_offset)) {
                $message = Tools::displayError('Minimum booking offset is invalid.');
            } else if (!$this->use_global_min_booking_offset
                && !$this->use_global_max_checkout_offset
                && $this->max_checkout_offset
                && $this->max_checkout_offset <= $this->min_booking_offset
            ) {
                $message = Tools::displayError('Maximum checkout offset cannot be less than or equal to Minimum booking offset.');
            } else if (!$this->use_global_max_checkout_offset
                && $this->use_global_min_booking_offset
                && $this->max_checkout_offset <= Configuration::get('PS_MIN_BOOKING_OFFSET')
            ) {
                $message = Tools::displayError('Maximum checkout offset cannot be less than or equal to Global Minimum booking offset.');
            } else if ($this->use_global_max_checkout_offset
                && !$this->use_global_min_booking_offset
                && $this->min_booking_offset >= Configuration::get('PS_MAX_CHECKOUT_OFFSET')
            ) {
                $message = Tools::displayError('Minimum booking offset cannot be be greater than or equal to Global Maximum checkout offset.');
            } elseif (!Validate::isLoadedObject($objCountry = new Country($this->id_country))) {
                $message = Tools::displayError('country is invalid.');
            } elseif ($this->id_state
                && (!Validate::isLoadedObject($objState = new State($this->id_state)) || $objState->id_country != $this->id_country)
            ) {
                $message = Tools::displayError('State is invalid.');
            } elseif (State::getStatesByIdCountry($this->id_country) && !$this->id_state) {
                $message = Tools::displayError('State is required field.');
            } elseif (empty($this->zipcode) && $objCountry->need_zip_code) {
                $message = Tools::displayError('A Zip / Postal code is required.');
            } elseif ($objCountry->zip_code_format && !$objCountry->checkZipCode($this->zipcode)) {
                $message = sprintf(Tools::displayError('The Zip/Postal code you have entered is invalid. It must follow this format: %s'), str_replace('C', $objCountry->iso_code, str_replace('N', '0', str_replace('L', 'A', $objCountry->zip_code_format))));
            } elseif ($this->zipcode && !Validate::isPostCode($this->zipcode)) {
                $message = Tools::displayError('The Zip / Postal code is invalid.');
            } elseif ($this->fax && !Validate::isGenericName($this->fax)) {
                $message = Tools::displayError('The Fax is invalid.');
            } else {
                if ($addressValidation = Address::getValidationRules('Address')) {
                    foreach ($addressValidation['size'] as $field => $maxSize) {
                        if ('phone' == $field && Tools::strlen($this->phone) > $maxSize) {
                            $message = sprintf(
                                Tools::displayError('The Hotel phone number is too long (%1$d chars max).'),
                                $maxSize
                            );
                            break;
                        } elseif ('address1' == $field && Tools::strlen($this->address) > $maxSize) {
                            $message = sprintf(
                                Tools::displayError('The Hotel address is too long (%1$d chars max).'),
                                $maxSize
                            );
                            break;
                        }  elseif ('city' == $field && Tools::strlen($this->city) > $maxSize) {
                            $message = sprintf(
                                Tools::displayError('The Hotel city name is too long (%1$d chars max).'),
                                $maxSize
                            );
                            break;
                        } elseif ('postcode' == $field && Tools::strlen($this->zipcode) > $maxSize) {
                            $message = sprintf(
                                Tools::displayError('The Hotel zip code is too long (%1$d chars max).'),
                                $maxSize
                            );
                            break;
                        }
                    }
                }
            }

            if (isset($message) && $message != '') {
                if ($die) {
                    throw new PrestaShopException($message);
                }

                return $error_return ? $message : false;
            }
        }

        return parent::validateFields($die, $error_return);
    }

    /**
     * @see ObjectModel::validateField()
     */
    public function validateField($field, $value, $id_lang = null, $skip = array(), $human_errors = false)
    {
        if ($field == 'short_description') {
            $limit = (int)Configuration::get('PS_SHORT_DESC_LIMIT');
            if ($limit <= 0) {
                $limit = Configuration::PS_SHORT_DESC_LIMIT;
            }

            $this->def['fields']['short_description']['size'] = $limit;
        }

        return parent::validateField($field, $value, $id_lang, $skip, $human_errors);
    }

    // Webservice :: function will run when hotel deleted from API
    public function deleteWs()
    {
        if ($this->delete()) {
            return true;
        }
        return false;
    }

    // Webservice:: create country, state, city, hotel categories after hotel creation
    public function setWsHotelCategories()
    {
        if ($this->id) {
            // hotel categories before save categories
            $categsBeforeUpd = $this->getAllHotelCategories();


            $idLang = Configuration::get('PS_LANG_DEFAULT');
            $groupIds = array();
            if ($dataGroupIds = Group::getGroups($idLang)) {
                foreach ($dataGroupIds as $key => $value) {
                    $groupIds[] = $value['id_group'];
                }
            }
            $objCountry = new Country();
            $countryName = $objCountry->getNameById($idLang, $this->id_country);
            $linkRewriteArray =  array();
            foreach (Language::getLanguages(true) as $lang) {
                $linkRewriteArray[$lang['id_lang']] = Tools::link_rewrite($this->hotel_name[$lang['id_lang']]);
            }

            if ($catCountry = $this->addCategory(
                array (
                    'name' => $countryName,
                    'group_ids' => $groupIds,
                    'parent_category' => false
                )
            )) {
                if ($this->id_state) {
                    $objState = new State();
                    $stateName = $objState->getNameById($this->id_state);
                } else {
                    $stateName = $this->city;
                }

                if ($catState = $this->addCategory(
                    array (
                        'name' => $stateName,
                        'group_ids' => $groupIds,
                        'parent_category' => $catCountry
                    )
                )) {
                    if ($catCity = $this->addCategory(
                        array (
                            'name' => $this->city,
                            'group_ids' => $groupIds,
                            'parent_category' => $catState
                        )
                    )) {
                        $hotelCatName = $this->hotel_name[Configuration::get('PS_LANG_DEFAULT')];
                        if ($catHotel = $this->addCategory(
                            array (
                                'name' => $hotelCatName,
                                'group_ids' => $groupIds,
                                'parent_category' => $catCity,
                                'is_hotel' => 1,
                                'id_hotel' => $this->id,
                                'link_rewrite' => $linkRewriteArray
                            )
                        )) {
                            $this->id_category = $catHotel;
                            $this->save();
                        }
                    }
                }
            }

            // hotel categories after save categories
            $categsAfterUpd = $this->getAllHotelCategories();

            // delete categories which not in hotel categories and also unused
            if ($unusedCategs = array_diff($categsBeforeUpd, $categsAfterUpd)) {
                if ($hotelCategories = $this->getAllHotelCategories()) {
                    foreach ($unusedCategs as $idCategory) {
                        if (!in_array($idCategory, $hotelCategories)
                            && $idCategory != Configuration::get('PS_HOME_CATEGORY')
                        ) {
                            $objCategory = new Category($idCategory);
                            $objCategory->delete();
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    public function searchByName($query, $idEmployeeProfile, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $sql = 'SELECT a.`id`, a.`active`, hbl.`hotel_name`, aa.`city`, s.`name` AS `state_name`, cl.`name` AS `country_name`
            FROM `'._DB_PREFIX_.'htl_branch_info` a
            LEFT JOIN  `'._DB_PREFIX_.'htl_branch_info_lang` hbl ON hbl.`id` = a.`id` AND hbl.`id_lang` = '.(int)$idLang.'
            LEFT JOIN  `'._DB_PREFIX_.'address` aa ON aa.`id_hotel` = a.`id`
            LEFT JOIN `'._DB_PREFIX_.'state` s ON s.`id_state` = aa.`id_state`
            LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON cl.`id_country` = aa.`id_country` AND cl.`id_lang` = hbl.`id_lang`
            LEFT JOIN `'._DB_PREFIX_.'htl_access` ha ON a.`id` = ha.`id_hotel`
            WHERE hbl.`hotel_name` LIKE \'%'.pSQL($query).'%\'
            AND ha.`access`= 1 AND ha.`id_profile` = '.(int) $idEmployeeProfile;

        return Db::getInstance()->executeS($sql);
    }

}
