<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs.
// 	This program is free software. You can redistribute it and/or modify it
// 	under the terms of version 2 of the GNU General Public License, which you
// 	should have received with it.
// 	This program is distributed in the hope that it will be useful, but
// 	without any warranty, expressed or implied.

/**
 * Class to represent a chord
 *
 * @package DSG
 */
class Chord
{

  private $_chordrec = array();

  /**
   * The parameter $chord can be a string of note names, an array of integers representing pitches
   * (middle c = 60) or an associative array containing a chord analysis (same format as returned
   * by the getChordAnalysis() method)
   *
   * @param string|array $chord
   */
  public  function __construct($chord)
  {
    if (is_string($chord)) {
      $this->_chordrec = $this->_scan($this->_makeintoset($chord));
    } elseif (is_array($chord)) {
      if (array_key_exists('stru', $chord)) {
        $pitches = array();
        foreach($chord['stru'] as $note) {
          $pitches[] = $note['pitch'];
        }
        $chord = $pitches;
      }
      $this->_chordrec = $this->_scan($chord);
    }
  }

  /**
   * Returns an array containing the chord analysis
   *
   * @return array
   */
  public function getChordAnalysis()
  {
    return $this->_chordrec;
  }

  /**
   * Returns the chord as a string of note names separated by dashes
   *
   * @return string
   */
  public function toString()
  {
    $tmp_arr = array();
    if (array_key_exists('stru', $this->_chordrec)) {
      foreach ($this->_chordrec['stru'] as $notes) {
        $tmp_arr[] = $this->_nameOf($notes['pitch']);
      }
    }
    return join('-', $tmp_arr);
  }

  /**
   * The dsg ("dissonance grading")  is twice the dissonance total of the chord
   * divided by the number of component notes
   *
   * @return integer the dissonance grading of the chord
   */
  public function getDissonance()
  {
    return $this->_chordrec['diss'];
  }

  /**
   * The chord's  dissonance total is a number such that if you subtract
   * from it the contribution of any of the chord's component notes
   * (octaves make for exceptions here, sorry), you get the total of
   * another chord that is just like this one except for the absence
   * of that component note
   *
   * @return mixed the dissonance total of the chord
   */
  public function getTotal()
  {
    return $this->_chordrec['total'];
  }

  /**
   * Returns a representation of the chord as an array of pitches (integers). Middle c = 60
   *
   * @return array array (set) of integers representing pitches
   */
  public function getPitchSet()
  {
    $pitchset = array();
    if (array_key_exists('stru', $this->_chordrec)) {
      foreach ($this->_chordrec['stru'] as $notes) {
        $pitchset = array_addtoset($notes['pitch'], $pitchset);
      }
    }
    return $pitchset;
  }

  /**
   * Returns a textual represention of a MIDI file for the current chord
   *
   * Creates a MF2T/T2MF MIDI format text string for the current chord,
   * to be converted by Valentin Schmidts Midi Class 1.6 into a binary MIDI file
   * @return string MF2T/T2MF format text string
   */
  public function getMIDItext()
  {
    $midi_txt = 'MFile 0 1 480
MTrk
TimestampType=Delta
0 TimeSig 4/4 24 8
0 Tempo 500000
0 PrCh ch=1 p=20' . "\n";
    foreach ($this->getPitchSet() as $note) {
      $midi_txt .= '0 On ch=1 n=' . $note . ' v=100' . "\n";
    }
    $midi_txt .= '2000 Off ch=16 n=1 v=0
0 Meta TrkEnd
TrkEnd';

    return $midi_txt;
  }

  /**
   * Returns a human readable string representation of the analysis of the current chord,
   * in the same format as that used by the original Pascal implementation
   *
   * @return string human-readable analysis
   */
  public function getTextualAnalysis()
  {
    $chordanalysis = '';
    if (array_key_exists('stru', $this->_chordrec)) {
      foreach ($this->_chordrec['stru'] as $notes) {
        $chordanalysis .= $notes['name'] . '<' . $notes['contribution'] . '> ';
      }
    }
    $chordanalysis .= "\n";
    $chordanalysis .= 'TOTAL--><' . $this->_chordrec['total'] . '>; ';
    $chordanalysis .= 'DSG is ' . $this->_chordrec['diss'] . ' (class: ' . $this->_chordrec['class'] . '); ';
    $chordanalysis .= 'Hindemith Root: ' . $this->_chordrec['hifu']['name'] . "\n";

    return $chordanalysis;
  }

