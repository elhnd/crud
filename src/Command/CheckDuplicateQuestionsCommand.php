<?php

namespace App\Command;

use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Input\InputOption;

#[AsCommand(
    name: 'app:check-duplicate-questions',
    description: 'Check for duplicate questions in the database',
)]
class CheckDuplicateQuestionsCommand extends Command
{
    public function __construct(
        private QuestionRepository $questionRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('delete', null, InputOption::VALUE_NONE, 'Delete duplicate questions (keeps the first by ID)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $delete = $input->getOption('delete');

        $io->title('Checking for duplicate questions');

        // Requête pour trouver les questions avec le même texte
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('q.text, COUNT(q.id) as count')
            ->from('App\Entity\Question', 'q')
            ->groupBy('q.text')
            ->having('COUNT(q.id) > 1')
            ->orderBy('count', 'DESC');

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            $io->success('No duplicate questions found.');
            return Command::SUCCESS;
        }

        $io->warning(sprintf('%d group(s) of duplicate questions found.', count($results)));

        $table = $io->createTable();
        $table->setHeaders(['Question text', 'Number of duplicates']);

        foreach ($results as $result) {
            $table->addRow([$result['text'], $result['count']]);
        }

        $table->render();

        if ($delete) {
            $io->section('Deleting duplicates');
            $totalDeleted = 0;

            foreach ($results as $result) {
                $text = $result['text'];
                $questions = $this->questionRepository->findBy(['text' => $text], ['id' => 'ASC']);
                
                // Garde la première (ID le plus bas), supprime les autres
                $keep = array_shift($questions);
                foreach ($questions as $question) {
                    $this->entityManager->remove($question);
                    $totalDeleted++;
                }
            }

            $this->entityManager->flush();
            $io->success(sprintf('%d duplicate questions deleted.', $totalDeleted));
        } else {
            $io->section('Duplicate details');
            foreach ($results as $result) {
                $text = $result['text'];
                $questions = $this->questionRepository->findBy(['text' => $text]);
                $ids = array_map(fn($q) => $q->getId(), $questions);
                $io->writeln(sprintf('Question "%s" : IDs %s', substr($text, 0, 50) . '...', implode(', ', $ids)));
            }

            $io->note('Use --delete to remove duplicates (keeps the first by ID).');
        }

        return Command::SUCCESS;
    }
}