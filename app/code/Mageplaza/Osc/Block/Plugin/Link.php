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

namespace Mageplaza\Osc\Block\Plugin;

use Magento\Framework\App\RequestInterface;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class Link
 * @package Mageplaza\Osc\Block\Plugin
 */
class Link
{
    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var OscHelper
     */
    protected $oscHelper;

    /**
     * Link constructor.
     * @param RequestInterface $httpRequest
     * @param OscHelper $oscHelper
     */
    public function __construct(
        RequestInterface $httpRequest,
        OscHelper $oscHelper
    )
    {
        $this->_request  = $httpRequest;
        $this->oscHelper = $oscHelper;
    }

    /**
     * @param \Magento\Framework\Url $subject
     * @param $routePath
     * @param $routeParams
     * @return array|null
     */
    public function beforeGetUrl(\Magento\Framework\Url $subject, $routePath = null, $routeParams = null)
    {
        if ($this->oscHelper->isEnabled() && $routePath == 'checkout' && $this->_request->getFullActionName() != 'checkout_index_index') {
            return ['onestepcheckout', $routeParams];
        }

        return null;
    }
}
