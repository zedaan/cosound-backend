<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class BaseApiController extends Controller
{
	protected $obj;
	protected $transformer;
	protected $per_page = 10;
	protected $guestAcl = [];
	protected $filters = [];
	protected $order_checked = 0;

	public function __construct()
	{
		$this->obj = new $this->model;
		$this->transformer = new $this->transformer;
	}

	public function index($query = null) 
	{	
		if(!$query)
			$query = $this->obj->buildQuery();

		if (Input::has('filter'))
			$query = $query->genericSearch(Input::get('filter'));

		foreach ($this->filters as $filter) {

			if(Input::has($filter))
				$query = $query->where($filter,'=',Input::get($filter));	
		}
		
		if (Input::has('order') && $this->order_checked == 0) {
			$desc = substr(Input::get('order'),0,1);
			if ( $desc == '-' ) {
                $order = ltrim(Input::get('order'),'-'); 
			    $query = $query->orderBy($order,'desc');				
			} else { 	
				$order = ltrim(Input::get('order'),'-'); 
				$query = $query->orderBy($order);
			}	
		}

		$relationAsStr = Input::has('relations') ? Input::get('relations') : '';
		$relationAsStr = array_map('trim', array_filter(explode(',', $relationAsStr)));

		$query->with($relationAsStr);

		$perPage = Input::has('per_page') ? Input::get('per_page') : $this->per_page;
		
		if ( Input::has('distinct') )
			$query = $query->groupBy(Input::get('distinct'));

		$sendData = $query->paginate($perPage);

		return $this->response->paginator($sendData, $this->transformer);
	}

	public function store(Request $request)
	{			
        $obj = $this->obj->create($request->all());

        return $this->response->array([
    		'data' => [
				'id' => $obj->id
    		]
    	]);       
	}

	public function show($id)
	{
		$genericObj = $this->obj->find($id);
		if (empty($genericObj)) {
			return $this->response->errorBadRequest();
		}

		$relationAsStr = Input::has('relations')?Input::get('relations') : '';
		$relationAsStr = array_map('trim', array_filter(explode(',', $relationAsStr)));

		$sendData = $this->obj->with($relationAsStr)->find($id);
		
		return $this->response->item($sendData, $this->transformer);
	}

	public function update(Request $request, $id)
	{	
		$genericObj = $this->obj->find($id);
		if (empty($genericObj)) {
			return $this->response->errorBadRequest();
		}

		// if (!(in_array("update", $this->guestAcl)))
			//$this->authorize($this->obj);

		if (isset($request['email'])) {
			$genericObj->fill($request->except('id'));
		}

		$genericObj->save();
		$object = $this->obj->find($id);
		return $this->response->item($object, $this->transformer);
	}

	public function destroy($id)
	{
		$genericObj = $this->obj->find($id);
		if(! $genericObj) {
			return $this->response->errorBadRequest();
		}

		if ($genericObj->delete()) {
			return $this->response->array([
				'data' => array(
						'message' => 'record_deleted_successfully'
					)
			]);
		}
	}
}