# tcproc

Turn any STDIN/STDOUT program into a TCP/IP server.

## Installation

1. [Get Cone](https://getcone.org/#installation)
2. `cone get tcproc`

## Example

```Bash
git clone https://github.com/hell-sh/tcproc
cd tcproc
php tcproc.php 1337 php echo.php &
telnet localhost 1337
```
