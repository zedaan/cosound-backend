<?php

namespace App\Http\Controllers\Marketplace;

use Auth, Validator;

use App\Models\{Service, Cart};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transformers\Marketplace\CartTransformer;

class CartController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function addToCart(Request $request)
    {
        $user = $this->user;

        $data = request([
    		'service_id',
    	]);

    	$rules = [
    		'service_id' => 'required',
        ];
        
        $validator = Validator::make($data, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $service = Service::find($data['service_id']);
        if (! $service) {
            return $this->errorNotFound('Service doesn\'t exist!');
        }

        $user->cart()->syncWithoutDetaching($data['service_id']);

        $count = Cart::whereHas('service')->whereUserId($user->id)->count();

        return response()->json([
            'message' => 'added',
            'count' => $count
        ]);
    }

    public function removeFromCart(Request $request, $id)
    {
        $user = $this->user;

        $cart = Cart::whereUserId($user->id)->find($id);

        if ($cart)
            $cart->delete();
        
        $count = Cart::whereHas('service')->whereUserId($user->id)->count();

        return response()->json([
            'message' => 'removed',
            'count' => $count
        ]);
    }

    public function getCartItems(Request $request)
    {
        $perPage = 6;
        
        $user = $this->user;
        
        $carts = Cart::whereHas('service')->whereUserId($user->id)->orderBy('created_at', 'desc')->paginate($perPage);

        $vat = 0;
        $subTotal = (new Cart)->subTotal($this->user->id);
        $total = $subTotal + $vat;

        return $this->response->paginator($carts, new CartTransformer)->setMeta([
            'vat' => $vat,
            'total' => $total,
            'sub_total' => $subTotal
        ]);
    }

    public function count(Request $request)
    {
        $count = Cart::whereHas('service')->whereUserId($this->user->id)->count();

        return response()->json([
            'data' => $count
        ]);
    }
}
