<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;

/**
 * Convert mapped objects to search documents
 */
class ObjectToDocumentConverter
{
    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(Factory $factory, FieldEvaluator $fieldEvaluator)
    {
        $this->factory = $factory;
        $this->fieldEvaluator = $fieldEvaluator;
    }

    /**
     * Return the field evaluator
     *
     * @return FieldEvaluator
     */
    public function getFieldEvaluator()
    {
        return $this->fieldEvaluator;
    }

    /**
     * Map the given object to a new document using the
     * given metadata.
     *
     * @param IndexMetadata $metadata
     * @param object $object
     * @return Document
     */
    public function objectToDocument(IndexMetadata $metadata, $object)
    {
        $idField = $metadata->getIdField();
        $urlField = $metadata->getUrlField();
        $titleField = $metadata->getTitleField();
        $descriptionField = $metadata->getDescriptionField();
        $imageUrlField = $metadata->getImageUrlField();
        $localeField = $metadata->getLocaleField();
        $fieldMapping = $metadata->getFieldMapping();
        $category = $metadata->getCategoryName();

        $document = $this->factory->makeDocument();
        $document->setId($this->fieldEvaluator->getValue($object, $idField));
        $document->setClass($metadata->getName());

        if ($urlField) {
            $url = $this->fieldEvaluator->getValue($object, $urlField);
            if ($url) {
                $document->setUrl($url);
            }
        }

        if ($titleField) {
            $title = $this->fieldEvaluator->getValue($object, $titleField);
            if ($title) {
                $document->setTitle($title);
            }
        }

        if ($descriptionField) {
            $description = $this->fieldEvaluator->getValue($object, $descriptionField);
            if ($description) {
                $document->setDescription($description);
            }
        }

        if ($imageUrlField) {
            $imageUrl = $this->fieldEvaluator->getValue($object, $imageUrlField);
            $document->setImageUrl($imageUrl);
        }

        if ($localeField) {
            $locale = $this->fieldEvaluator->getValue($object, $localeField);
            $document->setLocale($locale);
        }

        if ($category) {
            $document->setCategory($category);
        }

        $this->populateDocument($document, $object, $fieldMapping);

        return $document;
    }

    /**
     * Populate the Document with the actual values from the object which
     * is being indexed.
     *
     * @param Document $document
     * @param mixed $object
     * @param array $fieldMapping
     * @param string $prefix Prefix the document field name (used when called recursively)
     *
     * @throws \InvalidArgumentException
     */
    private function populateDocument($document, $object, $fieldMapping, $prefix = '')
    {
        foreach ($fieldMapping as $fieldName => $mapping) {
            if (!isset($mapping['field'])) {
                throw new \RuntimeException(sprintf(
                    'Mapping for "%s" does not have "field" key',
                    $fieldName
                ));
            }

            if (!isset($mapping['type'])) {
                throw new \RuntimeException(sprintf(
                    'Mapping for "%s" does not have "type" key',
                    $fieldName
                ));
            }

            if ($mapping['type'] == 'complex') {
                if (!isset($mapping['mapping'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '"complex" field mappings must have an additional array key "mapping" '.
                            'which contains the mapping for the complex structure in mapping: %s',
                            print_r($mapping, true)
                        )
                    );
                }

                $childObjects = $this->fieldEvaluator->getValue($object, new Property($fieldName));

                foreach ($childObjects as $i => $childObject) {
                    $this->populateDocument(
                        $document,
                        $childObject,
                        $mapping['mapping']->getFieldMapping(),
                        $prefix . $fieldName . $i
                    );
                }

                continue;
            }

            $value = $this->fieldEvaluator->getValue($object, $mapping['field']);

            if (!is_array($value)) {
                $document->addField(
                    $this->factory->makeField(
                        $prefix . $fieldName,
                        $value,
                        $mapping['type']
                    )
                );

                continue;
            }

            foreach ($value as $key => $itemValue) {
                $document->addField(
                    $this->factory->makeField(
                        $prefix . $fieldName . $key,
                        $itemValue,
                        $mapping['type']
                    )
                );
            }
        }
    }
}
