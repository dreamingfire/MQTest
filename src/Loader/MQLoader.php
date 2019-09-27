<?php


namespace App\Loader;


use PhpAmqpLib\Connection\AMQPSocketConnection;

class MQLoader
{
    private static $_connection;

    public static function connect()
    {
        /**@var AMQPSocketConnection $connection */
        $connection = self::$_connection;
        if(empty($connection)) {
            try {
                self::$_connection = new AMQPSocketConnection(
                    $_ENV['MQ_HOST'] ?? "",
                    $_ENV['MQ_PORT'] ?? 5672,
                    $_ENV['MQ_USER'] ?? "",
                    $_ENV['MQ_PASSWORD'] ?? "",
                    '/' . ($_ENV['MQ_VHOST'] ?? ""),
                    $insist = false,
                    $login_method = 'AMQPLAIN',
                    $login_response = null,
                    $locale = 'en_US',
                    $_ENV['MQ_READ_TIMEOUT'] ?? 3,
                    $_ENV['MQ_KEEPALIVE'] ?? false,
                    $_ENV['MQ_WRITE_TIMEOUT'] ?? 3,
                    $_ENV['MQ_HEARTBEAR'] ?? 0,
                    $_ENV['MQ_CHANNEL_TIMEOUT'] ?? 0.0
                );
            } catch (\Exception $e) {
                die($e->getMessage()."\n");
            }
            self::$_connection->set_close_on_destruct(true);
        } else{
            $connection->reconnect();
        }
    }

    public static function close()
    {
        /**@var AMQPSocketConnection $connection */
        $connection = self::$_connection;
        if(!empty($connection) && $connection->isConnected()) {
            try {
                $connection->close();
            } catch (\Exception $e) {
                die($e->getMessage()."\n");
            }
            self::$_connection = null;
        }
    }

    public static function getConnection()
    {
        /**@var AMQPSocketConnection $connection */
        $connection = self::$_connection;
        return $connection;
    }
}