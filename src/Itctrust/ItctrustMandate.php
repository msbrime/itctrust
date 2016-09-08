<?php namespace Itctrust;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Itctrust\Contracts\ItctrustRoleInterface;
use Itctrust\Traits\ItctrustMandateTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ItctrustMandate extends Model implements ItctrustRoleInterface
{
    use ItctrustMandateTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('itctrust.mandates_table');
    }
}
