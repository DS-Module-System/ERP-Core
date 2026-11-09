<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2020-10-06
 * Time: 16:44
 */

namespace App\Command\Core;


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'core:create-module')]
class CreateCoreModule extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new core module.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a core module...')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityName = $input->getArgument('entity');

        if (str_contains((string) $entityName, '\\')) {
            $entityName = explode('\\', (string) $entityName)[1];
        }

        if (!file_exists(__DIR__ . '/../../Entity/' . $entityName . '.php')) {
            throw new \LogicException('Entity not exist src/Entity/' . $entityName . '.php');
        }

        $controllerName = $entityName . 'Controller';
        $repositoryName = $entityName . 'Repository';
        $searchFormName = $entityName . 'SearchForm';
        $formName = $entityName . 'Form';
        $templateFolderName = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst((string) $entityName)));
        $mainRoute = str_replace('_', '-', $templateFolderName) . 's';

        if((float)phpversion() < 8) {
            die('stop');
        } else {
            $controllerTemplate = file_get_contents(__DIR__ . '/ControllerTemplate_php8.txt');
        }

        $replaceWords = [
            '{#CONTROLLER_NAME#}' => $controllerName,
            '{#REPOSITORY_NAME#}' => $repositoryName,
            '{#ENTITY_NAME#}' => $entityName,
            '{#MODULE_NAME#}' => lcfirst((string) $entityName),
            '{#SEARCH_FORM_NAME#}' => $searchFormName,
            '{#FORM_NAME#}' => $formName,
            '{#MAIN_ROUTE#}' => $mainRoute,
            '{#TEMPLATE_FORDER_NAME#}' => $templateFolderName,
        ];

        $output->writeln([
            'Create Controller',
            '============',
            '',
        ]);

        $controllerTemplate = str_replace(array_keys($replaceWords), array_values($replaceWords), $controllerTemplate);
        file_put_contents(__DIR__.'/../../Controller/'.$controllerName.'.php', $controllerTemplate);


        $fields = [];
        $fieldTypes = [];
        $formFields = [];
        $useClass = [];

        $reflectionClass = new \ReflectionClass('App\\Entity\\' . $entityName);

        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->getName() == 'id') {
                continue;
            }

            $annotationColumns = $property->getAttributes(\Doctrine\ORM\Mapping\Column::class);

            if (empty($annotationColumns)) {
                continue;
            }

            $annotationColumn = $annotationColumns[0];

            $propertyType = $property->getType();
            $columnType = null;
            if($propertyType instanceof \ReflectionNamedType) {
                $columnType = $propertyType->getName();
            }

            $columnLength = 0;

            $columnArguments = $annotationColumn->getArguments();
            if(isset($columnArguments['type'])) {
                $columnType = $columnArguments['type'];
            }

            $columnNullable = false;
            if(isset($columnArguments['nullable'])) {
                $columnNullable = $columnArguments['nullable'];
            }

            if(isset($columnArguments['length'])) {
                $columnLength = $columnArguments['length'];
            }

            $fields[] = $property->getName();
            $fieldTypes[] = $columnType;

            $label = "'label' => '" . lcfirst((string) $entityName) . "." . $property->getName() . "',";
            $labelAttr = '';
            $widget = '';
            $html5 = '';
            $attr = '';
            $emptyData = '';
            $currency = '';
            $scale = '';
            $inputType = '';
            $emptyData = null;

            $fieldType = '';
            if ($columnType == 'string' || $columnType == 'integer' || $columnType == 'int') {
                $fieldType = 'TextType';
                $useClass[] = 'use Symfony\Component\Form\Extension\Core\Type\TextType;';
            } elseif ($columnType == 'date_immutable') {
                $fieldType = 'DateType';
                $useClass[] = 'use Symfony\Component\Form\Extension\Core\Type\DateType;';
                $widget = "'widget' => 'single_text',";
                $html5 = "'html5' => false,";
                $attr = "'attr' => ['data-datepicker' => '{}'],";
                $inputType = "'input'  => 'datetime_immutable',";
            } elseif ($columnType == 'DateTimeImmutable' || $columnType == 'datetime_immutable') {
                $fieldType = 'DateTimeType';
                $useClass[] = 'use Symfony\Component\Form\Extension\Core\Type\DateTimeType;';
                $widget = "'widget' => 'single_text',";
                $html5 = "'html5' => false,";
                $attr = "'attr' => ['data-datepicker' => '{\"enableTime\": true}'],";
                $inputType = "'input'  => 'datetime_immutable',";
            } elseif ($columnType == 'text') {
                $fieldType = 'TextareaType';
                $useClass[] = 'use Symfony\Component\Form\Extension\Core\Type\TextareaType;';
            } elseif ($columnType == 'decimal') {
                $fieldType = 'MoneyType';
                $useClass[] = 'use Symfony\Component\Form\Extension\Core\Type\MoneyType;';
                $currency = "'currency' => false,";
                if(isset($columnArguments['scale'])) {
                    $scale = "'scale' => {$columnArguments['scale']},";
                }
            } elseif ($columnType == 'boolean' || $columnType == 'bool') {
                $fieldType = 'CheckboxType';
                $useClass[] = 'use Symfony\Component\Form\Extension\Core\Type\CheckboxType;';
                $labelAttr = "'label_attr' => ['data-on-label' => 'Yes','data-off-label' => 'No',],";
                $attr = "'attr' => ['data-switch' => 'success',],";
            } else {
                continue;
            }

            $required = '';
            $constraints = [];
            if ($columnNullable === false) {

                if(in_array($columnType, ['date_immutable', 'DateTimeImmutable', 'datetime_immutable'])) {
                    $constraints[] = "new NotBlank(),";
                    $useClass[] = 'use Symfony\Component\Validator\Constraints\NotBlank;';
                } else {
                    $constraints[] = "new NotBlank(),";
                    $useClass[] = 'use Symfony\Component\Validator\Constraints\NotBlank;';
                }
                $required = "'required' => true,";

                if(in_array($columnType, ['string', 'text'])) {
                    $emptyData = "'empty_data' => '',";
                } elseif (in_array($columnType, ['integer', 'int'])) {
                    $emptyData = "'empty_data' => 0,";
                } elseif (in_array($columnType, ['date_immutable', 'DateTimeImmutable', 'datetime_immutable'])) {
                    $emptyData = "'empty_data' => '',";
                } elseif (in_array($columnType, ['decimal'])) {
                    $emptyData = "'empty_data' => '0',";
                }

            } else {
                $required = "'required' => false,";
            }

            if (in_array($columnType, ['boolean', 'bool'])) {
                $constraints = [];
                $required = "'required' => false,";
//                $emptyData = "'empty_data' => false,";
            }



            if ($columnLength > 0) {
                $constraints[] = <<<EOD
new Length([
                        'max' => {$columnLength},
                    ]),
EOD;
                $useClass[] = 'use Symfony\Component\Validator\Constraints\Length;';
            }

            if (!empty($constraints)) {
                $constraints = implode("\r\n                    ", $constraints);
                $constraints = <<<EOD
'constraints' => [
                    {$constraints}
                ],
EOD;
            }

            $options = [];
            $options[] = '                ' . $label;
            $options[] = '                ' . $required;

            if (!empty($labelAttr)) {
                $options[] = '                ' . $labelAttr;
            }
            if (!empty($constraints)) {
                $options[] = '                ' . $constraints;
            }
            if (!empty($widget)) {
                $options[] = '                ' . $widget;
            }
            if (!empty($inputType)) {
                $options[] = '                ' . $inputType;
            }
            if (!empty($currency)) {
                $options[] = '                ' . $currency;
            }
            if (!empty($scale)) {
                $options[] = '                ' . $scale;
            }
            if (!empty($html5)) {
                $options[] = '                ' . $html5;
            }
            if (!empty($attr)) {
                $options[] = '                ' . $attr;
            }
            if (!empty($emptyData)) {
//                $options[] = '                ' . $emptyData;
            }

            $options = "[\r\n" . implode("\r\n", $options);
            $options .= "\r\n            ]";

            $formFields[] = <<<EOD
            ->add('{$property->getName()}', {$fieldType}::class, {$options})
