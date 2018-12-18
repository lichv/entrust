<?php
namespace Lichv\Entrust;

/**
 * This file is part of Entrust,
 * a group & user & role management solution for Laravel.
 *
 * @license MIT
 * @package Lichv\Entrust
 */

use Lichv\Entrust\Contracts\EntrustGroupInterface;
use Lichv\Entrust\Traits\EntrustGroupTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class EntrustGroup extends Model implements EntrustGroupInterface
{
    use EntrustGroupTrait;

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
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('entrust.groups_table');
    }

}
