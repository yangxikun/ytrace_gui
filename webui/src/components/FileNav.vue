<template>
  <b-list-group style="max-height: 35rem;">
    <b-list-group-item v-for="(item, index) in showItems" :key="index" v-on:click="rowClicked(item)" v-bind:class="{ 'active': index === select }">
      {{item}}
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
          if (this.items[index].indexOf(val) >= 0) {
            showItems.push(this.items[index])
          }
        }
        this.showItems = showItems
        this.$emit('updateShowLen', this.showItems.length)
      },
      enter: function (val) {
        if (val) {
          this.$emit('close')
          this.$bus.$emit('openFile', this.showItems[this.select])
        }
      }
    },
    methods: {
      fetchFileNav () {
        this.$http.get('/file-nav/' + this.$route.params.id)
          .then(resp => {
            this.search = ''
            this.items = resp.data
            this.showItems = this.items
            this.$emit('updateShowLen', this.showItems.length)
          })
      },
      rowClicked (file) {
        this.$emit('close')
        this.$bus.$emit('openFile', file)
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .list-group-item
    cursor pointer
</style>
