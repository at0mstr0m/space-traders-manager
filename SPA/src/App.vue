<template>
  <router-view />
</template>

<script setup>
  import axios from 'axios';
  axios.get('http://localhost/api/').then((res) => {
    console.log(res);
  });
  console.log('Bingo Bongo');
</script>
