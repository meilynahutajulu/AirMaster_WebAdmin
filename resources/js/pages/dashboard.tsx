import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import * as React from "react"
import { TrendingUp } from "lucide-react"
import { Label, Pie, PieChart } from "recharts"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  ChartConfig,
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
} from "@/components/ui/chart"


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const chartData = [
  { role: "Pilot Instructor", visitors: 275, fill: "red" },
  { role: "Pilot Administrator", visitors: 200, fill: "yellow" },
  { role: "CPTS", visitors: 287, fill: "green" }, 
  { role: "Pilot Examine", visitors: 173, fill: "orange" },
//   { browser: "other", visitors: 190, fill: "var(--color-other)" },
]

const pilotStatus = [
    {status: "Active", count: 275, fill: "red"},
    {status: "Inactive", count: 200, fill: "yellow"},
]
const chartConfig = {
  visitors: {
    label: "Visitors",
  },
  chrome: {
    label: "Chrome",
    color: "hsl(var(--chart-1))",
  },
  safari: {
    label: "Safari",
    color: "hsl(var(--chart-2))",
  },
  firefox: {
    label: "Firefox",
    color: "hsl(var(--chart-3))",
  },
  edge: {
    label: "Edge",
    color: "hsl(var(--chart-4))",
  },
  other: {
    label: "Other",
    color: "hsl(var(--chart-5))",
  },
} satisfies ChartConfig
const totalVisitors = chartData.reduce((acc, { visitors }) => acc + visitors, 0)

const pilotStatusTotal = pilotStatus.reduce((acc, { count }) => acc + count, 0)


export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="w-full border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <Card className="flex flex-col">
                        <CardHeader className="items-center pb-0">
                            <CardTitle>Pie Chart - Pilot IAA</CardTitle>
                            <CardDescription>January - June 2024</CardDescription>
                        </CardHeader>
                        <CardContent className="flex-1 pb-0">
                            <ChartContainer
                            config={chartConfig}
                            className="mx-auto aspect-square max-h-[250px]"
                            >
                            <PieChart>
                                <ChartTooltip
                                cursor={false}
                                content={<ChartTooltipContent hideLabel />}
                                />
                                <Pie
                                data={chartData}
                                dataKey="visitors"
                                nameKey="role"
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
                                            Visitors
                                            </tspan>
                                        </text>
                                        )
                                    }
                                    }}
                                />
                                </Pie>
                            </PieChart>
                            </ChartContainer>
                        </CardContent>
                        <CardFooter className="flex-col gap-2 text-sm">
                            <div className="flex items-center gap-2 font-medium leading-none">
                            Trending up by 5.2% this month <TrendingUp className="h-4 w-4" />
                            </div>
                            <div className="leading-none text-muted-foreground">
                            Showing total visitors for the last 6 months
                            </div>
                        </CardFooter>
                        </Card>
                    </div>

                    <div className="w-full border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <Card className="flex flex-col">
                        <CardHeader className="items-center pb-0">
                            <CardTitle>Pie Chart - Pilot IAA</CardTitle>
                            <CardDescription>January - June 2024</CardDescription>
                        </CardHeader>
                        <CardContent className="flex-1 pb-0">
                            <ChartContainer
                            config={chartConfig}
                            className="mx-auto aspect-square max-h-[250px]"
                            >
                            <PieChart>
                                <ChartTooltip
                                cursor={false}
                                content={<ChartTooltipContent hideLabel />}
                                />
                                <Pie
                                data={pilotStatus}
                                dataKey="count"
                                nameKey="status"
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
                                            {pilotStatusTotal.toLocaleString()}
                                            </tspan>
                                            <tspan
                                            x={viewBox.cx}
                                            y={(viewBox.cy || 0) + 24}
                                            className="fill-muted-foreground"
                                            >
                                            Visitors
                                            </tspan>
                                        </text>
                                        )
                                    }
                                    }}
                                />
                                </Pie>
                            </PieChart>
                            </ChartContainer>
                        </CardContent>
                        <CardFooter className="flex-col gap-2 text-sm">
                            <div className="flex items-center gap-2 font-medium leading-none">
                            Trending up by 5.2% this month <TrendingUp className="h-4 w-4" />
                            </div>
                            <div className="leading-none text-muted-foreground">
                            Showing total visitors for the last 6 months
                            </div>
                        </CardFooter>
                        </Card>
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border md:min-h-min">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
