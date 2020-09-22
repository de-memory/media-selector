<?php

namespace Encore\MediaSelector\Controllers;

use Encore\MediaSelector\RestApi\Helpers\ResourcesMedia;
use Encore\MediaSelector\RestApi\Services\MediaService;
use Illuminate\Http\Request;
use Validator;

class MediaSelectorController extends ApiController
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getMediaList(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'keyword' => 'nullable',
            'order' => 'nullable',
            'orderName' => 'nullable',
            'page' => 'required|numeric',
            'pageSize' => 'required|numeric',
            'type' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $data = $this->mediaService->getMediaList(
            $this->userInfo()->id,
            $request->get('keyword'),
            $request->get('order', 'desc'),
            $request->get('orderName', 'id'),
            $request->get('pageSize', '25'),
            $request->get('type')
        );

        return $data;
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
            'type' => 'required',
            'move' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $media_obj = $this->mediaService->upload(
            $this->userInfo()->id,
            $request->file('file'),
            $request->get('type'),
            json_decode($request->get('move'))
        );

        $data = ResourcesMedia::make($media_obj);

        return $this->success($data);
    }
}