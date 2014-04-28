
<html <?php echo $OUTPUT->htmlattributes() ?>>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->main_content() ?>
<?php echo $OUTPUT->standard_end_of_body_html() ?>

</body>
</html>