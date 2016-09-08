<?php namespace Itctrust;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Itctrust\Contracts\ItctrustPermissionSetInterface;
use Itctrust\Traits\ItctrustPermissionSetTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;


class ItctrustPermissionSet extends Model implements ItctrustPermissionSetInterface
{

	use ItctrustPermissionSetTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    public function __construct(array $attributes = []){
    	parent::__construct($attributes);
    	$this->table = Config::get('itctrust.permission_sets_table');

    }

}