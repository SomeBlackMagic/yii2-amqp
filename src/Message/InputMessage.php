<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Yii;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;


/**
 * Class InputMessage
 * @package SomeBlackMagic\AMQP
 */
class InputMessage extends AbstractMessage
{
    
    /**
     * @var
     */
    private $isRedelivery;
    
    private $exchange;

    /**
     * @var string
     */
    private $deliveryTag;
    
    /**
     * @var AMQPChannel
     */
    private $deliveryChannel;
    
    /**
     * @param AMQPMessage $message
     * @return InputMessage
     * @throws ErrorException
     */
    public static function decodeMessage(AMQPMessage $message): self
    {
        $obj = new self();
        $obj->setIncomingMessage($message);
        return $obj;
    }
    
    
    /**
     * @param AMQPMessage $message
     * @throws ErrorException
     */
    public function setIncomingMessage(AMQPMessage $message): void
    {
        if ($this->message !== null) {
            throw new ErrorException('Message is already set');
        }
        $this->message = $message;
        $this->parseAfterConsume();
    }

    /**
     * @throws ErrorException
     */
    private function parseAfterConsume(): void
    {
        $applicationHeaders = $this->message->get_properties();
        $this->applicationHeaders = ArrayHelper::getValue(
            $applicationHeaders,
            'application_headers',
            new AMQPTable()
        )->getNativeData();
        $this->appId = ArrayHelper::getValue($applicationHeaders, 'app_id');
        $this->clusterId = ArrayHelper::getValue($applicationHeaders, 'cluster_id');
        $this->contentEncoding = ArrayHelper::getValue($applicationHeaders, 'content_encoding');
        $this->contentType = ArrayHelper::getValue($applicationHeaders, 'content_type');
        $this->correlationId = ArrayHelper::getValue($applicationHeaders, 'correlation_id');
        $this->deliveryMode = ArrayHelper::getValue($applicationHeaders, 'delivery_mode');
        $this->expiration = ArrayHelper::getValue($applicationHeaders, 'expiration');
        $this->messageId = ArrayHelper::getValue($applicationHeaders, 'message_id');
        $this->priority = ArrayHelper::getValue($applicationHeaders, 'priority');
        $this->replyTo = ArrayHelper::getValue($applicationHeaders, 'reply_to');
        $this->timestamp = ArrayHelper::getValue($applicationHeaders, 'timestamp');
        $this->body = $this->decodeContent($this->message->getBody(), $this->contentType);
        
        $this->deliveryChannel = $this->message->delivery_info['channel'];
        $this->deliveryTag = $this->message->delivery_info['delivery_tag'];
        $this->isRedelivery = $this->message->delivery_info['redelivered'];
        $this->exchange = $this->message->delivery_info['exchange'];
        
        
    }

    /**
     * @param $body
     * @param $contentType
     * @return mixed
     * @throws ErrorException
     */
    private function decodeContent($body, $contentType)
    {
        switch ($contentType) {
            case self::CONTENT_TYPE_JSON:
                return json_decode($body, true);
                break;
            case self::CONTENT_TYPE_XML:
                throw new ErrorException('Not implemented');
                break;
            case self::CONTENT_TYPE_OCTET_STREAM:
                throw new ErrorException('Not implemented TYPE_OCTET_STREAM');
                break;
            default:
                throw  new ErrorException('Not support content type');
        }
        
    }

    /**
     *
     */
    public function acknowledge(): void
    {
        Yii::debug('Acknowledge message.', __CLASS__);
        $this->deliveryChannel->basic_ack($this->deliveryTag);
    }

    /**
     *
     */
    public function negativeAcknowledge(): void
    {
        Yii::debug('Negative Acknowledge message.', __CLASS__);
        $this->deliveryChannel->basic_nack($this->deliveryTag);
    }

    /**
     * @param $requeue
     */
    public function reject($requeue): void
    {
        Yii::debug('Reject message.', __CLASS__);
        $this->deliveryChannel->basic_reject($this->deliveryTag, $requeue);
    }

    /**
     * @param bool $noWait
     * @param bool $noReturn
     */
    public function cancel($noWait = false, $noReturn = false): void
    {
        Yii::debug('Cancel message.', __CLASS__);
        $this->deliveryChannel->basic_cancel($this->deliveryTag, $noWait, $noReturn);
    }
}
