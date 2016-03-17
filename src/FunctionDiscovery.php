<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2015 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

use Notoj\Filesystem;
use FunctionDiscovery\Templates;
use FunctionDiscovery\TFunction;
use crodas\FileUtil\File;

class FunctionDiscovery
{
    protected $tmp;
    protected $tmpFile;
    protected $dirs;
    protected $fs;
    protected static $cache = array();
    protected static $dirty = array();

    protected function load($file)
    {
        if (!is_file($file)) {
            return array('files' => array(), 'cache' => array());
        }

        $data = (array)include $file;

        foreach (array('files', 'cache') as $type) {
            if (empty($data[$type])) {
                $data[$type] = array();
            }
        }

        return $data;
    }

    public function __construct($directories, $temporary = null)
    {
        $this->dirs = (array)$directories;
        $this->tmpFile = $temporary ?: File::generateFilepath('function-discovery', serialize($this->dirs));

        if (empty(self::$cache[$this->tmpFile])) {
            self::$cache[$this->tmpFile] = $this->load($this->tmpFile);
            self::$dirty[$this->tmpFile] = false;
        }

        $this->tmp = &self::$cache[$this->tmpFile];
    }

    public function __destruct()
    {
        if (!empty(self::$dirty[$this->tmpFile])) {
            $this->tmp['files'] = array_unique($this->tmp['files']);
            $code = Templates::get('template')->render(array('data' => $this->tmp), true);
            File::writeAndInclude($this->tmpFile, $code);
        }
    }
    
    public function wipeCache()
    {
        self::$cache[$this->tmpFile] = array('files' => array(), 'cache' => array());
        self::$dirty[$this->tmpFile] = true;
    }

    public function getTemporaryFile()
    {
        return $this->tmp;
    }

    public function getFunctions($ann, & $wasCached = null)
    {
        $ann = strtolower(str_replace("@", "", $ann));

        if (array_key_exists($ann, $this->tmp['cache'])) {
            $wasCached = true;
            return $this->tmp['cache'][$ann];
        }

        if (empty($this->fs)) {
            $this->fs = new Filesystem($this->dirs);
        }

        self::$dirty[$this->tmpFile] = true;
        $wasCached = false;
        $functions = array();
        foreach ($this->fs->get($ann, 'Callable') as $annotation) {
            $function = $annotation->getObject();
            $static   = false;
            if ($function->isMethod()) {
                $callback = [$function->getClass()->getName(), $function->getName()];
                $static   = $function->isStatic();
            } else {
                $callback = $function->getName();
            }

            try {
                $name = strtolower($annotation->getArg());
            } catch (Exception $e) {
                $name = null;
            }

            $annotations = [];
            foreach ($annotation->getParent() as $annotation) {
                $annotations[] = [
                    strtolower($annotation->getName()),
                    $annotation->getArgs()
                ];
            }
            $wrapper = new TFunction($function->getFile(), $callback, $static);
            $wrapper->setAnnotations($annotations);
            if ($name) {
                $functions[$name] = $wrapper;
            } else {
                $functions[] = $wrapper;
            }

            $this->tmp['files'][] = $function->getFile();
            $this->tmp['files'][] = dirname($function->getFile());
        }

        $this->tmp['cache'][$ann] = $functions;

        return $functions;
    }
}
