<?php

// 	This program is copyright (c) 1993 - 2006 Jos Kunst, the Jos Kunst heirs. 
// 	This program is free software. You can redistribute it and/or modify it 
// 	under the terms of version 2 of the GNU General Public License, which you 
// 	should have received with it. 
// 	This program is distributed in the hope that it will be useful, but 
// 	without any warranty, expressed or implied. 

/**
 * @package DSG
 */
 
 /**
 * Configuration file
 */ 
require('dsg_config.inc.php');

if (isset($_GET['getstringcompact'])) {
	$getstringcompact = $_GET['getstringcompact'];
	$backlink = 'index.php?getstringcompact=' . urlencode($getstringcompact);
} else {
	$backlink = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="<?php echo DSG_CSS; ?>">
<title>Dissonance Grading Documentation</title>
</head>
<body id="dsgdocs">
<p id="toplink"><a href="<?php echo $backlink; ?>">Back to Dissonance Grading Program</a></p>
<h1>Dissonance Grading Documentation</h1>
<p id="dsgauthor">by Jos Kunst (1993)</p>
<h2>Rationale</h2>
<p>This program is meant to be a pre-compositional tool that scans and/or modifies chords along the dimension of dissonance &ndash; an uneasy concept if there ever was one; here it is perhaps best understood as referring to something like the opacity of chords: the degree to which it might be difficult to "hear through them" any new sound not already contained in them. Be that as it may; we let the test measure what it does, in fact, measure and its usefulness remain to be seen.</p>
<h2>Assumptions</h2>
<p>A chord's dissonance is assumed to be a function of its component intervals. Intervals within the octave are ordered from dissonant to consonant (minor second &#8211; major seventh &#8211; major second &#8211; minor seventh &#8211; augmented fourth/diminished fifth &#8211; all thirds and sixths &#8211; perfect fourth &#8211; perfect fifth &#8211; octave), and all intervals wider than those are proportionally downgraded, a minor second plus octave taking the value of a major seventh, a major seventh plus octave taking the value of a major second, and so on. Furthermore, the possible contribution of a tone that has also its own lower octave(s) in the chord is correspondingly lessened.</p>
<p>In the first place, the program may be used as a chordscanner. In that case, it displays four things: 1) each note's contribution to the chord's overall dissonance, 2) the chord's dissonance total, which is a number such that if you subtract from it the contribution of any of the chord's component notes (octaves make for exceptions here, sorry), you get the total of another chord that is just like this one except for the absence of that component note; 3) the dsg ("dissonance grading"), which is twice that total divided by the number of component notes, and 4) the dsg class, which positions the chord in one of the five musically interesting subareas in the dissonance continuum we have seen fit to number, roman-style, I through V, in the following way:</p>
<pre>
dsg class: I   II  III    IV    V
dsg:       0 --&gt;3 --&gt;8 --&gt;21 --&gt;55 --&gt;
</pre> 
<p>In the second place, the program may be used in some preparatory stage of musical composition. It will try to increase or to diminish, at your choice, the dissonance of any chord you care to specify. It offers you the choice of two ways to do it: (1) by setting it a target dsg, and letting it work its way toward that target for as far as it can, or (2) by parallel subset (semitone) motion.</p>
<h3>Option (1): Going for a Target</h3>
<p>When (after entering your starting chord) you choose the Target option, the program prompts you for the dissonance to be realized. It then sets about attaining this goal in the smallest possible number of (minor or major second) steps, keeping you informed of its progress through its screen and/or printer output. In determining the steps to be taken it is guided by principles essentially akin to those governing voice-leading, such as: in diminishing dissonance, take first the tone contributing most to it; do not conflate two tones into one, etc., and the converse for increasing dissonance. This strategy results in a tendency towards equal distribution of dissonance: extremely unequal contributions of individual notes, which (I think) make for the special combination of spiciness and clarity of many jazz chords, become less likely as the program proceeds in this mode. The other limitation inherent in the target mode lies in the fact that, e.g., starting with eight pitches within a space of an octave you obviously cannot get below a certain dissonance. If you don't like these limitations, use, or mix in, the parallel subset and the change-by-hand modes.</p>
<h3>Option (2): Parallel Subset Motion</h3>
<p>When you choose this option, you accept primes as intervals &ndash; two pitches, in working towards consonance, may get fused into one. (The only way of achieving the converse, i.e. of splitting a single tone in two, is to do it by hand.) Next, you actually enforce change: if the chord was already maximally consonant, and you try to make it more consonant still, you end up with a slightly less consonant chord instead. Lastly, Parallel Subset Motion, or PSM for short, brings home a freedom that every composer working with this program will have to face: it does find effective and elegant ways of changing a chord's dissonance, but it does not tell you much about the precise spatial relations of the resulting two consecutive chords. It may propose you a subset motion of three pitches out of five: obviously, you may do better by moving the other two, or by moving the whole chord in steps of different lengths for its two subchords. (Note that this goes not only for PSM: the results of the Target option leave you with exactly the same kind of freedom; as we all know, letting an (almost) whole chord move in order to relieve the dissonance of even a single motionless note is not unheard of in good music.)</p>
<h2>The Hindemith Root</h2>
<p>As an extra bonus, the user gets the chord's root thrown in, as it is computed by applying a slightly generalized version of the Hindemith Unterweisung im Tonsatz method. Because of the generalizations results will not always coincide with Hindemith's own. Thus, the program will not balk at chords with less than three pitches; it will also assign their roots to chords considered to be undecidable, such as the augmented triad, the chord built up out of two perfect fourths, and the diminished seventh chord, and it will do that by applying the same methods as everywhere else. Most importantly, we are in disagreement with the master because we think that his method, dependent as it is on the analysis of isolated chords, provides no solid basis at all for musical composition. (Composers are, as always, on their own.) However, its results are never simply foolish, which is why we have included it. An example. The user should suspend any notions about chords being incomplete &ndash; for the program, all chords are complete, and the chord c4-b4-d5 is assigned b4 as its root, because the third is the best interval contained in it. One might therefore say that, if the chord is incomplete after all, the program would have preferred to add f#4 and a4 to it rather than e4 and g4. Hindemith-style serious results are to be expected only for complete chords.</p>
<p id="bottomlink"><a href="<?php echo $backlink; ?>">Back to Dissonance Grading Program</a></p>
</body>
</html>
