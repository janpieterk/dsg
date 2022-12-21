<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied.


/**
 * Controller part of the web interface for the Dissonance Grading package
 *
 * Parses the request array, filters input, and provides clean data to
 * the model (DSG class) ; acts as a link between model and view (DSG_view class).
 *
 * @package DSG
 * @subpackage web
 */
class DSG_Controller
{

  /**
   * @var DSG model part of the MVC setup
   */
  public DSG $dsg;
  /**
   * @var DSG_View view part of the MVC setup
   */
  public DSG_View $view;

  /**
   * The constructor
   *
   * @param DSG $dsg
   * @param DSG_View $view
   * @param array $request_array the raw $_REQUEST array
   */
  public function __construct(DSG $dsg, DSG_View $view, array $request_array)
  {
    $this->view = $view;
    $this->dsg = $dsg;

    if (DSG_MIDI_SUPPORT) {
      $this->_check_settings();
      $this->_clear_tmpdir();
    }
    $input = $this->_parse_request_array($request_array);

    if (empty($input)) {
      $this->_default();
    } else {
      $this->dsg->setChord(new Chord($input['current_chord']));
      if (!array_key_exists('stru', $this->dsg->getCurrentChord()->getChordAnalysis())) {
        //  no valid chord entered
        $this->_default();
      } else {
        if (isset($input['targetdsg'])) {
          $this->view->setTargetDSG($input['targetdsg']);
          $originaldsg = $this->dsg->getCurrentChord()->getDissonance();
          $this->dsg->changeCurrentChordByTargetDSG($input['targetdsg']);
          $newdsg = $this->dsg->getCurrentChord()->getDissonance();
          if ($originaldsg == $newdsg) {
            $this->view->setWarning('Desired target dissonance not reachable');
          }
        } elseif (isset($input['psm'])) {
          $this->view->setSelectedPSMType($input['psm']);
          $this->dsg->changeCurrentChordByPSM($input['psm']);
        }
        if (isset($input['history'])) {
          $this->view->setHistory($input['history']);
        }
        $this->view->setCurrentChord($this->dsg->getCurrentChord());
        if (DSG_MIDI_SUPPORT && $input['show_sound']) {
          $this->view->setSound(TRUE);
          $filepath = $this->_saveMIDI();
          $this->view->setMIDIFilePath($filepath);
        } else {
          $this->view->setSound(FALSE);
        }
        if (isset($input['last_fed_in_chord'])) {
          $this->dsg->setLastFedInChord(new Chord($input['last_fed_in_chord']));
        } else {
          $this->dsg->setLastFedInChord(new Chord($input['current_chord']));
        }
        $this->view->setLastFedInChord($this->dsg->getLastFedInChord());
        $this->view->setForm();
      }
    }
  }

  /**
   * Function to sanitize and summarize the user input ($_REQUEST array)
   *
   * @param array $request_array the raw $_REQUEST array
   * @return array clean data for the DSG class
   */
  private function _parse_request_array(array $request_array): array
  {

    $getstringcompact = '';

    foreach (array_keys($request_array) as $key) {
      if (!in_array($key, array('getstringcompact', 'last_fed_in_chord', 'chordstring', 'return_to_last', 'show_sound', 'history', 'work', 'dsg_type', 'targetdsg', 'psm_options', 'erase_history'))) {
        unset($request_array[$key]);
      }
    }

    if (isset($request_array['getstringcompact'])) {
      $request_array = unserialize(gzuncompress(base64_decode($request_array['getstringcompact'])));
    }

    if (!empty($request_array)) {
      $getstringcompact = base64_encode(gzcompress(serialize($request_array)));
    }

    $input = array();

    if (isset($request_array['last_fed_in_chord']) && !isset($request_array['chordstring'])) {
      // akkoord eerste keer ingevoerd
      $input['current_chord'] = trim($request_array['last_fed_in_chord']);
    } elseif (isset($request_array['last_fed_in_chord']) && isset($request_array['chordstring'])) {
      if (isset($request_array['return_to_last'])) {
        $input['current_chord'] = trim($request_array['last_fed_in_chord']);
      } else {
        $input['last_fed_in_chord'] = trim($request_array['last_fed_in_chord']);
        $input['current_chord'] = trim($request_array['chordstring']);
      }
    }

    if (isset($request_array['history'])) {
      $input['history'] = trim($request_array['history']);
    }

    if (isset($request_array['work'])) {
      if ($request_array['dsg_type'] == 'targetdsg' && is_numeric($request_array['targetdsg'])) {
        $input['targetdsg'] = intval($request_array['targetdsg']);
      } elseif ($request_array['dsg_type'] == 'psm' && in_array($request_array['psm_options'], array('inc', 'dim'))) {
        $input['psm'] = $request_array['psm_options'];
      }
    }

    if (isset($request_array['show_sound'])) {
      $input['show_sound'] = TRUE;
    } else {
      $input['show_sound'] = FALSE;
    }

    if (empty($input['current_chord']) || isset($request_array['erase_history'])) {
      $input = array();
      $getstringcompact = '';
    }

    $this->view->setGetStringCompact($getstringcompact);

    return $input;
  }

