<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * Contains functions for overall program logic, user input and program output
 *
 * @package DSG
 * @subpackage cli
 */

/**
 * Program flow and user interaction related to working with chords
 *
 * @param bool $newchord whether the user has entered a new chord, or if the last entered chord should be reused
 * @param bool|resource $fh (if program output is to be saved to file) or bool FALSE if not
 * @global $last_fed_in_chord string used to save original chord provided by the user
 */
function work(bool $newchord, $fh)
{

    global $last_fed_in_chord;

    if ($newchord) {
        $cr = choosestartingposition();
        $last_fed_in_chord = $cr;
    } else {
        $cr = $last_fed_in_chord;
    }

    $chord = new Chord($cr);
    echo $chord->getTextualAnalysis();
    if ($fh !== false) {
        fwrite($fh, $chord->getTextualAnalysis());
    }
    $dsg = new DSG();

    while (1) {
        $chord = changebyhand($chord, $fh);
        $dsg->setChord($chord);
        $ch = makechoice(array('w', 'W', 'a', 'A'), 'Shall I work on it, or abandon it? (W/A)');
        if ($ch == 'w' || $ch == 'W') {
            $ch = makechoice(array('t', 'T', 'p', 'P'),
              'Aiming for a target dissonance, or using parallel subset motion? (T/P)');
            if ($ch == 't' || $ch == 'T') {
                echo 'Enter the target DSG:' . "\n";
                echo '--> ';
                $dsg->changeCurrentChordByTargetDSG(intval(trim(fgets(STDIN))));
            } else {
                $ch = makechoice(array('i', 'I', 'd', 'D'), 'Increasing, or diminishing the dissonance? (I/D)');
                if (in_array($ch, array('i', 'I'))) {
                    $ch = 'inc';
                } else {
                    $ch = 'dim';
                }
                $dsg->changeCurrentChordByPSM($ch);
            }
            echo $dsg->getCurrentChord()->getTextualAnalysis();
            if ($fh !== false) {
                fwrite($fh, $dsg->getCurrentChord()->getTextualAnalysis());
            }
        } elseif ($ch == 'a' || $ch == 'A') {
            break;
        }
        $chord = $dsg->getCurrentChord();
    }
}

/**
 * Receive a string representing a chord from the user, return array with analysis
 *
 * @return string representing a chord
 */
function choosestartingposition(): string
{

    echo 'Your CHORD (format something like: d4-g#4-c#5, or f4db4bb3ab4,' . "\n";
    echo '   each note name to be ended by ';
    echo 'its octave position digit (a440 = a4))' . "\n";
    echo '--> ';
    return trim(fgets(STDIN));
}

/**
 * Add pitches to or remove pitches from chord by hand
 *
 * @param Chord $chord
 * @param bool|resource $fh (if program output is to be saved to file) or bool FALSE if not
 * @return Chord changed chord
 */
function changebyhand(Chord $chord, $fh): Chord
{

    while (1) {
        $chset = $chord->getPitchSet();
        $ch = makechoice(array('y', 'Y', 'n', 'N'), 'Do you wish to change this chord by hand? (Y/N)');
        if ($ch == 'y' || $ch == 'Y') {
            echo 'Type pitches to be added (if any)' . "\n";
            echo '--> ';
            $chord_in = new Chord(trim(fgets(STDIN)));
            echo 'Type pitches to be deleted (if any)' . "\n";
            echo '--> ';
            $chord_out = new Chord(trim(fgets(STDIN)));
            $chset = array_removefromset($chord_out->getPitchSet(), array_addtoset($chord_in->getPitchSet(), $chset));
            $chord = new Chord($chset);
            echo $chord->getTextualAnalysis();
            if ($fh !== false) {
                fwrite($fh, $chord->getTextualAnalysis());
            }
        } elseif ($ch == 'n' || $ch == 'N') {
            break;
        }
    }
    return $chord;
}


/**
 * Receive user input to decide if results are to be saved to file
 *
 * @return resource|bool file handle (if program output is to be saved to file) or bool FALSE if not
 */
