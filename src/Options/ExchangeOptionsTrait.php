<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP\Options;

use InvalidArgumentException;
use Yii;

/**
 * Trait ExchangeOptionsTrait
 * @package SomeBlackMagic\AMQP\Options
 */
trait ExchangeOptionsTrait
{
    
    /**
     * @var string
     */
    public $exchangeName;
    
    /**
     * @var string
     */
    public $exchangeType;
    
    /**
     * @var bool
     */
    public $exchangePassive = false;
    
    /**
     * @var bool
     */
    public $exchangeDurable = true;
    
    /**
     * @var bool
     */
    public $exchangeAutoDelete = false;
    
    /**
     * @var bool
     */
    public $exchangeInternal = false;
    
    /**
     * @var bool
     */
    public $exchangeNoWait = false;
    
    /**
     * @var null
     */
    public $exchangeArguments;
    
    /**
     * @var bool
     */
    public $exchangeTicket = false;
    
    /**
     * @var bool
     */
    public $exchangeDeclare = false;


    /**
     * @return array
     */
    protected function getExchangeOptionsOptions(): array
    {
        return [
            'name'        => $this->exchangeName,
            'type'        => $this->exchangeType,
            'passive'     => $this->exchangePassive,
            'durable'     => $this->exchangeDurable,
            'auto_delete' => $this->exchangeAutoDelete,
            'internal'    => $this->exchangeInternal,
            'nowait'      => $this->exchangeNoWait,
            'arguments'   => $this->exchangeArguments,
            'ticket'      => $this->exchangeTicket,
            'declare'     => $this->exchangeDeclare,
        ];
    }
    
    /**
     * @var bool
     */
    protected $exchangeDeclared = false;
    
    /**
     *
     */
    protected function exchangeDeclare(): void
    {
        
        if ($this->exchangeName === null) {
            throw new InvalidArgumentException('You must provide an exchange name');
        }
        
        if ($this->exchangeType === null) {
            throw new InvalidArgumentException('You must provide an exchange type');
        }
        
        if (!$this->exchangeDeclare) {
            $this->getChannel()
                ->exchange_declare(
                    $this->exchangeName,
                    $this->exchangeType,
                    $this->exchangePassive,
                    $this->exchangeDurable,
                    $this->exchangeAutoDelete,
                    $this->exchangeInternal,
                    $this->exchangeNoWait,
                    $this->exchangeArguments,
                    $this->exchangeTicket
                );
            
            $this->exchangeDeclared = true;
            Yii::debug('Declarated Exchange:'.$this->exchangeName.':'.$this->exchangeType, __CLASS__);
        }
    }
    
}
