<template>
  <b-list-group style="max-height: 35rem;">
    <b-list-group-item v-for="(item, index) in showItems" :key="index" v-on:click="rowClicked(item)" v-bind:class="{ 'active': index === select }">
      {{item.file}}: {{item.line}}
      <div v-on:click.stop="remove(item)" style="display: inline-block; float: right;">
        <icon name="close"></icon>
      </div>
    </b-list-group-item>
  </b-list-group>
</template>

<script>
  export default {
    props: {
      search: String,
      select: Number,
      enter: Boolean
    },
    data () {
      return {
        showItems: [],
        items: []
      }
    },
    mounted () {
      this.fetchBreakpoints()
    },
    watch: {
      search: function (val) {
        let showItems = []
        for (let index in this.items) {
          if (this.items[index].file.indexOf(val) >= 0) {
            showItems.push(this.items[index])
          }
        }
        this.showItems = showItems
        this.$emit('updateShowLen', this.showItems.length)
      },
      enter: function (val) {
        if (val) {
          this.$emit('close')
          const item = this.showItems[this.select]
          this.$bus.$emit('openFile', item.file, item.line)
        }
      }
    },
    methods: {
      fetchBreakpoints () {
        let items = []
        for (let file in window.localStorage) {
          const lines = JSON.parse(window.localStorage[file])
          for (let line in lines) {
            items.push({file: file, line: line})
          }
        }
        this.items = items
        this.showItems = this.items
        this.$emit('updateShowLen', this.showItems.length)
      },
      rowClicked (item) {
        this.$emit('close')
        this.$bus.$emit('openFile', item.file, item.line)
      },
      remove (item) {
        let breakpoint = this.$localStorage.get(item.file)
        if (breakpoint) {
          breakpoint = JSON.parse(breakpoint)
          delete breakpoint[item.line]
          this.$localStorage.set(item.file, JSON.stringify(breakpoint))
          this.$bus.$emit('removeBp', item.file, item.line)
          this.fetchBreakpoints()
        }
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .list-group-item
    cursor pointer
</style>
