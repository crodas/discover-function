<?php

$functions = array();

@foreach (array_unique($files) as $file)
    if (!is_readable({{@$file}}) || {{filemtime($file)}} < filemtime({{@$file}})) {
        return false;
    }
@end

return array(
@foreach ($functions as $function)
    {{@$function->getName() }} => {{@$function}},
@end
);
