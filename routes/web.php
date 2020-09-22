<?php

Route::post('media-selector/media-list', \Encore\MediaSelector\Controllers\MediaSelectorController::class . '@getMediaList');

Route::post('media-selector/media-upload', \Encore\MediaSelector\Controllers\MediaSelectorController::class . '@upload');