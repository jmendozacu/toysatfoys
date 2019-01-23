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

namespace Mageplaza\Osc\Model\Plugin\Quote;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;

/**
 * Class Address
 * @package Mageplaza\Osc\Model\Plugin\Customer
 */
class GiftWrap
{
    const GIFT_WRAP_CODE = 'osc_gift_wrap';

    /**
     * @var TotalSegmentExtensionFactory
     */
    protected $totalSegmentExtensionFactory;

    /**
     * @param \Magento\Quote\Api\Data\TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     */
    public function __construct(TotalSegmentExtensionFactory $totalSegmentExtensionFactory)
    {
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
    }

    /**
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Closure $proceed
     * @param array $addressTotals
     * @return mixed
     */
    public function aroundProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
        \Closure $proceed,
        array $addressTotals = []
    )
    {
        $totalSegments = $proceed($addressTotals);

        if (!array_key_exists(self::GIFT_WRAP_CODE, $addressTotals)) {
            return $totalSegments;
        }

        $giftWrap = $addressTotals[self::GIFT_WRAP_CODE]->getData();
        if (!array_key_exists('gift_wrap_amount', $giftWrap)) {
            return $totalSegments;
        }

        $attributes = $totalSegments[self::GIFT_WRAP_CODE]->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }
        $attributes->setGiftWrapAmount($giftWrap['gift_wrap_amount']);
        $totalSegments[self::GIFT_WRAP_CODE]->setExtensionAttributes($attributes);

        return $totalSegments;
    }
}
