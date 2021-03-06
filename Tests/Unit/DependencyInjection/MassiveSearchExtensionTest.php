<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\DependencyInjection;

use Massive\Bundle\SearchBundle\DependencyInjection\MassiveSearchExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class MassiveSearchExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        $this->container->setParameter('kernel.bundles', array('Massive\Bundle\SearchBundle\MassiveSearchBundle'));
        $this->container->setParameter('kernel.root_dir', __DIR__ . '/../../Resources/app');

        return array(
            new MassiveSearchExtension()
        );
    }

    public function testLoad()
    {
        $this->load();
    }

    public function provideLocalizationStrategy()
    {
        return array(
            array(
                array(
                ),
                'noop',
                array(
                    'localization_strategy' => 'noop',
                ),
                'noop',
                array(
                    'localization_strategy' => 'index',
                ),
                'index',
            ),
        );
    }

    /**
     * @dataProvider provideLocalizationStrategy
     */
    public function testLocalizationStrategy($config, $expectedStrategy)
    {
        $this->load($config);

        $aliasTarget = $this->container->getAlias('massive_search.localization_strategy');
        $this->assertEquals(
            'massive_search.localization_strategy.' . $expectedStrategy,
            $aliasTarget
        );
    }

    public function provideAdapterConfig()
    {
        return array(
            array(
                'zend_lucene',
                array(
                    'basepath' => 'foobar',
                    'hide_index_exception' => true
                ),
                array(
                    'massive_search.adapter.zend_lucene.basepath' => 'foobar',
                    'massive_search.adapter.zend_lucene.hide_index_exception' => true,
                ),
            ),
            array(
                'zend_lucene',
                array(
                ),
                array(
                    'massive_search.adapter.zend_lucene.basepath' => '%kernel.root_dir%/data',
                    'massive_search.adapter.zend_lucene.hide_index_exception' => false,
                ),
            ),
            array(
                'elastic',
                array(
                    'hosts' => array(
                        'localhost:8081',
                        'http://www.example.com:9091',
                    ),
                ),
                array(
                    'massive_search.adapter.elastic.hosts' => array(
                        'localhost:8081',
                        'http://www.example.com:9091',
                    )
                )
            ),
            array(
                'elastic',
                array(
                ),
                array(
                    'massive_search.adapter.elastic.hosts' => array('localhost:9200'),
                )
            ),
        );
    }

    /**
     * @dataProvider provideAdapterConfig
     */
    public function testAdapterConfig($adapter, $config, $expectedParameters)
    {
        $config = array(
            'adapter' => $adapter,
            'adapters' => array(
                $adapter => $config
            )
        );

        $this->load($config);

        foreach ($expectedParameters as $expectedKey => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $this->container->getParameter($expectedKey)
            );
        }

        $serviceId = 'massive_search.adapter.' . $adapter;
        $this->container->get($serviceId);
    }
}
