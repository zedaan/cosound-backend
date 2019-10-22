<?php

namespace App\Http\Controllers\Notification;

use Auth, Exception, Validator;

use Illuminate\Http\Request;
use App\Models\{User, Post};
use App\Contracts\NotificationContract;
use App\Http\Controllers\BaseApiController;

class NotificationController extends BaseApiController
{
    protected $user, $notificationContract;

    protected $activityTransformer = 'App\Transformers\Notification\NotificationActivityTransformer';
    protected $notificationTransformer = 'App\Transformers\Notification\NotificationTransformer';

    public function __construct(NotificationContract $notificationContract)
    {
        $this->user = Auth::user();
        $this->notificationContract = $notificationContract;
    }

    public function notifications(Request $request)
    {
        $user = $this->user;
        $perPage = 20;

        $page = $request->has('page') ? $request->input('page') : 1;
        $offset = ($page - 1) * $perPage;

        $activityTransformer = new $this->activityTransformer;

        try {
            $notificationFeedData = $this->notificationContract->getNotifications($user->id, $perPage, $offset);
            $data = $notificationFeedData['results'];
        } catch (Exception $e) {
            return $this->errorInternal('Error loading notifications. Please try again!');
        }

        // Count of notifications fetched from getstream
        $notificationCount = sizeof($data);

        $notificationList = [];
        
        for ($i=0; $i < sizeof($data); $i++) {
            $notificationData = $data[$i];
            $notification = (object) [];
            $activities = [];

            for ($j=0; $j < sizeof($notificationData['activities']); $j++) {
                $actor = $notificationData['activities'][$j]['actor'];
                $object = $notificationData['activities'][$j]['object'];
                $comment = $notificationData['activities'][$j]['comment'] ?? null;

                if (
                    ($actor instanceof User) && 
                    ($object instanceof Post)
                )
                {
                    $activity = (object) [];
                    $activity->user = $actor;
                    $activity->comment = $comment;
                    $notification->post = $object;
                    // $activity->post = $object;

                    array_push($activities, $activityTransformer->transform($activity));
                }
            }

            $notification->activities = $activities;
            // $notification->activity_count = $notificationData['activity_count'];
            $notification->activity_count = sizeof($notification->activities);
            $notification->actor_count = $notificationData['actor_count'];
            $notification->updated_at = $notificationData['updated_at'];
            $notification->is_read = $notificationData['is_read'];
            $notification->is_seen = $notificationData['is_seen'];
            $notification->verb = $notificationData['verb'];
            $notification->id = $notificationData['id'];

            if ($notification->activity_count > 0)
                $notificationList[] = $notification;            
        }

        $notifications = [];

        $transformer = new $this->notificationTransformer;

        foreach ($notificationList as $notification)
            $notifications[] = $transformer->transform($notification);

        return $this->response()->array([
            'count' => [
                'total' => $notificationCount,
                'unread' => $notificationFeedData['unread'],
                'unseen' => $notificationFeedData['unseen']
            ],
            'data' => $notifications
        ]);

        // $notificationList = collect($notificationList);

        // return $this->response->collection($notificationList, $this->notificationTransformer);
    }

    public function action(Request $request, $type)
    {
        $user = $this->user;

        if (! in_array($type, ["seen", "read"]))
            return $this->response->errorBadRequest("Invalid action!");

        $data = request([ 'notifications' ]);

        $rules = [ 'notifications' => 'required|array' ];
       
        $validator = Validator::make($data, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }
        
        $options = [];
        $options['mark_seen'] = $data['notifications'];
        $type === "read" ? $options['mark_read'] = $data['notifications'] : null;

        $this->notificationContract->getNotifications($user->id, 0, 0, $options);

        if ($type === "seen")
            return $this->count($request);

        return response()->json([
            'data' => $type
        ]);
    }

    public function count(Request $request)
    {
        $user = $this->user;

        $notificationFeedData = $this->notificationContract->getNotifications($user->id, 0, 0);
        return response()->json([
            'data' => $notificationFeedData['unseen']
        ]);
    }
}
