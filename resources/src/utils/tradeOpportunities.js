export default function useTradeOpportunityUtils() {
  const overviewTableColumns = [
    {
      title: "Symbol",
      key: "symbol",
    },
    {
      title: "Waypoint Symbol",
      key: "waypoint_symbol",
    },
    {
      title: "Purchase Price",
      key: "purchase_price",
    },
    {
      title: "Sell Price",
      key: "sell_price",
    },
    {
      title: "Type",
      key: "type",
    },
    {
      title: "Trade Volume",
      key: "trade_volume",
    },
    {
      title: "Supply",
      key: "supply",
    },
    {
      title: "Activity",
      key: "activity",
    },
  ];

  const marketTableColumns = [
    {
      title: "Type",
      key: "type",
    },
    {
      title: "Symbol",
      key: "symbol",
    },
    {
      title: "Purchase Price",
      key: "purchase_price",
    },
    {
      title: "Sell Price",
      key: "sell_price",
    },
    {
      title: "Trade Volume",
      key: "trade_volume",
    },
    {
      title: "Activity",
      key: "activity",
    },
  ];

  return {
    overviewTableColumns,
    marketTableColumns,
  };
}
