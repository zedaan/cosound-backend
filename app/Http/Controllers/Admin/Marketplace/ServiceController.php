<?php

namespace App\Http\Controllers\Admin\Marketplace;

use Mail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Mails\{ServiceApprovalEMail, ServiceRemovalEMail};
use App\Models\Service;

class ServiceController extends Controller
{
    protected $transformer = 'App\Transformers\Admin\ServiceTransformer';
    protected $listTransformer = 'App\Transformers\Admin\ServiceListTransformer';

    protected $allowedOrderFields = ['title', 'price', 'rating', 'review_count', 'created_at'];
    protected $searchFields = ['title', 'services.description', 'name', 'CONCAT (first_name, \' \', last_name)'];           // name = service_categories.name
    
    public function activeServices(Request $request)
    {
        $perPage = $request->has('per_page') ? $request->input('per_page') : 5;
        $searchString = $request->has('query') ? '%' . $request->input('query') . '%' : "";

        $services = Service::where('approved', 1)
                            ->join('users','services.user_id','=','users.id')
                            ->join('service_categories','services.category_id','=','service_categories.id')
                            ->select("services.*");

        if ($searchString)
            $services = filterResults($services, $searchString, $this->searchFields);

        if (Input::has('order'))
            $services = sortResults($services, Input::get('order'), $this->allowedOrderFields);
        else
            $services = $services->orderBy('created_at','desc');
            
        $services = $services->paginate($perPage);

        return $this->response->paginator($services, $this->listTransformer);
    }

    public function pendingServices(Request $request)
    {
        $perPage = $request->has('per_page') ? $request->input('per_page') : 5;
        $searchString = $request->has('query') ? '%' . $request->input('query') . '%' : "";

        $services = Service::where('approved', 0)
                            ->join('users','services.user_id','=','users.id')
                            ->join('service_categories','services.category_id','=','service_categories.id')
                            ->select("services.*");

        if ($searchString)
            $services = filterResults($services, $searchString, $this->searchFields);

        if (Input::has('order'))
            $services = sortResults($services, Input::get('order'), $this->allowedOrderFields);
        else
            $services = $services->orderBy('created_at','desc');

        $services = $services->paginate($perPage);

        return $this->response->paginator($services, $this->listTransformer);
    }

    public function getServiceById(Request $request, $id)
    {
        $service = Service::find($id);

        if (! $service)
            return $this->errorNotFound('Service doesn\'t exist!');

        return $this->response->item($service, $this->transformer);
    }

    public function approveService(Request $request, $id)
    {
        $service = Service::find($id);

        if (! $service)
            return $this->errorNotFound('Service doesn\'t exist!');

        if ($service->approved)
            return $this->errorBadRequest('Service already approved!');

        $service->approved = true;
        $service->save();

        $provider = $service->user;
        Mail::to(
			$provider->email,
			$provider->first_name
		)->queue((new ServiceApprovalEMail($service->id))->onQueue('mail'));

        return $this->response->item($service, $this->transformer);
    }

    public function deleteService(Request $request, $id)
    {
        $service = Service::find($id);

        if (! $service)
            return $this->errorNotFound('Service doesn\'t exist!');

        $comment = request('comment') ?? "";

        $provider = $service->user;
        $title = $service->title;
        
        if ($service->approved) {
            return $this->response->errorBadRequest('Service cannot be deleted as it is approved');
        }

        $service->delete();

        Mail::to(
			$provider->email,
			$provider->first_name
		)->queue((new ServiceRemovalEMail($title, $provider->first_name, $comment))->onQueue('mail'));

        return $this->response->item($service, $this->transformer);
    }
}
