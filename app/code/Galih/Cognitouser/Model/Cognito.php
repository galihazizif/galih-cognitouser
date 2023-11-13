<?php
namespace Galih\Cognitouser\Model;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class Cognito
{

    protected $cognito;
    protected $auth_result;
    protected $scopeConfig;
    protected $logger;

    private function _initialize()
    {
        $this->cognito = new CognitoIdentityProviderClient([
            'region' => 'ap-southeast-2',
            'version' => 'latest',
            'credentials' => array(
                'key' => $this->scopeConfig->getValue('galihcognitouser/general/aws_key'),
                'secret'  => $this->scopeConfig->getValue('galihcognitouser/general/aws_secret_key'),
            )
        ]);
    }

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->_initialize();
    }

    protected function _authenticate($username, $password)
    {
        if(isset($this->cognito)){
            $auth_result = $this->cognito->adminInitiateAuth([
                'AuthFlow' => 'ADMIN_USER_PASSWORD_AUTH',
                'ClientId' => $this->scopeConfig->getValue('galihcognitouser/general/app_client_id'),
                'UserPoolId' => $this->scopeConfig->getValue('galihcognitouser/general/user_pool_id'),
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ]
            ]);

            $this->logger->info('AWSCOG Res->adminInitiateAuth: '.$auth_result->__toString());
            if ($auth_result->get('Session') || $auth_result->get('AuthenticationResult')) {
                if($auth_result->get('AuthenticationResult')){
                    return $auth_result->get('AuthenticationResult');
                } else {
                    return $auth_result->get('Session');
                } 
                
            }
            return false;
        }

        return false;
    }

    protected function _signup($firstname, $lastname, $email, $password)
    {
        if(isset($this->cognito)){
            $username = str_ireplace('@','_', $email);
            $signupResult = $this->cognito->adminCreateUser([
                'ForceAliasCreation' => true,
                'MessageAction' => 'SUPPRESS',
                'TemporaryPassword' => $password,
                'ForceAliasCreation' => true,
                'DesiredDeliveryMediums' => ['EMAIL'],
                'UserAttributes' => [
                    [
                        'Name' => 'email',
                        'Value' => $email,
                    ],
                    [
                        'Name' => 'name',
                        'Value' => $firstname . ' ' . $lastname
                    ],
                    [
                        'Name' => 'email_verified',
                        'Value' => 'true'
                    ]
                    // ...
                ],
                'UserPoolId' => $this->scopeConfig->getValue('galihcognitouser/general/user_pool_id'),
                'Username' => str_ireplace('@','_', $email), // REQUIRED
            ]);
            $this->logger->info('AWSCOG Res->adminCreateUser: '.$signupResult->__toString());

            if ($signupResult->get('UserSub')) {
                $confirmSignupResult = $this->cognito->adminConfirmSignUp([
                    'UserPoolId' => $this->scopeConfig->getValue('galihcognitouser/general/user_pool_id'),
                    'Username' => $username
                ]);
                $this->logger->info('AWSCOG Res->adminConfirmSignUp: '.$confirmSignupResult->__toString());


                $session = $this->_authenticate($username, $password);
                return $signupResult->get('UserSub');
                
            }
            return false;
        }

        return false;
    }

    public function login($username, $password)
    {
        return $this->_authenticate($username, $password);   
    }

    public function signup($firstname, $lastname, $email, $password)
    {
        return $this->_signup($firstname, $lastname, $email, $password);
    }

    public function changePassword($username, $newPassword)
    {   
        $changePasswordResult = $this->cognito->adminSetUserPassword(
            [
                'UserPoolId' => $this->scopeConfig->getValue('galihcognitouser/general/user_pool_id'),
                'Username' => $username,
                'Permanent' => true,
                'Password' => $newPassword
            ]
        );

        $this->logger->info('AWSCOG Res->adminSetUserPassword: '.$changePasswordResult->__toString());
    }
}