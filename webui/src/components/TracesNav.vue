<template>
  <table class="table">
    <tbody>
      <tr v-for="(file, index) in showFiles" v-on:click="rowClicked(file)" v-bind:class="{ 'table-active': index === select }">
        <td>{{file.sapi}}</td>
        <td>{{file.method}}</td>
        <td class="uri">{{file.uri}}</td>
        <td>{{file.file}}</td>
        <td>{{file.size}}</td>
        <td>{{file.time}}</td>
      </tr>
    </tbody>
  </table>
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
        files: [],
        showFiles: []
      }
    },
    mounted () {
      this.fetchTraceFiles()
    },
    watch: {
      search: function (val) {
        let showFiles = []
        for (let index in this.files) {
          if (this.files[index].uri && this.files[index].uri.indexOf(val) >= 0) {
            showFiles.push(this.files[index])
          } else if (this.files[index].file.indexOf(val) >= 0) {
            showFiles.push(this.files[index])
          }
        }
        this.showFiles = showFiles
        this.$emit('updateShowLen', this.showFiles.length)
      },
      enter: function (val) {
        if (val) {
          this.$emit('close')
          this.$router.push({ name: 'Source', params: {id: this.showFiles[this.select].file} })
        }
      }
    },
    methods: {
      fetchTraceFiles () {
        this.$http.get('/trace')
          .then(resp => {
            this.search = ''
            this.files = resp.data.slice(0, 10)
            this.showFiles = this.files
            this.$emit('updateShowLen', this.showFiles.length)
          })
      },
      rowClicked (item) {
        this.$emit('close')
        this.$router.push({ name: 'Source', params: {id: item.file} })
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .uri
    max-width 25rem
    word-wrap break-word
  tr
    cursor pointer
</style>
