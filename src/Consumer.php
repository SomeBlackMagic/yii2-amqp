<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP;


use DateTime;
use Exception;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class Consumer
 * @package SomeBlackMagic\AMQP
 */
class Consumer extends BaseConsumer
{

    private const TIMEOUT_TYPE_IDLE = 'idle';
    private const TIMEOUT_TYPE_GRACEFUL_MAX_EXECUTION = 'graceful-max-execution';
    
    /**
     * @var int $memoryLimit
     */
    protected $memoryLimit;
    
    /**
     * @var DateTime|null DateTime after which the consumer will gracefully exit. "Gracefully" means, that
     *      any currently running consumption will not be interrupted.
     */
    protected $gracefulMaxExecutionDateTime;
    
    /**
     * @var int Exit code used, when consumer is closed by the Graceful Max Execution Timeout feature.
     */
    protected $gracefulMaxExecutionTimeoutExitCode = 0;
    
    /**
     * Set the memory limit
     *
     * @param int $memoryLimit
     */
    public function setMemoryLimit($memoryLimit): void
    {
        $this->memoryLimit = $memoryLimit;
    }
    
    /**
     * Get the memory limit
     *
     * @return int
     */
    public function getMemoryLimit(): int
    {
        return $this->memoryLimit;
    }

    /**
     * Consume the message
     *
     * @return  int
     *
     * @throws  AMQPTimeoutException
     * @throws InvalidConfigException
     */
    public function startConsume(): int
    {

        while (count($this->getChannel()->callbacks)) {
            $this->maybeStopConsumer();
            /*
             * Be careful not to trigger ::wait() with 0 or less seconds, when
             * graceful max execution timeout is being used.
             */
            $waitTimeout = $this->chooseWaitTimeout();
            if (
                $waitTimeout['timeoutType'] === self::TIMEOUT_TYPE_GRACEFUL_MAX_EXECUTION
                && $waitTimeout['seconds'] < 1
            ) {
                return $this->gracefulMaxExecutionTimeoutExitCode;
            }
            if (!$this->forceStop) {
                try {
                    $this->getChannel()->wait(null, false, $waitTimeout['seconds']);
                } catch (AMQPTimeoutException $e) {
                    Yii::debug('Timeout', __CLASS__);
                    if (self::TIMEOUT_TYPE_GRACEFUL_MAX_EXECUTION === $waitTimeout['timeoutType']) {
                        return $this->gracefulMaxExecutionTimeoutExitCode;
                    }
                    if ($this->forceStop) {
                        if (null !== $this->getIdleTimeoutExitCode()) {
                            return $this->getIdleTimeoutExitCode();
                        }

                        throw $e;
                    }
                }
            }
        }
        Yii::info('Exit', __CLASS__);
        return 0;
    }

    /**
     * @param DateTime|null $dateTime
     */
    public function setGracefulMaxExecutionDateTime(DateTime $dateTime = null): void
    {
        $this->gracefulMaxExecutionDateTime = $dateTime;
    }

    /**
     * @param int $secondsInTheFuture
     * @throws Exception
     */
    public function setGracefulMaxExecutionDateTimeFromSecondsInTheFuture($secondsInTheFuture): void
    {
        $this->setGracefulMaxExecutionDateTime(new DateTime("+{$secondsInTheFuture} seconds"));
    }
    
    /**
     * @param int $exitCode
     */
    public function setGracefulMaxExecutionTimeoutExitCode($exitCode): void
    {
        $this->gracefulMaxExecutionTimeoutExitCode = $exitCode;
    }
    
    /**
     * @return DateTime|null
     */
    public function getGracefulMaxExecutionDateTime(): ?DateTime
    {
        return $this->gracefulMaxExecutionDateTime;
    }
    
    /**
     * @return int
     */
    public function getGracefulMaxExecutionTimeoutExitCode(): int
    {
        return $this->gracefulMaxExecutionTimeoutExitCode;
    }

    /**
     * Choose the timeout to use for the $this->getChannel()->wait() method.
     *
     * @return array Of structure
     *  {
     *      timeoutType: string; // one of self::TIMEOUT_TYPE_*
     *      seconds: int;
     *  }
     * @throws Exception
     */
    private function chooseWaitTimeout(): array
    {
        if ($this->gracefulMaxExecutionDateTime) {
            $allowedExecutionDateInterval = $this->gracefulMaxExecutionDateTime->diff(new DateTime());
            $allowedExecutionSeconds =  $allowedExecutionDateInterval->days * 86400
                + $allowedExecutionDateInterval->h * 3600
                + $allowedExecutionDateInterval->i * 60
                + $allowedExecutionDateInterval->s;
            if (!$allowedExecutionDateInterval->invert) {
                $allowedExecutionSeconds *= -1;
            }
            /*
             * Respect the idle timeout if it's set and if it's less than
             * the remaining allowed execution.
             */
            if (
                $this->getIdleTimeout()
                && $this->getIdleTimeout() < $allowedExecutionSeconds
            ) {
                return [
                    'timeoutType' => self::TIMEOUT_TYPE_IDLE,
                    'seconds' => $this->getIdleTimeout(),
                ];
            }
            return [
                'timeoutType' => self::TIMEOUT_TYPE_GRACEFUL_MAX_EXECUTION,
                'seconds' => $allowedExecutionSeconds,
            ];
        }
        return [
            'timeoutType' => self::TIMEOUT_TYPE_IDLE,
            'seconds' => $this->getIdleTimeout(),
        ];
    }


}
