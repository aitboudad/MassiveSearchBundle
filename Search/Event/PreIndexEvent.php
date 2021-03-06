<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Preindex event is fired before a document is indexed
 */
class PreIndexEvent extends Event
{
    /**
     * The object, which has been indexed
     * @var object
     */
    protected $subject;

    /**
     * The search document, which is the result of the indexing
     * @var Document
     */
    protected $document;

    /**
     * The metadata, on which the index process has been based
     * @var IndexMetadataInterface
     */
    protected $metadata;

    /**
     * @param $subject
     * @param Document $document
     * @param IndexMetadataInterface $metadata
     */
    public function __construct($subject, Document $document, IndexMetadataInterface $metadata)
    {
        $this->subject = $subject;
        $this->document = $document;
        $this->metadata = $metadata;
    }

    /**
     * Returns the indexed subject
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the document, which is the result of the indexed object
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns the metadata based on which the indexing was done
     * @return IndexMetadataInterface
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