  /**
   * Looks for the most dissonant tone in the chord
   *
   * @param array $exceptpitchset array of pitches to be ignored (if any)
   * @return int the most dissonant tone
   */
  public function findPMaxContrib(array $exceptpitchset = array())
  {
    $pmax = 0;
    $maxcontrib = -1;
    if (array_key_exists('stru', $this->_chordrec)) {
      foreach ($this->_chordrec['stru'] as $notes) {
        if (($notes['contribution'] > $maxcontrib) && (!in_array($notes['pitch'], $exceptpitchset))) {
          $pmax = $notes['pitch'];
          $maxcontrib = $notes['contribution'];
        }
      }
    }

    return $pmax;
  }


  /**
   * Looks for the least dissonant tone in the chord
   *
   * @param array $exceptpitchset array of pitches to be ignored (if any)
   * @return int the least dissonant tone
   */
  public function findPMinContrib(array $exceptpitchset = array())
  {
    $pmin = 0;
    $mincontrib = 500;
    if (array_key_exists('stru', $this->_chordrec)) {
      foreach ($this->_chordrec['stru'] as $notes) {
        if (($notes['contribution'] < $mincontrib) && (!in_array($notes['pitch'], $exceptpitchset))) {
          $pmin = $notes['pitch'];
          $mincontrib = $notes['contribution'];
        }
      }
    }
    return $pmin;
  }


  /**
   * Returns the tone from the intersection of two chords which differs most in dissonance value in the two chords
   *
   * @param Chord $chord
   * @return int tone which differs most in dissonance value in the two chords
   */
  public function findPMaxDiff(Chord $chord)
  {
    $myval = $otherval = NULL;
    $myrec = $this->getChordAnalysis();
    $otherrec = $chord->getChordAnalysis();
    $mycs = $this->getPitchSet();
    $othercs = $chord->getPitchSet();
    $maxdiff = -100;
    $pmaxdiff = array();
    $intersect = array_values(array_intersect($mycs, $othercs));
    sort($intersect);
    foreach ($intersect as $p) {

      foreach ($myrec['stru'] as $note) {
        if ($note['pitch'] == $p) {
          $myval = $note['contribution'];
        }
      }
      foreach ($otherrec['stru'] as $note) {
        if ($note['pitch'] == $p) {
          $otherval = $note['contribution'];
        }
      }
      if (($otherval - $myval) > $maxdiff) {
        $maxdiff = $otherval - $myval;
        $pmaxdiff = $p;
      }
    }
    return $pmaxdiff;
  }


  /**
   * Changes a chord four different ways, by performing four possible second steps of a given tone
   *
   * @param int $tone tone to be moved
   * @return array array containing four different variants of the chord
   */
  public function moveToneFourPossibleWays($tone)
  {
    $pitchset = $this->getPitchSet();

    if (in_array($tone + 1, $pitchset)) {
      $c1 = clone($this);
    } else {
      $c1 = new Chord(
        array_addtoset(
          $tone + 1, array_removefromset($tone, $pitchset)
        )
      );
    }
    if (in_array($tone + 2, $pitchset)) {
      $c11 = clone($this);
    } else {
      $c11 = new Chord(
        array_addtoset(
          $tone + 2, array_removefromset($tone, $pitchset)
        )
      );
    }
    if (in_array($tone - 1, $pitchset)) {
      $c2 = clone($this);
    } else {
      $c2 = new Chord(
        array_addtoset(
          $tone - 1, array_removefromset($tone, $pitchset)
        )
      );
    }
    if (in_array($tone - 2, $pitchset)) {
      $c22 = clone($this);
    } else {
      $c22 = new Chord(
        array_addtoset(
          $tone - 2, array_removefromset($tone, $pitchset)
        )
      );
    }

    return array($c1, $c11, $c2, $c22);
  }

