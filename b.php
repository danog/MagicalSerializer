<?php

require 'vendor/autoload.php';
class a extends \danog\SerializableVolatile
{
    public function ___construct()
    {
        var_dump('CONSTRUCTED a');
    }

    public function __wakeup()
    {
        var_dump('WOKE UP a');
    }
}
new a();
class b extends \danog\SerializableVolatile
{
}
$result = \danog\Serialization::unserialize(file_get_contents('test'));
var_dump($result);
file_put_contents('testb', \danog\Serialization::serialize($result));
