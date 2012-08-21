<?php

namespace AC\TranscodingBundle\Notification;

use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use AC\Component\Transcoding\Transcoder;

/**
 * Transcode notification consumer for SonataNotificationBundle.  Format of the message should be:
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
     * Constructor needs transcoder, and optional default conflict/fail modes to appy to all transcode
     * processes, if not set in the consumer message.
     *
     * @param Transcoder $transcoder 
     * @param int $conflictMode 
     * @param int $dirMode 
     * @param int $failMode 
     */
    public function __construct(Transcoder $transcoder, $conflictMode = Transcoder::ONCONFLICT_EXCEPTION, $dirMode = Transcoder::ONDIR_EXCEPTION, $failMode = Transcoder::ONFAIL_DELETE)
    {
        $this->transcoder = $transcoder;
        $this->failMode = $failMode;
        $this->conflictMode = $conflictMode;
        $this->dirMode = $dirMode;
    }
    
    /**
     * Handle a transcode message.
     *
     * @param ConsumerEvent $e 
     */
    public function process(ConsumerEvent $e)
    {
        $message = $e->getMessage();
        
        if (!$infile = $message->getValue('infile')) {
            throw new \InvalidArgumentException("No input file specified.");
        }
        
        if (!$preset = $message->getValue('preset')) {
            throw new \InvalidArgumentException("No preset specified.");
        }
        
        //get optional params
        $outfile = $message->getValue('outfile', false);
        $conflictMode = $message->getValue('conflictMode', $this->conflictMode);
        $dirMode = $message->getValue('dirMode', $this->dirMode);
        $failMode = $message->getValue('failMode', $this->failMode);
        
        $this->transcoder->transcodeFileWithPreset($infile, $preset, $outfile, $conflictMode, $dirMode, $failMode);
    }
}
