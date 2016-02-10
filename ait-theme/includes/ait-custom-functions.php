<?php
// allows programmers to include part in the same way as in latte syntax
function aitRenderLatteTemplate($template, $params = array())
{
    AitWpLatte::init();
    ob_start();
    WpLatte::render(aitPath('theme'). $template, $params);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}
