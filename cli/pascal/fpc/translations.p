{	This program is copyright (c) 1993 - 2005 Jos Kunst, the Jos Kunst heirs.	}
{	This program is free software. You can redistribute it and/or modify it 	}
{	under the terms of version 2 of the GNU General Public License, which you	}
{	should have received with it.												}
{	This program is distributed in the hope that it will be useful, but			}
{	without any warranty, expressed or implied.									}

unit translations;
{ --- converted to MIDI pitch numbering (pf: 21 <--> 108) on sep 9 1993 -- }
interface

	type
		integerstring = string[6];
		pitchstring = string[3];
		chordstring = string[127];
		pitchset = set of byte;

	const
		middlec = 60;
		bottomnote = middlec - 48;   {  van contrabas-c (24, c1) - 12 tot piccolo-c (108, c8) + 12: dus zeer ruim de   }
		topnote = middlec + 60;         {      benodigde breedte   (c' = c4 = sleutelgat-c = 60!) }

	function charinted (stri: integerstring): integer;
{translates strings that are integer names into integers}
	function intchared (j: integer): integerstring;
{translates integers into their names as strings}
	function upcased (c: char): char;
{translates character, if lower case, into its upper case form}
	function nameof (pitch: byte): pitchstring;
{translates byte denoting pitch into the string that is its name}
	procedure makeintoset (chstr: chordstring; var chs: pitchset);
{translates string denoting chord into a set of bytes}
	procedure taketochordclass (cstr: chordstring; var ms: pitchset);
{translates string denoting chord into set of octave zero namesakes}
	procedure makeintobyte (pstr: pitchstring; var pb: byte);
{translates string denoting pitch into byte}
	procedure taketobyteclass (pstr: pitchstring; var pbc: byte);
{translates string denoting pitch into its octave zero namesake byte}
	function powerof (number, power: byte): integer;
{returns the value of 'number' to the power 'power'}
{
	function rootof (number: integer; power: byte): real;
}
{returns the value of the powerth root of the number 'number' - not implemented}
	function base10to2 (number: integer): string;
{returns the binary form of 'number'}
	function base2to10 (number: string): integer;
{returns the base ten form of the binary number 'number'}
	function posvalue (posfromend: byte; number: integer; base: byte): byte;
{returns the value of the 'posfromend'th position from the end }
{in the base 'base' form of the number 'number'}
	function hextobyte (hexposfromend2, hexposfromend1: byte): byte;
{returns the byte value of a two position hex number}

implementation

	function charinted (stri: integerstring): integer;
		var
			p, q, n: byte;
			c: char;
			negat: boolean;
			st: integerstring;
		procedure stopit (var str: integerstring);
		begin
			writeln('not within integer range!!');
			str := ' '
		end;
	begin
		st := stri;
{--------strip preceding and trailing spaces------------}
		while copy(st, 1, 1) = ' ' do
			delete(st, 1, 1);
		while copy(st, length(st), 1) = ' ' do
			delete(st, length(st), 1);
{------default values---------}
		q := 0;
		p := 0;
{----------look for negative integer sign----------}
		negat := FALSE;
		c := st[1];
		if c = '-' then
			begin
				negat := TRUE;
				delete(st, 1, 1)
			end;
{--------thorough clean-up, stripping away separators and any junk----------}
		for n := length(st) downto 1 do
			if ((copy(st, n, 1) < '0') or (copy(st, n, 1) > '9')) then
				delete(st, n, 1);
{--------give alert and stop in case number exceeds integer range------}
		if length(st) > 5 then
			stopit(st)
		else if length(st) = 5 then
			begin
				if ((copy(st, 1, 1) > '3') and (copy(st, 1, 1) < '0')) then
					stopit(st)
				else if copy(st, 1, 1) = '3' then
					if copy(st, 2, 1) > '2' then
						stopit(st)
					else if copy(st, 2, 1) = '2' then
						if copy(st, 3, 1) > '7' then
							stopit(st)
						else if copy(st, 3, 1) = '7' then
							if copy(st, 4, 1) > '6' then
								stopit(st)
							else if copy(st, 4, 1) = '6' then
								if copy(st, 5, 1) > '7' then
									stopit(st)
								else if negat then
									if copy(st, 5, 1) = '7' then
										stopit(st)
			end;
{----calculate abs size of number...------}
		while not (st = '') do
			begin
				c := st[1];
				p := ord(c) - 48;
				delete(st, 1, 1);
				for n := 1 to length(st) do
					p := p * 10;
				q := q + p
			end;
{------... and reconstruct negative numbers------}
		if negat then
			charinted := q - 2 * q
		else
			charinted := q
	end;

	function intchared (j: integer): integerstring;
		var
			i: integer;
			ch0, ch1, ch2, ch3, ch4, ch5: char;
			str: integerstring;
	begin
		if j < 0 then
			begin
				ch0 := '-';
				i := abs(j)
			end
		else
			begin
				ch0 := '+';
				i := j
			end;
		if (i >= 0) and (i <= 9) then
			str := concat(ch0, chr(i + 48))
		else if (i >= 10) and (i <= 99) then
			begin
				ch1 := chr((i div 10) + 48);
				ch2 := chr((i mod 10) + 48);
				str := concat(ch0, ch1, ch2)
			end
		else if (i >= 100) and (i <= 999) then
			begin
				ch1 := chr((i div 100) + 48);
				ch2 := chr(((i div 10) mod 10) + 48);
				ch3 := chr((i mod 10) + 48);
				str := concat(ch0, ch1, ch2, ch3)
			end
		else if (i >= 1000) and (i <= 9999) then
			begin
				ch1 := chr((i div 1000) + 48);
				ch2 := chr(((i div 100) mod 10) + 48);
				ch3 := chr(((i div 10) mod 10) + 48);
				ch4 := chr((i mod 10) + 48);
				str := concat(ch0, ch1, ch2, ch3, ch4)
			end
		else if i > 9999 then
			begin
				ch1 := chr((i div 10000) + 48);
				ch2 := chr(((i div 1000) mod 10) + 48);
				ch3 := chr(((i div 100) mod 10) + 48);
				ch4 := chr(((i div 10) mod 10) + 48);
				ch5 := chr((i mod 10) + 48);
				str := concat(ch0, ch1, ch2, ch3, ch4, ch5)
			end;
		if copy(str, 1, 1) = '+' then
			delete(str, 1, 1);
		intchared := str;
	end;

	function upcased (c: char): char;
	begin
		if (c >= 'a') and (c <= 'z') then
			upcased := chr(ord(c) - 32)
		else
			upcased := c
	end;

	function nameof (pitch: byte): pitchstring;
		var
			chrom, oct: string[2];
	begin{nameof}
		if (pitch >= bottomnote) and (pitch <= topnote) then
			begin
				case (pitch - bottomnote) mod 12 of
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
						chrom := 'b'
				end;
				oct := intchared((pitch - bottomnote) div 12);
				if oct <> '0' then
					nameof := concat(chrom, oct)
				else
					nameof := chrom
			end
		else if pitch < bottomnote then
			nameof := 'Xlo'
		else if pitch > topnote then
			nameof := 'Xhi'
	end;{nameof}

	procedure makeintoset (chstr: chordstring; var chs: pitchset);
		var
			oct: integer;
			pch: byte;
			ch: char;
			cstr: chordstring;
	begin{makeintoset}
		chs := [];
		pch := topnote + 1;
		cstr := chstr;
		repeat
			while ((copy(cstr, 1, 1) < 'a') or (copy(cstr, 1, 1) > 'g')) and not (cstr = '') do
				delete(cstr, 1, 1);
			if not (cstr = '') then
				begin
					ch := cstr[1];
					case ch of
						'a': 
							begin
								pch := bottomnote + 9;
								delete(cstr, 1, 1)
							end;
						'b': 
							begin
								pch := bottomnote + 11;
								delete(cstr, 1, 1)
							end;
						'c': 
							begin
								pch := bottomnote;
								delete(cstr, 1, 1)
							end;
						'd': 
							begin
								pch := bottomnote + 2;
								delete(cstr, 1, 1)
							end;
						'e': 
							begin
								pch := bottomnote + 4;
								delete(cstr, 1, 1)
							end;
						'f': 
							begin
								pch := bottomnote + 5;
								delete(cstr, 1, 1)
							end;
						'g': 
							begin
								pch := bottomnote + 7;
								delete(cstr, 1, 1)
							end;
					end;
				end;
			if copy(cstr, 1, 1) = '#' then
				begin
					pch := pch + 1;
					delete(cstr, 1, 1)
				end
			else if copy(cstr, 1, 1) = 'b' then
				begin
					pch := pch - 1;
					delete(cstr, 1, 1)
				end;
			if (copy(cstr, 1, 1) >= '0') and (copy(cstr, 1, 1) <= '8') then
				begin
					ch := cstr[1];
					oct := charinted(ch);
					pch := pch + oct * 12;
					delete(cstr, 1, 1)
				end;
			chs := chs + [pch]
		until cstr = '';
	end;{makeintoset}

	procedure taketochordclass (cstr: chordstring; var ms: pitchset);
		var
			p, q: byte;
			cs: pitchset;
	begin{taketochordclass}
		ms := [];
		makeintoset(cstr, cs);
		for p := 0 to bottomnote - 1 do
			if p in cs then
				begin
					cs := cs - [p];
					cs := cs + [p + 12]
				end;
		for p := bottomnote to topnote do
			if p in cs then
				begin
					q := p;
					while q >= bottomnote + 12 do
						q := q - 12;
					ms := ms + [q]
				end;
	end;{taketochordclass}

	procedure makeintobyte (pstr: pitchstring; var pb: byte);
		var
			bs: pitchset;
			p: byte;
			pbfound: boolean;
	begin{makeintobyte}
		makeintoset(pstr, bs);
		pb := 0;
		pbfound := FALSE;
		p := bottomnote - 1;
		repeat
			if p in bs then
				begin
					pb := p;
					pbfound := TRUE
				end;
			p := p + 1
		until pbfound or (p > topnote)
	end;{makeintobyte}

	procedure taketobyteclass (pstr: pitchstring; var pbc: byte);
	begin{taketobyteclass}
		makeintobyte(pstr, pbc);
		while pbc < bottomnote do
			pbc := pbc + 12; { cb0, resulting in 11, is a (remote but real) possibility! - ...  replaced by b0 }
		while pbc >= bottomnote + 12 do
			pbc := pbc - 12
	end;{taketobyteclass}

	function powerof (number, power: byte): integer;
		var
			pow: integer;
			c: byte;
	begin
		if power = 0 then
			pow := 1
		else
			begin
				pow := number;
				for c := 2 to power do
					pow := pow * number
			end;
		powerof := pow
	end;
	
{not implemented}
{
	function rootof (number: integer; power: byte): real;
		var
			rt: real;
	begin
		rootof := rt
	end;
}
	function base10to2 (number: integer): string;
		var
			p: byte;
			num, remainder: integer;
			base2: string;
	begin
		if number = 0 then
			base2 := '0'
		else
			begin
				if number < 0 then
					base2 := '-'
				else
					base2 := '';
				p := 15;
				remainder := number;
				repeat
					p := p - 1;
					num := powerof(2, p);
					if num > remainder then
						begin
							if base2 <> '' then
								base2 := concat(base2, '0')
						end
					else
						begin
							base2 := concat(base2, '1');
							remainder := remainder - num
						end
				until p = 0
			end;
		base10to2 := base2
	end;

	function base2to10 (number: string): integer;
		var
			base10: integer;
			str: string;
	begin
		base10 := 0;
		str := number;
		while not (str = '') do
			if copy(str, 1, 1) <> '1' then
				delete(str, 1, 1)
			else
				begin
					base10 := base10 + powerof(2, length(str) - 1);
					delete(str, 1, 1)
				end;
		base2to10 := base10
	end;

	function posvalue (posfromend: byte; number: integer; base: byte): byte;
	begin
		posvalue := (number mod powerof(base, posfromend)) div powerof(base, (posfromend - 1))
	end;

	function hextobyte (hexposfromend2, hexposfromend1: byte): byte;
	begin
		hextobyte := hexposfromend2 * 16 + hexposfromend1
	end;

end.