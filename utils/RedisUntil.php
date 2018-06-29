<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;

use Predis\Client;

class RedisUntil extends BaseUntil
{
    private $client;

    public function __construct($host='127.0.0.1', $port=6379)
    {
        \Predis\Autoloader::register();
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => $host,
            'port'   => $port,
        ]);
    }

    public function set($key, $value, $timeout=24*60*60)
    {
        if (empty($key) || empty($value)) {
            return false;
        }
        return $this->client->setex($key, $timeout, $value);
    }

    public function get($key)
    {
        if (empty($key)) {
            return false;
        }
        return $this->client->get($key);
    }

    public function incr($key)
    {
        if (empty($key)) {
            return false;
        }
        return $this->client->incr($key);
    }

    public function incrby($key, $number)
    {
        if (empty($key)) {
            return false;
        }
        return $this->client->incrby($key, $number);
    }

    public function decr($key)
    {
        if (empty($key)) {
            return false;
        }
        return $this->client->incr($key);
    }

    public function decrby($key, $number)
    {
        if (empty($key)) {
            return false;
        }
        return $this->client->decrby($key, $number);
    }

    public function delete($key)
    {
        if (empty($key)) {
            return false;
        }
        if (!is_array($key)) {
            $key = [$key];
        }
        return $this->client->del($key);
    }
}
?>
