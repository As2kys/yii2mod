<?php

use app\models\NginxLog;
use yii\db\Migration;

class m250809_141519_create_table_nginx_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(NginxLog::tableName(), [
            'id'        => $this->primaryKey(),
            'ip'        => $this->string(30),
            'url'       => $this->string(255),
            'date'      => $this->string(30),
            'datetime'  => $this->string(30),
            'useragent' => $this->string(255),
            'os'        => $this->string(30),
            'browser'   => $this->string(30),
            'x64'       => $this->string(4),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(NginxLog::tableName());
    }
}