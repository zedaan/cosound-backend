<?php

namespace App\Http\Controllers\Marketplace;

use Auth, DB, Exception, Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\{Cart, Order, OrderItem, Service};

use App\Services\StripeService;

use App\Transformers\OrderTransformer;

class OrderController extends Controller
{
    protected $user;
    protected $stripeService;
    protected $transformer = 'App\Transformers\OrderTransformer';

    public function __construct(StripeService $service)
    {
        $this->user = Auth::user();
        $this->stripeService = $service;
    }

    public function placeOrder(Request $request)
    {
        $user = $this->user;
        $stripeService = $this->stripeService;

        $serviceIds = Cart::whereHas('service')->whereUserId($user->id)->pluck('service_id');
        $serviceIds = $serviceIds->toArray();
        
        if (count($serviceIds) === 0) {
            return response()->json([
                'message' => "There are no items in your cart!",
            ], 400);
        }
        
        $services = Service::whereIn('id', $serviceIds)->get();

        if (! $user->card_last_four) {
            return response()->json([
                'message' => "Please add a card to continue with payment!",
            ], 400);
        }

        $tax = 0;
        $subTotal = (new Cart)->subTotal($user->id);
        $total = $subTotal + $tax;

        $chargeData = [];
        $chargeData['amount'] = $total * 100;             // Conversion to smallest unit of currency (USD)
        $chargeData['currency'] = 'usd';
        $chargeData['customer'] = $user->stripe_id;
        $chargeData['description'] = "CoSound";

        DB::beginTransaction();

        try {
            $chargeResponse = $stripeService->createCharge($chargeData);
            if (! $chargeResponse['success']) {
                return response()->json([
                    'message' => $chargeResponse['message'],
                ], 400);
            }

            $charge = $chargeResponse['charge'];

            $description = (object) [];
            $description->charge_id = $charge->id;

            $orderData = [];
            $orderData = [
                'user_id' => $user->id,
                'total' => $subTotal,
                'tax' => $tax,
                'description' => $description
            ];

            $order = new Order;

            $order->fill($orderData)->save();

            foreach ($services as $service) {
                $orderItem = new OrderItem;
                
                $orderItem['user_id'] = $user->id;
                $orderItem['service_id'] = $service->id;
                $orderItem['price'] = $service->price;
                $orderItem['tax'] = 0;

                $order->items()->save($orderItem);
            }

            Cart::whereHas('service')->whereUserId($user->id)->delete();

            DB::commit();
            
            return response()->json([
                'order_id' => $order->id,
                'tax' => $tax,
                'sub_total' => $subTotal
            ]);
        } catch (Exception $e) {
            \Log::info('exception');
            \Log::info($e);
            DB::rollback();
            return $this->errorInternal("Order couldn't be placed due to some internal error. Please try again!");
        }
    }
}
