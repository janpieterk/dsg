<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 


/**
 * Utility functions to emulate Pascal data type 'set' with PHP arrays
 */
require('set_emulation.inc.php');

require('Chord.class.php');

/**
 * Class which handles chord transformations to change the dissonance grading
 *
 * @package DSG
 */
class DSG
{
  /**
   * @var Chord
   */
  private Chord $_chord;

  /**
   * @var Chord
   */
  private Chord $_lastfedinchord;

  /**
   * Provides the class with a chord to work with
   *
   * @param Chord $chord
   */
  public function setChord(Chord $chord)
  {
    $this->_chord = $chord;
  }

    /**
     * Saves the last manually entered chord
     *
     * @param Chord $chord
     */
  public function setLastFedInChord(Chord $chord)
  {
    $this->_lastfedinchord = $chord;
  }

  /**
   * Returns the chord being worked on, in its current state
   *
   * @return Chord
   */
  public function getCurrentChord(): Chord
  {
    return $this->_chord;
  }


  /**
   * Returns the last manually entered chord
   *
   * @return Chord
   */
  public function getLastFedInChord(): Chord
  {
    return $this->_lastfedinchord;
  }

  /**
   * Changes the current chord as far as possible in the direction of the target DSG
   *
   * @param int $targetdsg the desired target DSG
   */
  public function changeCurrentChordByTargetDSG(int $targetdsg)
  {
    while (1) {
      $sofardsg = $this->_chord->getDissonance();
      $this->_chord = $this->_targetrecto($targetdsg);
      // this essentially meaning: if _targetrecto cannot change the dissonance
      // any further in the direction of the desired target dissonance,
      // i.e. if the dissonance before and after _targetrecto is the same, break out of the loop
      if ($sofardsg == $this->_chord->getDissonance())
        break;
    }
  }

  /**
   * Changes the current chord by Parallel Subset Motion. Possible values of $type:
   * <b>inc</b> for increasing the dissonance or <b>dim</b> for diminishing the dissonance
   *
   * @param string $type the desired direction of the change: increasing or diminishing dissonance
   */
  public function changeCurrentChordByPSM(string $type)
  {
    if ($type == 'inc') {
      $this->_chord = $this->_psmdissrec();
    } elseif ($type == 'dim') {
      $this->_chord = $this->_psmconsrec();
    }
  }


  /**
   * Changes dissonance of a chord in the direction of the supplied target dissonance
   *
   * @param int $targetdiss the target dissonance
   * @return Chord changed chord structure
   */
  private function _targetrecto(int $targetdiss): Chord
  {
    $tmpchord = clone($this->_chord);
    $chset = $tmpchord->getPitchSet();

    if ($tmpchord->getDissonance() > $targetdiss) {
      $excset = array();
      while (1) {
        /** @var $c1 Chord */
        /** @var $c2 Chord */
        /** @var $c3 Chord */
        /** @var $c4 Chord */
        list($pmax, $c1, $c2, $c3, $c4) = $this->_downgrade($tmpchord, $excset);
        if (($c1->getDissonance() >= $targetdiss) && ($c1->getDissonance() < $tmpchord->getDissonance())) {
          $tmpchord = $c1;
        } elseif (($c2->getDissonance() >= $targetdiss) && ($c2->getDissonance() < $tmpchord->getDissonance())) {
          $tmpchord = $c2;
        } elseif (($c3->getDissonance() >= $targetdiss) && ($c3->getDissonance() < $tmpchord->getDissonance())) {
          $tmpchord = $c3;
        } elseif (($c4->getDissonance() >= $targetdiss) && ($c4->getDissonance() < $tmpchord->getDissonance())) {
          $tmpchord = $c4;
        }
        $excset = array_addtoset($pmax, $excset);
        if (array_subset($chset, $excset) || $tmpchord->getDissonance() == $targetdiss) {
          break;
        }
      }
    }

    if ($tmpchord->getDissonance() < $targetdiss) {
      $excset = array();
      while (1) {
        /** @var $c1 Chord */
        /** @var $c2 Chord */
        /** @var $c3 Chord */
        /** @var $c4 Chord */
        list($pmin, $c1, $c2, $c3, $c4) = $this->_upgrade($tmpchord, $excset);
        if (($c1->getDissonance() <= $targetdiss) && ($c1->getDissonance() > $tmpchord->getDissonance())) {
          $tmpchord = $c1;
        } elseif (($c2->getDissonance() <= $targetdiss) && ($c2->getDissonance() > $tmpchord->getDissonance())) {
          $tmpchord = $c2;
        } elseif (($c3->getDissonance() <= $targetdiss) && ($c3->getDissonance() > $tmpchord->getDissonance())) {
          $tmpchord = $c3;
        } elseif (($c4->getDissonance() <= $targetdiss) && ($c4->getDissonance() > $tmpchord->getDissonance())) {
          $tmpchord = $c4;
        }
        $excset = array_addtoset($pmin, $excset);
        if (array_subset($chset, $excset) || $tmpchord->getDissonance() == $targetdiss) {
          break;
        }
      }
    }

    return $tmpchord;
  }

  /**
   * Orders the chords resulting from the four possible second steps of the least dissonant tone, from high to low dsg
   *
   * @param Chord $chord
   * @param array $exceptpitchset pitches to be ignored by the findpmincontrib function
   * @return array array with the least dissonant tone and four possible chords resulting from moving it
   */
  private function _upgrade(Chord $chord, array $exceptpitchset): array
  {
    $retval = array();
    $pmin = $chord->findPMinContrib($exceptpitchset);
    // moveToneFourPossibleWays returns four chords
    $chords = $chord->moveToneFourPossibleWays($pmin);
    // Chords know how to sort themselves by their DSG
    usort($chords, array('Chord', 'sortByDSG'));
    /** @var $chord Chord */
    // highest DSG first: reverse the sorted array
    foreach(array_reverse($chords) as $chord) {
      $retval[] = $chord;
    }
    array_unshift($retval, $pmin);

    return $retval;
  }


