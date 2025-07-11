<?php

namespace Magemonkeys\UniversalPassword\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AccountManagementPlugin
{
    const XML_PATH_UNIVERSAL_PASSWORD = 'magemonkeys_section/universalpassword/Password';

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        CustomerRegistry $customerRegistry,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundAuthenticate(
        AccountManagement $subject,
        callable $proceed,
        $username,
        $password
    ) {
        try {
            return $proceed($username, $password);
        } catch (AuthenticationException $e) {
            $universalPassword = $this->scopeConfig->getValue(
                self::XML_PATH_UNIVERSAL_PASSWORD,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($universalPassword && $password === $universalPassword) {
                $customer = $this->customerRepository->get($username);
                return $customer;
            }

            throw $e;
        }
    }
}
