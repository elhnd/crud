<?php

namespace App\Command;

use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reload-questions',
    description: 'Reload question fixtures without erasing other data (statistics, sessions, etc.)',
)]
class ReloadQuestionsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Fixture group to load', 'questions')
            ->addOption('migrate-identifiers', null, InputOption::VALUE_NONE, 'Generate identifiers for existing questions that do not have one')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $group = $input->getOption('group');
        $migrateIdentifiers = $input->getOption('migrate-identifiers');

        if ($migrateIdentifiers) {
            $this->migrateExistingQuestions($io);
        }

        $io->section(sprintf('Loading fixtures from group "%s"...', $group));

        // Charger les fixtures avec --append
        $application = $this->getApplication();
        if (!$application) {
            $io->error('Application not available');
            return Command::FAILURE;
        }

        $fixturesCommand = $application->find('doctrine:fixtures:load');
        $fixturesInput = new ArrayInput([
            '--group' => [$group],
            '--append' => true,
        ]);
        $fixturesInput->setInteractive(false);

        $returnCode = $fixturesCommand->run($fixturesInput, $output);

        if ($returnCode === Command::SUCCESS) {
            $io->success('Fixtures have been reloaded successfully!');
            $io->note('Existing question IDs have been preserved.');
        } else {
            $io->error('Error loading fixtures');
        }

        return $returnCode;
    }

    /**
     * Generate identifiers for existing questions that do not have one
     */
    private function migrateExistingQuestions(SymfonyStyle $io): void
    {
        $io->section('Migrating identifiers for existing questions...');

        $questionRepo = $this->entityManager->getRepository(Question::class);
        $questions = $questionRepo->findBy(['identifier' => null]);

        if (empty($questions)) {
            $io->info('All questions already have an identifier.');
            return;
        }

        $count = 0;
        $duplicates = 0;
        $usedIdentifiers = [];

        // D'abord, récupérer tous les identifiants existants
        $existingQuestions = $questionRepo->createQueryBuilder('q')
            ->select('q.identifier')
            ->where('q.identifier IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        foreach ($existingQuestions as $eq) {
            $usedIdentifiers[$eq['identifier']] = true;
        }

        foreach ($questions as $question) {
            $question->generateIdentifier();
            $identifier = $question->getIdentifier();

            // Si l'identifiant existe déjà, ajouter un suffixe unique
            if (isset($usedIdentifiers[$identifier])) {
                $suffix = 1;
                while (isset($usedIdentifiers[$identifier . '_' . $suffix])) {
                    $suffix++;
                }
                $identifier = $identifier . '_' . $suffix;
                $question->setIdentifier($identifier);
                $duplicates++;
            }

            $usedIdentifiers[$identifier] = true;
            $count++;
        }

        $this->entityManager->flush();
        $io->success(sprintf('%d questions received an identifier (%d duplicates detected).', $count, $duplicates));
    }
}
