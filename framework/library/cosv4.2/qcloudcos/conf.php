<?php

namespace qcloudcos;

class Conf {
    // Cos php sdk version number.
    const VERSION = 'v4.2.2';
    const API_COSAPI_END_POINT = 'http://region.file.myqcloud.com/files/v2/';

    // Please refer to http://console.qcloud.com/cos to fetch your app_id, secret_id and secret_key.
    const APP_ID = '1251620152';
    const SECRET_ID = 'AKIDwoQkHlWcGhiSqkc8PLJXKKhmwxdE7F8m';
    const SECRET_KEY = 'Fne2khi69K7dwOWGPprdmGhadQScWz2r';

    /**
     * Get the User-Agent string to send to COS server.
     */
    public static function getUserAgent() {
        return 'cos-php-sdk-' . self::VERSION;
    }
}
