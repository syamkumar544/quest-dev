<?php
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
$haslogo = (!empty($PAGE->theme->settings->logo));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

if ($hascustommenu) {
    $bodyclasses[] = 'has_navbar';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">
	<div id="wrapper">
	
<!-- start OF header -->
<?php if($PAGE->bodyid != 'page-mod-scorm-player') {?>
		<div id="page-header-wrapper">
		<div id="page-header" class="page-header-home wrap">
			<?php if ($haslogo) {
                        echo html_writer::link(new moodle_url('/'), "<img src='".$PAGE->theme->settings->logo."' id='logo' alt='logo' />");
                    } else { ?>
			<a href="<?php p($CFG->wwwroot) ?>"><img src="<?php echo $OUTPUT->pix_url('logo', 'theme')?>" id="logo"></a>
			<?php } ?>
			
<!-- start of custom menu -->	
<?php if ($hascustommenu) { ?>
<div id="cmenuwrap"><div id="custommenu">
<?php echo $custommenu; ?>
</div></div>
<?php } ?>
<!-- end of menu -->	
				<div class="headermenu">
        		<?php
	        	    echo $OUTPUT->login_info();
    	        	echo $OUTPUT->lang_menu();
	        	    echo $PAGE->headingmenu;
		        ?>	    
				 </div>
		</div>
		</div>
<?php } ?>
<!-- end of header -->		

<!-- start of navbar -->
<?php if ($hasnavbar) { ?>
        <div class="navbar clearfix wrap">
          <a href="<?php p($CFG->wwwroot) ?>" id="homer" alt="home" title="home"></a>
          <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
          <div class="navbutton"> <?php echo $PAGE->button; ?></div>
        </div>
<?php } ?>
<!-- end of navbar -->

<div id="page-content-wrapper" class="wrap">
<!-- start OF moodle CONTENT -->
 <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">
            
                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
<!-- end OF moodle CONTENT -->
<div class="clearer"></div>
</div>
<!-- end OF moodle CONTENT wrapper -->


<!-- start of footer -->	
<?php if($PAGE->bodyid != 'page-mod-scorm-player') {?>
<div id="page-footer">
<?php
echo $OUTPUT->login_info();
//echo $OUTPUT->home_link();
echo $OUTPUT->standard_footer_html();
?>
</div>
<?php } ?>
<!-- end of footer -->	

<div class="clearer"></div>

	</div><!-- end #wrapper -->
</div><!-- end #page -->	

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>