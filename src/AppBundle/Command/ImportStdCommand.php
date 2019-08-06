<?php

namespace AppBundle\Command;

use DateTime;
use Doctrine\ORM\ORMException;
use Exception;
use GlobIterator;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Card;

/**
 * Data import command.
 * Class ImportStdCommand
 * @package AppBundle\Command
 */
class ImportStdCommand extends ContainerAwareCommand
{
    /* @var $em EntityManager */
    private $em;

    /* @var $output OutputInterface */
    private $output;

    /**
     * @var array
     */
    private $collections = [];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
        ->setName('app:import:std')
        ->setDescription(
            'Import cards data file in json format from a copy of '.
            'https://github.com/fiskhandlarn/doomtrooperdb-json-data'
        )
        ->addArgument(
            'path',
            InputArgument::REQUIRED,
            'Path to the repository'
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->output = $output;

        /* @var $helper QuestionHelper */
        $helper = $this->getHelper('question');

        // factions

        $output->writeln("Importing Factions...");
        $factionsFileInfo = $this->getFileInfo($path, 'factions.json');
        $imported = $this->importFactionsJsonFile($factionsFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Faction');
        $output->writeln("Done.");

        // types

        $output->writeln("Importing Types...");
        $typesFileInfo = $this->getFileInfo($path, 'types.json');
        $imported = $this->importTypesJsonFile($typesFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Type');
        $output->writeln("Done.");

        // expansions

        $output->writeln("Importing Expansions...");
        $expansionsFileInfo = $this->getFileInfo($path, 'expansions.json');
        $imported = $this->importExpansionsJsonFile($expansionsFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Expansion');
        $output->writeln("Done.");

        // cards

        $output->writeln("Importing Cards...");
        $fileSystemIterator = $this->getFileSystemIterator($path);
        $rawData = [];
        foreach ($fileSystemIterator as $fileinfo) {
            $baseName = $fileinfo->getBasename('.json');
            $rawData[$baseName] = $this->readCardsFromJsonFile($fileinfo);
        }

        $imported = [];
        foreach ($rawData as $cardsData) {
            $imported = array_merge($imported, $this->importCards($cardsData));
        }

        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $output->writeln("Done.");
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importFactionsJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach ($list as $data) {
            $faction = $this->getEntityFromData('AppBundle\\Entity\\Faction', $data, [
                'code',
                'name',
                'octgn_id',
            ], [], []);
            if ($faction) {
                $result[] = $faction;
                $this->em->persist($faction);
            }
        }

        return $result;
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importTypesJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach ($list as $data) {
            $type = $this->getEntityFromData('AppBundle\\Entity\\Type', $data, [
                    'code',
                    'name'
            ], [], []);
            if ($type) {
                $result[] = $type;
                $this->em->persist($type);
            }
        }

        return $result;
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importExpansionsJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];
        $position = 0;
        $expansionsData = $this->getDataFromFile($fileinfo);
        foreach ($expansionsData as $expansionData) {
            $expansionData['position'] = $position;
            $expansion = $this->getEntityFromData('AppBundle\Entity\Expansion', $expansionData, [
                    'code',
                    'name',
                    'position',
                    'size'
            ], [], []);
            if ($expansion) {
                $result[] = $expansion;
                $this->em->persist($expansion);
            }
            $position++;
        }

        return $result;
    }

    /**
     * @param array $cardsData
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importCards(array $cardsData)
    {
        $result = [];
        foreach ($cardsData as $cardData) {
            $card = $this->getEntityFromData('AppBundle\Entity\Card', $cardData, [
                'code',
                'deck_limit',
                'name',
                'rarity',
            ], [
                'expansion_code',
                'faction_code',
                'type_code'
            ], [
                'armor',
                'clarification_text',
                'fight',
                'flavor',
                'illustrator',
                'image_url',
                'notes',
                'octgn_id',
                'post_play',
                'shoot',
                'text',
                'value',
            ]);
            if ($card) {
                $result[] = $card;
                $this->em->persist($card);
            }
        }

        return $result;
    }

    /**
     * @param $entity
     * @param $entityName
     * @param $fieldName
     * @param $newJsonValue
     * @throws Exception
     */
    protected function copyFieldValueToEntity($entity, $entityName, $fieldName, $newJsonValue)
    {
        $metadata = $this->em->getClassMetadata($entityName);
        $type = $metadata->fieldMappings[$fieldName]['type'];

        // new value, by default what json gave us is the correct typed value
        $newTypedValue = $newJsonValue;

        // current value, by default the json, serialized value is the same as what's in the entity
        $getter = 'get'.ucfirst($fieldName);
        $currentJsonValue = $currentTypedValue = $entity->$getter();

        // if the field is a data, the default assumptions above are wrong
        if (in_array($type, ['date', 'datetime'])) {
            if ($newJsonValue !== null) {
                $newTypedValue = new DateTime($newJsonValue);
            }
            if ($currentTypedValue !== null) {
                switch ($type) {
                    case 'date':
                        /* @var DateTime $currentTypedValue*/
                        $currentJsonValue = $currentTypedValue->format('Y-m-d');
                        break;
                    case 'datetime':
                        /* @var DateTime $currentTypedValue*/
                        $currentJsonValue = $currentTypedValue->format('Y-m-d H:i:s');
                }
            }
        }

        $different = ($currentJsonValue !== $newJsonValue);
        if ($different) {
            $this->output->writeln(
                "Changing the <info>$fieldName</info> of <info>"
                . $entity->toString()
                . "</info> ($currentJsonValue => $newJsonValue)"
            );
            $setter = 'set'.ucfirst($fieldName);
            $entity->$setter($newTypedValue);
        }
    }

    /**
     * @param $entity
     * @param $entityName
     * @param $data
     * @param $key
     * @param bool $isMandatory
     * @throws Exception
     */
    protected function copyKeyToEntity($entity, $entityName, $data, $key, $isMandatory = true)
    {
        $metadata = $this->em->getClassMetadata($entityName);

        if (!key_exists($key, $data)) {
            if ($isMandatory) {
                throw new Exception("Missing key [$key] in ".json_encode($data));
            } else {
                $data[$key] = null;
            }
        }
        $value = $data[$key];

        if (!key_exists($key, $metadata->fieldNames)) {
            throw new Exception("Missing column [$key] in entity ".$entityName);
        }
        $fieldName = $metadata->fieldNames[$key];

        $this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value);
    }

    /**
     * @param $entityName
     * @param $data
     * @param $mandatoryKeys
     * @param $foreignKeys
     * @param $optionalKeys
     * @return object|null
     * @throws Exception
     */
    protected function getEntityFromData($entityName, $data, $mandatoryKeys, $foreignKeys, $optionalKeys)
    {
        if (!key_exists('code', $data)) {
            throw new Exception("Missing key [code] in ".json_encode($data));
        }

        $entity = $this->em->getRepository($entityName)->findOneBy(['code' => $data['code']]);
        if (!$entity) {
            $entity = new $entityName();
        }
        $orig = $entity->serialize();

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, true);
        }

