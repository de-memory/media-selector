<?php

namespace Encore\MediaSelector\Models;

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
    protected $fileTypes = [
        'image' => 'png|jpg|jpeg|tmp|gif',
        'word' => 'doc|docx',
        'ppt' => 'ppt|pptx',
        'pdf' => 'pdf',
        'code' => 'php|js|java|python|ruby|go|c|cpp|sql|m|h|json|html|aspx',
        'zip' => 'zip|tar\.gz|rar|rpm',
        'txt' => 'txt|pac|log|md',
        'audio' => 'mp3|wav|flac|3pg|aa|aac|ape|au|m4a|mpc|ogg',
        'video' => 'mkv|rmvb|flv|mp4|avi|wmv|rm|asf|mpeg',
    ];

    public function _initFileData()
    {

        $disk = Storage::disk(config('admin.upload.disk'));

        $this->storage = $disk;

        $allFiles = $disk->allFiles('/');

        $dataList = [];

        foreach ($allFiles as $key => $v) {

            $file = $this->storage->getDriver()->getAdapter()->applyPathPrefix($v);

            $bucket = $disk == 'qiniu' ? config('filesystems.disks.qiniu.bucket') : null;

            $meta = $this->_getMeta($file);

            $dataList[] = array(
                'user_id' => 0,
                'path' => $v,
                'file_name' => $this->getBasename($file),
                'size' => $disk->size($v),
                'type' => $this->getFileType($file),
                'file_ext' => $this->getExtension($file),
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
        switch ($this->getFileType($file)) {
            case 'image':
                $manager = new ImageManager();
                $image = $manager->make($file);
                $meta = [
                    'format' => $this->getExtension($file),
                    'suffix' => $this->getExtension($file),
                    'size' => $this->getFileSize($file),
                    'width' => $image->getWidth(),
                    'height' => $image->getHeight()
                ];
                break;
            case 'video':
                $meta = [
                    'format' => $this->getFileType($file),
                    'suffix' => $this->getFileType($file),
                    'size' => $this->getFileSize($file),
                    'width' => 0,
                    'height' => 0
                ];
                break;
            default :
                $meta = [];
        }
        return json_encode($meta);
    }

    public function getFileSize($file)
    {
        return File::size($file);
    }

    protected function getBasename($file)
    {
        return File::basename($file);
    }

    protected function getExtension($file)
    {
        return File::extension($file);
    }

    protected function getFileType($file)
    {
        foreach ($this->fileTypes as $type => $regex) {
            if (preg_match("/^($regex)$/i", $this->getExtension($file)) !== 0) {
                return $type;
            }
        }
        return false;
    }

}