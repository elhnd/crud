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
    description: 'Recharge les fixtures de questions sans effacer les autres données (statistiques, sessions, etc.)',
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
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Groupe de fixtures à charger', 'questions')
            ->addOption('migrate-identifiers', null, InputOption::VALUE_NONE, 'Génère les identifiants pour les questions existantes qui n\'en ont pas')
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

        $io->section(sprintf('Chargement des fixtures du groupe "%s"...', $group));

        // Charger les fixtures avec --append
        $application = $this->getApplication();
        if (!$application) {
            $io->error('Application non disponible');
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
            $io->success('Les fixtures ont été rechargées avec succès !');
            $io->note('Les IDs des questions existantes ont été préservés.');
        } else {
            $io->error('Erreur lors du chargement des fixtures');
        }

        return $returnCode;
    }

    /**
     * Génère les identifiants pour les questions existantes qui n'en ont pas
     */
    private function migrateExistingQuestions(SymfonyStyle $io): void
    {
        $io->section('Migration des identifiants des questions existantes...');

        $questionRepo = $this->entityManager->getRepository(Question::class);
        $questions = $questionRepo->findBy(['identifier' => null]);

        if (empty($questions)) {
            $io->info('Toutes les questions ont déjà un identifiant.');
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
        $io->success(sprintf('%d questions ont reçu un identifiant (%d doublons détectés).', $count, $duplicates));
    }
}
