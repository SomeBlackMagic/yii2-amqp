<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP\Options;

use Yii;

/**
 * Trait QueueOptionsTrait
 * @package SomeBlackMagic\AMQP\Options
 */
trait QueueOptionsTrait
{
    /**
     * @var null
     */
    public $queueName;
    
    /**
     * @var null
     */
    public $queuePassive;
    
    /**
     * @var null
     */
    public $queueDurable;
    
    /**
     * @var null
     */
    public $queueExclusive;
    
    /**
     * @var null
     */
    public $queueAutoDelete;
    
    /**
     * @var null
     */
    public $queueNoWait;
    
    /**
     * @var null
     */
    public $queueArguments;
    
    /**
     * @var null
     */
    public $queueTicket;
    
    public $queueRoutingKeys;

    /**
     * @return array
     */
    protected function getQueueOptions(): array
    {
        return [
            'name'        => $this->queueName,
            'passive'     => $this->queuePassive,
            'durable'     => $this->queueDurable,
            'exclusive'   => $this->queueExclusive,
            'auto_delete' => $this->queueAutoDelete,
            'nowait'      => $this->queueNoWait,
            'arguments'   => $this->queueArguments,
            'ticket'      => $this->queueTicket
        ];
    }
    
    
    /**
     * @var bool
     */
    protected $queueDeclared = false;
    
    /**
     *
     */
    protected function queueDeclare(): void
    {
        if (null !== $this->queueName) {
            [$queueName, ,] = $this->getChannel()
                ->queue_declare(
                    $this->queueName,
                    $this->queuePassive,
                    $this->queueDurable,
                    $this->queueExclusive,
                    $this->queueAutoDelete,
                    $this->queueNoWait,
                    $this->queueArguments,
                    $this->queueTicket
                );
            
            if (count($this->queueRoutingKeys) > 0) {
                foreach ($this->queueRoutingKeys as $routingKey) {
                    $this->getChannel()->queue_bind($queueName, $this->exchangeName, $routingKey);
                }
            } else {
                $this->getChannel()->queue_bind($queueName, $this->exchangeName, $this->routingKey);
            }
            
            $this->queueDeclared = true;
            Yii::debug('Declarated Queue: ' . $this->queueName , __CLASS__);
        }
    }
}
