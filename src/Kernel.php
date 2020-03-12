<?php

namespace Pagekit\News;

use Pagekit\Application as App;
use Pagekit\News\Helper\WebpHelper;

class Kernel
{

    const PATHTEMP = 'tmp/temp/news';

    public static function webpConvert(string $image = '')
    {
        if (!$image) {
            return $image;
        }
        $webphelper = new WebpHelper();
        if (!App::config('news')->get('image.autoConvertWebp')) {
            return $image;
        }

        $pattern = '/^.+\/(.+).(jpg|png|jpeg)$/';
        preg_match_all($pattern, $image, $result);
        $tempImage = [
            'origin' => $result[0][0],
            'name' => $result[1][0],
            'extension' => $result[2][0]
        ];

        if (!$imageReturn = $webphelper->exists($tempImage['name'])) {
            if (in_array($tempImage['extension'], ['jpg', 'png', 'jpeg'])) {
                $imageReturn = $webphelper->convert($tempImage);
                return $imageReturn;
            } else {
                //Geri Dönecek
            }
        }
        return $imageReturn;
    }
    /**
     * create secret slug strings
     * Kernel::secretString
     */
    public static function secretString(string $strin = null)
    {
        $secret = APP::system()->config["secret"];
        $key = mt_rand();
        $hash = hash_hmac("sha1", $secret . mt_rand() . time(), $key, true);
        $token = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
        return $token;
    }
}
