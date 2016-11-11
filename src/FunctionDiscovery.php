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
use FunctionDiscovery\TFunction;
use Remember\Remember;

class FunctionDiscovery
{
    protected $dirs = array();

    public function __construct($directories = null)
    {
        if ($directories !== null) {
            $this->addDirectory($directories);
        }
    }

    public function addDirectory($directories)
    {
        $this->dirs = array_merge($this->dirs, (array)$directories);
    }

    public function getFunctions($ann, &$isCached = null)
    {
        $ann    = strtolower(str_replace("@", "", $ann));
        $dirs   = array_merge(array($ann), $this->dirs);
        $isCached = true;

        $loader = Remember::wrap('function-discovery', function($args) use (&$isCached) {
            $functions = array();
            $isCached = false;
            $query  = array_shift($args);
            $fs = new Filesystem($args);
            foreach ($fs->get($query, 'Callable') as $annotation) {
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
            }

            return $functions;
        });

        return $loader($dirs);
    }
}
