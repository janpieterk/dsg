<?php
// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * index.php file from joskunst.net for the web interface of the DSG package
 *
 * @package DSG
 * @subpackage web
 */

 /**
 * Configuration file
 */
require('dsg_config.inc.php');

$dsg = new DSG();
$view = new DSG_View(DSG_ROOTDIR);
$controller = new DSG_Controller($dsg, $view, $_REQUEST);

$show_play_button = FALSE;
if (DSG_MIDI_SUPPORT && $view->getSound() && file_exists($view->getMIDIFilePath())) {
  $show_play_button = TRUE;
  $nonce = basename($view->getMIDIFilePath(), '.mid');
  header("Content-Security-Policy: default-src 'self';script-src 'unsafe-inline' 'nonce-$nonce' 'self';base-uri 'self';form-action 'self';object-src 'self';frame-ancestors 'none';frame-src 'none';report-uri https://b6951961d3ee91f0e6a14aeb394a9b8d.report-uri.com/r/d/csp/enforce;");
} else {
  header("Content-Security-Policy: default-src 'self';base-uri 'self';form-action 'self';object-src 'none';frame-ancestors 'none';frame-src 'none';report-uri https://b6951961d3ee91f0e6a14aeb394a9b8d.report-uri.com/r/d/csp/enforce;");
}

?>
<!DOCTYPE html>
<!--suppress HtmlUnknownTarget -->
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dissonance Grading</title>
    <link rel="stylesheet" type="text/css" href="<?php echo DSG_CSS; ?>">
  <?php if ($show_play_button) { ?>
      <script src="js/midi.js"></script>
      <script nonce="<?php echo $nonce; ?>">
        let midiplayer = new MidiPlayer("<?php echo $view->getMIDIFilePath();?>");
        midiplayer.play();
        midiplayer.stop();

        document.addEventListener('DOMContentLoaded', function () {
          document.getElementById("midiplayer").addEventListener("click", function(e) {
            e.preventDefault();
            midiplayer.play();
            midiplayer.stop();
          });
          document.getElementById("psm_options").addEventListener("click", function() {
            document.getElementById("psmradio").checked = true;
          });
          document.getElementById("targetdsg").addEventListener("focus", function() {
            document.getElementById("targetdsgradio").checked = true;
          });
        });

      </script>
  <?php } ?>
</head>
<body id="dsgpage">
<h1 id="dsgtitle">Dissonance Grading Program</h1>
<p id="originalaboutstring">by <span id="originalauthor">Jos Kunst</span>, who is indebted to Jan Vriend and Jan Pieter
    Kunst. Version 2.0.1. <a href="<?php echo $view->getDocumentationLink(); ?>">Documentation</a>.</p>
<p id="phpportaboutstring">Originally written in Pascal (1993); PHP port &amp; web interface &copy;
    2006-<?php echo date('Y'); ?> by Jan Pieter Kunst. Source code available on <a href="https://github.com/janpieterk/dsg">Github</a>.
  <?php if (DSG_MIDI_SUPPORT) { ?>
      <br>
      MIDI support: <a href="https://valentin.dasdeck.com/midi/">Midi Class 1.6</a> by Valentin Schmidt and <a
              href="https://github.com/chenx/MidiPlayer">MidiPlayer</a> by chenx.
  <?php } ?>
</p>
<?php echo $view->dsgform; ?>
</body>
</html>
