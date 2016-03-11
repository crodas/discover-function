<?php

@foreach ($data['files'] as $file)
    if (!is_{{is_file($file) ? 'file': 'dir'}}({{@$file}}) || {{filemtime($file)}} < filemtime({{@$file}})) {
        return false;
    }
@end

return {{@$data}};
