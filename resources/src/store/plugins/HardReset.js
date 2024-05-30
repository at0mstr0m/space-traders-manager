/**
 * Reset Store to initial state.
 * inspired by:
 * @link https://dev.to/the_one/pinia-how-to-reset-stores-created-with-functionsetup-syntax-1b74
 */

import _cloneDeep from "lodash/cloneDeep";

export default function ({ store }) {
  const initialState = _cloneDeep(store.$state);
  store.hardReset = () => store.$patch(_cloneDeep(initialState));
}