<template>
  <b-modal hide-header hide-footer size="lg" ref="popupList" @shown="searchInputFocus">
    <div style="padding: 0 10rem; margin-bottom: 1rem;">
      <b-form-input v-model="search" type="text" ref="searchInput"></b-form-input>
    </div>
    <traces-nav :enter="enter" :search="search" :select="select" v-on:close="close" v-on:updateShowLen="updateShowLen" v-if="type === 'traces-nav'"></traces-nav>
    <func-nav :enter="enter" :search="search" :select="select" v-on:close="close" v-on:updateShowLen="updateShowLen" v-if="type === 'func-nav'"></func-nav>
    <file-nav :enter="enter" :search="search" :select="select" v-on:close="close" v-on:updateShowLen="updateShowLen" v-if="type === 'file-nav'"></file-nav>
    <breakpoint-nav :enter="enter" :search="search" :select="select" v-on:close="close" v-on:updateShowLen="updateShowLen" v-if="type === 'breakpoint-nav'"></breakpoint-nav>
  </b-modal>
</template>

<script>
  import TracesNav from './TracesNav.vue'
  import FuncNav from './FuncNav.vue'
  import FileNav from './FileNav.vue'
  import BreakpointNav from './BreakpointNav.vue'
  export default {
    components: {
      TracesNav,
      FuncNav,
      FileNav,
      BreakpointNav
    },
    data () {
      return {
        search: '',
        type: '',
        select: 0,
        enter: false,
        showLen: 0
      }
    },
    mounted () {
      document.onkeydown = (ev) => {
        if (ev.key === 'Escape') {
          this.type = ''
          this.enter = false
          return
        }
        switch (true) {
          case ev.key === 'o' && ev.ctrlKey:
            ev.preventDefault()
            this.search = ''
            this.select = 0
            this.type = 'traces-nav'
            this.enter = false
            this.$refs.popupList.show()
            break
          case ev.key === 'r' && ev.ctrlKey && this.$route.name === 'Source':
            ev.preventDefault()
            this.search = ''
            this.select = 0
            this.type = 'func-nav'
            this.enter = false
            this.$refs.popupList.show()
            break
          case ev.key === 'p' && ev.ctrlKey && this.$route.name === 'Source':
            ev.preventDefault()
            this.search = ''
            this.select = 0
            this.type = 'file-nav'
            this.enter = false
            this.$refs.popupList.show()
            break
          case ev.key === 'b' && ev.ctrlKey && this.$route.name === 'Source':
            ev.preventDefault()
            this.search = ''
            this.select = 0
            this.type = 'breakpoint-nav'
            this.enter = false
            this.$refs.popupList.show()
            break
          case this.$refs.popupList.is_show && ev.key === 'ArrowDown' && this.showLen > 1:
            if (this.select === this.showLen - 1) {
              this.select = 0
            } else {
              this.select += 1
            }
            break
          case this.$refs.popupList.is_show && ev.key === 'ArrowUp' && this.showLen > 1:
            if (this.select === 0) {
              this.select = this.showLen - 1
            } else {
              this.select -= 1
            }
            break
          case this.$refs.popupList.is_show && ev.key === 'Enter' && this.showLen > 0:
            this.enter = true
            break
        }
      }
    },
    methods: {
      searchInputFocus () {
        this.$refs.searchInput.focus()
      },
      updateShowLen (val) {
        this.showLen = val
      },
      close () {
        this.type = ''
        this.enter = false
        this.$refs.popupList.hide()
      }
    }
  }
</script>
