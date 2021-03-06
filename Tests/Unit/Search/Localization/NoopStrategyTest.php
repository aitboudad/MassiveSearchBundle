<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\MassiveSearchBundle\Tests\Unit\Search;

use Massive\Bundle\SearchBundle\Search\Localization\NoopStrategy;

class NoopStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function provideStrategy()
    {
        return array(
            array('hello', 'fr', 'hello'),
            array('hello', null, 'hello'),
            array('', 'fr', ''),
        );
    }

    /**
     * @dataProvider provideStrategy
     */
    public function testStrategy($indexName, $locale, $expected)
    {
        $strategy = new NoopStrategy();
        $res = $strategy->localizeIndexName($indexName, $locale);
        $this->assertEquals($expected, $res);
    }
}
