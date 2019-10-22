<?php

namespace App\Services;

use Exception;

use Stripe\{Charge, Customer, Token};
use Stripe\Error\Card as CardError;

class StripeService
{
    public function createToken($card = array())
	{
		try {
			$token = Token::create([
				'card' => [
					'number' => array_get($card, 'number'),
					'exp_month' => array_get($card, 'exp_month'),
					'cvc' => array_get($card, 'cvc'),
					'exp_year' => array_get($card, 'exp_year')
				]
            ]);
            
			return [
				'success' => true,
				'token' => $token
			];

		} catch (CardError $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
    }
    
    public function createCustomer($token)
    {
        try {
			$customer = Customer::create([
                'source' => $token,
            ]);
            
			return [
				'success' => true,
				'customer' => $customer
			];

		} catch (Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
    }

    public function fetchCustomer($customerId)
    {
        try {
            $customer = Customer::retrieve($customerId);
            
			return [
				'success' => true,
				'customer' => $customer
			];

		} catch (Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
    }

    public function updateCard($customerId, $cardToken)
    {
        try {
            $customer = Customer::update($customerId, [
                'source' => $cardToken,
            ]);

            return [
				'success' => true,
				'customer' => $customer
			];
        } catch (Exception $e) {
            return [
				'success' => false,
				'message' => $e->getMessage()
			];
        }
    }

    public function deleteCard($customerId, $cardId)
    {
        try {
            $customer = Customer::retrieve($customerId);
            $customer->sources->retrieve($cardId)->delete();

            return [
				'success' => true,
			];
        } catch (Exception $e) {
            return [
				'success' => false,
				'message' => $e->getMessage()
			];
        }
    }

    public function createCharge($data)
    {
		try {
			$charge = Charge::create($data);
			
			return [
				'success' => true,
				'charge' => $charge
			];
        } catch(CardError $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
    	}
    }
}