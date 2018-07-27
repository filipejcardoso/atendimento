//============VUE=====================
import Vue from 'vue'
import axios from 'axios'
import router from './routes/web.js'
import VueAxios from 'vue-axios'
import store from './store/store'

//============COMPONENTES=====================
import App from './App.vue'

//============VUE=====================
Vue.use(VueAxios, axios);

//============COMPONENTES=====================

//============CONSTANTES=====================
window.api = "dev.caronas";

const app = new Vue({
	store,
	el: '#root',
	template: '<app></app>',
	components: { App },
	router
})