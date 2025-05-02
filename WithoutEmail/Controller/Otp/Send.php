<?php
namespace MagoArab\WithoutEmail\Controller\Otp;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Send implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $phoneNumber = $this->request->getParam('phone_number');
            if (empty($phoneNumber)) {
                throw new \Exception('Phone number is required.');
            }
            
            // للاختبار، سنرجع نجاحًا دائمًا
            return $resultJson->setData([
                'success' => true,
                'message' => 'OTP sent successfully (test mode).',
                'phone' => $phoneNumber
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}