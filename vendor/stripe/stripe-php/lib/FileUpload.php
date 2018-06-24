<?php

namespace Stripe;

/**
 * Class FileUpload
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property string $filename
 * @property string $purpose
 * @property int $size
 * @property string $type
 * @property string $url
 *
 * @package Stripe
 */
class FileUpload extends ApiResource
{

    const OBJECT_NAME = "file_upload";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;

    public static function baseUrl()
    {
        return Stripe::$apiUploadBase;
    }

    public static function className()
    {
        return 'file';
    }
}