EOD;


        }

        $useClass = array_unique($useClass);
        $replaceWords['{#USE_CLASS#}'] = implode("\r\n", $useClass);
        $replaceWords['{#FORM_FIELDS#}'] = implode("\r\n", $formFields);

        $output->writeln([
            'Create Form',
            '============',
            '',
        ]);

        $formTemplate = file_get_contents(__DIR__ . '/FormTemplate.txt');
        $formTemplate = str_replace(array_keys($replaceWords), array_values($replaceWords), $formTemplate);
        file_put_contents(__DIR__ . '/../../Form/' . $entityName . 'Form.php', $formTemplate);

        $output->writeln([
            'Create Search Form',
            '============',
            '',
        ]);

        $replaceWords['{#FIRST_FIELD_NAME#}'] = reset($fields);

        $searchFormTemplate = file_get_contents(__DIR__ . '/SearchFormTemplate.txt');
        $searchFormTemplate = str_replace(array_keys($replaceWords), array_values($replaceWords), $searchFormTemplate);
        file_put_contents(__DIR__ . '/../../Form/' . $entityName . 'SearchForm.php', $searchFormTemplate);

        $tableFields = [];
        foreach ($fields as $key => $field) {
            $name = '' . lcfirst((string) $entityName) . '.' . $field;
            $field = lcfirst(
                preg_replace_callback('/(^|_|\.)+(.)/', fn($match) => ('.' === $match[1] ? '_' : '') . strtoupper((string) $match[2]), $field)
            );

            $type = $fieldTypes[$key];
            if (in_array($type, ['date', 'date_immutable'])) {
                $tableFields[] = <<<EOD
"{$field}": {
                "label": "{$name}",
                "type": "date",
            },
EOD;
            } elseif (in_array($type, ['datetime', 'datetime_immutable', 'DateTimeImmutable'])) {
                    $tableFields[] = <<<EOD
"{$field}": {
                "label": "{$name}",
                "type": "datetime",
            },
EOD;
            } elseif (in_array($type, ['boolean', 'bool'])) {
                    $tableFields[] = <<<EOD
"{$field}": {
                "label": "{$name}",
                "type": "boolean",
            },
EOD;
            } else {
                $tableFields[] = <<<EOD
"{$field}": {
                "label": "{$name}",
            },
EOD;
            }

        }
        $tableFields = implode("\r\n            ", $tableFields);

        $dir = __DIR__ . '/../../../templates/' . $templateFolderName;
        if (!file_exists($dir)) {
            mkdir($dir, 0776, true);
        }

        $replaceWords['{#TABLE_FIELDS#}'] = $tableFields;

        $output->writeln([
            'Create index.html.twig',
            '============',
            '',
        ]);

        $indexTemplate = file_get_contents(__DIR__ . '/index-template.txt');
        $indexTemplate = str_replace(array_keys($replaceWords), array_values($replaceWords), $indexTemplate);
        file_put_contents($dir . '/index.html.twig', $indexTemplate);

        $output->writeln([
            'Create form.html.twig',
            '============',
            '',
        ]);

        $createTemplate = file_get_contents(__DIR__ . '/form-template.txt');
        $createTemplate = str_replace(array_keys($replaceWords), array_values($replaceWords), $createTemplate);
        file_put_contents($dir . '/form.html.twig', $createTemplate);


        $output->writeln([
            'Add method pagination in repository',
            '============',
            '',
        ]);

        $getPaginationQueryFuntion = <<<EOD
    public function getPaginatedQuery(array \$searchFormData = [], array \$languages = []): Query
    {
        \$qb = \$this->createQueryBuilder('t');
        
        if (!empty(\$languages)) {
            foreach (\$languages as \$language) {
                \$qb->leftJoin('t.lngs', \$language, "WITH", "{\$language}.lng='{\$language}'")
                    ->addSelect(\$language);
            }
        }

        return \$qb->getQuery();
    }
