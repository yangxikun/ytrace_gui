<template>
  <b-container style="text-align: center;">
    <b-row>
      <b-col style="text-align: left;">
        <div style="margin: 1rem 0">
          <b-button v-on:click="clearTraces()" variant="danger">Clear Traces</b-button>
          <b-button v-on:click="fetchTraces()" variant="outline-primary">Refresh</b-button>
        </div>
        <b-table hover :items="files" :fields="fields" @row-clicked="rowClicked"></b-table>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
  export default {
    name: 'Home',
    data () {
      return {
        files: [],
        fields: [
          { key: 'sapi', sortable: true },
          { key: 'method', sortable: true },
          { key: 'uri', sortable: true, tdClass: 'uri' },
          { key: 'file', sortable: true },
          { key: 'size', sortable: false },
          { key: 'time', sortable: true }
        ]
      }
    },
    mounted () {
      this.fetchTraces()
    },
    methods: {
      rowClicked (item, index, event) {
        this.$router.push({ name: 'Source', params: {id: item.file} })
      },
      fetchTraces () {
        this.$http.get('/trace')
          .then(resp => {
            this.files = resp.data
          })
      },
      clearTraces () {
        this.$http.delete('/trace')
          .then(resp => {
            this.fetchTraces()
          })
        this.$bus.$emit('clearTraces')
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .table-hover
    cursor pointer
  button
    cursor pointer
</style>

<style lang="stylus">
  .uri
    max-width 25rem
    word-wrap break-word
</style>
