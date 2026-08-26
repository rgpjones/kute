<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Media;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VideoConvertCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('media:video:convert');
        $this->setDescription('Convert videos into MP4 format');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Directory containing videos to convert.');
        $this->addOption(
            'delete',
            'd',
            InputOption::VALUE_NONE,
            'If a duplicate of the target file is detected, delete it if the duration matches.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = $input->getArgument('directory');
        if (!file_exists($directory)) {
            $output->writeln('<error>Directory does not exist</error>');
            return self::FAILURE;
        }

        $convertFiles = [];

        $dir = Dir($directory);
        while (false !== ($entry = $dir->read())) {
            if (!is_dir($entry)) {
                $file = realpath($directory . DIRECTORY_SEPARATOR . $entry);
                $info = pathinfo($file);

                if ($info['extension'] !== 'mp4') {
                    $convertFiles[] = $file;
                }
            }
        }

        $filesToConvert = count($convertFiles);
        $output->writeln(sprintf("<info>Files to convert: %d</info>", $filesToConvert));

        foreach ($convertFiles as $i => $file) {
            $output->writeln(sprintf('Processing %d of %d', $i+1, $filesToConvert));
            $this->convertFile($file, $directory, $input, $output);
        }


        return self::SUCCESS;
    }

    private function convertFile(string $file, string $directory, InputInterface $input, OutputInterface $output): void
    {
        $info = pathinfo($file);
        $newFile = realpath($directory) . DIRECTORY_SEPARATOR . $info['filename'] . '.mp4';

        $output->writeln(sprintf(
            'Converting "<comment>%s</comment>" => "<info>%s</info>"',
            escapeshellarg($file),
            escapeshellarg($newFile)
        ));

        if (!file_exists($newFile)) {
            $tmpFile = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($newFile);
            exec(sprintf('ffmpeg -i %s %s', escapeshellarg($file), escapeshellarg($tmpFile)));
            $output->writeln('Done!');
            rename($tmpFile, $newFile);
            unlink($file);
        } else {
            $output->writeln("\t<fg=cyan>Target file exists...</>");
            $oldFileDuration = $this->getFileDuration($file);
            $newFileDuration = $this->getFileDuration($newFile);
            if ($oldFileDuration !== $newFileDuration) {
                $output->writeln(
                    sprintf("\t<error>File durations differ</error>: %d => %d", $oldFileDuration, $newFileDuration)
                );
                $output->writeln(
                    "\t  <error>The conversion process was probably interrupted. Delete the target file to try "
                    . "again.</error>"
                );
            } else {
                if ($input->getOption('delete')) {
                    unlink($file);
                    $output->writeln("\t<comment>Durations match. Original file deleted!</comment>");
                } else {
                    $output->writeln("\t<info>Durations match. Original file can probably be deleted.</info>");
                }
            }
        }

        $output->writeln('');
    }

    private function getFileDuration(string $file): int
    {
        $command = sprintf("ffmpeg -i %s 2>&1 | grep Duration | cut -d ' ' -f 4 | sed s/,//", escapeshellarg($file));
        $result = exec($command);

        list($hours, $minutes, $seconds) = explode(':', $result);

        return (intval($hours) * 60 * 60) + (intval($minutes) * 60) + intval($seconds);
    }
}
