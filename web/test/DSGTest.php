<?php

error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

/** @noinspection PhpIncludeInspection */
require_once('../includes/DSG.class.php');

class DSGTest extends PHPUnit\Framework\TestCase
{

  /** @var $_dsg DSG */
  private $_dsg;

  public function  setUp(): void
  {
    $this->_dsg = new DSG();
  }

  public function tearDown(): void
  {
    unset($this->_dsg);
  }

  private function _getPitches($chordrec)
  {
    $pitches = array();
    if (array_key_exists('stru', $chordrec)) {
      foreach($chordrec['stru'] as $note) {
        $pitches[] = $note['pitch'];
      }
    }
    return $pitches;
  }

  private function _getNames($chordrec)
  {
    $names = array();
    if (array_key_exists('stru', $chordrec)) {
      foreach($chordrec['stru'] as $note) {
        $names[] = $note['name'];
      }
    }
    return $names;
  }


  private function _getContributions($chordrec)
  {
    $contributions = array();
    if (array_key_exists('stru', $chordrec)) {
      foreach($chordrec['stru'] as $note) {
        $contributions[] = $note['contribution'];
      }
    }
    return $contributions;
  }


  public function testAnalyzedStructurePitches()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = array( 58, 61, 65,  68);
    $actual = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public function testAnalyzedStructureContributions()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = array( 12,7,7,12);
    $actual = $this->_getContributions($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }


  public function testAnalyzedStructureNames()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = array( 'a#3', 'c#4', 'f4', 'g#4');
    $actual = $this->_getNames($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }


