<template>
  <div>
    <v-img
      class="mx-auto my-6"
      max-width="228"
      src="https://spacetraders.io/logo/logo-over-black.svg"
    />
    <v-card
      class="mx-auto pa-12 pb-8"
      elevation="8"
      max-width="448"
      rounded="lg"
    >
      <v-form>
        <div class="text-subtitle-1 text-medium-emphasis">Account</div>
        <v-text-field
          v-model="email"
          density="compact"
          placeholder="Email address"
          prepend-inner-icon="mdi-email-outline"
          variant="outlined"
        />
        <div
          class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between"
        >
          Password
          <a
            class="text-caption text-decoration-none text-blue"
            href="#"
            rel="noopener noreferrer"
            target="_blank"
          >
            Forgot login password?
          </a>
        </div>
        <v-text-field
          v-model="password"
          :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
          :type="visible ? 'text' : 'password'"
          density="compact"
          placeholder="Enter your password"
          prepend-inner-icon="mdi-lock-outline"
          variant="outlined"
          autocomplete="on"
          @click:append-inner="visible = !visible"
        />
        <v-btn
          block
          class="mb-8"
          color="blue"
          size="large"
          variant="tonal"
          text="Log In"
          @click="login"
        />
      </v-form>
      <v-card-text class="text-center">
        <a
          class="text-blue text-decoration-none"
          href="#"
          rel="noopener noreferrer"
          target="_blank"
        >
          Register <v-icon icon="mdi-chevron-right" />
        </a>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from 'vue-router'
import useUserStore from "@/store/user";

const userStore = useUserStore();
const router = useRouter();

const visible = ref(false);
const email = ref("");
const password = ref("");

async function login() {
  try {
    console.log(await userStore.login(email.value, password.value));
  } catch (error) {
    console.log(error);
    return;
  }
  router.push({ name: "home" });
}

onMounted(() => {
  if (userStore.isAuthenticated()) {
    router.push({ name: "home" });
  }
});
</script>
