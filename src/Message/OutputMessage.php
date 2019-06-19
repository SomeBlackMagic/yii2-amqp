<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP\Message;


use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;
use yii\base\ErrorException;

/**
 * Class OutputMessage
 * @package SomeBlackMagic\AMQP\Message
 */
class OutputMessage extends AbstractMessage
{
    /**
     * @return array
     */
    private function getMessageParams(): array
    {
        return [
            'content_type'        => $this->contentType,
            'content_encoding'    => $this->contentEncoding,
            'application_headers' => $this->applicationHeaders,
            'delivery_mode'       => $this->deliveryMode,
            'priority'            => $this->priority,
            'correlation_id'      => $this->correlationId,
            'reply_to'            => $this->replyTo,
            'expiration'          => $this->expiration,
            'message_id'          => $this->messageId,
            'timestamp'           => $this->timestamp,
            'type'                => $this->type,
            'user_id'             => $this->userId,
            'app_id'              => $this->appId,
            'cluster_id'          => $this->clusterId,
            //'headers'             => $this->headers
        ];
    }

    /**
     * @param $body
     * @param string $contentType
     * @return OutputMessage
     * @throws Exception
     */
    public static function buildMessage($body, string $contentType = self::CONTENT_TYPE_TEXT): self
    {
        
        $obj = new self();
        $obj->body = $body;
        $obj->contentType = $contentType;
        $obj->timestamp = time();
        $obj->messageId = Uuid::uuid4()->toString();
        return $obj;
    }
    
    
    /**
     * @param AMQPChannel $channel
     * @param string $exchange
     * @param string $routingKey
     * @throws ErrorException
     */
    public function send(AMQPChannel $channel, string $exchange, string $routingKey): void
    {
        $this->validateBeforeSend();
        $this->prepareToSend();
        $data = new AMQPMessage($this->body, $this->getMessageParams());
        $channel->basic_publish($data, $exchange, $routingKey);
    }
    
    /**
     * @throws ErrorException
     */
    private function validateBeforeSend(): void
    {
        if($this->contentType === self::CONTENT_TYPE_TEXT && !is_string($this->body)) {
            throw new ErrorException('Body is not a string');
        }
        
        if(!in_array($this->deliveryMode, [self::DELIVERY_MODE_NON_PERSISTENT, self::DELIVERY_MODE_PERSISTENT], true)) {
            throw new ErrorException('Delivery Mode is not support');
        }
        
    }
    
    /**
     * @throws ErrorException
     */
    private function prepareToSend(): void
    {
        switch ($this->contentType) {
            case self::CONTENT_TYPE_JSON:
                $this->body = json_encode($this->body);
                break;
            case self::CONTENT_TYPE_XML:
                $xml = new SimpleXMLElement('<root/>');
                array_walk_recursive($this->body, [$xml, 'addChild']);
                $this->body = $xml->asXML();
                break;
            case self::CONTENT_TYPE_OCTET_STREAM:
                throw new ErrorException('Not implemented TYPE_OCTET_STREAM');
                break;
            default:
                throw  new ErrorException('Not support content type');
            
        }
        $this->applicationHeaders = new AMQPTable($this->applicationHeaders);
        $this->contentEncoding = self::CONTENT_ENCODING_COMPRESS;
    }
    
    
    
}
