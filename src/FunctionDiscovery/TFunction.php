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
namespace FunctionDiscovery;


class TFunction
{
    protected $file;
    protected $static;
    protected $function;
    protected $annotations = array();
    protected $name;

    public static function __set_state(Array $state)
    {
        $object = new self($state['file'], $state['function'], $state['static']);
        foreach (array('name', 'annotations') as $property) {
            $object->$property = $state[$property];
        }

        return $object;
    }

    public function setAnnotations(Array $annotations)
    {
        $this->annotations = $annotations;
        return $this;
    }

    public function getAnnotation($name = '') {
        if (empty($name)) {
            return $this->annotations;
        }
        $name = str_replace('@', '', strtolower($name));
        foreach ($this->annotations as $annotation) {
            if ($annotation[0] === $name) {
                return $annotation;
            }
        }

        return null;
    }


    public function hasAnnotation($name)
    {
        $name = str_replace('@', '', strtolower($name));
        foreach ($this->annotations as $annotation) {
            if ($annotation[0] === $name) {
                return true;
            }
        }

        return false;
    }

    public function __invoke()
    {
        if (!is_callable($this->function)) {
            require $this->file;
        }
        $arguments = func_get_args();
        $function  = $this->function;
        if (is_array($function) && !$this->static) {
            $function[0] = new $function[0];
        }

        return call_user_func_array($function, $arguments);
    }

    public function __construct($file, $function, $static = false)
    {
        $this->file     = $file;
        $this->static   = $static;
        $this->function = $function;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
