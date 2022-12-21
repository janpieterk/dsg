<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * View part of the web interface for the Dissonance Grading package
 *
 * @package DSG
 * @subpackage web
 */
class DSG_View
{

  /**
   * @var string HTML for the form to work with chords
   */
  public string $dsgform;
  /**
   * @var string URL of the calling script
   */
  private string $_url;
  /**
   * @var Chord the current chord
   */
  private Chord $_currentchord;
  /**
   * @var Chord the last manually entered chord
   */
  private Chord $_lastfedinchord;
  /**
   * @var string list of analysed chords so far
   */
  private string $_history = '';
  /**
   * @var string the target DSG for the current chord
   */
  private string $_targetdsg = '';
  /**
   * @var string HTML for the radio button to choose target DSG
   */
  private string $_targetdsgradio = '';
  /**
   * @var string HTML for the radio button to choose Parallel Subset Motion
   */
  private string $_psmradio = '';
  /**
   * @var string 'inc' (increasing) or 'dim' (diminishing) dissonance by PSM
   */
  private string $_psmtype = '';
  /**
   * @var string HTML for the select menu to choose increasing or diminishing dissonance by PSM
   */
  private $_psmoptions = array();
  /**
   * @var string warning message, currently only used for unattainable target dissonance
   */
  private string $_warning = '';
  /**
   * @var string compacted version of request array, to save history when going to documentation
   */
  private string $_getstringcompact = '';
  /**
   * @var string src attribute for the embed element for MIDI files
   */
  private string $_midi_file_path = '';
  /**
   * @var bool whether or not to show the MIDI controls and check the MIDI checkbox
   */
  private bool $_show_sound = TRUE;
  /**
   * @var string HTML for the checbox to use MIDI or not
   */
  private string $_soundcheckbox = '';

  /**
   * The constructor
   *
   * @param string $url the URL of the calling script
   */
  public function __construct(string $url)
  {
    $this->_url = $url;
  }

  /**
   * Creates form for startpage
   *
   */
  public function setInitialForm()
  {

    $this->dsgform = '<form action="' . $this->_url . '" method="post" id="dsgform">
<table>
<tr>
<td>Enter chord: </td><td><input type="text" size="30" name="last_fed_in_chord" value="">
<input type="submit" value="Analyze" class="dsgbutton"></td></tr>
<tr><td>&nbsp;</td><td id="midicheckbox">';
    if (DSG_MIDI_SUPPORT) {
      $this->dsgform .= '
<input type="checkbox" name="show_sound" id="show_sound" value="on" checked="checked"> <label for="show_sound">use MIDI sound</label>';
    }

    $this->dsgform .= '</td></tr>
</table>
</form>
<p id="dsgexample">Format something like: <span class="chordexample">d4-g#4-c#5</span>, or <span class="chordexample">f4db4bb3ab4</span>,<br>each note name to be ended by its octave position digit (a440 = a4)</p>';
  }

  /**
   * Sets the current chord
   *
   * @param Chord $chord the current chord
   */
  public function setCurrentChord(Chord $chord)
  {
    $this->_currentchord = $chord;
  }

  /**
   * Sets the last manually entered chord, to be saved in a hidden variable
   *
   * @param Chord $chord the last manually entered chord
   */
  public function setLastFedInChord(Chord $chord)
  {
    $this->_lastfedinchord = $chord;
  }

  /**
   * Sets the history (list of chord analyses so far) to be saved, and appended to, in a textarea
   *
   * @param string $history series of chord analyses
   */
  public function setHistory(string $history)
  {
    $this->_history = $history;
  }

  /**
   * Sets the previously entered target dissonance, to be reshown in its text field
   *
   * @param string $dsg the previously entered target dissonance
   */
  public function setTargetDSG(string $dsg)
  {
    $this->_targetdsg = $dsg;
  }

  /**
   * Sets the previously chosen Parallel Subset Motion type (increasing or diminishing dissonance)
   *
   * Used to select the corresponding option in the PSM select menu
   *
   * @param string $type the previously chosen PSM type
   */
  public function setSelectedPSMType(string $type)
  {
    $this->_psmtype = $type;
  }

  /**
   * Sets a warning text
   *
   * Currently only used if the desired target dissonance is not attainable
   *
   * @param string $string the warning message
   */
  public function setWarning(string $string)
  {
    $this->_warning = $string;
  }

  /**
   * Sets a compacted request array containing the state of the current page
   *
   * Used to return to where you left off when viewing the documentation
   *
   * @param string $string the compacted request array
   */
  public function setGetStringCompact(string $string)
  {
    $this->_getstringcompact = $string;
  }

  /**
   * Returns link to documentation page (with saved page state in compacted request array)
   *
   * @return string link with compacted page state, if applicable
   */
  public function getDocumentationLink(): string
  {

    if ($this->_getstringcompact != '') {
      return 'dsgdoc.php?getstringcompact=' . urlencode($this->_getstringcompact);
    } else {
      return 'dsgdoc.php';
    }
  }

