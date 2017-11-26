<template>
  <div>
    <div v-show="!loading">
      <div style="padding: 0.5rem 1rem;">
        <h6 style="display: inline-block; color: rgba(0, 0, 0, 0.5);" v-show="file">{{file}}:{{line}}</h6>
        <b-button v-on:click="reset(0)" size="sm" variant="outline-secondary">reset</b-button>
        <b-button :disabled="step == items.length-1" v-on:click="_continue()" size="sm" variant="outline-secondary">continue</b-button>
        <b-button :disabled="step == items.length-1" v-on:click="stepOut()" size="sm" variant="outline-secondary">step out</b-button>
        <b-button :disabled="step == items.length-1" v-on:click="stepOver()" size="sm" variant="outline-secondary">step over</b-button>
        <b-button v-on:click="stepBack()" size="sm" variant="outline-secondary">step back</b-button>
        <b-button :disabled="step == items.length-1" v-on:click="stepInto(step, step+1)" size="sm" variant="outline-secondary">step into</b-button>
      </div>
      <div class="container-fluid">
        <b-row>
          <b-col cols="8">
            <div v-bind:style="{ height: codeHeight + 'px' }" style="width: 100%; font-size: 1rem;" ref="code">
            </div>
          </b-col>
          <b-col>
            <div v-bind:style="{ height: codeHeight + 'px' }" style="width: 100%; font-size: 1rem;" ref="var"></div>
          </b-col>
        </b-row>
      </div>
    </div>
    <div v-show="loading" style="margin-top: 5%;">
      <vue-loading type="bars" color="#d9544e" :size="{ width: '8rem', height: '6rem' }"></vue-loading>
    </div>
  </div>
</template>

