<?php
$file = rex_file::get(rex_path::addon('maintenance','README.md'));
$body = rex_markdown::factory()->parse($file);
$fragment = new rex_fragment();
$fragment->setVar('body', $body, false);
$content = '<div class="FoR-help">'.$fragment->parse('core/page/section.php').'</div>';
echo $content;
?>