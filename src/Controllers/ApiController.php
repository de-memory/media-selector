<?php

namespace Encore\MediaSelector\Controllers;

use App\Http\Controllers\Controller;
use Encore\MediaSelector\RestApi\Helpers\ApiResponse;

class ApiController extends Controller
{
    use ApiResponse;

    /**
     * @return mixed
     */
    public function userInfo()
    {
        return \Admin::user();

    }
}