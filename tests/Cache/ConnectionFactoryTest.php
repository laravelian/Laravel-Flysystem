<?php

/**
 * This file is part of Laravel Flysystem by Graham Campbell.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace GrahamCampbell\Tests\Flysystem\Cache;

use Mockery;
use GrahamCampbell\Flysystem\Cache\ConnectionFactory;
use GrahamCampbell\TestBench\Classes\AbstractTestCase;

/**
 * This is the cache connection factory test class.
 *
 * @package    Laravel-Flysystem
 * @author     Graham Campbell
 * @copyright  Copyright 2014 Graham Campbell
 * @license    https://github.com/GrahamCampbell/Laravel-Flysystem/blob/master/LICENSE.md
 * @link       https://github.com/GrahamCampbell/Laravel-Flysystem
 */
class ConnectionFactoryTest extends AbstractTestCase
{
    public function testMake()
    {
        $manager = Mockery::mock('GrahamCampbell\Flysystem\Managers\FlysystemManager');

        $factory = $this->getMockedFactory($manager);

        $return = $factory->make(array('name' => 'foo', 'driver' => 'illuminate', 'connector' => 'redis'), $manager);

        $this->assertInstanceOf('League\Flysystem\CacheInterface', $return);
    }

    public function testCreateIlluminateConnector()
    {
        $manager = Mockery::mock('GrahamCampbell\Flysystem\Managers\FlysystemManager');

        $factory = $this->getConnectionFactory();

        $return = $factory->createConnector(array('name' => 'foo', 'driver' => 'illuminate', 'connector' => 'redis'), $manager);

        $this->assertInstanceOf('GrahamCampbell\Flysystem\Cache\IlluminateConnector', $return);
    }

    public function testCreateAdapterConnector()
    {
        $manager = Mockery::mock('GrahamCampbell\Flysystem\Managers\FlysystemManager');

        $factory = $this->getConnectionFactory();

        $return = $factory->createConnector(array('name' => 'foo', 'driver' => 'adapter', 'adapter' => 'local'), $manager);

        $this->assertInstanceOf('GrahamCampbell\Flysystem\Cache\AdapterConnector', $return);
    }

    public function testCreateEmptyDriverConnector()
    {
        $manager = Mockery::mock('GrahamCampbell\Flysystem\Managers\FlysystemManager');

        $factory = $this->getConnectionFactory();

        $return = null;

        try {
            $factory->createConnector(array(), $manager);
        } catch (\Exception $e) {
            $return = $e;
        }

        $this->assertInstanceOf('InvalidArgumentException', $return);
    }

    public function testCreateUnsupportedDriverConnector()
    {
        $manager = Mockery::mock('GrahamCampbell\Flysystem\Managers\FlysystemManager');

        $factory = $this->getConnectionFactory();

        $return = null;

        try {
            $factory->createConnector(array('driver' => 'unsupported'), $manager);
        } catch (\Exception $e) {
            $return = $e;
        }

        $this->assertInstanceOf('InvalidArgumentException', $return);
    }

    protected function getConnectionFactory()
    {
        $cache = Mockery::mock('Illuminate\Cache\CacheManager');

        return new ConnectionFactory($cache);
    }

    protected function getMockedFactory($manager)
    {
        $cache = Mockery::mock('Illuminate\Cache\CacheManager');

        $mock = Mockery::mock('GrahamCampbell\Flysystem\Cache\ConnectionFactory[createConnector]', array($cache));

        $connector = Mockery::mock('GrahamCampbell\Flysystem\Cache\IlluminateConnector', array($cache));

        $connector->shouldReceive('connect')->once()
            ->with(array('name' => 'foo', 'driver' => 'illuminate', 'connector' => 'redis'), $manager)
            ->andReturn(Mockery::mock('League\Flysystem\CacheInterface'));

        $mock->shouldReceive('createConnector')->once()
            ->with(array('name' => 'foo', 'driver' => 'illuminate', 'connector' => 'redis'))
            ->andReturn($connector);

        return $mock;
    }
}