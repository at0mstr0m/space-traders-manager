// Composables
import { createRouter, createWebHistory } from "vue-router";

const routes = [
  {
    path: "/",
    component: () => import("@/layouts/Default.vue"),
    children: [
      {
        path: "",
        name: "Home",
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () =>
          import("@/views/Home.vue"),
      },
      {
        path: "ships",
        name: "Ships",
        component: () =>
          import("@/views/Ships.vue"),
      },
      {
        path: "map",
        name: "Map",
        component: () =>
          import("@/views/Map.vue"),
      },
      {
        path: "contracts",
        name: "Contracts",
        component: () =>
          import("@/views/Contracts.vue"),
      },
    ],
  },
  {
    path: "/login",
    name: "Login",
    component: () =>
      import("@/views/Login.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