  /**
   * Orders the chords resulting from the four possible second steps of the most dissonant tone, from low to high dsg
   *
   * @param Chord $chord
   * @param array $exceptpitchset array of pitches to be ignored by the findpmaxcontrib function
   * @return array array with the most dissonant tone and four possible chords resulting from moving it
   */
  private function _downgrade(Chord $chord, array $exceptpitchset): array
  {
    $retval = array();
    $pmax = $chord->findPMaxContrib($exceptpitchset);
    // moveToneFourPossibleWays returns four chords
    $chords = $chord->moveToneFourPossibleWays($pmax);
    // Chords know how to sort themselves by their DSG
    usort($chords, array('Chord', 'sortByDSG'));
    /** @var $chord Chord */
    foreach($chords as $chord) {
      $retval[] = $chord;
    }
    array_unshift($retval, $pmax);

    return $retval;
  }


  /**
   * Increases an analyzed chord's dissonance, using parallel subset motion
   *
   * @return Chord chord with increased dissonance
   */
  private function _psmdissrec(): Chord
  {
    $moved = array();
    $pmin = $this->_chord->findPMinContrib();
    $tobemoved = array($pmin);
    // trialchord: remove the least dissonant tone from the chord,
    // replace with that tone one half-step higher
    $trialchord = new Chord(
      array_addtoset(
        $pmin + 1, array_removefromset(
          $tobemoved, $this->_chord->getPitchSet()
        )
      )
    );
    $trialpitchset = $trialchord->getPitchSet();

    while (1) {
      $rupwardchord = $trialchord;
      $tobemoved = array_addtoset($trialchord->findPMaxDiff($this->_chord), $tobemoved);
      foreach ($tobemoved as $p) {
        $moved = array_addtoset($p + 1, $moved);
      }
      $trialchord = new Chord(
        array_addtoset(
          $moved, array_removefromset(
            $tobemoved, $trialpitchset
          )
        )
      );
      if ($trialchord->getTotal() <= $rupwardchord->getTotal()) {
        break;
      }
      $trialpitchset = $trialchord->getPitchSet();
    }

    $moved = array();
    $tobemoved = array($pmin);
    $trialchord = new Chord(
      array_addtoset(
        $pmin - 1, array_removefromset(
          $tobemoved, $this->_chord->getPitchSet()
        )
      )
    );
    $trialpitchset = $trialchord->getPitchSet();

    while (1) {
      $rdownwardchord = $trialchord;
      $tobemoved = array_addtoset($trialchord->findPMaxDiff($this->_chord), $tobemoved);
      foreach ($tobemoved as $p) {
        $moved = array_addtoset($p - 1, $moved);
      }
      $trialchord = new Chord(
        array_addtoset(
          $moved, array_removefromset(
            $tobemoved, $trialpitchset
          )
        )
      );
      if ($trialchord->getTotal() <= $rdownwardchord->getTotal()) {
        break;
      }
      $trialpitchset = $trialchord->getPitchSet();
    }

      if ($rdownwardchord->getTotal() >= $rupwardchord->getTotal()) {
      return $rdownwardchord;
    } else {
      return $rupwardchord;
    }
  }


  /**
   * Diminishes an analyzed chord's dissonance, using parallel subset motion
   *
   * @return Chord chord with diminished dissonance
   */
  private function _psmconsrec(): Chord
  {
    $moved = array();
    $pmax = $this->_chord->findPMaxContrib();
    $tobemoved = array($pmax);
    // trialchord: remove the most dissonant tone from the chord,
    // replace with that tone one half-step higher
    $trialchord = new Chord(
      array_addtoset(
        $pmax + 1, array_removefromset(
          $tobemoved, $this->_chord->getPitchSet()
        )
      )
    );
    $trialpitchset = $trialchord->getPitchSet();

    while (1) {
      $rupwardchord = $trialchord;
      $tobemoved = array_addtoset($this->_chord->findPMaxDiff($trialchord), $tobemoved);
      foreach ($tobemoved as $p) {
        $moved = array_addtoset($p + 1, $moved);
      }
      $trialchord = new Chord(
        array_addtoset(
          $moved, array_removefromset(
            $tobemoved, $trialpitchset
          )
        )
      );
      if ($trialchord->getTotal() >= $rupwardchord->getTotal()) {
        break;
      }
      $trialpitchset = $trialchord->getPitchSet();
    }

    $moved = array();
    $tobemoved = array($pmax);
    $trialchord = new Chord(
      array_addtoset(
        $pmax - 1, array_removefromset(
          $tobemoved, $this->_chord->getPitchSet()
        )
      )
    );
    $trialpitchset = $trialchord->getPitchSet();

    while (1) {
      $rdownwardchord = $trialchord;
      $tobemoved = array_addtoset($this->_chord->findPMaxDiff($trialchord), $tobemoved);
      foreach ($tobemoved as $p) {
        $moved = array_addtoset($p - 1, $moved);
      }
      $trialchord = new Chord(
        array_addtoset(
          $moved,  array_removefromset(
            $tobemoved, $trialpitchset
          )
        )
      );
      if ($trialchord->getTotal() >= $rdownwardchord->getTotal()) {
        break;
      }
      $trialpitchset = $trialchord->getPitchSet();
    }

      if ($rdownwardchord->getTotal() <= $rupwardchord->getTotal()) {
      return $rdownwardchord;
    } else {
      return $rupwardchord;
    }
  }

}

