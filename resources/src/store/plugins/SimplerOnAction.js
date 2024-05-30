/**
 * Enables listening for actions with a simpler syntax.
 * inspired by:
 * @link https://stackoverflow.com/a/75198987
 */
export default function ({ store }) {
  store.onAction = (action, callback) => {
    store.$onAction((ctx) => {
      if (ctx.name === action) {
        callback(ctx);
      }
    });
  };
}