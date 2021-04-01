<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="col-xs-12 {{$viewClass['field']}}">

        @include('admin::form.error')

        <div class="input-group">

            <input name="{{$name}}" class="form-control {{$class}}" placeholder="{{ $placeholder }}"
                   {!! $attributes !!} value="{{ old($column, $value) }}">

            <div class="input-group-btn input-group-append">

                <div class="btn btn-danger btn-file button-heights">
                    <i class="fa fa-upload"></i>
                    <span class="hidden-xs">上传</span>
                    <span id="{{$class}}PercentForm"></span>
                    <input type="file" class="avatar" @if($maxFileCount > 1) multiple
                           @endif id="{{$class}}MediaUploadForm">
                </div>

                <div class="btn btn-primary btn-file button-heights select-button"
                     id="{{$class}}OpenMediaSelectorModal">
                    <i class="fa fa-folder-open"></i>
                    <span class="hidden-xs">选择</span>
                </div>

            </div>

        </div>

        @include('admin::form.help-block')

        <ul class="row list-inline plupload-preview" id="{{$class}}MediaDisplay"></ul>

    </div>

</div>

<div class="modal" id="{{$class}}MediaSelectorModal" tabindex="-1" role="dialog"
     aria-labelledby="{{$class}}MediaSelectorModalLabel">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title" id="{{$class}}MediaSelectorModalLabel">选择</h4>

                <div id="{{$class}}MediaSelectorModalForm" class="form-horizontal" style="display: none">

                    <div class="form-group" style="margin-top:15px">

                        <label class="control-label col-sm-1">关键字</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="{{$class}}Keyword" placeholder="名称/后缀">
                        </div>

                        <label class="control-label col-sm-1">类型</label>
                        <div class="col-sm-3">
                            <select class="form-control" id="{{$class}}MediaType" name="type" tabindex="-1"
                                    aria-hidden="true" style="width: 100%">
                                <option></option>
                                @foreach($selectList as $option => $label)
                                    <option value="{{$option}}" {{$option==$type ? 'selected':''}}>{{$label}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4" style="text-align:left;">
                            <button type="button" id="{{$class}}Query" class="btn btn-info ">
                                <i class="fa fa-search"></i> 查询
                            </button>
                        </div>

                    </div>

                </div>

                <div id="{{$class}}Toolbar" class="toolbar">

                    <span style="position: relative;">

                        <label class="btn btn-success btn-sm">

                            <i class="fa fa-upload"></i> 上传

                            <span id="{{$class}}PercentModal"></span>

                            <input type="file" id="{{$class}}MediaUploadModal" multiple style="display: none;">

                         </label>

                    </span>

                    @if ($maxFileCount > 1)
                        <a class="btn btn-danger btn-sm" id="{{$class}}Choose"><i class="fa fa-check"></i> 选择</a>
                    @endif

                    <a class="btn btn-sm btn-dropbox" id="{{$class}}Screening"><i class="fa fa-filter"></i> 筛选</a>

                </div>

            </div>

            <div class="modal-body ">
                <div class="table-responsives panel-body">
                    <table class="table table-bordered  tb_departments" id="{{$class}}MediaTable">
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>