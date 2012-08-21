<?php

namespace AC\TranscodingBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AC\Component\Transcoding\Console\OutputSubscriber;

class TranscodeCommand extends Command
{
	protected function configure()
    {
        $this->setName("transcoding:transcode")
            ->setDescription("Transcode a file, given a preset.");

        $this->addArgument('inFile', InputArgument::REQUIRED, "String path to input file.");
        $this->addArgument('preset', InputArgument::REQUIRED, "Key of preset to use.");
        $this->addArgument('outFile', InputArgument::OPTIONAL, "String path to output file.  If not provided, will be determined automatically based on source file.", false);

        //boolean option flags
        $this->addOption('increment', 'i', InputOption::VALUE_NONE, "Numerically increment the output file path if it already exists.");
        $this->addOption('force', 'f', InputOption::VALUE_NONE, "Force transcoder to overwrite any pre-existing files if present.");
        $this->addOption('recurse', 'r', InputOption::VALUE_NONE, "Recursively create any needed directories during the transcode process.");
        $this->addOption('preserve', 'p', InputOption::VALUE_NONE, "Do not delete any created files on a failed transcode.");
    }
    
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inFile = new File($input->getArgument('inFile'));
        $presetKey = $input->getArgument('preset_key');
        $outputPath = $input->getArgument('outFile');

        //figure out conflict mode
        $conflictMode = ($input->getOption('increment')) ? Transcoder::ONCONFLICT_INCREMENT : Transcoder::ONCONFLICT_EXCEPTION;
        if ($input->getOption('force')) {
            $conflictMode = Transcoder::ONCONFLICT_DELETE;
        }

        //figure out dir mode
        $dirMode = ($input->getOption('recurse')) ? Transcoder::ONDIR_CREATE : Transcoder::ONDIR_EXCEPTION;

        //figure out fail mode
        $failMode = ($input->getOption('preserve')) ? Transcoder::ONFAIL_PRESERVE : Transcoder::ONFAIL_DELETE;

        //get transcoder and register output listeners
        $transcoder = $this->getContainer()->get('transcoder');
        $outputSubscriber = new OutputSubscriber;
        $outputSubscriber->setOutput($output);
        $outputSubscriber->setHelperSet($this->getHelperSet());
        $transcoder->addSubscriber($outputSubscriber);

        //check for verbose mode
        if ($input->getOption('verbose')) {
            $adapterKey = $transcoder->getPreset($presetKey)->getRequiredAdapter();
            $adapter = $transcoder->getAdapter($adapterKey);
            if ($adapter instanceof AbstractCliAdapter) {
                $adapter->setStreamBuffer();
            }
        }

        //run the transcode
        $newFile = $transcoder->transcodeWithPreset($inFile, $presetKey, $outputPath, $conflictMode, $dirMode, $failMode);

        return true;
    }
}
