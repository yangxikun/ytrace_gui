<template>
  <div class="container" style="margin-top: 4rem;">
    <h1 style="text-align: center;">欢迎使用 Ytrace<h5 style="display: inline-block;"><b-badge pill variant="secondary">alpha</b-badge></h5></h1>
    <p style="text-align: center;">这是个包含Ytrace的gui、chrome扩展、php扩展相关Wiki的页面</p>
    <div>
      <b-card title="GUI">
        <div class="card-text">
          <vue-markdown :source="GUI"></vue-markdown>
        </div>
      </b-card>
      <b-card title="Chrome 扩展">
        <div class="card-text">
          <vue-markdown :source="ChromeExtension"></vue-markdown>
        </div>
      </b-card>
      <b-card title="PHP 扩展">
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
        GUI: `__Home 页面__: 列出所有的跟踪文件, 通过点击列表中的某行记录打开Source 页面。
+ Clear Traces: 删除所有的跟踪文件。
+ Refresh: 刷新列表。
+ 列表字段说明：
  - Sapi: cli, fpm-fcgi 等
  - Method: fpm-fcgi中的http 请求方法
  - Uri: fpm-fcgi中的http 请求路径及参数
  - File: 跟踪文件名
  - Size: 跟踪文件大小
  - Time: 跟踪文件创建时间

__Source Page__: 显示跟踪内容详情。
+ 左边模块显示源码文件，右边模块显示跟踪到的变量值。
  - 只跟踪赋值和函数调用
  - ++、--、+=、/=、-=、*=、%=等，显示的值是这些运算符执行之前的值
  - PHP 7无法获取到扩展函数的参数名称（无法显示参数名称的会显示为$...）
+ 被执行过的代码行，行号会以绿色高亮出来，深绿色表示当前执行所在的行
  - 点击高亮的行号，可以将执行跳转至对应行
  - ctrl+点击行号，可以设置或取消断点（断点的行号会以红色高亮）
+ 执行操作:
  - step into: 同gdb
  - step back: 根据你的执行历史，往回执行
  - step over: 同gdb
  - step out: 同gdb
  - continue: 同gdb，执行到下一个断点
  - reset：重置执行过程
+ 快捷键:
  - ctrl+o: 打开最新10个跟踪文件的列表浮框
  - ctrl+p: 打开被跟踪到的源码文件的列表浮框，只能在Source 页面使用
  - ctrl+r: 打开当前显示的源码文件函数/方法的列表浮框，只能在Source 页面使用
  - ctrl+b: 打开断点列表浮框，只能在Source 页面使用

[ytrace_gui](https://github.com/miaolz123/vue-markdown)
[report issue](https://github.com/miaolz123/vue-markdown)
        `,
        ChromeExtension: `__Options__
+ ytrace.*_name: 配置cookie的名称，必须与ytrace.ini保持一致，通常不需要更改默认值。

__弹框__
+ 开启/关闭跟踪
+ 设置跟踪的配置，ytrace PHP扩展会从cookie里检测它们。

[ytrace_chrome_extension](https://github.com/miaolz123/vue-markdown)
[report issue](https://github.com/miaolz123/vue-markdown)
        `,
        PHPExtension: `__INI 配置__
+ auto_enable: 类型：boolean，默认值：0, PHP_INI_SYSTEM.
+ enable_trigger: 类型：boolean，默认值：0, PHP_INI_SYSTEM.
  - 当设置为1时，你可以通过名为YTRACE_TRIGGER的GET/POST参数，或cookie，或环境变量来触发跟踪
+ enable_trigger_name: 类型：string，默认值：YTRACE_TRIGGER, PHP_INI_SYSTEM.
+ enable_trigger_value: 类型：string，默认值："", PHP_INI_SYSTEM.
  - 用于限制触发跟踪的值
  - 当为空字符串时，只要检测到有YTRACE_TRIGGER名称的GET/POST参数，或cookie，或环境变量时，就会触发跟踪
  - 当为非空字符串时，名称为YTRACE_TRIGGER的GET/POST参数，或cookie，或环境变量对应的值必须与之匹配
+ output_dir: 类型：string，默认值： /tmp, PHP_INI_SYSTEM.
  - 确保有写权限
+ output_format: 类型：string，默认值： trace-%t, PHP_INI_SYSTEM.
  - 跟踪文件命名
  - %t: 秒级时间戳
  - %u: 毫秒级时间戳
  - %p: pid
  - %H: $_SERVER['HTTP_HOST']
  - %U: $_SERVER['UNIQUE_ID']
  - %R: $_SERVER['REQUEST_URI']
  - %%: %字面量
+ white_list: 类型：string，默认值： "", PHP_INI_ALL.
  - 由逗号分隔的多个字符串组成，大小写敏感
  - 当设置它的值时，例如“controller,model”，那么只有源码文件路径包含“controller”或“model”，才会被跟踪
  - white_list优先级高于black_list
+ white_list_name: 类型：string，默认值： "YTRACE_WHITE_LIST", PHP_INI_SYSTEM.
+ black_list: 类型：string，默认值： "", PHP_INI_ALL.
  - 由逗号分隔的多个字符串组成，大小写敏感
  - 当设置它的值时，例如“vendor,lib”，如果源码文件路径包含“vendor”或“lib”，将不会被跟踪
+ black_list_name: 类型：string，默认值： "YTRACE_BLACK_LIST", PHP_INI_SYSTEM.
+ var_display_max_children: 类型：integer，默认值： 128, PHP_INI_ALL.
  - Controls the amount of array children and object's properties are traced.
  - 控制跟踪到的变量值最大的数组元素个数和对象的属性个数
  - 最大值是 32
+ var_display_max_children_name 类型：string，默认值： "YTRACE_VAR_DISPLAY_MAX_CHILDREN", PHP_INI_SYSTEM.
+ var_display_max_data: 类型：integer，默认值： 512, PHP_INI_ALL.
  - Controls the maximum string length that is traced.
  - 控制跟踪到的字符串变量值的最大长度
  - 最大值 1024
+ var_display_max_data_name 类型：string，默认值： "YTRACE_VAR_DISPLAY_MAX_DATA", PHP_INI_SYSTEM.
+ var_display_max_depth: 类型：integer，默认值： 3, PHP_INI_ALL.
  - 控制跟踪到的变量值的最大嵌套层级
  - 最大 16
+ var_display_max_depth_name 类型：string，默认值： "YTRACE_VAR_DISPLAY_MAX_DEPTH", PHP_INI_SYSTEM.
==通常，你不需要修改*_name配置的默认值。*_name的配置是用于设置cookie、环境变量、GET/POST参数的名称。==

__PHP 函数__
+ ytrace_enable ([$traced_file_name])
  - 开启跟踪，并写入到$traced_file_name
+ ytrace_disable ()
  - 停止跟踪，返回跟踪文件名
        `
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .card
    margin-top 2rem
</style>
