<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 26
 */
class CertificationQuestionsFixtures26 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures25::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \Exception('Base categories not found. Please load AppFixtures first.');
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
            // Q1 - HTTP - Validation caching headers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following headers are valid ones in the validation caching model?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les en-têtes valides pour le modèle de validation de cache sont If-Modified-Since et If-None-Match. ETag et Last-Modified sont des en-têtes de réponse, pas de requête de validation. Age est un en-tête de réponse de cache.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#page-85',
                'answers' => [
                    ['text' => '<code>If-Modified-Since</code>', 'correct' => true],
                    ['text' => '<code>If-None-Match</code>', 'correct' => true],
                    ['text' => '<code>Etag</code>', 'correct' => false],
                    ['text' => '<code>Age</code>', 'correct' => false],
                    ['text' => '<code>Last-Modified</code>', 'correct' => false],
                ],
            ],

            // Q6 - PHP Functions - Syslog binary safe
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Functions'],
                'text' => 'Is <code>syslog</code> considered binary safe?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis PHP 8.1, syslog est considéré comme binary safe et peut gérer les chaînes binaires.',
                'resourceUrl' => 'https://www.php.net/manual/en/migration81.other-changes.php, https://www.php.net/manual/en/function.syslog.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - SecurityBundle - Clear site data
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Is there a way to make the browser clear its associated data with the requesting website after logging out an user?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.3, il est possible d\'utiliser l\'en-tête Clear-Site-Data après la déconnexion pour demander au navigateur de supprimer les données associées.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-login-and-logout-improvements#clear-site-data-after-logout',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - PHP Arrays - array_shift
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays & Collections'],
                'text' => 'Which function is used to remove and return the first element of an array?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'array_shift() supprime et retourne le premier élément d\'un tableau. array_pop() fait de même pour le dernier élément.',
                'resourceUrl' => 'https://php.net/array, https://php.net/manual/en/function.array-pop.php, https://php.net/manual/fr/function.array-shift.php',
                'answers' => [
                    ['text' => '<code>array_shift</code>', 'correct' => true],
                    ['text' => '<code>array_pop</code>', 'correct' => false],
                    ['text' => '<code>array_grab</code>', 'correct' => false],
                    ['text' => '<code>array_pull</code>', 'correct' => false],
                ],
            ],

            // Q13 - PHP Basics - Final class constants
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Is it allowed to declare a class constant as <code>final</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis PHP 8.1, il est possible de déclarer une constante de classe comme final pour empêcher sa surcharge dans les classes enfants.',
                'resourceUrl' => 'https://wiki.php.net/rfc/final_class_const',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - FrameworkBundle - Locale aware services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Which tag will be applied to the following service once registered?
<pre><code class="language-php">&lt;?php

namespace App\Service;

use Symfony\Contracts\Translation\LocaleAwareInterface;

final class LocaleHelper implements LocaleAwareInterface
{
  public function setLocale(string $locale): void
  {
    // ...
  }

  public function getLocale(): string
  {
    return \'en\';
  }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les services qui implémentent LocaleAwareInterface reçoivent automatiquement le tag kernel.locale_aware.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L386',
                'answers' => [
                    ['text' => '<code>kernel.locale_aware</code>', 'correct' => true],
                    ['text' => '<code>kernel.locale_entrypoint</code>', 'correct' => false],
                    ['text' => 'None', 'correct' => false],
                    ['text' => '<code>locale_listener</code>', 'correct' => false],
                    ['text' => '<code>kernel.locale_listener</code>', 'correct' => false],
                    ['text' => '<code>locale_aware</code>', 'correct' => false],
                ],
            ],

            // Q17 - Doctrine ORM - Identifiers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'What is true about identifiers/primary keys?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Chaque entité doit avoir un identifiant/clé primaire. On utilise @Id pour le marquer et généralement @GeneratedValue pour l\'auto-incrémentation.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#identifiers-primary-keys',
                'answers' => [
                    ['text' => 'Every entity class must have an identifier/primary key.', 'correct' => true],
                    ['text' => 'The <code>@Id</code> annotation need to be used.', 'correct' => true],
                    ['text' => 'Usually, the <code>@GeneratedValue</code> annotation is used in order to have an AUTO_INCREMENT attribute on mysql.', 'correct' => true],
                    ['text' => 'The <code>@Identifier</code> annotation need to be used.', 'correct' => false],
                    ['text' => 'The primary key must be an <code>integer</code> only.', 'correct' => false],
                ],
            ],

            // Q18 - HTTP - Immutable cache-control
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Given the context where a file (called <code>react.0.0.0.js</code> and incremented each time the file is updated) is available via <code>https://example.com/react.0.0.0.js</code>, could an <code>immutable</code> cache-control header be applied to it without encountering any cache issues?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'en-tête immutable est approprié pour les fichiers versionnés car le contenu ne changera jamais pour cette URL spécifique.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc8246, https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q19 - SecurityBundle - Security configuration keywords
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Consider the following <code>SecurityBundle</code> configuration:
<pre><code class="language-yaml">security:

    ???:
        AppBundle\Entity\Utilisateur: bcrypt

    ???:
        users:
            id: app.user_repository

    ???:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        public:
            pattern: \'^/\'
            provider: users
            anonymous: ~
            form_login: ~
            logout: ~
