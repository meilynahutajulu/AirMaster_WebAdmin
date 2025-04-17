import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/react'; // Import Inertia's router
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';
import { userColumns } from '@/components/user-columns';
import { DataTable } from '@/layouts/users/user-table';
import { buttonVariants } from '@/components/ui/button';
import { User2Icon } from 'lucide-react';
import { useCallback, useMemo } from 'react';
import CryptoJS from 'crypto-js';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users Data',
        href: '/users/index',
    },
];

export default function UserData() {
    const { users } = usePage<{ users: User[] }>().props;

    const key = CryptoJS.enc.Utf8.parse('1234567890123456');
    const iv = CryptoJS.enc.Utf8.parse('abcdef9876543210');

    const onDelete = useCallback(
        (user: User) => console.log(`Delete ${user.EMAIL}`),
        [],
    );

    const onEdit = useCallback(
        (user: User) => {
            const message = user['ID NO'].toString();
            const encrypted = CryptoJS.AES.encrypt(message, key, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            }).toString(); 

            // console.log(`Edit ${encrypted}`);
            router.visit(`/users/edit/${encodeURIComponent(encrypted)}`);
        },
        [],
    );

    const columns = useMemo(() => userColumns({ onEdit, onDelete }), []);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users Data" />
            <UsersLayout>
                <Link className={buttonVariants({ variant: "default", className: "mb-2" })} href={'users/add'}><User2Icon />Add User</Link>
                <DataTable columns={columns} data={users} />
            </UsersLayout>
        </AppLayout>
    );
}