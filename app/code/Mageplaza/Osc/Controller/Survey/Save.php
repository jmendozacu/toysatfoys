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

namespace Mageplaza\Osc\Controller\Survey;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class Save
 * @package Mageplaza\Osc\Controller\Survey
 */
class Save extends Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var OscHelper
     */
    protected $oscHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param Session $checkoutSession
     * @param Order $order
     * @param OscHelper $oscHelper
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        Session $checkoutSession,
        Order $order,
        OscHelper $oscHelper
    )
    {
        $this->jsonHelper       = $jsonHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_order           = $order;
        $this->oscHelper        = $oscHelper;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|null
     */
    public function execute()
    {
        $response = [];
        if ($this->getRequest()->getParam('answerChecked') && isset($this->_checkoutSession->getOscData()['survey'])) {
            try {
                $order   = $this->_order->load($this->_checkoutSession->getOscData()['survey']['orderId']);
                $answers = '';
                foreach ($this->getRequest()->getParam('answerChecked') as $item) {
                    $answers .= $item . ' - ';
                }
                $order->setData('osc_survey_question', $this->oscHelper->getSurveyQuestion());
                $order->setData('osc_survey_answers', substr($answers, 0, -2));
                $order->save();

                $response['status']  = 'success';
                $response['message'] = 'Thank you for completing our survey!';
                $this->_checkoutSession->unsOscData();
            } catch (\Exception $e) {
                $response['status']  = 'error';
                $response['message'] = "Can't save survey answer. Please try again! ";
            }

            return $this->getResponse()->representJson(OscHelper::jsonEncode($response));
        }

        return null;
    }
}
