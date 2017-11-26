This is a web ui for [ytrace](https://github.com/yangxikun/ytrace), see also [ytrace_chrome_extension](https://github.com/yangxikun/ytrace_chrome_extension).

![](img/index.png)

![](img/home.png)

![](img/source.png)

## Wiki
__Home Page__: List all traced files, click on one of the items in the list to open source page.
+ Clear Traces: delete all traced files.
+ Refresh: update list.
+ List field explanation:
  - Sapi: cli, fpm-fcgi etc
  - Method: http method in fpm-fcgi
  - Uri: http request uri in fpm-fcgi
  - File: file name
  - Size: file size
  - Time: created time

__Source Page__: show detail of traced files.
+ source code show in left side, traced value show in right side.
  - only assignment and function call will be traced
  - ++, --, +=, /=, -=, *=, %= etc, shows the values before these operators execute
  - PHP 7 cannot show internal function parameters name (parameter name cannot be found will be showed as $...)
+ executed line will be highlight in green, dark green means current execute
  - click highlighted line number, can jump current execute to it
  - ctrl+click line number, can set/clear a breakpoint (breakpoint will be highlight in red line number)
+ execute operation:
  - step into: same with gdb
  - step back: execute back through your step history
  - step over: same with gdb
  - step out: same with gdb
  - continue: same with gdb, execute to next breakpoint
  - reset: reset execute to first entry
+ shortcut:
  - ctrl+o: open 10 latest traced file popup list
  - ctrl+p: open traced source file popup list, only available in source page
  - ctrl+r: open current source file function/method popup list, only available in source page
  - ctrl+b: open breakpoints popup list, only available in source page
