<?php

@foreach ($data['files'] as $file)
    if (!is_{{is_file($file) ? 'file': 'dir'}}({{@$file}}) || {{filemtime($file)}} < filemtime({{@$file}})) {
        return array('files' => array(), 'cache' => array());
    }
@end

return {{@$data}};
