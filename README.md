# Laravar-admin 本地媒体选择器
![1](https://github.com/de-memory/media-selector/blob/master/1.png)
![2](https://github.com/de-memory/media-selector/blob/master/2.png)
![3](https://github.com/de-memory/media-selector/blob/master/3.png)

## 依赖

  -    | 版本  
 ---- | ----- 
 php  | >=7.0.0 
 encore/laravel-admin  | >=~1.6 
 intervention/image  | >= ^2.4


## 安装

### composer 安装

```
composer require de-memory/media-selector
```

### 发布资源

```
php artisan vendor:publish --provider=Encore\MediaSelector\MediaSelectorServiceProvider
```

### 添加数据库

```
php artisan migrate --path=vendor/de-memory/media-selector/database/migrations
```

### 回滚数据库（这里是指删除，数据库表）

```
php artisan migrate:rollback --path=vendor/de-memory/media-selector/database/migrations
```

### 将根目录下面的文件同步到数据库

```
php artisan media-selector:install
```

### 参数说明

```
/**
* move:第一个参数上传路径（默认路径upload_files），第二个参数媒体名是否加密（默认false）
*
* type:上传类型，选择类型（模态框上传无限制）
*
*/
$form->mediaSelector('avatar', '头像')->move('user', true)->type('image')->help('只能上传png, jpg, jpeg, bmp, gif, webp, psd, svg, tiff');

/**
* maxFileCount:上传数量，选择数量（模态框上传无限制）
*
* sortable:开启推动排序
*
*/
$form->mediaSelector('avatar1', '头像1')->maxFileCount(3)->sortable()->help('最多上传或选择三个媒体文件，可推动排序');

maxFileCount(int)：媒体选择数量（默认1）

move(dir,false)： 第一个参数上传路径（默认upload_files），第二个参数上传的文件是否加密（默认false）

type(blend)：blend | 混合选择，image | 图片选择，video | 视频选择，udio | 音频选择，txt   | txt选择，excel | excel选择，word  | word选择，pdf   | pdf选择，ppt   | word选择

sortable()：开启推动排序（默认关闭）

```
