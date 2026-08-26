<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\File;

use App\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class WipeCommand extends Command
{
    public function configure(): void
    {
        $this->setName('file:wipe');
        $this->setAliases(['wipe']);
        $this->setDescription('Wipe file');
        $this->addArgument('filename', InputArgument::REQUIRED, 'File to wipe');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will wipe the contents of the file. Proceed? (y/N) ', false);

        if ($helper->ask($input, $output, $question)) {
            file_put_contents($input->getArgument('filename'), '');
        }

        return self::SUCCESS;
    }
}
