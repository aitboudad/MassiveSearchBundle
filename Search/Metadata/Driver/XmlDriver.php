<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\AbstractFileDriver;
use Massive\Bundle\SearchBundle\Search\Factory;
use Metadata\Driver\FileLocatorInterface;

class XmlDriver extends AbstractFileDriver implements DriverInterface
{
    protected $factory;

    public function __construct(Factory $factory, FileLocatorInterface $locator)
    {
        parent::__construct($locator);
        $this->factory = $factory;
    }

    public function getExtension()
    {
        return 'xml';
    }

    public function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $meta = $this->factory->makeIndexMetadata($class->name);
        $xml = simplexml_load_file($file);

        if (count($xml->children()) > 1) {
            throw new \InvalidArgumentException(sprintf(
                'Only one mapping allowed per class in file "%s',
                $file
            ));
        }

        if (count($xml->children()) == 0) {
            throw new \InvalidArgumentException(sprintf('No mapping in file "%s"', $file));
        }

        $mapping = $xml->children();

        $mappedClassName = (string) $mapping->mapping['class'];

        if ($class->getName() !== $mappedClassName) {
            throw new \InvalidArgumentException(sprintf(
                'Mapping in file "%s" does not correspond to class "%s", is a mapping for "%s"',
                $file,
                $class->getName(),
                $mappedClassName
            ));
        }

        $indexName = (string) $mapping->mapping->indexName[0];
        $meta->setIndexName($indexName);

        $idField = (string) $mapping->mapping->idField['name'];
        $meta->setIdField((string) $idField);

        $localeField = (string) $mapping->mapping->localeField['name'];
        $meta->setLocaleField((string) $localeField);

        $titleField = (string) $mapping->mapping->titleField['name'];
        $meta->setTitleField((string) $titleField);

        $urlField = (string) $mapping->mapping->urlField['name'];
        $meta->setUrlField((string) $urlField);

        $descriptionField = (string) $mapping->mapping->descriptionField['name'];
        $meta->setDescriptionField((string) $descriptionField);

        foreach ($mapping->mapping->fields->children() as $field) {
            $fieldName = (string) $field['name'];
            $fieldType = (string) $field['type'];

            $meta->addFieldMapping($fieldName, array(
                'type' => $fieldType
            ));
        }

        return $meta;
    }
}
