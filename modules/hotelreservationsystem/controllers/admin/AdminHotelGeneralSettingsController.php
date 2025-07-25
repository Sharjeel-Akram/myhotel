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

class AdminHotelGeneralSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'configuration';
        $this->className = 'Configuration';
        $this->bootstrap = true;
        parent::__construct();

        $psImgUrl = $this->context->link->getMediaLink(_PS_IMG_.Configuration::get('WK_HOTEL_HEADER_IMAGE'));
        if ($imgExist = (bool)Tools::file_get_contents($psImgUrl)) {
            $image = '<img class="img-thumbnail img-responsive" style="max-width:200px" src="'.$psImgUrl.'">';
        }
        $objHotelInfo = new HotelBranchInformation();
        if (!$hotelsInfo = $objHotelInfo->hotelBranchesInfo(false, 1)) {
            $hotelsInfo = array();
        }
        foreach ($hotelsInfo as &$hotel) {
            $hotel['name'] = $hotel['hotel_name'];
        }
        $hotelNameDisable = (count($hotelsInfo) > 1 ? true : false);
        $locationDisable = ((count($hotelsInfo) < 2) && !Configuration::get('WK_HOTEL_NAME_ENABLE')) ? true : false;

        $countryList = array();
        $countryList[] = array('id' => '0', 'name' => $this->l('Choose your country'));
        foreach (Country::getCountries($this->context->language->id) as $country) {
            $countryList[] = array('id' => $country['id_country'], 'name' => $country['name']);
        }
        $stateList = array();
        $stateList[] = array('id' => '0', 'name' => $this->l('Choose your state (if applicable)'));
        foreach (State::getStates($this->context->language->id) as $state) {
            $stateList[] = array('id' => $state['id_state'], 'name' => $state['name']);
        }
        $this->fields_options = array(
            'hotelsearchpanel' => array(
                'icon' => 'icon-search',
                'title' => $this->l('Search Panel Setting'),
                'fields' => array(
                    'WK_HOTEL_LOCATION_ENABLE' => array(
                        'title' => $this->l('Enable Hotel Location'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'disabled' => $locationDisable,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Either display Hotel location field on hotel search panel or hide it.'),
                    ),
                    'WK_HOTEL_NAME_ENABLE' => array(
                        'title' => $this->l('Display Hotel Name'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '0',
                        'disabled' => $hotelNameDisable,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('This option can be disabled if only one active hotel in the website. In case of more than one active hotel, Hotel Name will always be shown in the search panel.'),
                    ),
                    'WK_SEARCH_AUTO_FOCUS_NEXT_FIELD' => array(
                        'title' => $this->l('Focus next field automatically'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '0',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Enable if you want the next booking search field to be focused automatically after setting value for a field.'),
                    ),
                    'WK_HOTEL_NAME_SEARCH_THRESHOLD' => array(
                        'title' => $this->l('Hotel name search threshold'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedInt',
                        'hint' => $this->l('Enter the number of hotels after which user can search hotel by name.'),
                        'desc' => $this->l('Set to 0 to always show the search box.'),
                        'class' => 'fixed-width-xxl',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
            'generalsetting' => array(
                'title' => $this->l('Website Configuration'),
                'fields' => array(
                    'WK_HTL_CHAIN_NAME' => array(
                        'title' => $this->l('Hotel Name'),
                        'type' => 'textLang',
                        'lang' => true,
                        'required' => true,
                        'validation' => 'isGenericName',
                        'hint' => $this->l('Enter Hotel name in case of single hotel or enter your hotels chain name in case of multiple hotels.'),
                    ),
                    'WK_HTL_TAG_LINE' => array(
                        'title' => $this->l('Hotel Tag Line'),
                        'type' => 'textareaLang',
                        'lang' => true,
                        'required' => true,
                        'validation' => 'isGenericName',
                        'hint' => $this->l('This will display hotel tag line in hotel page.'),
                    ),
                    'WK_HTL_SHORT_DESC' => array(
                        'title' => $this->l('Hotel Short Description'),
                        'type' => 'textareaLang',
                        'lang' => true,
                        'required' => true,
                        'rows' => '4',
                        'cols' => '2',
                        'validation' => 'isGenericName',
                        'hint' => $this->l('This will display hotel short description in footer. Note: number of letters must be less than 220.'),
                    ),
                    'WK_PRIMARY_HOTEL' => array(
                        'title' => $this->l('Primary hotel'),
                        'hint' => $this->l('Primary hotel is used to default address for your business. The hotel address will be considered as your registered business address.'),
                        'type' => 'select',
                        'identifier' => 'id',
                        'list' => $hotelsInfo,
                    ),
                    'WK_HTL_ESTABLISHMENT_YEAR' => array(
                        'title' => $this->l('Website Launch Year'),
                        'hint' => $this->l('The year when your hotel site was launched.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_HTL_HEADER_IMAGE' => array(
                        'title' => $this->l('Header Background Image'),
                        'type' => 'file',
                        'image' => $imgExist ? $image : false,
                        'hint' => $this->l('This image appears as header background image on home page.'),
                        'name' => 'WK_HOTEL_HEADER_IMAGE',
                        'url' => _PS_IMG_,
                    ),
                     'WK_DISPLAY_PROPERTIES_LINK_IN_HEADER' => array(
                        'title' => $this->l('Display Our Properties link in Header'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '0',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Display Our Properties link in header in the front office'),
                    ),
                    'WK_DISPLAY_CONTACT_PAGE_HOTEL_LIST' => array(
                        'title' => $this->l('Display Contact Page Hotel List'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '0',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Enable to display hotels list on the contact us page.'),
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'contactdetail' => array(
                'icon' => 'icon-phone',
                'title' => $this->l('Support Contact Details'),
                'fields' => array(
                    'WK_CUSTOMER_SUPPORT_PHONE_NUMBER' => array(
                        'title' => $this->l('Support Phone Number'),
                        'type' => 'text',
                        'hint' => $this->l('The phone number used for customer service. It will be shown on navigation bar.'),
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_CUSTOMER_SUPPORT_EMAIL' => array(
                        'title' => $this->l('Support Email'),
                        'type' => 'text',
                        'hint' => $this->l('The email used for customer service. It will be shown on navigation bar.'),
                        'class' => 'fixed-width-xxl',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
            'websitedetail' => array(
                'title' => $this->l('Website Contact Details'),
                'icon' => 'icon-user',
                'fields' => array(
                    'PS_SHOP_NAME' => array(
                        'title' => $this->l('Website name'),
                        'hint' => $this->l('Displayed in emails and page titles.'),
                        'validation' => 'isGenericName',
                        'required' => true,
                        'type' => 'text',
                        'no_escape' => true,
                    ),
                    'PS_SHOP_EMAIL' => array(
                        'title' => $this->l('Website email'),
                        'hint' => $this->l('Displayed in emails sent to customers and on Contact Us page.'),
                        'validation' => 'isEmail',
                        'required' => true,
                        'type' => 'text'
                    ),
                    'PS_SHOP_PHONE' => array(
                        'title' => $this->l('Phone'),
                        'validation' => 'isGenericName',
                        'required' => true,
                        'type' => 'text',
                        'hint' => $this->l('The phone number of the main branch.'),
                    ),
                    'PS_SHOP_ADDR1' => array(
                        'title' => $this->l('Address line 1'),
                        'validation' => 'isAddress',
                        'type' => 'text',
                        'hint' => $this->l('The address of the main branch.'),
                        'isCleanHtml' => true,
                        'required' => true,
                    ),
                    'PS_SHOP_ADDR2' => array(
                        'title' => $this->l('Address line 2'),
                        'validation' => 'isAddress',
                        'type' => 'text'
                    ),
                    'PS_SHOP_CODE' => array(
                        'title' => $this->l('Zip/postal code'),
                        'validation' => 'isGenericName',
                        'type' => 'text'
                    ),
                    'PS_SHOP_CITY' => array(
                        'title' => $this->l('City'),
                        'validation' => 'isGenericName',
                        'type' => 'text'
                    ),
                    'PS_SHOP_COUNTRY_ID' => array(
                        'title' => $this->l('Country'),
                        'validation' => 'isInt',
                        'type' => 'select',
                        'list' => $countryList,
                        'identifier' => 'id',
                        'cast' => 'intval',
                        'defaultValue' => (int)$this->context->country->id
                    ),
                    'PS_SHOP_STATE_ID' => array(
                        'title' => $this->l('State'),
                        'validation' => 'isInt',
                        'type' => 'select',
                        'list' => $stateList,
                        'identifier' => 'id',
                        'cast' => 'intval'
                    ),
                    'PS_SHOP_DETAILS' => array(
                        'title' => $this->l('Registration number'),
                        'hint' => $this->l('Website registration information (e.g. SIRET or RCS).'),
                        'validation' => 'isGenericName',
                        'type' => 'textarea',
                        'cols' => 30,
                        'rows' => 5
                    ),
                    'PS_SHOP_FAX' => array(
                        'title' => $this->l('Fax'),
                        'validation' => 'isGenericName',
                        'type' => 'text'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
            'advancedPayment' => array(
                'title' => $this->l('Advance Payment Global Setting'),
                'fields' => array(
                    'WK_ALLOW_ADVANCED_PAYMENT' => array(
                        'title' => $this->l('Allow Advance Payment'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '1',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('If No, Advance Payment functionality will be disabled'),
                    ),
                    'WK_ADVANCED_PAYMENT_GLOBAL_MIN_AMOUNT' => array(
                        'title' => $this->l('Global Minimum Booking Amount'),
                        'hint' => $this->l('Enter Minimum amount to pay in percentage for booking aroom.'),
                        'type' => 'text',
                        'validation' => 'isUnsignedFloat',
                        'suffix' => $this->l('%'),
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_ADVANCED_PAYMENT_INC_TAX' => array(
                        'title' => $this->l('Global Booking Amount Include Tax'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '1',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Yes, if you want to take tax with Advance payment otherwise No.'),
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'occupancypanel' => array(
                'icon' => 'icon-users',
                'title' => $this->l('Occupancy Settings'),
                'fields' => array(
                    // max age of child
                    'WK_GLOBAL_CHILD_MAX_AGE' => array(
                        'title' => $this->l('Consider guest as child below age'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedInt',
                        'hint' => $this->l('Enter the age of the guest,  which that guest will be considered as child.'),
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_GLOBAL_MAX_CHILD_IN_ROOM' => array(
                        'title' => $this->l('Maximum children allowed in a room'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedInt',
                        'hint' => $this->l('Enter number of the child allowed in a room.'),
                        'desc' => $this->l('Set as 0 if you do not want to limit children in a room.'),
                        'class' => 'fixed-width-xxl',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
            'googleMap' => array(
                'title' => $this->l('Google Maps Settings'),
                'fields' => array(
                    'PS_API_KEY' => array(
                        'title' => $this->l('Google Maps API Key'),
                        'hint' => $this->l('Unique API key for Google Maps.'),
                        'type' => 'text',
                    ),
                    'PS_MAP_ID' => array(
                        'title' => $this->l('Google Map ID'),
                        'hint' => $this->l('Map Id for Google Maps.'),
                        'type' => 'text',
                        'desc' => $this->l('Google Maps API Key and Google Map ID is required to display Google Maps.')
                    ),
                    'WK_GOOGLE_ACTIVE_MAP' => array(
                        'title' => $this->l('Display Google Maps For Hotel Location'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '1',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Enable to display Google Maps for hotel locations. If disabled, Google Maps will not be shown.'),
                    ),
                    'WK_MAP_HOTEL_ACTIVE_ONLY' => array(
                        'title' => $this->l('Display Active Hotels Only'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '1',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('If yes, only active hotels will be displayed on map.'),
                    ),
                    'WK_DISPLAY_PROPERTIES_PAGE_GOOGLE_MAP' => array(
                        'title' => $this->l('Display Our Properties page Google map'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '0',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Enable to display map with hotels locations on the our properties page.'),
                    ),
                    'WK_DISPLAY_CONTACT_PAGE_GOOLGE_MAP' => array(
                        'title' => $this->l('Display Contact Us page Google map'),
                        'cast' => 'intval',
                        'type' => 'bool',
                        'default' => '0',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Enable to display map with hotels locations on the contact us page.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

    }

    public function beforeUpdateOptions()
    {
        if (isset($_POST['PS_SHOP_STATE_ID']) && $_POST['PS_SHOP_STATE_ID'] != '0') {
            $sql = 'SELECT `active` FROM `'._DB_PREFIX_.'state`
					WHERE `id_country` = '.(int)Tools::getValue('PS_SHOP_COUNTRY_ID').'
						AND `id_state` = '.(int)Tools::getValue('PS_SHOP_STATE_ID');
            $isStateOk = Db::getInstance()->getValue($sql);
            if ($isStateOk != 1) {
                $this->errors[] = Tools::displayError('The specified state is not located in this country.');
            }
        }
    }

    public function updateOptionPsShopCountryId($value)
    {
        if (!$this->errors && $value) {
            $country = new Country($value, $this->context->language->id);
            if ($country->id) {
                Configuration::updateValue('PS_SHOP_COUNTRY_ID', $value);
                Configuration::updateValue('PS_SHOP_COUNTRY', pSQL($country->name));
            }
        }
    }

    public function updateOptionPsShopStateId($value)
    {
        if (!$this->errors && $value) {
            $state = new State($value);
            if ($state->id) {
                Configuration::updateValue('PS_SHOP_STATE_ID', $value);
                Configuration::updateValue('PS_SHOP_STATE', pSQL($state->name));
            }
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitOptions'.$this->table)) {
            $hotelNameSearchThreshold = Tools::getValue('WK_HOTEL_NAME_SEARCH_THRESHOLD');

            // check if field is atleast in default language. Not available in default prestashop
            $defaultLangId = Configuration::get('PS_LANG_DEFAULT');
            $objDefaultLanguage = Language::getLanguage((int) $defaultLangId);
            $languages = Language::getLanguages(false);

            // Validation for the occupancy settings
            // max age of infant after which guest will considered as child // below 18
            $globalChildMaxAge = Tools::getValue('WK_GLOBAL_CHILD_MAX_AGE');
            $globalMaxChildInRoom = Tools::getValue('WK_GLOBAL_MAX_CHILD_IN_ROOM');
            if (!Validate::isUnsignedInt($globalChildMaxAge)) {
                $this->errors[] = $this->l('Invalid value for "Consider guest as child below age".');
            } else if ($globalChildMaxAge <= 0) {
                $this->errors[] = $this->l('The value for "Consider guest as child below age" must be at least 1.');
            }

            // End occupancy fields validation

            if (!$hotelNameSearchThreshold && $hotelNameSearchThreshold !== '0') {
                $this->errors[] = $this->l('Hotel name search threshold field is required.');
            } elseif (!Validate::isUnsignedInt($hotelNameSearchThreshold)) {
                $this->errors[] = $this->l('Hotel name search threshold field is invalid.');
            }

            if (!trim(Tools::getValue('WK_HTL_CHAIN_NAME_'.$defaultLangId))) {
                $this->errors[] = $this->l('Hotel chain name is required at least in ').$objDefaultLanguage['name'];
            } else {
                foreach ($languages as $lang) {
                    if (trim(Tools::getValue('WK_HTL_CHAIN_NAME_'.$lang['id_lang']))) {
                        if (!Validate::isGenericName(Tools::getValue('WK_HTL_CHAIN_NAME_'.$lang['id_lang']))) {
                            $this->errors[] = $this->l('Invalid hotel chain name in ').$lang['name'];
                        }
                    }
                }
            }
            if (!trim(Tools::getValue('WK_HTL_TAG_LINE_'.$defaultLangId))) {
                $this->errors[] = $this->l('Hotel tag line is required at least in ').$objDefaultLanguage['name'];
            } else {
                foreach ($languages as $lang) {
                    if (trim(Tools::getValue('WK_HTL_TAG_LINE_'.$lang['id_lang']))) {
                        if (!Validate::isGenericName(Tools::getValue('WK_HTL_TAG_LINE_'.$lang['id_lang']))) {
                            $this->errors[] = $this->l('Invalid Hotel tag line in ').$lang['name'];
                        }
                    }
                }
            }
            if (!trim(Tools::getValue('WK_HTL_SHORT_DESC_'.$defaultLangId))) {
                $this->errors[] = $this->l('Hotel short description is required at least in ').
                $objDefaultLanguage['name'];
            } else {
                foreach ($languages as $lang) {
                    if (trim(Tools::getValue('WK_HTL_SHORT_DESC_'.$lang['id_lang']))) {
                        if (!Validate::isGenericName(Tools::getValue('WK_HTL_SHORT_DESC_'.$lang['id_lang']))) {
                            $this->errors[] = $this->l('Invalid hotel short description in ').$lang['name'];
                        }
                    }
                }
            }
            if ($_FILES['WK_HOTEL_HEADER_IMAGE']['name']) {
                if ($error = ImageManager::validateUpload($_FILES['WK_HOTEL_HEADER_IMAGE'], Tools::getMaxUploadSize())) {
                    $this->errors[] = $error;
                }

                if (!count($this->errors)) {
                    $file_name = 'hotel_header_image_'.time().'.jpg';
                    $img_path = _PS_IMG_DIR_.$file_name;

                    if (ImageManager::resize($_FILES['WK_HOTEL_HEADER_IMAGE']['tmp_name'], $img_path)) {
                        $olderHeaderImg = _PS_IMG_DIR_.Configuration::get('WK_HOTEL_HEADER_IMAGE');
                        Configuration::updateValue('WK_HOTEL_HEADER_IMAGE', $file_name);
                        Tools::deleteFile($olderHeaderImg);
                    } else {
                        $this->errors[] = $this->l('Some error occured while uoploading image.Please try again.');
                    }
                }
            }
            if (!Validate::isUnsignedInt(Tools::getValue('WK_ADVANCED_PAYMENT_GLOBAL_MIN_AMOUNT'))) {
                $this->errors[] = $this->l('Invalid minimum partial payment percentage.');
            } elseif (Tools::getValue('WK_ADVANCED_PAYMENT_GLOBAL_MIN_AMOUNT') <= 0) {
                $this->errors[] = $this->l('Minimum partial payment percentage should be more than 0.');
            } elseif (Tools::getValue('WK_ADVANCED_PAYMENT_GLOBAL_MIN_AMOUNT') > 100) {
                $this->errors[] = $this->l('Minimum partial payment percentage should not be more than 100.');
            }
            if (Tools::getValue('WK_GOOGLE_ACTIVE_MAP')) {
                if (!Tools::getValue('PS_API_KEY')) {
                    $this->errors[] = $this->l('Please enter Google API key.');
                }
                if (!Tools::getValue('PS_MAP_ID')) {
                    $this->errors[] = $this->l('Please enter Google Map Id.');
                }
            }
            if (!trim(Tools::getValue('PS_SHOP_NAME'))) {
                $this->errors[] = $this->l('Website name field is required');
            }
            if (!trim(Tools::getValue('PS_SHOP_ADDR1'))) {
                $this->errors[] = $this->l('Address Line1 field is required');
            }
            if (!trim(Tools::getValue('PS_SHOP_PHONE'))) {
                $this->errors[] = $this->l('Phone field is required');
            } elseif (!Validate::isPhoneNumber(Tools::getValue('PS_SHOP_PHONE'))) {
                $this->errors[] = $this->l('Phone field is invalid');
            }
            if (trim(Tools::getValue('WK_CUSTOMER_SUPPORT_PHONE_NUMBER')) != '') {
                if (!Validate::isPhoneNumber(trim(Tools::getValue('WK_CUSTOMER_SUPPORT_PHONE_NUMBER')))) {
                    $this->errors[] = $this->l('Support Phone Number is invalid.');
                }
            }
            if (trim(Tools::getValue('WK_CUSTOMER_SUPPORT_EMAIL')) != '') {
                if (!Validate::isEmail(trim(Tools::getValue('WK_CUSTOMER_SUPPORT_EMAIL')))) {
                    $this->errors[] = $this->l('Support Email is invalid.');
                }
            }

            if (!count($this->errors)) {
                $objHotelInfo = new HotelBranchInformation();
                if ($hotelsInfo = $objHotelInfo->hotelBranchesInfo(false, 1)) {
                    if (count($hotelsInfo) > 1) {
                        $_POST['WK_HOTEL_NAME_ENABLE'] = 1;
                    }
                }
                foreach ($languages as $lang) {
                    // if lang fileds are at least in default language and not available in other languages then
                    // set empty fields value to default language value
                    if (!trim(Tools::getValue('WK_HTL_CHAIN_NAME_'.$lang['id_lang']))) {
                        $_POST['WK_HTL_CHAIN_NAME_'.$lang['id_lang']] = trim(
                            Tools::getValue('WK_HTL_CHAIN_NAME_'.$defaultLangId)
                        );
                    }
                    if (!trim(Tools::getValue('WK_HTL_TAG_LINE_'.$lang['id_lang']))) {
                        $_POST['WK_HTL_TAG_LINE_'.$lang['id_lang']] = trim(
                            Tools::getValue('WK_HTL_TAG_LINE_'.$defaultLangId)
                        );
                    }
                    if (!trim(Tools::getValue('WK_HTL_SHORT_DESC_'.$lang['id_lang']))) {
                        $_POST['WK_HTL_SHORT_DESC_'.$lang['id_lang']] = trim(
                            Tools::getValue('WK_HTL_SHORT_DESC_'.$defaultLangId)
                        );
                    }
                }
                parent::postProcess();
                if (empty($this->errors)) {
                    Tools::redirectAdmin(self::$currentIndex.'&conf=6&token='.$this->token);
                }
            }
        } else {
            parent::postProcess();
        }
    }

    public function setMedia()
    {
        parent::setMedia();
        Media::addJsDef(
            array(
                'filesizeError' => $this->l('File exceeds maximum size.', null, true),
                'maxSizeAllowed' => Tools::getMaxUploadSize(),
            )
        );
        $this->addJs(_MODULE_DIR_.'hotelreservationsystem/views/js/HotelReservationAdmin.js');
    }
}
