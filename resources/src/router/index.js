// Composables
import { createRouter, createWebHistory } from "vue-router";

const routes = [
  {
    path: "/",
    component: () => import("@/layouts/Default.vue"),
    children: [
      {
        path: "",
        name: "home",
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () =>
          import("@/views/Home.vue"),
      },
      {
        path: "ships",
        name: "ships",
        component: () =>
          import("@/views/Ships.vue"),
      },
      {
        path: "map",
        name: "map",
        component: () =>
          import("@/views/Map.vue"),
      },
      {
        path: "contracts",
        name: "contracts",
        component: () =>
          import("@/views/Contracts.vue"),
      },
      {
        path: "potential-trade-routes",
        name: "potential-trade-routes",
        component: () =>
          import("@/views/PotentialTradeRoutes.vue"),
      },
      {
        path: "purchasable-ships",
        name: "purchasable-ships",
        component: () =>
          import("@/views/PurchasableShips.vue"),
      },
    ],
  },
  {
    path: "/login",
    name: "login",
    component: () =>
      import("@/views/Login.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
