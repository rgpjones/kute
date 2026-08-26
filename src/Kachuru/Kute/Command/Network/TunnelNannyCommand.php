<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Network;

use App\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TunnelNannyCommand extends Command
{
    private const SSH_COMMAND = 'ssh -fND %d %s';

    public function configure(): void
    {
        $this->setName('network:tunnel:nanny');
        $this->setDescription('Set up and monitor an SSH tunnel');
        $this->addArgument('host', InputArgument::REQUIRED, 'The host to establish connection to');
        $this->addArgument('port', InputArgument::REQUIRED, 'The port to connect to');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $sshCommand = $this->getSshCommand($input);

        // @phpstan-ignore-next-line
        while (true) {
            $result = [];
            exec(sprintf('ps aux|grep "%s"|grep -v "grep"', $sshCommand), $result);

            if (array_filter(
                $result,
                function ($entry) use ($sshCommand) {
                    return strstr($entry, $sshCommand);
                }
            )) {
                $this->output($output, 'Tunnel active');
            } else {
                $this->output($output, 'Tunnel not active...');
                $this->connect($sshCommand);
                $this->output($output, '... connected');
                continue;
            }

            sleep(300 - (time() % 300));
        }
    }

    private function getSshCommand(InputInterface $input): string
    {
        return sprintf(self::SSH_COMMAND, (int) $input->getArgument('port'), (string) $input->getArgument('host'));
    }

    private function output(OutputInterface $output, $message)
    {
        $output->writeln(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
    }

    /** @SuppressWarnings(PHPMD).ExitExpression */
    private function connect(string $sshCommand): array
    {
        $result = [];
        exec($sshCommand, $result);
        if (!empty($result)) {
            die(print_r($result, true));
        }
        return $result;
    }
}
