<?php

ob_clean();

$fragment = new rex_fragment();
echo $fragment->parse('maintenance/frontend.php');
exit;
