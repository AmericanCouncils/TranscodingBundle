<?php

namespace AC\TranscodingBundle\Console;
use AC\Component\Transcoding\File;
use AC\Component\Transcoding\Event\MessageEvent;
use AC\Component\Transcoding\Event\TranscodeEvent;
use AC\Component\Transcoding\Event\TranscodeEvents;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Pipes transcoder output to the command line by registering event listeners for all transcode events.
 *
 * @package TranscodingBundle
 * @author Evan Villemez
 */
class OutputSubscriber implements EventSubscriberInterface
{
    private $startTime;
    private $output;
    private $helperSet;

    public static function getSubscribedEvents()
    {
        return array(
            TranscodeEvents::MESSAGE => 'onMessage',
            TranscodeEvents::BEFORE => 'onTranscodeStart',
            TranscodeEvents::AFTER => 'onTranscodeComplete',
            TranscodeEvents::ERROR => 'onTranscodeFailure',
        );
    }

    /**
     * Write any messages received by an adapter
     */
    public function onMessage(MessageEvent $e)
    {
        $formatter = $this->getFormatter();
        $adapterKey = $e->getAdapter()->getKey();
        $level = $e->getLevel();
        $message = $e->getMessage();

        $match = '/\r\n?/';

        //check if the message has weird formatting before trying to format it (currently a hack to avoid segmentation faults)
        if (!preg_match($match, $message)) {
            $msg = sprintf(
                "%s (%s): %s",
                $formatter->formatBlock($adapterKey, 'info'),
                $formatter->formatBlock($level, 'comment'),
                $message
            );
        } else {
            $msg = sprintf(
                "%s (%s): %s",
                $adapterKey,
                $level,
                preg_replace('/\r\n?/', '', $message)
            );
        }

        $this->getOutput()->writeln($msg);
    }

    /**
     * Write to output that a process has started.
     */
    public function onTranscodeStart(TranscodeEvent $e)
    {
        $inpath = $e->getInputPath();
        $presetKey = $e->getPreset();

        $formatter = $this->getFormatter();
        $msg = sprintf(
            "Starting transcode of file %s with preset %s ...",
            $formatter->formatBlock($inpath, 'info'),
            $formatter->formatBlock($presetKey, 'info')
        );

        $this->getOutput()->writeln($msg);
        $this->startTime = microtime(true);
    }

    /**
     * Write to output that a process has completed.
     */
    public function onTranscodeComplete(TranscodeEvent $e)
    {
        $outpath = $e->getOutputPath();

        $totalTime = microtime(true) - $this->startTime;
        $formatter = $this->getFormatter();
        $msg = sprintf(
            "Transcode completed in %s seconds.",
            $formatter->formatBlock(round($totalTime, 4), 'info')
        );
        $this->getOutput()->writeln($msg);

        $msg = sprintf(
            "Created new file %s",
            $formatter->formatBlock($outpath, 'info')
        );
        $this->getOutput()->writeln($msg);
    }

    /**
     * Write to output that a process has failed.
     */
    public function onTranscodeFailure(TranscodeEvent $e)
    {
        $inpath = $e->getInputPath();
        $errorMsg = $e->getException()->getMessage();

        $formatter = $this->getFormatter();
        $msg = sprintf(
            "Transcode of %s failed!  Message: %s",
            $formatter->formatBlock($inpath, 'info'),
            $formatter->formatBlock($errorMsg, 'error')
        );

        $this->getOutput()->writeln($msg);
    }

    protected function getFormatter()
    {
        return $this->helperSet->get('formatter');
    }

    public function setHelperSet(HelperSet $set)
    {
        $this->helperSet = $set;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
