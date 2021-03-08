<?php

namespace Encore\MediaSelector;

use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Storage;

class FormMediaSelector extends Field
{
    protected $view = 'media-selector::index';

    protected static $css = [
        'vendor/de-memory/media-selector/app.css',
        'vendor/de-memory/media-selector/bootstrap-table/dist/bootstrap-table.min.css'
    ];

    protected static $js = [
        'vendor/de-memory/media-selector/bootstrap-table/dist/bootstrap-table.min.js',
        'vendor/de-memory/media-selector/bootstrap-table/dist/locale/bootstrap-table-zh-CN.min.js',
        'vendor/de-memory/media-selector/sortablejs/Sortable.js',
        'vendor/de-memory/media-selector/app.js',
    ];

    /**
     * dir 媒体上传路径
     * fileNameIsEncrypt 媒体名是否加密
     *
     * @var array
     */
    protected $move = [
        'dir' => 'upload_files',
        'fileNameIsEncrypt' => true,
    ];

    /**
     * 媒体选择最大文件数
     *
     * @var int
     */
    protected $maxFileCount = 1;

    /**
     * 媒体选择类型
     *
     * blend            混合选择
     * image            图片选择
     * video            视频选择
     * audio            音频选择
     * powerpoint       文稿选择
     * code             代码文件选择
     * zip              压缩包选择
     * text             文本选择
     * other            其他选择
     *
     * @var string
     */
    protected $type = 'blend';

    /**
     * 拖动排序
     *
     * @var bool
     */
    protected $sortable = false;

    protected $selectList = [
        'image' => '图片',
        'video' => '视频',
        'audio' => '音频',
        'powerpoint' => '文稿',
        'code' => '代码',
        'zip' => '压缩包',
        'text' => '文本选择',
        'other' => '其它',
    ];

    /**
     * @param string $dir
     * @param bool $fileNameIsEncrypt
     * @return $this
     */
    public function move($dir, $fileNameIsEncrypt = true)
    {
        $this->move = [
            'dir' => $dir,
            'fileNameIsEncrypt' => $fileNameIsEncrypt,
        ];

        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function maxFileCount($count)
    {
        $this->maxFileCount = $count;

        return $this;
    }

    /**
     * @return $this
     */
    public function sortable()
    {
        $this->sortable = true;

        return $this;
    }


    /**
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 初始化
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string|void
     */
    public function render()
    {
        $disk = Storage::disk(config('admin.upload.disk'));

        // 文件存储的根目录
        $rootPath = $disk->url('');

        // 向视图添加变量
        $this->addVariables([
            'maxFileCount' => $this->maxFileCount,
            'rootPath' => $rootPath,
            'type' => $this->type,
            'selectList' => $this->selectList,
        ]);

        $label = $this->label;
        $name = $this->column;
        $move = json_encode($this->move);
        $maxFileCount = $this->maxFileCount;
        $type = $this->type;
        $sortable = $this->sortable;

        $this->script = "
            if(!window.Demo{$name}){
                window.Demo{$name} = new MediaSelector(
                    '{$rootPath}','{$label}','{$name}','{$move}',{$maxFileCount},'{$type}','{$sortable}'
                );
                Demo{$name}.run();
            }
            Demo{$name}.init();
            Demo{$name}.getMediaList();
            Demo{$name}.sortable();
        ";

        return parent::render();
    }
}