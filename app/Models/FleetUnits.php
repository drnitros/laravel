<?php 

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetUnits extends Model {
    use SoftDeletes;

    public $primaryKey = 'id';
    protected $table = 'fleet_units';
    protected $guarded = ['id'];
    protected $dates =['deleted_at'];

    
}