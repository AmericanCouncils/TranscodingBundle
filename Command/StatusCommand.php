<?php

namespace AC\TranscodingBundle\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AC\Component\Transcoding\Transcoder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class StatusCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName("transcoder:status")->setDescription("Show config, status of all available adapters, and list usable presets.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');

        //show adapters
        $adapters = $this->getContainer()->get('transcoder')->getAdapters();
        $output->writeln("Adapter status:");
        if (empty($adapters)) {
            $output->writeln($formatter->formatBlock("No available adapters.", 'info'));
        } else {
            foreach ($adapters as $adapter) {
                $msg = $adapter->verify() ? "Verified." : $adapter->getVerificationError();
                $msg = $adapter->verify() ? $formatter->formatBlock($msg, 'info') : $formatter->formatBlock($msg, 'error');
                $output->writeln($formatter->formatBlock($adapter->getName().":      ", 'comment').$msg);
            }
        }

        //show presets
        $output->writeln('');
        $output->writeln("Usable Presets: ");

        $presets = $this->getContainer()->get('transcoder')->getPresets();
        $usablePresets = array();
        foreach ($presets as $preset) {
            if ($this->getContainer()->get('transcoder')->getAdapter($preset->getRequiredAdapter())->verify()) {
                $usablePresets[] = $preset;
            }
        }

        if (empty($usablePresets)) {
            $output->writeln($formatter->formatBlock("No usable presets.", 'info'));
        } else {
            foreach ($usablePresets as $preset) {
                $output->writeln($formatter->formatBlock($preset->getName()." (".$preset->getKey().")", 'comment').": ".$formatter->formatBlock($preset->getDescription(), 'info'));
            }
        }

        return true;
    }
}
