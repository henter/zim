<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Zim\Config\Config;

class ConfigTest extends TestCase
{
    public function testConfig()
    {
        $arr = [
            'a' => 'b',
            'c' => 'd',
            'e' => [
                'f' => [
                    'g' => 'h',
                ],
                'i' => 'j'
            ],
            'aa' => ['bb', 'cc']
        ];
        $c = new Config($arr);
        $this->assertEquals($arr, $c->all());
        $this->assertEquals('b', $c->get('a'));
        $this->assertEquals('b', $c['a']);
        $this->assertEquals('d', $c->get('c'));
        $this->assertEquals(['f' => ['g' => 'h'], 'i' => 'j'], $c->get('e'));
        $this->assertEquals(['g' => 'h'], $c->get('e.f'));
        $this->assertEquals('h', $c->get('e.f.g'));
        $this->assertEquals('j', $c->get('e.i'));

        $this->assertEquals(['a' => 'b', 'c' => 'd'], $c->get(['a', 'c']));
        $this->assertEquals(['a' => 'b', 'c' => 'd'], $c->getMany(['a', 'c']));

        $this->assertTrue(isset($c['a']));
        $this->assertTrue($c->has('a'));
        $this->assertTrue($c->has('c'));
        $this->assertTrue($c->has('e.i'));
        $this->assertFalse($c->has('not_exists'));

        $c->set('k', 'v');
        $this->assertEquals('v', $c->get('k'));
        $c['k'] = 'vv';
        $this->assertEquals('vv', $c['k']);
        unset($c['k']);
        $this->assertNull($c['k']);
        $this->assertNull($c->get('k'));

        $c->set(['o' => 'p']);
        $this->assertEquals('p', $c->get('o'));

        $c->prepend('e.f', 'test_prepend');
        $this->assertEquals('test_prepend', $c->get('e.f')[0]);
        $c->push('aa', 'test_push');
        $this->assertEquals('test_push', \end($c->get('aa')));
        $c->push('e.f', 'test_push');
        $this->assertEquals('test_push', \end($c->get('e.f')));
    }
}
