<?php
namespace Galih\Cognitouser\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Monolog\Logger;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class Signup
{

    protected $cognito;

    protected $eventManager;

    protected $customerRepository;

    protected $customerFactory;

    protected $logger;

    public function __construct(
        \Galih\Cognitouser\Model\Cognito $cognito,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger,
        EventManagerInterface $eventManager
    )
    {
        $this->cognito = $cognito;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }
    

    public function afterCreateAccount(\Magento\Customer\Api\AccountManagementInterface $subject, $result, $customer, $password, $redirectUrl)
    {
        $email = $customer->getEmail();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();
        $signupResult = $this->cognito->signup($firstName, $lastName, $email, $password);

        $this->logger->info('GALIH: Signup result' . $signupResult);
       
        return $result;
        
    }
}
