import { ref } from 'vue';

export function useRerenderingHelper () {
  // Add random number to ensure helperValue is unique and can be used multiple times on the same page
  const rerenderingKey = ref('helperValue_' + Math.floor(Math.random() * 100000000) + '_' + 0);

  function forceRerendering () {
      let [prefix, uniqueId, counter] = rerenderingKey.value.split('_');
      counter = (parseInt(counter ?? '') + 1).toString();
      rerenderingKey.value = [prefix, uniqueId, counter].join('_');
  }

  return [rerenderingKey, forceRerendering];
}

export function dump(value) {
  console.log(value);
  return value;
}