<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;

use SomeBlackMagic\AMQP\Message\OutputMessage;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;

/**
 * Class Producer
 * @package SomeBlackMagic\AMQP
 */
class Producer extends BaseAmqp
{

    /**
     * @param OutputMessage $message
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function publishMessage(OutputMessage $message): void
    {
        $channel = $this->getChannel();
        $message->send($channel, $this->exchangeName, $this->routingKey);
    }
}
