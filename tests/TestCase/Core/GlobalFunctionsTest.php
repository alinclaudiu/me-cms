<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Core;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * GlobalFunctionsTest class
 */
class GlobalFunctionsTest extends TestCase
{
    /**
     * Test for `config()` global function
     * @test
     */
    public function testConfig()
    {
        $this->assertNotEmpty(config());
        $this->assertNotEmpty(config(null));
        $this->assertNull(config('noExisting'));
        $this->assertNull(config(ME_CMS . '.noExisting'));

        Configure::write('exampleKey', 'exampleValue');

        $this->assertEquals('exampleValue', config('exampleKey'));

        Configure::write(ME_CMS . '.exampleKey', 'MeCmsExampleValue');

        $this->assertEquals('MeCmsExampleValue', config('exampleKey'));
    }

    /**
     * Test for `firstImage()` global function
     * @test
     */
    public function testfirstImage()
    {
        $this->assertFalse(firstImage('Text'));

        $this->assertFalse(firstImage('<img src=\'\'>'));
        $this->assertFalse(firstImage('<img src=\'a\'>'));
        $this->assertFalse(firstImage('<img src=\'a.a\'>'));
        $this->assertFalse(firstImage('<img src=\'data:\'>'));
        $this->assertFalse(firstImage('<img src=\'text.txt\'>'));

        $this->assertEquals('image.jpg', firstImage('<img src=\'image.jpg\'>'));
        $this->assertEquals('image.jpeg', firstImage('<img src=\'image.jpeg\'>'));
        $this->assertEquals('image.gif', firstImage('<img src=\'image.gif\'>'));
        $this->assertEquals('image.png', firstImage('<img src=\'image.png\'>'));

        $this->assertEquals('IMAGE.jpg', firstImage('<img src=\'IMAGE.jpg\'>'));
        $this->assertEquals('image.JPG', firstImage('<img src=\'image.JPG\'>'));
        $this->assertEquals('IMAGE.JPG', firstImage('<img src=\'IMAGE.JPG\'>'));

        $this->assertEquals('/image.jpg', firstImage('<img src=\'/image.jpg\'>'));
        $this->assertEquals('subdir/image.jpg', firstImage('<img src=\'subdir/image.jpg\'>'));
        $this->assertEquals('/subdir/image.jpg', firstImage('<img src=\'/subdir/image.jpg\'>'));

        //Some attributes
        $this->assertEquals('image.jpg', firstImage('<img alt=\'\' src=\'image.jpg\'>'));
        $this->assertEquals('image.jpg', firstImage('<img alt="" src="image.jpg">'));
        $this->assertEquals('image.jpg', firstImage('<img alt=\'\' class=\'my-class\' src=\'image.jpg\'>'));
        $this->assertEquals('image.jpg', firstImage('<img alt="" class="my-class" src="image.jpg">'));

        //Two images
        $this->assertEquals('image.jpg', firstImage('<img src=\'image.jpg\' /><img src=\'image.gif\' />'));
        $this->assertEquals('image.jpg', firstImage('<img src=\'image.jpg\'><img src=\'image.gif\'>'));
        $this->assertEquals('image.jpg', firstImage('<img src=\'image.jpg\'> Text <img src=\'image.gif\'>'));

        $expected = 'http://example.com/image.jpg';

        $this->assertEquals($expected, firstImage('<img src=\'http://example.com/image.jpg\'>'));
        $this->assertEquals($expected, firstImage('<img src=\'http://example.com/image.jpg\' />'));
        $this->assertEquals($expected, firstImage('<img src=\'http://example.com/image.jpg\' />Text'));
        $this->assertEquals($expected, firstImage('<img src=\'http://example.com/image.jpg\' /> Text'));

        $this->assertEquals('ftp://example.com/image.jpg', firstImage('<img src=\'ftp://example.com/image.jpg\'>'));
        $this->assertEquals('https://example.com/image.jpg', firstImage('<img src=\'https://example.com/image.jpg\'>'));
        $this->assertEquals('http://www.example.com/image.jpg', firstImage('<img src=\'http://www.example.com/image.jpg\'>'));
    }
}