  /**
   *  To allow sorting of an array of chords by DSG
   *
   * @param Chord $a
   * @param Chord $b
   * @return int
   */
  public static function sortByDSG(Chord $a, Chord $b)
  {
    $dsga = $a->getDissonance();
    $dsgb = $b->getDissonance();
    if ($dsga == $dsgb) {
      return 0;
    }
    return ($dsga > $dsgb) ? 1 : -1;
  }

  /**
   * Analyzes a given chord (array of pitches) and returns the analysis
   *
   * @param array $pitchset array of integers (pitches)
   * @return array array containing the analysis of the chord
   */
  private function _scan(array $pitchset)
  {
    $octaves = $this->_takestockofoctaves($pitchset);
    $chordrec = $this->_doDSG($pitchset, $octaves);
    $chordrec['hifu'] = $this->_doghm($chordrec); // Calculate Hindemith Root

    return $chordrec;
  }

  /**
   * Calculate which notes in a pitchset are octaves
   *
   * @param array $pitchset array of integers (pitches)
   * @return array array with octaves
   */
  private function _takestockofoctaves(array $pitchset)
  {
    $oct = array(1 => array(),
      2 => array(),
      3 => array(),
      4 => array(),
      5 => array(),
      6 => array(),
      7 => array(),
      8 => array());

    foreach ($pitchset as $p) {
      foreach ($pitchset as $q) {
        if ($q > $p - 11) {
          continue;
        }
        if (in_array($q, $oct[7]) && ($p - $q) % 12 == 0) {
          $oct[8] = array_addtoset($p, $oct[8]);
        } elseif (in_array($q, $oct[6]) && ($p - $q) % 12 == 0) {
          $oct[7] = array_addtoset($p, $oct[7]);
        } elseif (in_array($q, $oct[5]) && ($p - $q) % 12 == 0) {
          $oct[6] = array_addtoset($p, $oct[6]);
        } elseif (in_array($q, $oct[4]) && ($p - $q) % 12 == 0) {
          $oct[5] = array_addtoset($p, $oct[5]);
        } elseif (in_array($q, $oct[3]) && ($p - $q) % 12 == 0) {
          $oct[4] = array_addtoset($p, $oct[4]);
        } elseif (in_array($q, $oct[2]) && ($p - $q) % 12 == 0) {
          $oct[3] = array_addtoset($p, $oct[3]);
        } elseif (in_array($q, $oct[1]) && ($p - $q) % 12 == 0) {
          $oct[2] = array_addtoset($p, $oct[2]);
        } elseif (in_array($q, $pitchset) && ($p - $q) % 12 == 0) {
          $oct[1] = array_addtoset($p, $oct[1]);
        }
      }
    }

    return $oct;
  }

