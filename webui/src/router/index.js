import Vue from 'vue'
import Router from 'vue-router'
import Source from '@/components/Source'
import Stack from '@/components/Stack'
import Home from '@/components/Home'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/trace/source/:id',
      name: 'Source',
      component: Source
    },
    {
      path: '/trace/call/:id',
      name: 'Stack',
      component: Stack
    },
    {
      path: '/home',
      name: 'Home',
      component: Home
    }
  ]
})
