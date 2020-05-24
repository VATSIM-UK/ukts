import {InertiaApp} from '@inertiajs/inertia-vue'
import Vue from 'vue'
import Consilio from '@vatsim-uk/consilio'
import "@vatsim-uk/consilio/dist/consilio.css"

Vue.use(Consilio)
Vue.use(InertiaApp)

const app = document.getElementById('app')

new Vue({
    render: h => h(InertiaApp, {
        props: {
            initialPage: JSON.parse(app.dataset.page),
            resolveComponent: name => require(`./Pages/${name}`).default,
        },
    }),
}).$mount(app)
