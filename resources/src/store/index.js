// Utilities
import { createPinia } from "pinia";
import SimplerOnAction from "@piniaPlugins/SimplerOnAction";
import HardReset from "@piniaPlugins/HardReset";

export default createPinia()
  .use(SimplerOnAction)
  .use(HardReset);
