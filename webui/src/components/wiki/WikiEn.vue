<template>
  <div class="container" style="margin-top: 4rem;">
    <h1 style="text-align: center;">Welcome to Ytrace<h5 style="display: inline-block;"><b-badge pill variant="secondary">alpha</b-badge></h5></h1>
    <p style="text-align: center;">This is wiki page for Ytrace's gui, chrome extension, php extension.</p>
    <div>
      <b-card title="GUI">
        <div class="card-text">
          <vue-markdown :source="GUI"></vue-markdown>
        </div>
      </b-card>
      <b-card title="Chrome Extension">
        <div class="card-text">
          <vue-markdown :source="ChromeExtension"></vue-markdown>
        </div>
      </b-card>
      <b-card title="PHP Extension">
        <div class="card-text">
          <vue-markdown :source="PHPExtension"></vue-markdown>
        </div>
      </b-card>
    </div>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        GUI: `__Home Page__: List all traced files, click on one of the items in the list to open source page.
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

[ytrace_gui](https://github.com/yangxikun/ytrace_gui)
[report issue](https://github.com/yangxikun/ytrace_gui/issues)
        `,
        ChromeExtension: `__Options__
+ ytrace.*_name: cookie name will be used, must be same with ytrace.ini config, generally you don't need to change the default config.

__Popup__
+ enable/disable trace
+ set trace config, ytrace php extension will detect it from cookie

[ytrace_chrome_extension](https://github.com/yangxikun/ytrace_chrome_extension)
[report issue](https://github.com/yangxikun/ytrace_chrome_extension/issues)
        `,
        PHPExtension: `__INI config__
+ auto_enable: Type: boolean, Default value: 0, PHP_INI_SYSTEM.
+ enable_trigger: Type: boolean, Default value: 0, PHP_INI_SYSTEM.
  - When this setting is set to 1, you can trigger the generation of trace files by using the YTRACE_TRIGGER GET/POST parameter, or set a cookie with the name YTRACE_TRIGGER, or set an environment variable with the name YTRACE_TRIGGER.
+ enable_trigger_name: Type: string, Default value: YTRACE_TRIGGER, PHP_INI_SYSTEM.
+ enable_trigger_value: Type: string, Defalut value: "", PHP_INI_SYSTEM.
  - This setting can be used to restrict who can make use of the YTRACE_TRIGGER functionality as outlined in ytrace.enable_trigger.
  - When changed from its default value of an empty string, the value of the cookie, environment variable, GET or POST argument needs to match the shared secret set with this setting in order for the trace file to be generated.
+ output_dir: Type: string, Default value: /tmp, PHP_INI_SYSTEM.
  - make sure has write permission.
+ output_format: Type: string, Default value: trace-%t, PHP_INI_SYSTEM.
  - name format of traced file
  - %t: timestamp (in seconds)
  - %u: timestamp (in microseconds)
  - %p: pid
  - %H: $_SERVER['HTTP_HOST']
  - %U: $_SERVER['UNIQUE_ID']
  - %R: $_SERVER['REQUEST_URI']
  - %%: literal %
+ white_list: Type: string, Default value: "", PHP_INI_ALL.
  - comma separated string, case sensitive.
  - When set its value, sunch as "controller,model", only executed source file path that contain "controller" or "model" will be traced.
  - white_list takes precedence over black_list.
+ white_list_name: Type: string, Default value: "YTRACE_WHITE_LIST", PHP_INI_SYSTEM.
+ black_list: Type: string, Default value: "", PHP_INI_ALL.
  - comma separated string, case sensitive.
  - When set its value, sunch as "vendor,lib", executed source file path that contain "vendor" or "lib" will not be traced.
+ black_list_name: Type: string, Default value: "YTRACE_BLACK_LIST", PHP_INI_SYSTEM.
+ var_display_max_children: Type: integer, Default value: 128, PHP_INI_ALL.
  - Controls the amount of array children and object's properties are traced.
  - max value 32
+ var_display_max_children_name Type: string, Default value: "YTRACE_VAR_DISPLAY_MAX_CHILDREN", PHP_INI_SYSTEM.
+ var_display_max_data: Type: integer, Default value: 512, PHP_INI_ALL.
  - Controls the maximum string length that is traced.
  - max value 1024
+ var_display_max_data_name Type: string, Default value: "YTRACE_VAR_DISPLAY_MAX_DATA", PHP_INI_SYSTEM.
+ var_display_max_depth: Type: integer, Default value: 3, PHP_INI_ALL.
  - Controls how many nested levels of array elements and object properties are traced.
  - max value 16
+ var_display_max_depth_name Type: string, Default value: "YTRACE_VAR_DISPLAY_MAX_DEPTH", PHP_INI_SYSTEM.
==Generally, you don’t need to change the default config of *_name. The value of *_name is used for the name of cookie, environment variable, GET or POST argument==

__PHP function__
+ ytrace_enable ([$traced_file_name])
  - enable trace when trace is disable, write trace to $traced_file_name.
+ ytrace_disable ()
  - disable trace when trace is enable, return traced file.

[ytrace](https://github.com/yangxikun/ytrace)
[report issue](https://github.com/yangxikun/ytrace/issues)
        `
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .card
    margin-top 2rem
</style>