<script>
  import ace from 'brace'
  import 'brace/mode/php'
  import 'brace/theme/monokai'
  import vueLoading from 'vue-loading-template'
  const sprintf = require('sprintf-js').sprintf
  export default {
    name: 'Trace-Source',
    components: {
      vueLoading
    },
    data () {
      return {
        loading: true,
        initOk: false,
        id: '',
        sourceEditor: null,
        varEditor: null,
        file: '',
        line: 0,
        type: '',
        level: 0,
        items: [],
        fileCoverage: {},
        step: -1,
        stepHistory: [],
        codeHeight: 0
      }
    },
    watch: {
      '$route.params.id': function (val) {
        if (val === undefined) return
        if (this.id !== '' && this.id !== val) {
          this.id = val
          this.fetchTrace()
        }
      }
    },
    mounted () {
      const body = document.body
      const html = document.documentElement
      const docHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight)
      this.codeHeight = docHeight - document.getElementById('head').clientHeight - 44
      this.sourceEditor = this.initEditor(this.$refs.code, true)
      delete this.sourceEditor.keyBinding.$defaultHandler.commandKeyBinding['ctrl-p']
      this.varEditor = this.initEditor(this.$refs.var, false)
      this.varEditor.keyBinding.$defaultHandler.commandKeyBinding = {}
      this.fetchTrace()
      this.$bus.$on('gotoLine', (line) => {
        this.sourceEditor.gotoLine(line)
      })
      this.$bus.$on('openFile', (file, line) => {
        this.justLoadFile(file, line)
      })
      this.$bus.$on('removeBp', (file, line) => {
        if (this.$bus.curSourceFile === file) {
          const lines = this.$refs.code.querySelector('div.ace_gutter-layer').childNodes
          const el = lines[line - 1]
          el.style.color = null
          el.style.fontWeight = null
        }
      })
    },
    activated () {
      if (this.$route.query.step) {
        this.stepInto(this.step, Number(this.$route.query.step))
      }
    },
    methods: {
      initEditor: function (element, lineno) {
        const editor = ace.edit(element)
        editor.getSession().setMode('ace/mode/php')
        editor.setTheme('ace/theme/monokai')
        editor.setReadOnly(true)
        editor.$blockScrolling = Infinity
        editor.renderer.$cursorLayer.element.style.opacity = 0
        editor.renderer.setShowGutter(lineno)
        editor.on('gutterclick', (event) => {
          const row = event.editor.getCursorPosition().row + 1
          if (event.domEvent.ctrlKey) {
            // set/clear breakpoint
            let breakpoints = this.$localStorage.get(this.file)
            if (breakpoints) {
              breakpoints = JSON.parse(breakpoints)
            } else {
              breakpoints = {}
            }
            const key = String(row)
            const el = event.domEvent.target
            if (breakpoints[key]) {
              delete breakpoints[key]
              el.style.color = null
              el.style.fontWeight = null
            } else {
              breakpoints[String(row)] = true
              el.style.color = '#ff0000'
              el.style.fontWeight = 'bold'
            }
            this.$localStorage.set(this.file, JSON.stringify(breakpoints))
          } else {
            // jump
            const fileCoverage = this.fileCoverage[this.$bus.curSourceFile]
            let jumpStep = -1
            for (let line in fileCoverage) {
              if (Number(line) === row) {
                jumpStep = fileCoverage[line]
              }
            }
            if (jumpStep >= 0) {
              this.stepInto(this.step, jumpStep)
            }
          }
        }, false)
        return editor
      },
      fetchTrace: function () {
        this.loading = true
        this.id = this.$route.params.id
        this.$http.get('/trace/' + encodeURI(this.id))
          .then(resp => {
            const _items = resp.data.trimRight().split('\n')
            let items = []
            let fileCoverage = {}
            delete _items[0]
            let breakpoints = {}
            let firstBreakpoint = -1
            for (let index in _items) {
              const item = _items[index].split('\t')
              item[3] = Number(item[3])
              if (item[2] === 'F') {
                item[5] = Number(item[5])
              }
              items.push(item)
              if (!fileCoverage[item[0]]) {
                fileCoverage[item[0]] = {}
              }
              if (!fileCoverage[item[0]][item[1]]) {
                fileCoverage[item[0]][item[1]] = Number(index) - 1
              }
              if (firstBreakpoint === -1) {
                if (!breakpoints[item[0]]) {
                  // load breakpoint from localstorage
                  let breakpoint = this.$localStorage.get(item[0])
                  if (breakpoint) {
                    breakpoint = JSON.parse(breakpoint)
                    breakpoints[item[0]] = breakpoint
                  }
                }
                if (breakpoints[item[0]] && breakpoints[item[0]][item[1]]) {
                  // find first breakpoint
                  firstBreakpoint = index - 1
                }
              }
            }
            this.items = items
            this.fileCoverage = fileCoverage
            this.loading = false
            if (firstBreakpoint === -1) {
              firstBreakpoint = 0
            }
            this.reset(firstBreakpoint)
          })
      },
      // https://github.com/kvz/locutus/blob/master/src/php/strings/stripslashes.js
      stripslashes: function (str) {
        return (str + '').replace(/\\./g, function (match) {
          return (new Function('return "' + match + '"'))() || match
        })
      },
      stepInto: function (curStep, nextStep) {
        const item = this.items[nextStep]
        if (!item) return
        if (curStep >= 0) {
          this.stepHistory.push(curStep)
        }
        this.step = nextStep
        this.line = Number(item[1])
        this.type = item[2]
        this.level = item[3]
        if (item[0] !== this.file) {
          // next file
          this.file = item[0]
          this.loadFile()
        } else if (this.file !== this.$bus.curSourceFile) {
          this.loadFile()
        } else {
          this.sourceEditor.gotoLine(this.line)
        }
        switch (this.type) {
          case 'A':
            this.varEditor.setValue('<?php\n/*\n * Assignment\n */\n' + this.stripslashes(item[4]) + ' = ' + this.stripslashes(item[5]))
            break
          case 'F':
            let params = []
            let paramsName = []
            const argCount = item[5]
            for (let i = 6; i < 6 + 2 * argCount; i += 2) {
              paramsName.push(this.stripslashes(item[i]))
              params.push(this.stripslashes(item[i]) + ' = ' + this.stripslashes(item[i + 1]))
            }
            this.varEditor.setValue(sprintf('<?php\n/*\n * Function Call\n */\n%s(\n%s\n)', item[4], params.join('\n\n')))
            break
          case 'I':
            this.varEditor.setValue('<?php\n/*\n * include\n */\n' + this.stripslashes(item[5]))
            break
          case 'E':
            this.varEditor.setValue('<?php\n/*\n * {eval}\n */\n' + this.stripslashes(item[5]))
            break
          default:
            this.varEditor.setValue('')
        }
      },
      stepOver: function () {
        if (this.type !== 'F' && this.type !== 'I') {
          this.stepInto(this.step, this.step + 1)
          return
        }
        let walk = this.step + 1
        let step = this.items[walk]
        while (step) {
          if (step[3] < this.level) break
          if ((step[2] === 'F' || step[2] === 'I') && step[3] === this.level) break
          walk++
          step = this.items[walk]
        }
        if (step) {
          this.stepInto(this.step, walk)
        }
      },
      stepOut: function () {
        let walk = this.step + 1
        let step = this.items[walk]
        let level = this.level
        if (this.type === 'F' || this.type === 'I' || this.type === 'E') {
          level -= 1
        }
        while (step) {
          if (step[3] < level) break
          if ((step[2] === 'F' || step[2] === 'I' || step[2] === 'E') && step[3] === level) break
          walk++
          step = this.items[walk]
        }
        if (step) {
          this.stepInto(this.step, walk)
        }
      },
      stepBack: function () {
        if (this.stepHistory.length > 0) {
          this.stepInto(-1, this.stepHistory.pop())
        }
      },
      _continue: function () {
        let step = this.step + 1
        let item = this.items[step]
        let breakpoints = {}
        while (item) {
          if (!breakpoints[item[0]]) {
            // load breakpoint from localstorage
            let breakpoint = this.$localStorage.get(item[0])
            if (breakpoint) {
              breakpoint = JSON.parse(breakpoint)
              breakpoints[item[0]] = breakpoint
            }
          }
          if (breakpoints[item[0]] && breakpoints[item[0]][item[1]]) {
            // find next breakpoint
            this.stepInto(this.step, step)
            return
          }
          step++
          item = this.items[step]
        }
      },
      reset: function (breakpoint) {
        this.file = ''
        this.line = 0
        this.type = ''
        this.level = 0
        this.stepHistory = []
        this.step = breakpoint
        this.stepInto(-1, this.step)
      },
      loadFile: function () {
        this.$bus.curSourceFile = this.file
        this.$http.get('/source?file=' + encodeURI(this.file))
          .then(resp => {
            const editor = this.sourceEditor
            editor.setValue(resp.data)
            editor.gotoLine(this.line)
            editor.renderer.on('afterRender', () => {
              const lines = this.$refs.code.querySelector('div.ace_gutter-layer').childNodes
              const fileCoverage = this.fileCoverage[this.file]
              let breakpoints = this.$localStorage.get(this.file)
              if (breakpoints) {
                breakpoints = JSON.parse(breakpoints)
              }
              for (let index = 0; index < lines.length; index++) {
                const el = lines[index]
                if (fileCoverage[el.innerText] !== undefined) {
                  if (this.line === Number(el.innerText)) {
                    el.style.backgroundColor = '#1e7e34'
                  } else {
                    el.style.backgroundColor = '#87ff9d'
                  }
                } else {
                  el.style.backgroundColor = null
                  el.style.cursor = null
                }
                if (breakpoints && breakpoints[el.innerText]) {
                  el.style.color = '#ff0000'
                  el.style.fontWeight = 'bold'
                }
              }
              editor.focus()
            })
          })
      },
      justLoadFile: function (file, line) {
        this.$bus.curSourceFile = file
        this.$http.get('/source?file=' + encodeURI(file))
          .then(resp => {
            const editor = this.sourceEditor
            editor.setValue(resp.data)
            if (line) {
              editor.gotoLine(line)
            } else {
              editor.navigateFileEnd()
            }
            editor.renderer.on('afterRender', () => {
              const lines = this.$refs.code.querySelector('div.ace_gutter-layer').childNodes
              const fileCoverage = this.fileCoverage[file]
              let breakpoints = this.$localStorage.get(this.file)
              if (breakpoints) {
                breakpoints = JSON.parse(breakpoints)
              }
              for (let index = 0; index < lines.length; index++) {
                const el = lines[index]
                if (fileCoverage[el.innerText] !== undefined) {
                  if (this.file === this.$bus.curSourceFile && this.line === Number(el.innerText)) {
                    el.style.backgroundColor = '#1e7e34'
                  } else {
                    el.style.backgroundColor = '#87ff9d'
                  }
                } else {
                  el.style.backgroundColor = null
                  el.style.cursor = null
                }
                if (breakpoints && breakpoints[el.innerText]) {
                  el.style.color = '#ff0000'
                  el.style.fontWeight = 'bold'
                }
              }
            })
          })
      }
    }
  }
</script>

<style lang="stylus" scoped>
  button
    float right
    margin-right 0.5rem
    cursor pointer
  #mode
    float right
    label
      cursor pointer
</style>

<style lang="stylus">
  .ace_gutter-cell
    cursor pointer
</style>