EOD;

        $repositoryFile = file_get_contents(__DIR__ . '/../../Repository/' . $entityName . 'Repository.php');

        if (!str_contains($repositoryFile, 'getPaginatedQuery')) {

            $pos = strrpos($repositoryFile, '}');

            if ($pos !== false) {
                $repositoryFile = substr_replace($repositoryFile, "\r\n" . $getPaginationQueryFuntion . "\r\n}", $pos,
                    strlen('}'));
                file_put_contents(__DIR__ . '/../../Repository/' . $entityName . 'Repository.php',
                    $repositoryFile);
            }

            $repositoryFile = file_get_contents(__DIR__ . '/../../Repository/' . $entityName . 'Repository.php');
            if(!str_contains($repositoryFile, 'use Doctrine\ORM\Query;')) {
                $repositoryFile = str_replace('use Doctrine\Persistence\ManagerRegistry;', "use Doctrine\ORM\Query;\r\nuse Doctrine\Persistence\ManagerRegistry;", $repositoryFile);
                file_put_contents(__DIR__ . '/../../Repository/' . $entityName . 'Repository.php',
                    $repositoryFile);
            }
        }

        $output->writeln([
            'Add Core Menu Item',
            '============',
            '',
        ]);

        $menuFile = file_get_contents(__DIR__ . '/../../Menu/Builder.php');
        $leftMenuLabelKey = lcfirst((string) $entityName);
        $menuItem = <<<EOD
\$menu->addChild('leftMenu.{$leftMenuLabelKey}', [
            'route' => '{$templateFolderName}_list',
            'extras' => [
                'routes' => [
                    '{$templateFolderName}_edit',
                    '{$templateFolderName}_create'
                ],
            ],
        ]);
        
        return \$menu;
EOD;

        $menuFile = str_replace('return $menu;', $menuItem, $menuFile);
        file_put_contents(__DIR__ . '/../../Menu/Builder.php', $menuFile);

        return 0;
    }
}
