<template>
  <div>
    <div>
      <b-button :disabled="wiki == 'en'" v-on:click="wiki = 'en'" size="sm" variant="outline-secondary">EN</b-button>
      <b-button :disabled="wiki == 'zh'" v-on:click="wiki = 'zh'" size="sm" variant="outline-secondary">ZH</b-button>
    </div>
    <wiki-en v-show="wiki == 'en'"></wiki-en>
    <wiki-zh v-show="wiki == 'zh'"></wiki-zh>
  </div>
</template>

<script>
  import WikiEn from './wiki/WikiEn'
  import WikiZh from './wiki/WikiZh'

  export default {
    components: {
      WikiEn,
      WikiZh
    },
    data () {
      return {
        wiki: 'en'
      }
    }
  }
</script>

<style lang="stylus" scoped>
  button
    float right
    margin-right 0.5rem
    cursor pointer
</style>
