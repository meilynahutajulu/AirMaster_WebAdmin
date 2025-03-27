import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useInitials } from '@/hooks/use-initials';
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users Data',
        href: '/users/index',
    },
];

export default function Users() {
    const { users } = usePage<{ users: User[] }>().props;
    const getInitials = useInitials();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users Data" />

            <UsersLayout>
                <Table>
                    <TableCaption>PT. Indonesia Air Asia User Account</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>PHOTO</TableHead>
                            <TableHead className='w-[200px]'>EMAIL</TableHead>
                            <TableHead>NAME</TableHead>
                            <TableHead>RANK</TableHead>
                            <TableHead>LOA NO</TableHead>
                            <TableHead>LICENSE NO</TableHead>
                            <TableHead>INSTRUCTOR</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {users.map((user, index) => (
                            <TableRow key={index}>
                                <TableCell><Avatar className="h-8 w-8 overflow-hidden rounded-full">
                                    <AvatarImage src={user.PHOTOURL} alt={user.NAME} />
                                    <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {getInitials(user.NAME)}
                                    </AvatarFallback>
                                </Avatar></TableCell>
                                <TableCell>{user.EMAIL}</TableCell>
                                <TableCell>{user.NAME}</TableCell>
                                <TableCell>{user.RANK}</TableCell>
                                <TableCell>{user['LOA NO']}</TableCell>
                                <TableCell>{user['LICENSE NO.']}</TableCell>
                                <TableCell>{user.INSTRUCTOR.length > 1 ? user.INSTRUCTOR.join(', ') : user.INSTRUCTOR}</TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </UsersLayout>
        </AppLayout>
    );
}
