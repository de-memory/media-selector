<?php

namespace Encore\MediaSelector\RestApi\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\Resource;

class ResourcesMedia extends Resource
{
    use CustomResource;

    public function toArray($request)
    {
        switch ($this->type) {
            case 'image':
                return $this->_image();
                break;
            case 'video':
                return $this->_video();
                break;
            default:
                return [];
        }

    }


    private function _image()
    {
        return $this->filterFields([
            'id' => $this->id,
            'media_type' => $this->type,
            'name' => $this->file_name,
            'size' => $this->_setSize($this->size),
            //'file_ext' => $this->file_ext,
            'path' => $this->path,
            'url' => $this->_storeMedia($this),
            'meta' => $this->meta,
            //'disk' => $this->disk,
            'width' => $this->meta['width'],
            'height' => $this->meta['height'],
        ]);
    }

    private function _video()
    {
        return $this->filterFields([
            'id' => $this->id,
            'media_type' => $this->type,
            'name' => $this->file_name,
            'size' => $this->_setSize($this->size),
            //'file_ext' => $this->file_ext,
            'path' => $this->path,
            'url' => $this->_storeMedia($this),
            'meta' => $this->meta,
            //'disk' => $this->disk,
            'width' => 0,
            'height' => 0,
        ]);
    }

    public static function _storeMedia($media)
    {
        $disk = Storage::disk($media->disk);
        return $disk->url($media->path);
    }

    public static function _setSize($size)
    {
        $units = array(' B', ' KB', ' M', ' G', ' T');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $units[$i];

    }
}