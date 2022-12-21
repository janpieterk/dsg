{	This program is copyright (c) 1993 - 2005 Jos Kunst, the Jos Kunst heirs.	}
{	This program is free software. You can redistribute it and/or modify it 	}
{	under the terms of version 2 of the GNU General Public License, which you	}
{	should have received with it.												}
{	This program is distributed in the hope that it will be useful, but			}
{	without any warranty, expressed or implied.									}

unit chordscanner;

interface
	uses
		translations;
	type
		chordarr = array[1..102, 1..2] of integer;
		chordrec = record
				stru: chordarr;
				total: integer;
				diss: byte;
				class: string[4];
				hifu: byte;
			end;

	procedure initto (var r: chordrec;
									b: integer);
{initializes a chord's analysis record using a given integer, such as zero}
	procedure scan (cset: pitchset;
									var crec: chordrec);
{analyzes a given chord (typically a set of bytes between 12 and 108) and stores the analysis in a record}

implementation

	procedure initto (var r: chordrec;
									b: integer);
		var
			p, q: byte;
	begin
		for p := 1 to 102 do
			for q := 1 to 2 do
				r.stru[p, q] := b;
		r.total := b;
		r.diss := 0;
		r.class := 'x';
		r.hifu := 0
	end;


	procedure scan (cset: pitchset;
									var crec: chordrec); { doormeting accoord }

		procedure dodsg (cs: pitchset;
										var cr: chordrec);  { bepaling dissonantiegraad }
			var
				contribp, sumcontrib: integer;
				p, q, n, s: byte;
				oct1, oct2, oct3, oct4, oct5, oct6, oct7, oct8: pitchset;

			procedure takestockofoctaves (ch: pitchset);{welke tonen hebben benedenoctaven,}
				var
					p, q: byte;                                                  { en hoeveel? }
			begin{takestockofoctaves}
				oct1 := [];
				oct2 := [];
				oct3 := [];
				oct4 := [];
				oct5 := [];
				oct6 := [];
				oct7 := [];
				oct8 := [];
				for p := bottomnote to topnote do
					if p in ch then
						for q := bottomnote to p - 11 do
							if (q in oct7) and ((p - q) mod 12 = 0) then
								oct8 := oct8 + [p]
							else if (q in oct6) and ((p - q) mod 12 = 0) then
								oct7 := oct7 + [p]
							else if (q in oct5) and ((p - q) mod 12 = 0) then
								oct6 := oct6 + [p]
							else if (q in oct4) and ((p - q) mod 12 = 0) then
								oct5 := oct5 + [p]
							else if (q in oct3) and ((p - q) mod 12 = 0) then
								oct4 := oct4 + [p]
							else if (q in oct2) and ((p - q) mod 12 = 0) then
								oct3 := oct3 + [p]
							else if (q in oct1) and ((p - q) mod 12 = 0) then
								oct2 := oct2 + [p]
							else if (q in ch) and ((p - q) mod 12 = 0) then
								oct1 := oct1 + [p]
			end;{takestockofoctaves}

			function intdsg (int: byte): integer;   { verbinden van precieze dsgwaarden }
				var
					intclass: byte;                   { aan intervallen, nog afgezien van }
			begin{intdsg}
                         { eventuele octaafposities der tonen }
				case int mod 12 of
					0: 
						intclass := 1;                         {prime}
					1: 
						intclass := 9;                         {kl sec}
					2: 
						intclass := 7;                         {gr sec}
					3: 
						intclass := 4;                         {kl t}
					4: 
						intclass := 4;                         {gr t}
					5: 
						intclass := 3;                         {kwart}
					6: 
						intclass := 5;                         {ov kw}
					7: 
						intclass := 2;                         {kwint}
					8: 
						intclass := 4;                         {kl sext}
					9: 
						intclass := 4;                         {gr sext}
					10: 
						intclass := 6;                         {kl sept}
					11: 
						intclass := 8;                         {gr sept}
				end;
				if int div 12 = 1 then
					if intclass > 1 then
						intclass := intclass - 1
					else
						intclass := 1;
				if int div 12 = 2 then
					if intclass > 2 then
						intclass := intclass - 2
					else
						intclass := 1;
				if int div 12 = 3 then
					if intclass > 3 then
						intclass := intclass - 3
					else
						intclass := 1;
				if int div 12 = 4 then
					if intclass > 4 then
						intclass := intclass - 4
					else
						intclass := 1;
				if int div 12 = 5 then
					if intclass > 5 then
						intclass := intclass - 5
					else
						intclass := 1;
				if int div 12 = 6 then
					if intclass > 6 then
						intclass := intclass - 6
					else
						intclass := 1;
				if int div 12 = 7 then
					if intclass > 7 then
						intclass := intclass - 7
					else
						intclass := 1;
				if int div 12 > 7 then
					if intclass > 8 then
						intclass := intclass - 8
					else
						intclass := 1;
				case intclass of
					1: 
						intdsg := 0;
					2: 
						intdsg := 1;
					3: 
						intdsg := 2;
					4: 
						intdsg := 3;
					5: 
						intdsg := 5;
					6: 
						intdsg := 8;
					7: 
						intdsg := 13;
					8: 
						intdsg := 21;
					9: 
						intdsg := 34;
				end
			end;{intdsg}

		begin{dodsg}
			initto(cr, 0);
			takestockofoctaves(cs);
			n := 0;
			sumcontrib := 0;
			for p := bottomnote to topnote do
				if p in cs then
					begin
						n := n + 1;
						contribp := 0;
						for q := bottomnote to topnote do
							if q in cs then
								if p in oct8 then
									contribp := contribp + intdsg(abs(p - q) + 8 * 12)
								else if p in oct7 then
									contribp := contribp + intdsg(abs(p - q) + 7 * 12)
								else if p in oct6 then
									contribp := contribp + intdsg(abs(p - q) + 6 * 12)
								else if p in oct5 then
									contribp := contribp + intdsg(abs(p - q) + 5 * 12)
								else if p in oct4 then
									contribp := contribp + intdsg(abs(p - q) + 4 * 12)
								else if p in oct3 then
									contribp := contribp + intdsg(abs(p - q) + 3 * 12)
								else if p in oct2 then
									contribp := contribp + intdsg(abs(p - q) + 2 * 12)
								else if p in oct1 then
									contribp := contribp + intdsg(abs(p - q) + 12)
								else
									contribp := contribp + intdsg(abs(p - q));
						s := 0;
						repeat
							s := s + 1;
						until cr.stru[s, 1] = 0;
						cr.stru[s, 1] := p;
						cr.stru[s, 2] := contribp;
						sumcontrib := sumcontrib + contribp
					end;
			cr.total := sumcontrib div 2;
			if n > 0 then
				cr.diss := sumcontrib div n
			else
				cr.diss := 0;
			if n > 0 then
				case sumcontrib div n of              { bepaling van de dsg-klasse }
					0..3: 
						cr.class := 'I';
					4..8: 
						cr.class := 'II';
					9..21: 
						cr.class := 'III';
					22..55: 
						cr.class := 'IV';
					otherwise
						cr.class := 'V';
				end
			else
				cr.class := 'none'
		end;{dodsg}

		procedure doghm (var cr: chordrec);        { zoek Hindemith grondtoon }
			var
				p, q, r, sofarhf, hfweight: byte;
		begin{doghm}
			p := 1;
			sofarhf := 0;
			hfweight := 0;
			repeat
				q := p + 1;
				while not (cr.stru[q, 1] = 0) do
					begin
						if ((cr.stru[q, 1] - cr.stru[p, 1]) mod 12 = 7) and (hfweight < 10) then
							begin
								hfweight := 10;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[q, 1] - cr.stru[p, 1]) mod 12 = 4) and (hfweight < 8) then
							begin
								hfweight := 8;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[q, 1] - cr.stru[p, 1]) mod 12 = 3) and (hfweight < 6) then
							begin
								hfweight := 6;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[q, 1] - cr.stru[p, 1]) mod 12 = 10) and (hfweight < 4) then
							begin
								hfweight := 4;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[q, 1] - cr.stru[p, 1]) mod 12 = 11) and (hfweight < 2) then
							begin
								hfweight := 2;
								sofarhf := cr.stru[p, 1]
							end;
						q := q + 1;
					end;
				r := p - 1;
				while not (r = 0) do
					begin
						if ((cr.stru[p, 1] - cr.stru[r, 1]) mod 12 = 5) and (hfweight < 9) then
							begin
								hfweight := 9;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[p, 1] - cr.stru[r, 1]) mod 12 = 8) and (hfweight < 7) then
							begin
								hfweight := 7;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[p, 1] - cr.stru[r, 1]) mod 12 = 9) and (hfweight < 5) then
							begin
								hfweight := 5;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[p, 1] - cr.stru[r, 1]) mod 12 = 2) and (hfweight < 3) then
							begin
								hfweight := 3;
								sofarhf := cr.stru[p, 1]
							end;
						if ((cr.stru[p, 1] - cr.stru[r, 1]) mod 12 = 1) and (hfweight < 1) then
							begin
								hfweight := 1;
								sofarhf := cr.stru[p, 1]
							end;
						r := r - 1;
					end;
				p := p + 1;
			until cr.stru[p, 1] = 0;
			cr.hifu := sofarhf
		end;{doghm}

	begin{scan}
		dodsg(cset, crec);
		doghm(crec)
	end;{scan}
end.
