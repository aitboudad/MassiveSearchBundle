<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

use Metadata\ClassMetadata;

/**
 * Metadata for searchable objects
 */
class IndexMetadata extends ClassMetadata implements IndexMetadataInterface
{
    /**
     * @var string
     */
    private $indexName;

    /**
     * @var array
     */
    private $fieldMapping = array();

    /**
     * @var string
     */
    private $idField;

    /**
     * @var string
     */
    private $urlField;

    /**
     * @var string
     */
    private $titleField;

    /**
     * @var string
     */
    private $descriptionField;

    /**
     * @var string
     */
    private $imageUrlField;

    /**
     * @var string
     */
    private $localeField;

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * {@inheritDoc}
     */
    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * {@inheritDoc}
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * {@inheritDoc}
     */
    public function addFieldMapping($name, $mapping)
    {
        $this->fieldMapping[$name] = $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdField()
    {
        return $this->idField;
    }

    /**
     * {@inheritDoc}
     */
    public function setIdField($idField)
    {
        $this->idField = $idField;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlField()
    {
        return $this->urlField;
    }

    /**
     * {@inheritDoc}
     */
    public function setUrlField($urlField)
    {
        $this->urlField = $urlField;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitleField()
    {
        return $this->titleField;
    }

    /**
     * {@inheritDoc}
     */
    public function setTitleField($titleField)
    {
        $this->titleField = $titleField;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescriptionField()
    {
        return $this->descriptionField;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescriptionField($descriptionField)
    {
        $this->descriptionField = $descriptionField;
    }

    /**
     * {@inheritDoc}
     */
    public function getImageUrlField()
    {
        return $this->imageUrlField;
    }

    /**
     * {@inheritDoc}
     */
    public function setImageUrlField($imageUrlField)
    {
        $this->imageUrlField = $imageUrlField;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocaleField()
    {
        return $this->localeField;
    }

    /**
     * {@inheritDoc}
     */
    public function setLocaleField($localeField)
    {
        $this->localeField = $localeField;
    }
}