  /**
   * Default method to be invoked when no user input is present
   *
   * Tells the view to display the initial screen of the application
   */
  private function _default()
  {
    $this->view->setInitialForm();
  }

  /**
   * Saves MIDI file in designated tmp directory and returns the src attribute
   *
   * Uses Valentin Schmidts Midi Class 1.6 to create the MIDI file. This package
   * can be downloaded from {@link http://valentin.dasdeck.com/midi/}.
   * Since Midi Class 1.6 uses Call-time pass-by-reference, I set the error level
   * to supress warnings while opening the midi.class.php file if allow_call_time_pass_reference
   * is set to off in php.ini.
   * @return string value of the src attribute (file path) to the saved MIDI file
   */
  private function _saveMIDI(): string
  {

    $old_error_reporting = error_reporting();
    if (!ini_get('allow_call_time_pass_reference')) {
      error_reporting(E_ERROR);
    }
    require('midi.class.php');
    error_reporting($old_error_reporting);

    $filename = md5(uniqid()) . '.mid';
    $miditxt = $this->dsg->getCurrentChord()->getMIDItext();
    $midi = new Midi();
    $midi->importTxt($miditxt);
    $midi->saveMidFile(DSG_TMPDIR . DIRECTORY_SEPARATOR . $filename);

    return DSG_TMPDIR_DOCROOT . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Check if the settings are correct for MIDI support to function, otherwise trigger error
   *
   */
  private function _check_settings()
  {

    if (!file_exists(DSG_EXTERNAL_LIBS_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'midi.class.php')) {
      $error_message = 'MIDI support is set to TRUE in dsg_config.inc.php, but ' . DSG_EXTERNAL_LIBS_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'midi.class.php is not found.<br>';
      $error_message .= 'Please download and install <a href="https://valentin.dasdeck.com/midi/">Midi Class 1.6</a>, or set MIDI support to FALSE. ';
      trigger_error($error_message, E_USER_ERROR);
    }

    if (!file_exists(DSG_TMPDIR) || !is_writable(DSG_TMPDIR)) {
      $error_message = 'MIDI support is set to TRUE in dsg_config.inc.php, but the designated directory ' . DSG_TMPDIR . ' does not exist or is not writeable.<br>';
      $error_message .= 'This program needs to be able to save its temporary MIDI files somewhere within the document root; see dsg_config.inc.php for options.';
      trigger_error($error_message, E_USER_ERROR);
    }
  }

  /**
   * Clear out tmp directory once every 24 hours
   *
   */
  private function _clear_tmpdir()
  {

    $timestampfile = DSG_TMPDIR . DIRECTORY_SEPARATOR . 'lasttouched';
    if (!file_exists($timestampfile)) {
      touch($timestampfile);
    } else {
      $time = time();
      if (($time - filemtime($timestampfile)) < 86400) {
        return;
      } else {
        $files = glob(DSG_TMPDIR . DIRECTORY_SEPARATOR . '*.mid');
        foreach ($files as $file) {
          if ($time - filemtime($file) > 43200) {
            unlink($file);
          }
        }
        // update time of last cleanup
        touch($timestampfile);
      }
    }
  }

}

