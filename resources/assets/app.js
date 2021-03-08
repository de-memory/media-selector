(function () {

        function MediaSelector(root_path, label, name, move, max_file_count, type, sortable) {

            this.root_path = root_path;

            this.input_label = label;

            this.input_name = name;

            this.move = move;

            this.max_file_count = max_file_count;

            this.type = type;

            this.sortables = sortable;

        }

        // 初始化
        MediaSelector.prototype.init = function () {

            var _this = this;

            var value = $('input[name=' + this.input_name + ']').val();

            // 获取name值后将input清空，防止叠加
            $('input[name=' + this.input_name + ']').val('');

            $('#' + _this.input_name + 'MediaType').select2({
                language: 'zh-CN',
                placeholder: '类型',
                allowClear: true,
                minimumInputLength: 0
            });

            $('#' + _this.input_name + 'MediaSelectorModalLabel').text('请选择' + _this.input_label);

            if (value) {
                var arr = value.split(',');
                for (var i in arr) {
                    var suffix = arr[i].substring(arr[i].lastIndexOf(".") + 1);
                    var fileType = _this.getFileType(suffix);
                    _this.fileDisplay({data: {path: arr[i], media_type: fileType}})
                }
            }
        };

        // 初始化点击事件
        MediaSelector.prototype.run = function () {

            var _this = this;

            // Form媒体上传事件
            $("body").delegate('#' + _this.input_name + 'MediaUploadForm', 'change', function (e) {

                if ($(this).val() != "") {

                    var files = $(this)[0].files;

                    if (_this.max_file_count > 1 && files && (_this.getFileNumber() + files.length) > _this.max_file_count) {
                        toastr.error('媒体文件不能超过' + _this.max_file_count + '个', 400);
                        return false;
                    }

                    var isEnd = true;
                    $.each(files, function (i, field) {
                        var suffix = field.name.substring(field.name.lastIndexOf(".") + 1);

                        var fileType = _this.getFileType(suffix);

                        if (_this.type != 'blend' && _this.type != fileType) {
                            toastr.error('媒体文件有误：' + field.name, 400);
                            isEnd = false;
                            return false;
                        }

                    });

                    if (isEnd)
                        _this.mediaUpload(this, 'form')
                }


                // 操作完成后，使用如下代码，将其值置位空，则可以解决再次触发change事件时失效的问题
                e.target.value = '';
            });

            // Modal媒体上传事件
            $("body").delegate('#' + _this.input_name + 'MediaUploadModal', 'change', function (e) {

                if ($(this).val() != "")
                    _this.mediaUpload(this, 'modal');

                // 操作完成后，使用如下代码，将其值置位空，则可以解决再次触发change事件时失效的问题
                e.target.value = '';
            });

            // Modal选择选点击事件
            $("body").delegate('#' + _this.input_name + 'Choose', 'click', function () {

                var row = $('#' + _this.input_name + 'MediaTable').bootstrapTable('getSelections');

                if (row.length == 0)
                    $('#' + _this.input_name + 'MediaSelectorModal').modal('hide');

                if (_this.max_file_count > 1 && (_this.getFileNumber() + row.length) > _this.max_file_count) {
                    toastr.error('媒体文件不能超过' + _this.max_file_count + '个', 400);
                    return false;
                }

                $.each(row, function (i, field) {
                    if (_this.type != 'blend') {
                        if (field.media_type != _this.type) {
                            toastr.error('只能选择类型:' + _this.type + '的媒体', 400);
                            return false;
                        }
                    }
                });

                $.each(row, function (i, field) {
                    _this.fileDisplay({data: field});
                    $('#' + _this.input_name + 'MediaSelectorModal').modal('hide')
                });
            });

            // Modal框筛选点击事件
            $("body").delegate('#' + _this.input_name + 'Screening', 'click', function () {
                $('#' + _this.input_name + 'MediaSelectorModalForm').toggle();
            });

            // Modal框查询点击事件
            $("body").delegate('#' + _this.input_name + 'Query', 'click', function () {
                $('#' + _this.input_name + 'MediaTable').bootstrapTable(('refresh'));
            });
        };

        // 获取媒体列表数据
        MediaSelector.prototype.getMediaList = function () {

            var _this = this;

            $('#' + _this.input_name + 'MediaTable').bootstrapTable('destroy').bootstrapTable({
                url: '/admin/media-selector/media-list',         //请求后台的URL（*）
                method: 'post',                      //请求方式（*）
                toolbar: '#' + _this.input_name + 'Toolbar',                //工具按钮用哪个容器
                dataField: 'data',
                striped: true,                      //是否显示行间隔色
                cache: false,                       //是否使用缓存，默认为true，所以一般情况下需要设置一下这个属性（*）
                pagination: true,                   //是否显示分页（*）
                sortable: true,                     //是否启用排序
                sortOrder: "desc",                   //排序方式
                queryParams: function (params) {
                    return {
                        _token: LA.token,
                        search: params.search,
                        page: (params.limit + params.offset) / params.limit,  //页码
                        pageSize: params.limit,   //页面大小
                        order: params.order,
                        orderName: params.sort,
                        keyword: $('input[name="' + _this.input_name + 'Keyword"]').val(),
                        type: $('#' + _this.input_name + 'MediaType').select2('val'),
                    }
                },
                sidePagination: "server",           //分页方式：client客户端分页，server服务端分页（*）
                pageNumber: 1,                       //初始化加载第一页，默认第一页
                pageSize: 20,                       //每页的记录行数（*）
                pageList: [10, 25, 50, 100],        //可供选择的每页的行数（*）
                search: false,                       //是否显示表格搜索，此搜索是客户端搜索，不会进服务端，所以，个人感觉意义不大
                showColumns: true,                  //是否显示所有的列
                showRefresh: true,                  //是否显示刷新按钮
                minimumCountColumns: 2,             //最少允许的列数
                clickToSelect: true,                //是否启用点击选中行
                // height: 20,                        //行高，如果没有设置height属性，表格自动根据记录条数觉得表格高度
                uniqueId: "id",                     //每一行的唯一标识，一般为主键列
                showToggle: false,                    //是否显示详细视图和列表视图的切换按钮
                cardView: false,                    //是否显示详细视图
                detailView: false,                   //是否显示父子表
                columns: [
                    {checkbox: true},
                    {field: 'id', title: "ID", width: '10%', sortable: true},
                    {
                        title: '预览', width: '150%',
                        formatter: function (value, row, index) {
                            var html = '';
                            var src = _this.root_path + row.path;
                            if (row.media_type === 'image')
                                html = '<img src="' + src + '" style="max-height:90px;max-width:120px" >';
                            else if (row.media_type === 'video')
                                html = '<video src="' + src + '" controls="controls" style="max-height:90px;max-width:120px"> </video>';
                            else if (row.media_type === 'audio')
                                html += '<i class="fa fa-file-audio-o fa-fw " style="font-size: 90px;"></i>';
                            else if (row.media_type === 'powerpoint')
                                html += '<i class="fa fa-file-word-o fa-fw " style="font-size: 90px;"></i>';
                            else if (row.media_type === 'code')
                                html += '<i class="fa fa-file-code-o fa-fw " style="font-size: 90px;"></i>';
                            else if (row.media_type === 'zip')
                                html += '<i class="fa fa-file-zip-o fa-fw " style="font-size: 90px;"></i>';
                            else if (row.media_type === 'text')
                                html += '<i class="fa fa-file-text-o fa-fw " style="font-size: 90px;"></i>';
                            else if (row.media_type === 'other')
                                html += '<i class="fa fa-file fa-fw " style="font-size: 90px;"></i>';


                            return html
                        }
                    },
                    {field: 'name', title: '名称', visible: false},
                    {field: 'media_type', title: '类型'},
                    {field: 'size', title: '大小'},
                    {field: 'file_ext', title: '后缀', width: '40%'},
                    {field: 'created_at', title: '创建时间', width: '150%', sortable: true},
                    {
                        field: 'operate',
                        title: '操作',
                        width: '40%',
                        events: {
                            'click .chooseone': function (e, value, row, index) {

                                if (_this.type != 'blend') {
                                    if (row.media_type != _this.type) {
                                        toastr.error('只能选择类型:' + _this.type + '的媒体', 400);
                                        return false;
                                    }
                                }

                                if (_this.max_file_count > 1 && (_this.getFileNumber() + 1) > _this.max_file_count) {
                                    toastr.error('媒体文件不能超过' + _this.max_file_count + '个', 400);
                                    return false;
                                }

                                _this.fileDisplay({data: row});
                                $('#' + _this.input_name + 'MediaSelectorModal').modal('hide')
                            },
                        },
                        formatter: _this.operateFormatter
                    },
                ],
                onClickRow: function (row) { // 点击每行进行函数的触发
                },
                onCheckAll: function (row) { // 点击全选框时触发的操作
                },
                onCheck: function (row) { // 点击每一个单选框时触发的操作
                },
                onUncheck: function (row) { // 取消每一个单选框时对应的操作
                },
                onUncheckAll: function (row) { // 取消所有
                }
            })

        };

        // 媒体列表操作
        MediaSelector.prototype.operateFormatter = function () {
            return [
                '<a href="javascript:;" class="btn btn-danger btn-xs chooseone"><i class="fa fa-check"></i> 选择</a>'
            ].join('');
        };

        // 拖动排序
        MediaSelector.prototype.sortable = function () {

            var _this = this;

            if (_this.sortables) {

                new Sortable($('#' + _this.input_name + 'MediaDisplay').get(0), {
                    animation: 150,
                    ghostClass: 'blue-background-class',
                    // 结束拖拽,对input值排序
                    onEnd: function (evt) {
                        _this.getInputMedia();
                        return false;
                    },
                });

            }
        };

        // 媒体上传
        MediaSelector.prototype.mediaUpload = function (data, whereToUpload) {

            var _this = this;

            var formData = new FormData();

            var files = $(data)[0].files;

            $.each(files, function (i, field) {

                formData.append("file", field);
                formData.append("type", _this.type);
                formData.append("move", _this.move);
                formData.append("_token", LA.token);

                $.ajax({
                    type: 'post', // 提交方式 get/post
                    url: '/admin/media-selector/media-upload', // 需要提交的 url
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function (event) {
                                var percent = Math.floor(event.loaded / event.total * 100);
                                if (whereToUpload == 'form')
                                    $('#' + _this.input_name + 'PercentForm').text(percent + "%");
                                else if (whereToUpload == 'modal')
                                    $('#' + _this.input_name + 'PercentModal').text(percent + "%");
                            }, false);
                        }
                        return xhr
                    },
                    success: function (data) {
                        if (data['code'] == 200) {
                            if (whereToUpload == 'form') {
                                _this.fileDisplay(data);
                                $('#' + _this.input_name + 'PercentForm').text('');
                            } else if (whereToUpload == 'modal') {
                                $('#' + _this.input_name + 'PercentModal').text('');
                            }

                            toastr.success('上传成功');
                        } else {
                            toastr.error(data['message']);
                        }
                    },
                    error: function (XmlHttpRequest, textStatus, errorThrown) {
                        if (whereToUpload == 'form')
                            $('#' + _this.input_name + 'PercentForm').text('');
                        else if (whereToUpload == 'modal')
                            $('#' + _this.input_name + 'PercentModal').text('');
                        toastr.error(XmlHttpRequest.responseJSON.message, XmlHttpRequest.status);
                    }
                });

                // 删除formData，防止重复累加
                formData.delete('file');
                formData.delete('type');
                formData.delete('move');
                formData.delete('_token');
                if (i == files.length - 1 && whereToUpload == 'modal')
                // 延迟刷新
                    setTimeout(function () {
                        $('#' + _this.input_name + 'MediaTable').bootstrapTable('refresh').bootstrapTable();
                    }, 500);
            });
        };

        // 媒体预览
        MediaSelector.prototype.fileDisplay = function (data) {

            var _this = this;

            var path = data.data.path;

            var root_path = _this.root_path + path;

            var file_name = data.data.name;

            if (_this.max_file_count === 1)
                $('.' + _this.input_name).val(path);
            else if (_this.max_file_count > 1)
                $('.' + _this.input_name).val() ? $('.' + _this.input_name).val($('.' + _this.input_name).val() + ',' + path) : $('.' + _this.input_name).val(path);

            var html = "";
            html += '<li>';
            html += '<a href="' + root_path + '" target="_blank" class="thumbnail" title="' + file_name + '">';
            if (data.data.media_type === 'image')
                html += '<img class="img-responsive" src="' + root_path + '">';
            else if (data.data.media_type === 'video')
                html += '<video class="img-responsive" controls src="' + root_path + '"></video>';
            else if (data.data.media_type === 'audio') {
                html += '<i class="fa fa-file-audio-o fa-fw img-responsive media-preview-fa"></i>';
                html += '<video src="' + root_path + '" style="display: none"></video>';
            } else if (data.data.media_type === 'powerpoint') {
                html += '<i class="fa fa-file-word-o fa-fw img-responsive media-preview-fa"></i>';
                html += '<video src="' + root_path + '" style="display: none"></video>';
            } else if (data.data.media_type === 'code') {
                html += '<i class="fa fa-file-code-o fa-fw img-responsive media-preview-fa"></i>';
                html += '<video src="' + root_path + '" style="display: none"></video>';
            } else if (data.data.media_type === 'zip') {
                html += '<i class="fa fa-file-zip-o fa-fw img-responsive media-preview-fa"></i>';
                html += '<video src="' + root_path + '" style="display: none"></video>';
            } else if (data.data.media_type === 'text') {
                html += '<i class="fa fa-file-text-o fa-fw img-responsive media-preview-fa"></i>';
                html += '<video src="' + root_path + '" style="display: none"></video>';
            } else if (data.data.media_type === 'other') {
                html += '<i class="fa fa-file fa-fw img-responsive media-preview-fa"></i>';
                html += '<video src="' + root_path + '" style="display: none"></video>';
            }
            html += '</a>';
            html += '<a href="javascript:;" class="btn btn-danger btn-xs btn-trash remove_shop_media">';
            html += '<i class="fa fa-trash"></i>';
            html += '</a>';
            html += '</li>';

            if (_this.max_file_count === 1) {
                $('#' + _this.input_name + 'MediaDisplay').html(html);
                // 删除
                $(".remove_shop_media").on('click', function () {
                    $(this).hide().parent().remove();
                    $('.' + _this.input_name).val('');
                    return false
                });
            } else if (_this.max_file_count > 1) {
                $('#' + _this.input_name + 'MediaDisplay').append(html);
                // 删除
                $(".remove_shop_media").on('click', function () {
                    $(this).hide().parent().remove();
                    _this.getInputMedia();

                    return false
                });
            }
        };

        // 获取预览区媒体值，重新组装
        MediaSelector.prototype.getInputMedia = function () {

            var _this = this;

            var src = '';

            // 循环获取属性下面的img/video src 值
            $.each($('#' + _this.input_name + 'MediaDisplay li a'), function (index, content) {

                $(content).html().replace(/<img.*?src="(.*?)"[^>]*>/ig, function (a, b) {
                    src += b + ',';
                });

                $(content).html().replace(/<video.*?src="(.*?)"[^>]*>/ig, function (a, b) {
                    src += b + ',';
                });

                $(content).html().replace(/<a.*?href="(.*?)"[^>]*>/ig, function (a, b) {
                    src += b + ',';
                });

            });
            var reg = new RegExp(_this.root_path, "g");//g,表示全部替换。

            var srcs = src.replace(reg, "");

            $('.' + _this.input_name).val(srcs.substring(0, srcs.length - 1));

        };

        // 获取预览区媒体数量
        MediaSelector.prototype.getFileNumber = function () {

            var _this = this;

            return $('#' + _this.input_name + 'MediaDisplay').find('li').length;

        };

        // 获取文件类型
        MediaSelector.prototype.getFileType = function (suffix) {

            // 获取类型结果
            var result = '';

            // 匹配图片
            var img_list = [
                'bmp', 'cgm', 'djv', 'djvu', 'gif', 'ico', 'ief', 'jp2', 'jpe', 'jpeg', 'jpg', 'mac', 'pbm', 'pct', 'pgm', 'pic', 'pict',
                'png', 'pnm', 'pnt', 'pntg', 'ppm', 'qti', 'qtif', 'ras', 'rgb', 'svg', 'tif', 'tiff', 'wbmp', 'xbm', 'xpm', 'xwd'
            ];

            // 匹配音频
            var audio_list = ['mp3', 'wav', 'flac', '3pg', 'aa', 'aac', 'ape', 'au', 'm4a', 'mpc', 'ogg'];

            // 匹配视频
            var video_list = ['mp4', 'rmvb', 'flv', 'mkv', 'avi', 'wmv', 'rm', 'asf', 'mpeg'];

            // 匹配文稿
            var powerpoint_list = [
                'doc', 'dot', 'docx', 'dotx', 'docm', 'dotm', 'xls', 'xlt', 'xla', 'xlsx', 'xltx', 'xlsm', 'xltm', 'xlam', 'xlsb',
                'ppt', 'pdf', 'pot', 'pps', 'ppa', 'pptx', 'potx', 'ppsx', 'ppam', 'pptm', 'potm', 'ppsm'
            ];

            // 匹配代码
            var code_list = ['php', 'js', 'java', 'python', 'ruby', 'go', 'c', 'cpp', 'sql', 'm', 'h', 'json', 'html', 'aspx'];

            // 匹配压缩包
            var zip_list = ['zip', 'tar', 'gz', 'rar', 'rpm'];

            // 匹配文本
            var text_list = ['txt', 'pac', 'log', 'md'];

            // 无后缀返回 false
            if (!suffix) {
                result = false;
                return result;
            }


            result = img_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'image';
                return result;
            }

            result = audio_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'audio';
                return result;
            }
            result = video_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'video';
                return result;
            }
            result = powerpoint_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'powerpoint';
                return result;
            }
            result = code_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'code';
                return result;
            }
            result = zip_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'zip';
                return result;
            }
            result = text_list.some(function (item) {
                return item == suffix;
            });
            if (result) {
                result = 'text';
                return result;
            }


            // 其他 文件类型
            result = 'other';
            return result;

        };

        window.MediaSelector = MediaSelector;
    }
)();

