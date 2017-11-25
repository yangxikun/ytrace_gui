<template>
  <div id="app">
    <div id="head">
      <b-navbar toggleable="md" type="light" variant="light">

        <b-nav-toggle target="nav_collapse"></b-nav-toggle>

        <b-navbar-brand href="#">Ytrace</b-navbar-brand>

        <b-collapse is-nav id="nav_collapse">

          <b-nav is-nav-bar>
            <b-nav-item>
              <router-link class="nav-link" to="/home" v-bind:class="{ active: nav == 'Home'}">Home</router-link>
            </b-nav-item>
            <b-nav-item>
              <router-link :event="traceId ? 'click' : ''" class="nav-link" :to="'/trace/source/'+traceId" v-bind:class="{ disabled: traceId == '', active: nav == 'Source'}">Source</router-link>
            </b-nav-item>
            <b-nav-item>
              <router-link :event="traceId ? 'click' : ''" class="nav-link" :to="'/trace/call/'+traceId" v-bind:class="{ disabled: traceId == '', active: nav == 'Stack'}">Stack</router-link>
            </b-nav-item>
          </b-nav>

        </b-collapse>

      </b-navbar>

    </div>
    <keep-alive exclude="Home">
    <router-view/>
    </keep-alive>
    <popup-list></popup-list>
  </div>
</template>

<script>
  import PopupList from '@/components/PopupList'
  export default {
    name: 'app',
    components: {
      PopupList
    },
    data () {
      return {
        nav: 'Home',
        traceId: ''
      }
    },
    watch: {
      '$route': 'activateNav'
    },
    mounted () {
      console.log(this.$route)
      this.activateNav()
    },
    methods: {
      activateNav () {
        this.nav = this.$route.name
        if (this.$route.params.id) {
          this.traceId = this.$route.params.id
        }
      }
    }
  }
</script>

<style lang="stylus" scoped>
  .disabled
    cursor default
</style>
