<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 13
 */
class CertificationQuestionsFixtures13 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures12::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        // Load existing subcategories from AppFixtures
        $subcategories = $this->loadSubcategories($manager);
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [
            // Q1 - Console - Signals handling
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Is it possible to make a command listen signals and stop the command accordingly?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, Symfony Console supporte la gestion des signaux via l\'interface SignalableCommandInterface. Cela permet d\'écouter des signaux comme SIGINT ou SIGTERM et de stopper proprement une commande.',
                'resourceUrl' => 'https://symfony.com/doc/current/console/signals.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - PHP Arrays - extract() security
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Is using <code>extract()</code> on <code>$_GET</code>, <code>$_FILES</code> and other unsecured data sources considered secure?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, extract() sur des données utilisateur est dangereux car cela peut écraser des variables existantes et créer des vulnérabilités de sécurité. C\'est explicitement déconseillé dans la documentation PHP.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.extract.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q4 - DI - Autowiring definition
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony, what is autowiring?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'L\'autowiring permet d\'injecter automatiquement les dépendances en se basant sur les type-hints des paramètres du constructeur, sans configuration manuelle.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html',
                'answers' => [
                    ['text' => 'It allows automatic injection of services based on type hints alone, without manual configuration', 'correct' => true],
                    ['text' => 'It\'s a setting that enables the automatic creation of controller services', 'correct' => false],
                    ['text' => 'It\'s Symfony\'s method for generating unique wire names for each service', 'correct' => false],
                    ['text' => 'It refers to the process of encrypting data transfers between services', 'correct' => false],
                ],
            ],

            // Q5 - PHP OOP - __debugInfo return type
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What should the magic method <code>__debugInfo()</code> return?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode __debugInfo() doit retourner un tableau (array) contenant les propriétés à afficher lors de l\'utilisation de var_dump() sur l\'objet.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo',
                'answers' => [
                    ['text' => 'Array', 'correct' => true],
                    ['text' => 'String', 'correct' => false],
                    ['text' => 'Bool', 'correct' => false],
                    ['text' => 'Object', 'correct' => false],
                ],
            ],

            // Q6 - Mime - Email->date() accepted formats
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'Which formats are accepted by the <code>Email->date()</code> method?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode date() de la classe Email n\'accepte que DateTimeImmutable comme argument. Elle ne supporte pas DateTime, les timestamps ou les chaînes de caractères.',
                'resourceUrl' => 'https://github.com/symfony/mime/blob/7.2/Email.php',
                'answers' => [
                    ['text' => '<code>DatetimeImmutable</code>', 'correct' => true],
                    ['text' => '<code>Datetime</code>', 'correct' => false],
                    ['text' => '<code>Int Timestamp</code>', 'correct' => false],
                    ['text' => '<code>String</code>', 'correct' => false],
                ],
            ],

            // Q7 - Security - Access Decision Strategy default
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the default access decision strategy?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La stratégie par défaut est "affirmative" : l\'accès est accordé dès qu\'un voter vote OUI. Les autres stratégies sont "consensus" (majorité), "unanimous" (tous) et "priority" (premier vote).',
                'resourceUrl' => 'https://symfony.com/doc/current/security/voters.html#changing-the-access-decision-strategy',
                'answers' => [
                    ['text' => '<code>affirmative</code>', 'correct' => true],
                    ['text' => '<code>consensus</code>', 'correct' => false],
                    ['text' => '<code>unanimous</code>', 'correct' => false],
                    ['text' => '<code>priority</code>', 'correct' => false],
                ],
            ],

            // Q8 - Mailer - MessageBusInterface required
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'Is <code>MessageBusInterface</code> mandatory when sending emails with the Symfony Mailer?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, MessageBusInterface n\'est pas obligatoire. Le Mailer peut envoyer des emails de manière synchrone sans le Messenger. Le bus de messages n\'est utilisé que pour l\'envoi asynchrone.',
                'resourceUrl' => 'https://symfony.com/doc/current/mailer.html#sending-emails-asynchronously',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q9 - Yaml - Dumping Multi-line Literal Blocks
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'Using the Yaml component, what output can be expected when dumping a string containing newlines?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Par défaut, le composant Yaml échappe les retours à la ligne dans une chaîne entre guillemets avec \\n. Pour obtenir un bloc littéral avec |, il faut utiliser le flag DUMP_MULTI_LINE_LITERAL_BLOCK.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/yaml.html#advanced-usage-flags',
                'answers' => [
                    ['text' => '<pre><code class="language-yaml">string: "Multiple\\nLine\\nString"</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-yaml">string: |\n  Multiple\n  Line\n  String</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-yaml">string: >\n  Multiple\n  Line\n  String</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-yaml">string:\n  - Multiple\n  - Line\n  - String</code></pre>', 'correct' => false],
                ],
            ],

            // Q10 - Twig - format_datetime calendar parameter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Does the <code>format_datetime</code> filter allow an optional <code>calendar</code> parameter?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le filtre format_datetime de Twig Extensions accepte un paramètre calendar optionnel qui permet de spécifier le calendrier à utiliser (gregorian, buddhist, etc.).',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/filters/format_datetime.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q11 - Event Dispatcher - ImmutableEventDispatcher read-only proxy
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Is the <code>ImmutableEventDispatcher</code> a read-only proxy around an <code>EventDispatcher</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ImmutableEventDispatcher est un décorateur en lecture seule qui encapsule un EventDispatcher et empêche l\'ajout ou la suppression de listeners après son instanciation.',
                'resourceUrl' => 'https://github.com/symfony/event-dispatcher/blob/7.2/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - HttpFoundation - getMasterRequest deprecated
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Can <code>RequestStack::getMasterRequest()</code> be called?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, getMasterRequest() a été déprécié en Symfony 5.3 et supprimé en Symfony 6.0. Il faut utiliser getMainRequest() à la place.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_foundation.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q13 - Form - Button event subscriber
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Can a form Button receive an Event Subscriber?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, un Button de formulaire Symfony ne peut pas recevoir d\'Event Subscriber. La méthode addEventSubscriber() n\'est pas disponible sur ButtonBuilder, seulement sur FormBuilder.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/button_based_validation.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q14 - Event Dispatcher - ImmutableEventDispatcher add subscribers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Can an <code>ImmutableEventDispatcher</code> receive additional subscribers after its creation?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, ImmutableEventDispatcher est immuable. Les méthodes addListener() et addSubscriber() lèvent une BadMethodCallException si elles sont appelées.',
                'resourceUrl' => 'https://github.com/symfony/event-dispatcher/blob/7.2/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q15 - HttpKernel - controller.service_arguments tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Can a controller service resolve arguments without having the <code>controller.service_arguments</code> tag?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, le tag controller.service_arguments est nécessaire pour que le ServiceValueResolver puisse injecter les services dans les arguments des actions du contrôleur. Sans ce tag, la résolution des arguments ne fonctionne pas.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller/service.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],
        ];
    }
}
