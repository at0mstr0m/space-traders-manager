// Utilities
import { createPinia } from "pinia";

export default createPinia()
  // allowes to listen for actions with easyer syntax
  // inspired by https://stackoverflow.com/a/75198987
  .use(({ store }) => {
    store.onAction = (action, callback) => {
      store.$onAction((ctx) => {
        if (ctx.name === action) {
          callback(ctx);
        }
      });
    };
  });
