<?php
/**
 * Copyright (C) 2012 Louis-Philippe Huberdeau
 *
 * Permission is hereby granted, free of charge, to any person obtaining a 
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace HireVoice\Neo4j\Tests;
use HireVoice\Neo4j\Meta\Repository as MetaRepository;

class EntityMetaTest extends \PHPUnit_Framework_TestCase
{
    function testObtainIndexedProperties()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Movie');

        $names = array();
        foreach ($meta->getIndexedProperties() as $property) {
            $names[] = $property->getName();
        }

        $this->assertEquals(array('title', 'movieRegistryCode'), $names);
    }

    function testGetProperties()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Movie');

        $names = array();
        foreach ($meta->getProperties() as $property) {
            $names[] = $property->getName();
        }

        $this->assertEquals(array('title', 'alternateTitles', 'category', 'releaseDate', 'movieRegistryCode', 'blob'), $names);
    }

    function testManyToMany()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Movie');

        $names = array();
        foreach ($meta->getManyToManyRelations() as $property) {
            $names[] = $property->getName();
        }

        $this->assertEquals(array('actor', 'presentedMovie'), $names);
    }

    function testManyToOne()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Movie');

        $names = array();
        foreach ($meta->getManyToOneRelations() as $property) {
            $names[] = $property->getName();
        }

        $this->assertEquals(array('mainActor'), $names);
    }

    /**
     * @group neo4j-v2
     */
    function testObtainLabels()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\City');

        $names = $meta->getLabels();

        $this->assertEquals(array('Location', 'City'), $names);
    }
    
    
    /**
     * FACTS:
     * The Car class extends the Vehicle class. 
     * The Car class has a 'Car' label.
     * The Vehicle class has a 'Vehicle' label.
     * EXPECTATION:
     * The resulting label set for the Car class should include both labels.
     */
    function testObtainLabelsWithInheritance()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Vehicles\\Car');

        $actual = $meta->getLabels();
        $expected = array('Vehicle', 'Car');
        
        $this->assertSame(array_diff($expected, $actual), array_diff($actual, $expected));
    }
    
    /**
     * FACTS:
     * The Tacoma class extends the Truck class.
     * The Truck class extends the Vehicle class.
     * The Tacoma class has a 'Toyota' label.
     * The Truck class has no labels.
     * The Vehicle class has a 'Vehicle' label.
     * EXPECTATION:
     * The resulting label set should include all labels from all levels of inheritance, but should
     * not include any null values, despite a class in the inheritance chain not having any labels.
     */
    function testObtainLabelsWithInheritanceDoesNotIncludeNull()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Vehicles\\Tacoma');

        $actual = $meta->getLabels();
        $expected = array('Vehicle', 'Toyota');
        
        $this->assertSame(array_diff($expected, $actual), array_diff($actual, $expected));
    }
    
    /**
     * FACTS:
     * The Altima class extends the Car class.
     * The Car class extends the Vehicle class.
     * The Car class has a 'Car' label. 
     * The Altima class also has a 'Car' label.
     * EXPECTATION:
     * The resulting label set should include all labels from all levels of inheritance, but should
     * not include any duplicate values, despite multiple classes in the inheritance chain having the same labels.
     */
    function testObtainLabelsWithInheritanceContainNoDuplicates()
    {
        $repo = new MetaRepository;
        $meta = $repo->fromClass('HireVoice\\Neo4j\\Tests\\Entity\\Vehicles\\Altima');

        $actual = $meta->getLabels();
        $expected = array('Vehicle', 'Car', 'Nissan');
        
        // Results should be the same, but this doesn't account for duplicates
        $this->assertSame(array_diff($expected, $actual), array_diff($actual, $expected));
        
        // Make sure they have the same count too
        $this->assertEquals(count($expected), count($actual));
    }
}