  public function testDissonanceTotal()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = 19;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['total']);
  }

  public function testDissonanceGrading()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = 9;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['diss']);
  }

  public function testDissonanceClass()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = 'III';
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['class']);
  }

  public function testHindemithRoot()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = array('pitch' => 58, 'name' => 'a#3');
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['hifu']);
  }

  public function testChangeCurrentChordByPSMIncPitches()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = array(58, 60, 64, 68 );
    $actual = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public function testChangeCurrentChordByPSMIncNames()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = array('a#3', 'c4', 'e4', 'g#4');
    $actual = $this->_getNames($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }


  public function testChangeCurrentChordByPSMContributions()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = array(26,19,11,14);
    $actual = $this->_getContributions($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public function testChangeCurrentChordByPSMGrading()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = 17;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['diss']);
  }


  public function testChangeCurrentChordByPSMTotal()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = 35;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['total']);
  }

  public function testChangeCurrentChordByPSMClass()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = 'III';
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['class']);
  }

  public function testChangeCurrentChordByPSMHindemithRoot()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByPSM('inc');
    $expected = array('pitch' => 60, 'name' => 'c4');
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['hifu']);
  }


  public  function testChangeCurrentChordByPSMDimPitches()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = array(57, 60, 64, 67);
    $actual = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public  function testChangeCurrentChordByPSMDimNames()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = array('a3','c4', 'e4','g4');
    $actual = $this->_getNames($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public  function testChangeCurrentChordByPSMDimContributions()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = array(12,7,7,12);
    $actual = $this->_getContributions($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public  function testChangeCurrentChordByPSMDimGrading()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = 9;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['diss']);
  }
  public  function testChangeCurrentChordByPSMDimTotal()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = 19;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['total']);
  }
  public  function testChangeCurrentChordByPSMDimClass()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = 'III';
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['class']);
  }
  public  function testChangeCurrentChordByPSMDimHindemithRoot()
  {
    $this->_dsg->setChord(new Chord('a#3-c4-e4-g#4'));
    $this->_dsg->changeCurrentChordByPSM('dim');
    $expected = array('pitch' => 57,'name' => 'a3');
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['hifu']);
  }



  public  function testChangeCurrentChordByTargetDissDownPitches()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = array(56, 61, 65, 68);
    $actual = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public  function testChangeCurrentChordByTargetDissDownNames()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = array('g#3', 'c#4', 'f4', 'g#4');
    $actual = $this->_getNames($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public  function testChangeCurrentChordByTargetDissDownContributions()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = array( 5, 6, 9,2);
    $actual = $this->_getContributions($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }
  public  function testChangeCurrentChordByTargetDissDownTotal()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = 11;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['total']);
  }
  public  function testChangeCurrentChordByTargetDissDownGrading()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = 5;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['diss']);
  }
  public  function testChangeCurrentChordByTargetDissDownClass()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = 'II';
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['class']);
  }
  public  function testChangeCurrentChordByTargetDissDownHindemithRoot()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(0);
    $expected = array('pitch' => 61, 'name' => 'c#4');
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['hifu']);
  }


  public  function testChangeCurrentChordByTargetDissUpPitches()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = array(57, 58, 67, 68);
    $actual = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }

  // bugje met referentie ipv kopie in Chord->movetonefourpossibleways
  // gaf hier verkeerde output
  public  function testChangeCurrentChordByTargetDissUpPitches2()
  {
    $this->_dsg->setChord(new Chord('f#3-e4-f4-f#4'));
    $this->_dsg->changeCurrentChordByTargetDSG(50);
    $expected = array(54, 64, 65, 66);
    $actual = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }


  public  function testChangeCurrentChordByTargetDissUpNames()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = array('a3', 'a#3', 'g4', 'g#4');
    $actual = $this->_getNames($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }
  public  function testChangeCurrentChordByTargetDissUpContributions()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = array(63, 45, 45, 63);
    $actual = $this->_getContributions($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->assertEquals($expected, $actual);
  }
  public  function testChangeCurrentChordByTargetDissUpTotal()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = 108;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['total']);
  }
  public  function testChangeCurrentChordByTargetDissUpGrading()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = 54;
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['diss']);
  }
  public  function testChangeCurrentChordByTargetDissUpClass()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = 'IV';
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['class']);
  }

  public  function testChangeCurrentChordByTargetDissUpHindemithRoot()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $expected = array('pitch' => 67,'name' => 'g4');
    $actual = $this->_dsg->getCurrentChord()->getChordAnalysis();
    $this->assertEquals($expected, $actual['hifu']);
  }

  public  function testgetLastFedInChord()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $this->_dsg->setLastFedInChord(new Chord('f4db4bb3ab4'));
    $fedinchord = $this->_getPitches($this->_dsg->getCurrentChord()->getChordAnalysis());
    $this->_dsg->changeCurrentChordByTargetDSG(100);
    $this->_dsg->changeCurrentChordByPSM('inc');
    $this->_dsg->changeCurrentChordByTargetDSG(50);
    $this->_dsg->changeCurrentChordByPSM('inc');
    $lastfedinchord = $this->_getPitches($this->_dsg->getLastFedInChord()->getChordAnalysis());
    $this->assertEquals($fedinchord, $lastfedinchord);
  }

  public function testgetTextualAnalysis()
  {
    $this->_dsg->setChord(new Chord('f4db4bb3ab4'));
    $expected = 'a#3<12> c#4<7> f4<7> g#4<12> ' . "\n" . 'TOTAL--><19>; DSG is 9 (class: III); Hindemith Root: a#3';
    $actual = trim($this->_dsg->getCurrentChord()->getTextualAnalysis());
    $this->assertEquals($expected, $actual);
  }

  public function testgetMIDItext()
  {
    $this->_dsg->setChord(new Chord('d4-g#4-c#5-f4db4bb3ab4'));
    $expected = 'MFile 0 1 480
MTrk
TimestampType=Delta
0 TimeSig 4/4 24 8
0 Tempo 500000
0 PrCh ch=1 p=20
0 On ch=1 n=58 v=100
0 On ch=1 n=61 v=100
0 On ch=1 n=62 v=100
0 On ch=1 n=65 v=100
0 On ch=1 n=68 v=100
0 On ch=1 n=73 v=100
2000 Off ch=16 n=1 v=0
0 Meta TrkEnd
TrkEnd';
    $actual = $this->_dsg->getCurrentChord()->getMIDItext();
    $this->assertEquals($expected, $actual);
  }

}


