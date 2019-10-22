<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model {

    /*
    |--------------------------------------------------------------------------
    | Base class for models
    |--------------------------------------------------------------------------
    |
    | This model base class provides a central location to place any logic that
    | is shared across all of the models. The trait included with the class
    | provides validation on model which can be defined in "rules" property of
    | each of the derived classes. If "rules" is not defined, validation is
    | skipped for that derived class.
    |--------------------------------------------------------------------------
    |
    */


	/**
	 * The attributes that have default values defined for each derived class.
	 *
	 * @var array
	 */
	protected $defaults = [];

	//protected $throwValidationExceptions = false;

	/**
	 * The validations rules applicable on attributes defined for each derived class.
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * The constructor function
	 *
	 * @param array $attributes
	 *
	 * @return void
	 */
	function __construct(array $attributes = [])
	{
		// Assign default attributes values to raw attributes
		$this->setRawAttributes($this->defaults, true);
		parent::__construct($attributes);
	}

	/**
	 * Scope function to return a query builder.
	 *
	 * @param query $query
	 *
	 * @return  query
	 */
	public function scopeBuildQuery($query)
	{
		return $query;
	}

}