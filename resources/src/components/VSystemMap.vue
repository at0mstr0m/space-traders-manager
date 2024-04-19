<template>
  <scatter
    :options="options"
    :data="props.data"
  />
</template>

<script setup>
import {
  Chart as ChartJS,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend
} from 'chart.js'
import zoomPlugin from 'chartjs-plugin-zoom';
import { Scatter } from 'vue-chartjs'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

const customCanvasBackgroundColor = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart, args, options) => {
    const {ctx} = chart;
    ctx.save();
    ctx.globalCompositeOperation = 'destination-over';
    ctx.fillStyle = options.color || '#99ffff';
    ctx.fillRect(0, 0, chart.width, chart.height);
    ctx.restore();
  }
};

ChartJS.register(
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Legend,
    zoomPlugin,
    customCanvasBackgroundColor
);

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    customCanvasBackgroundColor: {
    //   color: '#0F172A',
      color: '#FFFFFF',
    },
    zoom: {
      zoom: {
        wheel: {
          enabled: true,
        },
        pinch: {
          enabled: true
        },
        mode: 'xy',
      },
      pan: {
        enabled: true,
      }
    }
  }
};
</script>