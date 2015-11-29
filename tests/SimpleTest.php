<?php

class SimpleTest extends PHPUnit_Framework_TestCase
{
    public function testLoading()
    {
        $cache = new FunctionDiscovery(__DIR__, '@foo');
        $cache->wipeCache();
        $functions = $cache->filter(function($function, $annotation) {
            $name = $annotation->getArg();
            if (!$name) {
                return false;
            }
            $function->setName($name);
        }, $cached);
        $this->assertFalse($cached);
        $this->assertTrue(is_array($functions));
        $this->assertTrue(!empty($functions));
    }

    /** @dependsOn testLoading */
    public function testFunctionLoading()
    {
        $cache = new FunctionDiscovery(__DIR__, '@foo');
        $functions = $cache->filter(function($function, $annotation) {
            $name = $annotation->getArg();
            if (!$name) {
                return false;
            }
            $function->setName($name);
        }, $cached);
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
        $this->assertTrue($functions['yyy1']->hasAnnotation('@auth'));
        $this->assertTrue($functions['yyy']->hasAnnotation('auth'));
        $this->assertEquals(['auth', []], $functions['yyy']->getAnnotation('@auth'));
        $this->assertEquals(['auth', []], $functions['yyy']->getAnnotation('auth'));
    }

    /**
     *  @dependsOn testFunctionLoading 
     */
    public function testCacheInvalidationDirectory()
    {
        $cache = new FunctionDiscovery(__DIR__, '@foo');
        $functions = $cache->filter(function($function, $annotation) {
            $name = $annotation->getArg();
            if (!$name) {
                return false;
            }
            $function->setName($name);
        }, $cached);
        $this->assertTrue($cached);

        touch(__DIR__ . '/features/' . uniqid(true) . 'php');
        $cache = new FunctionDiscovery(__DIR__, '@foo');
        $functions = $cache->filter(function($function, $annotation) {
            $name = $annotation->getArg();
            if (!$name) {
                return false;
            }
            $function->setName($name);
        }, $cached);
        $this->assertFalse($cached);
    }

    /**
     *  @dependsOn testFunctionLoading 
     */
    public function testCacheInvalidation()
    {
        $cache = new FunctionDiscovery(__DIR__, '@foo');
        $functions = $cache->filter(function($function, $annotation) {
            $name = $annotation->getArg();
            if (!$name) {
                return false;
            }
            $function->setName($name);
        }, $cached);
        $this->assertTrue($cached);

        touch(__DIR__ . '/features/foo.php');
        $cache = new FunctionDiscovery(__DIR__, '@foo');
        $functions = $cache->filter(function($function, $annotation) {
            $name = $annotation->getArg();
            if (!$name) {
                return false;
            }
            $function->setName($name);
        }, $cached);
        $this->assertFalse($cached);
    }
}