</code></pre>
<p>Which keywords do <code>???</code> replace in order to make this configuration valid?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les trois sections de configuration de sécurité sont : encoders (pour le hachage des mots de passe), providers (pour charger les utilisateurs) et firewalls (pour définir les règles de sécurité).',
                'resourceUrl' => 'https://symfony.com/doc/3.4/security.html, https://symfony.com/doc/3.4/components/security.html, https://symfony.com/doc/3.4/reference/configuration/security.html',
                'answers' => [
                    ['text' => '<code>encoders</code>', 'correct' => true],
                    ['text' => '<code>providers</code>', 'correct' => true],
                    ['text' => '<code>firewalls</code>', 'correct' => true],
                    ['text' => '<code>authentication</code>', 'correct' => false],
                    ['text' => '<code>access_control</code>', 'correct' => false],
                ],
            ],

            // Q20 - Security - check_path firewall
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Should the <code>check_path</code> route be behind a firewall ?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le check_path doit être derrière le même firewall que le login_path, car il fait partie du processus d\'authentification.',
                'resourceUrl' => 'http://symfony.com/doc/3.1/reference/configuration/security.html#check-path',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q21 - HttpKernel - HttpKernelInterface handle 3rd argument
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the third argument of the <code>handle</code> method of <code>Symfony\Component\HttpKernel\HttpKernelInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le troisième argument de la méthode handle() est un booléen indiquant s\'il faut capturer les exceptions ou non (catch).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/HttpKernelInterface.php#L42',
                'answers' => [
                    ['text' => 'Whether to catch exceptions or not.', 'correct' => true],
                    ['text' => 'A Request instance', 'correct' => false],
                    ['text' => 'The type of the request', 'correct' => false],
                    ['text' => 'The name of the environment', 'correct' => false],
                    ['text' => 'Whether to activate the debug or not', 'correct' => false],
                ],
            ],

            // Q22 - Form - FormType representation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following can be represented as a <code>Symfony\Component\Form\Extension\Core\Type\FormType</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FormType peut représenter toutes ces possibilités : un formulaire complet, un groupe de champs, ou même un seul champ. C\'est le type de base le plus flexible.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/forms#form-types',
                'answers' => [
                    ['text' => 'A single <code>&lt;input type="text"&gt;</code> form field', 'correct' => true],
                    ['text' => 'A group of several HTML fields used to input a postal address', 'correct' => true],
                    ['text' => 'An entire <code>&lt;form&gt;</code> with multiple fields to edit a user profile', 'correct' => true],
                ],
            ],

            // Q24 - FrameworkBundle - Default ORM
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the default ORM that integrates with Symfony ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Doctrine est l\'ORM par défaut qui s\'intègre avec Symfony, bien que d\'autres ORM comme Propel puissent être utilisés.',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html',
                'answers' => [
                    ['text' => 'Doctrine.', 'correct' => true],
                    ['text' => 'Propel.', 'correct' => false],
                    ['text' => 'Hibernate.', 'correct' => false],
                    ['text' => 'Symfony ORM.', 'correct' => false],
                ],
            ],

            // Q26 - PHP Basics - printf function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What will be the output of the following script ?