  /**
   * Creates the form to work on a chord, also containing the history so far
   *
   */
  public function setForm()
  {

    $this->_setRadiosAndSelects();

    $this->dsgform = '<form action="' . $this->_url . '" method="post" id="dsgform">
<table>
<tr><td>Edit chord: </td><td><input type="text" size="30" name="chordstring" value="' . $this->_currentchord->toString() . '">
<input type="hidden" name="last_fed_in_chord" value="' . $this->_lastfedinchord->toString() . '">
<input type="submit" value="Analyze" class="dsgbutton">';
    if ($this->_currentchord->toString() != $this->_lastfedinchord->toString()) {
      $this->dsgform .= '<input type="submit" name="return_to_last" value="Return to original" class="dsgbutton">';
    }
    $this->dsgform .= '<input type="submit" name="erase_history" value="Clear history" class="dsgbutton"></td></tr>';
    $this->dsgform .= '<tr><td>&nbsp;</td><td id="midicheckbox">';
    if (DSG_MIDI_SUPPORT) {
      $this->dsgform .= $this->_soundcheckbox . ' <label for="show_sound">use MIDI sound</label>';
    }
    $this->dsgform .= '</td></tr></table>' . "\n";
    $this->dsgform .= '<pre id="chordstructure">' . htmlspecialchars($this->_currentchord->getTextualAnalysis()) . '</pre>' . "\n";
    if (DSG_MIDI_SUPPORT && $this->_show_sound) {
      $this->dsgform .= '<p id="playbuttonwrapper"><button id="midiplayer">play MIDI</button></p>' . "\n";
    }
    $this->dsgform .= '<table id="chordworker">';
    $this->dsgform .= '<caption>Work on this chord:</caption>';
    $this->dsgform .= '<tr><td>' . $this->_targetdsgradio . ' <label for="targetdsgradio">Target dissonance: </label></td>';
    $this->dsgform .= '<td><input type="text" size="5" maxlength="4" name="targetdsg" id="targetdsg" value="' . $this->_targetdsg . '">';
    $this->dsgform .= '</td><td rowspan="2"><input type="submit" name="work" value="Go" id="workbutton" class="dsgbutton"></td></tr>';
    $this->dsgform .= '<tr><td>' . $this->_psmradio . ' <label for="psmradio">By parellel subset motion: </label></td>';
    $this->dsgform .= '<td><select name="psm_options" id="psm_options">' . $this->_psmoptions . '</select></td></tr>';
    $this->dsgform .= '</table>' . "\n";
    if ($this->_warning != '') {
      $this->dsgform .= '<p id="warning">' . $this->_warning . '</p>';
    }
    $this->dsgform .= '<div id="dsghistory"><p>History:</p>';
    $this->dsgform .= '<textarea name="history" rows="20" cols="60">' . htmlspecialchars($this->_history) . "\n" . htmlspecialchars($this->_currentchord->getTextualAnalysis()) . '</textarea></div>';
    $this->dsgform .= '</form>';
  }

  /**
   * Sets the file path to the current MIDI file
   *
   * @param string $filepath value of the src attribute
   */
  public function setMIDIFilePath(string $filepath)
  {
    $this->_midi_file_path = $filepath;
  }

  /**
   * Returns the path to the currend MIDI file
   *
   * @return string
   */
  public function getMIDIFilePath(): string
  {
    return $this->_midi_file_path;
  }

  /**
   * Whether or not to show the MIDI controls, sets the MIDI checkbox accordingly
   *
   * @param bool $bool
   */
  public function setSound(bool $bool)
  {
    $this->_show_sound = $bool;
  }

  /**
   * @return bool
   */
  public function getSound(): bool
  {
    return $this->_show_sound;
  }

  /**
   * Sets the correct states for radio buttons, checkboxes and selectmenus of the form
   *
   */
  private function _setRadiosAndSelects()
  {

    if ($this->_targetdsg == '' && $this->_psmtype != '') {
      $this->_targetdsgradio = '<input type="radio" name="dsg_type" value="targetdsg" id="targetdsgradio">';
      $this->_psmradio = '<input type="radio" name="dsg_type" value="psm" id="psmradio" checked>';
    } elseif ($this->_targetdsg != '' && $this->_psmtype == '') {
      $this->_targetdsgradio = '<input type="radio" name="dsg_type" value="targetdsg"  id="targetdsgradio" checked>';
      $this->_psmradio = '<input type="radio" name="dsg_type" value="psm" id="psmradio">';
    } else {
      $this->_targetdsgradio = '<input type="radio" name="dsg_type" value="targetdsg"  id="targetdsgradio" checked>';
      $this->_psmradio = '<input type="radio" name="dsg_type" value="psm" id="psmradio">';
    }

    if ($this->_psmtype == 'inc') {
      $this->_psmoptions = '<option value="inc" selected>increase dissonance</option><option value="dim">diminish dissonance</option>';
    } elseif ($this->_psmtype == 'dim') {
      $this->_psmoptions = '<option value="inc">increase dissonance</option><option value="dim" selected>diminish dissonance</option>';
    } else {
      $this->_psmoptions = '<option value="inc" selected>increase dissonance</option><option value="dim">diminish dissonance</option>';
    }

    if (DSG_MIDI_SUPPORT && $this->_show_sound) {
      $this->_soundcheckbox = '<input type="checkbox" name="show_sound" id="show_sound" value="on" checked>';
    } else {
      $this->_soundcheckbox = '<input type="checkbox" name="show_sound" id="show_sound" value="on">';
    }
  }
}
