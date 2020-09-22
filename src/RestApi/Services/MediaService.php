<?php

namespace Encore\MediaSelector\RestApi\Services;

use Encore\MediaSelector\Models\Media;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

class MediaService
{
    public function getMediaList($userId, $keyword, $order, $orderName, $pageSize, $type)
    {
        $query = Media::query()->where(function ($q) use ($keyword, $type) {

            if (!empty($keyword))
                $q->where('file_name', 'like', '%' . $keyword . '%')
                    ->orWhere('file_ext', 'like', '%' . $keyword . '%');

            if (!empty($type))
                $q->where('type', $type);

        });

        $list = $query->orderBy($orderName, $order)->paginate($pageSize);

        $dataList = [];

        foreach ($list as $value) {

            $dataList[] = array(
                'id' => $value->id,
                'media_type' => $value->type,
                'path' => $value->path,
                'size' => sprintf('%.2f', $value->size / 1024 / 1024),
                'file_ext' => $value->file_ext,
                'file_name' => $value->file_name,
                'created_at' => $value->created_at,
            );
        }


        return json_encode(["total" => $list->total(), "data" => $dataList], JSON_UNESCAPED_UNICODE);
    }

    public function upload($userId, UploadedFile $file, $type, $move)
    {
        $mime_type = $file->getMimeType();
        $type_info = $this->_getTypeInfoByMimeType($mime_type);

        //配置上传信息
        config([
            'filesystems.default' => config('union.disk', 'admin')
        ]);

        $disk = config('filesystems.default');

        $bucket = $disk == 'qiniu' ? config('filesystems.disks.qiniu.bucket') : null;

        $folder = $move->dir; //保存文件夹

        $file_name = $this->_getFileName($move, $file);

        $path = $file->storeAs($folder, $file_name);

        $meta = $this->_getMeta($disk, $file, $path, $type_info);

        $data = [
            'user_id' => $userId,
            'path' => $path,
            'file_name' => $file_name,
            'size' => $file->getSize(),
            'type' => $type_info['type'],
            'file_ext' => $type_info['suffix'],
            'disk' => $disk,
            'bucket' => $bucket,
            'meta' => $meta
        ];

        return Media::query()->create($data);
    }

    private function _getMeta($disk, $file, $path, $type_info)
    {
        switch ($type_info['type']) {
            case 'image':
                $manager = new ImageManager();
                $image = $manager->make($file);
                $meta = [
                    'format' => $type_info['suffix'],
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => $image->getWidth(),
                    'height' => $image->getHeight()
                ];
                break;
            default :
                $meta = [];
        }
        return $meta;
    }

    private function _getTypeInfoByMimeType($mt)
    {
        $arr = explode('/', $mt);
        return [
            'type' => $arr[0],
            'suffix' => $arr[1]
        ];
    }

    private function _getFileName($move, $file)
    {
        $fileName = $file->getClientOriginalName();
        if ($move->fileNameIsEncrypt)
            $fileName = md5(rand(1, 99999) . $file->getClientOriginalName()) . "." . $file->getClientOriginalExtension();

        return $fileName;
    }
}