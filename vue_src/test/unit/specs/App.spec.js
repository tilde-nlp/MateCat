import {shallowMount, createLocalVue} from '@vue/test-utils'
import {AlertsObserver} from '@shibetec/vue-toolbox'
import VueRouter from 'vue-router'
import App from '@/App'

describe('App.vue', () => {
  const localVue = createLocalVue()
  localVue.use(VueRouter)
  const router = new VueRouter()
  localVue.prototype.$Alerts = AlertsObserver
  let wrapper
  beforeEach(() => {
    wrapper = shallowMount(App, {
      localVue,
      router
    })
  })
  /**
   * Test name of component
   */
  it('Is called confirmation', () => {
    expect(wrapper.name()).toBe('App')
  })
  /**
   * Can add alert
   */
  it('Can add alert', () => {
    expect(wrapper.vm.alerts.length).toBe(0)
    const alert = {id: 1, text: 'doge'}
    wrapper.vm.newAlert(alert)
    expect(wrapper.vm.alerts).toEqual([alert])
  })
  /**
   * Can remove alert specified by id
   */
  it('Can remove alert specified by id', () => {
    expect(wrapper.vm.alerts.length).toBe(0)
    const alert1 = {id: 1, text: 'doge'}
    const alert2 = {id: 2, text: 'doge2'}
    wrapper.vm.newAlert(alert1)
    wrapper.vm.newAlert(alert2)
    expect(wrapper.vm.alerts).toEqual([alert1, alert2])
    wrapper.vm.removeAlert(2)
    expect(wrapper.vm.alerts).toEqual([alert1])
    wrapper.vm.removeAlert(2)
    expect(wrapper.vm.alerts).toEqual([alert1])
    wrapper.vm.removeAlert(1)
    expect(wrapper.vm.alerts).toEqual([])
  })
})
