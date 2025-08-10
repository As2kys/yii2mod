<?php

namespace app\models;

class NginxLog extends \yii\db\ActiveRecord
{
    public $id;
    public $ip;
    public $url;
    public $date;
    public $datetime;
    public $useragent;
    public $os;
    public $x64;
    public $browser;

    // or private ?
    public static $browsers = [
        'Edge'      => 'Edge',
        'Chrome'    => 'Chrome',
        'Firefox'   => 'Firefox',
        'Googlebot' => 'Googlebot',
        'MSIE'      => 'Internet Explorer',
        'Opera'     => 'Opera',
        'Safari'    => 'Safari',
    ];

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
}