function decidewhethertoprint()
{

    echo 'Do you want results stored in printable diskfile? (Y/N)' . "\n";
    echo '--> ';
    $ch = trim(fgets(STDIN));
    $fh = false;
    if ($ch != 'n' && $ch != 'N') {
        echo 'Name your file: --> ';
        $filename = trim(fgets(STDIN));
        while (file_exists($filename)) {
            echo "File $filename already exists. Overwrite? (Y/N)";
            echo '--> ';
            $ch = trim(fgets(STDIN));
            if ($ch == 'y' || $ch == 'Y') {
                break;
            } else {
                echo 'Name your file:';
                echo '--> ';
                $filename = trim(fgets(STDIN));
            }
        }
        if ($filename != '') {
            $fh = fopen($filename, 'wb') or die('Error: cannot open file ' . $filename . '. Quitting.' . "\n");
        }
    }
    return $fh;
}

/**
 * Utility function to keep repeating a prompt string until a valid choice has been made by the user
 *
 * @param array $validchoice array with valid user input
 * @param string $promptstring prompt string asking for user input
 * @return string user input
 */
function makechoice(array $validchoice, string $promptstring): string
{

    $ch = '';
    while (1) {
        echo $promptstring . "\n";
        echo '--> ';
        $ch = trim(fgets(STDIN));
        if (in_array($ch, $validchoice)) {
            break;
        }
    }

    return $ch;
}

/**
 * Five screens of introductory text
 */
