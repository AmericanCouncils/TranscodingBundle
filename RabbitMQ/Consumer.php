<?php

namespace AC\TranscodingBundle\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use AC\Component\Transcoding\Transcoder;

/**
 * This consumer handles messages published to RabbitMQ for transcoding files asyncronously. The format of the message
 * body is:
 *
 *  array(
 *      'infile' => '/absolute/path/to/input/file',             //required
 *      'preset' => 'preset_key',                               //required
 *      'outfile' => '/path/to/output'                          //optional
 *      'conflictMode' => Transcoder::ONCONFLICT_EXCEPTION,     //optional
 *      'dirMode' => Transcoder::ONDIR_EXCEPTION,               //optional
 *      'failMode' => Transcoder::ONFAIL_DELETE                 //optional
 *  );
 *
 *
 *  These messages can be sent by publishing them via the `transcoding.rabbitmq.publisher` service:
 *
 *      $msg = array( // ... // );
 *      $this->container->get('transcoding.rabbitmq.publisher')->publish(serialize($msg));
 *
 * @package TranscodingBundle
 * @author Evan Villemez
 */
class Consumer implements ConsumerInterface
{
    protected $transcoder;
    protected $conflictMode;
    protected $dirMode;
    protected $failMode;
    
    /**
     * Constructor needs the container for retrieving the transcoding.
     *
     * @param ContainerInterface $container 
     */
	public function __construct(Transcoder $transcoder, $conflictMode = Transcoder::ONCONFLICT_EXCEPTION, $dirMode = Transcoder::ONDIR_EXCEPTION, $failMode = Transcoder::ONFAIL_DELETE)
    {
        $this->transcoder = $transcoder;
        $this->failMode = $failMode;
        $this->conflictMode = $conflictMode;
        $this->dirMode = $dirMode;
    }
    
    /**
     * {@inheritdoc}
     */
	public function execute(AMQPMessage $msg)
    {
        $info = $msg->body;
        
        if(!isset($info['infile']) || !isset($info['preset'])) {
            throw new \InvalidArgumentException("The [infile] and [preset] options must be set to run a transcode process.");
        }
        
        $outfile = (isset($info['outfile'])) ? $info['outfile'] : null;
        $conflictMode = (isset($info['conflictMode'])) ? $info['conflictMode'] : null;
        $dirMode = (isset($info['dirMode'])) ? $info['dirMode'] : null;
        $failMode = (isset($info['failMode'])) ? $info['failMode'] : null;            
        
        try {
            $transcoder = $this->container->get('transcoder');
            
            $newFile = $transcoder->transcodeWithPreset($info['infile'], $info['preset'], $outfile, $conflictMode, $dirMode, $failMode);
                        
        } catch (\Exception $e) {
            return false;
        }
    }
}
