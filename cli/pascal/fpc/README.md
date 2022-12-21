With a few minimal changes, mainly the removal of a pseudo-terminal window
(every modern OS has a real terminal available now) and the change of the
construction `copy(str, 1, 1)` to `str[1]` as a way to get the first character
of a string, the Think Pascal source files by Jos Kunst compile successfully
using the Free Pascal compiler (version 3.2.2). See [https://www.freepascal.org/](https://www.freepascal.org).

On Mac OS X 12.6.2 on an Intel Mac mini I used the following command:

`$ fpc dsgrading.p -odsg`

To create an executable called 'dsg'.
