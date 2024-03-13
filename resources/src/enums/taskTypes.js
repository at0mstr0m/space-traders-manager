const taskTypes = Object.freeze({
  COLLECTIVE_MINING: "COLLECTIVE_MINING",
  SUPPORT_COLLECTIVE_MINERS: "SUPPORT_COLLECTIVE_MINERS",
  SERVE_TRADE_ROUTE: "SERVE_TRADE_ROUTE",
  COLLECTIVE_SIPHONING: "COLLECTIVE_SIPHONING",
});

const taskTypesSelectItems = Object.keys(taskTypes)
  .map((taskType) => ({
    title: taskTypes[taskType],
    value: taskType,
  })
);

export {
  taskTypes,
  taskTypesSelectItems,
};
