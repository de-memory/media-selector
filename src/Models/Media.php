<?php


namespace Encore\MediaSelector\Models;


use Illuminate\Database\Eloquent\Model;

class Media extends Model
{

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array'
    ];

    const IMAGE = 'image';
    const VIDEO = 'video';
    const AUDIO = 'audio';


    // 获取添加时间
    public function getCreatedAtAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->attributes['created_at']));
    }

    // 获取更新时间
    public function getUpdatedAtAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->attributes['updated_at']));
    }
}