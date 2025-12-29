<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CertificationLearningController extends AbstractController
{
    #[Route('/certification/learning', name: 'certification_learning')]
    public function index(): Response
    {
        $curriculum = $this->getCurriculum();
        
        return $this->render('certification/learning.html.twig', [
            'curriculum' => $curriculum,
        ]);
    }

    private function getCurriculum(): array
    {
        return [
            [
                'level' => 'Beginner',
                'icon' => '⭐',
                'color' => 'success',
                'sections' => [
                    [
                        'title' => 'Before you start Symfony',
                        'topics' => [
                            [
                                'name' => 'HTTP',
                                'subtopics' => [
                                    [
                                        'title' => 'Symfony and HTTP Fundamentals',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Symfony and HTTP Fundamentals', 'url' => 'https://symfony.com/doc/7.0/introduction/http_fundamentals.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Method',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Method', 'url' => 'https://tools.ietf.org/html/rfc2616#section-5.1.1'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Status Code',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Status-Line', 'url' => 'https://tools.ietf.org/html/rfc2616#section-6.1'],
                                            ['type' => 'doc', 'title' => 'Status Code Definitions', 'url' => 'https://tools.ietf.org/html/rfc2616#section-10'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'PHP',
                                'subtopics' => [
                                    [
                                        'title' => 'Classes and Objects',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'The Basics', 'url' => 'https://php.net/manual/en/language.oop5.basic.php'],
                                            ['type' => 'doc', 'title' => 'Properties', 'url' => 'https://php.net/manual/en/language.oop5.properties.php'],
                                            ['type' => 'doc', 'title' => 'Class Constants', 'url' => 'https://php.net/manual/en/language.oop5.constants.php'],
                                            ['type' => 'doc', 'title' => 'Constructors and Destructors', 'url' => 'https://php.net/manual/en/language.oop5.decon.php'],
                                            ['type' => 'doc', 'title' => 'Visibility', 'url' => 'https://php.net/manual/en/language.oop5.visibility.php'],
                                            ['type' => 'doc', 'title' => 'Object Inheritance', 'url' => 'https://php.net/manual/en/language.oop5.inheritance.php'],
                                            ['type' => 'doc', 'title' => 'Object Interfaces', 'url' => 'https://php.net/manual/en/language.oop5.interfaces.php'],
                                            ['type' => 'doc', 'title' => 'Objects and references', 'url' => 'https://php.net/manual/en/language.oop5.references.php'],
                                            ['type' => 'doc', 'title' => 'Scope Resolution Operator (::)', 'url' => 'https://php.net/manual/en/language.oop5.paamayim-nekudotayim.php'],
                                            ['type' => 'doc', 'title' => 'OOP Changelog', 'url' => 'https://www.php.net/manual/en/language.oop5.changelog.php'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Namespaces',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Namespaces', 'url' => 'https://php.net/manual/en/language.namespaces.php'],
                                            ['type' => 'video', 'title' => 'Namespaces on SymfonyCasts', 'url' => 'https://symfonycasts.com/screencast/php-namespaces-in-120-seconds/namespaces'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Discover Symfony',
                        'topics' => [
                            [
                                'name' => 'Symfony Architecture',
                                'subtopics' => [
                                    [
                                        'title' => 'Code organization',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'The Architecture', 'url' => 'https://symfony.com/doc/7.0/quick_tour/the_architecture.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'The Symfony Components',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'The Symfony Components', 'url' => 'https://symfony.com/doc/7.0/components/index.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Symfony Roadmap',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Symfony Roadmap', 'url' => 'https://symfony.com/roadmap'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Setup',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Installing & Setting up the Symfony Framework', 'url' => 'https://symfony.com/doc/7.0/setup.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Managing Dependencies With Symfony Flex',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Symfony Flex', 'url' => 'https://symfony.com/doc/7.0/setup/flex.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'License',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'License', 'url' => 'https://symfony.com/doc/7.0/contributing/code/license.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Symfony controllers for beginners',
                        'topics' => [
                            [
                                'name' => 'Controllers',
                                'subtopics' => [
                                    [
                                        'title' => 'The Request',
                                        'links' => [
                                            ['type' => 'doc', 'title' => '"Request -> Controller -> Response" lifecycle', 'url' => 'https://symfony.com/doc/7.0/components/http_kernel.html#the-workflow-of-a-request'],
                                            ['type' => 'doc', 'title' => 'Request usage', 'url' => 'https://symfony.com/doc/7.0/components/http_foundation.html#request'],
                                            ['type' => 'code', 'title' => 'Request class source code', 'url' => 'https://github.com/symfony/symfony/blob/4.0/src/Symfony/Component/HttpFoundation/Request.php'],
                                        ],
                                    ],
                                    [
                                        'title' => 'The Response',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Response usage', 'url' => 'https://symfony.com/doc/7.0/components/http_foundation.html#response'],
                                            ['type' => 'code', 'title' => 'Response class source code', 'url' => 'https://github.com/symfony/symfony/blob/4.0/src/Symfony/Component/HttpFoundation/Response.php'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Controller',
                                        'links' => [
                                            ['type' => 'doc', 'title' => "Symfony's front controller", 'url' => 'https://symfony.com/doc/7.0/create_framework/front_controller.html'],
                                            ['type' => 'doc', 'title' => 'Creating a web page', 'url' => 'https://symfony.com/doc/7.0/page_creation.html'],
                                            ['type' => 'doc', 'title' => "Symfony's controller", 'url' => 'https://symfony.com/doc/7.0/controller.html'],
                                            ['type' => 'code', 'title' => "Symfony's base controller class code", 'url' => 'https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Controller best practices',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Controllers best practices', 'url' => 'https://symfony.com/doc/7.0/best_practices/controllers.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Routing',
                                'subtopics' => [
                                    [
                                        'title' => 'Base routing usage',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Route configuration (YAML, XML, PHP & annotations)', 'url' => 'https://symfony.com/doc/7.0/routing.html#creating-routes'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Localized routes i18n',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Creating localized routes', 'url' => 'https://symfony.com/doc/7.0/routing.html#localized-routes-i18n'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Generate URL parameters',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Generating urls', 'url' => 'https://symfony.com/doc/7.0/routing.html#generating-urls'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Twig for beginners',
                        'topics' => [
                            [
                                'name' => 'Templating with Twig',
                                'subtopics' => [
                                    [
                                        'title' => 'Loops and conditions',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'for loop', 'url' => 'https://twig.symfony.com/doc/tags/for.html'],
                                            ['type' => 'doc', 'title' => 'Control structures', 'url' => 'https://twig.symfony.com/doc/templates.html#control-structure'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Template includes',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Include templates in your Symfony application', 'url' => 'https://symfony.com/doc/7.0/templates.html#including-templates'],
                                            ['type' => 'doc', 'title' => 'include twig function reference', 'url' => 'https://twig.symfony.com/doc/2.x/tags/include.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Templating inheritance',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Template layouts', 'url' => 'https://symfony.com/doc/7.0/templating.html#template-inheritance-and-layouts'],
                                            ['type' => 'doc', 'title' => 'Template inheritance', 'url' => 'https://twig.symfony.com/doc/templates.html#template-inheritance'],
                                            ['type' => 'doc', 'title' => 'extends tag in Twig', 'url' => 'https://twig.symfony.com/doc/tags/extends.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Create services',
                        'topics' => [
                            [
                                'name' => 'Dependency Injection basics',
                                'subtopics' => [
                                    [
                                        'title' => 'Dependency Injection basics',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Types of Injection', 'url' => 'https://symfony.com/doc/7.0/service_container/injection_types.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Register Services and Parameters',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Introduction to Parameters', 'url' => 'https://symfony.com/doc/7.0/configuration.html#configuration-parameters'],
                                            ['type' => 'doc', 'title' => 'Environment variables', 'url' => 'https://symfony.com/doc/7.0/configuration.html#configuration-based-on-environment-variables'],
                                            ['type' => 'doc', 'title' => 'How to Import Configuration Files/Resources', 'url' => 'https://symfony.com/doc/7.0/service_container/import.html'],
                                            ['type' => 'doc', 'title' => 'How to Make Service Arguments/References Optional', 'url' => 'https://symfony.com/doc/7.0/service_container/optional_dependencies.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Form for beginners',
                        'topics' => [
                            [
                                'name' => 'Create a Simple Form',
                                'subtopics' => [
                                    [
                                        'title' => 'Forms',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Forms', 'url' => 'https://symfony.com/doc/7.0/forms.html'],
                                            ['type' => 'doc', 'title' => 'Form Types Reference', 'url' => 'https://symfony.com/doc/7.0/reference/forms/types.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Rendering of a Form',
                                'subtopics' => [
                                    [
                                        'title' => 'How to Control the Rendering of a Form',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Control the Rendering of a Form', 'url' => 'https://symfony.com/doc/7.0/form/form_customization.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Upload Files',
                                'subtopics' => [
                                    [
                                        'title' => 'How to Upload Files',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Upload Files', 'url' => 'https://symfony.com/doc/7.0/controller/upload_file.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Translations basics',
                        'topics' => [
                            [
                                'name' => 'Translations',
                                'subtopics' => [
                                    [
                                        'title' => 'Translations',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Translations', 'url' => 'https://symfony.com/doc/7.0/translation.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Locale',
                                        'links' => [
                                            ['type' => 'doc', 'title' => "How to Work with the User's Locale", 'url' => 'https://symfony.com/doc/7.0/translation/locale.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Validation basics',
                        'topics' => [
                            [
                                'name' => 'Validation',
                                'subtopics' => [
                                    [
                                        'title' => 'The Basics of Validation',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Validation', 'url' => 'https://symfony.com/doc/7.0/validation.html'],
                                            ['type' => 'doc', 'title' => 'Constraints Reference', 'url' => 'https://symfony.com/doc/7.0/reference/constraints.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Console for beginners',
                        'topics' => [
                            [
                                'name' => 'Symfony built-in commands',
                                'subtopics' => [
                                    [
                                        'title' => 'Built-in commands',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Using built-in commands', 'url' => 'https://symfony.com/doc/7.0/components/console/usage.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Custom commands',
                                'subtopics' => [
                                    [
                                        'title' => 'Custom commands',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Creating a custom console command', 'url' => 'https://symfony.com/doc/7.0/console.html#creating-a-command'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Configuration',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Handling console arguments', 'url' => 'https://symfony.com/doc/7.0/components/console/console_arguments.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Options and arguments',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Using command-line arguments', 'url' => 'https://symfony.com/doc/7.0/console/input.html#using-command-arguments'],
                                            ['type' => 'doc', 'title' => 'Using command-line options (like -f)', 'url' => 'https://symfony.com/doc/7.0/console/input.html#using-command-options'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'Intermediate',
                'icon' => '⭐⭐',
                'color' => 'warning',
                'sections' => [
                    [
                        'title' => 'Intermediate usage of the Service Definitions',
                        'topics' => [
                            [
                                'name' => 'More with Service Definitions',
                                'subtopics' => [
                                    [
                                        'title' => 'Autowiring',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Autowiring', 'url' => 'https://symfony.com/doc/7.0/service_container.html#the-autowire-option'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Method calls',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Method calls', 'url' => 'https://symfony.com/doc/7.0/service_container/calls.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Synthetic services',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Synthetic services', 'url' => 'https://symfony.com/doc/7.0/service_container/synthetic_services.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Alias',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Aliasing', 'url' => 'https://symfony.com/doc/7.0/service_container/alias_private.html#aliasing'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Non shared services',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Define Non Shared Services', 'url' => 'https://symfony.com/doc/7.0/service_container/shared.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'CompilerPass',
                                'subtopics' => [
                                    [
                                        'title' => 'CompilerPass',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Work with Service Tags', 'url' => 'https://symfony.com/doc/7.0/service_container/tags.html'],
                                            ['type' => 'doc', 'title' => 'How to Work with Compiler Passes in Bundles', 'url' => 'https://symfony.com/doc/7.0/service_container/compiler_passes.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Intermediate usage of Forms',
                        'topics' => [
                            [
                                'name' => 'Data transformers',
                                'subtopics' => [
                                    [
                                        'title' => 'Data Transformers',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Use Data Transformers', 'url' => 'https://symfony.com/doc/7.0/form/data_transformers.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Validation Groups',
                                'subtopics' => [
                                    [
                                        'title' => 'Advanced usage of Validation Groups',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Define the Validation Groups to Use', 'url' => 'https://symfony.com/doc/7.0/form/validation_groups.html'],
                                            ['type' => 'doc', 'title' => 'How to Choose Validation Groups Based on the Clicked Button', 'url' => 'https://symfony.com/doc/7.0/form/button_based_validation.html'],
                                            ['type' => 'doc', 'title' => 'How to Choose Validation Groups Based on the Submitted Data', 'url' => 'https://symfony.com/doc/7.0/form/data_based_validation.html'],
                                            ['type' => 'doc', 'title' => 'How to Disable the Validation of Submitted Data', 'url' => 'https://symfony.com/doc/7.0/form/disabling_validation.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Custom Form Field Type',
                                'subtopics' => [
                                    [
                                        'title' => 'Custom Form Field Type',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Create a Custom Form Field Type', 'url' => 'https://symfony.com/doc/7.0/form/create_custom_field_type.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Form Themes',
                                'subtopics' => [
                                    [
                                        'title' => 'Form Themes',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Customize Form Rendering', 'url' => 'https://symfony.com/doc/7.0/form/form_customization.html'],
                                            ['type' => 'doc', 'title' => 'How to Work with Form Themes', 'url' => 'https://symfony.com/doc/7.0/form/form_themes.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Debug Translations',
                        'topics' => [
                            [
                                'name' => 'Debug Translations',
                                'subtopics' => [
                                    [
                                        'title' => 'Debug Translations',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Find Missing or Unused Translation Messages', 'url' => 'https://symfony.com/doc/7.0/translation/debug.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'Advanced',
                'icon' => '⭐⭐⭐',
                'color' => 'danger',
                'sections' => [
                    [
                        'title' => 'Advanced usage of Forms',
                        'topics' => [
                            [
                                'name' => 'Embed Forms',
                                'subtopics' => [
                                    [
                                        'title' => 'Embed Forms',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Embed Forms', 'url' => 'https://symfony.com/doc/7.0/form/embedded.html'],
                                            ['type' => 'doc', 'title' => 'How to Embed a Collection of Forms', 'url' => 'https://symfony.com/doc/7.0/form/form_collections.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Form Events',
                                'subtopics' => [
                                    [
                                        'title' => 'Form Events',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Form Events', 'url' => 'https://symfony.com/doc/7.0/form/events.html'],
                                            ['type' => 'doc', 'title' => 'How to Dynamically Modify Forms Using Form Events', 'url' => 'https://symfony.com/doc/7.0/form/dynamic_form_modification.html'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Form Type Extension',
                                'subtopics' => [
                                    [
                                        'title' => 'Form Type Extension',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Create a Form Type Extension', 'url' => 'https://symfony.com/doc/7.0/form/create_form_type_extension.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Advanced usage of the Service Definitions',
                        'topics' => [
                            [
                                'name' => 'Service Definitions',
                                'subtopics' => [
                                    [
                                        'title' => 'Configurator',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Configure a Service with a Configurator', 'url' => 'https://symfony.com/doc/7.0/service_container/configurators.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Parent Services',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Manage Common Dependencies with Parent Services', 'url' => 'https://symfony.com/doc/7.0/service_container/parent_services.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Lazy Services',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'Lazy Services', 'url' => 'https://symfony.com/doc/7.0/service_container/lazy_services.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'How to Decorate Services',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Decorate Services', 'url' => 'https://symfony.com/doc/7.0/service_container/service_decoration.html'],
                                        ],
                                    ],
                                    [
                                        'title' => 'Inject Values Based on Complex Expressions',
                                        'links' => [
                                            ['type' => 'doc', 'title' => 'How to Inject Values Based on Complex Expressions', 'url' => 'https://symfony.com/doc/7.0/service_container/expression_language.html'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
