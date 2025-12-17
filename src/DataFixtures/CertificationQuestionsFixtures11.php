<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 11
 */
class CertificationQuestionsFixtures11 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['certification11', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures10::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        $subcategories = $this->getSubcategories($manager, $symfony, $php);
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getSubcategories(ObjectManager $manager, Category $symfony, Category $php): array
    {
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = [];

        foreach ($subcategoryRepo->findAll() as $sub) {
            $subcategories[$sub->getCategory()->getName() . ':' . $sub->getName()] = $sub;
        }

        $additional = [
            'Symfony' => [
                'Finder' => 'Finder component for file system operations',
                'Intl' => 'Intl component for internationalization',
                'CssSelector' => 'CssSelector component for CSS to XPath conversion',
                'Messenger' => 'Messenger component for message handling',
            ],
            'PHP' => [
                'XML' => 'XML and DOM manipulation in PHP',
            ],
        ];

        foreach ($additional as $catName => $subs) {
            $category = $catName === 'Symfony' ? $symfony : $php;
            foreach ($subs as $name => $description) {
                $key = $catName . ':' . $name;
                if (!isset($subcategories[$key])) {
                    $sub = new Subcategory();
                    $sub->setName($name);
                    $sub->setDescription($description);
                    $sub->setCategory($category);
                    $manager->persist($sub);
                    $subcategories[$key] = $sub;
                }
            }
        }

        $manager->flush();
        return $subcategories;
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [
            // Q1 - Form - Render the rest of the fields
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Is there a way to make sure that the <code>{{ form_end(form) }}</code> does not render all the fields not rendered?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, vous pouvez passer l\'option render_rest à false dans form_end() pour empêcher le rendu automatique des champs non rendus: {{ form_end(form, {\'render_rest\': false}) }}',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/twig_reference.html#form-end-view-variables',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - Finder - Service ID
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'] ?? $subcategories['Symfony:Console'],
                'text' => 'What is the finder service id?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le Finder n\'est pas un service en raison de sa nature stateful. Il doit être instancié à chaque utilisation car il maintient un état interne.',
                'resourceUrl' => 'https://symfony.com/doc/3.3/components/finder.html#usage',
                'answers' => [
                    ['text' => 'finder.iterator', 'correct' => false],
                    ['text' => 'finder', 'correct' => false],
                    ['text' => 'None of them, the finder is not a service', 'correct' => true],
                    ['text' => 'finder.finder', 'correct' => false],
                ],
            ],

            // Q3 - PHP strcmp
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the output?
<pre><code class="language-php">echo strcmp(123, \'123\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'strcmp() compare deux chaînes. L\'entier 123 est converti en chaîne "123", donc strcmp("123", "123") retourne 0 car les deux chaînes sont identiques.',
                'resourceUrl' => 'http://php.net/manual/en/language.types.type-juggling.php, http://php.net/manual/fr/function.strcmp.php',
                'answers' => [
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                    ['text' => '0', 'correct' => true],
                    ['text' => '-1', 'correct' => false],
                ],
            ],

            // Q4 - Form Validation trigger
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which method of the <code>Symfony\Component\Form\Form</code> class really triggers the whole data validation process?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode submit() déclenche le processus de validation via le ValidationListener qui écoute l\'événement FormEvents::POST_SUBMIT.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/0baa58d4e4bb006c4ae68f75833b586bd3cb6e6f/src/Symfony/Component/Form/Form.php#L665, https://github.com/symfony/symfony/blob/0baa58d4e4bb006c4ae68f75833b586bd3cb6e6f/src/Symfony/Component/Form/Extension/Validator/EventListener/ValidationListener.php#L35',
                'answers' => [
                    ['text' => '<code>submit()</code>', 'correct' => true],
                    ['text' => '<code>validate()</code>', 'correct' => false],
                    ['text' => '<code>getErrors()</code>', 'correct' => false],
                    ['text' => '<code>isValid()</code>', 'correct' => false],
                ],
            ],

            // Q5 - FlashBag usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could the <code>FlashBag</code> messages be retrieving while being removed from the bag?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la méthode get() du FlashBag récupère et supprime les messages en une seule opération. C\'est le comportement par défaut des flash messages.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/fc0a09a2052e9275c16b5ab7af426935fe432f39/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php#L162',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - XPath query
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:XML'] ?? $subcategories['PHP:PHP Basics'],
                'text' => 'Considering the following HTML structure:
<pre><code class="language-html">&lt;!DOCTYPE html&gt;
&lt;html&gt;
    &lt;head&gt;
        &lt;meta charset="UTF-8" /&gt;
    &lt;/head&gt;
    &lt;body bgcolor="test"&gt;
        &lt;a href="test.com"&gt;Some link&lt;/a&gt;
    &lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>And the following PHP script:</p>
<pre><code class="language-php">$dom = new DomDocument;
$dom-&gt;load(\'test.xml\');
$xpath = new DomXPath($dom);
$nodes = $xpath-&gt;query(/* ... */);
echo $nodes-&gt;item(0)-&gt;getAttributeNode(\'bgcolor\')-&gt;value;</code></pre>
<p>What Xpath query should go in the <code>/* ... */</code> to display the <strong>bgcolor</strong> attribute of the first <strong>body</strong> node?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La fonction XPath local-name() permet de sélectionner des éléments par leur nom local, sans tenir compte du namespace. *[local-name()="body"] sélectionne tous les éléments dont le nom local est "body".',
                'resourceUrl' => 'https://www.w3.org/TR/1999/REC-xpath-19991116/#function-local-name, https://www.php.net/manual/fr/class.domxpath.php',
                'answers' => [
                    ['text' => '<code>name="body"</code>', 'correct' => false],
                    ['text' => '<code>*[lname()="body"]</code>', 'correct' => false],
                    ['text' => '<code>*[local-name()="body"]</code>', 'correct' => true],
                    ['text' => '<code>/body/body[0]</code>', 'correct' => false],
                    ['text' => '<code>/body[0]/text</code>', 'correct' => false],
                ],
            ],

            // Q7 - Password confirmation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What solution can you use to ask the user to type his password twice in a form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le RepeatedType est conçu spécifiquement pour ce cas d\'usage. Il crée deux champs qui doivent contenir la même valeur et affiche une erreur si les valeurs diffèrent.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/repeated.html',
                'answers' => [
                    ['text' => 'Use the <code>ask_confirmation</code> option on the <code>PasswordType</code> form type.', 'correct' => false],
                    ['text' => 'Call the <code>render_widget</code> twig function twice on the password form type.', 'correct' => false],
                    ['text' => 'Use the <code>RepeatedType</code> form type.', 'correct' => true],
                    ['text' => 'Use the <strong>Validation</strong> plugin of <strong>jQuery</strong>.', 'correct' => false],
                ],
            ],

            // Q8 - Security isGranted on anonymous users
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'When using the new Authenticator-based Security, does <code>isGranted()</code> will work on anonymous users?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Avec le nouveau système d\'authentification, isGranted() fonctionne sur les utilisateurs anonymes mais retourne toujours false car ils n\'ont aucun rôle attribué.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/security/experimental_authenticators.html#adding-support-for-unsecured-access-i-e-anonymous-users',
                'answers' => [
                    ['text' => 'No, It will throw an exception', 'correct' => false],
                    ['text' => 'Yes, It will always return <code>true</code>', 'correct' => false],
                    ['text' => 'Yes, It will always return <code>false</code>', 'correct' => true],
                ],
            ],

            // Q9 - Messenger Doctrine transaction
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'] ?? $subcategories['Symfony:Services'],
                'text' => 'Given the context where the doctrine transport is used, could all the handlers be wrapped in a single transaction?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, en utilisant le middleware DoctrineTransactionMiddleware, tous les handlers peuvent être exécutés dans une seule transaction Doctrine.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/messenger.html#middleware-for-doctrine',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q10 - CssSelector :is pseudo-class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:CssSelector'] ?? $subcategories['Symfony:Console'],
                'text' => 'Could the <code>*:is</code> selector be used?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 7.1, le sélecteur :is() est supporté par le composant CssSelector.',
                'resourceUrl' => 'https://symfony.com/doc/7.1/components/css_selector.html#limitations-of-the-cssselector-component',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q11 - HttpFoundation $_SERVER access
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_SERVER</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La propriété $request->server est un ServerBag qui contient toutes les données de $_SERVER.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/http_foundation/introduction.html#accessing-request-data',
                'answers' => [
                    ['text' => '<code>$request->getServersData()</code>', 'correct' => false],
                    ['text' => '<code>$request->servers</code>', 'correct' => false],
                    ['text' => '<code>$request->getServerData()</code>', 'correct' => false],
                    ['text' => '<code>$request->server</code>', 'correct' => true],
                    ['text' => '<code>$request->getServer()</code>', 'correct' => false],
                ],
            ],

            // Q12 - Process isSuccessful
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'] ?? $subcategories['Symfony:Console'],
                'text' => 'How is determined the fact that a process terminated successfully, internally by <code>Process::isSuccessful()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un processus est considéré comme réussi si son code de retour est 0. C\'est la convention Unix standard.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/Process/Process.php#L761',
                'answers' => [
                    ['text' => 'The return code of the process is equal to <code>0</code>', 'correct' => true],
                    ['text' => 'The return code of the process is equal to <code>true</code>', 'correct' => false],
                    ['text' => 'The output of the process finishes with <code>success</code>', 'correct' => false],
                ],
            ],

            // Q14 - NumberType scale option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What is the option you can pass to a <code>Symfony\Component\Form\Extension\Core\Type\NumberType</code> form type to change the number of decimals allowed until the field rounds?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option scale définit le nombre de décimales autorisées avant l\'arrondi. L\'option precision a été renommée en scale depuis Symfony 2.7.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types/number.html',
                'answers' => [
                    ['text' => '<code>decimals</code>', 'correct' => false],
                    ['text' => '<code>precision</code>', 'correct' => false],
                    ['text' => '<code>scale</code>', 'correct' => true],
                ],
            ],

            // Q15 - HttpKernel Fragment renderer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could a custom fragment renderer strategy be created?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, vous pouvez créer votre propre stratégie de rendu de fragments en implémentant FragmentRendererInterface et en taguant le service avec kernel.fragment_renderer.',
                'resourceUrl' => 'https://symfony.com/doc/2.2/reference/dic_tags.html#kernel-fragment-renderer, https://github.com/symfony/symfony/blob/2.2/src/Symfony/Component/HttpKernel/Fragment/FragmentRendererInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - Twig node_class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'When writing a <code>Twig_Test</code>, what is a <code>node_class</code> for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'L\'option node_class permet de spécifier une classe de noeud personnalisée qui sera compilée en primitives PHP, permettant une meilleure optimisation des performances.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/advanced.html#tests',
                'answers' => [
                    ['text' => 'The given test will rely on a custom <code>Twig_NodeVisitorInterface</code>.', 'correct' => false],
                    ['text' => 'The given test will use a semantic validation in addition to the basic evaluation.', 'correct' => false],
                    ['text' => 'The given test will be compiled into PHP primitives.', 'correct' => true],
                    ['text' => 'The <code>node_class</code> is a mandatory option to get defined in a <code>Twig_Environment</code>.', 'correct' => false],
                ],
            ],

            // Q17 - Twig Escaping with raw filter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given <code>var</code> and <code>bar</code> are existing variables, among the following, which expressions are escaped?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le filtre raw doit être le dernier filtre pour empêcher l\'échappement. {{ var|raw|upper }} est échappé car upper est après raw. {{ var|raw~bar }} a bar qui est échappé car la concaténation crée une nouvelle valeur.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/raw.html',
                'answers' => [
                    ['text' => '<code>{{ var|raw~bar }}</code>', 'correct' => true],
                    ['text' => '<code>{{ var|upper|raw }}</code>', 'correct' => false],
                    ['text' => '<code>{{ var|raw|upper }}</code>', 'correct' => true],
                ],
            ],
        ];
    }
}
