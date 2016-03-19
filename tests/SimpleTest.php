<?php

class SimpleTest extends PHPUnit_Framework_TestCase
{
    public function testLoading()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $cache->wipeCache();
        $functions = $cache->getFunctions('@foo', $cached);
        $this->assertFalse($cached);
        $this->assertTrue(is_array($functions));
        $this->assertTrue(!empty($functions));
    }

    /** @dependsOn testLoading */
    public function testFunctionAnnotations()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $functions = $cache->getFunctions('@foo', $cached);
        $this->assertTrue($cached);
        foreach ($functions as $function) {
            $this->assertTrue($function());
            foreach (['foo', 'FOO', 'FoO'] as $ann) {
                $this->assertTrue($function->hasAnnotation($ann));
                $this->assertTrue($function->hasAnnotation('@' . $ann));
                $this->assertFalse($function->hasAnnotation('@' . $ann . $ann));
                $this->assertEquals(
                    $function->getAnnotations('@' . $ann),
                    $function->getAnnotations($ann)
                );
                $ret = $function->getAnnotations($ann);
                $this->assertNotEquals(
                    [[]],
                    $ret
                );
            }
            $this->assertEquals($function('foobar'), 'foobar');
            $this->assertEquals($function('foobar'), $function->call(array('foobar')));
        }
        $this->assertTrue($functions['yyy1']->hasAnnotation('@auth'));
        $this->assertTrue($functions['yyy']->hasAnnotation('auth'));
        $this->assertEquals(['auth', []], $functions['yyy']->getAnnotation('@auth'));
        $this->assertEquals(['auth', []], $functions['yyy']->getAnnotation('auth'));
    }

    /** @dependsOn testLoading */
    public function testFunctionLoading()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $functions = $cache->getfunctions('@foo', $cached);
        $this->assertTrue($cached);
        foreach ($functions as $function) {
            $this->assertTrue($function());
            foreach (['foo', 'FOO', 'FoO'] as $ann) {
                $this->assertTrue($function->hasAnnotation($ann));
                $this->assertTrue($function->hasAnnotation('@' . $ann));
                $this->assertFalse($function->hasAnnotation('@' . $ann . $ann));
            }
            $this->assertEquals($function('foobar'), 'foobar');
        }
        $this->assertFalse($function->hasAnnotation(uniqid(true)));
        $this->assertEquals(null, $function->getAnnotation(uniqid(true)));
        $this->assertEquals($function->getAnnotations(), $function->getAnnotation());
        $this->assertTrue($functions['yyy1']->hasAnnotation('@auth'));
        $this->assertTrue($functions['yyy']->hasAnnotation('auth'));
        $this->assertEquals(['auth', []], $functions['yyy']->getAnnotation('@auth'));
        $this->assertEquals(['auth', []], $functions['yyy']->getAnnotation('auth'));
        $this->assertEquals(__DIR__ . '/features/foo.php', $functions['yyy1']->getFile());
    }

    /**
     *  @dependsOn testFunctionLoading 
     */
    public function testCacheInvalidationDirectory()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $functions = $cache->getFunctions('@foo', $cached);
        $this->assertTrue($cached);
        touch(__DIR__ . '/features/' . uniqid(true) . 'php');
    }

    /**
     *  @dependsOn testCacheInvalidationDirectory
     */
    public function testCacheInvalidDirectoryFalse()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $cache->getFunctions('@foo', $cached);
        $this->assertFalse($cached);
    }

    /**
     *  @dependsOn testFunctionLoading 
     */
    public function testCacheInvalidation()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $functions = $cache->getFunctions('@foo', $cached);
        $this->assertTrue($cached);
        touch(__DIR__ . '/features/foo.php');
    }

    /**
     *  @dependsOn testCacheInvalidation
     */
    public function testCacheInvalidationFalse()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $functions = $cache->getFunctions('@foo', $cached);
        $this->assertFalse($cached);
    }

    /**
     *  @dependsOn testCacheInvalidationFalse
     */
    public function testWithNoName()
    {
        $cache = new FunctionDiscovery(__DIR__, 'tmp.php');
        $functions = $cache->getFunctions('crawler', $cached);
        $this->assertFalse($cached);
        $this->assertEquals(array(0), array_keys($functions));
        $this->AssertTrue($functions[0]());
    }
}
