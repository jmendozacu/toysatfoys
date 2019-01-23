<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Controller\Index;

use Magento\Checkout\Controller\Onepage;

/**
 * Class Index
 * @package Mageplaza\Osc\Controller\Index
 */
class Index extends Onepage
{
    /**
     * @type \Mageplaza\Osc\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_checkoutHelper = $this->_objectManager->get(\Mageplaza\Osc\Helper\Data::class);
        if (!$this->_checkoutHelper->isEnabled()) {
            $this->messageManager->addError(__('One step checkout is turned off.'));

            return $this->resultRedirectFactory->create()->setPath('checkout');
        }

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();

        $this->initDefaultMethods($quote);

        $resultPage    = $this->resultPageFactory->create();
        $checkoutTitle = $this->_checkoutHelper->getCheckoutTitle();
        $resultPage->getConfig()->getTitle()->set($checkoutTitle);
        $resultPage->getConfig()->setPageLayout($this->_checkoutHelper->isShowHeaderFooter() ? '1column' : 'checkout');

        return $resultPage;
    }

    /**
     * Default shipping/payment method
     *
     * @param $quote
     * @return bool
     */
    public function initDefaultMethods($quote)
    {
        $shippingAddress = $quote->getShippingAddress();

        $defaultCountryId = $this->getDefaultCountryFromLocale();
        if (!$shippingAddress->getCountryId()) {
            /** Get default country id from Geo Ip or Locale */
            $geoIpData = $this->_checkoutHelper->getAddressHelper()->getGeoIpData();
            if (!empty($geoIpData)) {
                $defaultCountryId = $geoIpData['country_id'];
            } else if (!empty($this->_checkoutHelper->getDefaultCountryId())) {
                $defaultCountryId = $this->_checkoutHelper->getDefaultCountryId();
            }
            $shippingAddress->setCountryId($defaultCountryId);
        }

        $shippingAddress->setCollectShippingRates(true);

        /** @var \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector */
        $totalsCollector = $this->_objectManager->get(\Magento\Quote\Model\Quote\TotalsCollector::class);
        $totalsCollector->collectAddressTotals($quote, $shippingAddress);

        $method = null;

        try {
            $availableMethods = $this->_objectManager->get(\Magento\Quote\Api\ShippingMethodManagementInterface::class)
                ->getList($quote->getId());
            if (sizeof($availableMethods) == 1) {
                $method = array_shift($availableMethods);
            } else if (!$shippingAddress->getShippingMethod() && sizeof($availableMethods)) {
                $defaultMethod = array_filter($availableMethods, [$this, 'filterMethod']);
                if (sizeof($defaultMethod)) {
                    $method = array_shift($defaultMethod);
                }
            }

            if ($method) {
                $methodCode = $method->getCarrierCode() . '_' . $method->getMethodCode();
                $this->getOnepage()->saveShippingMethod($methodCode);
            }

            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $method
     * @return bool
     */
    public function filterMethod($method)
    {
        $defaultShippingMethod = $this->_checkoutHelper->getDefaultShippingMethod();
        $methodCode            = $method->getCarrierCode() . '_' . $method->getMethodCode();
        if ($methodCode == $defaultShippingMethod) {
            return true;
        }

        return false;
    }

    /**
     * Get  default country id from locale
     * @return string
     */
    public function getDefaultCountryFromLocale()
    {
        $locale = $this->_objectManager->get(\Magento\Framework\Locale\Resolver::class)
            ->getLocale();

        return substr($locale, strrpos($locale, "_") + 1);
    }
}
