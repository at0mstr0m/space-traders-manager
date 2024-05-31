<template>
  <scatter
    ref="scatterPlot"
    v-bind="{ ...$attrs, ...$props }"
    :options="options"
    :data="props.data"
    @click="onClick"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Chart as ChartJS,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend
} from 'chart.js'
import zoomPlugin from 'chartjs-plugin-zoom';
import { Scatter, getElementAtEvent } from 'vue-chartjs'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});
const emit = defineEmits(['select']);

const scatterPlot = ref(null);

const customCanvasBackgroundColor = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart, args, options) => {
    const { ctx } = chart;
    ctx.save();
    ctx.globalCompositeOperation = 'destination-over';
    ctx.fillStyle = options.color || '#99ffff';
    ctx.fillRect(0, 0, chart.width, chart.height);
    ctx.restore();
  }
};

// https://www.youtube.com/watch?v=PNbDrDI97Ng
const scatterDataLabels = computed(() => ({
  id: 'scatterDataLabels',
  afterDatasetsDraw(chart, args, options) {
    const { ctx } = chart;
    ctx.save();
    ctx.fillStyle = '#ffffff';
    for (let x = 0; x < chart.config.data.datasets.length; x++) {
      for (let i = 0; i < chart.config.data.datasets[x].data.length; i++) {
        let textWidth = ctx.measureText(chart.config.data.datasets[x].data[i].symbol).width;
        ctx.fillText(
          chart.config.data.datasets[x].data[i].symbol,
          chart.getDatasetMeta(x).data[i].x - (textWidth / 3),
          chart.getDatasetMeta(x).data[i].y - 10
        );
      }
    }
    ctx.restore();
  }
}));

ChartJS.register(
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  zoomPlugin,
  customCanvasBackgroundColor,
  scatterDataLabels.value
);

const options = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    customCanvasBackgroundColor: {
      color: '#0F172A',
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
    },
    tooltip: {
      enabled: true,
      // https://www.chartjs.org/docs/latest/configuration/interactions.html#event-option
      events: ['mousemove'],
      callbacks: {
        title: (context) => {
          return context[0].raw.symbol;
        },
        label: (context) => {
          return context.raw.symbol + ' (' + context.raw.type + ')';
        },
        afterLabel: (context) => {
          return [
            'ID: ' + context.raw.id,
            'Faction: ' + (context.raw.faction?.symbol || '-'),
            'x: ' + context.raw.x,
            'y: ' + context.raw.y,
          ];
        }
      }
    }
  }
}));

function onClick(event) {
  const elements = getElementAtEvent(scatterPlot.value.chart, event)
    ?.map(({ datasetIndex, index }) =>  props.data.datasets[datasetIndex].data[index]);

  emit('select', elements);
}
</script>