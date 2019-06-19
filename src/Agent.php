<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;

use Yii;
use yii\base\BaseObject;

use yii\base\InvalidConfigException;

/**
 * Class Agent
 * @package SomeBlackMagic\AMQP
 */
class Agent extends BaseObject
{
    /**
     * @var string|array|Connection
     */
    public $connection = Connection::class;
    
    /**
     * @var string|array|BaseConsumer
     */
    public $producer = [];
    
    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if(is_array($this->connection) && !isset($this->connection['class'])) {
            $this->connection['class'] = Connection::class;
        }
        $this->connection = Yii::createObject($this->connection);
        
    }
}
