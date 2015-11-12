<?php

$functions = array();

@foreach (array_unique($files) as $file)
    if (!is_{{is_file($file) ? 'file': 'dir'}}({{@$file}}) || {{filemtime($file)}} < filemtime({{@$file}})) {
        return false;
    }
@end

return array(
@foreach ($functions as $function)
    {{@$function->getName() }} => {{@$function}},
@end
);
