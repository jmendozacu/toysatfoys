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

namespace Mageplaza\Osc\Controller\Add;

/**
 * Class Index
 * @package Mageplaza\Osc\Controller\Add
 */
class Index extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : 11;
        $storeId   = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $product   = $this->productRepository->getById($productId, false, $storeId);

        $this->cart->addProduct($product, []);
        $this->cart->save();

        return $this->goBack($this->_url->getUrl('onestepcheckout'));
    }
}
