<?php

namespace Encore\MediaSelector\RestApi\Services;

use Encore\MediaSelector\Models\Media;
use Encore\MediaSelector\RestApi\Helpers\FileUtil;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
                'size' => FileUtil::getFormatBytes($value->size),
                'file_ext' => $value->file_ext,
                'name' => $value->file_name,
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

        $getFileType = FileUtil::getFileType(Storage::disk(config('admin.upload.disk'))->url($path));

        $meta = $this->_getMeta($file, $getFileType, $type_info['suffix']);

        $data = [
            'user_id' => $userId,
            'path' => $path,
            'file_name' => $file_name,
            'size' => $file->getSize(),
            'type' => $getFileType,
            'file_ext' => $file->getClientOriginalExtension(),
            'disk' => $disk,
            'bucket' => $bucket,
            'meta' => $meta
        ];

        return Media::query()->create($data);
    }

    private function _getMeta($file, $getFileType, $format)
    {
        switch ($getFileType) {
            case 'image':
                $manager = new ImageManager();
                $image = $manager->make($file);
                $meta = [
                    'format' => $format,
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => $image->getWidth(),
                    'height' => $image->getHeight()
                ];
                break;
            case 'video':
            case 'audio':
            case 'powerpoint':
            case 'code':
            case 'zip':
            case 'text':
                $meta = [
                    'format' => $format,
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => 0,
                    'height' => 0
                ];
                break;
            default :
                $meta = [
                    'format' => $format,
                    'suffix' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'width' => 0,
                    'height' => 0
                ];;
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