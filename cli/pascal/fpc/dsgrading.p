{	This program is copyright (c) 1993 - 2005 Jos Kunst, the Jos Kunst heirs.	}
{	This program is free software. You can redistribute it and/or modify it 	}
{	under the terms of version 2 of the GNU General Public License, which you	}
{	should have received with it.												}
{	This program is distributed in the hope that it will be useful, but			}
{	without any warranty, expressed or implied.									}


{   Ported to Free Pascal from Think Pascal by Jan Pieter Kunst (2012)          }
{   using Free Pascal Compiler version 2.6.0 [2011/12/30] for i386              }
{   compiles correctly on my Intel Mac mini with                                }
{   $ fpc dsgrading.p -k-macosx_version_min -k10.5 -gw -FU./intermediate-files/ }

program dissonancegrading (input, output);

{$mode objfpc} {freepascal: needed to write to files}

	uses
		translations, chordscanner, chordworker;

	var
		startchord, zerothchord: chordrec;
		printfile, newchord, again: boolean;
		r: char;
		chordfile: TextFile;

	procedure introduction;
		var
			goon: boolean;
			ch: char;
	begin{introduction}
		goon := TRUE;
		repeat
			writeln;
			writeln;
			writeln;
			writeln;
			writeln;
			writeln;
			writeln('                      DISSONANCE GRADING PROGRAM');
			writeln('   by Jos Kunst, who is indebted to Jan Vriend and Jan Pieter Kunst');
			writeln;
			writeln;
			writeln;
			writeln('                            Version 2.0.1');
			writeln;
			writeln;
			writeln('                               *     *');
			writeln('                                  *');
			writeln;
			writeln;
			writeln;
			writeln;
			writeln;
			writeln('     We first offer you five screens of explanatory text. If you');
			writeln('          don''t want to see them any more, press W for just');
			writeln('                 going to work; else press any key.');
			writeln;
			write('--> ');
			readln(ch);
			if (ch = 'W') or (ch = 'w') then
				goon := FALSE
			else
				begin
					writeln;
					writeln;
					writeln('   RATIONALE. This program is meant to be a pre-compositional tool');
					writeln('   that scans and/or modifies chords along the dimension of');
					writeln('   dissonance - an uneasy concept if there ever was one; here it');
					writeln('   is perhaps best understood as referring to something like the');
					writeln('   opacity of chords: the degree to which it might be difficult to');
					writeln('   "hear through them" any new sound not already contained in them.');
					writeln('   Be that as it may; we let the test measure what it does, in fact,');
					writeln('   measure and its usefulness remain to be seen.');
					writeln;
					writeln('   ASSUMPTIONS. A chord''s dissonance is assumed to be a function of');
					writeln('   its component intervals. Intervals within the octave are ordered');
					writeln('   from dissonant to consonant (minor second - major seventh - ');
					writeln('   major second - minor seventh - augmented fourth/diminished');
					writeln('   fifth - all thirds and sixths - perfect fourth - perfect fifth -');
					writeln('   octave), and all intervals wider than those are proportionally');
					writeln('   downgraded, a minor second plus octave taking the value of a');
					writeln('   major seventh, a major seventh plus octave taking the value of a');
					writeln('   major second, and so on. Furthermore, the possible contribution');
					writeln('   of a tone that has also its own lower octave(s) in the chord is');
					writeln('   correspondingly lessened.');
					writeln;
					writeln('--------------------------------------------------------------------');
					writeln('   1st PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.');
					write('--> ');
					readln(ch)
				end;
			if (ch = 'W') or (ch = 'w') then
				goon := FALSE
			else
				begin
					writeln;
					writeln;
					writeln('   USES. In the first place, the program may be used as a chord-');
					writeln('   scanner. In that case, it displays four things: 1) each note''s');
					writeln('   contribution to the chord''s overall dissonance, 2) the chord''s');
					writeln('   dissonance total, which is a number such that if you subtract ');
					writeln('   from it the contribution of any of the chord''s component notes');
					writeln('   (octaves make for exceptions here, sorry), you get the total of');
					writeln('   another chord that is just like this one except for the absence');
					writeln('   of that component note; 3) the dsg ("dissonance grading"), which');
					writeln('   is twice that total divided by the number of component notes,');
					writeln('   and 4) the dsg class, which positions the chord in one of the');
					writeln('   five musically interesting subareas in the dissonance continuum');
					writeln('   we have seen fit to number, roman-style, I through V, in the');
					writeln('   following way:      dsg class:     I      II   III   IV    V');
					writeln('                           dsg:        0 -->3 -->8 -->21 -->55 -->');
					writeln;
					writeln('   In the second place, the program may be used in some preparatory');
					writeln('   stage of musical composition. It will try to increase or to');
					writeln('   diminish, at your choice, the dissonance of any chord you care');
					writeln('   to specify. It offers you the choice of two ways to do it:');
					writeln('     (1) by setting it a target dsg, and letting it work its way');
					writeln('         toward that target for as far as it can, or');
					writeln('     (2) by parallel subset (semitone) motion.');
					writeln;
					writeln('--------------------------------------------------------------------');
					writeln('   2nd PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.');
					write('--> ');
					readln(ch)
				end;
			if (ch = 'W') or (ch = 'w') then
				goon := FALSE
			else
				begin
					writeln('                  Option (1): Going for a Target');
					writeln;
					writeln('   When (after entering your starting chord) you choose the Target');
					writeln('   option, the program prompts you for the dissonance to be real-');
					writeln('   ized. It then sets about attaining this goal in the smallest');
					writeln('   possible number of (minor or major second) steps, keeping you');
					writeln('   informed of its progress through its screen and/or printer');
					writeln('   output. In determining the steps to be taken it is guided by');
					writeln('   principles essentially akin to those governing voice-leading,');
					writeln('   such as: in diminishing dissonance, take first the tone con-');
					writeln('   tributing most to it; do not conflate two tones into one,');
					writeln('   etc., and the converse for increasing dissonance. This strategy ');
					writeln('   results in a tendency towards equal distribution of dissonance:');
					writeln('   extremely unequal contributions of individual notes, which (I');
					writeln('   think) make for the special combination of spiciness and');
					writeln('   clarity of many jazz chords, become less likely as the program');
					writeln('   proceeds in this mode. The other limitation inherent in the');
					writeln('   target mode lies in the fact that, e.g., starting with eight');
					writeln('   pitches within a space of an octave you obviously cannot get');
					writeln('   below a certain dissonance. If you don''t like these limita-');
					writeln('   tions, use, or mix in, the parallel subset and the change-by-');
					writeln('   hand modes.');
					writeln;
					writeln('--------------------------------------------------------------------');
					writeln('   3d PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.');
					write('--> ');
					readln(ch)
				end;
			if (ch = 'W') or (ch = 'w') then
				goon := FALSE
			else
				begin
					writeln('                Option (2): Parallel Subset Motion');
					writeln;
					writeln('   When you choose this option, you accept primes as intervals -');
					writeln('   two pitches, in working towards consonance, may get fused into');
					writeln('   one. (The only way of achieving the converse, i.e. of splitting');
					writeln('   a single tone in two, is to do it by hand.) Next, you actually');
					writeln('   enforce change: if the chord was already maximally consonant,');
					writeln('   and you try to make it more consonant still, you end up with a');
					writeln('   slightly less consonant chord instead. Lastly, Parallel Subset');
					writeln('   Motion, or PSM for short, brings home a freedom that every com-');
					writeln('   poser working with this program will have to face: it does find');
					writeln('   effective and elegant ways of changing a chord''s dissonance,');
					writeln('   but it does not tell you much about the precise spatial re-');
					writeln('   lations of the resulting two consecutive chords. It may propose');
					writeln('   you a subset motion of three pitches out of five: obviously, you');
					writeln('   may do better by moving the other two, or by moving the whole');
					writeln('   chord in steps of different lengths for its two subchords. (Note');
					writeln('   that this goes not only for PSM: the results of the Target op-');
					writeln('   tion leave you with exactly the same kind of freedom; as we all');
					writeln('   know, letting an (almost) whole chord move in order to relieve');
					writeln('   the dissonance of even a single motionless note is not unheard');
					writeln('   of in good music.)');
					writeln;
					writeln('--------------------------------------------------------------------');
					writeln('   4th PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.');
					write('--> ');
					readln(ch)
				end;
			if (ch = 'W') or (ch = 'w') then
				goon := FALSE
			else
				begin
					writeln('   THE HINDEMITH ROOT. As an extra bonus, the user gets the chord''s');
					writeln('   root thrown in, as it is computed by applying a slightly gener-');
					writeln('   alized version of the Hindemith Unterweisung im Tonsatz method.');
					writeln('   Because of the generalizations results will not always coincide');
					writeln('   with Hindemith''s own. Thus, the program will not balk at chords');
					writeln('   with less than three pitches; it will also assign their roots');
					writeln('   to chords considered to be undecidable, such as the augmented');
					writeln('   triad, the chord built up out of two perfect fourths, and the');
					writeln('   diminished seventh chord, and it will do that by applying the');
					writeln('   same methods as everywhere else. Most importantly, we are in dis-');
					writeln('   agreement with the master because we think that his method, de-');
					writeln('   pendent as it is on the analysis of isolated chords, provides no');
					writeln('   solid basis at all for musical composition. (Composers are, as');
					writeln('   always, on their own.) However, its results are never simply');
					writeln('   foolish, which is why we have included it. An example. The user');
					writeln('   should suspend any notions about chords being incomplete - for');
					writeln('   the program, all chords are complete, and the chord c4-b4-d5 is');
					writeln('   assigned b4 as its root, because the third is the best interval');
					writeln('   contained in it. One might therefore say that, if the chord is');
					writeln('   incomplete after all, the program would have preferred to add');
					writeln('   f#4 and a4 to it rather than e4 and g4. Hindemith-style serious');
					writeln('   results are to be expected only for complete chords.');
					writeln;
					writeln('--------------------------------------------------------------------');
					writeln('   5th (LAST) PAGE - IF YOU WANT TO GO TO WORK, PRESS W, ELSE ANY KEY.');
					write('--> ');
					readln(ch)
				end;
			if (ch = 'W') or (ch = 'w') then
				goon := FALSE;
		until goon = FALSE;
	end;{introduction}

	procedure decidewhethertoprint;
		var
			ch: char;
			filename: string[128];
	begin
		writeln('Do you want results stored in printable diskfile? (Y/N)');
		write('--> ');
		readln(ch);
		if (ch <> 'N') and (ch <> 'n') then
			begin
				writeln('Name your file');
				write('--> ');
				readln(filename);
				if filename <> '' then
					begin
						printfile := TRUE;
						assignfile(chordfile, filename);
						rewrite(chordfile)
					end
				else
					printfile := FALSE
			end
		else
			printfile := FALSE
	end;

	procedure work (var cr: chordrec);
		var
			ch: char;
			ta, sofardsg: byte;
			go: boolean;
			validchoice: set of char;

		procedure makeintoset (var chstr: chordstring;
										var chs: pitchset);
			var
				oct: integer;
				pch: byte;
				ch: char;
		begin{makeintoset}
			chs := [];
			pch := 111;
			repeat
				while ((chstr[1] < 'a') or (chstr[1] > 'g')) and not (chstr = '') do
				begin
					delete(chstr, 1, 1);
				end;
				if not (chstr = '') then
					begin
						ch := chstr[1];
						case ch of
							'a': 
								begin
									pch := 21;
									delete(chstr, 1, 1)
								end;
							'b': 
								begin
									pch := 23;
									delete(chstr, 1, 1)
								end;
							'c': 
								begin
									pch := 12;
									delete(chstr, 1, 1)
								end;
							'd': 
								begin
									pch := 14;
									delete(chstr, 1, 1)
								end;
							'e': 
								begin
									pch := 16;
									delete(chstr, 1, 1)
								end;
							'f': 
								begin
									pch := 17;
									delete(chstr, 1, 1)
								end;
							'g': 
								begin
									pch := 19;
									delete(chstr, 1, 1)
								end;
						end;
					end;
				if chstr[1] = '#' then
					begin
						pch := pch + 1;
						delete(chstr, 1, 1)
					end;
				if chstr[1] = 'b' then
					begin
						pch := pch - 1;
						delete(chstr, 1, 1)
					end;
				if (chstr[1] >= '0') and (chstr[1] <= '8') then
					begin
						ch := chstr[1];
						oct := ord(ch) - 48;
						pch := pch + oct * 12;
						chs := chs + [pch];
						delete(chstr, 1, 1)
					end;
			until chstr = '';
		end;{makeintoset}

		function nameof (pitch: byte): pitchstring;
			var
				chrom, oct: string[2];
		begin{nameof}
			if pitch = 0 then
				nameof := 'xxx'
			else
				begin
					case (pitch - 12) mod 12 of
						0: 
							chrom := 'c';
						1: 
							chrom := 'c#';
						2: 
							chrom := 'd';
						3: 
							chrom := 'd#';
						4: 
							chrom := 'e';
						5: 
							chrom := 'f';
						6: 
							chrom := 'f#';
						7: 
							chrom := 'g';
						8: 
							chrom := 'g#';
						9: 
							chrom := 'a';
						10: 
							chrom := 'a#';
						11: 
							chrom := 'b';
					end;
					oct := chr(((pitch - 12) div 12) + 48);
					nameof := concat(chrom, oct)
				end
		end;{nameof}

		procedure writestructure (r: chordrec);   { beschrijving accoord naar scherm / file }
			var
				s: byte;
		begin{writestructure}
			s := 1;
			while r.stru[s, 1] <> 0 do
				begin
					write(nameof(r.stru[s, 1]), '<', r.stru[s, 2] : 1, '> ');
					if printfile then
						write(chordfile, nameof(r.stru[s, 1]), '<', r.stru[s, 2] : 1, '> ');
					s := s + 1
				end;
			writeln;
			if printfile then
				writeln(chordfile);
			write('TOTAL--><', r.total : 1, '>; DSG is ', r.diss : 1, ' (class: ', r.&class, ')');
			if printfile then
				write(chordfile, 'TOTAL--><', r.total : 1, '>; DSG is ', r.diss : 1, ' (class: ', r.&class, ')');
			writeln('; Hindemith Root: ', nameof(r.hifu));
			if printfile then
				writeln(chordfile, '; Hindemith Root: ', nameof(r.hifu))
		end;{writestructure}

		procedure choosestartingposition (var r: chordrec);
			var
				s: pitchset;

			procedure readin (var cs: pitchset);
				var
					str: chordstring;
			begin
				writeln('Your CHORD (format something like: d4-g#4-c#5, or f4db4bb3ab4,');
				write('   each note name to be ended by ');
				writeln('its octave position digit (a440 = a4))');
				write('--> ');
				readln(str);
				makeintoset(str, cs);
			end;

		begin{choosestartingposition}
			if newchord then
				begin
					readin(s);
					scan(s, r);
					startchord := r
				end
			else
				r := startchord;
		end;{choosestartingposition}


		procedure changebyhand (var chrc: chordrec);    {  handmatige correctie  }
			var
				c: char;
				chstr: chordstring;
				ch, cha, chs: pitchset;
				hand: boolean;
		begin{changebyhand}
			hand := TRUE;
			extractset(chrc, ch);
			repeat
				cha := [];
				chs := [];
				validchoice := ['Y', 'N'];
				repeat
					writeln('Do you wish to change this chord by hand? (Y/N)');
					write('--> ');
					readln(c);
				until (c in validchoice) or (chr(ord(c) - 32) in validchoice);
				if (c = 'Y') or (c = 'y') then
					begin
						writeln('Type pitches to be added (if any)');
						write('--> ');
						readln(chstr);
						makeintoset(chstr, cha);
						writeln('Type pitches to be deleted (if any)');
						write('--> ');
						readln(chstr);
						makeintoset(chstr, chs);
						ch := ch + cha;
						ch := ch - chs;
						scan(ch, chrc);
						writestructure(chrc);
					end
				else
					hand := FALSE;
			until not hand;
		end;{changebyhand}

	begin{work}
		go := TRUE;
		choosestartingposition(cr);
		writestructure(cr);
		repeat
			changebyhand(cr);
			validchoice := ['W', 'A'];
			repeat
				writeln('Shall I work on it, or abandon it? (W/A)');
				write('--> ');
				readln(ch);
			until (ch in validchoice) or (chr(ord(ch) - 32) in validchoice);
			case ch of
				'W', 'w': 
					begin
						validchoice := ['T', 'P'];
						repeat
							write('Aiming for a target dissonance, ');
							writeln('or using parallel subset motion? (T/P)');
							write('--> ');
							readln(ch);
						until (ch in validchoice) or (chr(ord(ch) - 32) in validchoice);
						case ch of
							'T', 't': 
								begin
									writeln('Enter the target DSG:');
									write('--> ');
									readln(ta);
									repeat 
										sofardsg := cr.diss;
										targetrecto(cr, ta);
									until cr.diss = sofardsg;
									writestructure(cr)
								end;
							'P', 'p': 
								begin
									validchoice := ['I', 'D'];
									repeat
										writeln('Increasing, or diminishing the dissonance? (I/D)');
										write('--> ');
										readln(ch);
									until (ch in validchoice) or (chr(ord(ch) - 32) in validchoice);
									case ch of
										'I', 'i': 
											begin
												psmdissrec(cr, 1);
												writestructure(cr);
											end;
										'D', 'd': 
											begin
												psmconsrec(cr, 1);
												writestructure(cr);
											end;
									end;
								end;
						end;
					end;
				'A', 'a': 
					go := FALSE;
			end;
		until not go;
	end;{work}

begin{dissonancegrading}
	introduction;
	initto(startchord, 0);
	newchord := TRUE;
	decidewhethertoprint;
	repeat
		work(zerothchord);
		writeln('Do you want to leave the program? (Y/N)');
		write('--> ');
		readln(r);
		if (r = 'N') or (r = 'n') then
			begin
				if not printfile then
					decidewhethertoprint;
				writeln('Go back to the CHORD you fed in the last time? (Y/N)');
				write('--> ');
				readln(r);
				if (r <> 'N') and (r <> 'n') then
					newchord := FALSE
				else
					newchord := TRUE;
				again := TRUE
			end
		else
			again := FALSE;
	until again = FALSE;
	
	if printfile then
		close(chordfile)
		
end.{dissonancegrading}