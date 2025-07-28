import * as React from "react"
import { Label, Pie, PieChart } from "recharts"
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart"
import { text } from "stream/consumers";

// Komponen PieChart yang reusable
interface PieChartWithTextProps {
  chartData: { attribute: string; data: number; fill: string }[]
  chartConfig: any
  countData: number
}

const PieChartWithText: React.FC<PieChartWithTextProps> = ({ chartData, chartConfig, countData }) => {
  const totalVisitors = countData

  return (
    <ChartContainer config={chartConfig} className="mx-auto aspect-square max-h-[250px]">
      <PieChart>
        <ChartTooltip cursor={false} content={<ChartTooltipContent hideLabel />} />
        <Pie
          data={chartData}
          dataKey="data"
          nameKey="attribute"
          innerRadius={60}
          strokeWidth={5}
        >
          <Label
            content={({ viewBox }) => {
              if (viewBox && "cx" in viewBox && "cy" in viewBox) {
                  return (
                    <text
                      x={viewBox.cx}
                      y={viewBox.cy}
                      textAnchor="middle"
                      dominantBaseline="middle"
                    >
                      <tspan
                        x={viewBox.cx}
                        y={viewBox.cy}
                        className="fill-foreground text-3xl font-bold"
                      >
                        {totalVisitors.toLocaleString()}
                      </tspan>
  
                      <tspan
                        x={viewBox.cx}
                        y={(viewBox.cy || 0) + 24}
                        className="fill-muted-foreground"
                      >
                        {totalVisitors === 0
                          ? "No Pilot"
                          : totalVisitors === 1
                          ? "Pilot"
                          : "Pilots"}
                      </tspan>
  
                    </text>
                  )
                }
            }}
          />
        </Pie>
      </PieChart>
    </ChartContainer>
  )
}

export default PieChartWithText
