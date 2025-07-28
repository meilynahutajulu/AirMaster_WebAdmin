import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { TrendingUp } from "lucide-react"
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
} from "@/components/ui/chart"
import { usePage } from '@inertiajs/react';
import DashboardChart from '@/layouts/users/chart';
import PieChartWithText from '@/layouts/users/pie';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const chartConfig = {
    visitors: {
        label: "Visitors",
    },
    PilotInstructor: {
        label: "Pilot Instructor",
        color: "var(--color-chart-1)",
    },
    PilotExaminee: {
        label: "Pilot Examinee",
        color: "var(--color-chart-2)",
    },
    PilotAdministrator: {
        label: "Pilot Administrator",
        color: "var(--color-chart-4)",
    },
    CPTS: {
        label: "CPTS",
        color: "var(--color-chart-4)",
    },
    Active: {
        label: "Active",
        color: "var(--color-chart-6)",
    },
    Inactive: {
        label: "Inactive",
        color: "var(--color-chart-7)",
    },
    other: {
        label: "Other",
        color: "var(--color-chart-5)",
    },
} satisfies ChartConfig


export default function DashboardPage() {

    // Data Pilot's Role Chart
    const { countInstructor } = usePage().props as unknown as { countInstructor: number };
    const { countExaminee } = usePage().props as unknown as { countExaminee: number };
    const { countAdministrator } = usePage().props as unknown as { countAdministrator: number };
    const { countCPTS } = usePage().props as unknown as { countCPTS: number };

    const charRole = [
        { attribute: "Pilot Instructor", data: countInstructor, fill: chartConfig.PilotInstructor.color },
        { attribute: "Pilot Examinee", data: countExaminee, fill: chartConfig.PilotExaminee.color },
        { attribute: "Pilot Administrator", data: countAdministrator, fill: chartConfig.PilotAdministrator.color },
        { attribute: "CPTS", data: countCPTS, fill: chartConfig.CPTS.color },
    ]

    // Data Pilot's Status
    const { countValid } = usePage().props as unknown as { countValid: number };
    const { countInvalid } = usePage().props as unknown as { countInvalid: number };

    const pilotStatus = [
        { attribute: "Active", data: countValid, fill: chartConfig.Active.color },
        { attribute: "Inactive", data: countInvalid, fill: chartConfig.Inactive.color },
    ]
    const totalType = countInstructor + countExaminee + countAdministrator + countCPTS;
    const pilotStatusTotal = countValid + countInvalid;

    const today = new Date();
    const { totalExpired } = usePage().props as unknown as { totalExpired: number };

    const { loginData } = usePage().props as unknown as { loginData: { date: string; mobile: number }[] };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">

                    {/* Total Users */}

                    <div className="flex flex-col justify-between gap-2">
                        <Link href="/users" className="w-full">
                            <div className="border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden rounded-xl flex flex-col items-center justify-center h-50">

                                {/* Icon with gradient background */}
                                <div className="bg-gradient-to-r from-blue-500 to-indigo-500 p-3 rounded-full mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" className="w-8 h-8">
                                        <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                                    </svg>
                                </div>

                                {/* Animated Number (optional, React only) */}
                                <div className="text-3xl font-bold text-gray-800 dark:text-white">{totalType}</div>
                                <div className="text-sm text-gray-500 dark:text-gray-400">Total Users</div>
                            </div>
                        </Link>
                        <Link href="/users/expired" className='w-full'>
                            <div className="border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden rounded-xl flex flex-col items-center justify-center h-50">
                                {/* Icon with gradient background */}
                                <div className="relative inline-block mb-3">
                                    {/* Lingkaran biru dengan ikon user */}
                                    <div className="bg-gradient-to-r from-red-500 to-red-600 p-3 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" className="w-8 h-8">
                                        <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                                        </svg>
                                    </div>

                                    {/* Badge jam di sudut kanan bawah */}
                                    <div className="absolute -bottom-1 -right-1 bg-black p-1 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>

                                {/* Animated Number (optional, React only) */}
                                <div className="text-3xl font-bold text-gray-800 dark:text-white">{totalExpired}</div>
                                <div className="text-sm text-gray-500 dark:text-gray-400">Total Users Expired</div>
                            </div>
                        </Link>
                    </div>


                    {/* Pilot's Role Chart */}

                    { totalType === 0 ? (
                        <Card className="flex flex-col">
                            <CardHeader className="items-center pb-0">
                                <CardTitle>Pie Chart - IAA Pilot Role</CardTitle>
                                <CardDescription>
                                    {new Date(new Date().setMonth(new Date().getMonth() - 6)).toLocaleDateString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    })} - {new Date().toLocaleDateString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    })}
                                </CardDescription>
                            </CardHeader>
                            <p className="text-lg font-semibold text-muted-foreground italic flex items-center justify-center h-full tracking-wide">
                                No Data Available
                            </p>
                        </Card>
                        ) : (
                            <Card className="flex flex-col">
                                <CardHeader className="items-center pb-0">
                                    <CardTitle>Pie Chart - IAA Pilot Role</CardTitle>
                                    <CardDescription>
                                        {new Date(new Date().setMonth(new Date().getMonth() - 6)).toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        })} - {new Date().toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        })}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="pb-0">
                                    <PieChartWithText chartData={charRole} chartConfig={chartConfig} countData={totalType} />
                                </CardContent>
                                <CardFooter className="flex-col gap-2 text-sm">
                                    <div className="leading-none text-muted-foreground">
                                        PT Indonesia Air Asia
                                    </div>
                                </CardFooter>
                            </Card>
                        )}


                    {/* Pilot Invalid */}

                    { pilotStatusTotal === 0 ? (
                        <Card className="flex flex-col">
                            <CardHeader className="items-center pb-0">
                                <CardTitle>Pie Chart - IAA Pilot Status</CardTitle>
                                <CardDescription>
                                {new Date(new Date().setMonth(new Date().getMonth() - 6)).toLocaleDateString('en-GB', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                })} - {new Date().toLocaleDateString('en-GB', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                })}
                            </CardDescription>
                            </CardHeader>
                            <p className="text-lg font-semibold text-muted-foreground italic flex items-center justify-center h-full tracking-wide">
                                No Data Available
                            </p>
                        </Card>
                        ) : (
                            <Card className="flex flex-col">
                                <CardHeader className="items-center pb-0">
                                    <CardTitle>Pie Chart - IAA Pilot Status</CardTitle>
                                    <CardDescription>
                                        {new Date(new Date().setMonth(new Date().getMonth() - 6)).toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        })} - {new Date().toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        })}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="pb-0">
                                    <PieChartWithText chartData={pilotStatus} chartConfig={chartConfig} countData={pilotStatusTotal} />
                                </CardContent>
                                <CardFooter className="flex-col gap-2 text-sm">
                                    <div className='leading-none text-muted-foreground '>
                                        PT Indonesia Air Asia
                                    </div>
                                </CardFooter>
                            </Card>
                        )}


                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border md:min-h-min ">
                    <DashboardChart loginData={loginData} />
                </div>
            </div>
        </AppLayout>
    );
}
