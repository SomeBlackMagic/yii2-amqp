<?php
declare(strict_types=1);

namespace SomeBlackMagic\AMQP\Message;

use PhpAmqpLib\Message\AMQPMessage;
use yii\base\BaseObject;

/**
 * Class Message
 * @package SomeBlackMagic\AMQP
 */
abstract class AbstractMessage extends BaseObject
{
    /**
     * @var static
     */
    public $contentType;

    protected const CONTENT_TYPE_TEXT = 'text/plain';
    protected const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';
    protected const CONTENT_TYPE_JSON = 'application/json';
    protected const CONTENT_TYPE_XML = 'application/xml';
    
    /**
     * @var string
     */
    public $contentEncoding;
    
    /**
     * A format using the Lempel-Ziv coding (LZ77), with a 32-bit CRC.
     * This is originally the format of the UNIX gzip program.
     */
    const CONTENT_ENCODING_GZIP = 'gzip';
    
    /**
     * A format using the Lempel-Ziv-Welch (LZW) algorithm.
     * The value name was taken from the UNIX compress program, which implemented this algorithm.
     */
    const CONTENT_ENCODING_COMPRESS = 'compress';
    
    /**
     * Using the zlib structure (defined in RFC 1950),
     * with the deflate compression algorithm (defined in RFC 1951).
     */
    const CONTENT_ENCODING_DEFLATE = 'deflate';
    
    /**
     * Indicates the identity function (i.e. no compression, nor modification).
     * This token, except if explicitly specified, is always deemed acceptable.
     */
    const CONTENT_ENCODING_IDENTIFY = 'identity';
    
    /**
     * A format using the Brotli algorithm.
     */
    const CONTENT_ENCODING_BR = 'br';
    
    /**
     * @var
     */
    public $applicationHeaders;
    
    /**
     * @var
     */
    public $deliveryMode;
    
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    
    const DELIVERY_MODE_PERSISTENT = 2;
    
    /**
     * @var
     */
    public $priority;
    
    /**
     * @var
     */
    public $correlationId;
    
    /**
     * @var
     */
    public $replyTo = 12341;
    
    /**
     * @var
     */
    public $expiration;
    
    /**
     * @var
     */
    public $messageId = 141234;
    
    /**
     * @var
     */
    public $timestamp;
    
    /**
     * @var
     */
    public $type;
    
    /**
     * @var
     */
    public $userId;
    
    /**
     * @var
     */
    public $appId;
    
    /**
     * @var
     */
    public $clusterId;
    
    /**
     * @var array
     */
    public $headers = [];
    
    /**
     * @var
     */
    public $body;
    
    /**
     * @var AMQPMessage
     */
    protected  $message;
    

}
