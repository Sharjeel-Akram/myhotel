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

include_once(dirname(__FILE__) . '/Helpers/PayPalHelper.php');

class PayPalOrders extends PayPalHelper
{
    private function _createOrder($postData) {
        $this->_checkToken();
        $this->_http->resetHelper();
        $this->_setDefaultHeaders();
        // $this->_http->addHeader("PayPal-Request-Id: " . $this->getUUID());
        $this->_setAPIVersion('2');
        $this->_http->setUrl($this->_createApiUrl("checkout/orders"));
        $this->_http->setBody($postData);
        return $this->_respond($this->_http->sendRequest());
    }

    private function _getOrder($orderID) {
        $this->_checkToken();
        $this->_http->resetHelper();
        $this->_setDefaultHeaders();
        $this->_setAPIVersion('2');
        $this->_http->setUrl($this->_createApiUrl("checkout/orders/" . $orderID));
        return $this->_respond($this->_http->sendRequest());
    }

    private function _patchOrder($orderID, $patchData) {
        $this->_checkToken();
        $this->_http->resetHelper();
        $this->_setDefaultHeaders();
        $this->_setAPIVersion('2');
        $this->_http->setUrl($this->_createApiUrl("checkout/orders/" . $orderID));
        $this->_http->setBody($patchData);
        $this->_http->setRequestType('PATCH');
        return $this->_respond($this->_http->sendRequest());
    }

    private function _captureOrder($orderID) {
        $this->_checkToken();
        $this->_http->resetHelper();

        $this->_setDefaultHeaders();
        $this->_http->addHeader("PayPal-Request-Id: " . $this->getUUID());

        $this->_setAPIVersion('2');
        $this->_http->setUrl($this->_createApiUrl("checkout/orders/" . $orderID . "/capture"));
        $this->_http->setBody('');
        return $this->_respond($this->_http->sendRequest());
    }

    private function _disburseCapture($postData) {
        $this->_checkToken();
        $this->_http->resetHelper();
        $this->_setDefaultHeaders();
        $this->_setAPIVersion('1');
        $this->_http->setUrl($this->_createApiUrl("payments/referenced-payouts-items"));
        $this->_http->setBody($postData);
        return $this->_respond($this->_http->sendRequest());
    }

    private function _refundCapture($transactionID, $assertion, $postData) {
        $this->_checkToken();
        $this->_http->resetHelper();
        $this->_setDefaultHeaders();
        $this->_http->addHeader("PayPal-Request-Id: " . $this->getUUID());
        $this->_http->addHeader("PayPal-Auth-Assertion: " . $assertion);
        $this->_setAPIVersion('2');
        $this->_http->setUrl($this->_createApiUrl("payments/captures/" . $transactionID . "/refund"));
        $this->_http->setBody($postData);
        return $this->_respond($this->_http->sendRequest());
    }

    public function create($postData) {
        if($postData['flow'] === 'mark') {
            $postData['original']['purchase_units'][0]['amount']['value'] -= $postData['original']['purchase_units'][0]['amount']['breakdown']['shipping']['value'];
            $postData['original']['purchase_units'][0]['amount']['breakdown']['shipping']['value'] = $postData['update']['updated_shipping'];
            $postData['original']['purchase_units'][0]['amount']['value'] += $postData['original']['purchase_units'][0]['amount']['breakdown']['shipping']['value'];
            $postData['original']['purchase_units'][0]['shipping'] = $postData['update']['shipping_address'];
            return $this->_createOrder($postData['original']);
        } else {
            return $this->_createOrder($postData['original']);
        }
    }

    public function get($orderID) {
        return $this->_getOrder($orderID);
    }

    public function patch($data) {
        $data['order']['amount']['value'] -= $data['order']['amount']['breakdown']['shipping']['value'];
        $data['order']['amount']['breakdown']['shipping']['value'] = $data['updated_shipping'];
        $data['order']['amount']['value'] += $data['order']['amount']['breakdown']['shipping']['value'];
        $patchData = array(
            array(
                "op" => "replace",
                "path" => "/purchase_units/@reference_id=='" . $data['order']['reference_id'] . "'/amount",
                "value" => $data['order']['amount']
            )
        );
        return $this->_patchOrder($data['order']['order_id'], $patchData);
    }

    public function capture($orderID) {
        return $this->_captureOrder($orderID);
    }

    public function disburse($transactionID) {
        $postData = array(
            "reference_type" => "TRANSACTION_ID",
            "reference_id" => $transactionID
        );
        return $this->_disburseCapture($postData);
    }

    public function refund($postData) {
        $transactionID = $postData['transaction_id'];
        $assertion = $postData['auth_assertion'];
        $postDataArr = array();
        $postDataArr['note_to_payer'] = $postData['refund_reason'];
        if (array_key_exists('amount', $postData)) {
            $postDataArr['amount'] = $postData['amount'];
        }
        return $this->_refundCapture($transactionID, $assertion, $postDataArr);
    }

    private function _getRefund($refundID) {
        $this->_checkToken();
        $this->_http->resetHelper();
        $this->_setDefaultHeaders();
        $this->_setAPIVersion('2');
        $this->_http->setUrl($this->_createApiUrl("payments/refunds/" . $refundID));
        $this->_http->setRequestType('GET');
        return $this->_respond($this->_http->sendRequest());
    }

    public function getRefund($refundID) {
        return $this->_getRefund($refundID);
    }
}
