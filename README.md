# Laravar-admin 媒体资源选择器

![图片名称](https://laravel-admin.org/storage/2020/09/25/C8WZmrKi6Ocl7zYRxiaLgbMrFHfWmjUm0Ct77U73.png)

## 依赖
 
- php  | >=7.0.0
- encore/laravel-admin  | >=~1.6 
- intervention/image  | >= ^2.4 

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

### 将根目录下面的文件同步到数据库(可以不执行。如果执行会去掉数据库已有的，根据path字段过滤)

```
php artisan media-selector:install
```

## 更新

```
composer update

// 强制发布静态资源文件
php artisan vendor:publish --tag=media-selector --force

// 清理视图缓存
php artisan view:clear
```


## 方法使用

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
```

## 参数说明

```
/*
|--------------------------------------------------------------------------
| 媒体选择数量。默认1
|--------------------------------------------------------------------------
*/
maxFileCount(int)

/*
|--------------------------------------------------------------------------
| 媒体上传路径，媒体名称是否加密
|--------------------------------------------------------------------------
| 第一个参数，媒体上传路径。默认upload_files
| 第二个参数，媒体名称是否加密。默认true
|
| 注意：第二个参数如果是false，上传文件时，跟已上传的文件名称相同，会覆盖已上传的文件
| 
*/
move(string, boolean)

/*
|--------------------------------------------------------------------------
| 媒体选择类型。默认blend
|--------------------------------------------------------------------------
| blend            混合选择
| image            图片选择
| video            视频选择
| audio            音频选择
| powerpoint       文稿选择
| code             代码文件选择
| zip              压缩包选择
| text             文本选择
| other            其他选择
*/
type(string)

/*
|--------------------------------------------------------------------------
| 开启推动排序。
|--------------------------------------------------------------------------
*/
sortable()
```

## 说明

```
数据保存处理
1、可以用官网文档中的，模型表单回调
https://laravel-admin.org/docs/zh/1.x/model-form-callback

2、可以用laravel模型处理（模型修改器）
https://learnku.com/docs/laravel/5.8/eloquent-mutators/3934#defining-a-mutator
```

## 若要回滚最后一次迁移， 以下命令。 此命令将回滚最后一次 “迁移” 的操作，只回滚该推展“迁移”。(谨慎操作)

```
php artisan migrate:rollback --path=vendor/de-memory/media-selector/database/migrations
```