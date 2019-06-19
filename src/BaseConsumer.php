<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;

use BadFunctionCallException;
use const SIGUSR1;
use SomeBlackMagic\AMQP\Message\InputMessage;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;

/**
 * Class BaseConsumer
 * @package SomeBlackMagic\AMQP
 */
class BaseConsumer extends BaseAmqp
{

    /**
     * @var
     */
    protected $target;

    /**
     * @var int
     */
    protected $consumed = 0;

    /**
     * @var bool
     */
    protected $forceStop = false;

    /** @var int */
    protected $idleTimeout = 0;
    
    /** @var int */
    protected $idleTimeoutExitCode = 0;
    
    /**
     * @var
     */
    private $callback;
    
    /**
     * @var bool
     */
    private $inProgress;

    /**
     * @param $callback
     * @throws InvalidConfigException
     */
    public function consume($callback): void
    {
        $this->callback = $callback;
        $this->getChannel()
            ->basic_consume(
                $this->queueName,
                $this->consumerTag,
                false,
                false,
                false,
                false,
                [$this, 'processMessage']
            );
    }


    /**
     * @param AMQPMessage $message
     * @throws ErrorException
     */
    public function processMessage(AMQPMessage $message): void
    {
        $this->inProgress = true;

        $inputMessage = InputMessage::decodeMessage($message);
        call_user_func_array($this->callback, [$inputMessage]);
        $this->inProgress = false;
    }
    
    /**
     *
     */
    public function stopConsumer(): void
    {
        Yii::debug('Handle SIGTERM/SIGINT PCNTL signal.', __CLASS__);
        // Halt consumer if waiting for a new message from the queue
        $this->forceStop = true;
        if(!$this->inProgress) {
            $this->maybeStopConsumer();
        }
    }
    
    /**
     *
     */
    public function restartConsumer(): void
    {
        // TODO: Implement restarting of consumer
    }
    
    /**
     *
     */
    public function onSignal() : void
    {
        // TODO: Implement  functional
    }
    
    /**
     * @param bool $withoutSignals
     */
    public function registerShutdownHandler(bool $withoutSignals = false): void
    {

        if (defined('AMQP_WITHOUT_SIGNALS') === false) {
            define('AMQP_WITHOUT_SIGNALS', $withoutSignals);
        }

        if (!AMQP_WITHOUT_SIGNALS && extension_loaded('pcntl')) {
            if (!function_exists('pcntl_signal')) {
                throw new BadFunctionCallException("Function 'pcntl_signal' is referenced in the php.ini 'disable_functions' and can't be called.");
            }
            Yii::info('Register pcntl signals', __CLASS__);
            pcntl_signal(SIGTERM, [$this, 'stopConsumer']);
            pcntl_signal(SIGINT, [&$this, 'stopConsumer']);
            pcntl_signal(SIGHUP, [&$this, 'restartConsumer']);
            pcntl_signal(SIGUSR1, [&$this, 'onSignal']);
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidConfigException
     * @throws InvalidConfigException
     */
    public function start(): void
    {
        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait(null, true);
        }
    }

    /**
     *
     * @throws InvalidConfigException
     */
    public function stopConsuming(): void
    {
        Yii::info(__FUNCTION__, __CLASS__);
        $this->getChannel()->basic_cancel($this->consumerTag, false, true);
    }
    
    /**
     * Set exit code to be returned when there is a timeout exception
     *
     * @param int|null $idleTimeoutExitCode
     */
    public function setIdleTimeoutExitCode($idleTimeoutExitCode): void
    {
        $this->idleTimeoutExitCode = $idleTimeoutExitCode;
    }

    /**
     * @return int
     */
    public function getIdleTimeout(): int
    {
        return $this->idleTimeout;
    }
    
    /**
     * Get exit code to be returned when there is a timeout exception
     *
     * @return int|null
     */
    public function getIdleTimeoutExitCode(): ?int
    {
        return $this->idleTimeoutExitCode;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function maybeStopConsumer(): void
    {
        Yii::debug(__FUNCTION__, __CLASS__);
        if (extension_loaded('pcntl') && (defined('AMQP_WITHOUT_SIGNALS') ? !AMQP_WITHOUT_SIGNALS : true)) {
            if (!function_exists('pcntl_signal_dispatch')) {
                throw new BadFunctionCallException("Function 'pcntl_signal_dispatch' is referenced in the php.ini 'disable_functions' and can't be called.");
            }
            pcntl_signal_dispatch();
        }
        if ($this->forceStop || ($this->consumed === $this->target && $this->target > 0)) {
            $this->stopConsuming();
        }
    }

}
