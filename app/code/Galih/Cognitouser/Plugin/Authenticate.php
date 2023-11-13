<?php
namespace Galih\Cognitouser\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Customer\Model\CustomerFactory;
use Monolog\Logger;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class Authenticate
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
    

    public function aroundAuthenticate(\Magento\Customer\Api\AccountManagementInterface $subject, $proceed, $username, $password)
    {
        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        $customerId = $customer->getId();
        $customer = $this->customerRepository->getById($customerId);

        $authResult = $this->cognito->login($username, $password);
        if($authResult != false){
            $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
            return $customer;

        }else {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        
    }
}
