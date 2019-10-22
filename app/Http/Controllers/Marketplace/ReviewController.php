<?php

namespace App\Http\Controllers\MarketPlace;

use Auth, Validator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{Service, ServiceReview};
use App\Transformers\Marketplace\ServiceReviewTransformer;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $service = Service::find($id);
        if (! $service) {
        	return $this->errorNotFound('Service doesn\'t exist!');
        }

        $reviews = ServiceReview::whereServiceId($service->id)->paginate(15);
        return $this->response->paginator($reviews, new ServiceReviewTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
    	$user = Auth::user();

        $payload = request([
        	'rating',
        	'description'
        ]);

        $rules = [
        	'rating' => 'required|integer|between:0,5',
        	'description' => 'nullable|string'
        ];

        $validator = Validator::make($payload, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $service = Service::find($id);
       	if (! $service) {
       		return $this->errorNotFound('Service doesn\'t exist!');
       	}

       	(new ServiceReview)->fill([
       		'user_id' => $user->id,
       		'service_id' => $service->id,
       		'rating' => array_get($payload, 'rating', 0),
       		'description' => array_get($payload, 'description', NULL)
       	])->save();

        $service->calculateRating();

       	return response()->json([
            'message' => 'Added your review to the service!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $reviewId)
    {
    	$user = Auth::user();

        $review = ServiceReview::whereServiceId($id)->whereUserId($user->id)->find($reviewId);
        if (! $review) {
       		return $this->errorNotFound('Service review doesn\'t exist!');
       	}

        $service = Service::find($review->service_id);
        $review->delete();

        if ($service) $service->calculateRating();

       	return response()->json([
            'message' => 'Removed your review from the service!'
        ]);
    }
}
