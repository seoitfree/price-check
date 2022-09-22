<?php
namespace ItFreeCrm\Hotline\Domains\Entities;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotlineReport  extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'hotline_reports';

    /**
     * @var array
     */
    protected $fillable = [
        "product_id",
        "url_path",
        "created_at",
        'title',
        'price',
        'min_price',
        'leader_price',
    ];

    protected $casts = [
        'shops' => 'array'
    ];
}
