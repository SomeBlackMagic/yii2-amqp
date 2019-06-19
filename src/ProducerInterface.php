<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;

/**
 * Interface ProducerInterface
 * @package SomeBlackMagic\AMQP
 */
interface ProducerInterface
{
    /**
     * Publish a message
     *
     * @param string $msgBody
     * @param string $routingKey
     * @param array $additionalProperties
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = []);
}
