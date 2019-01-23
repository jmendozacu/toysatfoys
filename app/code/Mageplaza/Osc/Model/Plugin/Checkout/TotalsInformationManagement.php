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

namespace Mageplaza\Osc\Model\Plugin\Checkout;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

/**
 * Class TotalsInformationManagement
 * @package Mageplaza\Osc\Model\Plugin\Checkout
 */
class TotalsInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CartTotalRepositoryInterface $cartTotalRepository
    )
    {
        $this->quoteRepository     = $quoteRepository;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        TotalsInformationInterface $addressInformation
    )
    {
        $result = $proceed($cartId, $addressInformation);

        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $extensionAttributes = $quote->getExtensionAttributes();
        if (!$quote->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface[] $shippingAssignments */
            $shippingAssignments = $extensionAttributes->getShippingAssignments();

            if (count($shippingAssignments)) {
                $shippingAssignments[0]->getShipping()->setMethod($quote->getShippingAddress()->getShippingMethod());
            }

            $this->quoteRepository->save($quote);
        }

        return $result;
    }
}