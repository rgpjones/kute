<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Jwt;

use App\Command\Command;
use Firebase\JWT\JWT;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    public function configure(): void
    {
        $this->setName('jwt:generate');
        $this->setDescription('Generate a JWT token');

        // Header options
        $this->addOption('algorithm', 'a', InputOption::VALUE_REQUIRED, 'Algorithm to use', 'HS256');
        $this->addOption('key-id', 'k', InputOption::VALUE_REQUIRED, 'Key ID for the header');

        // Payload options
        $this->addOption(
            'payload-data',
            'p',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Payload data items: add as -p"<item>:<value>"'
        );
        $this->addOption('expiry', 'e', InputOption::VALUE_OPTIONAL, 'Expiry time of token', '+1 hour');

        // Signature options
        $this->addOption('secret', 's', InputOption::VALUE_REQUIRED, 'Shared secret to use');
        $this->addOption('encode-secret', 'x', InputOption::VALUE_OPTIONAL, 'Base64 encode the secret', false);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $algorithm = $input->getOption('algorithm');
        $secret = $input->getOption('secret');
        $payload = $this->payloadExtract($input->getOption('payload-data'));
        $expiry = $input->getOption('expiry');
        $keyId = $input->getOption('key-id');

        if ($input->getOption('encode-secret') == true) {
            $secret = JWT::urlsafeB64Encode($secret);
        }

        $payload['iat'] = time();
        $payload['exp'] = strtotime($expiry);

        $output->writeln(JWT::encode($payload, $secret, $algorithm, $keyId));

        return self::SUCCESS;
    }

    private function payloadExtract(?array $options): array
    {
        $result = [];

        foreach ($options as $option) {
            $result = array_merge_recursive($result, $this->flat2associate(explode(':', $option)));
        }

        return $result;
    }

    private function flat2associate(array $data): array
    {
        $result = [];

        $key = array_shift($data);

        if (count($data) > 1) {
            $result[$key] = $this->flat2associate($data);
        }

        if (count($data) == 2) {
            return $result;
        }

        $result[$key] = $data[0];

        return $result;
    }
}
