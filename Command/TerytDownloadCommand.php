<?php

/**
 * (c) FSi sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\TerytDatabaseBundle\Command;

use FSi\Bundle\TerytDatabaseBundle\Teryt\DownloadPageParser;
use Guzzle\Common\Event;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class TerytDownloadCommand extends ContainerAwareCommand
{
    /**
     * @return string
     */
    abstract protected function getFileDownloadUrl();

    /**
     * @return int
     */
    abstract protected function getFileRoundedSize();

    /**
     * @return DownloadPageParser
     */
    protected function getTerytPageParser()
    {
        if (!isset($this->terytPageParser)) {
            $this->terytPageParser = new DownloadPageParser($this->getContainer()->get('fsi_teryt_db.http_client'));
        }

        return $this->terytPageParser;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $this->getDownloadTargetFolder($input);
        $request = $this->createDownloadHttpRequest($input, $target);

        $progressBar = new ProgressBar($output, 100);
        $progressBar->start();

        $request->getEventDispatcher()->addListener(
            'curl.callback.progress',
            $this->getDownloadProgressCallbackFunction($output, $progressBar)
        );

        $request->send();

        $progressBar->setProgress(100);
        $progressBar->finish();

        $output->writeln("");

        return 0;
    }

    protected function getDefaultTargetPath()
    {
        return $this->getContainer()->getParameter('kernel.root_dir') . '/teryt';
    }

    /**
     * @param InputInterface $input
     * @return mixed|string
     */
    private function getDownloadTargetFolder(InputInterface $input)
    {
        $target = $input->getArgument('target');
        if (!isset($target)) {
            $target = $this->getDefaultTargetPath();
        }

        if (!file_exists($target)) {
            mkdir($target);
            return $target;
        }

        return $target;
    }

    /**
     * @param InputInterface $input
     * @param $target
     * @return mixed
     */
    protected function createDownloadHttpRequest(InputInterface $input, $target)
    {
        $client = $this->getContainer()->get('fsi_teryt_db.http_client');

        $request = $client->get($this->getFileDownloadUrl(), null, array(
            'connect_timeout' => 10,
            'save_to' => sprintf('%s/%s.zip', $target, $input->getArgument('filename')),
        ));

        $request->getCurlOptions()->set('progress', true);

        return $request;
    }

    /**
     * @param OutputInterface $output
     * @param ProgressBar $progressBar
     *
     * @return callable
     */
    protected function getDownloadProgressCallbackFunction(OutputInterface $output, ProgressBar $progressBar)
    {
        $fileSize = $this->getFileRoundedSize();

        return function (Event $event) use ($output, $fileSize, $progressBar) {
            if (version_compare(curl_version()['version'], '7.32', '<=')) {
                $downloaded = $event['upload_size'];
            } else {
                $downloaded = $event['downloaded'];
            }

            if ($downloaded === 0 || $fileSize == 0) {
                return;
            }

            $percent = ($downloaded / $fileSize) * 100;

            if ($percent > 100) {
                return;
            }

            $progressBar->setProgress((int) $percent);
        };
    }
}
