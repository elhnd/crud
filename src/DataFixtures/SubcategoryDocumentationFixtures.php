<?php

namespace App\DataFixtures;

use App\Entity\Subcategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Adds documentation URLs to subcategories for revision strategy links
 */
class SubcategoryDocumentationFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['documentation'];
    }

    // Documentation URLs mapping for each subcategory
    private const DOCUMENTATION_URLS = [
        // Symfony subcategories - Core
        'Dependency Injection' => 'https://symfony.com/doc/current/service_container.html',
        'Services' => 'https://symfony.com/doc/current/service_container.html',
        'HttpKernel' => 'https://symfony.com/doc/current/components/http_kernel.html',
        'HttpFoundation' => 'https://symfony.com/doc/current/components/http_foundation.html',
        'Event Dispatcher' => 'https://symfony.com/doc/current/event_dispatcher.html',
        'Routing' => 'https://symfony.com/doc/current/routing.html',
        'Controllers' => 'https://symfony.com/doc/current/controller.html',
        
        // Symfony - Security & Validation
        'Security' => 'https://symfony.com/doc/current/security.html',
        'Validation' => 'https://symfony.com/doc/current/validation.html',
        'Validator' => 'https://symfony.com/doc/current/validation.html',
        'PasswordHasher' => 'https://symfony.com/doc/current/security/passwords.html',
        
        // Symfony - Forms & Templating
        'Forms' => 'https://symfony.com/doc/current/forms.html',
        'Twig' => 'https://twig.symfony.com/doc/3.x/',
        'OptionsResolver' => 'https://symfony.com/doc/current/components/options_resolver.html',
        
        // Symfony - HTTP & Caching
        'HTTP' => 'https://symfony.com/doc/current/introduction/http_fundamentals.html',
        'Cache' => 'https://symfony.com/doc/current/cache.html',
        'HttpCache' => 'https://symfony.com/doc/current/http_cache.html',
        'Session' => 'https://symfony.com/doc/current/session.html',
        'HttpClient' => 'https://symfony.com/doc/current/http_client.html',
        
        // Symfony - Console
        'Console' => 'https://symfony.com/doc/current/console.html',
        
        // Symfony - Assets & Frontend
        'Assets' => 'https://symfony.com/doc/current/frontend/asset_mapper.html',
        'AssetMapper' => 'https://symfony.com/doc/current/frontend/asset_mapper.html',
        'CssSelector' => 'https://symfony.com/doc/current/components/css_selector.html',
        
        // Symfony - Serialization & Data
        'Serializer' => 'https://symfony.com/doc/current/serializer.html',
        'Messenger' => 'https://symfony.com/doc/current/messenger.html',
        'Yaml' => 'https://symfony.com/doc/current/components/yaml.html',
        'PropertyAccess' => 'https://symfony.com/doc/current/components/property_access.html',
        'VarDumper' => 'https://symfony.com/doc/current/components/var_dumper.html',
        'VarExporter' => 'https://symfony.com/doc/current/components/var_exporter.html',
        
        // Symfony - Testing
        'Testing' => 'https://symfony.com/doc/current/testing.html',
        'BrowserKit' => 'https://symfony.com/doc/current/components/browser_kit.html',
        
        // Symfony - Other components
        'Filesystem' => 'https://symfony.com/doc/current/components/filesystem.html',
        'Finder' => 'https://symfony.com/doc/current/components/finder.html',
        'Expression Language' => 'https://symfony.com/doc/current/components/expression_language.html',
        'FrameworkBundle' => 'https://symfony.com/doc/current/reference/configuration/framework.html',
        'Process' => 'https://symfony.com/doc/current/components/process.html',
        'Lock' => 'https://symfony.com/doc/current/lock.html',
        'Mailer' => 'https://symfony.com/doc/current/mailer.html',
        'Mime' => 'https://symfony.com/doc/current/components/mime.html',
        'Translation' => 'https://symfony.com/doc/current/translation.html',
        'Intl' => 'https://symfony.com/doc/current/components/intl.html',
        'Inflector' => 'https://symfony.com/doc/current/components/string.html#inflector',
        'Dotenv' => 'https://symfony.com/doc/current/components/dotenv.html',
        'Runtime' => 'https://symfony.com/doc/current/components/runtime.html',
        'Clock' => 'https://symfony.com/doc/current/components/clock.html',
        'ErrorHandler' => 'https://symfony.com/doc/current/components/error_handler.html',
        
        // Symfony - Architecture & Config
        'Architecture' => 'https://symfony.com/doc/current/introduction/symfony_architecture.html',
        'Configuration' => 'https://symfony.com/doc/current/configuration.html',
        'Miscellaneous' => 'https://symfony.com/doc/current/components.html',
        
        // Symfony - Future/Additional components
        'Doctrine' => 'https://symfony.com/doc/current/doctrine.html',
        'Migrations' => 'https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-columns',
        'Sessions' => 'https://symfony.com/doc/current/session.html',
        'Cookies' => 'https://symfony.com/doc/current/components/http_foundation.html#setting-cookies',
        'Request Handling' => 'https://symfony.com/doc/current/introduction/http_fundamentals.html',
        'Response' => 'https://symfony.com/doc/current/components/http_foundation.html#response',
        'Attributes' => 'https://symfony.com/doc/current/routing.html#creating-routes-as-attributes',
        'String' => 'https://symfony.com/doc/current/components/string.html',
        'Uid' => 'https://symfony.com/doc/current/components/uid.html',
        'Workflow' => 'https://symfony.com/doc/current/workflow.html',
        'RateLimiter' => 'https://symfony.com/doc/current/rate_limiter.html',
        'Notifier' => 'https://symfony.com/doc/current/notifier.html',
        'Scheduler' => 'https://symfony.com/doc/current/scheduler.html',
        'WebLink' => 'https://symfony.com/doc/current/web_link.html',
        'Stopwatch' => 'https://symfony.com/doc/current/components/stopwatch.html',
        'DomCrawler' => 'https://symfony.com/doc/current/components/dom_crawler.html',
        
        // PHP subcategories - OOP
        'OOP' => 'https://www.php.net/manual/en/language.oop5.php',
        'Interfaces & Traits' => 'https://www.php.net/manual/en/language.oop5.interfaces.php',
        
        // PHP - Core
        'PHP Basics' => 'https://www.php.net/manual/en/langref.php',
        'Functions' => 'https://www.php.net/manual/en/language.functions.php',
        'Closures' => 'https://www.php.net/manual/en/functions.anonymous.php',
        'Exceptions' => 'https://www.php.net/manual/en/language.exceptions.php',
        
        // PHP - Types & Data
        'Typing & Strict Types' => 'https://www.php.net/manual/en/language.types.declarations.php',
        'Arrays' => 'https://www.php.net/manual/en/book.array.php',
        'Data Format & Types' => 'https://www.php.net/manual/en/language.types.php',
        'Strings' => 'https://www.php.net/manual/en/ref.strings.php',
        'JSON' => 'https://www.php.net/manual/en/book.json.php',
        'XML' => 'https://www.php.net/manual/en/book.simplexml.php',
        'DOM' => 'https://www.php.net/manual/en/book.dom.php',
        
        // PHP - Organization
        'Namespaces' => 'https://www.php.net/manual/en/language.namespaces.php',
        'PSR' => 'https://www.php-fig.org/psr/',
        'SPL' => 'https://www.php.net/manual/en/book.spl.php',
        'I/O' => 'https://www.php.net/manual/en/book.filesystem.php',
        
        // PHP - Future/Additional
        'Dates' => 'https://www.php.net/manual/en/book.datetime.php',
        'Generators' => 'https://www.php.net/manual/en/language.generators.php',
        'Attributes PHP' => 'https://www.php.net/manual/en/language.attributes.php',
        'Enums' => 'https://www.php.net/manual/en/language.enumerations.php',
        'Fibers' => 'https://www.php.net/manual/en/language.fibers.php',
        'Reflection' => 'https://www.php.net/manual/en/book.reflection.php',
        'Regular Expressions' => 'https://www.php.net/manual/en/book.pcre.php',
        'PDO' => 'https://www.php.net/manual/en/book.pdo.php',
        'Sessions PHP' => 'https://www.php.net/manual/en/book.session.php',
        'Error Handling' => 'https://www.php.net/manual/en/book.errorfunc.php',
    ];

    public function load(ObjectManager $manager): void
    {
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = $subcategoryRepo->findAll();

        $updated = 0;
        foreach ($subcategories as $subcategory) {
            $name = $subcategory->getName();
            if (isset(self::DOCUMENTATION_URLS[$name])) {
                $subcategory->setDocumentationUrl(self::DOCUMENTATION_URLS[$name]);
                $updated++;
            }
        }

        $manager->flush();
        
        echo "Updated documentation URLs for {$updated} subcategories.\n";
    }
}
