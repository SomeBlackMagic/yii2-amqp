<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;

use Exception;
use PhpAmqpLib\Connection\{
    AMQPLazyConnection, AMQPLazySocketConnection, AMQPSocketConnection, AMQPSSLConnection, AMQPStreamConnection
};
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;


/**
 * Class AmqpConnection
 * @package SomeBlackMagic\AMQP
 */
class Connection extends Component
{
    
    /**
     * @var AMQPStreamConnection|AMQPSSLConnection|AMQPSocketConnection|AMQPLazySocketConnection|AMQPLazyConnection
     */
    protected $connection;
    
    /**
     * /**
     * @var string
     */
    public $host = '127.0.0.1';
    
    /**
     * @var string
     */
    public $port = '5672';
    
    /**
     * @var string
     */
    public $user;
    
    /**
     * @var string
     */
    public $password;
    
    /**
     * @var string
     */
    public $vhost = '/';
    
    /**
     * @var int
     */
    public $heartbeats = 0;
    
    /**
     * @var string
     */
    public $locale = 'en_US';
    
    
    public $sslOptions = [];
    
    /**
     * @var string
     */
    public $connectionType = 'stream';
    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();
        if (empty($this->user)) {
            throw new Exception("Parameter 'user' was not set for AMQP connection.");
        }
        Yii::debug('Connecting to AMQP buss', __CLASS__);
        switch ($this->connectionType) {
            case 'stream':
                $this->createStreamConnection();
                break;
            case 'ssl':
                $this->createSSLConnection();
                break;
            case 'socket':
                $this->createSocketConnection();
                break;
            case 'lazySocket':
                $this->createLazySocketConnection();
                break;
            case 'Lazy':
                $this->createLazyConnection();
                break;
            default:
                throw new InvalidConfigException('Unknown connectionType');
        }
    }
    
    /**
     *
     */
    private function createStreamConnection(): void
    {
        $this->connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
            false, // insist
            'AMQPLAIN', // login_method
            null, // login_response
            $this->locale, // locale
            3.0, // connection_timeout
            3.0, // read_write_timeout
            null, // context
            false, // keep alive
            $this->heartbeats
        );
    }
    
    /**
     *
     */
    private function createSSLConnection(): void
    {
        $this->connection = new AMQPSSLConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
            $this->sslOptions
        );
    }
    
    /**
     *
     */
    private function createSocketConnection(): void
    {
        $this->connection = new AMQPSocketConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
            false, // insist
            'AMQPLAIN', // login_method
            null, // login_response
            $this->locale, // locale
            3.0, // connection_timeout
            false, // keep alive
            3.0, // read_write_timeout
            $this->heartbeats
        );
    }
    
    /**
     *
     */
    private function createLazySocketConnection(): void
    {
        $this->connection = new AMQPLazySocketConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
            false, // insist
            'AMQPLAIN', // login_method
            null, // login_response
            $this->locale, // locale
            3.0, // connection_timeout
            false, // keep alive
            3.0, // read_write_timeout
            $this->heartbeats
        );
    }
    
    /**
     *
     */
    private function createLazyConnection(): void
    {
        $this->connection = new AMQPLazyConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
            false, // insist
            'AMQPLAIN', // login_method
            null, // login_response
            $this->locale, // locale
            3.0, // connection_timeout
            3.0, // read_write_timeout
            null, // context
            false, // keep alive
            $this->heartbeats
        );
    }
    
    /**
     * @return AMQPLazyConnection|AMQPLazySocketConnection|AMQPSocketConnection|AMQPSSLConnection|AMQPStreamConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
}
