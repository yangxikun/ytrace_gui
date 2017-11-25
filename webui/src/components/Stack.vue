<template>
  <div>
    <div style="overflow: scroll" v-bind:style="{ height: stackHeight + 'px' }" v-show="!loading" ref="stack">
      <div class="statement" v-on:click="jump(item.step)" v-for="item in items" v-bind:style="{padding: '0.5rem 0rem 0rem ' + (item.level - lowestLevel) * 3 + 'rem'}">
        <div style="float: left; color: #7c7c7d;">{{item.file}}:{{item.line}}</div>
        <pre v-bind:class="{scope: item.type === 'F' || item.type === 'I' || item.type === 'E'}">{{item.statement}}</pre>
      </div>
    </div>
    <div v-show="loading" style="margin-top: 5%;">
      <vue-loading type="bars" color="#d9544e" :size="{ width: '8rem', height: '6rem' }"></vue-loading>
    </div>
  </div>
</template>

<script>
  import vueLoading from 'vue-loading-template'
  const sprintf = require('sprintf-js').sprintf
  export default {
    name: 'Trace-Stack',
    components: {
      vueLoading
    },
    data () {
      return {
        loading: true,
        id: this.$route.params.id,
        items: [],
        stackHeight: 0,
        scrollTop: 0,
        lowestLevel: Number.MAX_SAFE_INTEGER
      }
    },
    created () {
      this.fetchTrace()
    },
    mounted () {
      console.log('-------->stack mounted')
      const body = document.body
      const html = document.documentElement
      const docHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight)
      this.stackHeight = docHeight - document.getElementById('head').clientHeight - 44
      this.$refs.stack.addEventListener('scroll', () => {
        this.scrollTop = this.$refs.stack.scrollTop
      })
    },
    activated () {
      if (this.$route.params.id !== undefined) {
        if (this.id !== this.$route.params.id) {
          this.id = this.$route.params.id
          this.scrollTop = 0
          this.fetchTrace()
        } else if (this.scrollTop > 0) {
          this.$refs.stack.scrollTop = this.scrollTop
        }
      }
    },
    methods: {
      // https://github.com/kvz/locutus/blob/master/src/php/strings/stripslashes.js
      stripslashes: function (str) {
        return (str + '').replace(/\\./g, function (match) {
          return (new Function('return "' + match + '"'))() || match
        })
      },
      fetchTrace: function () {
        this.loading = true
        this.$http.get('/trace/' + this.id)
          .then(resp => {
            const _items = resp.data.trimRight().split('\n')
            delete _items[0]
            let items = []
            for (let index in _items) {
              const item = _items[index].split('\t')
              let statement = ''
              switch (item[2]) {
                case 'A':
                  statement = this.stripslashes(item[4]) + ' = ' + this.stripslashes(item[5])
                  break
                case 'F':
                  let params = []
                  const argCount = item[5]
                  for (let i = 6; i < 6 + 2 * argCount; i += 2) {
                    params.push(this.stripslashes(item[i]) + ' = ' + this.stripslashes(item[i + 1]))
                  }
                  statement = sprintf('%s (%s)', item[4], params.join(', '))
                  break
                case 'I':
                  statement = 'include(' + this.stripslashes(item[5]) + ')'
                  break
                case 'E':
                  statement = '{eval}(' + this.stripslashes(item[5]) + ')'
                  break
              }
              const level = Number(item[3])
              if (level < this.lowestLevel) {
                this.lowestLevel = level
              }
              items.push({
                statement: statement,
                step: index - 1,
                file: item[0],
                line: item[1],
                type: item[2],
                level: level
              })
            }
            this.items = items
            this.loading = false
            console.log('lowestLevel', this.lowestLevel)
          })
      },
      jump: function (step) {
        this.$router.push({name: 'Source', params: {id: this.id}, query: {step: step}})
      }
    }
  }
</script>

<style lang="stylus" scoped>
  #mode
    label
      cursor pointer
  .statement
    cursor pointer
    :hover
      background-color #eaeaea
  pre
    font-size 100%
    margin 0
    padding-left 1rem
  .scope
    border-top 2px solid #e6e6e6
</style>
