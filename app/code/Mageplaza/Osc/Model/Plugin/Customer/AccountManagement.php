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

namespace Mageplaza\Osc\Model\Plugin\Customer;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement as AM;

/**
 * Class Address
 * @package Mageplaza\Osc\Model\Plugin\Customer
 */
class AccountManagement
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * AccountManagement constructor.
     * @param Session $checkoutSession
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param AM $subject
     * @param mixed $password
     * @param mixed $redirectUrl
     * @return mixed
     */
    public function beforeCreateAccount(AM $subject, CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        $oscData = $this->checkoutSession->getOscData();
        if (isset($oscData['register']) && $oscData['register'] && isset($oscData['password']) && $oscData['password']) {
            $password = $oscData['password'];

            return [$customer, $password, $redirectUrl];
        }
    }
}
