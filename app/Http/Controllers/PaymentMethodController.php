<?php

namespace App\Http\Controllers;

use Auth, DB, Exception, Validator;

use Illuminate\Http\Request;

use App\Services\StripeService;

class PaymentMethodController extends Controller
{
    protected $user;
    protected $stripeService;

    protected $transformer = 'App\Transformers\CardTransformer';

    public function __construct(StripeService $service)
    {
        $this->user = Auth::user();
        $this->stripeService = $service;
    }

    public function saveCard(Request $request)
    {
        $user = $this->user;
        $stripeService = $this->stripeService;

        $data = request([
    		'number',
            'exp_month',
            'exp_year',
            'cvc',
        ]);

        $data['exp_month'] = (int) $data['exp_month'];
        $data['exp_year'] = (int) $data['exp_year'];

    	$rules = [
            'number' => 'required|min:12|max:16',
            'exp_month' => 'required|integer|min:1|max:12',
            'exp_year' => 'required|integer|min:' . date('y'),
            'cvc' => 'required|digits:3',
        ];
       
        $validator = Validator::make($data, $rules);

        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        DB::beginTransaction();

        try {
            $cardResponse = $stripeService->createToken($data);
            if (! $cardResponse['success']) {
                return response()->json([
                    'message' => $cardResponse['message'],
                ], 400);
            }

            $cardToken = $cardResponse['token']->id;

            // If user isn't a Stripe customer
            if (! $user->stripe_id) {
                $customerResponse = $stripeService->createCustomer($cardToken);

                if (! $customerResponse['success']) {
                    return response()->json([
                        'message' => $customerResponse['message'],
                    ], 400);
                }

                $customer = $customerResponse['customer'];
                $card = $customer['sources']['data'][0];

                $user->stripe_id = $customer->id;
            }

            // If user is already a Stripe customer
            else {
                $updateResponse = $stripeService->updateCard($user->stripe_id, $cardToken);

                if (! $updateResponse['success']) {
                    return response()->json([
                        'message' => $updateResponse['message'],
                    ], 400);
                }

                $card = $updateResponse['customer']['sources']['data'][0];
            }

            $user->card_brand = $card->brand;
            $user->card_type = $card->funding;
            $user->card_last_four = $card->last4;

            $user->save();

            DB::commit();

            return $this->response->item($user, $this->transformer);

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorInternal("Card couldn't be saved due to some internal error. Please try again!");
        }
    }

    public function getCard(Request $request)
    {
        $user = $this->user;

        return $this->response->item($user, $this->transformer);
    }

    public function removeCard(Request $request)
    {
        $user = $this->user;
        $stripeService = $this->stripeService;

        if (! $user->stripe_id || ! $user->card_last_four) {
            return response()->json([
                'message' => "No card linked with your account!",
            ], 400);
        }

        DB::beginTransaction();

        try {
            $customerResponse = $stripeService->fetchCustomer($user->stripe_id);

            if (! $customerResponse['success']) {
                return response()->json([
                    'message' => $customerResponse['message'],
                ], 400);
            }

            $cards = $customerResponse['customer']['sources']['data'];

            if (count($cards) !== 0) {
                $deleteResponse = $stripeService->deleteCard($user->stripe_id, $cards[0]->id);
                if (! $deleteResponse['success']) {
                    return response()->json([
                        'message' => $deleteResponse['message'],
                    ], 400);
                }
            }

            $user->card_brand = null;
            $user->card_last_four = null;
            $user->save();

            DB::commit();

            return $this->response->item($user, $this->transformer);

        } catch (Exception $e) {
            DB::rollback();
            return $this->errorInternal("Card couldn't be removed due to some internal error. Please try again!");
        }
    }
}
