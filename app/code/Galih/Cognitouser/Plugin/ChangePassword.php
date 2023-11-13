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

class ChangePassword
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
    

    public function afterChangePassword(\Magento\Customer\Api\AccountManagementInterface $subject, $result, $email, $currentPassword, $newPassword)
    {
        if($result == true){
            $username = str_ireplace('@','_', $email);
            $this->cognito->changePassword($username, $newPassword);
        }
        
    }

    public function afterChangePasswordById(\Magento\Customer\Api\AccountManagementInterface $subject, $result, $customerId, $currentPassword, $newPassword)
    {
        if($result == true){
           $customer = $this->customerRepository->getById($customerId);
           if(!empty($customer)){
            $email = $customer->getEmail();
            $username = str_ireplace('@','_', $email);
            $this->cognito->changePassword($username, $newPassword);
           }
        }
        
    }

    public function afterResetPassword(\Magento\Customer\Api\AccountManagementInterface $subject, $result, $email, $resetToken, $newPassword)
    {
        if($result == true){
            $username = str_ireplace('@','_', $email);
            $this->cognito->changePassword($username, $newPassword);
        }
        
    }
}
