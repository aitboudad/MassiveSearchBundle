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

use Massive\Bundle\SearchBundle\Search\Event\SearchEvent;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Metadata\MetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;

/**
 * Search manager is the public API to the search
 * functionality.
 */
class SearchManager implements SearchManagerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var \Metadata\MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Factory
     */
    protected $factory;

    public function __construct(
        Factory $factory,
        AdapterInterface $adapter,
        MetadataFactory $metadataFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'You must pass an object to the %s method, you passed: %s',
                    __METHOD__,
                    var_export($object, true)
                )
            );
        }

        $objectClass = get_class($object);
        $metadata = $this->metadataFactory->getMetadataForClass($objectClass);

        if (null === $metadata) {
            throw new MetadataNotFoundException(
                sprintf(
                    'There is no search mapping for class "%s"',
                    $objectClass
                )
            );
        }

        return $metadata->getOutsideClassMetadata();
    }

    /**
     * {@inheritDoc}
     */
    public function deindex($object)
    {
        $metadata = $this->getMetadata($object);
        $indexName = $metadata->getIndexName();
        $document = $this->objectToDocument($metadata, $object);

        $this->adapter->deindex($document, $indexName);
    }

    /**
     * {@inheritdoc}
     */
    public function index($object)
    {
        $metadata = $this->getMetadata($object);
        $indexName = $metadata->getIndexName();
        $document = $this->objectToDocument($metadata, $object);

        $this->eventDispatcher->dispatch(
            SearchEvents::PRE_INDEX,
            new PreIndexEvent($object, $document, $metadata)
        );

        $this->adapter->index($document, $indexName);
    }

    /**
     * {@inheritDoc}
     */
    public function createSearch($string)
    {
        return new SearchQueryBuilder($this, new SearchQuery($string));
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchQuery $query)
    {
        $indexNames = $query->getIndexes();

        if (null === $indexNames) {
            throw new \Exception('Searching all indexes is not yet implemented');
        }

        $this->eventDispatcher->dispatch(
            SearchEvents::SEARCH,
            new SearchEvent($query)
        );

        $hits = $this->adapter->search($query);

        $reflections = array();
        /** @var QueryHit $hit */
        foreach ($hits as $hit) {
            $document = $hit->getDocument();

            // only throw events for existing documents
            if (!class_exists($document->getClass())) {
                continue;
            }

            // we need a reflection instance of the document in event listeners
            if (!isset($metas[$document->getClass()])) {
                $reflections[$document->getClass()] = new \ReflectionClass($document->getClass());
            }

            $this->eventDispatcher->dispatch(
                SearchEvents::HIT,
                new HitEvent($hit, $reflections[$document->getClass()])
            );
        }

        return $hits;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        $data = array('Adapter' => get_class($this->adapter));
        $data += $this->adapter->getStatus() ? : array();

        return $data;
    }

    /**
     * Map the given object to a new document using the
     * given metadata.
     *
     * @param IndexMetadata $metadata
     * @param object $object
     * @return Document
     */
    private function objectToDocument(IndexMetadata $metadata, $object)
    {
        $idField = $metadata->getIdField();
        $urlField = $metadata->getUrlField();
        $titleField = $metadata->getTitleField();
        $descriptionField = $metadata->getDescriptionField();
        $imageUrlField = $metadata->getImageUrlField();
        $localeField = $metadata->getLocaleField();
        $fieldMapping = $metadata->getFieldMapping();

        $accessor = PropertyAccess::createPropertyAccessor();

        $document = $this->factory->makeDocument();
        $document->setId($accessor->getValue($object, $idField));
        $document->setClass($metadata->getName());

        if ($urlField) {
            $url = $accessor->getValue($object, $urlField);
            if ($url) {
                $document->setUrl($accessor->getValue($object, $urlField));
            }
        }

        if ($titleField) {
            $title = $accessor->getValue($object, $titleField);
            if ($title) {
                $document->setTitle($accessor->getValue($object, $titleField));
            }
        }

        if ($descriptionField) {
            $description = $accessor->getValue($object, $descriptionField);
            if ($description) {
                $document->setDescription($accessor->getValue($object, $descriptionField));
            }
        }

        if ($imageUrlField) {
            $imageUrl = $accessor->getValue($object, $imageUrlField);
            $document->setImageUrl($imageUrl);
        }

        if ($localeField) {
            $locale = $accessor->getValue($object, $localeField);
            $document->setLocale($locale);
        }

        $this->populateDocument($document, $object, $accessor, $fieldMapping);

        return $document;
    }

    /**
     * Populate the Document with the actual values from the object which
     * is being indexed.
     *
     * @param Document $document
     * @param mixed $object
     * @param PropertyAccessor $accessor
     * @param array $fieldMapping
     * @param string $prefix Prefix the document field name (used when called recursively)
     * @throws \InvalidArgumentException
     */
    private function populateDocument($document, $object, $accessor, $fieldMapping, $prefix = '')
    {
        foreach ($fieldMapping as $field => $mapping) {

            if ($mapping['type'] == 'complex') {

                if (!isset($mapping['mapping'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '"complex" field mappings must have an additional array key "mapping" ' .
                            'which contains the mapping for the complex structure in mapping: %s',
                            print_r($mapping, true)
                        )
                    );
                }

                $childObjects = $accessor->getValue($object, $field);

                foreach ($childObjects as $i => $childObject) {
                    $this->populateDocument(
                        $document,
                        $childObject,
                        $accessor,
                        $mapping['mapping']->getFieldMapping(),
                        $prefix . $field . $i
                    );
                }
            } else {
                if (is_array($object)) {
                    $path = '[' . $field . ']';
                } else {
                    $path = $field;
                }

                $value = $accessor->getValue($object, $path);

                if (!is_array($value)) {
                    $document->addField(
                        $this->factory->makeField(
                            $prefix . $field,
                            $value,
                            $mapping['type']
                        )
                    );
                } else {
                    foreach ($value as $key => $itemValue) {
                        $document->addField(
                            $this->factory->makeField(
                                $prefix . $field . $key,
                                $itemValue,
                                $mapping['type']
                            )
                        );
                    }
                }
            }
        }
    }
}
