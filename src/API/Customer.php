<?php

namespace Boparaiamrit\ZohoSubscription\API;

/**
 * @author Hang Pham <thi@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link   https://www.zoho.com/subscriptions/api/v1/#customers
 */
class Customer extends Base
{

    /**
     * @param string $customerEmail
     *
     * @return array
     */
    public function getListCustomersByEmail(string $customerEmail): array
    {
        $cacheKey = sprintf('zoho_customer_%s', md5($customerEmail));
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->sendRequest('GET', sprintf('customers?email=%s', $customerEmail));

            $result = $response;

            $customers = $result['customers'];

            $this->saveToCache($cacheKey, $customers);

            return $customers;
        }

        return $hit;
    }

    /**
     * @param string $customerEmail
     *
     * @return array
     */
    public function getCustomerByEmail(string $customerEmail): array
    {
        $customers = $this->getListCustomersByEmail($customerEmail);

        if (count($customers) === 0) {
            throw new \LogicException(sprintf('customer with email %s not found', $customerEmail));
        }

        return $this->getCustomerById($customers[0]['customer_id']);
    }

    /**
     * @param string $customerId The customer's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getCustomerById(string $customerId): array
    {
        $cacheKey = sprintf('zoho_customer_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->sendRequest('GET', sprintf('customers/%s', $customerId));
            $result = $response;

            $customer = $result['customer'];

            $this->saveToCache($cacheKey, $customer);

            return $customer;
        }

        return $hit;
    }

    /**
     * @param string $customerId The customer's id
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return array|bool
     */
    public function updateCustomer(string $customerId, array $data)
    {
        $response = $this->sendRequest('PUT', sprintf('customers/%s', $customerId), ['content-type' => 'application/json'], json_encode($data));

        $result = $response;

        if ($result['code'] == '0') {
            $customer = $result['customer'];

            $this->deleteCustomerCache($customer);

            return $customer;
        } else {
            return false;
        }
    }

    /**
     * @param array $customer
     */
    private function deleteCustomerCache(array $customer)
    {
        $cacheKey = sprintf('zoho_customer_%s', $customer['customer_id']);
        $this->deleteCacheByKey($cacheKey);

        $cacheKey = sprintf('zoho_customer_%s', md5($customer['email']));
        $this->deleteCacheByKey($cacheKey);
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     *
     * @return array|bool
     */
    public function createCustomer(array $data)
    {
        $response = $this->sendRequest('POST', 'customers', ['content-type' => 'application/json'], json_encode($data));

        $result = $response;

        if ($result['code'] == '0') {
            $customer = $result['customer'];

            return $customer;
        } else {
            return false;
        }
    }

    /**
     * @param string $customerId The customer's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createCard(string $customerId, array $data)
    {
        $cacheKey = sprintf('zoho_cards_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->sendRequest('POST', sprintf('customers/%s/cards', $customerId), ['content-type' => 'application/json'], json_encode($data));

            $result = $response;

            $cards = $result['card'];

            $this->saveToCache($cacheKey, $cards);

            return $cards;
        }

        return $hit;
    }

    /**
     * @param string $customerId The customer's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listCardsByCustomer(string $customerId)
    {
        $cacheKey = sprintf('zoho_cards_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->sendRequest('GET', sprintf('customers/%s/cards', $customerId));

            $result = $response;

            $cards = $result['cards'];

            $this->saveToCache($cacheKey, $cards);

            return $cards;
        }

        return $hit;
    }

    /**
     * List all payment transactions
     *
     * @param string $customerId
     * @return array
     */
    public function listTransactions(string $customerId)
    {
        $cacheKey = sprintf('zoho_transaction_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->sendRequest('GET', sprintf('transactions?filter_by=TransactionType.All&customer_id=%s', $customerId));

            $result = $response;

            $transactions = $result['transactions'];

            $this->saveToCache($cacheKey, $transactions);

            return $transactions;
        }

        return $hit;
    }

}