  /**
   * Dissonance Grading of a chord
   *
   * @param array $pitchset array of integers (pitches)
   * @param array $octaves array of octaves
   * @return array array containing the dissonance grading of the chord
   */
  private function _doDSG(array $pitchset, array $octaves)
  {
    $sumcontrib = 0;

    foreach ($pitchset as $key => $p) {
      $contribp = 0;
      foreach ($pitchset as $q) {
        if (in_array($p, $octaves[8])) {
          $contribp += $this->_intDSG(abs($p - $q) + (8 * 12));
        } elseif (in_array($p, $octaves[7])) {
          $contribp += $this->_intDSG(abs($p - $q) + (7 * 12));
        } elseif (in_array($p, $octaves[6])) {
          $contribp += $this->_intDSG(abs($p - $q) + (6 * 12));
        } elseif (in_array($p, $octaves[5])) {
          $contribp += $this->_intDSG(abs($p - $q) + (5 * 12));
        } elseif (in_array($p, $octaves[4])) {
          $contribp += $this->_intDSG(abs($p - $q) + (4 * 12));
        } elseif (in_array($p, $octaves[3])) {
          $contribp += $this->_intDSG(abs($p - $q) + (3 * 12));
        } elseif (in_array($p, $octaves[2])) {
          $contribp += $this->_intDSG(abs($p - $q) + (2 * 12));
        } elseif (in_array($p, $octaves[1])) {
          $contribp += $this->_intDSG(abs($p - $q) + 12);
        } else {
          $contribp += $this->_intDSG(abs($p - $q));
        }
      }
      $chordrec['stru'][$key] = array('pitch' => $p, 'contribution' => $contribp, 'name' => $this->_nameOf($p));
      $sumcontrib += $contribp;
    }

    $chordrec['total'] = floor($sumcontrib / 2);

    $n = count($pitchset);

    if ($n > 0) {
      $chordrec['diss'] = floor($sumcontrib / $n);
      if ($chordrec['diss'] >= 0 && $chordrec['diss'] <= 3) {
        $chordrec['class'] = 'I';
      } elseif ($chordrec['diss'] >= 4 && $chordrec['diss'] <= 8) {
        $chordrec['class'] = 'II';
      } elseif ($chordrec['diss'] >= 9 && $chordrec['diss'] <= 21) {
        $chordrec['class'] = 'III';
      } elseif ($chordrec['diss'] >= 22 && $chordrec['diss'] <= 55) {
        $chordrec['class'] = 'IV';
      } else {
        $chordrec['class'] = 'V';
      }
    } else {
      $chordrec['diss'] = 0;
      $chordrec['class'] = 'none';
    }

    return $chordrec;
  }

