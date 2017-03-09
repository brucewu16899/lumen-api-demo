<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *   definition="Post",
 *   type="object"
 * )
 */
class Post extends BaseModel
{
    use SoftDeletes;

    /**
     * @var int
     * @SWG\Property(format="int64")
     */
    public $id;

    /**
     * @var int
     * @SWG\Property(format="int64")
     */
    public $user_id;
    /**
     * @var string
     * @SWG\Property(example="it is a title")
     */
    public $title;

    /**
     * @var string
     * @SWG\Property(example="it is content")
     */
    public $content;

    /**
     * @var string
     * @SWG\Property(example="2017-01-25 22:52:42")
     */
    public $created_at;


    protected $casts = ['extra' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
}
