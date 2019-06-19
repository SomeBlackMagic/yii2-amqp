<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use SomeBlackMagic\AMQP\Options\{
    ExchangeOptionsTrait, QueueOptionsTrait
};
use PhpAmqpLib\Channel\AMQPChannel;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class BaseAmqp
 * @package SomeBlackMagic\AMQP
 */
class BaseAmqp extends BaseObject
{
    use ExchangeOptionsTrait;
    use QueueOptionsTrait;
    
    /**
     * @var AMQPStreamConnection
     */
    protected $connection;
    
    /**
     * @var AMQPChannel
     */
    private $channel;
    
    /**
     * @var string|null
     */
    protected $consumerTag;
    
    /**
     * @var string
     */
    public $routingKey;
    
    //--------------------------------------------------------------
    
    /**
     *
     */
    public function setup(): void
    {
        if($this->consumerTag === null) {
            $this->consumerTag = sprintf('PHPPROCESS_%s_%s', gethostname(), getmypid());
        }
        $this->exchangeDeclare();
        $this->queueDeclare();
    }
    
    /**
     * @param AMQPChannel $channel
     */
    public function setChannel(AMQPChannel $channel): void
    {
        $this->channel = $channel;
    }
    
    /**
     * @return AMQPChannel
     * @throws InvalidConfigException
     */
    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            throw new InvalidConfigException('Channel is not configured');
        }
        return $this->channel;
    }
    
}
