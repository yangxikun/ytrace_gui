import Vue from 'vue'
import Router from 'vue-router'
import Source from '@/components/Source'
import Index from '@/components/Index'
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
      path: '/home',
      name: 'Home',
      component: Home
    },
    {
      path: '/',
      name: 'Index',
      component: Index
    }
  ]
})
