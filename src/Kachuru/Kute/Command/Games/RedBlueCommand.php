<?php

declare(strict_types=1);

namespace Kachuru\Kute\Command\Games;

use App\Command\Command;
use Kachuru\RedBlue\Game;
use Kachuru\RedBlue\Selection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RedBlueCommand extends Command
{
    private const BLUE_CIRCLE = "\033[34m⬤\033[0m";
    private const RED_CIRCLE = "\033[31m⬤\033[0m";

    public function __construct(
        private readonly Game $game
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('games:red-blue');
        $this->setDescription('AI for the Blue/Red game');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->game->setup();
        $results = $this->game->play();

        $style = new SymfonyStyle($input, $output);

        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['Round', '1', '2', '3', '4']);
        $table->setColumnWidth(1, 3);
        $table->setColumnWidth(2, 3);
        $table->setColumnWidth(3, 3);
        $table->setColumnWidth(4, 3);

        foreach ($results as $round => $result) {
            $table->addRow([
                $round,
                $this->formatResult($result[1]),
                $this->formatResult($result[2]),
                $this->formatResult($result[3]),
                $this->formatResult($result[4]),
            ]);
        }

        $table->render();

        return 1;
    }

    private function formatResult(Selection $selection): string
    {
        return match ($selection) {
            Selection::BLUE => self::BLUE_CIRCLE,
            Selection::RED => self::RED_CIRCLE
        };
    }
}