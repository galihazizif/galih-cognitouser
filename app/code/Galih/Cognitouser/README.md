# Magento2 Module Galih Cognitouser

    ``galih/module-cognitouser``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)

## Main Functionalities
Galih Cognito, integrate Magento2 and AWS Cognito user pool
- Customer Registration account will passed to AWS Cognito
- Customer Login will authenticate against data stored in AWS Cognito
- Change Password 
- Unit Test (Not Implemented)
- Profile Update (Not Implemented)

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Galih`
 - Enable the module by running `php bin/magento module:enable Galih_Cognitouser`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`
