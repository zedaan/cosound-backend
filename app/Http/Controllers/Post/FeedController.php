<?php

namespace App\Http\Controllers\Post;

use Auth, Exception;

use Illuminate\Http\Request;
use App\Models\{User, Post};
use App\Contracts\FeedContract;
use App\Http\Controllers\BaseApiController;
use App\Services\GetStreamService;

class FeedController extends BaseApiController
{
    protected $user, $feedContract, $getStreamService;

    protected $transformer = 'App\Transformers\Post\FeedTransformer';

    public function __construct(FeedContract $feedContract, GetStreamService $getStreamService)
    {
        $this->user = Auth::user();
        $this->feedContract = $feedContract;
        $this->getStreamService = $getStreamService;
    }

    public function feeds(Request $request)
    {
        $user = $this->user;

        $perPage = 20;

        $page = $request->has('page') ? $request->input('page') : 1;
        $offset = ($page - 1) * $perPage;

        try {
            $activities = $this->feedContract->getFeed($user->id, $perPage, $offset);
        } catch (Exception $e) {
            return $this->errorInternal('Error loading feeds. Please refresh page to try again!');
        }

        // Count of feeds fetched from getstream
        $activitiesCount = sizeof($activities);
        $feeds = [];
        
        $transformer = new $this->transformer;
        
        for ($i=0; $i < $activitiesCount; $i++) {

            $feedUser = $activities[$i]["actor"];
            $feedPost = $activities[$i]["object"];

            // Filtering non existing posts
            if (
                ($feedUser instanceof User) && 
                ($feedPost instanceof Post)
            ) {
                $feed = (object) [];
                $feed->user = $feedUser;
                $feed->post = $feedPost;

                array_push($feeds, $transformer->transform($feed));
            }
        }

        return $this->response()->array([
            'count' => $activitiesCount,
            'data' => $feeds
        ]);
    }

    public function enrich(Request $request)
    {
        $activities = $request->new;
        if (! $activities) {
            return $this->errorBadRequest('No data to enrich');
        }

        $activities = $this->getStreamService->enrich($activities);

        $feeds = [];
        
        for ($i = 0; $i < sizeof($activities); $i++) {
            
            $feedUser = $activities[$i]["actor"];
            $feedPost = $activities[$i]["object"];

            if (
                ($feedUser instanceof User) && 
                ($feedPost instanceof Post)
            ) {
                $feeds[$i] = (object) [];
                $feeds[$i]->user = $feedUser;
                $feeds[$i]->post = $feedPost;
            }
        }

        $feeds = collect($feeds);

        return $this->response->collection($feeds, $this->transformer);
    }
}
