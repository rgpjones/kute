<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Tools;

use App\Command\Command;
use Kachuru\Kute\Tools\TemplateProvider;
use Kachuru\Kute\Tools\TemplateRenderer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class InitialiseCommand extends Command
{
    public function __construct(
        private readonly TemplateProvider $provider,
        private readonly TemplateRenderer $renderer
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('tools:initialise');
        $this->setAliases(['init', 'tools:initialize']);
        $this->setDescription('Install RC files from templates');
    }

    /** @SuppressWarnings(PHPMD).Superglobals */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        foreach ($this->provider->getTemplates() as $template) {
            $output->writeln(sprintf('Initialising: <info>%s</info>', $template->getName()));

            $proceedQuestion = new ConfirmationQuestion('  Proceed? (Y/n) ', false);
            if (!$questionHelper->ask($input, $output, $proceedQuestion)) {
                continue;
            }

            $output->writeln(
                sprintf(
                    '  This will compile template "%s" and write it to "%s"',
                    $template->getFilename(),
                    $template->getTarget()
                )
            );

            $target = $template->getTarget();
            if (str_starts_with($target, '~')) {
                $target = str_replace('~', $_SERVER['HOME'], $target);
            }

            if (file_exists($target)) {
                $backupFile = sprintf('%s.%s', $target, date('YmdHis'));
                $createBackupQuestion = new ConfirmationQuestion(
                    sprintf(
                        '  The target file already exists. Do you want to create a backup [<info>%s</info>]? (Y/n) ',
                        $backupFile
                    ),
                    true
                );
                if (!$questionHelper->ask($input, $output, $createBackupQuestion)) {
                    continue;
                }

                copy($target, $backupFile);
            }

            $answers = [];
            foreach ($template->getQuestions() as $subject => $question) {
                $ask = new Question($question . ': ');
                $answers[$subject] = $questionHelper->ask($input, $output, $ask);
            }

            file_put_contents(
                $target,
                $this->renderer->render($template->getFilename(), $template->processAnswers($answers))
            );
        }

        return self::SUCCESS;
    }
}