  /**
   * Calculate Hindemith Root of a chord
   *
   * @param array $chordrec array containing the dissonance grading of the chord
   * @return int Hindemith Root of the chord
   */
  private function _doghm(array $chordrec)
  {
    $sofarhf = 0;
    $hfweight = 0;
    // empty chord
    if (!array_key_exists('stru', $chordrec)) {
      return 0;
    }

    $chordsize = count($chordrec['stru']);
    $chordrec_numeric = array();
    foreach($chordrec['stru'] as $k => $note) {
      $chordrec_numeric[$k] = array($note['pitch'], $note['contribution']);
    }

    for ($p = 0; $p < $chordsize; $p++) {
      for ($q = $p + 1; $q < $chordsize; $q++) {
        if ((($chordrec_numeric[$q][0] - $chordrec_numeric[$p][0]) % 12 == 7) && ($hfweight < 10)) {
          $hfweight = 10;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$q][0] - $chordrec_numeric[$p][0]) % 12 == 4) && ($hfweight < 8)) {
          $hfweight = 8;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$q][0] - $chordrec_numeric[$p][0]) % 12 == 3) && ($hfweight < 6)) {
          $hfweight = 6;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$q][0] - $chordrec_numeric[$p][0]) % 12 == 10) && ($hfweight < 4)) {
          $hfweight = 4;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$q][0] - $chordrec_numeric[$p][0]) % 12 == 11) && ($hfweight < 2)) {
          $hfweight = 2;
          $sofarhf = $chordrec_numeric[$p][0];
        }
      }
      for ($r = $p - 1; $r >= 0; $r--) {
        if ((($chordrec_numeric[$p][0] - $chordrec_numeric[$r][0]) % 12 == 5) && ($hfweight < 9)) {
          $hfweight = 9;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$p][0] - $chordrec_numeric[$r][0]) % 12 == 8) && ($hfweight < 7)) {
          $hfweight = 7;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$p][0] - $chordrec_numeric[$r][0]) % 12 == 9) && ($hfweight < 5)) {
          $hfweight = 5;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$p][0] - $chordrec_numeric[$r][0]) % 12 == 2) && ($hfweight < 3)) {
          $hfweight = 3;
          $sofarhf = $chordrec_numeric[$p][0];
        }
        if ((($chordrec_numeric[$p][0] - $chordrec_numeric[$r][0]) % 12 == 1) && ($hfweight < 1)) {
          $hfweight = 1;
          $sofarhf = $chordrec_numeric[$p][0];
        }
      }
    }

    return array('pitch' => $sofarhf, 'name' => $this->_nameOf($sofarhf));
  }

  /**
   * Calculate dissonance value of an interval (not taking into account possible octave positions of notes)
   *
   * @param int $interval interval to be analyzed
   * @return int dissonance value
   */
  private function _intDSG($interval)
  {
    $intclassvalues = array(0 => 1, // prime
      1 => 9, // min sec
      2 => 7, // maj sec
      3 => 4, // min third
      4 => 4, // maj third
      5 => 3, // fourth
      6 => 5, // tritone
      7 => 2, // fifth
      8 => 4, // min sixth
      9 => 4, // maj sixth
      10 => 6, // min seventh
      11 => 8); // maj seventh


    $intdsgvalues = array(1 => 0,
      2 => 1,
      3 => 2,
      4 => 3,
      5 => 5,
      6 => 8,
      7 => 13,
      8 => 21,
      9 => 34);

    $intclass = $intclassvalues[$interval % 12];

    $div = floor($interval / 12);

    if ($div >= 1 && $div <= 7) {
      if ($intclass > $div) {
        $intclass -= $div;
      } else {
        $intclass = 1;
      }
    }

    if ($div > 7) {
      if ($intclass > 8) {
        $intclass -= 8;
      } else {
        $intclass = 1;
      }
    }

    return $intdsgvalues[$intclass];
  }

  /**
   * Translates user input, a string which represents a chord, into a set of pitches
   *
   * @param string $chordstring user input: a string containing zero or more notes
   * @return array set of pitches, extracted from the inputstring
   */
  private function _makeintoset($chordstring)
  {

    $pitchset = array();
    // why 111? I don't know, but I'm leaving it in because it
    // possibly has a secret, magical significance
    $pitch = 111;
    $notes = array('a' => 21,
      'b' => 23,
      'c' => 12,
      'd' => 14,
      'e' => 16,
      'f' => 17,
      'g' => 19);

    $chordstring = trim(strtolower($chordstring));

    while ($chordstring != '') {
      // check first character of string
      $char = $chordstring[0];
      if (!array_key_exists($char, $notes)) {
        // if not valid, chop off
        $chordstring = substr($chordstring, 1);
        // and start over
        continue;
      }
      // if first character is valid
      if (array_key_exists($char, $notes)) {
        // found beginning of note
        $pitch = $notes[$char];
        // chop off first character of string
        $chordstring = substr($chordstring, 1);
        // continue with next one
        $char = $chordstring[0];
      }
      // check if next character modifies the note
      // (sharp, flat, octave number)
      // if so, chop off character
      if ($char == '#') {
        $pitch += 1;
        $chordstring = substr($chordstring, 1);
        $char = $chordstring[0];
      }
      if ($char == 'b') {
        $pitch -= 1;
        $chordstring = substr($chordstring, 1);
        $char = $chordstring[0];
      }
      if (is_numeric($char) && intval($char) >= 0 && intval($char) <= 8) {
        // only if an octave number (0-8) is found, add the note to the pitchset
        $oct = ord($char) - 48;
        $pitch += ($oct * 12);
        $chordstring = substr($chordstring, 1);
        $pitchset = array_addtoset($pitch, $pitchset);
      }
    }

    sort($pitchset);

    return $pitchset;
  }

  /**
   * Translates integer denoting pitch into the string that is its name
   *
   * @param int $pitch integer denoting a pitch
   * @return string name of the pitch
   */
  private function _nameOf($pitch)
  {

    if ($pitch == 0) {
      return 'xxx';
    }

    $notenames = array(0 => 'c',
      1 => 'c#',
      2 => 'd',
      3 => 'd#',
      4 => 'e',
      5 => 'f',
      6 => 'f#',
      7 => 'g',
      8 => 'g#',
      9 => 'a',
      10 => 'a#',
      11 => 'b');

    $chrom = $notenames[($pitch - 12) % 12];
    $oct = chr(floor((($pitch - 12) / 12)) + 48);

    return $chrom . $oct;
  }
}
