<template>
  <b-list-group style="max-height: 35rem; overflow-y: scroll;">
    <b-list-group-item v-for="(item, index) in showItems" :key="index" v-on:click="rowClicked(item.line)" v-bind:class="{ 'active': index === select }">
      {{item.prototype}}
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
      this.fetchFileNav()
    },
    watch: {
      search: function (val) {
        let showItems = []
        for (let index in this.items) {
          if (this.items[index].name.indexOf(val) >= 0) {
            showItems.push(this.items[index])
          }
        }
        this.showItems = showItems
        this.$emit('updateShowLen', this.showItems.length)
      },
      enter: function (val) {
        if (val) {
          this.$emit('close')
          this.$bus.$emit('gotoLine', this.showItems[this.select].line)
        }
      }
    },
    methods: {
      fetchFileNav () {
        this.$http.get('/file-nav?file=' + encodeURI(this.$bus.curSourceFile))
          .then(resp => {
            this.search = ''
            let items = []
            for (let index in resp.data) {
              const item = resp.data[index]
              if (item.class) {
                items.push({
                  name: item.name,
                  prototype: item.class + '::' + item.name + '(' + item.params + ')',
                  line: item.line
                })
              } else {
                items.push({
                  name: item.name,
                  prototype: item.name + '(' + item.params + ')',
                  line: item.line
                })
              }
            }
            this.items = items
            this.showItems = this.items
            this.$emit('updateShowLen', this.showItems.length)
          })
      },
      rowClicked (line) {
        this.$emit('close')
        this.$bus.$emit('gotoLine', line)
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .list-group-item
    cursor pointer
</style>
