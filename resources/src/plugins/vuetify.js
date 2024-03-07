/**
 * plugins/vuetify.js
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Composables
import { createVuetify } from 'vuetify'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  theme: {
    defaultTheme: 'dark',
    themes: {
      dark: {
        colors: {
          background: '#031d44',
          surface: '#04395e',
          primary: '#70a288',
          'primary-darken-1': '#4D8267',
          secondary: '#DAB785',
          'secondary-darken-1': '#CFA263',
          'surface-light': '#065289',

          'surface-bright': '#FFFFFF',
          'surface-variant': '#424242',
          'on-surface-variant': '#EEEEEE',
          error: '#B00020',
          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FB8C00',
        },
      },
    },
  },
})
