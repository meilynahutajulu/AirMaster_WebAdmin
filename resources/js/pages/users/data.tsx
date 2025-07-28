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
import { toast } from 'sonner'
import { Toaster } from "@/components/ui/sonner"
import { useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Users Data',
		href: '/users/index',
	},
];

export default function UserData() {
	const { flash } = usePage<SharedData>().props;
	const { users } = usePage<{ users: User[] }>().props;

	useEffect(() => {
		if (flash?.error) {
			toast.error(flash.error);
		}
		if (flash?.success) {
			toast.success(flash.success);
		}
	}, [flash])

	const onDelete = useCallback(
		async (user: User) => {
			console.log(user.id_number);
			await router.post(`/users/delete/${user.id_number}`);
		},
		[],
	);

	const onEdit = useCallback(
		(user: User) => {
			const message = user['id_number'].toString();
				
			router.visit(`/users/edit/${message}`);
		},
		[]
	);
	


	const columns = useMemo(() => userColumns({ onEdit, onDelete }), []);

	return (
		< AppLayout breadcrumbs={breadcrumbs} >
			<Head title="Users Data" />
			<UsersLayout>
				<Toaster position="bottom-right" richColors />
				<Link className={buttonVariants({ variant: "default", className: "mb-2" })} href={'users/add'}><User2Icon />Add User</Link>
				<DataTable columns={columns} data={users} />
			</UsersLayout>
		</AppLayout >
	);
}