<pre><code class="language-php">$str = printf(\'%.1f\', 1/8);
echo \'Total is \';
echo $str;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'printf() affiche le résultat formaté (0.1) et retourne le nombre de caractères affichés (3). Donc on voit "0.1Total is 3".',
                'resourceUrl' => 'http://php.net/manual/en/function.printf.php',
                'answers' => [
                    ['text' => '<code>0.1Total is 3</code>', 'correct' => true],
                    ['text' => '<code>0.125Total is 0.1</code>', 'correct' => false],
                    ['text' => '<code>0.125Total is 0.125</code>', 'correct' => false],
                    ['text' => '<code>1/8Total is 1/8</code>', 'correct' => false],
                    ['text' => '<code>3Total is 3</code>', 'correct' => false],
                ],
            ],

            // Q27 - Event Dispatcher - Event listener tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What is the tag to use to listen to different events/hooks in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le tag kernel.event_listener permet d\'enregistrer un listener pour des événements Symfony.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#kernel-event-listener',
                'answers' => [
                    ['text' => '<code>kernel.event_listener</code>', 'correct' => true],
                    ['text' => '<code>kernel.listener</code>', 'correct' => false],
                    ['text' => '<code>event_dispatcher.event_listener</code>', 'correct' => false],
                    ['text' => '<code>dispatcher.listener</code>', 'correct' => false],
                    ['text' => '<code>dispatcher.event_listener</code>', 'correct' => false],
                    ['text' => '<code>event_listener</code>', 'correct' => false],
                ],
            ],

            // Q28 - PHP Basics - PHP DOM createTextNode
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:DOM'],
                'text' => 'What should ????? be replaced with to add a <code>&lt;title&gt;</code> node with the value of "Hello, World!"?
<pre><code class="language-php">$title = $dom-&gt;createElement(\'title\');

$node = ?????????

$title-&gt;appendChild($node);
$head-&gt;appendChild($title);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour créer un nœud texte en PHP DOM, on utilise la méthode createTextNode() du document.',
                'resourceUrl' => 'http://www.php.net/dom, http://php.net/manual/en/domdocument.createtextnode.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$dom-&gt;createTextNode(\'Hello, World!\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$dom-&gt;appendElement($title, \'text\', \'Hello, World!\');</code></pre>', 'correct' => false],
                    ['text' => 'None of the above', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$dom-&gt;appendTextNode($title, "Hello, World!");</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$dom-&gt;createElement(\'text\', \'Hello, World!\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q30 - Routing - Route methods matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'If no methods are specified for a route, what methods will be matched?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Si aucune méthode HTTP n\'est spécifiée pour une route, elle correspondra à toutes les méthodes HTTP.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/routing.html#matching-http-methods',
                'answers' => [
                    ['text' => 'Any methods', 'correct' => true],
                    ['text' => 'Safe methods: GET or HEAD', 'correct' => false],
                    ['text' => 'GET or POST', 'correct' => false],
                    ['text' => 'GET', 'correct' => false],
                ],
            ],

            // Q31 - HTTP - Proxies cache revalidation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Could a resource be revalidated only by proxies?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'en-tête Cache-Control avec la directive proxy-revalidate force uniquement les proxies à revalider une ressource expirée, pas les caches privés (navigateurs).',
                'resourceUrl' => 'https://httpwg.org/specs/rfc7234.html#cache-response-directive.proxy-revalidate, https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control#response_directives',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - SecurityBundle - access_control role
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Given the following <code>access_control</code> configuration:
<pre><code class="language-yaml">access_control:
    - { path: ^/profile, roles: ROLE_USER, requires_channel: https }
    - { path: ^/profile, roles: ROLE_ADMIN }</code></pre>
<p>The requested url is <code>http://mydomain.tld/profile</code>. Which role is needed to access to <code>/profile</code> ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La première règle matching est appliquée. requires_channel provoque une redirection mais n\'affecte pas le matching. Donc ROLE_USER est requis et une redirection vers HTTPS sera effectuée.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/security/access_control.html',
                'answers' => [
                    ['text' => '<code>ROLE_USER</code>', 'correct' => true],
                    ['text' => '<code>ROLE_ADMIN</code>', 'correct' => false],
                    ['text' => 'Neither <code>ROLE_USER</code> nor <code>ROLE_ADMIN</code>, an exception is thrown', 'correct' => false],
                ],
            ],
        ];
    }
}