function introduction()
{

    $goon = true;
    while (1) {
        echo "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        echo '                      DISSONANCE GRADING PROGRAM' . "\n";
        echo '   by Jos Kunst, who is indebted to Jan Vriend and Jan Pieter Kunst' . "\n";
        echo "\n";
        echo '                PHP port (c) 2006 by Jan Pieter Kunst' . "\n";
        echo "\n";
        echo '                            Version 2.0.1' . "\n";
        echo "\n";
        echo "\n";
        echo '                               *     *' . "\n";
        echo '                                  *' . "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        echo '     We first offer you five screens of explanatory text. If you' . "\n";
        echo '          don\'t want to see them any more, press W for just' . "\n";
        echo '                 going to work; else press any key.' . "\n";
        echo '--> ';
        $ch = trim(fgets(STDIN));
        if ($ch == 'W' || $ch == 'w') {
            $goon = false;
        } else {
            echo "\n";
            echo "\n";
            echo '   RATIONALE. This program is meant to be a pre-compositional tool' . "\n";
            echo '   that scans and/or modifies chords along the dimension of' . "\n";
            echo '   dissonance - an uneasy concept if there ever was one; here it' . "\n";
            echo '   is perhaps best understood as referring to something like the' . "\n";
            echo '   opacity of chords: the degree to which it might be difficult to' . "\n";
            echo '   "hear through them" any new sound not already contained in them.' . "\n";
            echo '   Be that as it may; we let the test measure what it does, in fact,' . "\n";
            echo '   measure and its usefulness remain to be seen.' . "\n";
            echo "\n";
            echo '   ASSUMPTIONS. A chord\'s dissonance is assumed to be a function of' . "\n";
            echo '   its component intervals. Intervals within the octave are ordered' . "\n";
            echo '   from dissonant to consonant (minor second - major seventh - ' . "\n";
            echo '   major second - minor seventh - augmented fourth/diminished' . "\n";
            echo '   fifth - all thirds and sixths - perfect fourth - perfect fifth -' . "\n";
            echo '   octave), and all intervals wider than those are proportionally' . "\n";
            echo '   downgraded, a minor second plus octave taking the value of a' . "\n";
            echo '   major seventh, a major seventh plus octave taking the value of a' . "\n";
            echo '   major second, and so on. Furthermore, the possible contribution' . "\n";
            echo '   of a tone that has also its own lower octave(s) in the chord is' . "\n";
            echo '   correspondingly lessened.' . "\n";
            echo "\n";
            echo '--------------------------------------------------------------------' . "\n";
            echo '   1st PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.' . "\n";
            echo '--> ';
            $ch = trim(fgets(STDIN));
        }
        if ($ch == 'W' || $ch == 'w') {
            $goon = false;
        } else {
            echo "\n";
            echo "\n";
            echo '   USES. In the first place, the program may be used as a chord-' . "\n";
            echo '   scanner. In that case, it displays four things: 1) each note\'s' . "\n";
            echo '   contribution to the chord\'s overall dissonance, 2) the chord\'s' . "\n";
            echo '   dissonance total, which is a number such that if you subtract ' . "\n";
            echo '   from it the contribution of any of the chord\'s component notes' . "\n";
            echo '   (octaves make for exceptions here, sorry), you get the total of' . "\n";
            echo '   another chord that is just like this one except for the absence' . "\n";
            echo '   of that component note; 3) the dsg ("dissonance grading"), which' . "\n";
            echo '   is twice that total divided by the number of component notes,' . "\n";
            echo '   and 4) the dsg class, which positions the chord in one of the' . "\n";
            echo '   five musically interesting subareas in the dissonance continuum' . "\n";
            echo '   we have seen fit to number, roman-style, I through V, in the' . "\n";
            echo '   following way:      dsg class:     I    II   III  IV    V' . "\n";
            echo '                       dsg:           0 -->3 -->8 -->21 -->55 -->' . "\n";
            echo "\n";
            echo '   In the second place, the program may be used in some preparatory' . "\n";
            echo '   stage of musical composition. It will try to increase or to' . "\n";
            echo '   diminish, at your choice, the dissonance of any chord you care' . "\n";
            echo '   to specify. It offers you the choice of two ways to do it:' . "\n";
            echo '     (1) by setting it a target dsg, and letting it work its way' . "\n";
            echo '         toward that target for as far as it can, or' . "\n";
            echo '     (2) by parallel subset (semitone) motion.' . "\n";
            echo "\n";
            echo '--------------------------------------------------------------------' . "\n";
            echo '   2nd PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.' . "\n";
            echo '--> ';
            $ch = trim(fgets(STDIN));
        }
        if ($ch == 'W' || $ch == 'w') {
            $goon = false;
        } else {
            echo '                  Option (1): Going for a Target' . "\n";
            echo "\n";
            echo '   When (after entering your starting chord) you choose the Target' . "\n";
            echo '   option, the program prompts you for the dissonance to be real-' . "\n";
            echo '   ized. It then sets about attaining this goal in the smallest' . "\n";
            echo '   possible number of (minor or major second) steps, keeping you' . "\n";
            echo '   informed of its progress through its screen and/or printer' . "\n";
            echo '   output. In determining the steps to be taken it is guided by' . "\n";
            echo '   principles essentially akin to those governing voice-leading,' . "\n";
            echo '   such as: in diminishing dissonance, take first the tone con-' . "\n";
            echo '   tributing most to it; do not conflate two tones into one,' . "\n";
            echo '   etc., and the converse for increasing dissonance. This strategy ' . "\n";
            echo '   results in a tendency towards equal distribution of dissonance:' . "\n";
            echo '   extremely unequal contributions of individual notes, which (I' . "\n";
            echo '   think) make for the special combination of spiciness and' . "\n";
            echo '   clarity of many jazz chords, become less likely as the program' . "\n";
            echo '   proceeds in this mode. The other limitation inherent in the' . "\n";
            echo '   target mode lies in the fact that, e.g., starting with eight' . "\n";
            echo '   pitches within a space of an octave you obviously cannot get' . "\n";
            echo '   below a certain dissonance. If you don\'t like these limita-' . "\n";
            echo '   tions, use, or mix in, the parallel subset and the change-by-' . "\n";
            echo '   hand modes.' . "\n";
            echo "\n";
            echo '--------------------------------------------------------------------' . "\n";
            echo '   3d PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.' . "\n";
            echo '--> ';
            $ch = trim(fgets(STDIN));
        }
        if ($ch == 'W' || $ch == 'w') {
            $goon = false;
        } else {
            echo '                Option (2): Parallel Subset Motion' . "\n";
            echo "\n";
            echo '   When you choose this option, you accept primes as intervals -' . "\n";
            echo '   two pitches, in working towards consonance, may get fused into' . "\n";
            echo '   one. (The only way of achieving the converse, i.e. of splitting' . "\n";
            echo '   a single tone in two, is to do it by hand.) Next, you actually' . "\n";
            echo '   enforce change: if the chord was already maximally consonant,' . "\n";
            echo '   and you try to make it more consonant still, you end up with a' . "\n";
            echo '   slightly less consonant chord instead. Lastly, Parallel Subset' . "\n";
            echo '   Motion, or PSM for short, brings home a freedom that every com-' . "\n";
            echo '   poser working with this program will have to face: it does find' . "\n";
            echo '   effective and elegant ways of changing a chord\'s dissonance,' . "\n";
            echo '   but it does not tell you much about the precise spatial re-' . "\n";
            echo '   lations of the resulting two consecutive chords. It may propose' . "\n";
            echo '   you a subset motion of three pitches out of five: obviously, you' . "\n";
            echo '   may do better by moving the other two, or by moving the whole' . "\n";
            echo '   chord in steps of different lengths for its two subchords. (Note' . "\n";
            echo '   that this goes not only for PSM: the results of the Target op-' . "\n";
            echo '   tion leave you with exactly the same kind of freedom; as we all' . "\n";
            echo '   know, letting an (almost) whole chord move in order to relieve' . "\n";
            echo '   the dissonance of even a single motionless note is not unheard' . "\n";
            echo '   of in good music.)' . "\n";
            echo "\n";
            echo '--------------------------------------------------------------------' . "\n";
            echo '   4th PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.' . "\n";
            echo '--> ';
            $ch = trim(fgets(STDIN));
        }
        if ($ch == 'W' || $ch == 'w') {
            $goon = false;
        } else {
            echo '   THE HINDEMITH ROOT. As an extra bonus, the user gets the chord\'s' . "\n";
            echo '   root thrown in, as it is computed by applying a slightly gener-' . "\n";
            echo '   alized version of the Hindemith Unterweisung im Tonsatz method.' . "\n";
            echo '   Because of the generalizations results will not always coincide' . "\n";
            echo '   with Hindemith\'s own. Thus, the program will not balk at chords' . "\n";
            echo '   with less than three pitches; it will also assign their roots' . "\n";
            echo '   to chords considered to be undecidable, such as the augmented' . "\n";
            echo '   triad, the chord built up out of two perfect fourths, and the' . "\n";
            echo '   diminished seventh chord, and it will do that by applying the' . "\n";
            echo '   same methods as everywhere else. Most importantly, we are in dis-' . "\n";
            echo '   agreement with the master because we think that his method, de-' . "\n";
            echo '   pendent as it is on the analysis of isolated chords, provides no' . "\n";
            echo '   solid basis at all for musical composition. (Composers are, as' . "\n";
            echo '   always, on their own.) However, its results are never simply' . "\n";
            echo '   foolish, which is why we have included it. An example. The user' . "\n";
            echo '   should suspend any notions about chords being incomplete - for' . "\n";
            echo '   the program, all chords are complete, and the chord c4-b4-d5 is' . "\n";
            echo '   assigned b4 as its root, because the third is the best interval' . "\n";
            echo '   contained in it. One might therefore say that, if the chord is' . "\n";
            echo '   incomplete after all, the program would have preferred to add' . "\n";
            echo '   f#4 and a4 to it rather than e4 and g4. Hindemith-style serious' . "\n";
            echo '   results are to be expected only for complete chords.' . "\n";
            echo "\n";
            echo '--------------------------------------------------------------------' . "\n";
            echo '   5th (LAST) PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.' . "\n";
            echo '--> ';
            $ch = trim(fgets(STDIN));
        }
        if ($ch == 'W' || $ch == 'w') {
            $goon = false;
        }
        if (!$goon) {
            break;
        }
    }
}


