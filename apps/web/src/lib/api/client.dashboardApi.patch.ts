export const dashboardApi = {
  summary: () => getDashboardSummary(apiHttpClient),
  production: (
    queryParams?: Parameters<typeof getDashboardProduction>[1],
  ): Promise<DashboardProductionEnvelope> => getDashboardProduction(apiHttpClient, queryParams),
}
