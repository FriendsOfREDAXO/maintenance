<?php
$announcement = rex_config::get('maintenance', 'announcement', '');
$start_date = rex_config::get('maintenance', 'announcement_start_date');
$end_date = rex_config::get('maintenance', 'announcement_end_date');
$current_date = date('Y-m-d\TH:i:s');

if ('' !== $announcement && $start_date <= $current_date && $end_date >= $current_date) {
    ?>
<div class="maintenance-announcement">
    <?= $announcement ?>
</div>
<?php }
?>
