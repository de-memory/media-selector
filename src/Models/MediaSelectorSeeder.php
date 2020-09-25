<?php

namespace Encore\MediaSelector\Models;

use Encore\MediaSelector\RestApi\Helpers\FileUtil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class MediaSelectorSeeder extends Seeder
{
    /**
     * 运行数据库填充。
     *
     * @return void
     */
    public function run()
    {
        $this->_initFileData();

    }

    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    protected $storage;

    /**
     * @var array
     */

    public function _initFileData()
    {
        set_time_limit(0);

        ini_set('memory_limit', '5000M');

        $disk = Storage::disk(config('admin.upload.disk'));

        $this->storage = $disk;

        $allFiles = $disk->allFiles('/');

        $dataList = [];

        foreach ($allFiles as $key => $v) {

            if (Media::query()->where('path', $v)->count() > 0)
                continue;

            $file = $this->storage->getDriver()->getAdapter()->applyPathPrefix($v);

            $bucket = $disk == 'qiniu' ? config('filesystems.disks.qiniu.bucket') : null;

            $meta = $this->_getMeta($file);

            $dataList[] = array(
                'user_id' => 0,
                'path' => $v,
                'file_name' => FileUtil::getBasename($file),
                'size' => $disk->size($v),
                'type' => FileUtil::getFileType($file),
                'file_ext' => FileUtil::getExtension($file),
                'disk' => config('admin.upload.disk'),
                'bucket' => $bucket,
                'meta' => $meta,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }
        Media::query()->insert($dataList);
    }

    private function _getMeta($file)
    {
        switch (FileUtil::getFileType($file)) {
            case 'image':
                $manager = new ImageManager();
                $image = $manager->make($file);
                $meta = [
                    'format' => FileUtil::getExtension($file),
                    'suffix' => FileUtil::getExtension($file),
                    'size' => FileUtil::getFileSize($file),
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
                    'format' => FileUtil::getFileType($file),
                    'suffix' => FileUtil::getFileType($file),
                    'size' => FileUtil::getFileSize($file),
                    'width' => 0,
                    'height' => 0
                ];
                break;
            default :
                $meta = [
                    'format' => FileUtil::getFileType($file),
                    'suffix' => FileUtil::getFileType($file),
                    'size' => FileUtil::getFileSize($file),
                    'width' => 0,
                    'height' => 0
                ];
        }
        return json_encode($meta);
    }
}