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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'classes/RequiredFiles.php';

class QloHotelReview extends Module
{
    public $secure_key;
    public function __construct()
    {
        $this->name = 'qlohotelreview';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');
        $this->author = 'Webkul';
        $this->bootstrap = true;

        parent::__construct();

        $this->secure_key = Tools::encrypt($this->name);
        $this->displayName = $this->l('QloApps Hotel Reviews');
        $this->description = $this->l('This module allows guests to review hotels on specific categories.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerModuleHooks()
            || !$this->installModuleTabs()
            || !QhrHotelReviewDb::createTables()
            || !$this->saveModuleDefaultConfig()
        ) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminHotelReviewHotelReview'));
    }

    public function registerModuleHooks()
    {
        return $this->registerHook(
            array(
                'actionFrontControllerSetMedia',
                'displayProductTab',
                'displayProductTabContent',
                'displayFooterBefore',
                'displayRoomTypeDetailRoomTypeNameAfter',
                'actionRoomBookingStatusUpdateAfter',
                'displayBookingAction',
                'displayBackOfficeHeader',
                'displayAdminAfterHeader',
                'actionCleanData',
            )
        );
    }

    public function saveModuleDefaultConfig()
    {
        $config = QhrHotelReviewDb::getModuleDefaultConfig();

        foreach ($config as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        if (!QhrHotelReviewDb::saveDefaultCategories()) {
            return false;
        }

        return true;
    }

    public function hookActionFrontControllerSetMedia()
    {
        // review popup resources
        $this->reviewPopupResources();

        // review list resources
        $this->reviewListResources();

        // room type detail page resources
        $this->roomTypeDetailResources();
    }

    public function reviewPopupResources()
    {
        $controllers = array('orderdetail', 'guesttracking');
        $controller = Tools::getValue('controller');
        if (!in_array($controller, $controllers)) {
            return;
        }

        $idOrder = 0;
        if ($controller == 'orderdetail') {
            $idOrder = (int) Tools::getValue('id_order');
        }

        $this->loadMedia($idOrder);
    }

    public function reviewListResources()
    {
        if (Tools::getValue('controller') == 'product') {
            $idProduct = Tools::getValue('id_product');
            $qlo_hotel_review_js_vars = array(
                'review_ajax_link' => $this->context->link->getModuleLink($this->name),
                'review_ajax_token' => $this->secure_key,
                'raty_img_path' => $this->getPathUri().'views/img/raty',
            );
            $objHotelRoomType = new HotelRoomType();
            if ($roomTypeInfo = $objHotelRoomType->getRoomTypeInfoByIdProduct($idProduct)) {
                $idHotel = $roomTypeInfo['id_hotel'];
                $reviewImages = QhrHotelReview::getAllImages($idHotel);
                $qlo_hotel_review_js_vars['review_images'] = $reviewImages;

            }
            Media::addJsDef(array('qlo_hotel_review_js_vars' => $qlo_hotel_review_js_vars));


            $this->context->controller->addCSS(_PS_JS_DIR_.'raty/jquery.raty.css');
            $this->context->controller->addJS(_PS_JS_DIR_.'raty/jquery.raty.js');
            $this->context->controller->addJS(_PS_JS_DIR_.'jquery-circle-progress/circle-progress.min-1.2.2.js');
            $this->context->controller->addCSS($this->getPathUri().'views/css/front/review-list.css');
            $this->context->controller->addJS($this->getPathUri().'views/js/front/review-list.js');
        }
    }

    public function roomTypeDetailResources()
    {
        if (Tools::getValue('controller') == 'product') {
            Media::addJsDef(array('qlo_hotel_review_rtd_js_vars' => array(
                'raty_img_path' => $this->getPathUri().'views/img/raty',
            )));

            $this->context->controller->addCSS(_PS_JS_DIR_.'raty/jquery.raty.css');
            $this->context->controller->addJS(_PS_JS_DIR_.'raty/jquery.raty.js');
            $this->context->controller->addCSS($this->getPathUri().'views/css/front/room-type-detail.css');
            $this->context->controller->addJS($this->getPathUri().'views/js/front/room-type-detail.js');
        }
    }

    public function loadMedia($idOrder)
    {
        Media::addJsDef(array('qlo_hotel_review_js_vars' => array(
            'review_ajax_link' => $this->context->link->getModuleLink($this->name),
            'review_ajax_token' => $this->secure_key,
            'raty_img_path' => $this->getPathUri().'views/img/raty',
            'num_images_max' => (int) Configuration::get('QHR_MAX_IMAGES_PER_REVIEW'),
            'admin_approval_enabled' => (int) Configuration::get('QHR_ADMIN_APPROVAL_ENABLED'),
            'texts' => array(
                'num_files' => sprintf(
                    $this->l('You can upload a maximum of %d images.', false, true),
                    (int) Configuration::get('QHR_MAX_IMAGES_PER_REVIEW')
                ),
            ),
        )));

        $this->context->controller->addCSS(_PS_JS_DIR_.'raty/jquery.raty.css');
        $this->context->controller->addJS(_PS_JS_DIR_.'raty/jquery.raty.js');
        $this->context->controller->addCSS($this->getPathUri().'views/css/front/review.css');
        $this->context->controller->addJS($this->getPathUri().'views/js/hook/review.js');
    }

    public function hookDisplayBookingAction($params)
    {
        $idOrder = $params['id_order'];
        if (QhrHotelReviewHelper::getIsReviewable($idOrder)
            && !QhrHotelReview::getByIdOrder($idOrder)
        ) {
            if ($hotel = QhrHotelReviewHelper::getHotelByOrder($idOrder)) {
                $this->smarty->assign(array(
                    'id_order' => (int) $idOrder,
                    'id_hotel' => $hotel['id_hotel'],
                    'hotel_name' => $hotel['hotel_name'],
                ));
                return $this->display(__FILE__, 'booking-action.tpl');
            }
        }
    }

    public function hookDisplayFooterBefore()
    {
        $controllers = array('orderdetail', 'guesttracking');
        $controller = Tools::getValue('controller');
        if (in_array($controller, $controllers)) {
            $categories = QhrCategory::getAll();
            $this->smarty->assign(array(
                'categories' => $categories,
                'action' => $this->context->link->getModuleLink($this->name),
            ));
            return $this->display(__FILE__, 'add-review-popup.tpl');
        }
    }

    public function hookDisplayRoomTypeDetailRoomTypeNameAfter($params)
    {
        if ($params['product']->booking_product) {
            $idProduct = $params['id_product'];
            $objHotelRoomType = new HotelRoomType();
            $roomTypeInfo = $objHotelRoomType->getRoomTypeInfoByIdProduct($idProduct);
            $idHotel = $roomTypeInfo['id_hotel'];
            $this->smarty->assign(array(
                'num_reviews' => QhrHotelReview::getReviewCountByIdHotel($idHotel),
                'avg_rating' => QhrHotelReview::getAverageRatingByIdHotel($idHotel),
                'ratting_img_path' => _MODULE_DIR_.'hotelreservationsystem/views/img/Slices/icons-sprite.png',
            ));

            return $this->display(__FILE__, 'room-type-name-after.tpl');
        }
    }

    public function hookActionRoomBookingStatusUpdateAfter($params)
    {
        $idOrder = $params['id_order'];
        if (QhrHotelReviewHelper::getIsOrderCheckedOut($idOrder)) {
            QhrHotelReviewHelper::sendReviewRequestMail($idOrder);
        }
    }

    public function hookDisplayProductTab($params)
    {
        if ($params['product']->booking_product) {
            return $this->display(__FILE__, 'product-tab.tpl');
        }
    }

    public function hookDisplayProductTabContent($params)
    {
        if ($params['product']->booking_product) {
            $idProduct = Tools::getValue('id_product');
            $objHotelRoomType = new HotelRoomType();
            $reviewsAtOnce = (int) Configuration::get('QHR_REVIEWS_AT_ONCE');
            if ($roomTypeInfo = $objHotelRoomType->getRoomTypeInfoByIdProduct($idProduct)) {
                $idHotel = $roomTypeInfo['id_hotel'];
                $reviews = QhrHotelReview::getByHotel(
                    $idHotel,
                    1,
                    $reviewsAtOnce,
                    QhrHotelReview::QHR_SORT_BY_TIME_NEW,
                    $this->context->cookie->id_customer
                );
                if (is_array($reviews) && count($reviews)) {
                    foreach ($reviews as &$review) {
                        $review['images'] = QhrHotelReview::getImagesById($review['id_hotel_review']);
                    }
                }
                $summary = QhrHotelReview::getSummaryByHotel($idHotel);
                if (is_array($summary['categories']) && count($summary['categories'])) {
                    $summary = QhrHotelReviewHelper::prepareCategoriesData($summary);
                }

                $hasNextPage = QhrHotelReview::hasNextPage($idHotel, 1, $reviewsAtOnce);

                $this->smarty->assign(array(
                    'id_hotel' => $idHotel,
                    'reviews' => $reviews,
                    'summary' => $summary,
                    'images' => QhrHotelReview::getAllImages($idHotel),
                    'logged' => $this->context->customer->isLogged(true),
                    'show_load_more_btn' => $hasNextPage,
                ));
                return $this->display(__FILE__, 'product-tab-content.tpl');
            }
        }
    }

    public function hookActionCleanData($params)
    {
        if ($params['method'] == 'catalog' || $params['method'] ==  'sales') {
            QhrHotelReviewDb::truncateUserData();
        }
    }

    public function installModuleTabs()
    {
        $tabs = array(
            array('AdminParentHotelReview', 'Hotel Reviews', false, true),
            array('AdminHotelReviewCategory', 'Configuration', 'AdminParentHotelReview', true),
            array('AdminHotelReviewHotelReview', 'Reviews', 'AdminParentHotelReview', false),
        );

        foreach ($tabs as $tab) {
            $this->installTab($tab[0], $tab[1], $tab[2], $tab[3]);
        }
        return true;
    }

    public function installTab($className, $tabName, $tabParentName = false, $hidden = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } elseif ($hidden) {
            $tab->id_parent = -1;
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;
        return $tab->add();
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->getPathUri().'views/css/hook/global.css');
    }

    public function hookDisplayAdminAfterHeader()
    {
        if ($currentController = Tools::getValue('controller')) {
            $controllers = array(
                'AdminHotelReviewHotelReview',
                'AdminHotelReviewCategory',
            );
            if (in_array($currentController, $controllers)) {
                return $this->display(__FILE__, 'admin-after-header.tpl');
            }
        }
    }

    public function deleteModuleConfigKeys()
    {
        $config = QhrHotelReviewDb::getModuleDefaultConfig();
        foreach ($config as $key => $value) {
            Configuration::deleteByName($key);
        }
        return true;
    }

    public function uninstallModuleTabs()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }
        return true;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install()) {
            return false;
        }
        return true;
    }

    public function uninstall($keep = true)
    {
        if (!parent::uninstall()
            || ($keep && !QhrHotelReviewDb::deleteTables())
            || ($keep && !QhrHotelReview::cleanImagesDirectory())
            || !$this->uninstallModuleTabs()
            || !$this->deleteModuleConfigKeys()
        ) {
            return false;
        }
        return true;
    }
}
