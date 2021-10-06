<?php

namespace App\Command;

use App\Omdb\OmdbClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImdbCommand extends Command
{
    protected static $defaultName = 'app:imdb';
    protected static $defaultDescription = 'Add a short description for your command';
    private $omdbClient;

    public function __construct(OmdbClient $omdbClient)
    {
        parent::__construct(null);

        $this->omdbClient = $omdbClient;
    }

    protected function configure(): void
    {
        $this
            ->addOption('keyword', 'k', InputOption::VALUE_REQUIRED, 'Movies containing the given keyword')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // You are looking for movies containing "Sun"

        $keyword = $input->getOption('keyword');
        if (!$keyword) {
            $keyword = $io->ask('Which movie you are looking for?', 'Harry Potter', function($answer) {
                $answer = strtolower($answer);
                $blackList = ['fuck', 'shit'];
                if (in_array($answer, $blackList)) {
                    throw new \InvalidArgumentException('Your keyword is not allowed, please try again !');
                }

                return $answer;
            });
        }

        $movies = $this->omdbClient->requestAllBySearch($keyword);
        if ($output->isVerbose()) {
            dump($movies);
        }
        $io->title('You are looking for movies containing ' . $keyword);

        $rows = [];
        $io->progressStart(count($movies['Search']));
        foreach ($movies['Search'] as $movie) {
            usleep(100000);
            $io->progressAdvance();
            $rows[] = [$movie['Title'], $movie['Year'], 'https://www.imdb.com/title/'. $movie['imdbID'].'/'];
        }
        $output->write("/\r");
        //$io->progressFinish();

        $io->table(['TITLE', 'YEAR', 'URL'], $rows);

        //dump($keyword, $movies);

        return Command::SUCCESS;
    }
}
