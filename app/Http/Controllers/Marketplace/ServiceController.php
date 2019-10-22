<?php

namespace App\Http\Controllers\Marketplace;

use Auth, DB, Exception, Storage, Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;

use App\Jobs\{DeleteMedia, ExtractMediaMetadata};
use App\Http\Controllers\Controller;
use App\Models\{Service, ServiceSubCategory, Upload};
use App\Contracts\Marketplace\ServiceContract;

class ServiceController extends Controller
{
    protected $user;
    protected $serviceContract;
    protected $transformer = 'App\Transformers\Marketplace\ServiceTransformer';

    protected $allowedOrderFields = ['title', 'price', 'rating', 'review_count', 'created_at'];
    protected $searchFields = ['title', 'description'];

    public function __construct(ServiceContract $serviceContract)
    {
        $this->user = Auth::user();
        $this->serviceContract = $serviceContract;
    }

    public function getServicesByCategory(Request $request, $categoryId, $subCategoryId = null)
    {
        $perPage = $request->has('per_page') ? $request->input('per_page') : 12;

        $services = Service::where('approved', 1)->where('category_id', $categoryId);
        $searchString = $request->has('query') ? '%' . $request->input('query') . '%' : "";

        if ($subCategoryId) {
            $services = $services->where('sub_category_id', $subCategoryId);
        }

        if ($searchString) {
            $services = $services->where(function ($query) use ($searchString) {
                foreach ($this->searchFields as $field) {
                    $query = $query->orWhere($field, 'like', $searchString);
                }
            });
        }
        
        if (Input::has('order'))
            $services = sortResults($services, Input::get('order'), $this->allowedOrderFields);
        else
            $services = $services->orderBy('created_at','desc');
            
        $services = $services->paginate($perPage);

        return $this->response->paginator($services, $this->transformer);
    }

    public function getfeaturedServices(Request $request)
    {
        $services = Service::where('approved', 1)->where('featured', true)->inRandomOrder()->limit(5)->get();

        return $this->response->collection($services, $this->transformer);
    }

    public function createService(Request $request)
    {
        $featuredImagesMaxCount = 4;

        $data = request([
    		'category_id',
    		'sub_category_id',
    		'title',
            'description',
            'about',
            'key_points',
            'price',
            'delivery_time',
            'delivery_time_unit',
    		'image',
            'featured_images',
        ]);

    	$rules = [
            'category_id' => 'required|integer',
            'sub_category_id' => 'required|integer',
    		'title' => 'required|max:100',
            'description' => 'required',
    		'about' => 'required',
            'key_points' => 'required',
            'price' => 'required|numeric',
            'delivery_time' => 'required|numeric|min:1',
            'delivery_time_unit' => ['required', Rule::in(['hour', 'day', 'week'])],
            'image' => 'required|image|max:14648',
            'featured_images' => 'array|max:' . $featuredImagesMaxCount
        ];

        foreach(range(0, $featuredImagesMaxCount - 1) as $index) {
            $rules['featured_images.' . $index] = 'image|max:14648';
        }

        $validator = Validator::make($data, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $subCategory = ServiceSubCategory::find($data['sub_category_id']);
        if (! $subCategory) {
            return $this->errorBadRequest('Service sub category doesn\'t exist!');
        }
            
        if ($subCategory->parent_id != $data['category_id']) {
            return $this->errorBadRequest('Service sub category doesn\'t belong to specified category!');
        }
        
        $savedFiles = [];

        DB::beginTransaction();

        try {
            $data['user_id'] = $this->user->id;
            
            $service = $this->serviceContract->create($data);

            $path = Storage::put('services/images', $data['image'], 'public');
            $savedFiles[] = $path;

            $upload = new Upload;
                
            $upload['path'] = $path;
            $upload['file_type'] = 'image';
            $upload['user_id'] = $this->user->id;

            $meta = (object) [];
            $meta->isMain = true;

            $upload['metadata'] = $meta;

            $service->media()->save($upload);
            dispatch((new ExtractMediaMetadata($upload->id))->onQueue('image'));
            
            if ($data['featured_images'] ?? null) {
                foreach($data['featured_images'] as $image) {
                    $path = Storage::put('services/images', $image, 'public');
                    $savedFiles[] = $path;

                    $upload = new Upload;
                        
                    $upload['path'] = $path;
                    $upload['file_type'] = 'image';
                    $upload['user_id'] = $this->user->id;

                    $service->media()->save($upload);
                    dispatch((new ExtractMediaMetadata($upload->id))->onQueue('image'));
                }
            }

            DB::commit();

            return $this->response->item($service, $this->transformer);

        } catch(Exception $e) {
            DB::rollback();

            foreach ($savedFiles as $file) {
                dispatch((new DeleteMedia($file))->onQueue('delete'));
            }
            
            return $this->errorInternal("Service couldn't be created due to some internal error. Please try again after some time!");
        }
    }

    public function getServiceById(Request $request, $id)
    {
        $service = Service::find($id);

        if (! $service)
            return $this->errorNotFound('Service doesn\'t exist!');

        return $this->response->item($service, $this->transformer);
    }

    public function getOfferedServices(Request $request)
    {
        $user = $this->user;

        $perPage = 3;

        $services = $user->offeredServices();

        if (Input::has('order'))
            $services = sortResults($services, Input::get('order'), $this->allowedOrderFields);
        else
            $services = $services->orderBy('created_at','desc');

        $services = $services->paginate($perPage);

        return $this->response->paginator($services, $this->transformer);
    }

    public function getPurchasedServices(Request $request)
    {
        $user = $this->user;

        $perPage = 3;

        $services = $user->purchasedItems()->select('order_items.*','services.title','services.rating','services.review_count')->join('services','services.id','=','order_items.service_id');
        
        if (Input::has('order'))
            $services = sortResults($services, Input::get('order'), $this->allowedOrderFields);
        else
            $services = $services->orderBy('created_at','desc');

        $services = $services->paginate($perPage);

        return $this->response->paginator($services, 'App\Transformers\Marketplace\PurchasedServiceTransformer');
    }
}