        foreach ($optionalKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, false);
        }

        foreach ($foreignKeys as $key) {
            $foreignEntityShortName = ucfirst(str_replace('_code', '', $key));

            if (!key_exists($key, $data)) {
                throw new Exception("Missing key [$key] in ".json_encode($data));
            }

            $foreignCode = $data[$key];
            if (!key_exists($foreignEntityShortName, $this->collections)) {
                throw new Exception("No collection for [$foreignEntityShortName] in ".json_encode($data));
            }

            if (is_array($foreignCode)) {
                // factions
                // TODO change to array
                $foreignCode = $foreignCode[0];
            }

            if (!key_exists($foreignCode, $this->collections[$foreignEntityShortName])) {
                throw new Exception("Invalid code [$foreignCode] for key [$key] in ".json_encode($data));
            }
            $foreignEntity = $this->collections[$foreignEntityShortName][$foreignCode];

            $getter = 'get'.$foreignEntityShortName;
            if (!$entity->$getter() || $entity->$getter()->getId() !== $foreignEntity->getId()) {
                $this->output->writeln("Changing the <info>$key</info> of <info>".$entity->toString()."</info>");
                $setter = 'set'.$foreignEntityShortName;
                $entity->$setter($foreignEntity);
            }
        }

        // special case for Card
        if ($entityName === 'AppBundle\Entity\Card' &&
            ('Warrior' === $entity->getType()->getName() || 'Warzone' === $entity->getType()->getName())) {
            $this->importSFAVData($entity, $data);
        }

        if ($entity->serialize() !== $orig) {
            return $entity;
        }

        return null;
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importSFAVData(Card $card, $data)
    {
        $mandatoryKeys = [
            'armor',
            'fight',
            'shoot',
            'value',
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return mixed
     * @throws Exception
     */
    protected function getDataFromFile(SplFileInfo $fileinfo)
    {
        $file = $fileinfo->openFile('r');
        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $lines = [];
        foreach ($file as $line) {
            if ($line !== false) {
                $lines[] = $line;
            }
        }
        $content = implode('', $lines);

        $data = json_decode($content, true);

        if ($data === null) {
            throw new Exception(
                "File ["
                . $fileinfo->getPathname()
                . "] contains incorrect JSON (error code "
                . json_last_error()
                . ")"
            );
        }

        return $data;
    }

    /**
     * @param $path
     * @param $filename
     * @return SplFileInfo
     * @throws Exception
     */
    protected function getFileInfo($path, $filename)
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $filepath = "$path/$filename";

        if (!$fs->exists($filepath)) {
            throw new Exception("No $filename file found at [$path]");
        }

        return new SplFileInfo($filepath);
    }

    /**
     * @param $path
     * @return GlobIterator
     * @throws Exception
     */
    protected function getFileSystemIterator($path)
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $directory = 'cards';

        if (!$fs->exists("$path/$directory")) {
            throw new Exception("No '$directory' directory found at [$path]");
        }

        $iterator = new GlobIterator("$path/$directory/*.json");

        if (!$iterator->count()) {
            throw new Exception("No json file found at [$path/set]");
        }

        return $iterator;
    }

    /**
     * @param $entityShortName
     */
    protected function loadCollection($entityShortName)
    {
        $this->collections[$entityShortName] = [];

        $entities = $this->em->getRepository('AppBundle:'.$entityShortName)->findAll();

        foreach ($entities as $entity) {
            $this->collections[$entityShortName][$entity->getCode()] = $entity;
        }
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return array
     * @throws Exception
     */
    protected function readCardsFromJsonFile(SplFileInfo $fileInfo)
    {
        $code = $fileInfo->getBasename('.json');

        $expansion = $this->em->getRepository('AppBundle:Expansion')->findOneBy(['code' => $code]);
        if (!$expansion) {
            throw new Exception("Unable to find Expansion [$code]");
        }

        return $this->getDataFromFile($fileInfo);
    }

    /**
     * @param array $rawData
     * @return array
     */
    protected function extractCardNamesWithMultipleInstances(array $rawData)
    {
        $names = [];
        foreach ($rawData as $cardsData) {
            foreach ($cardsData as $cardData) {
                $name = $cardData['name'];
                if (array_key_exists($name, $names)) {
                    $names[$name] = $names[$name] + 1;
                } else {
                    $names[$name] = 1;
                }
            }
        }

        return array_keys(array_filter($names, function ($value) {
            return ($value > 1);
        }));
    }
}
